import '../config/api_config.dart';
import 'api_service.dart';

class InsuranceService {
  static Future<Map<String, dynamic>> getDashboard() =>
      ApiService.get(ApiConfig.insuranceDashboard, withAuth: true);

  static Future<Map<String, dynamic>> getProformas({String? status, int page = 1}) =>
      ApiService.get('${ApiConfig.insuranceProformas}?page=$page${status != null ? '&status=$status' : ''}', withAuth: true);

  static Future<Map<String, dynamic>> getProformaDetail(int id) =>
      ApiService.get(ApiConfig.insuranceProformaDetail(id), withAuth: true);

  static Future<Map<String, dynamic>> requestClose(int id) =>
      ApiService.post(ApiConfig.insuranceRequestClose(id), {}, withAuth: true);

  static Future<Map<String, dynamic>> getReceivedProformas({int page = 1}) =>
      ApiService.get('${ApiConfig.insuranceReceivedProformas}?page=$page', withAuth: true);

  static Future<Map<String, dynamic>> getBalance() =>
      ApiService.get(ApiConfig.insuranceBalance, withAuth: true);

  static Future<Map<String, dynamic>> getPartners() =>
      ApiService.get(ApiConfig.insurancePartners, withAuth: true);

  static Future<Map<String, dynamic>> getAvailablePartners() =>
      ApiService.get(ApiConfig.insuranceAvailablePartners, withAuth: true);

  static Future<Map<String, dynamic>> addPartners(List<int> partnerIds) =>
      ApiService.post(ApiConfig.insurancePartners, {'partners': partnerIds}, withAuth: true);

  static Future<Map<String, dynamic>> removePartner(int id) =>
      ApiService.delete(ApiConfig.insuranceDeletePartner(id), withAuth: true);

  static Future<Map<String, dynamic>> getEmployees() =>
      ApiService.get(ApiConfig.insuranceEmployees, withAuth: true);

  static Future<Map<String, dynamic>> createEmployee(Map<String, dynamic> body) =>
      ApiService.post(ApiConfig.insuranceEmployees, body, withAuth: true);

  static Future<Map<String, dynamic>> deleteEmployee(int id) =>
      ApiService.delete(ApiConfig.insuranceDeleteEmployee(id), withAuth: true);

  static Future<Map<String, dynamic>> getBilling() =>
      ApiService.get(ApiConfig.insuranceBilling, withAuth: true);

  static Future<Map<String, dynamic>> updateBillingPlan(String plan) =>
      ApiService.put(ApiConfig.insuranceBillingPlan, {'plan': plan}, withAuth: true);

  static Future<Map<String, dynamic>> getBillingStatements() =>
      ApiService.get(ApiConfig.insuranceBillingStatements, withAuth: true);

  static Future<Map<String, dynamic>> getStatementDetail(String sku) =>
      ApiService.get(ApiConfig.insuranceStatementDetail(sku), withAuth: true);

  static Future<Map<String, dynamic>> createProforma(Map<String, dynamic> body) =>
      ApiService.post(ApiConfig.insuranceCreateFile, body, withAuth: true);
}
