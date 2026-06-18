import '../config/api_config.dart';
import '../models/proforma.dart';
import '../models/user.dart';
import 'api_service.dart';

class BusinessOwnerService {
  // ─── Dashboard ────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getDashboard() async {
    return ApiService.get(ApiConfig.businessOwnerDashboard, withAuth: true);
  }

  // ─── Balance ──────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getBalance() async {
    return ApiService.get(ApiConfig.businessOwnerBalance, withAuth: true);
  }

  // ─── Submit withdrawal ────────────────────────────────────────
  static Future<Map<String, dynamic>> submitWithdrawal({
    required double amount,
    required String bankName,
    required String accountNumber,
  }) async {
    return ApiService.post(
      ApiConfig.businessOwnerWithdraw,
      {
        'amount': amount,
        'bank_name': bankName,
        'account_number': accountNumber,
      },
      withAuth: true,
    );
  }

  // ─── Proformas list ───────────────────────────────────────────
  static Future<({List<ProformaItem> items, String? error})> getProformas() async {
    final res = await ApiService.get(ApiConfig.businessOwnerProformas, withAuth: true);
    if (res['unauthorized'] == true) {
      return (items: <ProformaItem>[], error: 'unauthorized');
    }
    if (res['success'] == true) {
      try {
        final raw = res['data'];
        List list;
        if (raw is List) {
          list = raw;
        } else if (raw is Map && raw['data'] is List) {
          list = raw['data'] as List;
        } else {
          list = [];
        }
        final items = list
            .map((e) => ProformaItem.fromJson(e as Map<String, dynamic>))
            .toList();
        return (items: items, error: null);
      } catch (e) {
        return (items: <ProformaItem>[], error: 'Parse error: $e');
      }
    }
    return (items: <ProformaItem>[], error: res['message']?.toString() ?? 'Failed to load');
  }

  // ─── Proforma detail ──────────────────────────────────────────
  static Future<({ProformaItem? item, Map<String, dynamic>? invoice, String? error})>
      getProformaDetail(int id) async {
    final res = await ApiService.get(ApiConfig.businessOwnerProformaDetail(id), withAuth: true);
    if (res['unauthorized'] == true) return (item: null, invoice: null, error: 'unauthorized');
    if (res['success'] == true && res['data'] != null) {
      try {
        final data = res['data'] as Map<String, dynamic>;
        Map<String, dynamic>? invoice;
        if (data.containsKey('proforma')) {
          final proformaJson = Map<String, dynamic>.from(data['proforma'] as Map<String, dynamic>);
          final partsJson = data['parts'];
          if (partsJson is List) proformaJson['parts'] = partsJson;
          final shopsJson = data['shops'];
          if (shopsJson is List) proformaJson['shops'] = shopsJson;
          final garagesJson = data['garages'];
          if (garagesJson is List) proformaJson['garages'] = garagesJson;
          if (data['invoice'] is Map) {
            invoice = Map<String, dynamic>.from(data['invoice'] as Map);
          }
          return (item: ProformaItem.fromJson(proformaJson), invoice: invoice, error: null);
        }
        return (item: ProformaItem.fromJson(data), invoice: null, error: null);
      } catch (e) {
        return (item: null, invoice: null, error: 'Parse error: $e');
      }
    }
    return (item: null, invoice: null, error: res['message']?.toString() ?? 'Failed to load');
  }

  // ─── Request close ────────────────────────────────────────────
  static Future<Map<String, dynamic>> requestClose(int id) async {
    return ApiService.post(ApiConfig.businessOwnerRequestClose(id), {}, withAuth: true);
  }

  // ─── List employees ───────────────────────────────────────────
  static Future<({List<User> employees, String? error})> listEmployees() async {
    final res = await ApiService.get(ApiConfig.businessOwnerEmployees, withAuth: true);
    if (res['unauthorized'] == true) return (employees: <User>[], error: 'unauthorized');
    if (res['success'] == true && res['data'] != null) {
      try {
        final list = res['data'] as List;
        final employees = list
            .map((e) => User.fromJson(e as Map<String, dynamic>))
            .toList();
        return (employees: employees, error: null);
      } catch (e) {
        return (employees: <User>[], error: 'Parse error: $e');
      }
    }
    return (employees: <User>[], error: res['message']?.toString() ?? 'Failed to load');
  }

  // ─── Create employee ──────────────────────────────────────────
  static Future<Map<String, dynamic>> createEmployee({
    required String name,
    required String phoneNumber,
    String? email,
    required String password,
    required String passwordConfirmation,
  }) async {
    return ApiService.post(
      ApiConfig.businessOwnerEmployees,
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

  // ─── Delete employee ──────────────────────────────────────────
  static Future<Map<String, dynamic>> deleteEmployee(int id) async {
    return ApiService.delete(ApiConfig.businessOwnerDeleteEmployee(id), withAuth: true);
  }

  // ─── Billing overview ─────────────────────────────────────────
  static Future<Map<String, dynamic>> getBillingOverview() async {
    return ApiService.get(ApiConfig.businessOwnerBilling, withAuth: true);
  }

  // ─── Update billing plan ──────────────────────────────────────
  static Future<Map<String, dynamic>> updateBillingPlan(String plan) async {
    return ApiService.put(
      ApiConfig.businessOwnerBillingPlan,
      {'plan': plan},
      withAuth: true,
    );
  }

  // ─── Billing statements list ──────────────────────────────────
  static Future<Map<String, dynamic>> getBillingStatements() async {
    return ApiService.get(ApiConfig.businessOwnerBillingStatements, withAuth: true);
  }

  // ─── Statement detail ─────────────────────────────────────────
  static Future<Map<String, dynamic>> getStatementDetail(String sku) async {
    return ApiService.get(ApiConfig.businessOwnerStatementDetail(sku), withAuth: true);
  }

  // ─── Paginated proforma invoices (all plans) ──────────────────
  static Future<Map<String, dynamic>> getBillingInvoices({int page = 1}) async {
    return ApiService.get('${ApiConfig.businessOwnerBillingInvoices}?page=$page', withAuth: true);
  }
}
