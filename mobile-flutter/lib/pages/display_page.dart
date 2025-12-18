import 'dart:convert';
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

class DisplayPage extends StatefulWidget {
  const DisplayPage({super.key});

  @override
  State<DisplayPage> createState() => _DisplayPageState();
}

class _DisplayPageState extends State<DisplayPage> {
  late Future<Map<String, dynamic>> profileFuture;

  String serverTime = '';
  String serverDate = '';
  Timer? timer;

  // 🔥 STATE DINAMIS ANTREAN
  List<dynamic> currentQueue = [];
  List<dynamic> historyQueue = [];

  @override
  void initState() {
    super.initState();
    profileFuture = fetchProfile();
    fetchTime();
    fetchDisplay();

    timer = Timer.periodic(
      const Duration(seconds: 1),
      (_) => fetchTime(),
    );
  }

  @override
  void dispose() {
    timer?.cancel();
    super.dispose();
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
                                  : GridView.builder(
                                      shrinkWrap: true,
                                      physics:
                                          const NeverScrollableScrollPhysics(),
                                      itemCount: currentQueue.length,
                                      gridDelegate:
                                          SliverGridDelegateWithFixedCrossAxisCount(
                                        crossAxisCount:
                                            gridCount(context),
                                        crossAxisSpacing: 12,
                                        mainAxisSpacing: 12,
                                        childAspectRatio: 1.4,
                                      ),
                                      itemBuilder: (context, index) {
                                        final item =
                                            currentQueue[index];
                                        return AntreanCard(
                                          title: 'ANTREAN',
                                          nomor: item['kode'],
                                          loket: item['loket'],
                                          color:
                                              hexToColor(item['color']),
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
                                            item['kode'],
                                            item['loket'],
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
  const AntreanCard(
      {super.key,
      required this.title,
      required this.nomor,
      required this.loket,
      required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration:
          BoxDecoration(color: color, borderRadius: BorderRadius.circular(16)),
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
