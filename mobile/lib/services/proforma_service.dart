import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/proforma.dart';
import 'api_service.dart';

class ProformaService {
  /// Upload a single image to the temp endpoint.
  /// Returns the folder name string on success, null on failure.
  static Future<String?> uploadTempImage(String filePath) async {
    try {
      final uri = Uri.parse(ApiConfig.uploadTemp);
      final request = http.MultipartRequest('POST', uri);
      final token = await ApiService.getToken();
      if (token != null) request.headers['Authorization'] = 'Bearer $token';
      request.headers['Accept'] = 'application/json';
      request.files.add(await http.MultipartFile.fromPath('file', filePath));
      final streamed = await request.send();
      final response = await http.Response.fromStream(streamed);
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      if (body['success'] == true && body['folders'] is List) {
        final folders = body['folders'] as List;
        if (folders.isNotEmpty) return folders.first.toString();
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  /// Create a proforma via the role-based endpoint.
  /// Call [uploadTempImage] for each photo BEFORE calling this method,
  /// and store the returned folder names in [ProformaPart.tempPhotoPaths].
  static Future<Map<String, dynamic>> createProforma(ProformaRequest req, String userRole) async {
    try {
      final uri = Uri.parse(ApiConfig.createProformaUrl(userRole));
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
        request.fields['parts[$i][condition]'] = p.condition;
        request.fields['parts[$i][name]'] = p.name.isNotEmpty ? p.name : p.number;
        request.fields['parts[$i][number]'] = p.number.isNotEmpty ? p.number : p.name;
        request.fields['parts[$i][grade]'] = p.grade;
        request.fields['parts[$i][country]'] = p.country;
        request.fields['parts[$i][quantity]'] = p.quantity.toString();
        request.fields['parts[$i][component]'] = p.component;

        // Photos — send pre-uploaded temp folder names as strings
        for (int j = 0; j < p.tempPhotoPaths.length; j++) {
          if (p.tempPhotoPaths[j].isNotEmpty) {
            request.fields['parts[$i][photo_paths][$j]'] = p.tempPhotoPaths[j];
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
