import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../shared/profile_tab.dart';
import 'admin_approvals_tab.dart';
import 'admin_dashboard_tab.dart';
import 'admin_proformas_tab.dart';
import '../../widgets/notification_bell.dart';

class AdminHomeScreen extends StatefulWidget {
  const AdminHomeScreen({super.key});

  @override
  State<AdminHomeScreen> createState() =>
      _AdminHomeScreenState();
}

class _AdminHomeScreenState extends State<AdminHomeScreen> {
  int _currentIndex = 0;
  final _refreshNotifier = ValueNotifier<int>(0);

  @override
  void dispose() {
    _refreshNotifier.dispose();
    super.dispose();
  }

  void _goToTab(int index) => setState(() => _currentIndex = index);

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    final tabs = [
      AdminDashboardTab(
        onGoToProformas: () => _goToTab(1),
        onGoToApprovals: () => _goToTab(2),
        refreshTrigger: _refreshNotifier,
      ),
      const AdminProformasTab(),
      const AdminApprovalsTab(),
      const SharedProfileTab(),
    ];

    const navItems = [
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
        icon: Icon(Icons.how_to_reg_outlined),
        activeIcon: Icon(Icons.how_to_reg),
        label: 'Approvals',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.person_outline),
        activeIcon: Icon(Icons.person),
        label: 'Profile',
      ),
    ];

    return Scaffold(
      appBar: AppBar(
        title: const Text('etera'),
        automaticallyImplyLeading: false,
        actions: [
          const NotificationBell(),
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
                  Navigator.pushNamedAndRemoveUntil(
                      context, '/login', (r) => false);
                }
              }
            },
            itemBuilder: (_) => [
              PopupMenuItem(
                value: 'logout',
                child: Row(
                  children: [
                    Icon(Icons.logout,
                        size: 18, color: EteraTheme.error),
                    const SizedBox(width: 10),
                    Text('Logout',
                        style: TextStyle(
                            color: EteraTheme.error,
                            fontWeight: FontWeight.w600)),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: IndexedStack(index: _currentIndex, children: tabs),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (i) => setState(() => _currentIndex = i),
        items: navItems,
        selectedItemColor: EteraTheme.green,
        unselectedItemColor: EteraTheme.textMuted,
        type: BottomNavigationBarType.fixed,
      ),
    );
  }
}
