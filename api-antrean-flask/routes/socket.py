from flask_socketio import SocketIO

socketio = SocketIO(cors_allowed_origins="*")

def init_socket(app):
    socketio.init_app(app)

    @socketio.on('connect')
    def connect():
        print('🟢 Client connected')

    @socketio.on('disconnect')
    def disconnect():
        print('🔴 Client disconnected')


def emit_ambil_antrean(data):
    print('📢 emit ambil_antrean:', data)
    # 🔥 broadcast default = ke semua client
    socketio.emit('ambil_antrean', data)


def emit_panggil_antrean(data):
    print('📢 emit panggil_antrean:', data)
    socketio.emit('panggil_antrean', data)


def emit_panggil_ulang(data):
    print('🔁 emit panggil_ulang:', data)
    socketio.emit('panggil_ulang', data)

