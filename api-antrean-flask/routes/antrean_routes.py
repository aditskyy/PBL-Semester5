import uuid
from flask import Blueprint, jsonify, request
from database.connection import get_db_connection
from routes.socket import emit_ambil_antrean

antrean_bp = Blueprint('antrean', __name__)

# ===============================
# ENDPOINT: AMBIL DAFTAR LOKET (UNTUK FLUTTER DINAMIS)
# ===============================
@antrean_bp.route('/get-loket', methods=['GET'])
def get_loket():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    try:
        # Tambahkan kode_loket ke dalam query SELECT
        cursor.execute("SELECT kode_loket, kode_jenis, nama_loket, warna FROM loket")
        lokets = cursor.fetchall()
        return jsonify(lokets)
    except Exception as e:
        # Jika ada error (misal nama kolom salah), pesan error akan muncul di sini
        return jsonify({'success': False, 'message': str(e)}), 500
    finally:
        cursor.close()
        conn.close()

@antrean_bp.route('/ambil-antrean', methods=['POST'])
def ambil_antrean():
    # ===============================
    # 1. VALIDASI INPUT (Sekarang menerima kode_loket)
    # ===============================
    data = request.get_json()
    if not data or 'kode_loket' not in data:
        return jsonify({
            'success': False,
            'message': 'kode_loket wajib diisi'
        }), 400

    kode_loket_input = data['kode_loket'] # Contoh: "A-01"
    token = uuid.uuid4().hex[:8]

    # ===============================
    # 2. KONEKSI DATABASE
    # ===============================
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    try:
        # ===============================
        # 3. AMBIL DATA LOKET BERDASARKAN KODE_LOKET
        # ===============================
        # Kita butuh kode_jenis (A/B/C) untuk format nomor tiket
        cursor.execute(
            "SELECT kode_loket, kode_jenis FROM loket WHERE kode_loket = %s LIMIT 1",
            (kode_loket_input,)
        )
        loket = cursor.fetchone()

        if not loket:
            return jsonify({
                'success': False,
                'message': 'Loket tidak ditemukan'
            }), 404 # Ini yang menyebabkan error 404 jika data tidak pas

        kode_loket = loket['kode_loket']
        kode_jenis = loket['kode_jenis']

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
        # Hasil format: A001, B002, dll
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
        # 7. RESPONSE KE CLIENT (SINKRON DENGAN FLUTTER)
        # ===============================
        return jsonify({
            'success': True,
            'nomor': nomor_format, # Flutter mencari kunci 'nomor'
            'token': token,
            'message': f'Nomor antrean {nomor_format} berhasil diambil.'
        })

    finally:
        # ===============================
        # 8. TUTUP KONEKSI DATABASE
        # ===============================
        cursor.close()
        conn.close()
