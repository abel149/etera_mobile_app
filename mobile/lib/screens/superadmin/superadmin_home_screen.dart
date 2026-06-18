import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../shared/profile_tab.dart';
import '../../widgets/notification_bell.dart';
import 'superadmin_dashboard_tab.dart';
import 'superadmin_manage_tab.dart';
import 'superadmin_reports_tab.dart';
import '../admin/admin_proformas_tab.dart';

class SuperadminHomeScreen extends StatefulWidget {
  const SuperadminHomeScreen({super.key});

  @override
  State<SuperadminHomeScreen> createState() => _SuperadminHomeScreenState();
}

class _SuperadminHomeScreenState extends State<SuperadminHomeScreen> {
  int _currentIndex = 0;

  // Tracks active sub-section inside the hub tabs
  String? _manageSection;
  String? _reportsSection;

  void _goToManage(String section) =>
      setState(() { _currentIndex = 2; _manageSection = section; });

  void _goToReports(String section) =>
      setState(() { _currentIndex = 3; _reportsSection = section; });

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    final tabs = [
      SuperadminDashboardTab(
        onGoToProformas: () => setState(() => _currentIndex = 1),
        onGoToManage: _goToManage,
        onGoToReports: _goToReports,
      ),
      const AdminProformasTab(),
      SuperadminManageTab(
        activeSection: _manageSection,
        onSectionChanged: (s) => setState(() => _manageSection = s),
      ),
      SuperadminReportsTab(
        activeSection: _reportsSection,
        onSectionChanged: (s) => setState(() => _reportsSection = s),
      ),
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
        icon: Icon(Icons.apps_outlined),
        activeIcon: Icon(Icons.apps),
        label: 'Manage',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.bar_chart_outlined),
        activeIcon: Icon(Icons.bar_chart),
        label: 'Reports',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.person_outline),
        activeIcon: Icon(Icons.person),
        label: 'Profile',
      ),
    ];

    return PopScope(
      canPop: _manageSection == null && _reportsSection == null,
      onPopInvokedWithResult: (didPop, _) {
        if (!didPop) {
          setState(() {
            if (_currentIndex == 2) _manageSection = null;
            if (_currentIndex == 3) _reportsSection = null;
          });
        }
      },
      child: Scaffold(
        appBar: AppBar(
          title: Row(mainAxisSize: MainAxisSize.min, children: [
            Container(
              width: 28, height: 28,
              decoration: BoxDecoration(
                color: Colors.deepPurple.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(7),
              ),
              child: const Icon(Icons.admin_panel_settings, size: 16, color: Colors.deepPurple),
            ),
            const SizedBox(width: 8),
            const Text('etera', style: TextStyle(fontWeight: FontWeight.w800)),
          ]),
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
                  backgroundColor: Colors.deepPurple.withValues(alpha: 0.15),
                  child: Text(
                    (user?.name ?? 'S')[0].toUpperCase(),
                    style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: Colors.deepPurple),
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
        bottomNavigationBar: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: (i) => setState(() => _currentIndex = i),
          items: navItems,
          selectedItemColor:   Colors.deepPurple,
          unselectedItemColor: EteraTheme.textMuted,
          type: BottomNavigationBarType.fixed,
        ),
      ),
    );
  }
}
