import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import 'garage_applications_tab.dart';
import 'garage_balance_tab.dart';
import 'garage_billing_screen.dart';
import 'garage_dashboard_tab.dart';
import 'garage_employees_screen.dart';
import 'garage_files_tab.dart';
import 'garage_inbox_tab.dart';
import '../../widgets/notification_bell.dart';

class GarageHomeScreen extends StatefulWidget {
  const GarageHomeScreen({super.key});

  @override
  State<GarageHomeScreen> createState() => _GarageHomeScreenState();
}

class _GarageHomeScreenState extends State<GarageHomeScreen> {
  int _currentIndex = 0;
  int _inboxCount = 0;
  final _refreshNotifier = ValueNotifier<int>(0);

  @override
  void dispose() {
    _refreshNotifier.dispose();
    super.dispose();
  }

  void _goToTab(int index) => setState(() => _currentIndex = index);

  void _updateInboxCount(int count) {
    if (_inboxCount != count) setState(() => _inboxCount = count);
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    final tabs = [
      GarageDashboardTab(
        onGoToInbox: () => _goToTab(1),
        onGoToBids: () => _goToTab(2),
        onGoToFiles: () => _goToTab(3),
        refreshTrigger: _refreshNotifier,
        onInboxCountLoaded: _updateInboxCount,
      ),
      GarageInboxTab(refreshTrigger: _refreshNotifier),
      GarageApplicationsTab(refreshTrigger: _refreshNotifier),
      GarageFilesTab(refreshTrigger: _refreshNotifier),
      const GarageBalanceTab(),
    ];

    // Show FAB on Dashboard (tab 0) and Files (tab 3)
    final showFab = _currentIndex == 0 || _currentIndex == 3;

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
              } else if (value == 'employees') {
                Navigator.push(context,
                    MaterialPageRoute(
                        builder: (_) => const GarageEmployeesScreen()));
              } else if (value == 'billing') {
                Navigator.push(context,
                    MaterialPageRoute(
                        builder: (_) => const GarageBillingScreen()));
              }
            },
            itemBuilder: (_) => [
              const PopupMenuItem(
                value: 'employees',
                child: Row(
                  children: [
                    Icon(Icons.group_outlined, size: 18),
                    SizedBox(width: 10),
                    Text('Employees'),
                  ],
                ),
              ),
              const PopupMenuItem(
                value: 'billing',
                child: Row(
                  children: [
                    Icon(Icons.receipt_long_outlined, size: 18),
                    SizedBox(width: 10),
                    Text('Billing'),
                  ],
                ),
              ),
              PopupMenuItem(
                value: 'logout',
                child: Row(
                  children: [
                    Icon(Icons.logout, size: 18, color: EteraTheme.error),
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
              heroTag: 'garage_fab',
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
        type: BottomNavigationBarType.fixed,
        selectedItemColor: EteraTheme.green,
        unselectedItemColor: EteraTheme.textMuted,
        selectedFontSize: 11,
        unselectedFontSize: 11,
        items: [
          const BottomNavigationBarItem(
            icon: Icon(Icons.dashboard_outlined),
            activeIcon: Icon(Icons.dashboard),
            label: 'Dashboard',
          ),
          BottomNavigationBarItem(
            icon: _inboxCount > 0
                ? Badge(
                    label: Text('$_inboxCount',
                        style: const TextStyle(fontSize: 10)),
                    child: const Icon(Icons.inbox_outlined),
                  )
                : const Icon(Icons.inbox_outlined),
            activeIcon: _inboxCount > 0
                ? Badge(
                    label: Text('$_inboxCount',
                        style: const TextStyle(fontSize: 10)),
                    child: const Icon(Icons.inbox),
                  )
                : const Icon(Icons.inbox),
            label: 'Inbox',
          ),
          const BottomNavigationBarItem(
            icon: Icon(Icons.how_to_vote_outlined),
            activeIcon: Icon(Icons.how_to_vote),
            label: 'Proformas',
          ),
          const BottomNavigationBarItem(
            icon: Icon(Icons.folder_outlined),
            activeIcon: Icon(Icons.folder),
            label: 'Files',
          ),
          const BottomNavigationBarItem(
            icon: Icon(Icons.account_balance_wallet_outlined),
            activeIcon: Icon(Icons.account_balance_wallet),
            label: 'Balance',
          ),
        ],
      ),
    );
  }
}
