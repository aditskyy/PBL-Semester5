import 'dart:convert';
import 'dart:async';
import 'dart:js' as js;
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:socket_io_client/socket_io_client.dart' as IO;
import 'package:flutter_tts/flutter_tts.dart';


class DisplayPage extends StatefulWidget {
  const DisplayPage({super.key});

  @override
  State<DisplayPage> createState() => _DisplayPageState();
}

class _DisplayPageState extends State<DisplayPage> {
  late Future<Map<String, dynamic>> profileFuture;
  late FlutterTts flutterTts;
  late IO.Socket socket;

  String serverTime = '';
  String serverDate = '';
  String? activeCalledKode;
  Timer? timer;

  // 🔥 STATE ANTREAN
  List<dynamic> currentQueue = [];
  List<dynamic> historyQueue = [];

  // 📍 TAMBAHKAN KEY DI SINI
  final GlobalKey<AnimatedListState> _queueListKey = GlobalKey<AnimatedListState>();

  @override
  void initState() {
    super.initState();

    //  INIT TTS DI initState()
    flutterTts = FlutterTts();
    flutterTts.setLanguage('id-ID');
    flutterTts.setSpeechRate(0.45);
    flutterTts.setPitch(1.0);
    profileFuture = fetchProfile();

    fetchTime();
    initSocket();   // 🔥 realtime

    timer = Timer.periodic(
      const Duration(seconds: 1),
      (_) => fetchTime(),
    );
  }

  @override
  void dispose() {
    timer?.cancel();
    socket.disconnect();
    socket.dispose();
    super.dispose();
  }

   // ================= SOCKET INIT =================
  void initSocket() {
    socket = IO.io(
      'http://localhost:5000',
      IO.OptionBuilder()
          .setTransports(['websocket'])
          .disableAutoConnect()
          .build(),
    );

    socket.connect();

    socket.onConnect((_) {
      print('🟢 Connected to socket');
    });

    socket.onDisconnect((_) {
      print('🔴 Disconnected from socket');
    });

    socket.on('panggil_antrean', (data) {
  print('📡 EVENT panggil_antrean diterima: $data');
  onPanggilAntrean(data);
});

socket.on('panggil_ulang', (data) {
  print('🔁 EVENT panggil_ulang diterima: $data');
  onPanggilUlang(data);
});

socket.on('selesai_antrean', (data) {
  print('✅ selesai_antrean: $data');
  onSelesaiAntrean(data);
});


  }

  // =========================================================
// 🔌 SOCKET HANDLERS (CLEAN & SAFE VERSION)
// =========================================================
void onPanggilAntrean(dynamic data) {
  print('📡 panggil_antrean masuk: $data');

  final nomor = data['nomor'].toString();
  final loket = data['kode_loket'].toString();

  // 🔊 AUDIO
  speakAntrean(nomor, loket);

  // ✨ GLOW
  setState(() {
    activeCalledKode = nomor;
  });

  // 🟢 MASUKKAN KE CURRENT (SEDANG DIPANGGIL)
  final exists = currentQueue.any(
    (item) => item['nomor'] == nomor,
  );

  if (!exists) {
    setState(() {
      currentQueue.insert(0, {
        'nomor': nomor,
        'kode_loket': loket,
        'color': '#1E88E5',
      });
    });
  }
}


void onPanggilUlang(dynamic data) {
  // 🔊 Sesuaikan parameter: data['nomor'] & data['kode_loket']
  speakAntrean(data['nomor'].toString(), data['kode_loket']);
  
  print('🔊 Re-calling queue: ${data['nomor']}');
}

void onSelesaiAntrean(dynamic data) {
  final nomor = data['nomor'].toString();

  final index = currentQueue.indexWhere(
    (item) => item['nomor'] == nomor,
  );

  if (index != -1) {
    final removed = currentQueue.removeAt(index);

    setState(() {
      historyQueue.insert(0, removed);
      activeCalledKode = null;
    });
  }
}


// =========================================================
// 🔊 FUNGSI AUDIO (TTS)
// =========================================================

//Future<void> speakAntrean(dynamic nomor, String loket) async {
 // await flutterTts.stop();

 // final String nomorStr = nomor.toString();

  // Optimasi pembacaan: A001 -> A 0 0 1
  //final String formatNomor = nomorStr.split('').join(' ');

  //await flutterTts.speak(
    //'Nomor antrian $formatNomor, silakan menuju loket $loket',
 // );
//}

Future<void> speakAntrean(dynamic nomor, String loket) async {
  // Cek apakah audio sudah diizinkan browser
  final unlocked = js.context.callMethod('isSoundUnlocked');

  if (unlocked != true) {
    debugPrint('🔇 Audio belum diaktifkan oleh user');
    return;
  }

  await flutterTts.stop();

  final String nomorStr = nomor.toString();
  final String formatNomor = nomorStr.split('').join(' ');

  await flutterTts.setLanguage('id-ID');
  await flutterTts.setSpeechRate(0.45);
  await flutterTts.setVolume(1.0);

  await flutterTts.speak(
    'Nomor antrian $formatNomor, silakan menuju loket $loket',
  );
}



  Future<Map<String, dynamic>> fetchProfile() async {
    final res =
        await http.get(Uri.parse('http://localhost:5000/profile'));
    return json.decode(res.body);
  }

  Future<void> fetchTime() async {
    try {
      final res =
          await http.get(Uri.parse('http://localhost:5000/api/time'));
      if (res.statusCode == 200) {
        final data = json.decode(res.body);
        setState(() {
          serverTime = data['time'];
          serverDate = data['date'];
        });
      }
    } catch (_) {}
  }

  // 🔥 FETCH ANTREAN DINAMIS
  Future<void> fetchDisplay() async {
    try {
      final res =
          await http.get(Uri.parse('http://localhost:5000/api/display'));
      if (res.statusCode == 200) {
        final data = json.decode(res.body);
        setState(() {
          currentQueue = data['current'];
          historyQueue = data['history'];
        });

      // Memastikan AnimatedList melakukan sinkronisasi index dengan data terbaru
      // setelah data awal dimuat dari API
      _queueListKey.currentState?.setState(() {});
      }

    } catch (_) {}
  }

  Color hexToColor(String hex) =>
      Color(int.parse(hex.replaceFirst('#', '0xff')));

  // 🔥 ADAPTIVE GRID
  int gridCount(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    if (width > 1600) return 4;
    if (width > 1200) return 3;
    return 2;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: FutureBuilder<Map<String, dynamic>>(
        future: profileFuture,
        builder: (context, snapshot) {
          if (!snapshot.hasData) {
            return const Center(child: CircularProgressIndicator());
          }

          final profile = snapshot.data!;
          final headerColor = hexToColor(profile['color_palette']);

          return SafeArea(
            child: Column(
              children: [
                // ================= HEADER =================
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  color: headerColor,
                  child: Row(
                    children: [
                      Image.network(
                        'http://localhost:5000/static/logo/${profile['gambar_logo']}',
                        height: 50,
                        errorBuilder: (_, __, ___) =>
                            const Icon(Icons.image, color: Colors.white),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(profile['nama_instansi'],
                                style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 22,
                                    fontWeight: FontWeight.bold)),
                            Text(profile['alamat'],
                                style:
                                    const TextStyle(color: Colors.white70)),
                            Text(profile['telp'],
                                style:
                                    const TextStyle(color: Colors.white70)),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(serverTime,
                              style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 22,
                                  fontWeight: FontWeight.bold)),
                          Text(serverDate,
                              style:
                                  const TextStyle(color: Colors.white70)),
                        ],
                      ),
                    ],
                  ),
                ),

                // ================= MARQUEE (TEKS BERJALAN) =================
                Container(
                  height: 40,
                  color: Colors.blueAccent,
                  child: const MarqueeText(
                    text:
                        'Terima kasih atas kesabarannya, Anda berada dalam antrian dan giliran Anda akan segera tiba.',
                  ),
                ),

                // ================= BODY =================
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Row(
                      children: [
                        // ================= PANEL KIRI =================
                        Expanded(
                          flex: 3,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // ===== CURRENT QUEUE =====
                              currentQueue.isEmpty
                                  ? const Center(
                                      child: Padding(
                                        padding: EdgeInsets.all(40),
                                        child: Text(
                                          'BELUM ADA ANTRIAN',
                                          style: TextStyle(
                                              fontSize: 20,
                                              fontWeight: FontWeight.bold,
                                              color: Colors.grey),
                                        ),
                                      ),
                                    )
                                  : AnimatedList(
  key: _queueListKey,
  shrinkWrap: true,
  initialItemCount: currentQueue.length,
  physics: const NeverScrollableScrollPhysics(),
  itemBuilder: (context, index, animation) {
  final item = currentQueue[index];

  return Padding(
    padding: const EdgeInsets.only(bottom: 12),
    child: SizeTransition( // Ubah Slide jadi Size agar lebih rapi saat removeItem
      sizeFactor: animation,
      child: FadeTransition(
        opacity: animation,
        child: AntreanCard(
          title: 'ANTREAN',
          nomor: item['nomor'].toString(),
          loket: item['kode_loket'].toString(),
          color: hexToColor(item['color']),
          active: item['nomor'].toString() == activeCalledKode, // 📍 PASS STATE DI SINI
        ),
      ),
    ),
  );
},
),


                              const SizedBox(height: 20),

                              const Text('RIWAYAT ANTRIAN',
                                  style: TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold)),

                              const SizedBox(height: 12),

                              // ===== HISTORY =====
                              Expanded(
                                child: historyQueue.isEmpty
                                    ? const Center(
                                        child: Text(
                                          'BELUM ADA RIWAYAT',
                                          style: TextStyle(
                                              color: Colors.grey),
                                        ),
                                      )
                                    : GridView.builder(
                                        itemCount:
                                            historyQueue.length,
                                        gridDelegate:
                                            SliverGridDelegateWithFixedCrossAxisCount(
                                          crossAxisCount:
                                              gridCount(context),
                                          crossAxisSpacing: 12,
                                          mainAxisSpacing: 12,
                                        ),
                                        itemBuilder:
                                            (context, index) {
                                          final item =
                                              historyQueue[index];
                                          return RiwayatCard(
                                            item['nomor'].toString(),      // GANTI dari item['kode']
                                            item['kode_loket'].toString(),
                                            hexToColor(
                                                item['color']),
                                          );
                                        },
                                      ),
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(width: 16),

                        // ================= PANEL KANAN =================
                        Expanded(
                          flex: 2,
                          child: Container(
                            decoration: BoxDecoration(
                              color: Colors.black,
                              borderRadius:
                                  BorderRadius.circular(16),
                            ),
                            child: const Center(
                              child: Text('VIDEO YOUTUBE',
                                  style: TextStyle(
                                      color: Colors.white70,
                                      fontSize: 18)),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

// ================= MARQUEE =================
class MarqueeText extends StatefulWidget {
  final String text;
  const MarqueeText({super.key, required this.text});

  @override
  State<MarqueeText> createState() => _MarqueeTextState();
}

class _MarqueeTextState extends State<MarqueeText>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _controller =
        AnimationController(vsync: this, duration: const Duration(seconds: 10))
          ..repeat();
    _animation = Tween(begin: 1.0, end: -1.0).animate(_controller);
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _animation,
      builder: (_, child) {
        return FractionalTranslation(
          translation: Offset(_animation.value, 0),
          child: child,
        );
      },
      child: Center(
        child: Text(widget.text,
            style: const TextStyle(color: Colors.white, fontSize: 18)),
      ),
    );
  }
}

// ================= CARD =================
class AntreanCard extends StatelessWidget {
  final String title, nomor, loket;
  final Color color;
  final bool active; // 📍 Tambah ini

  const AntreanCard({
    super.key,
    required this.title,
    required this.nomor,
    required this.loket,
    required this.color,
    this.active = false, // 📍 Default false
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer( // Pakai AnimatedContainer agar transisi glow halus
      duration: const Duration(milliseconds: 500),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: active ? color.withOpacity(0.9) : color,
        borderRadius: BorderRadius.circular(16),
        boxShadow: active
            ? [
                BoxShadow(
                  color: Colors.yellow.withOpacity(0.8),
                  blurRadius: 20,
                  spreadRadius: 4,
                )
              ]
            : [],
      ),
      child: Column(
        children: [
          Text(title, style: const TextStyle(color: Colors.white70)),
          const SizedBox(height: 10),
          Text(nomor,
              style: const TextStyle(
                  color: Colors.white,
                  fontSize: 36,
                  fontWeight: FontWeight.bold)),
          const SizedBox(height: 6),
          Text(loket, style: const TextStyle(color: Colors.white70)),
        ],
      ),
    );
  }
}

class RiwayatCard extends StatelessWidget {
  final String nomor, loket;
  final Color color;
  const RiwayatCard(this.nomor, this.loket, this.color, {super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration:
          BoxDecoration(color: color, borderRadius: BorderRadius.circular(14)),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Text('ANTREAN', style: TextStyle(color: Colors.white70)),
          const SizedBox(height: 6),
          Text(nomor,
              style: const TextStyle(
                  color: Colors.white,
                  fontSize: 22,
                  fontWeight: FontWeight.bold)),
          const SizedBox(height: 4),
          Text(loket, style: const TextStyle(color: Colors.white70)),
        ],
      ),
    );
  }
}