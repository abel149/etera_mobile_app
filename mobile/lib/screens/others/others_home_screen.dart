import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../shared/profile_tab.dart';
import 'dashboard_tab.dart';
import 'proformas_tab.dart';

class OthersHomeScreen extends StatefulWidget {
  const OthersHomeScreen({super.key});

  @override
  State<OthersHomeScreen> createState() => _OthersHomeScreenState();
}

class _OthersHomeScreenState extends State<OthersHomeScreen> {
  int _currentIndex = 0;

  static const _tabs = [
    OthersDashboardTab(),
    OthersProformasTab(),
    SharedProfileTab(),
  ];

  static const _navItems = [
    BottomNavigationBarItem(
      icon: Icon(Icons.dashboard_outlined),
      activeIcon: Icon(Icons.dashboard),
      label: 'Dashboard',
    ),
    BottomNavigationBarItem(
      icon: Icon(Icons.receipt_long_outlined),
      activeIcon: Icon(Icons.receipt_long),
      label: 'Proformas',
    ),
    BottomNavigationBarItem(
      icon: Icon(Icons.person_outline),
      activeIcon: Icon(Icons.person),
      label: 'Profile',
    ),
  ];

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final showFab = _currentIndex != 2; // hide FAB on profile tab

    return Scaffold(
      appBar: AppBar(
        title: const Text('E-Tera'),
        automaticallyImplyLeading: false,
        actions: [
          IconButton(
            tooltip: 'Notifications',
            icon: const Icon(Icons.notifications_outlined),
            onPressed: () {},
          ),
          PopupMenuButton<String>(
            tooltip: 'Menu',
            offset: const Offset(0, 48),
            child: Padding(
              padding: const EdgeInsets.only(right: 12),
              child: CircleAvatar(
                radius: 16,
                backgroundColor: EteraTheme.green.withValues(alpha: 0.15),
                child: Text(
                  (user?.name ?? 'U')[0].toUpperCase(),
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: EteraTheme.green,
                  ),
                ),
              ),
            ),
            onSelected: (value) async {
              if (value == 'logout') {
                await context.read<AuthProvider>().logout();
                if (context.mounted) {
                  Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
                }
              }
            },
            itemBuilder: (_) => [
              PopupMenuItem(
                value: 'logout',
                child: Row(
                  children: [
                    Icon(Icons.logout, size: 18, color: EteraTheme.error),
                    const SizedBox(width: 10),
                    Text('Logout', style: TextStyle(color: EteraTheme.error, fontWeight: FontWeight.w600)),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: IndexedStack(index: _currentIndex, children: _tabs),
      floatingActionButton: showFab
          ? FloatingActionButton.extended(
              heroTag: 'others_fab',
              onPressed: () async {
                await Navigator.pushNamed(context, '/create-proforma');
              },
              backgroundColor: EteraTheme.green,
              foregroundColor: Colors.white,
              icon: const Icon(Icons.add),
              label: const Text('New Request', style: TextStyle(fontWeight: FontWeight.w600)),
            )
          : null,
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (i) => setState(() => _currentIndex = i),
        items: _navItems,
        selectedItemColor: EteraTheme.green,
        unselectedItemColor: EteraTheme.textMuted,
        type: BottomNavigationBarType.fixed,
      ),
    );
  }
}
