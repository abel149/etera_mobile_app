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

  // Admin / Superadmin (mobile)
  static const String adminDashboard       = '$baseUrl/admin-mobile/dashboard';
  static const String adminProformas       = '$baseUrl/admin-mobile/proformas';
  static String adminFloatProforma(int id) => '$baseUrl/admin-mobile/proformas/$id/float';
  static String adminCloseProforma(int id) => '$baseUrl/admin-mobile/proformas/$id/close';
  static const String adminApprovals       = '$baseUrl/admin-mobile/approvals';
  static String adminApproveUser(int id)   => '$baseUrl/admin-mobile/approvals/$id/approve';
  static String adminRejectUser(int id)    => '$baseUrl/admin-mobile/approvals/$id/reject';
  static const String adminAdmins          = '$baseUrl/admin-mobile/admins';
  static String adminDeleteAdmin(int id)   => '$baseUrl/admin-mobile/admins/$id';

  // Others role
  static const String othersDashboard         = '$baseUrl/others/dashboard';
  static const String othersProformas         = '$baseUrl/others/proformas';
  static String othersProformaDetail(int id)  => '$baseUrl/others/proformas/$id';
  static String othersRequestClose(int id)    => '$baseUrl/others/proformas/$id/request-close';
  static const String othersReceivedProformas = '$baseUrl/others/received-proformas';

  // Business Owner role
  static const String businessOwnerDashboard         = '$baseUrl/business-owner/dashboard';
  static const String businessOwnerProformas         = '$baseUrl/business-owner/proformas';
  static String businessOwnerProformaDetail(int id)  => '$baseUrl/business-owner/proformas/$id';
  static String businessOwnerRequestClose(int id)    => '$baseUrl/business-owner/proformas/$id/request-close';
  static const String businessOwnerReceivedProformas = '$baseUrl/business-owner/received-proformas';
  static const String businessOwnerBalance           = '$baseUrl/business-owner/balance';
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

  // Shop role
  static const String shopDashboard = '$baseUrl/shop/dashboard';
  static const String shopInbox = '$baseUrl/shop/inbox';
  static const String shopProformas = '$baseUrl/shop/proformas';
  static const String shopMyApplications = '$baseUrl/shop/my-applications';
  static String shopProformaDetail(int id) => '$baseUrl/shop/proformas/$id';
  static String shopApplyProforma(int id) => '$baseUrl/shop/proformas/$id/apply';
  static const String shopBalance = '$baseUrl/shop/balance';
  static const String shopEmployees = '$baseUrl/shop/employees';
  static String shopDeleteEmployee(int id) => '$baseUrl/shop/employees/$id';

  // Insurance role
  static const String insuranceDashboard = '$baseUrl/insurance/dashboard';
  static const String insuranceProformas = '$baseUrl/insurance/proformas';
  static String insuranceProformaDetail(int id) => '$baseUrl/insurance/proformas/$id';
  static String insuranceCreateFile = '$baseUrl/insurance/create-file';
  static String insuranceRequestClose(int id) => '$baseUrl/insurance/proformas/$id/request-close';
  static const String insuranceReceivedProformas = '$baseUrl/insurance/received-proformas';
  static const String insuranceBalance = '$baseUrl/insurance/balance';
  static const String insurancePartners = '$baseUrl/insurance/partners';
  static String insuranceDeletePartner(int id) => '$baseUrl/insurance/partners/$id';
  static const String insuranceEmployees = '$baseUrl/insurance/employees';
  static String insuranceDeleteEmployee(int id) => '$baseUrl/insurance/employees/$id';
  static const String insuranceBilling = '$baseUrl/insurance/billing';
  static const String insuranceBillingPlan = '$baseUrl/insurance/billing/plan';
  static const String insuranceBillingStatements = '$baseUrl/insurance/billing/statements';
  static String insuranceStatementDetail(String sku) => '$baseUrl/insurance/billing/statements/$sku';

  // Proforma (role-based endpoints)
  static const String _createProformaOthers = '$baseUrl/others/create-file';
  static const String _createProformaBusinessOwner = '$baseUrl/business-owner/create-file';
  static const String _createProformaGarage = '$baseUrl/garage/create-file';
  static const String _createProformaInsurance = '$baseUrl/insurance/create-file';

  static String createProformaUrl(String role) {
    switch (role) {
      case 'business_owner':
      case 'employee':
        return _createProformaBusinessOwner;
      case 'garage':
        return _createProformaGarage;
      case 'insurance':
        return _createProformaInsurance;
      default:
        return _createProformaOthers;
    }
  }
}
