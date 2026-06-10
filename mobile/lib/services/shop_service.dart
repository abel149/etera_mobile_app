import '../config/api_config.dart';
import 'api_service.dart';

class ShopService {
  static Future<Map<String, dynamic>> getDashboard() =>
      ApiService.get(ApiConfig.shopDashboard, withAuth: true);

  static Future<Map<String, dynamic>> getInbox({int page = 1}) =>
      ApiService.get('${ApiConfig.shopInbox}?page=$page', withAuth: true);

  static Future<Map<String, dynamic>> getProformas({int page = 1}) =>
      ApiService.get('${ApiConfig.shopProformas}?page=$page', withAuth: true);

  static Future<Map<String, dynamic>> getProformaDetail(int id) =>
      ApiService.get(ApiConfig.shopProformaDetail(id), withAuth: true);

  static Future<Map<String, dynamic>> applyProforma(int id, Map<String, dynamic> body) =>
      ApiService.post(ApiConfig.shopApplyProforma(id), body, withAuth: true);

  static Future<Map<String, dynamic>> getMyApplications({int page = 1}) =>
      ApiService.get('${ApiConfig.shopMyApplications}?page=$page', withAuth: true);

  static Future<Map<String, dynamic>> getBalance() =>
      ApiService.get(ApiConfig.shopBalance, withAuth: true);

  static Future<Map<String, dynamic>> getEmployees() =>
      ApiService.get(ApiConfig.shopEmployees, withAuth: true);

  static Future<Map<String, dynamic>> createEmployee(Map<String, dynamic> body) =>
      ApiService.post(ApiConfig.shopEmployees, body, withAuth: true);

  static Future<Map<String, dynamic>> deleteEmployee(int id) =>
      ApiService.delete(ApiConfig.shopDeleteEmployee(id), withAuth: true);
}
