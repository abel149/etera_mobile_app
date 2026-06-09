import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'config/theme.dart';
import 'providers/auth_provider.dart';
import 'screens/splash_screen.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/role_selection_screen.dart';
import 'screens/auth/individual_register_screen.dart';
import 'screens/auth/business_owner_register_screen.dart';
import 'screens/auth/garage_shop_register_screen.dart';
import 'screens/auth/pending_approval_screen.dart';
import 'screens/home/home_screen.dart';
import 'screens/proforma/create_proforma_screen.dart';
import 'screens/others/proforma_detail_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const EteraApp());
}

class EteraApp extends StatelessWidget {
  const EteraApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => AuthProvider(),
      child: MaterialApp(
        title: 'E-Tera',
        debugShowCheckedModeBanner: false,
        theme: EteraTheme.lightTheme,
        initialRoute: '/',
        routes: {
          '/': (_) => const SplashScreen(),
          '/login': (_) => const LoginScreen(),
          '/register': (_) => const RoleSelectionScreen(),
          '/register/individual': (_) => const IndividualRegisterScreen(),
          '/register/business-owner': (_) => const BusinessOwnerRegisterScreen(),
          '/register/garage-shop': (_) => const GarageShopRegisterScreen(),
          '/pending': (_) => const PendingApprovalScreen(),
          '/home': (_) => const HomeScreen(),
          '/create-proforma': (_) => const CreateProformaScreen(),
          '/proforma-detail': (_) => const ProformaDetailScreen(),
        },
      ),
    );
  }
}
