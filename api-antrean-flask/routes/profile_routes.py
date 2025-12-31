from flask import Blueprint, jsonify, send_file
import requests
import io
import os

profile_bp = Blueprint('profile', __name__)

@profile_bp.route('/profile')
def profile():
    try:
        # Flask panggil CI4 via localhost (tetap dipertahankan untuk ambil data teks)
        res = requests.get('http://localhost:8080/api/profile', timeout=5)
        data = res.json()
        
        # LOGO SUDAH DI ASSETS FLUTTER, jadi kita tidak perlu modifikasi URL lagi.
        # Flutter nanti akan mengabaikan field 'full_url_logo' dan pakai Image.asset.
        
        return jsonify(data)
    except Exception as e:
        return jsonify({"error": str(e)}), 500