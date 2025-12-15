import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl =
      'http://192.168.1.19:5000'; // GANTI IP SESUAI SERVERMU

  static Future<Map<String, dynamic>> ambilAntrean([
    String kodeJenis = "A",
  ]) async {
    final url = Uri.parse('$baseUrl/ambil-antrean');
    final response = await http.post(
      url,
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'kode_jenis': kodeJenis}),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      return {'success': false, 'message': 'Gagal ambil antrean'};
    }
  }
}
