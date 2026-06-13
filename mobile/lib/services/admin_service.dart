import '../config/api_config.dart';
import 'api_service.dart';

class AdminService {
  static Future<Map<String, dynamic>> getDashboard() =>
      ApiService.get(ApiConfig.adminDashboard, withAuth: true);

  static Future<Map<String, dynamic>> getProformas({String? status, int page = 1}) =>
      ApiService.get(
          '${ApiConfig.adminProformas}?page=$page${status != null ? '&status=$status' : ''}',
          withAuth: true);

  static Future<Map<String, dynamic>> floatProforma(int id) =>
      ApiService.post(ApiConfig.adminFloatProforma(id), {}, withAuth: true);

  static Future<Map<String, dynamic>> closeProforma(int id) =>
      ApiService.post(ApiConfig.adminCloseProforma(id), {}, withAuth: true);

  static Future<Map<String, dynamic>> getPendingApprovals({String? role, int page = 1}) =>
      ApiService.get(
          '${ApiConfig.adminApprovals}?page=$page${role != null ? '&role=$role' : ''}',
          withAuth: true);

  static Future<Map<String, dynamic>> approveUser(int id) =>
      ApiService.put(ApiConfig.adminApproveUser(id), {}, withAuth: true);

  static Future<Map<String, dynamic>> rejectUser(int id) =>
      ApiService.put(ApiConfig.adminRejectUser(id), {}, withAuth: true);

  static Future<Map<String, dynamic>> getAdmins() =>
      ApiService.get(ApiConfig.adminAdmins, withAuth: true);

  static Future<Map<String, dynamic>> createAdmin({
    required String name,
    required String phoneNumber,
    String? email,
  }) =>
      ApiService.post(ApiConfig.adminAdmins, {
        'name': name,
        'phone_number': phoneNumber,
        if (email != null && email.isNotEmpty) 'email': email,
      }, withAuth: true);

  static Future<Map<String, dynamic>> deleteAdmin(int id) =>
      ApiService.delete(ApiConfig.adminDeleteAdmin(id), withAuth: true);

  static Future<Map<String, dynamic>> getAllUsers({String? role, String? status, int page = 1}) =>
      ApiService.get(
          '${ApiConfig.adminUsers}?page=$page'
          '${role != null ? '&role=$role' : ''}'
          '${status != null ? '&status=$status' : ''}',
          withAuth: true);

  static Future<Map<String, dynamic>> deleteUser(int id) =>
      ApiService.delete(ApiConfig.adminDeleteUser(id), withAuth: true);
}
