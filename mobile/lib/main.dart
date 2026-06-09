import 'package:flutter/material.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'package:provider/provider.dart';
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
import 'screens/others/proforma_detail_screen.dart';

void main() async {
  final binding = WidgetsFlutterBinding.ensureInitialized();
  // Keep native splash (icon screen) visible while we check auth
  FlutterNativeSplash.preserve(widgetsBinding: binding);

  final auth = AuthProvider();
  final restored = await auth.tryRestoreSession();
  final startRoute =
      (restored && auth.user != null && auth.user!.approved) ? '/home' : '/login';

  // Auth done — dismiss native splash, show app immediately
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
        },
      ),
    );
  }
}
