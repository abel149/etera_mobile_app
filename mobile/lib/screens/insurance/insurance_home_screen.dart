import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/others_service.dart';
import '../../widgets/notification_bell.dart';
import 'in_billing_tab.dart';
import 'in_employees_tab.dart';
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

  void _showProfileDialog() {
    final user = context.read<AuthProvider>().user;
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Profile'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 24,
                  backgroundColor: EteraTheme.green.withValues(alpha: 0.15),
                  child: Text(
                    (user?.name ?? 'U')[0].toUpperCase(),
                    style: const TextStyle(
                        fontSize: 18, fontWeight: FontWeight.w700, color: EteraTheme.green),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(user?.name ?? '—', style: const TextStyle(fontWeight: FontWeight.w600)),
                      Text(user?.role ?? '—', style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            _profileRow('Phone', user?.phoneNumber ?? '—'),
            _profileRow('Email', user?.email ?? '—'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _openEditProfileSheet(user);
            },
            style: ElevatedButton.styleFrom(backgroundColor: EteraTheme.green),
            child: const Text('Edit', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _openEditProfileSheet(user) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => SafeArea(
        top: false,
        child: _EditProfileSheet(
          user: user,
          onSaved: () {
            Navigator.pop(context);
            _refreshNotifier.value++;
          },
        ),
      ),
    );
  }

  Widget _profileRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }

  String _getTabTitle(int index) {
    switch (index) {
      case 4:
        return 'Billing';
      case 5:
        return 'Employees';
      default:
        return 'etera';
    }
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
      const InsuranceBillingTab(),
      const InsuranceEmployeesTab(),
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
        title: Text(_currentIndex >= 4 ? _getTabTitle(_currentIndex) : 'etera'),
        automaticallyImplyLeading: _currentIndex >= 4,
        leading: _currentIndex >= 4
            ? IconButton(
                icon: const Icon(Icons.arrow_back_ios, size: 20),
                onPressed: () => setState(() => _currentIndex = 0),
              )
            : null,
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
              if (value == 'billing') {
                setState(() => _currentIndex = 4);
              } else if (value == 'employees') {
                setState(() => _currentIndex = 5);
              } else if (value == 'profile') {
                _showProfileDialog();
              } else if (value == 'logout') {
                await context.read<AuthProvider>().logout();
                if (context.mounted) {
                  Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
                }
              }
            },
            itemBuilder: (_) => [
              PopupMenuItem(
                value: 'billing',
                child: Row(children: [
                  Icon(Icons.receipt_outlined, size: 18, color: EteraTheme.green),
                  const SizedBox(width: 10),
                  const Text('Billing', style: TextStyle(fontWeight: FontWeight.w600)),
                ]),
              ),
              PopupMenuItem(
                value: 'employees',
                child: Row(children: [
                  Icon(Icons.people_outline, size: 18, color: EteraTheme.green),
                  const SizedBox(width: 10),
                  const Text('Employees', style: TextStyle(fontWeight: FontWeight.w600)),
                ]),
              ),
              PopupMenuItem(
                value: 'profile',
                child: Row(children: [
                  Icon(Icons.person_outline, size: 18, color: EteraTheme.green),
                  const SizedBox(width: 10),
                  const Text('Profile', style: TextStyle(fontWeight: FontWeight.w600)),
                ]),
              ),
              const PopupMenuDivider(),
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
                await Navigator.pushNamed(context, '/insurance-create-proforma');
                _refreshNotifier.value++;
              },
              backgroundColor: EteraTheme.green,
              foregroundColor: Colors.white,
              icon: const Icon(Icons.add),
              label: const Text('New File', style: TextStyle(fontWeight: FontWeight.w600)),
            )
          : null,
      bottomNavigationBar: _currentIndex < 4
          ? BottomNavigationBar(
              currentIndex: _currentIndex,
              onTap: (i) => setState(() => _currentIndex = i),
              items: navItems,
              selectedItemColor: EteraTheme.green,
              unselectedItemColor: EteraTheme.textMuted,
              type: BottomNavigationBarType.fixed,
            )
          : null,
    );
  }
}

// ─── Edit profile bottom sheet ────────────────────────────────────
class _EditProfileSheet extends StatefulWidget {
  final dynamic user;
  final VoidCallback onSaved;

  const _EditProfileSheet({required this.user, required this.onSaved});

  @override
  State<_EditProfileSheet> createState() => _EditProfileSheetState();
}

class _EditProfileSheetState extends State<_EditProfileSheet> {
  final _formKey = GlobalKey<FormState>();
  late final _nameCtrl = TextEditingController(text: widget.user?.name ?? '');
  late final _emailCtrl = TextEditingController(text: widget.user?.email ?? '');
  late final _locationCtrl = TextEditingController(text: widget.user?.location ?? '');
  bool _saving = false;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _locationCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final res = await OthersService.updateProfile(
      name: _nameCtrl.text.trim(),
      email: _emailCtrl.text.trim().isEmpty ? null : _emailCtrl.text.trim(),
      location: _locationCtrl.text.trim().isEmpty ? null : _locationCtrl.text.trim(),
    );

    if (!mounted) return;
    setState(() => _saving = false);

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Profile updated successfully'),
        backgroundColor: EteraTheme.green,
        behavior: SnackBarBehavior.floating,
      ));
      widget.onSaved();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(res['message']?.toString() ?? 'Update failed'),
        backgroundColor: EteraTheme.error,
        behavior: SnackBarBehavior.floating,
      ));
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottom = MediaQuery.of(context).viewInsets.bottom;
    return SingleChildScrollView(
      reverse: true,
      padding: EdgeInsets.fromLTRB(20, 16, 20, bottom + 24),
      child: Form(
        key: _formKey,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Center(
              child: Container(
                width: 40, height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),
            Text('Edit Profile', style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 20),
            TextFormField(
              controller: _nameCtrl,
              decoration: const InputDecoration(
                labelText: 'Full Name',
                border: OutlineInputBorder(),
              ),
              validator: (v) => v == null || v.isEmpty ? 'Required' : null,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _emailCtrl,
              decoration: const InputDecoration(
                labelText: 'Email (Optional)',
                border: OutlineInputBorder(),
              ),
              keyboardType: TextInputType.emailAddress,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _locationCtrl,
              decoration: const InputDecoration(
                labelText: 'Location (Optional)',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _save,
              style: ElevatedButton.styleFrom(
                backgroundColor: EteraTheme.green,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
              child: _saving
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation(Colors.white),
                      ),
                    )
                  : const Text('Save Changes', style: TextStyle(fontWeight: FontWeight.w600)),
            ),
          ],
        ),
      ),
    );
  }
}
