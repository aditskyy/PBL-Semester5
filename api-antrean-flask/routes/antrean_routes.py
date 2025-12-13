from flask import Blueprint, jsonify, request
from database.connection import get_db_connection
from datetime import date

antrean_bp = Blueprint('antrean', __name__)
@antrean_bp.route('/ambil-antrean', methods=['POST'])
def ambil_antrean():
    data = request.get_json()

    if not data or 'kode_jenis' not in data:
        return jsonify({'success': False, 'message': 'kode_jenis wajib diisi'}), 400

    kode_jenis = data['kode_jenis'].upper()  # A, B, atau C

    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    # Ambil kode_loket dari jenis yang dipilih
    cursor.execute("SELECT kode_loket FROM loket WHERE kode_jenis = %s LIMIT 1", (kode_jenis,))
    loket = cursor.fetchone()
    if not loket:
        cursor.close()
        conn.close()
        return jsonify({'success': False, 'message': 'Jenis loket tidak ditemukan'}), 404

    kode_loket = loket['kode_loket']

    # Ambil antrean terakhir
    cursor.execute("""
        SELECT nomor FROM antrian
        WHERE kode_loket = %s
        AND DATE(tanggal) = CURDATE()
        ORDER BY id_antrian DESC LIMIT 1
    """, (kode_loket,))
    last = cursor.fetchone()

    next_number = last['nomor'] + 1 if last else 1
    nomor_format = f"{kode_jenis}{str(next_number).zfill(3)}"

    # Simpan antrean baru
    cursor.execute("""
    INSERT INTO antrian (kode_jenis, kode_loket, nomor, tanggal, status, user_id, created_at)
    VALUES (%s, %s, %s, CURDATE(), 'Menunggu', NULL, NOW())
""", (kode_jenis, kode_loket, next_number))
    conn.commit()

    cursor.close()
    conn.close()

    return jsonify({
        'success': True,
        'nomor_antrean': nomor_format,
        'message': f'Nomor antrean {nomor_format} berhasil diambil.'
    })
