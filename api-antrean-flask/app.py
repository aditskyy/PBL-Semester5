from flask import Flask
from flask_cors import CORS
from routes.antrean_routes import antrean_bp

app = Flask(__name__)
CORS(app)

# Daftarkan blueprint
app.register_blueprint(antrean_bp)  

if __name__ == '__main__':
    # Jalankan di semua IP lokal agar bisa diakses lewat jaringan Wi-Fi
    app.run(host='0.0.0.0', port=5000, debug=True)
