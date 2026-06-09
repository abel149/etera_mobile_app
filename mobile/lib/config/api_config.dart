class ApiConfig {
  static const String baseUrl = 'https://etapp.usstandardgarage.com/api/v1';

  // Auth
  static const String login = '$baseUrl/auth/login';
  static const String logout = '$baseUrl/auth/logout';
  static const String brands = '$baseUrl/brands';

  // Registration
  static const String registerIndividual = '$baseUrl/register/others';
  static const String registerBusinessOwner = '$baseUrl/register/business-owner';
  static const String registerGarageShop = '$baseUrl/register/garage-shop';

  // Shared protected
  static const String profile = '$baseUrl/profile';
  static const String uploadTemp = '$baseUrl/upload/temp';

  // Others role
  static const String othersDashboard = '$baseUrl/others/dashboard';
  static const String othersProformas = '$baseUrl/others/proformas';
  static String othersProformaDetail(int id) => '$baseUrl/others/proformas/$id';
  static String othersRequestClose(int id) => '$baseUrl/others/proformas/$id/request-close';

  // Proforma (role-based endpoints)
  static const String _createProformaOthers = '$baseUrl/others/create-file';
  static const String _createProformaBusinessOwner = '$baseUrl/business-owner/create-file';
  static const String _createProformaGarage = '$baseUrl/garage/create-file';

  static String createProformaUrl(String role) {
    switch (role) {
      case 'business_owner':
      case 'employee':
        return _createProformaBusinessOwner;
      case 'garage':
      case 'shop':
        return _createProformaGarage;
      default:
        return _createProformaOthers;
    }
  }
}
