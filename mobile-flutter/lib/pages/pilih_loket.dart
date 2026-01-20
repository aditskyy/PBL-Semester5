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

  late Future<List<dynamic>> _loketFuture;

  @override
  void initState() {
    super.initState();
    _loketFuture = ApiService.getDaftarLoket();
  }

  // ✅ NULL SAFE ICON PARSING
  IconData _getIconData(String? iconName) {
    if (iconName == null) return Icons.confirmation_number;

    final name = iconName.trim().toLowerCase();

    switch (name) {
      case 'attach_money':
        return Icons.attach_money;
      case 'people':
        return Icons.people;
      case 'credit_card':
        return Icons.credit_card;
      case 'account_balance':
        return Icons.account_balance;
      case 'support_agent':
        return Icons.support_agent;
      default:
        return Icons.confirmation_number;
    }
  }

  // ✅ NULL SAFE COLOR PARSING
  Color _parseColor(String? hex) {
    if (hex == null || hex.isEmpty) return const Color(0xFF1976D2);

    try {
      final cleaned = hex.trim().replaceAll('#', '');
      if (cleaned.length == 6) {
        return Color(int.parse('0xFF$cleaned'));
      }
      return const Color(0xFF1976D2);
    } catch (e) {
      print('❌ Error parsing color: $hex - $e');
      return const Color(0xFF1976D2);
    }
  }

  /// Fungsi untuk ambil antrean - Disesuaikan dengan struktur DB
  Future<void> ambilAntrean(String kodeJenis, String namaJenis) async {
    setState(() {
      selectedLoket = kodeJenis;
      isLoading = true;
    });

    try {
      final response = await ApiService.ambilAntrean(kodeJenis);

      if (response['success'] == true) {
        if (!mounted) return;

        // BERPINDAH KE TIKET PAGE
        Navigator.push(
          context,
          MaterialPageRoute(
            builder:
                (context) => TiketPage(
                  nomor: response['nomor']?.toString() ?? '-',
                  namaLoket: namaJenis,
                ),
          ),
        );
      } else {
        _showSnackBar(response['message'] ?? "Gagal mengambil antrean");
      }
    } catch (e) {
      _showSnackBar("Kesalahan koneksi: $e");
    } finally {
      if (mounted) setState(() => isLoading = false);
    }
  }

  void _showSnackBar(String message) {
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Color(0xFF1976D2), Color(0xFF2196F3)],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              _buildHeader(),
              Expanded(
                child: Center(
                  child: SingleChildScrollView(
                    child: Padding(
                      padding: const EdgeInsets.all(24.0),
                      child: Column(
                        children: [
                          const Text(
                            "Silahkan Memilih Loket Antrean",
                            style: TextStyle(
                              fontSize: 28,
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 50),

                          FutureBuilder<List<dynamic>>(
                            future: _loketFuture,
                            builder: (context, snapshot) {
                              // Loading
                              if (snapshot.connectionState ==
                                  ConnectionState.waiting) {
                                return const CircularProgressIndicator(
                                  color: Colors.white,
                                );
                              }

                              // Error
                              if (snapshot.hasError) {
                                return Column(
                                  children: [
                                    const Icon(
                                      Icons.error_outline,
                                      color: Colors.white,
                                      size: 60,
                                    ),
                                    const SizedBox(height: 16),
                                    Text(
                                      "Error: ${snapshot.error}",
                                      style: const TextStyle(
                                        color: Colors.white,
                                      ),
                                      textAlign: TextAlign.center,
                                    ),
                                    const SizedBox(height: 16),
                                    ElevatedButton(
                                      onPressed: () {
                                        setState(() {
                                          _loketFuture =
                                              ApiService.getDaftarLoket();
                                        });
                                      },
                                      child: const Text("Coba Lagi"),
                                    ),
                                  ],
                                );
                              }

                              // No Data
                              if (!snapshot.hasData || snapshot.data!.isEmpty) {
                                return const Text(
                                  "Tidak ada loket tersedia",
                                  style: TextStyle(color: Colors.white),
                                );
                              }

                              // ✅ SUPER SAFE DATA PARSING
                              final lokets = <Map<String, dynamic>>[];

                              for (var item in snapshot.data!) {
                                if (item is Map<String, dynamic>) {
                                  print('📦 Loket Item: $item');
                                  lokets.add(item);
                                }
                              }

                              if (lokets.isEmpty) {
                                return const Text(
                                  "Data loket tidak valid",
                                  style: TextStyle(color: Colors.white),
                                );
                              }

                              return Wrap(
                                spacing: 20,
                                runSpacing: 20,
                                alignment: WrapAlignment.center,
                                children:
                                    lokets.map((data) {
                                      // ✅ SESUAIKAN DENGAN RESPONSE API FLASK
                                      String kodeJenis = '';
                                      String namaJenis = 'LOKET';
                                      String? iconDb;
                                      String? warnaDb;

                                      try {
                                        // Coba berbagai kemungkinan field
                                        kodeJenis =
                                            (data['kode_jenis'] ??
                                                    data['kode_loket'] ??
                                                    data['kode'] ??
                                                    '')
                                                .toString();

                                        namaJenis =
                                            (data['nama'] ??
                                                    data['nama_loket'] ??
                                                    data['nama_jenis'] ??
                                                    'LOKET')
                                                .toString();

                                        iconDb = data['icon']?.toString();
                                        warnaDb = data['warna']?.toString();

                                        print(
                                          '✅ Parsed - Kode: $kodeJenis, Nama: $namaJenis',
                                        );
                                      } catch (e) {
                                        print('❌ Error parsing: $e');
                                        print('❌ Data: $data');
                                      }

                                      // Skip jika kode kosong
                                      if (kodeJenis.isEmpty) {
                                        return const SizedBox.shrink();
                                      }

                                      return loketCard(
                                        kodeJenis,
                                        namaJenis,
                                        _getIconData(iconDb),
                                        _parseColor(warnaDb),
                                      );
                                    }).toList(),
                              );
                            },
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

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.all(24.0),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.tv, color: Colors.white, size: 32),
          ),
          const SizedBox(width: 16),
          const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                "SISTEM ANTREAN DIGITAL",
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Text(
                "BY BHUTKALA PROJECT",
                style: TextStyle(color: Colors.white70, fontSize: 12),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget loketCard(String kode, String label, IconData icon, Color color) {
    bool isThisLoading = isLoading && selectedLoket == kode;

    return AnimatedContainer(
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
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(20),
          onTap: isThisLoading ? null : () => ambilAntrean(kode, label),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.3),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, size: 60, color: Colors.white),
              ),
              const SizedBox(height: 20),
              Text(
                label,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 20,
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 12),
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
                    isThisLoading
                        ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                        : const Text(
                          "PILIH",
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
    );
  }
}
