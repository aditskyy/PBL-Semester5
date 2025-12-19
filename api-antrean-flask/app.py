from flask import Flask
from flask_cors import CORS
from routes.antrean_routes import antrean_bp
from routes.profile_routes import profile_bp
from routes.time_routes import time_bp
from routes.display_routes import display_bp
from routes.socket import init_socket
from flask_socketio import SocketIO

app = Flask(__name__)
CORS(app)

# 🔥 REGISTER SOCKET
init_socket(app)

# 🔥 REGISTER BLUEPRINT DENGAN PREFIX
app.register_blueprint(antrean_bp, url_prefix='/api')
app.register_blueprint(profile_bp)
app.register_blueprint(time_bp, url_prefix='/api')
app.register_blueprint(display_bp)

if __name__ == '__main__':
    from routes.socket import socketio
    socketio.run(app, host='0.0.0.0', port=5000, debug=True)
