import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../shared/profile_tab.dart';
import 'bo_billing_tab.dart';
import 'bo_dashboard_tab.dart';
import 'bo_employees_tab.dart';
import 'bo_proformas_tab.dart';

class BusinessOwnerHomeScreen extends StatefulWidget {
  const BusinessOwnerHomeScreen({super.key});

  @override
  State<BusinessOwnerHomeScreen> createState() =>
      _BusinessOwnerHomeScreenState();
}

class _BusinessOwnerHomeScreenState extends State<BusinessOwnerHomeScreen> {
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
      BODashboardTab(
        onGoToProformas: () => _goToTab(1),
        onGoToEmployees: () => _goToTab(2),
        onGoToBilling: () => _goToTab(3),
        refreshTrigger: _refreshNotifier,
      ),
      BOProformasTab(refreshTrigger: _refreshNotifier),
      const BOEmployeesTab(),
      const BOBillingTab(),
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
        icon: Icon(Icons.group_outlined),
        activeIcon: Icon(Icons.group),
        label: 'Employees',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.account_balance_outlined),
        activeIcon: Icon(Icons.account_balance),
        label: 'Billing',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.person_outline),
        activeIcon: Icon(Icons.person),
        label: 'Profile',
      ),
    ];

    // Show FAB (New Request) on Dashboard and Proformas tabs only
    final showFab = _currentIndex == 0 || _currentIndex == 1;

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
      floatingActionButton: showFab
          ? FloatingActionButton.extended(
              heroTag: 'bo_fab',
              onPressed: () async {
                await Navigator.pushNamed(context, '/create-proforma');
                _refreshNotifier.value++;
              },
              backgroundColor: EteraTheme.green,
              foregroundColor: Colors.white,
              icon: const Icon(Icons.add),
              label: const Text('New Request',
                  style: TextStyle(fontWeight: FontWeight.w600)),
            )
          : null,
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
