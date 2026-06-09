import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../business_owner/business_owner_home_screen.dart';
import '../garage/garage_home_screen.dart';
import '../others/others_home_screen.dart';

/// Routes to the correct home screen based on the authenticated user's role.
/// Add new role screens here as they are built.
class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    switch (user?.role) {
      case 'others':
      case 'individual':
        return const OthersHomeScreen();
      case 'business_owner':
      case 'employee':
        return const BusinessOwnerHomeScreen();
      case 'garage':
      case 'shop':
        return const GarageHomeScreen();
      default:
        return _ComingSoonScreen(role: user?.roleLabel ?? user?.role ?? 'Unknown');
    }
  }
}

// ─── Placeholder for roles not yet implemented ────────────────────
class _ComingSoonScreen extends StatelessWidget {
  final String role;
  const _ComingSoonScreen({required this.role});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('etera'),
        automaticallyImplyLeading: false,
        actions: [
          TextButton.icon(
            onPressed: () async {
              await context.read<AuthProvider>().logout();
              if (context.mounted) {
                Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
              }
            },
            icon: const Icon(Icons.logout, size: 18, color: EteraTheme.error),
            label: const Text('Logout', style: TextStyle(color: EteraTheme.error)),
          ),
        ],
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  gradient: EteraTheme.primaryGradient,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Icon(Icons.construction_outlined, color: Colors.white, size: 40),
              ),
              const SizedBox(height: 24),
              Text(
                'Dashboard coming soon',
                style: Theme.of(context).textTheme.titleLarge,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                'The $role dashboard is under development.',
                style: const TextStyle(color: EteraTheme.textMuted),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
