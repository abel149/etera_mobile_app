import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../widgets/etera_card.dart';

class _RoleOption {
  final String id;
  final String title;
  final String subtitle;
  final IconData icon;
  final String route;

  const _RoleOption({
    required this.id,
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.route,
  });
}

class RoleSelectionScreen extends StatelessWidget {
  const RoleSelectionScreen({super.key});

  static const _roles = [
    _RoleOption(
      id: 'individual',
      title: 'Individual',
      subtitle: 'Vehicle owner seeking parts',
      icon: Icons.person_outline,
      route: '/register/individual',
    ),
    _RoleOption(
      id: 'business_owner',
      title: 'Business Owner',
      subtitle: 'Garage or fleet manager',
      icon: Icons.business_center_outlined,
      route: '/register/business-owner',
    ),
    _RoleOption(
      id: 'garage',
      title: 'Garage',
      subtitle: 'Auto repair service',
      icon: Icons.build_outlined,
      route: '/register/garage-shop',
    ),
    _RoleOption(
      id: 'shop',
      title: 'Spare Part Shop',
      subtitle: 'Parts supplier & distributor',
      icon: Icons.store_outlined,
      route: '/register/garage-shop',
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Create Account'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Colors.white, Color(0xFFF9FAFB)],
          ),
        ),
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Choose Your Role',
                  style: Theme.of(context).textTheme.headlineMedium,
                ),
                const SizedBox(height: 8),
                Text(
                  'Select the type of account that best describes you',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: EteraTheme.textMuted,
                      ),
                ),
                const SizedBox(height: 28),

                ..._roles.map((role) => Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: _RoleCard(role: role),
                    )),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _RoleCard extends StatelessWidget {
  final _RoleOption role;
  const _RoleCard({required this.role});

  @override
  Widget build(BuildContext context) {
    return EteraCard(
      padding: const EdgeInsets.all(0),
      margin: EdgeInsets.zero,
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(EteraTheme.radiusLg),
          onTap: () {
            if (role.id == 'garage' || role.id == 'shop') {
              Navigator.pushNamed(context, role.route, arguments: role.id);
            } else {
              Navigator.pushNamed(context, role.route);
            }
          },
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Row(
              children: [
                Container(
                  width: 52,
                  height: 52,
                  decoration: BoxDecoration(
                    gradient: EteraTheme.primaryGradient,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(role.icon, color: Colors.white, size: 26),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        role.title,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                          color: EteraTheme.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        role.subtitle,
                        style: const TextStyle(
                          fontSize: 13,
                          color: EteraTheme.textMuted,
                        ),
                      ),
                    ],
                  ),
                ),
                const Icon(
                  Icons.arrow_forward_ios,
                  size: 16,
                  color: EteraTheme.textMuted,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
