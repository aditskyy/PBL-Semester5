from flask import Flask, request, jsonify
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

# 🔥 ROUTE EMIT SOCKET (INI YANG HILANG!)
@app.route('/api/emit', methods=['POST'])
def emit_event():
    if not request.is_json:
        return jsonify({'error': 'Request harus JSON'}), 400

    data = request.get_json()

    if 'event' not in data or 'data' not in data:
        return jsonify({'error': 'Format harus {event, data}'}), 400

    event = data['event']
    payload = data['data']

    print('📢 EMIT:', event, payload)

    socketio.emit(event, payload)

    return jsonify({'status': 'ok'})




if __name__ == '__main__':
    from routes.socket import socketio
    socketio.run(app, host='0.0.0.0', port=5000, debug=True)
