import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

/// Low-level HTTP helper that injects the Bearer token automatically.
class ApiService {
  static const String _tokenKey = 'auth_token';

  // ─── Token helpers ───────────────────────────────────────────
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }

  static Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }

  // ─── Headers ────────────────────────────────────────────────
  static Future<Map<String, String>> _headers({bool withAuth = false}) async {
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (withAuth) {
      final token = await getToken();
      if (token != null) headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  // ─── GET ────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> get(
    String url, {
    bool withAuth = false,
  }) async {
    try {
      final response = await http.get(
        Uri.parse(url),
        headers: await _headers(withAuth: withAuth),
      );
      return _processResponse(response);
    } catch (e) {
      return {'success': false, 'message': 'Connection error: $e'};
    }
  }

  // ─── POST (JSON) ───────────────────────────────────────────
  static Future<Map<String, dynamic>> post(
    String url,
    Map<String, dynamic> body, {
    bool withAuth = false,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(url),
        headers: await _headers(withAuth: withAuth),
        body: jsonEncode(body),
      );
      return _processResponse(response);
    } catch (e) {
      return {'success': false, 'message': 'Connection error: $e'};
    }
  }

  // ─── POST (Multipart) ──────────────────────────────────────
  static Future<Map<String, dynamic>> postMultipart(
    String url, {
    required Map<String, String> fields,
    Map<String, File>? files,
    Map<String, List<File>>? fileArrays,
    bool withAuth = false,
  }) async {
    try {
      final request = http.MultipartRequest('POST', Uri.parse(url));

      // Auth header
      if (withAuth) {
        final token = await getToken();
        if (token != null) request.headers['Authorization'] = 'Bearer $token';
      }
      request.headers['Accept'] = 'application/json';

      // Fields
      request.fields.addAll(fields);

      // Single files
      if (files != null) {
        for (final entry in files.entries) {
          request.files.add(
            await http.MultipartFile.fromPath(entry.key, entry.value.path),
          );
        }
      }

      // File arrays
      if (fileArrays != null) {
        for (final entry in fileArrays.entries) {
          for (final file in entry.value) {
            request.files.add(
              await http.MultipartFile.fromPath(entry.key, file.path),
            );
          }
        }
      }

      final streamed = await request.send();
      final response = await http.Response.fromStream(streamed);
      return _processResponse(response);
    } catch (e) {
      return {'success': false, 'message': 'Connection error: $e'};
    }
  }

  // ─── PUT (JSON) ────────────────────────────────────────────
  static Future<Map<String, dynamic>> put(
    String url,
    Map<String, dynamic> body, {
    bool withAuth = false,
  }) async {
    try {
      final response = await http.put(
        Uri.parse(url),
        headers: await _headers(withAuth: withAuth),
        body: jsonEncode(body),
      );
      return _processResponse(response);
    } catch (e) {
      return {'success': false, 'message': 'Connection error: $e'};
    }
  }

  // ─── Response processor ────────────────────────────────────
  static Map<String, dynamic> _processResponse(http.Response response) {
    try {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      body['statusCode'] = response.statusCode;
      // Auto-clear token on 401 — Sanctum token expired or revoked
      if (response.statusCode == 401) {
        clearToken();
        body['success'] = false;
        body['unauthorized'] = true;
      }
      return body;
    } catch (_) {
      return {
        'success': false,
        'message': 'Unexpected server response',
        'statusCode': response.statusCode,
      };
    }
  }
}
