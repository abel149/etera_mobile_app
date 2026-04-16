import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/proforma.dart';
import 'api_service.dart';

class ProformaService {
  /// Create a proforma via POST /api/create-file (multipart).
  static Future<Map<String, dynamic>> createProforma(ProformaRequest req) async {
    try {
      final uri = Uri.parse(ApiConfig.createProforma);
      final request = http.MultipartRequest('POST', uri);

      // Auth
      final token = await ApiService.getToken();
      if (token != null) request.headers['Authorization'] = 'Bearer $token';
      request.headers['Accept'] = 'application/json';

      // ─── Scalar fields ───
      request.fields['number_of_proformas'] = req.numberOfProformas.toString();
      if (req.isEteraChereta && req.eteraCheretaHours != null) {
        request.fields['etera_chereta_hours'] = req.eteraCheretaHours.toString();
      }
      request.fields['brand_id'] = req.brandId.toString();
      request.fields['car_type'] = req.carType;
      request.fields['model'] = req.model;
      request.fields['year'] = req.year;
      request.fields['customer_phone_number'] = req.customerPhoneNumber;
      request.fields['license_plate_number'] = req.licensePlateNumber;
      if (req.chassisNumber != null && req.chassisNumber!.isNotEmpty) {
        request.fields['chassis_number'] = req.chassisNumber!;
      }

      // ─── Parts arrays ───
      for (int i = 0; i < req.parts.length; i++) {
        final p = req.parts[i];
        request.fields['parts[condition][$i]'] = p.condition;
        request.fields['parts[number][$i]'] = p.number;
        request.fields['parts[grade][$i]'] = p.grade;
        request.fields['parts[country][$i]'] = p.country;
        request.fields['parts[quantity][$i]'] = p.quantity.toString();
        request.fields['parts[component][$i]'] = p.component;

        // Part images
        for (int j = 0; j < p.photoPaths.length; j++) {
          final file = File(p.photoPaths[j]);
          if (await file.exists()) {
            request.files.add(
              await http.MultipartFile.fromPath('parts[photo][$i][$j]', file.path),
            );
          }
        }
      }

      // ─── Voice note (base64) ───
      if (req.voiceNotePath != null && req.voiceNotePath!.isNotEmpty) {
        final voiceFile = File(req.voiceNotePath!);
        if (await voiceFile.exists()) {
          final bytes = await voiceFile.readAsBytes();
          final b64 = 'data:audio/webm;base64,${base64Encode(bytes)}';
          request.fields['voice_note'] = b64;
        }
      }

      final streamed = await request.send();
      final response = await http.Response.fromStream(streamed);
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      body['statusCode'] = response.statusCode;
      return body;
    } catch (e) {
      return {'success': false, 'message': 'Connection error: $e'};
    }
  }
}
