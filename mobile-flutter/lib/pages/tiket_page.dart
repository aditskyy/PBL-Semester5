import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class TiketPage extends StatelessWidget {
  final String nomor;     // Sesuai nama kolom di DB Anda
  final String namaLoket; // Nama tampilan (Teller/CS/Kredit)

  const TiketPage({
    Key? key,
    required this.nomor,
    required this.namaLoket,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final waktu = DateFormat('dd MMM yyyy, HH:mm').format(DateTime.now());

    return Scaffold(
      backgroundColor: Colors.grey[200],
      appBar: AppBar(title: const Text("Tiket Antrean"), backgroundColor: Colors.blueAccent),
      body: Center(
        child: Card(
          elevation: 5,
          child: Container(
            width: 300,
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text("BHUTKALA PROJECT", style: TextStyle(fontWeight: FontWeight.bold)),
                const Divider(),
                const Text("Nomor Antrean Anda:"),
                Text(nomor, style: const TextStyle(fontSize: 60, fontWeight: FontWeight.bold)),
                Text(namaLoket, style: const TextStyle(fontSize: 20, color: Colors.blue)),
                const Divider(),
                Text(waktu),
                const SizedBox(height: 20),
                ElevatedButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text("KEMBALI"),
                )
              ],
            ),
          ),
        ),
      ),
    );
  }
}