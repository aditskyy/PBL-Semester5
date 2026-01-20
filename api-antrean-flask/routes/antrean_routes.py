import uuid
from flask import Blueprint, jsonify, request
from database.connection import get_db_connection
from routes.socket import emit_ambil_antrean

antrean_bp = Blueprint('antrean', __name__)

# ===============================
# GET JENIS LAYANAN / LOKET (UNTUK UI)
# ===============================
@antrean_bp.route('/get-loket', methods=['GET'])
def get_loket():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    try:
        # ✅ AMBIL DARI TABEL jenis_loket DAN JOIN dengan loket untuk warna/icon
        cursor.execute("""
            SELECT 
                j.kode_jenis,
                j.nama_jenis AS nama,
                l.warna,
                l.icon
            FROM jenis_loket j
            LEFT JOIN loket l ON j.kode_jenis = l.kode_jenis
            GROUP BY j.kode_jenis, j.nama_jenis
        """)
        
        result = cursor.fetchall()
        
        # 🔍 DEBUG PRINT
        print(f"📦 Total jenis loket: {len(result)}")
        for idx, loket in enumerate(result):
            print(f"📦 Loket {idx + 1}: {loket}")
        
        return jsonify(result)
        
    except Exception as e:
        print(f"❌ Error get_loket: {e}")
        return jsonify({'error': str(e)}), 500
    finally:
        cursor.close()
        conn.close()

# ===============================
# AMBIL ANTREAN (BERDASARKAN JENIS)
# ===============================
@antrean_bp.route('/ambil-antrean', methods=['POST'])
def ambil_antrean():
    data = request.get_json()
    
    print(f"📥 Request data: {data}")
    
    if not data or 'kode_jenis' not in data:
        return jsonify({'success': False, 'message': 'kode_jenis wajib diisi'}), 400

    kode_jenis = data['kode_jenis']
    token = uuid.uuid4().hex[:8]

    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    try:
        # Cek nomor terakhir hari ini
        cursor.execute("""
            SELECT nomor FROM antrian
            WHERE kode_jenis = %s
              AND DATE(tanggal) = CURDATE()
            ORDER BY id_antrian DESC
            LIMIT 1
        """, (kode_jenis,))
        last = cursor.fetchone()

        next_number = last['nomor'] + 1 if last else 1
        nomor_format = f"{kode_jenis}{str(next_number).zfill(3)}"
        
        print(f"✅ Nomor baru: {nomor_format}")

        # Insert antrean baru (tanpa kode_loket dulu, akan diisi saat dipanggil)
        cursor.execute("""
            INSERT INTO antrian (kode_jenis, nomor, tanggal, status, token)
            VALUES (%s, %s, NOW(), 'Menunggu', %s)
        """, (kode_jenis, next_number, token))

        new_id = cursor.lastrowid

        # Log aktivitas
        cursor.execute("""
            INSERT INTO log_antrian (id_antrian, user_id, aksi, waktu)
            VALUES (%s, %s, %s, NOW())
        """, (new_id, 7, 'AMBIL'))

        conn.commit()

        # Emit socket
        emit_ambil_antrean({
            'kode_jenis': kode_jenis,
            'nomor': next_number,
            'nomor_format': nomor_format,
            'status': 'Menunggu',
            'token': token
        })

        return jsonify({
            'success': True,
            'nomor': nomor_format,
            'token': token
        })

    except Exception as e:
        print(f"❌ Error ambil_antrean: {e}")
        conn.rollback()
        return jsonify({'success': False, 'message': str(e)}), 500
    finally:
        cursor.close()
        conn.close()