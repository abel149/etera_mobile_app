import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/notification_bell.dart';
import 'insurance_balance_tab.dart';
import 'insurance_dashboard_tab.dart';
import 'insurance_partners_tab.dart';
import 'insurance_proformas_tab.dart';

class InsuranceHomeScreen extends StatefulWidget {
  const InsuranceHomeScreen({super.key});

  @override
  State<InsuranceHomeScreen> createState() => _InsuranceHomeScreenState();
}

class _InsuranceHomeScreenState extends State<InsuranceHomeScreen> {
  int _currentIndex = 0;
  final _refreshNotifier = ValueNotifier<int>(0);

  @override
  void dispose() {
    _refreshNotifier.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    final tabs = [
      InsuranceDashboardTab(
        refreshTrigger: _refreshNotifier,
        onGoToProformas: () => setState(() => _currentIndex = 1),
      ),
      InsuranceProformasTab(refreshTrigger: _refreshNotifier),
      const InsuranceBalanceTab(),
      const InsurancePartnersTab(),
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
        label: 'Files',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.account_balance_wallet_outlined),
        activeIcon: Icon(Icons.account_balance_wallet),
        label: 'Balance',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.people_outline),
        activeIcon: Icon(Icons.people),
        label: 'Partners',
      ),
    ];

    final showFab = _currentIndex == 0 || _currentIndex == 1;

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
                      fontSize: 14, fontWeight: FontWeight.w700, color: EteraTheme.green),
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
                child: Row(children: [
                  Icon(Icons.logout, size: 18, color: EteraTheme.error),
                  const SizedBox(width: 10),
                  Text('Logout',
                      style: TextStyle(color: EteraTheme.error, fontWeight: FontWeight.w600)),
                ]),
              ),
            ],
          ),
        ],
      ),
      body: IndexedStack(index: _currentIndex, children: tabs),
      floatingActionButton: showFab
          ? FloatingActionButton.extended(
              heroTag: 'ins_fab',
              onPressed: () async {
                await Navigator.pushNamed(context, '/create-proforma');
                _refreshNotifier.value++;
              },
              backgroundColor: EteraTheme.green,
              foregroundColor: Colors.white,
              icon: const Icon(Icons.add),
              label: const Text('New File', style: TextStyle(fontWeight: FontWeight.w600)),
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
