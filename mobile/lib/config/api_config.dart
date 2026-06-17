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
  static String adminProformaDetail(int id)       => '$baseUrl/admin-mobile/proformas/$id';
  static String adminInboxShops(int id)           => '$baseUrl/admin-mobile/proformas/$id/inbox-shops';
  static String adminInboxGarages(int id)         => '$baseUrl/admin-mobile/proformas/$id/inbox-garages';
  static String adminSendToOwner(int id)          => '$baseUrl/admin-mobile/proformas/$id/send-to-owner';
  static const String adminApprovals       = '$baseUrl/admin-mobile/approvals';
  static String adminApproveUser(int id)   => '$baseUrl/admin-mobile/approvals/$id/approve';
  static String adminRejectUser(int id)    => '$baseUrl/admin-mobile/approvals/$id/reject';
  static const String adminAdmins          = '$baseUrl/admin-mobile/admins';
  static String adminDeleteAdmin(int id)   => '$baseUrl/admin-mobile/admins/$id';
  static const String adminUsers           = '$baseUrl/admin-mobile/users';
  static String adminDeleteUser(int id)    => '$baseUrl/admin-mobile/users/$id';
 
  // Superadmin (/api/v1/admin/ — superadmin role only)
  static const String saBase                   = '$baseUrl/admin';
  static const String saDashboard              = '$saBase/dashboard';
  // User management
  static const String saUserApproval           = '$saBase/user-approval';
  static String saApproveUser(int id)          => '$saBase/user-approval/$id';
  static String saRevokeUser(int id)           => '$saBase/user-approval/$id';
  static String saViewUser(int id)             => '$saBase/user/$id';
  static String saDeleteUser(int id)           => '$saBase/user/$id';
  // Proformas
  static const String saProformas              = '$saBase/others-proforma';
  static String saFloatProforma(int id)        => '$saBase/float/$id';
  static String saCloseProforma(int id)        => '$saBase/close/$id';
  // Admin management
  static const String saAdmins                 = '$saBase/admins';
  static String saUpdateAdmin(int id)          => '$saBase/admins/$id';
  static String saDeleteAdmin(int id)          => '$saBase/admins/$id';
  // Insurance management
  static const String saInsurances             = '$saBase/insurances';
  static const String saAddInsurance           = '$saBase/add-insurance';
  static String saEditInsurance(int id)        => '$saBase/edit-insurance/$id';
  static String saDeleteInsurance(int id)      => '$saBase/delete-insurance/$id';
  // Shop management
  static const String saShops                  = '$saBase/spare-parts';
  static const String saAddShop                = '$saBase/add-shop';
  static String saEditShop(int id)             => '$saBase/edit-shop/$id';
  static String saUpdateShop(int id)           => '$saBase/edit-shop/$id';
  static String saDeleteShop(int id)           => '$saBase/delete-shop/$id';
  // Garage management
  static const String saGarages                = '$saBase/garages';
  static const String saAddGarage              = '$saBase/add-garage';
  static String saEditGarage(int id)           => '$saBase/edit-garage/$id';
  static String saUpdateGarage(int id)         => '$saBase/edit-garage/$id';
  static String saDeleteGarage(int id)         => '$saBase/delete-garage/$id';
  // Operators
  static const String saOperators              = '$saBase/operators';
  static String saAssignManager(int opId)     => '$saBase/assign-manager/$opId';
  static String saSetQuota(int opId)          => '$saBase/set-quota/$opId';
  static String saSetCommission(int opId)     => '$saBase/set-commission/$opId';
  static const String saCommissions            = '$saBase/commissions';
  // Marketers
  static const String saMarketers              = '$saBase/marketers';
  static const String saAddMarketer            = '$saBase/add-marketer';
  static String saEditMarketer(int id)        => '$saBase/edit-marketer/$id';
  static String saUpdateMarketer(int id)      => '$saBase/edit-marketer/$id';
  static String saDeleteMarketer(int id)      => '$saBase/delete-marketer/$id';
  // Brands
  static const String saBrands                 = '$saBase/brands';
  static const String saAddBrand               = '$saBase/brands';
  // Notifications
  static const String saNotifications          = '$saBase/notifications';
  static const String saMarkAsRead             = '$saBase/mark-as-read';
  // Ratings
  static const String saRatings                = '$saBase/ratings';
  // Transactions
  static const String saTransactions            = '$saBase/transactions';
  // Analytics
  static const String saAnalytics              = '$saBase/admin/analytics';
  static String saMarkPaid(int userId)         => '$saBase/admin/analytics/mark-paid/$userId';
  static String saReceivePayment(int userId)   => '$saBase/admin/analytics/receieve/$userId';
  // Settings
  static const String saSettings               = '$saBase/admin/settings';
  static const String saStoreCost              = '$saBase/admin/settings/costs';
  static String saStoreCommission             = '$saBase/admin/settings/commissions';
  static const String saToggleEmail            = '$saBase/admin/settings/email-toggle';

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
