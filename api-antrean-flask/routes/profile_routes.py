from flask import Blueprint, jsonify

profile_bp = Blueprint('profile', __name__)

@profile_bp.route('/profile', methods=['GET'])
def get_profile():
    return jsonify({
        "nama_instansi": "PUJASERA",
        "alamat": "Politeknik Negeri Bali",
        "telp": "0361 12345",
        "gambar_logo": "logo_pnb.png",
        "color_palette": "#0A2E6D"
    })
