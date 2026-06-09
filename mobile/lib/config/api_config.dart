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

  // Business Owner role
  static const String businessOwnerDashboard = '$baseUrl/business-owner/dashboard';
  static const String businessOwnerProformas = '$baseUrl/business-owner/proformas';
  static String businessOwnerProformaDetail(int id) => '$baseUrl/business-owner/proformas/$id';
  static String businessOwnerRequestClose(int id) => '$baseUrl/business-owner/proformas/$id/request-close';
  static const String businessOwnerBalance = '$baseUrl/business-owner/balance';
  static const String businessOwnerWithdraw = '$baseUrl/business-owner/withdraw';
  static const String businessOwnerEmployees = '$baseUrl/business-owner/employees';
  static String businessOwnerDeleteEmployee(int id) => '$baseUrl/business-owner/employees/$id';
  static const String businessOwnerBilling = '$baseUrl/business-owner/billing';
  static const String businessOwnerBillingPlan = '$baseUrl/business-owner/billing/plan';
  static const String businessOwnerBillingStatements = '$baseUrl/business-owner/billing/statements';
  static String businessOwnerStatementDetail(String sku) => '$baseUrl/business-owner/billing/statements/$sku';

  // Garage role
  static const String garageDashboard = '$baseUrl/garage/dashboard';
  static const String garageInbox = '$baseUrl/garage/inbox';
  static const String garageMyApplications = '$baseUrl/garage/my-applications';
  static String garageProformaDetail(int id) => '$baseUrl/garage/proformas/$id';
  static String garageApplyProforma(int id) => '$baseUrl/garage/proformas/$id/apply';
  static const String garageMyFiles = '$baseUrl/garage/my-files';
  static String garageMyFileDetail(int id) => '$baseUrl/garage/my-files/$id';
  static const String garageBalance = '$baseUrl/garage/balance';
  static const String garageWithdraw = '$baseUrl/garage/withdraw';
  static const String garageReceivedProformas = '$baseUrl/garage/received-proformas';
  static const String registerDeviceToken = '$baseUrl/device-token';
  static const String notifications = '$baseUrl/notifications';
  static const String notificationsMarkRead = '$baseUrl/notifications/read';
  static String garageRequestClose(int id) => '$baseUrl/garage/proformas/$id/request-close';
  static const String garageEmployees = '$baseUrl/garage/employees';
  static String garageDeleteEmployee(int id) => '$baseUrl/garage/employees/$id';
  static const String garageBilling = '$baseUrl/garage/billing';
  static const String garageBillingPlan = '$baseUrl/garage/billing/plan';
  static const String garageBillingStatements = '$baseUrl/garage/billing/statements';
  static String garageBillingStatementDetail(String sku) => '$baseUrl/garage/billing/statements/$sku';

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
