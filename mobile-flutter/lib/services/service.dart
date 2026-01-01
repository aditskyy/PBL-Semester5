import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  // GANTI IP INI: Gunakan IPv4 Laptop Anda (cek via 'ipconfig' di CMD)
  // Jangan gunakan 'localhost' jika menjalankan aplikasi di HP fisik/emulator
  static const String baseUrl = 'http://localhost:5000/api'; 

  /// 1. FUNGSI UNTUK MENGAMBIL DAFTAR LOKET (GET)
  /// Digunakan oleh FutureBuilder di PilihLoketPage
  static Future<List<dynamic>> getDaftarLoket() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/get-loket'));
      
      if (response.statusCode == 200) {
        // Mengembalikan list berisi data dari tabel 'loket'
        return jsonDecode(response.body);
      } else {
        print("Server Error: ${response.statusCode}");
        return [];
      }
    } catch (e) {
      print("Kesalahan Koneksi getDaftarLoket: $e");
      return [];
    }
  }

  /// 2. FUNGSI UNTUK MENGAMBIL NOMOR ANTREAN BARU (POST)
  /// Menyesuaikan dengan kolom 'nomor' dan 'kode_jenis' di tabel 'antrian'
  static Future<Map<String, dynamic>> ambilAntrean(String kode) async {
    final url = Uri.parse('$baseUrl/ambil-antrean');
    
    try {
      final response = await http.post(
        url,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'kode_loket': kode}),
      );

      if (response.statusCode == 200) {
        // Response harus berisi {'success': true, 'nomor': x, ...}
        return jsonDecode(response.body);
      } else {
        return {
          'success': false, 
          'message': 'Gagal mengambil antrean (Status: ${response.statusCode})'
        };
      }
    } catch (e) {
      return {
        'success': false, 
        'message': 'Tidak dapat terhubung ke server: $e'
      };
    }
  }
}