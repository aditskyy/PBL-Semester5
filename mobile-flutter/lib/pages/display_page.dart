import 'dart:convert';
import 'dart:async';
import 'package:audioplayers/audioplayers.dart';
import 'package:youtube_player_flutter/youtube_player_flutter.dart';
//import 'dart:js' as js;
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
  final AudioPlayer audioPlayer = AudioPlayer();
  
  @override
void initState() {
  super.initState();
  profileFuture = fetchProfile();
  audioPlayer.setReleaseMode(ReleaseMode.stop);

  WidgetsBinding.instance.addPostFrameCallback((_) {
    initAfterUIReady();
  });
}

void initAfterUIReady() {
  // ✅ TTS aman
  flutterTts = FlutterTts();
  flutterTts.setLanguage('id-ID');
  flutterTts.setSpeechRate(0.45);
  flutterTts.setPitch(1.0);

  // ✅ socket aman
  initSocket();

  fetchDisplay();
  
  // ✅ timer aman
  timer = Timer.periodic(
    const Duration(seconds: 1),
    (_) {
      if (!mounted) return;
      fetchTime();
    },
  );
}

  @override
  void dispose() {
    timer?.cancel();
    socket.disconnect();
    socket.dispose();
    audioPlayer.dispose();
    super.dispose();
  }

   // ================= SOCKET INIT =================
  void initSocket() {
    socket = IO.io(
      'http://192.168.1.2:5000',
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
  // Mengambil warna dinamis dari socket
  final warnaStr = data['warna'] ?? '#1E88E5'; 

  // 🔊 AUDIO: Jalankan TTS (Fungsi ini jangan sampai hilang)
  speakAntrean(nomor, loket);

  setState(() {
    // ✨ GLOW: Tandai nomor mana yang sedang aktif bersuara
    activeCalledKode = nomor;

    // 🔄 LOGIKA MULTI-LOKET
    // Cari apakah loket ini sudah ada di daftar 'currentQueue'
    final index = currentQueue.indexWhere(
      (item) => item['kode_loket'].toString() == loket,
    );

    if (index != -1) {
      // ✅ JIKA LOKET SUDAH ADA: Update nomor dan warnanya
      currentQueue[index]['nomor'] = nomor;
      currentQueue[index]['warna'] = warnaStr; // Update warna jika di DB berubah
    } else {
      // ✅ JIKA LOKET BELUM ADA: Tambahkan ke daftar
      currentQueue.add({
        'nomor': nomor,
        'kode_loket': loket,
        'warna': warnaStr,
      });

      // ↕️ SORTING: Supaya urutan di TV tetap Loket 1, 2, 3 (tidak acak)
      currentQueue.sort((a, b) => 
        a['kode_loket'].toString().compareTo(b['kode_loket'].toString())
      );
    }
  });
}

void onPanggilUlang(dynamic data) {
  print('📡 panggil_ulang masuk: $data');

  final nomor = data['nomor'].toString();
  final loket = data['kode_loket'].toString();

  // 🔊 AUDIO: Putar ulang suara panggilannya
  speakAntrean(nomor, loket);

  setState(() {
    // ✨ GLOW: Tandai kembali nomor ini agar kartunya berkedip/glow di TV
    activeCalledKode = nomor;
  });

  print('🔊 Re-calling queue: $nomor pada Loket: $loket');
}


void onSelesaiAntrean(dynamic data) {
  // Print ini sangat membantu saat debugging di TV
  print('📡 selesai_antrean masuk: $data');
  
  final loket = data['kode_loket'].toString(); 

  setState(() {
    final index = currentQueue.indexWhere(
      (item) => item['kode_loket'].toString() == loket, 
    );

    if (index != -1) {
      // 1. Ambil data yang dihapus (termasuk data nomor, kode_loket, dan warna)
      final removed = currentQueue.removeAt(index);
      
      // 2. Masukkan ke historyQueue
      // Data 'removed' ini tetap membawa field 'color', 
      // Jadi Riwayat Card di bawah bisa tetap berwarna jika kamu mau.
      historyQueue.insert(0, removed);
      
      // 3. Batasi history agar tidak terlalu panjang (misal maksimal 10)
      if (historyQueue.length > 10) {
        historyQueue.removeLast();
      }
      
      // 4. Reset glow jika nomor tersebut adalah yang terakhir dipanggil
      if (activeCalledKode == removed['nomor']) {
        activeCalledKode = null;
      }
    }
  });
}


Future<void> speakAntrean(dynamic nomor, String loket) async {
    if (!mounted) return;

    try {
      // A. Hentikan suara yang sedang berjalan (biar tidak tumpang tindih)
      await flutterTts.stop();
      await audioPlayer.stop();

      // B. Putar Suara Bell dari Assets
      // Kita gunakan AssetSource untuk file yang ada di folder assets
      await audioPlayer.play(AssetSource('bell.mp3'));

      // C. Beri jeda sebentar (misal 2 detik) agar Bell selesai baru TTS bicara
      // Sesuaikan durasi ini dengan panjang suara bell Anda
      await Future.delayed(const Duration(seconds: 2));

      // D. Jalankan TTS
      final formatNomor = nomor.toString().split('').join(' ');
      await flutterTts.speak(
        'Nomor antrian $formatNomor, silakan menuju loket $loket',
      );
    } catch (e) {
      print("Error pada Audio: $e");
    }
  }

  Future<Map<String, dynamic>> fetchProfile() async {
    final res =
        await http.get(Uri.parse('http://192.168.1.2:5000/profile'));
    return json.decode(res.body);
  }

  Future<void> fetchTime() async {
    try {
      final res =
          await http.get(Uri.parse('http://192.168.1.2:5000/api/time'));
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
          await http.get(Uri.parse('http://192.168.1.2:5000/api/display'));
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

  Color hexToColor(String? hexString) {
  if (hexString == null || hexString.isEmpty) return const Color(0xFF1E88E5); // Default Biru
  try {
    final buffer = StringBuffer();
    if (hexString.length == 6 || hexString.length == 7) buffer.write('ff');
    buffer.write(hexString.replaceFirst('#', ''));
    return Color(int.parse(buffer.toString(), radix: 16));
  } catch (e) {
    return const Color(0xFF1E88E5);
  }
}

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
                      Image.asset(
  'assets/logopnb.png', 
  height: 55,        
  fit: BoxFit.contain,
  errorBuilder: (context, error, stackTrace) {
    return const Icon(Icons.business, color: Colors.white, size: 40);
  },
),
                      const SizedBox(width: 15),
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
                          flex: 2,
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.start, 
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // ----- AREA ANTREAN AKTIF (ATAS) -----
                            const Text('ANTREAN SAAT INI', 
                            style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white)),
      
      // Gunakan Flexible agar GridView bisa menyesuaikan ruang yang ada
      Container(
      height: MediaQuery.of(context).size.height * 0.32, // Mengunci tinggi di layar
      margin: const EdgeInsets.only(bottom: 0),
      child: currentQueue.isEmpty
          ? _buildEmptyState('BELUM ADA ANTRIAN')
          : GridView.builder(
              padding: EdgeInsets.zero,
              physics: const NeverScrollableScrollPhysics(), // TV tidak butuh scroll di sini
              itemCount: currentQueue.length,
              gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                // Tetap bersebelahan: 2 kolom untuk 2 data, 3 kolom untuk 3 data atau lebih
                crossAxisCount: currentQueue.length <= 2 ? 2 : 3,
                crossAxisSpacing: 20,
                mainAxisSpacing: 0,
                // Rasio agar kartu tetap proporsional saat mengecil
                childAspectRatio: currentQueue.length <= 2 ? 1.8 : 1.8, 
              ),
              itemBuilder: (context, index) {
                final item = currentQueue[index];
                return AntreanCard(
                  title: 'ANTRIAN',
                  nomor: item['nomor'].toString(),
                  loket: item['kode_loket'].toString(),
                  color: hexToColor(item['warna'] ?? '#1E88E5'),
                  active: item['nomor'].toString() == activeCalledKode,
                );
              },
            ),
    ),

      const Text('RIWAYAT ANTRIAN',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
      const SizedBox(height: 12),

                              // ===== HISTORY =====
                              Expanded(
                                flex: 1,
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
                                          crossAxisCount:6,
                                          crossAxisSpacing: 10,
                                          mainAxisSpacing: 10,
                                          childAspectRatio: 1,
                                        ),
                                        itemBuilder:
                                            (context, index) {
                                          final item =
                                              historyQueue[index];
                                          return RiwayatCard(
                                            item['nomor'].toString(),      
                                            item['kode_loket'].toString(),
                                            hexToColor(
                                                item['warna']),
                                          );
                                        },
                                      ),
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(width: 12),

                        // ================= PANEL KANAN =================
                        Expanded(
  flex: 1,
  child: Column(
    // ✨ INI KUNCINYA: Memaksa semua anak Column mulai dari atas
    mainAxisAlignment: MainAxisAlignment.start, 
    children: [
      Container(
        decoration: BoxDecoration(
          color: Colors.black,
          borderRadius: BorderRadius.circular(16),
        ),
        // Gunakan AspectRatio agar video tetap proporsional 16:9
        child: AspectRatio(
          aspectRatio: 16 / 9,
          child: const YoutubePanel(
            videoUrl: 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
          ),
        ),
      ),
      // Jika ingin ada ruang kosong di bawah video agar tidak ditarik ke tengah, 
      // Anda bisa menambah Spacer() atau biarkan saja karena Column sudah mainAxisAlignment.start
    ],
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
  Widget _buildEmptyState(String message) {
    return Center(
      child: Container(
        padding: const EdgeInsets.all(40),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.05),
          borderRadius: BorderRadius.circular(15),
        ),
        child: Text(
          message,
          style: const TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: Colors.grey,
          ),
        ),
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
  final bool active;

  const AntreanCard({
    super.key,
    required this.title,
    required this.nomor,
    required this.loket,
    required this.color,
    this.active = false,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 500),
      padding: const EdgeInsets.symmetric(vertical: 2, horizontal: 10),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(20),
        border: active
            ? Border.all(color: Colors.yellowAccent, width: 8)
            : Border.all(color: Colors.white24, width: 1),
        boxShadow: active
            ? [
                BoxShadow(
                  color: Colors.yellow.withOpacity(0.6),
                  blurRadius: 40,
                  spreadRadius: 8,
                )
              ]
            : [
                BoxShadow(
                  color: Colors.black.withOpacity(0.3),
                  blurRadius: 15,
                  offset: const Offset(0, 8),
                )
              ],
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.start,
        children: [
          const Text(
            'ANTRIAN',
            style: TextStyle(
              color: Colors.white70,
              fontSize: 15,
              letterSpacing: 1,
            ),
          ),
          const SizedBox(height: 6),

          Expanded(
            child: FittedBox(
              fit: BoxFit.contain,
              child: Text(
                nomor,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 65,
                  fontWeight: FontWeight.bold,
                  shadows: [
                    Shadow(
                      color: Colors.black26,
                      blurRadius: 10,
                      offset: Offset(2, 2),
                    )
                  ],
                ),
              ),
            ),
          ),

          const SizedBox(height: 2),
          Text(
            'LOKET $loket',
            style: const TextStyle(
              color: Colors.white,
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),
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
      decoration: BoxDecoration(
        // Gunakan opacity sedikit agar riwayat tidak lebih mencolok dari antrean aktif
        color: color.withOpacity(0.8), 
        borderRadius: BorderRadius.circular(15),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 4,
            offset: const Offset(0, 2),
          )
        ],
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Text('ANTREAN', 
            style: TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.bold)),
          const SizedBox(height: 4),
          
          // Menggunakan FittedBox agar nomor selalu muat di kotak kecil
          Expanded(
            child: FittedBox(
              fit: BoxFit.contain,
              child: Text(nomor,
                  style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold)),
            ),
          ),
          
          const SizedBox(height: 4),
          // Menambahkan teks 'LOKET' agar lebih informatif
          Text('LOKET $loket', 
            style: const TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }
}

class YoutubePanel extends StatefulWidget {
  final String videoUrl;
  const YoutubePanel({super.key, required this.videoUrl});

  @override
  State<YoutubePanel> createState() => _YoutubePanelState();
}

class _YoutubePanelState extends State<YoutubePanel> {
  late YoutubePlayerController _controller;

  @override
  void initState() {
    super.initState();
    final videoId = YoutubePlayer.convertUrlToId(widget.videoUrl);

    _controller = YoutubePlayerController(
      initialVideoId: videoId ?? '',
      flags: const YoutubePlayerFlags(
        autoPlay: true,
        mute: true,      // Mute agar tidak mengganggu suara panggil antrean
        loop: true,      // Putar terus menerus
        isLive: false,
        forceHD: true,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: YoutubePlayer(
        controller: _controller,
        showVideoProgressIndicator: true,
        progressIndicatorColor: Colors.blueAccent,
      ),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }
}