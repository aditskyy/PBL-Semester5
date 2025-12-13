import 'package:flutter/material.dart';
import 'package:user_ambil/services/service.dart';
import 'tiket_page.dart';

class PilihLoketPage extends StatefulWidget {
  const PilihLoketPage({Key? key}) : super(key: key);

  @override
  State<PilihLoketPage> createState() => _PilihLoketPageState();
}

class _PilihLoketPageState extends State<PilihLoketPage> {
  String? selectedLoket;
  bool isLoading = false;

  /// Fungsi untuk ambil antrean dari API
  Future<void> ambilAntrean() async {
    if (selectedLoket == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Pilih loket terlebih dahulu")),
      );
      return;
    }

    setState(() => isLoading = true);

    try {
      final response = await ApiService.ambilAntrean(selectedLoket!);

      if (response['success'] == true) {
        // Jika sukses, pindah ke halaman tiket
        Navigator.push(
          context,
          MaterialPageRoute(
            builder:
                (context) => TiketPage(
                  nomorAntrean: response['nomor_antrean'],
                  kodeJenis: selectedLoket!,
                ),
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response['message'] ?? "Gagal mengambil antrean"),
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text("Terjadi kesalahan: $e")));
    } finally {
      setState(() => isLoading = false);
    }
  }

  /// UI utama
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              Color(0xFF1976D2), // Biru tua
              Color(0xFF2196F3), // Biru medium
            ],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              // Header
              Padding(
                padding: const EdgeInsets.all(24.0),
                child: Column(
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(
                            Icons.tv,
                            color: Colors.white,
                            size: 32,
                          ),
                        ),
                        const SizedBox(width: 16),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                "SISTEM ANTREAN BERBASIS ANDROID TV",
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              SizedBox(height: 4),
                              Text(
                                "BY BHUTKALA PROJECT",
                                style: TextStyle(
                                  color: Colors.white70,
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              // Content Area
              Expanded(
                child: Center(
                  child: SingleChildScrollView(
                    child: Padding(
                      padding: const EdgeInsets.all(24.0),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          // Teks Keterangan
                          const Text(
                            "Silahkan Memilih Loket Antrian",
                            style: TextStyle(
                              fontSize: 28,
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 1.2,
                            ),
                          ),
                          const SizedBox(height: 50),

                          // Pilihan loket dengan card style
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              loketCard(
                                "A",
                                "Teller",
                                Icons.attach_money,
                                const Color(0xFF00BCD4), // Cyan
                              ),
                              const SizedBox(width: 20),
                              loketCard(
                                "B",
                                "Customer Service",
                                Icons.people,
                                const Color(0xFF4CAF50), // Green
                              ),
                              const SizedBox(width: 20),
                              loketCard(
                                "C",
                                "Kredit",
                                Icons.credit_card,
                                const Color(0xFFFF9800), // Orange
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  /// Widget card loket dengan desain modern
  Widget loketCard(String kode, String label, IconData icon, Color color) {
    final isSelected = selectedLoket == kode;

    return GestureDetector(
      onTap: () {
        setState(() => selectedLoket = kode);
        // Langsung ambil antrean setelah pilih
        ambilAntrean();
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        width: 220,
        height: 260,
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.3),
              blurRadius: 15,
              spreadRadius: 2,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            borderRadius: BorderRadius.circular(20),
            onTap: () {
              setState(() => selectedLoket = kode);
              ambilAntrean();
            },
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Icon
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.3),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(icon, size: 60, color: Colors.white),
                ),
                const SizedBox(height: 20),
                // Label
                Text(
                  label,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 22,
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 12),
                // Button Pilih
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 24,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.3),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child:
                      isLoading && selectedLoket == kode
                          ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              color: Colors.white,
                              strokeWidth: 2,
                            ),
                          )
                          : const Text(
                            "Pilih",
                            style: TextStyle(
                              fontSize: 16,
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
