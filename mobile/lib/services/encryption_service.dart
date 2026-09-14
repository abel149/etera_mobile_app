import '../config/api_config.dart';
import 'api_service.dart';

/// All API calls related to E2E encryption.
/// Every method returns a Map with at least { 'success': bool }.
class EncryptionService {
  // ── Insurance ─────────────────────────────────────────────────────────────

  /// GET /api/v1/insurance/encryption/status
  /// Returns { has_encryption, public_key, has_recovery }
  static Future<Map<String, dynamic>> getStatus() =>
      ApiService.get(ApiConfig.insuranceEncryptionStatus, withAuth: true);

  /// POST /api/v1/insurance/encryption/setup
  /// Body: { public_key, encrypted_private_key, key_iv, key_salt,
  ///         recovery_encrypted_private_key?, recovery_key_iv?, recovery_key_salt? }
  static Future<Map<String, dynamic>> saveKeys(Map<String, dynamic> body) =>
      ApiService.post(ApiConfig.insuranceEncryptionSetup, body, withAuth: true);

  /// GET /api/v1/insurance/encryption/private-key
  /// Returns { encrypted_private_key, key_iv, key_salt }
  static Future<Map<String, dynamic>> getPrivateKey() =>
      ApiService.get(ApiConfig.insuranceEncryptionPrivKey, withAuth: true);

  /// POST /api/v1/insurance/encryption/change-pin
  /// Body: { encrypted_private_key, key_iv, key_salt }
  static Future<Map<String, dynamic>> changePin(Map<String, dynamic> body) =>
      ApiService.post(ApiConfig.insuranceEncryptionChgPin, body, withAuth: true);

  /// GET /api/v1/insurance/encryption/recovery-key
  /// Returns { recovery_encrypted_private_key, recovery_key_iv, recovery_key_salt }
  static Future<Map<String, dynamic>> getRecoveryKey() =>
      ApiService.get(ApiConfig.insuranceEncryptionRecKey, withAuth: true);

  /// GET /api/v1/insurance/application/{id}/encrypted-file
  /// Returns { encrypted_pdf (base64), encrypted_aes_key }
  static Future<Map<String, dynamic>> getEncryptedFile(int applicationId) =>
      ApiService.get(ApiConfig.insuranceEncryptedFile(applicationId), withAuth: true);

  // ── Shared ────────────────────────────────────────────────────────────────

  /// GET /api/v1/proforma/{id}/public-key
  /// Returns { has_encryption, public_key? }
  /// Called by shop/garage before submitting a price quote.
  static Future<Map<String, dynamic>> getPublicKey(int proformaId) =>
      ApiService.get(ApiConfig.proformaPublicKey(proformaId), withAuth: true);
}
