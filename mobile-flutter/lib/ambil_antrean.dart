import 'package:flutter/material.dart';
import 'package:user_ambil/services/service.dart';

class AmbilAntreanPage extends StatefulWidget {
  const AmbilAntreanPage({Key? key}) : super(key: key);

  @override
  State<AmbilAntreanPage> createState() => _AmbilAntreanPageState();
}

class _AmbilAntreanPageState extends State<AmbilAntreanPage> {
  String? selectedLoket;
  String? nomorAntrean;
  bool isLoading = false;

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
      setState(() {
        nomorAntrean = response['nomor_antrean'];
      });
    } catch (e) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text("Gagal mengambil antrean: $e")));
    } finally {
      setState(() => isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        backgroundColor: Colors.blueAccent,
        title: const Text("Ambil Nomor Antrean"),
        centerTitle: true,
      ),
      body: Padding(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          children: [
            const Text(
              "Pilih Loket Anda:",
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 20),
            ToggleButtons(
              borderRadius: BorderRadius.circular(10),
              isSelected: [
                selectedLoket == 'A',
                selectedLoket == 'B',
                selectedLoket == 'C',
              ],
              onPressed: (index) {
                setState(() {
                  if (index == 0) selectedLoket = 'A';
                  if (index == 1) selectedLoket = 'B';
                  if (index == 2) selectedLoket = 'C';
                });
              },
              children: const [
                Padding(
                  padding: EdgeInsets.symmetric(horizontal: 20),
                  child: Text("TELLER"),
                ),
                Padding(
                  padding: EdgeInsets.symmetric(horizontal: 20),
                  child: Text("CS"),
                ),
                Padding(
                  padding: EdgeInsets.symmetric(horizontal: 20),
                  child: Text("KREDIT"),
                ),
              ],
            ),
            const SizedBox(height: 30),
            ElevatedButton.icon(
              onPressed: isLoading ? null : ambilAntrean,
              icon: const Icon(Icons.confirmation_number),
              label:
                  isLoading
                      ? const CircularProgressIndicator(color: Colors.white)
                      : const Text("Ambil Nomor"),
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(
                  horizontal: 40,
                  vertical: 15,
                ),
                backgroundColor: Colors.blueAccent,
                textStyle: const TextStyle(fontSize: 18),
              ),
            ),
            const SizedBox(height: 40),
            if (nomorAntrean != null)
              Card(
                elevation: 5,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Container(
                  padding: const EdgeInsets.all(20),
                  width: 250,
                  child: Column(
                    children: [
                      const Text(
                        "Nomor Antrean Anda",
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 10),
                      Text(
                        nomorAntrean!,
                        style: const TextStyle(
                          fontSize: 48,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const Divider(),
                      Text(
                        selectedLoket == 'A'
                            ? "Loket Teller"
                            : selectedLoket == 'B'
                            ? "Loket Customer Service"
                            : "Loket Kredit",
                        style: const TextStyle(fontSize: 16),
                      ),
                      const SizedBox(height: 10),
                      ElevatedButton.icon(
                        icon: const Icon(Icons.print),
                        label: const Text("Cetak Tiket"),
                        onPressed: () {
                          // TODO: implementasi print kecil
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text("Fitur cetak segera aktif 🔖"),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
