import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  // ✅ GANTI DENGAN IP LAPTOP ANDA!
  // Cara cek IP:
  // - Windows: ketik "ipconfig" di CMD
  // - Mac/Linux: ketik "ifconfig" di Terminal
  static const String baseUrl = 'http://localhost:5000/api';

  /// ===============================
  /// GET DAFTAR LOKET
  /// ===============================
  static Future<List<dynamic>> getDaftarLoket() async {
    final url = Uri.parse('$baseUrl/get-loket');

    print('🔍 Requesting: $url');

    try {
      final response = await http
          .get(url)
          .timeout(
            const Duration(seconds: 10),
            onTimeout: () {
              throw Exception('Timeout: Server tidak merespons');
            },
          );

      print('📥 Status Code: ${response.statusCode}');
      print('📥 Response Body: ${response.body}');

      if (response.statusCode == 200) {
        // Cek apakah response kosong
        if (response.body.isEmpty) {
          print('❌ Response body kosong');
          return [];
        }

        dynamic data;
        try {
          data = jsonDecode(response.body);
        } catch (e) {
          print('❌ Error parsing JSON: $e');
          print('❌ Raw response: ${response.body}');
          return [];
        }

        // Validasi data
        if (data is List) {
          print('✅ Data loket berhasil dimuat: ${data.length} loket');

          // Pastikan setiap item adalah Map
          final validData =
              data.where((item) {
                if (item is! Map<String, dynamic>) {
                  print('⚠️ Item bukan Map: $item');
                  return false;
                }

                // Cek field wajib
                final hasKode =
                    item.containsKey('kode_jenis') ||
                    item.containsKey('kode_loket') ||
                    item.containsKey('kode');

                if (!hasKode) {
                  print('⚠️ Item tidak punya kode: $item');
                }

                return hasKode;
              }).toList();

          print('✅ Valid data: ${validData.length} dari ${data.length}');
          return validData;
        } else if (data is Map && data.containsKey('data')) {
          // Jika response dalam format {data: [...]}
          print('📦 Data dalam wrapper object');
          return data['data'] is List ? data['data'] : [];
        } else {
          print('❌ Format data bukan List: ${data.runtimeType}');
          print('❌ Data: $data');
          return [];
        }
      } else if (response.statusCode == 404) {
        print('❌ Endpoint tidak ditemukan (404)');
        return [];
      } else {
        print('❌ Server error: ${response.statusCode}');
        print('❌ Response: ${response.body}');
        return [];
      }
    } on http.ClientException catch (e) {
      print('❌ Connection error: $e');
      print('⚠️ Pastikan:');
      print('   1. Backend Flask sudah running');
      print('   2. IP address sudah benar: $baseUrl');
      print('   3. Firewall tidak memblokir port 5000');
      return [];
    } catch (e, stackTrace) {
      print('❌ Unexpected error: $e');
      print('❌ Stack trace: $stackTrace');
      return [];
    }
  }

  /// ===============================
  /// AMBIL ANTREAN
  /// ===============================
  static Future<Map<String, dynamic>> ambilAntrean(String kodeJenis) async {
    final url = Uri.parse('$baseUrl/ambil-antrean');

    print('🔍 Ambil Antrean - Kode Jenis: $kodeJenis');

    try {
      final response = await http
          .post(
            url,
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode({'kode_jenis': kodeJenis}),
          )
          .timeout(
            const Duration(seconds: 10),
            onTimeout: () {
              throw Exception('Timeout: Server tidak merespons');
            },
          );

      print('📥 Status: ${response.statusCode}');
      print('📥 Response: ${response.body}');

      if (response.body.isEmpty) {
        return {'success': false, 'message': 'Response kosong dari server'};
      }

      dynamic data;
      try {
        data = jsonDecode(response.body);
      } catch (e) {
        return {'success': false, 'message': 'Error parsing response: $e'};
      }

      if (response.statusCode == 200 &&
          data is Map &&
          data['success'] == true) {
        print('✅ Antrean berhasil diambil: ${data['nomor']}');
        return Map<String, dynamic>.from(data);
      } else {
        print(
          '❌ Gagal ambil antrean: ${data is Map ? data['message'] : 'Unknown error'}',
        );
        return {
          'success': false,
          'message':
              data is Map && data.containsKey('message')
                  ? data['message']
                  : 'Gagal mengambil antrean',
        };
      }
    } on http.ClientException catch (e) {
      print('❌ Connection error: $e');
      return {'success': false, 'message': 'Tidak dapat terhubung ke server'};
    } catch (e) {
      print('❌ ambilAntrean error: $e');
      return {'success': false, 'message': 'Koneksi ke server gagal: $e'};
    }
  }
}
