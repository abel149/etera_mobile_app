import 'dart:io';
import '../config/api_config.dart';
import 'api_service.dart';

class SuperadminService {
  // ── Dashboard ─────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getDashboard() =>
      ApiService.get(ApiConfig.saDashboard, withAuth: true);

  // ── User management ───────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getUsers({
    String? role,
    String? status,
    int page = 1,
  }) =>
      ApiService.get(
          '${ApiConfig.saUserApproval}?page=$page'
          '${role != null ? '&role=$role' : ''}'
          '${status != null ? '&status=$status' : ''}',
          withAuth: true);

  static Future<Map<String, dynamic>> approveUser(int id) =>
      ApiService.put(ApiConfig.saApproveUser(id), {}, withAuth: true);

  static Future<Map<String, dynamic>> revokeUser(int id) =>
      ApiService.delete(ApiConfig.saRevokeUser(id), withAuth: true);

  static Future<Map<String, dynamic>> deleteUser(int id) =>
      ApiService.delete(ApiConfig.saDeleteUser(id), withAuth: true);

  // ── Proformas ─────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getProformas() =>
      ApiService.get(ApiConfig.saProformas, withAuth: true);

  static Future<Map<String, dynamic>> floatProforma(int id) =>
      ApiService.post(ApiConfig.saFloatProforma(id), {}, withAuth: true);

  static Future<Map<String, dynamic>> closeProforma(int id) =>
      ApiService.post(ApiConfig.saCloseProforma(id), {}, withAuth: true);

  // ── Admin management ──────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getAdmins() =>
      ApiService.get(ApiConfig.saAdmins, withAuth: true);

  static Future<Map<String, dynamic>> createAdmin({
    required String name,
    required String phoneNumber,
    String? email,
  }) =>
      ApiService.post(ApiConfig.saAdmins, {
        'name': name,
        'phone_number': phoneNumber,
        if (email != null && email.isNotEmpty) 'email': email,
      }, withAuth: true);

  static Future<Map<String, dynamic>> updateAdmin(
    int id, {
    required String name,
    required String phoneNumber,
    String? email,
  }) =>
      ApiService.put(ApiConfig.saUpdateAdmin(id), {
        'name': name,
        'phone_number': phoneNumber,
        if (email != null && email.isNotEmpty) 'email': email,
      }, withAuth: true);

  static Future<Map<String, dynamic>> deleteAdmin(int id) =>
      ApiService.delete(ApiConfig.saDeleteAdmin(id), withAuth: true);

  // ── Insurances ────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getInsurances() =>
      ApiService.get(ApiConfig.saInsurances, withAuth: true);

  static Future<Map<String, dynamic>> createInsurance({
    required String name,
    required String phoneNumber,
    String? email,
    String? password,
  }) =>
      ApiService.post(ApiConfig.saAddInsurance, {
        'name': name,
        'phone_number': phoneNumber,
        if (email != null && email.isNotEmpty) 'email': email,
        if (password != null && password.isNotEmpty) 'password': password,
      }, withAuth: true);

  static Future<Map<String, dynamic>> updateInsurance(
    int id, {
    required String name,
    required String phoneNumber,
    String? email,
  }) =>
      ApiService.put(ApiConfig.saEditInsurance(id), {
        'name': name,
        'phone_number': phoneNumber,
        if (email != null && email.isNotEmpty) 'email': email,
      }, withAuth: true);

  static Future<Map<String, dynamic>> deleteInsurance(int id) =>
      ApiService.delete(ApiConfig.saDeleteInsurance(id), withAuth: true);

  // ── Shops ─────────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getShops({String? search}) =>
      ApiService.get(
          '${ApiConfig.saShops}${search != null ? '?search=$search' : ''}',
          withAuth: true);

  static Future<Map<String, dynamic>> getShopDetail(int id) =>
      ApiService.get(ApiConfig.saEditShop(id), withAuth: true);

  static Future<Map<String, dynamic>> createShop({
    required String name,
    required String phoneNumber,
    required String location,
    required String tinNumber,
    required List<int> brands,
    String? email,
    String? password,
    File? licenseImage,
    File? stampImage,
  }) =>
      ApiService.postMultipart(
        ApiConfig.saAddShop,
        fields: {
          'name': name,
          'phone_number': phoneNumber,
          'location': location,
          'tin_number': tinNumber,
          if (email != null && email.isNotEmpty) 'email': email,
          if (password != null && password.isNotEmpty) 'password': password,
          'password_confirmation': password ?? '123456',
          'brands': brands.map((e) => e.toString()).join(','),
        },
        files: {
          if (licenseImage != null) 'license_image': licenseImage,
          if (stampImage != null) 'stamp_image': stampImage,
        },
        withAuth: true,
      );

  static Future<Map<String, dynamic>> updateShop(
    int id, {
    required String name,
    required String phoneNumber,
    required String location,
    required String tinNumber,
    required List<int> brands,
    String? email,
    File? licenseImage,
    File? stampImage,
  }) =>
      ApiService.postMultipart(
        ApiConfig.saUpdateShop(id),
        fields: {
          'name': name,
          'phone_number': phoneNumber,
          'location': location,
          'tin_number': tinNumber,
          if (email != null && email.isNotEmpty) 'email': email,
          'brands': brands.map((e) => e.toString()).join(','),
          '_method': 'PUT',
        },
        files: {
          if (licenseImage != null) 'license_image': licenseImage,
          if (stampImage != null) 'stamp_image': stampImage,
        },
        withAuth: true,
      );

  static Future<Map<String, dynamic>> deleteShop(int id) =>
      ApiService.delete(ApiConfig.saDeleteShop(id), withAuth: true);

  // ── Garages ───────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getGarages({String? search}) =>
      ApiService.get(
          '${ApiConfig.saGarages}${search != null ? '?search=$search' : ''}',
          withAuth: true);

  static Future<Map<String, dynamic>> getGarageDetail(int id) =>
      ApiService.get(ApiConfig.saEditGarage(id), withAuth: true);

  static Future<Map<String, dynamic>> createGarage({
    required String name,
    required String phoneNumber,
    required String location,
    required String tinNumber,
    String? email,
    String? password,
    File? licenseImage,
    File? stampImage,
  }) =>
      ApiService.postMultipart(
        ApiConfig.saAddGarage,
        fields: {
          'name': name,
          'phone_number': phoneNumber,
          'location': location,
          'tin_number': tinNumber,
          if (email != null && email.isNotEmpty) 'email': email,
          if (password != null && password.isNotEmpty) 'password': password,
          'password_confirmation': password ?? '123456',
        },
        files: {
          if (licenseImage != null) 'license_image': licenseImage,
          if (stampImage != null) 'stamp_image': stampImage,
        },
        withAuth: true,
      );

  static Future<Map<String, dynamic>> updateGarage(
    int id, {
    required String name,
    required String phoneNumber,
    required String location,
    required String tinNumber,
    String? email,
    File? licenseImage,
    File? stampImage,
  }) =>
      ApiService.postMultipart(
        ApiConfig.saUpdateGarage(id),
        fields: {
          'name': name,
          'phone_number': phoneNumber,
          'location': location,
          'tin_number': tinNumber,
          if (email != null && email.isNotEmpty) 'email': email,
          '_method': 'PUT',
        },
        files: {
          if (licenseImage != null) 'license_image': licenseImage,
          if (stampImage != null) 'stamp_image': stampImage,
        },
        withAuth: true,
      );

  static Future<Map<String, dynamic>> deleteGarage(int id) =>
      ApiService.delete(ApiConfig.saDeleteGarage(id), withAuth: true);

  // ── Operators ─────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getOperators() =>
      ApiService.get(ApiConfig.saOperators, withAuth: true);

  static Future<Map<String, dynamic>> assignManager(int operatorId, int managerId) =>
      ApiService.post(ApiConfig.saAssignManager(operatorId), {
        'manager_id': managerId,
      }, withAuth: true);

  static Future<Map<String, dynamic>> setQuota(int operatorId, int quota) =>
      ApiService.post(ApiConfig.saSetQuota(operatorId), {
        'file_quota': quota,
      }, withAuth: true);

  static Future<Map<String, dynamic>> setCommission(int operatorId, double commission) =>
      ApiService.post(ApiConfig.saSetCommission(operatorId), {
        'commission_per_file': commission,
      }, withAuth: true);

  // ── Marketers ─────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getMarketers() =>
      ApiService.get(ApiConfig.saMarketers, withAuth: true);

  static Future<Map<String, dynamic>> createMarketer({
    required String name,
    required String phoneNumber,
    String? email,
    String? password,
  }) =>
      ApiService.post(ApiConfig.saAddMarketer, {
        'name': name,
        'phone_number': phoneNumber,
        if (email != null && email.isNotEmpty) 'email': email,
        if (password != null && password.isNotEmpty) 'password': password,
      }, withAuth: true);

  static Future<Map<String, dynamic>> updateMarketer(
    int id, {
    required String name,
    required String phoneNumber,
    String? email,
  }) =>
      ApiService.put(ApiConfig.saUpdateMarketer(id), {
        'name': name,
        'phone_number': phoneNumber,
        if (email != null && email.isNotEmpty) 'email': email,
      }, withAuth: true);

  static Future<Map<String, dynamic>> deleteMarketer(int id) =>
      ApiService.delete(ApiConfig.saDeleteMarketer(id), withAuth: true);

  // ── Brands ────────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getBrands() =>
      ApiService.get(ApiConfig.saBrands, withAuth: true);

  static Future<Map<String, dynamic>> createBrand(String name) =>
      ApiService.post(ApiConfig.saAddBrand, {'name': name}, withAuth: true);

  // ── Ratings ────────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getRatings() =>
      ApiService.get(ApiConfig.saRatings, withAuth: true);

  // ── Transactions ────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getTransactions() =>
      ApiService.get(ApiConfig.saTransactions, withAuth: true);

  // ── Analytics ────────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getAnalytics() =>
      ApiService.get(ApiConfig.saAnalytics, withAuth: true);

  static Future<Map<String, dynamic>> markAsPaid(int userId) =>
      ApiService.post(ApiConfig.saMarkPaid(userId), {}, withAuth: true);

  static Future<Map<String, dynamic>> receivePayment(int userId) =>
      ApiService.post(ApiConfig.saReceivePayment(userId), {}, withAuth: true);

  // ── Settings ────────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getSettings() =>
      ApiService.get(ApiConfig.saSettings, withAuth: true);

  static Future<Map<String, dynamic>> storeCost(Map<String, dynamic> data) =>
      ApiService.post(ApiConfig.saStoreCost, data, withAuth: true);

  static Future<Map<String, dynamic>> storeCommission(Map<String, dynamic> data) =>
      ApiService.post(ApiConfig.saStoreCommission, data, withAuth: true);

  static Future<Map<String, dynamic>> toggleEmail(String key) =>
      ApiService.post(ApiConfig.saToggleEmail, {'key': key}, withAuth: true);
}
