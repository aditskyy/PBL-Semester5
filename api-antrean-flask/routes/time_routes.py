from flask import Blueprint, jsonify
from datetime import datetime
import pytz
import locale # Tambahkan ini

time_bp = Blueprint('time', __name__)

@time_bp.route('/time', methods=['GET'])
def get_time():
    # Mengatur locale ke Indonesia
    try:
        # Di Windows gunakan 'id_ID' atau 'Indonesian_Indonesia.1252'
        # Di Linux/Mac gunakan 'id_ID.utf8'
        locale.setlocale(locale.LC_TIME, 'id_ID') 
    except:
        # Fallback jika locale id_ID tidak terinstall di sistem
        pass

    tz = pytz.timezone('Asia/Makassar')
    now = datetime.now(tz)

    return jsonify({
        "time": now.strftime('%H : %M : %S'),
        # %A akan otomatis menjadi Senin, Selasa, dst jika locale berhasil diset
        "date": now.strftime('%A, %d %B %Y') 
    })