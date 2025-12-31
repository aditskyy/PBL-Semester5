from flask import Blueprint, jsonify
from database.connection import get_db_connection

display_bp = Blueprint('display', __name__)

@display_bp.route('/api/display', methods=['GET'])
def display_antrean():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    # ================= CURRENT (MULTI-LOKET) =================
    # Perbaikan: Menggunakan id_antrian (sesuai database Anda)
    cursor.execute("""
        SELECT 
            CONCAT(a.kode_jenis, LPAD(a.nomor, 3, '0')) AS nomor,
            a.kode_loket,
            l.warna AS warna
        FROM antrian a
        JOIN loket l ON a.kode_loket = l.kode_loket
        WHERE a.id_antrian IN (
            SELECT MAX(id_antrian) 
            FROM antrian 
            WHERE status = 'Dipanggil' 
              AND DATE(tanggal) = CURDATE()
            GROUP BY kode_loket
        )
        ORDER BY a.kode_loket ASC
    """)
    current = cursor.fetchall()

    # ================= HISTORY (SELESAI) =================
    cursor.execute("""
        SELECT 
            CONCAT(a.kode_jenis, LPAD(a.nomor, 3, '0')) AS nomor,
            a.kode_loket,
            l.warna AS warna
        FROM antrian a
        JOIN loket l ON a.kode_loket = l.kode_loket
        WHERE a.status = 'Selesai'
          AND DATE(a.tanggal) = CURDATE()
        ORDER BY a.updated_at DESC
        LIMIT 12
    """)
    history = cursor.fetchall()

    cursor.close()
    conn.close()

    # Pastikan return 'current' (bukan 'active_queue') agar sinkron dengan fetchDisplay di Flutter
    return jsonify({
        'current': current,
        'history': history
    })
