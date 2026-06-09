import '../config/api_config.dart';
import '../models/proforma.dart';
import '../models/user.dart';
import 'api_service.dart';

class OthersService {
  // ─── Dashboard ───────────────────────────────────────────────
  static Future<Map<String, dynamic>> getDashboard() async {
    return ApiService.get(ApiConfig.othersDashboard, withAuth: true);
  }

  // ─── Proformas list ──────────────────────────────────────────
  static Future<({List<ProformaItem> items, String? error})> getProformas() async {
    final res = await ApiService.get(ApiConfig.othersProformas, withAuth: true);
    if (res['unauthorized'] == true) {
      return (items: <ProformaItem>[], error: 'unauthorized');
    }
    if (res['success'] == true) {
      try {
        final raw = res['data'];
        List list;
        if (raw is List) {
          list = raw; // non-paginated array
        } else if (raw is Map && raw['data'] is List) {
          list = raw['data'] as List; // Laravel paginated ResourceCollection
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

  // ─── Proforma detail ─────────────────────────────────────────
  static Future<({ProformaItem? item, String? error})> getProformaDetail(int id) async {
    final res = await ApiService.get(ApiConfig.othersProformaDetail(id), withAuth: true);
    if (res['unauthorized'] == true) return (item: null, error: 'unauthorized');
    if (res['success'] == true && res['data'] != null) {
      try {
        final data = res['data'] as Map<String, dynamic>;
        // show() returns: {proforma: {...}, parts: [...], shops: [...], garages: [...]}
        // If nested, extract proforma + merge parts
        if (data.containsKey('proforma')) {
          final proformaJson = Map<String, dynamic>.from(
              data['proforma'] as Map<String, dynamic>);
          // Inject parts, shops, garages from the separate arrays
          final partsJson = data['parts'];
          if (partsJson is List) proformaJson['parts'] = partsJson;
          final shopsJson = data['shops'];
          if (shopsJson is List) proformaJson['shops'] = shopsJson;
          final garagesJson = data['garages'];
          if (garagesJson is List) proformaJson['garages'] = garagesJson;
          final item = ProformaItem.fromJson(proformaJson);
          return (item: item, error: null);
        }
        // Fallback: data is the proforma directly
        final item = ProformaItem.fromJson(data);
        return (item: item, error: null);
      } catch (e) {
        return (item: null, error: 'Parse error: $e');
      }
    }
    return (item: null, error: res['message']?.toString() ?? 'Failed to load');
  }

  // ─── Request close ───────────────────────────────────────────
  static Future<Map<String, dynamic>> requestClose(int id) async {
    return ApiService.post(ApiConfig.othersRequestClose(id), {}, withAuth: true);
  }

  // ─── Profile (shared) ────────────────────────────────────────
  static Future<({User? user, String? error})> getProfile() async {
    final res = await ApiService.get(ApiConfig.profile, withAuth: true);
    if (res['unauthorized'] == true) return (user: null, error: 'unauthorized');
    if (res['success'] == true && res['data'] != null) {
      final data = res['data'];
      final userJson = data is Map && data['user'] != null
          ? data['user'] as Map<String, dynamic>
          : data as Map<String, dynamic>;
      return (user: User.fromJson(userJson), error: null);
    }
    return (user: null, error: res['message']?.toString() ?? 'Failed to load profile');
  }

  // ─── Update profile (shared) ─────────────────────────────────
  static Future<Map<String, dynamic>> updateProfile({
    String? name,
    String? location,
    String? email,
  }) async {
    final body = <String, dynamic>{};
    if (name != null) body['name'] = name;
    if (location != null) body['location'] = location;
    if (email != null) body['email'] = email;
    return ApiService.put(ApiConfig.profile, body, withAuth: true);
  }
}
