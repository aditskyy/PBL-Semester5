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
  String? selectedNamaLoket; // Tambahan: Untuk menyimpan nama asli loket (misal: "Teller-01")
  bool isLoading = false;
  
  late Future<List<dynamic>> _loketFuture;

  @override
  void initState() {
    super.initState();
    _loketFuture = ApiService.getDaftarLoket();
  }

  Future<void> ambilAntrean() async {
    if (selectedLoket == null) {
      _showSnackBar("Pilih loket terlebih dahulu");
      return;
    }

    setState(() => isLoading = true);

    try {
      final response = await ApiService.ambilAntrean(selectedLoket!);

      if (response['success'] == true) {
        if (!mounted) return;

        // NAVIGASI DENGAN PARAMETER YANG SUDAH DISESUAIKAN
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => TiketPage(
              nomor: response['nomor'].toString(), // Sesuai kolom 'nomor' di database
              namaLoket: selectedNamaLoket ?? "LOKET", // Nama asli dari tabel loket
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
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        backgroundColor: Colors.blueAccent,
        title: const Text("Ambil Nomor Antrean"),
        centerTitle: true,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          children: [
            const Icon(Icons.touch_app, size: 80, color: Colors.blueAccent),
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
                  return const Text("Gagal memuat daftar loket. Cek koneksi server.");
                }

                return Column(
                  children: [
                    Wrap(
                      spacing: 10,
                      runSpacing: 10,
                      alignment: WrapAlignment.center,
                      children: snapshot.data!.map((loket) {
                        final kode = loket['kode_jenis'];
                        final nama = loket['nama_loket']; // "Teller-01", "CS-01", dsb
                        final isSelected = selectedLoket == kode;

                        return ChoiceChip(
                          label: Text(nama),
                          selected: isSelected,
                          selectedColor: Colors.blueAccent,
                          labelStyle: TextStyle(
                            color: isSelected ? Colors.white : Colors.black,
                            fontWeight: FontWeight.bold,
                          ),
                          onSelected: (bool selected) {
                            setState(() {
                              selectedLoket = selected ? kode : null;
                              selectedNamaLoket = selected ? nama : null; // Simpan nama aslinya
                            });
                          },
                        );
                      }).toList(),
                    ),
                    const SizedBox(height: 40),
                    
                    SizedBox(
                      width: double.infinity,
                      height: 55,
                      child: ElevatedButton.icon(
                        onPressed: isLoading ? null : ambilAntrean,
                        icon: isLoading 
                          ? const SizedBox() 
                          : const Icon(Icons.confirmation_number, color: Colors.white),
                        label: isLoading
                            ? const CircularProgressIndicator(color: Colors.white)
                            : const Text("AMBIL NOMOR ANTREAN", 
                                style: TextStyle(fontSize: 16, color: Colors.white, fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.blueAccent,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(15),
                          ),
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