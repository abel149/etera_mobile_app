import '../config/api_config.dart';
import '../models/proforma.dart';
import '../models/user.dart';
import 'api_service.dart';

class GarageService {
  // ─── Dashboard ────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getDashboard() async {
    return ApiService.get(ApiConfig.garageDashboard, withAuth: true);
  }

  // ─── Balance ──────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getBalance() async {
    return ApiService.get(ApiConfig.garageBalance, withAuth: true);
  }

  // ─── Submit withdrawal ────────────────────────────────────────
  static Future<Map<String, dynamic>> submitWithdrawal({
    required double amount,
    required String bankName,
    required String accountNumber,
  }) async {
    return ApiService.post(
      ApiConfig.garageWithdraw,
      {'amount': amount, 'bank_name': bankName, 'account_number': accountNumber},
      withAuth: true,
    );
  }

  // ─── Inbox ────────────────────────────────────────────────────
  static Future<({List<Map<String, dynamic>> items, String? error})>
      getInbox() async {
    final res = await ApiService.get(ApiConfig.garageInbox, withAuth: true);
    if (res['unauthorized'] == true) {
      return (items: <Map<String, dynamic>>[], error: 'unauthorized');
    }
    if (res['success'] == true) {
      try {
        final raw = res['data'];
        final list = raw is List ? raw : <dynamic>[];
        return (
          items: list.map((e) => Map<String, dynamic>.from(e as Map)).toList(),
          error: null,
        );
      } catch (e) {
        return (items: <Map<String, dynamic>>[], error: 'Parse error: $e');
      }
    }
    return (
      items: <Map<String, dynamic>>[],
      error: res['message']?.toString() ?? 'Failed to load',
    );
  }

  // ─── Proforma detail (from inbox) ────────────────────────────
  static Future<Map<String, dynamic>> getProformaDetail(int id) async {
    return ApiService.get(ApiConfig.garageProformaDetail(id), withAuth: true);
  }

  // ─── Apply / submit quote ─────────────────────────────────────
  static Future<Map<String, dynamic>> applyProforma({
    required int proformaId,
    required double amount,
    double? discount,
  }) async {
    return ApiService.post(
      ApiConfig.garageApplyProforma(proformaId),
      {
        'amount': amount,
        if (discount != null) 'discount': discount,
      },
      withAuth: true,
    );
  }

  // ─── My applications (bids) ───────────────────────────────────
  static Future<({List<Map<String, dynamic>> items, String? error})>
      getMyApplications() async {
    final res =
        await ApiService.get(ApiConfig.garageMyApplications, withAuth: true);
    if (res['unauthorized'] == true) {
      return (items: <Map<String, dynamic>>[], error: 'unauthorized');
    }
    if (res['success'] == true) {
      try {
        final raw = res['data'];
        final list = raw is List ? raw : <dynamic>[];
        return (
          items: list.map((e) => Map<String, dynamic>.from(e as Map)).toList(),
          error: null,
        );
      } catch (e) {
        return (items: <Map<String, dynamic>>[], error: 'Parse error: $e');
      }
    }
    return (
      items: <Map<String, dynamic>>[],
      error: res['message']?.toString() ?? 'Failed to load',
    );
  }

  // ─── Received (completed proformas) ─────────────────────────
  static Future<({List<ProformaItem> items, String? error})>
      getReceivedProformas() async {
    final res = await ApiService.get(ApiConfig.garageReceivedProformas,
        withAuth: true);
    if (res['unauthorized'] == true) {
      return (items: <ProformaItem>[], error: 'unauthorized');
    }
    if (res['success'] == true) {
      try {
        final raw = res['data'];
        final list = raw is List ? raw : <dynamic>[];
        final items = list
            .map((e) => ProformaItem.fromJson(e as Map<String, dynamic>))
            .toList();
        return (items: items, error: null);
      } catch (e) {
        return (items: <ProformaItem>[], error: 'Parse error: $e');
      }
    }
    return (
      items: <ProformaItem>[],
      error: res['message']?.toString() ?? 'Failed to load',
    );
  }

  // ─── My files (proformas created by garage) ───────────────────
  static Future<({List<ProformaItem> items, String? error})>
      getMyFiles() async {
    final res = await ApiService.get(ApiConfig.garageMyFiles, withAuth: true);
    if (res['unauthorized'] == true) {
      return (items: <ProformaItem>[], error: 'unauthorized');
    }
    if (res['success'] == true) {
      try {
        final raw = res['data'];
        final list = raw is List ? raw : [];
        final items = list
            .map((e) => ProformaItem.fromJson(e as Map<String, dynamic>))
            .toList();
        return (items: items, error: null);
      } catch (e) {
        return (items: <ProformaItem>[], error: 'Parse error: $e');
      }
    }
    return (
      items: <ProformaItem>[],
      error: res['message']?.toString() ?? 'Failed to load',
    );
  }

  // ─── My file detail ───────────────────────────────────────────
  static Future<Map<String, dynamic>> getMyFileDetail(int id) async {
    return ApiService.get(ApiConfig.garageMyFileDetail(id), withAuth: true);
  }

  // ─── Request close ────────────────────────────────────────────
  static Future<Map<String, dynamic>> requestClose(int id) async {
    return ApiService.post(ApiConfig.garageRequestClose(id), {}, withAuth: true);
  }

  // ─── Employees ────────────────────────────────────────────────
  static Future<({List<User> employees, String? error})>
      listEmployees() async {
    final res = await ApiService.get(ApiConfig.garageEmployees, withAuth: true);
    if (res['unauthorized'] == true) {
      return (employees: <User>[], error: 'unauthorized');
    }
    if (res['success'] == true) {
      try {
        final raw = res['data'] as List? ?? [];
        final employees =
            raw.map((e) => User.fromJson(e as Map<String, dynamic>)).toList();
        return (employees: employees, error: null);
      } catch (e) {
        return (employees: <User>[], error: 'Parse error: $e');
      }
    }
    return (
      employees: <User>[],
      error: res['message']?.toString() ?? 'Failed to load',
    );
  }

  static Future<Map<String, dynamic>> createEmployee({
    required String name,
    required String phoneNumber,
    String? email,
    required String password,
    required String passwordConfirmation,
  }) async {
    return ApiService.post(
      ApiConfig.garageEmployees,
      {
        'name': name,
        'phone_number': phoneNumber,
        if (email != null && email.isNotEmpty) 'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
      },
      withAuth: true,
    );
  }

  static Future<Map<String, dynamic>> deleteEmployee(int id) async {
    return ApiService.delete(
        ApiConfig.garageDeleteEmployee(id), withAuth: true);
  }

  // ─── Billing ──────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getBillingOverview() async {
    return ApiService.get(ApiConfig.garageBilling, withAuth: true);
  }

  static Future<Map<String, dynamic>> updateBillingPlan(String plan) async {
    return ApiService.put(
        ApiConfig.garageBillingPlan, {'plan': plan}, withAuth: true);
  }

  static Future<Map<String, dynamic>> getStatementDetail(String sku) async {
    return ApiService.get(ApiConfig.garageBillingStatementDetail(sku),
        withAuth: true);
  }
}
