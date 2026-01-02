import 'package:flutter/material.dart';
import 'package:user_ambil/services/service.dart';
import 'package:user_ambil/pages/tiket_page.dart';

class AmbilAntreanPage extends StatefulWidget {
  const AmbilAntreanPage({Key? key}) : super(key: key);

  @override
  State<AmbilAntreanPage> createState() => _AmbilAntreanPageState();
}

class _AmbilAntreanPageState extends State<AmbilAntreanPage> {
  String? selectedLoket; // Menyimpan kode_loket (misal: A-01)
  String? selectedNamaLoket; // Menyimpan nama asli loket (misal: Teller-01)
  bool isLoading = false;
  
  late Future<List<dynamic>> _loketFuture;

  @override
  void initState() {
    super.initState();
    _loketFuture = ApiService.getDaftarLoket();
  }

  // Fungsi Penerjemah String ke IconData
IconData _getIconData(String? iconName) {
  // Kita hanya fokus pada data di kolom 'icon' dari database
  final name = iconName?.trim().toLowerCase() ?? '';

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
      // Ikon default jika data di DB tidak cocok/kosong
      return Icons.confirmation_number;
  }
}

  Future<void> ambilAntrean() async {
    if (selectedLoket == null) {
      _showSnackBar("Pilih layanan terlebih dahulu");
      return;
    }

    setState(() => isLoading = true);

    try {
      final response = await ApiService.ambilAntrean(selectedLoket!);

      if (response['success'] == true) {
        if (!mounted) return;

        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => TiketPage(
              nomor: response['nomor'].toString(),
              namaLoket: selectedNamaLoket ?? "LOKET",
            ),
          ),
        );
      } else {
        _showSnackBar(response['message'] ?? "Gagal mengambil antrean");
      }
    } catch (e) {
      _showSnackBar("Terjadi kesalahan koneksi: $e");
    } finally {
      if (mounted) setState(() => isLoading = false);
    }
  }

  void _showSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.blueAccent,
        title: const Text("Ambil Nomor Antrean", style: TextStyle(fontWeight: FontWeight.bold)),
        centerTitle: true,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          children: [
            const SizedBox(height: 10),
            const Icon(Icons.touch_app, size: 60, color: Colors.blueAccent),
            const SizedBox(height: 10),
            const Text(
              "Silahkan Pilih Layanan",
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 30),

            FutureBuilder<List<dynamic>>(
              future: _loketFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: CircularProgressIndicator());
                } else if (snapshot.hasError || !snapshot.hasData || snapshot.data!.isEmpty) {
                  return const Text("Gagal memuat daftar loket. Cek server Flask.");
                }

                return Column(
                  children: [
                    // --- GRID LAYOUT UNTUK LOKET ---
                    Wrap(
                      spacing: 15,
                      runSpacing: 15,
                      alignment: WrapAlignment.center,
                      children: snapshot.data!.map((loket) {
                        final kode = loket['kode_loket']; // Key dari DB
                        final nama = loket['nama_loket'];
                        final iconDb = loket['icon']?.toString().trim() ?? '';
                        final warnaDb = loket['warna'] ?? "#1976D2"; 
                        
                        final isSelected = selectedLoket == kode;
                        final Color themeColor = Color(int.parse(warnaDb.replaceAll('#', '0xFF')));
                      

                        // TAMBAHKAN PRINT INI:
                        print("LOG: Data Ikon adalah '$iconDb'");
                  
                        return GestureDetector(
                          onTap: () {
                            setState(() {
                              selectedLoket = kode;
                              selectedNamaLoket = nama;
                            });
                          },
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 200),
                            width: MediaQuery.of(context).size.width * 0.4,
                            padding: const EdgeInsets.all(15),
                            decoration: BoxDecoration(
                              color: isSelected ? themeColor : Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(
                                color: isSelected ? themeColor : Colors.grey.shade300,
                                width: 2,
                              ),
                              boxShadow: isSelected ? [
                                BoxShadow(color: themeColor.withOpacity(0.3), blurRadius: 8, offset: const Offset(0, 4))
                              ] : [],
                            ),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  _getIconData(iconDb),
                                  size: 45,
                                  color: isSelected ? Colors.white : themeColor,
                                ),
                                const SizedBox(height: 12),
                                Text(
                                  nama,
                                  textAlign: TextAlign.center,
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    color: isSelected ? Colors.white : Colors.black87,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                    
                    const SizedBox(height: 50),
                    
                    // --- TOMBOL KONFIRMASI ---
                    SizedBox(
                      width: double.infinity,
                      height: 55,
                      child: ElevatedButton(
                        onPressed: isLoading ? null : ambilAntrean,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.blueAccent,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
                          elevation: 5,
                        ),
                        child: isLoading
                            ? const CircularProgressIndicator(color: Colors.white)
                            : const Text(
                                "KONFIRMASI AMBIL ANTREAN", 
                                style: TextStyle(fontSize: 16, color: Colors.white, fontWeight: FontWeight.bold)
                              ),
                      ),
                    ),
                  ],
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}