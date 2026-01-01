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

  IconData _getIconData(String? iconName) {
    switch (iconName) {
      case 'attach_money': return Icons.attach_money;
      case 'people': return Icons.people;
      case 'credit_card': return Icons.credit_card;
      case 'account_balance': return Icons.account_balance;
      default: return Icons.confirmation_number;
    }
  }

  /// Fungsi untuk ambil antrean - Disesuaikan dengan struktur DB Anda
  Future<void> ambilAntrean(String kode, String namaAsliDariDb) async {
    setState(() {
      selectedLoket = kode;
      isLoading = true;
    });

    try {
      final response = await ApiService.ambilAntrean(kode);

      if (response['success'] == true) {
        if (!mounted) return;
        
        // BERPINDAH KE TIKET PAGE
        // Parameter disesuaikan: 'nomor' dan 'namaLoket'
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => TiketPage(
              nomor: response['nomor'].toString(), // Dari kolom 'nomor' di database
              namaLoket: namaAsliDariDb,          // Dari kolom 'nama_loket' di database
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
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
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
                              if (snapshot.connectionState == ConnectionState.waiting) {
                                return const CircularProgressIndicator(color: Colors.white);
                              } else if (snapshot.hasError) {
                                return Text("Error: ${snapshot.error}", style: const TextStyle(color: Colors.white));
                              } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
                                return const Text("Tidak ada loket tersedia", style: TextStyle(color: Colors.white));
                              }

                              return Wrap(
                                spacing: 20,
                                runSpacing: 20,
                                alignment: WrapAlignment.center,
                                children: snapshot.data!.map((data) {
                                  return loketCard(
                                    data['kode_loket'],
                                    data['nama_loket'], // Mengambil nama asli dari DB (Teller-01, dll)
                                    _getIconData(data['icon']),
                                    // Handle warna jika format dari DB adalah string hex
                                    Color(int.parse(data['warna'].replaceAll('#', '0xFF'))), 
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
              Text("SISTEM ANTREAN DIGITAL",
                  style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
              Text("BY BHUTKALA PROJECT", style: TextStyle(color: Colors.white70, fontSize: 12)),
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
              Text(label,
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 20, color: Colors.white, fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.3),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: isThisLoading
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text("PILIH", style: TextStyle(fontSize: 16, color: Colors.white, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}