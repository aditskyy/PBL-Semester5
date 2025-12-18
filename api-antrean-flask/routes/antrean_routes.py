import uuid
from flask import Blueprint, jsonify, request
from database.connection import get_db_connection
from routes.socket import emit_ambil_antrean

antrean_bp = Blueprint('antrean', __name__)

@antrean_bp.route('/ambil-antrean', methods=['POST'])
def ambil_antrean():
    # ===============================
    # 1. VALIDASI INPUT
    # ===============================
    data = request.get_json()
    if not data or 'kode_jenis' not in data:
        return jsonify({
            'success': False,
            'message': 'kode_jenis wajib diisi'
        }), 400

    kode_jenis = data['kode_jenis'].upper()  # A, B, C
    token = uuid.uuid4().hex[:8]            # token unik antrean

    # ===============================
    # 2. KONEKSI DATABASE
    # ===============================
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    try:
        # ===============================
        # 3. AMBIL KODE LOKET
        # ===============================
        cursor.execute(
            "SELECT kode_loket FROM loket WHERE kode_jenis = %s LIMIT 1",
            (kode_jenis,)
        )
        loket = cursor.fetchone()

        if not loket:
            return jsonify({
                'success': False,
                'message': 'Jenis loket tidak ditemukan'
            }), 404

        kode_loket = loket['kode_loket']

        # ===============================
        # 4. AMBIL NOMOR ANTRIAN TERAKHIR
        # ===============================
        cursor.execute("""
            SELECT nomor FROM antrian
            WHERE kode_loket = %s
              AND DATE(tanggal) = CURDATE()
            ORDER BY id_antrian DESC
            LIMIT 1
        """, (kode_loket,))
        last_antrian = cursor.fetchone()

        next_number = last_antrian['nomor'] + 1 if last_antrian else 1
        nomor_format = f"{kode_jenis}{str(next_number).zfill(3)}"

        # ===============================
        # 5. SIMPAN ANTRIAN BARU
        # ===============================
        cursor.execute("""
            INSERT INTO antrian (kode_jenis, kode_loket, nomor, tanggal, status, token)
            VALUES (%s, %s, %s, CURDATE(), 'Menunggu', %s)
        """, (kode_jenis, kode_loket, next_number, token))
        conn.commit()

        # ===============================
        # 6. EMIT WEBSOCKET (REAL-TIME)
        # ===============================
        emit_ambil_antrean({
            'kode_jenis': kode_jenis,
            'kode_loket': kode_loket,
            'nomor': next_number,
            'nomor_format': nomor_format,
            'status': 'Menunggu',
            'token': token
        })

        # ===============================
        # 7. RESPONSE KE CLIENT
        # ===============================
        return jsonify({
            'success': True,
            'nomor_antrean': nomor_format,
            'token': token,
            'message': f'Nomor antrean {nomor_format} berhasil diambil.'
        })

    finally:
        # ===============================
        # 8. TUTUP KONEKSI DATABASE
        # ===============================
        cursor.close()
        conn.close()
