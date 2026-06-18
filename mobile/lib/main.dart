import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'package:provider/provider.dart';
import 'services/notification_service.dart';
import 'screens/superadmin/admin_proforma_detail_screen.dart';
import 'screens/shop/shop_proforma_detail_screen.dart';
import 'screens/insurance/insurance_proforma_detail_screen.dart';
import 'screens/garage/garage_inbox_detail_screen.dart';
import 'config/theme.dart';
import 'providers/auth_provider.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/role_selection_screen.dart';
import 'screens/auth/individual_register_screen.dart';
import 'screens/auth/business_owner_register_screen.dart';
import 'screens/auth/garage_shop_register_screen.dart';
import 'screens/auth/pending_approval_screen.dart';
import 'screens/home/home_screen.dart';
import 'screens/proforma/create_proforma_screen.dart';
import 'screens/business_owner/bo_proforma_detail_screen.dart';
import 'screens/garage/garage_my_file_detail_screen.dart';
import 'screens/others/proforma_detail_screen.dart';
import 'screens/shared/notifications_screen.dart';
import 'screens/shared/received_proforma_detail_screen.dart';

void main() async {
  final binding = WidgetsFlutterBinding.ensureInitialized();
  FlutterNativeSplash.preserve(widgetsBinding: binding);

  // Init Firebase (gracefully — requires google-services.json to activate push)
  try {
    await Firebase.initializeApp();
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
    await NotificationService.init();
  } catch (_) {
    // Firebase not configured — in-app notifications still work via polling
  }

  final auth = AuthProvider();
  final restored = await auth.tryRestoreSession();
  final startRoute =
      (restored && auth.user != null && auth.user!.approved) ? '/home' : '/login';

  // If restored, register FCM token and cache role for notification routing
  if (restored && auth.user != null) {
    NotificationService.setUserRole(auth.user!.role);
    NotificationService.registerToken();
  }

  FlutterNativeSplash.remove();
  runApp(EteraApp(auth: auth, startRoute: startRoute));
}

class EteraApp extends StatelessWidget {
  final AuthProvider auth;
  final String startRoute;

  const EteraApp({super.key, required this.auth, required this.startRoute});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider.value(
      value: auth,
      child: MaterialApp(
        title: 'etera',
        debugShowCheckedModeBanner: false,
        theme: EteraTheme.lightTheme,
        navigatorKey: notificationNavigatorKey,
        initialRoute: startRoute,
        routes: {
          '/login': (_) => const LoginScreen(),
          '/register': (_) => const RoleSelectionScreen(),
          '/register/individual': (_) => const IndividualRegisterScreen(),
          '/register/business-owner': (_) => const BusinessOwnerRegisterScreen(),
          '/register/garage-shop': (_) => const GarageShopRegisterScreen(),
          '/pending': (_) => const PendingApprovalScreen(),
          '/home': (_) => const HomeScreen(),
          '/create-proforma': (_) => const CreateProformaScreen(),
          '/proforma-detail': (_) => const ProformaDetailScreen(),
          '/bo-proforma-detail': (_) => const BOProformaDetailScreen(),
          '/garage-file-detail': (_) => const GarageMyFileDetailScreen(),
          '/notifications':          (_) => const NotificationsScreen(),
          '/shop-proforma-detail':    (ctx) {
            final id = ModalRoute.of(ctx)!.settings.arguments as int;
            return ShopProformaDetailScreen(proformaId: id);
          },
          '/admin-proforma-detail':   (ctx) {
            final id = ModalRoute.of(ctx)!.settings.arguments as int;
            return AdminProformaDetailScreen(proformaId: id);
          },
          '/insurance-proforma-detail': (ctx) {
            final id = ModalRoute.of(ctx)!.settings.arguments as int;
            return InsuranceProformaDetailScreen(proformaId: id);
          },
          '/garage-inbox-detail': (ctx) {
            final id = ModalRoute.of(ctx)!.settings.arguments as int;
            return GarageInboxDetailScreen(proformaId: id);
          },
          '/admin-approvals': (_) => const NotificationsScreen(),
          '/received-proforma-detail': (ctx) {
            final args = ModalRoute.of(ctx)!.settings.arguments
                as Map<String, dynamic>;
            return ReceivedProformaDetailScreen(
              proformaId: args['id'] as int,
              detailUrl: args['url'] as String,
            );
          },
        },
      ),
    );
  }
}
