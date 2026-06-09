import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/notification_bell.dart';
import 'shop_applications_tab.dart';
import 'shop_balance_tab.dart';
import 'shop_dashboard_tab.dart';
import 'shop_inbox_tab.dart';
import 'shop_proformas_tab.dart';

class ShopHomeScreen extends StatefulWidget {
  const ShopHomeScreen({super.key});

  @override
  State<ShopHomeScreen> createState() => _ShopHomeScreenState();
}

class _ShopHomeScreenState extends State<ShopHomeScreen> {
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
      ShopDashboardTab(
        refreshTrigger: _refreshNotifier,
        onGoToInbox: () => setState(() => _currentIndex = 1),
        onGoToApplications: () => setState(() => _currentIndex = 3),
      ),
      ShopInboxTab(refreshTrigger: _refreshNotifier),
      ShopProformasTab(refreshTrigger: _refreshNotifier),
      ShopApplicationsTab(refreshTrigger: _refreshNotifier),
      ShopBalanceTab(),
    ];

    const navItems = [
      BottomNavigationBarItem(
        icon: Icon(Icons.dashboard_outlined),
        activeIcon: Icon(Icons.dashboard),
        label: 'Dashboard',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.inbox_outlined),
        activeIcon: Icon(Icons.inbox),
        label: 'Inbox',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.description_outlined),
        activeIcon: Icon(Icons.description),
        label: 'Proformas',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.receipt_long_outlined),
        activeIcon: Icon(Icons.receipt_long),
        label: 'My Bids',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.account_balance_wallet_outlined),
        activeIcon: Icon(Icons.account_balance_wallet),
        label: 'Balance',
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
                  Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
                }
              }
            },
            itemBuilder: (_) => [
              const PopupMenuItem(
                value: 'employees',
                child: Row(children: [
                  Icon(Icons.group_outlined, size: 18),
                  SizedBox(width: 10),
                  Text('Employees'),
                ]),
              ),
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
