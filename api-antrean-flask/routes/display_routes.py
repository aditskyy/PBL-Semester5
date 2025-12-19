from flask import Blueprint, jsonify
from database.connection import get_db_connection

display_bp = Blueprint('display', __name__)

@display_bp.route('/api/display', methods=['GET'])
def display_antrean():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    # ================= CURRENT (SEDANG DIPANGGIL) =================
    cursor.execute("""
        SELECT 
            CONCAT(kode_jenis, LPAD(nomor, 3, '0')) AS nomor,
            kode_loket,
            '#1E88E5' AS color
        FROM antrian
        WHERE status = 'Dipanggil'
          AND DATE(tanggal) = CURDATE()
        ORDER BY updated_at DESC
        LIMIT 1
    """)
    current = cursor.fetchall()

    # ================= HISTORY (SELESAI) =================
    cursor.execute("""
        SELECT 
            CONCAT(kode_jenis, LPAD(nomor, 3, '0')) AS nomor,
            kode_loket,
            '#43A047' AS color
        FROM antrian
        WHERE status = 'Selesai'
          AND DATE(tanggal) = CURDATE()
        ORDER BY updated_at DESC
        LIMIT 12
    """)
    history = cursor.fetchall()

    cursor.close()
    conn.close()

    return jsonify({
        'current': current,
        'history': history
    })
