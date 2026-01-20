import 'package:flutter/material.dart';
import 'package:user_ambil/services/service.dart';
import 'package:user_ambil/pages/tiket_page.dart';

class AmbilAntreanPage extends StatefulWidget {
  const AmbilAntreanPage({Key? key}) : super(key: key);

  @override
  State<AmbilAntreanPage> createState() => _AmbilAntreanPageState();
}

class _AmbilAntreanPageState extends State<AmbilAntreanPage> {
  String? selectedLoket;
  String? selectedNamaLoket;
  bool isLoading = false;

  late Future<List<dynamic>> _loketFuture;

  @override
  void initState() {
    super.initState();
    _loketFuture = ApiService.getDaftarLoket();
  }

  // ================= ICON SAFE =================
  IconData _getIconData(String? iconName) {
    if (iconName == null) return Icons.confirmation_number;

    final name = iconName.trim().toLowerCase();

    switch (name) {
      case 'account_balance':
        return Icons.account_balance;
      case 'credit_card':
        return Icons.credit_card;
      case 'people':
        return Icons.people;
      case 'person':
        return Icons.person;
      case 'support_agent':
        return Icons.support_agent;
      default:
        return Icons.confirmation_number;
    }
  }

  // ================= COLOR SAFE =================
  Color _parseColor(String? hex) {
    if (hex == null || hex.isEmpty) return Colors.blueAccent;

    try {
      // Hapus spasi dan lowercase
      final cleaned = hex.trim().toLowerCase();

      // Format #RRGGBB atau RRGGBB
      String hexColor =
          cleaned.startsWith('#') ? cleaned.substring(1) : cleaned;

      // Pastikan 6 karakter
      if (hexColor.length == 6) {
        return Color(int.parse('0xFF$hexColor'));
      }

      return Colors.blueAccent;
    } catch (e) {
      print('❌ Error parsing color: $hex - $e');
      return Colors.blueAccent;
    }
  }

  // ================= AMBIL ANTREAN =================
  Future<void> ambilAntrean() async {
    if (selectedLoket == null || selectedLoket!.isEmpty) {
      _showSnackBar("Pilih layanan terlebih dahulu");
      return;
    }

    setState(() => isLoading = true);

    try {
      final response = await ApiService.ambilAntrean(selectedLoket!);

      if (response['success'] == true && mounted) {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder:
                (_) => TiketPage(
                  nomor: response['nomor']?.toString() ?? '-',
                  namaLoket: selectedNamaLoket ?? 'LOKET',
                ),
          ),
        );
      } else {
        _showSnackBar(response['message'] ?? 'Gagal mengambil antrean');
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

  // ================= UI =================
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Ambil Nomor Antrean"),
        centerTitle: true,
        backgroundColor: Colors.blueAccent,
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            const Icon(Icons.touch_app, size: 60, color: Colors.blueAccent),
            const SizedBox(height: 10),
            const Text(
              "Silahkan Pilih Layanan",
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 30),

            // ================= FUTURE BUILDER =================
            Expanded(
              child: FutureBuilder<List<dynamic>>(
                future: _loketFuture,
                builder: (context, snapshot) {
                  // Loading
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator());
                  }

                  // Error
                  if (snapshot.hasError) {
                    return Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(
                            Icons.error_outline,
                            size: 60,
                            color: Colors.red,
                          ),
                          const SizedBox(height: 10),
                          Text(
                            "Error: ${snapshot.error}",
                            textAlign: TextAlign.center,
                            style: const TextStyle(color: Colors.red),
                          ),
                          const SizedBox(height: 20),
                          ElevatedButton(
                            onPressed: () {
                              setState(() {
                                _loketFuture = ApiService.getDaftarLoket();
                              });
                            },
                            child: const Text("Coba Lagi"),
                          ),
                        ],
                      ),
                    );
                  }

                  // No Data
                  if (!snapshot.hasData || snapshot.data!.isEmpty) {
                    return const Center(
                      child: Text("Tidak ada loket tersedia"),
                    );
                  }

                  // 🔐 SUPER SAFE PARSING
                  final lokets = <Map<String, dynamic>>[];

                  for (var item in snapshot.data!) {
                    if (item is Map<String, dynamic>) {
                      // Debug print setiap item
                      print('📦 Loket Item: $item');
                      lokets.add(item);
                    }
                  }

                  if (lokets.isEmpty) {
                    return const Center(child: Text("Data loket tidak valid"));
                  }

                  return SingleChildScrollView(
                    child: Wrap(
                      spacing: 15,
                      runSpacing: 15,
                      alignment: WrapAlignment.center,
                      children:
                          lokets.map((loket) {
                            // 🔐 SUPER SAFE NULL HANDLING
                            String kode = '';
                            String nama = 'LOKET';
                            String? iconDb;
                            String? warnaDb;

                            try {
                              // Coba berbagai kemungkinan nama field
                              kode =
                                  (loket['kode_jenis'] ??
                                          loket['kode_loket'] ??
                                          loket['kode'] ??
                                          '')
                                      .toString();

                              nama =
                                  (loket['nama'] ??
                                          loket['nama_loket'] ??
                                          'LOKET')
                                      .toString();

                              iconDb = loket['icon']?.toString();
                              warnaDb = loket['warna']?.toString();

                              print(
                                '✅ Parsed - Kode: $kode, Nama: $nama, Icon: $iconDb, Warna: $warnaDb',
                              );
                            } catch (e) {
                              print('❌ Error parsing loket: $e');
                              print('❌ Loket data: $loket');
                            }

                            // Skip jika kode kosong
                            if (kode.isEmpty) {
                              print('⚠️ Skipping loket dengan kode kosong');
                              return const SizedBox.shrink();
                            }

                            final isSelected = selectedLoket == kode;
                            final themeColor = _parseColor(warnaDb);

                            return GestureDetector(
                              onTap: () {
                                setState(() {
                                  selectedLoket = kode;
                                  selectedNamaLoket = nama;
                                });
                                print('🎯 Selected: $kode - $nama');
                              },
                              child: AnimatedContainer(
                                duration: const Duration(milliseconds: 200),
                                width: MediaQuery.of(context).size.width * 0.42,
                                padding: const EdgeInsets.all(15),
                                decoration: BoxDecoration(
                                  color: isSelected ? themeColor : Colors.white,
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(
                                    color:
                                        isSelected
                                            ? themeColor
                                            : Colors.grey.shade300,
                                    width: 2,
                                  ),
                                  boxShadow:
                                      isSelected
                                          ? [
                                            BoxShadow(
                                              color: themeColor.withOpacity(
                                                0.3,
                                              ),
                                              blurRadius: 8,
                                              offset: const Offset(0, 4),
                                            ),
                                          ]
                                          : [],
                                ),
                                child: Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      _getIconData(iconDb),
                                      size: 45,
                                      color:
                                          isSelected
                                              ? Colors.white
                                              : themeColor,
                                    ),
                                    const SizedBox(height: 12),
                                    Text(
                                      nama,
                                      textAlign: TextAlign.center,
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        color:
                                            isSelected
                                                ? Colors.white
                                                : Colors.black,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          }).toList(),
                    ),
                  );
                },
              ),
            ),

            const SizedBox(height: 20),

            SizedBox(
              width: double.infinity,
              height: 55,
              child: ElevatedButton(
                onPressed: isLoading ? null : ambilAntrean,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.blueAccent,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(15),
                  ),
                ),
                child:
                    isLoading
                        ? const CircularProgressIndicator(color: Colors.white)
                        : const Text(
                          "KONFIRMASI AMBIL ANTREAN",
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
