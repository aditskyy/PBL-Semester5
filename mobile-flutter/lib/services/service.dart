import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl =
      'http://localhost:5000/api'; // GANTI IP SESUAI SERVERMU

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
