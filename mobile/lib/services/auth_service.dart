import 'dart:io';
import '../config/api_config.dart';
import '../models/user.dart';
import '../models/brand.dart';
import 'api_service.dart';

class AuthResult {
  final bool success;
  final String message;
  final User? user;
  final String? token;
  final String? code; // e.g. PENDING_APPROVAL
  final Map<String, dynamic>? errors;

  AuthResult({
    required this.success,
    required this.message,
    this.user,
    this.token,
    this.code,
    this.errors,
  });
}

class AuthService {
  // ─── Login ──────────────────────────────────────────────────
  static Future<AuthResult> login(String phoneNumber, String password) async {
    final res = await ApiService.post(ApiConfig.login, {
      'phone_number': phoneNumber,
      'password': password,
    });

    if (res['success'] == true) {
      final data = res['data'] as Map<String, dynamic>;
      final user = User.fromJson(data['user']);
      final token = data['token'] as String;
      await ApiService.saveToken(token);
      return AuthResult(success: true, message: res['message'], user: user, token: token);
    }

    // Pending approval
    if (res['statusCode'] == 403 && res['code'] == 'PENDING_APPROVAL') {
      final data = res['data'] as Map<String, dynamic>;
      return AuthResult(
        success: false,
        message: res['message'],
        user: User.fromJson(data['user']),
        code: 'PENDING_APPROVAL',
      );
    }

    return AuthResult(success: false, message: res['message'] ?? 'Login failed');
  }

  // ─── Logout ─────────────────────────────────────────────────
  static Future<bool> logout() async {
    final res = await ApiService.post(ApiConfig.logout, {}, withAuth: true);
    await ApiService.clearToken();
    return res['success'] == true;
  }

  // ─── Fetch brands ──────────────────────────────────────────
  static Future<List<Brand>> fetchBrands() async {
    final res = await ApiService.get(ApiConfig.brands);
    if (res['success'] == true && res['data'] != null) {
      return (res['data'] as List).map((b) => Brand.fromJson(b)).toList();
    }
    return [];
  }

  // ─── Register: Individual ──────────────────────────────────
  static Future<AuthResult> registerIndividual({
    required String name,
    required String phoneNumber,
    required String location,
    String? email,
    required String password,
    required String passwordConfirmation,
  }) async {
    final res = await ApiService.post(ApiConfig.registerIndividual, {
      'name': name,
      'phone_number': phoneNumber,
      'location': location,
      if (email != null && email.isNotEmpty) 'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'terms': true,
    });

    if (res['success'] == true) {
      final data = res['data'] as Map<String, dynamic>;
      final token = data['token'] as String?;
      if (token != null) await ApiService.saveToken(token);
      final user = User.fromJson(data['user'] as Map<String, dynamic>);
      return AuthResult(success: true, message: res['message'], user: user, token: token);
    }

    return AuthResult(
      success: false,
      message: res['message'] ?? 'Registration failed',
      errors: res['errors'] as Map<String, dynamic>?,
    );
  }

  // ─── Register: Business Owner ──────────────────────────────
  static Future<AuthResult> registerBusinessOwner({
    required String name,
    required String phoneNumber,
    required String location,
    required String tinNumber,
    required File licenseImage,
    required File stampImage,
    String? licenseExpireDate,
    String? email,
    String? bankName,
    String? accountNumber,
    required String password,
    required String passwordConfirmation,
  }) async {
    final fields = <String, String>{
      'name': name,
      'phone_number': phoneNumber,
      'location': location,
      'tin_number': tinNumber,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'terms': '1',
    };
    if (email != null && email.isNotEmpty) fields['email'] = email;
    if (bankName != null) fields['bank_name'] = bankName;
    if (accountNumber != null) fields['account_number'] = accountNumber;
    if (licenseExpireDate != null) fields['license_expire_date'] = licenseExpireDate;

    final res = await ApiService.postMultipart(
      ApiConfig.registerBusinessOwner,
      fields: fields,
      files: {
        'license_image': licenseImage,
        'stamp_image': stampImage,
      },
    );

    return _parseRegistrationResult(res);
  }

  // ─── Register: Garage / Shop ───────────────────────────────
  static Future<AuthResult> registerGarageShop({
    required String name,
    required String phoneNumber,
    required String role, // 'garage' or 'shop'
    required String location,
    required String tinNumber,
    String? licenseExpireDate,
    String? email,
    required String password,
    required String passwordConfirmation,
    required File licenseImage,
    required File stampImage,
    List<int>? brandIds,
    String? bankName,
    String? accountNumber,
  }) async {
    final fields = <String, String>{
      'name': name,
      'phone_number': phoneNumber,
      'role': role,
      'location': location,
      'tin_number': tinNumber,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'terms': '1',
    };

    if (licenseExpireDate != null) fields['license_expire_date'] = licenseExpireDate;
    if (email != null && email.isNotEmpty) fields['email'] = email;
    if (bankName != null) fields['bank_name'] = bankName;
    if (accountNumber != null) fields['account_number'] = accountNumber;

    if (brandIds != null) {
      for (int i = 0; i < brandIds.length; i++) {
        fields['brands[$i]'] = brandIds[i].toString();
      }
    }

    final res = await ApiService.postMultipart(
      ApiConfig.registerGarageShop,
      fields: fields,
      files: {
        'license_image': licenseImage,
        'stamp_image': stampImage,
      },
    );

    return _parseRegistrationResult(res);
  }

  // ─── Helper ────────────────────────────────────────────────
  static AuthResult _parseRegistrationResult(Map<String, dynamic> res) {
    if (res['success'] == true) {
      final data = res['data'] as Map<String, dynamic>;
      return AuthResult(
        success: true,
        message: res['message'],
        user: User.fromJson(data),
      );
    }

    return AuthResult(
      success: false,
      message: res['message'] ?? 'Registration failed',
      errors: res['errors'] as Map<String, dynamic>?,
    );
  }
}
