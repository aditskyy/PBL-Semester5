import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class TiketPage extends StatelessWidget {
  final String nomorAntrean;
  final String kodeJenis;

  const TiketPage({
    Key? key,
    required this.nomorAntrean,
    required this.kodeJenis,
  }) : super(key: key);

  String getNamaLoket() {
    switch (kodeJenis) {
      case 'A':
        return "TELLER";
      case 'B':
        return "CUSTOMER SERVICE";
      case 'C':
        return "KREDIT";
      default:
        return "LOKET TIDAK DIKETAHUI";
    }
  }

  @override
  Widget build(BuildContext context) {
    final waktu = DateFormat('dd MMM yyyy, HH:mm').format(DateTime.now());

    return Scaffold(
      backgroundColor: Colors.grey[200],
      appBar: AppBar(backgroundColor: Colors.blueAccent, centerTitle: true),
      body: Center(
        child: Card(
          elevation: 5,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
          child: Container(
            width: 280,
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text(
                  "BHUTKALA PROJRCT",
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                ),
                const Divider(thickness: 1),
                const SizedBox(height: 10),
                const Text("Nomor Antrean Anda"),
                const SizedBox(height: 5),
                Text(
                  nomorAntrean,
                  style: const TextStyle(
                    fontSize: 48,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 10),
                Text(getNamaLoket(), style: const TextStyle(fontSize: 18)),
                const Divider(thickness: 1),
                Text("Waktu Ambil: $waktu"),
                const SizedBox(height: 20),
                ElevatedButton.icon(
                  icon: const Icon(Icons.print),
                  label: const Text("Cetak Tiket"),
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text(
                          "Cetak tiket masih dalam pengembangan 🔖",
                        ),
                      ),
                    );
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
