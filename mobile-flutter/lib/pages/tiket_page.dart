import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class TiketPage extends StatelessWidget {
  final String nomor;     // Sesuai dengan response['nomor'] dari Flask
  final String namaLoket; // Sesuai dengan selectedNamaLoket

  const TiketPage({
    Key? key,
    required this.nomor,
    required this.namaLoket,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final waktu = DateFormat('dd MMM yyyy, HH:mm').format(DateTime.now());

    return Scaffold(
      backgroundColor: Colors.blueAccent, // Latar belakang kontras
      appBar: AppBar(
        title: const Text("Tiket Antrean", style: TextStyle(color: Colors.white)),
        backgroundColor: Colors.transparent,
        elevation: 0,
        centerTitle: true,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: Center(
        child: SingleChildScrollView(
          child: Column(
            children: [
              // --- TAMPILAN STRUK TIKET ---
              Container(
                width: 320,
                padding: const EdgeInsets.symmetric(vertical: 30, horizontal: 20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.2),
                      blurRadius: 15,
                      offset: const Offset(0, 8),
                    )
                  ],
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Text(
                      "BHUTKALA PROJECT",
                      style: TextStyle(fontWeight: FontWeight.bold, letterSpacing: 1.5, fontSize: 16),
                    ),
                    const SizedBox(height: 5),
                    const Text(
                      "Sistem Antrean Digital",
                      style: TextStyle(fontSize: 12, color: Colors.grey),
                    ),
                    const SizedBox(height: 20),
                    
                    // Garis putus-putus (Dashed Line)
                    Row(
                      children: List.generate(15, (index) => Expanded(
                        child: Container(
                          color: index % 2 == 0 ? Colors.transparent : Colors.grey.shade300,
                          height: 2,
                        ),
                      )),
                    ),
                    
                    const SizedBox(height: 25),
                    const Text("NOMOR ANTREAN ANDA", style: TextStyle(color: Colors.black54)),
                    const SizedBox(height: 10),
                    
                    // NOMOR ANTREAN (Misal: A001)
                    Text(
                      nomor,
                      style: const TextStyle(
                        fontSize: 70, 
                        fontWeight: FontWeight.w900,
                        color: Colors.blueAccent,
                        letterSpacing: 2,
                      ),
                    ),
                    
                    const SizedBox(height: 10),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 5),
                      decoration: BoxDecoration(
                        color: Colors.blueAccent.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        namaLoket.toUpperCase(),
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.blueAccent),
                      ),
                    ),
                    
                    const SizedBox(height: 30),
                    const Icon(Icons.qr_code_2, size: 80, color: Colors.black87),
                    const SizedBox(height: 20),
                    
                    const Divider(),
                    const SizedBox(height: 10),
                    Text(
                      waktu,
                      style: const TextStyle(fontSize: 14, color: Colors.grey, fontStyle: FontStyle.italic),
                    ),
                    const SizedBox(height: 10),
                    const Text(
                      "Simpan tiket ini untuk verifikasi.",
                      style: TextStyle(fontSize: 11, color: Colors.redAccent),
                    ),
                  ],
                ),
              ),
              
              const SizedBox(height: 40),
              
              // TOMBOL KEMBALI
              SizedBox(
                width: 200,
                height: 50,
                child: ElevatedButton.icon(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.arrow_back, color: Colors.blueAccent),
                  label: const Text("KEMBALI", style: TextStyle(fontWeight: FontWeight.bold, color: Colors.blueAccent)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                    elevation: 0,
                  ),
                ),
              ),
              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }
}