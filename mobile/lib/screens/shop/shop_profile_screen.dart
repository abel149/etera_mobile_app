import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/shop_service.dart';
import '../../widgets/etera_card.dart';
import '../../widgets/notification_bell.dart';

class ShopProfileScreen extends StatefulWidget {
  const ShopProfileScreen({super.key});

  @override
  State<ShopProfileScreen> createState() => _ShopProfileScreenState();
}

class _ShopProfileScreenState extends State<ShopProfileScreen> {
  bool _loadingEmployees = true;
  List<dynamic> _employees = [];
  bool _addingEmployee = false;

  final _nameCtrl  = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _passCtrl  = TextEditingController();
  final _formKey   = GlobalKey<FormState>();

  @override
  void initState() {
    super.initState();
    _loadEmployees();
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadEmployees() async {
    setState(() => _loadingEmployees = true);
    final res = await ShopService.getEmployees();
    if (!mounted) return;
    setState(() {
      _loadingEmployees = false;
      _employees = res['data'] is List ? res['data'] as List : [];
    });
  }

  Future<void> _addEmployee() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _addingEmployee = true);
    final res = await ShopService.createEmployee({
      'name':     _nameCtrl.text.trim(),
      'phone':    _phoneCtrl.text.trim(),
      'password': _passCtrl.text.trim(),
    });
    if (!mounted) return;
    setState(() => _addingEmployee = false);
    Navigator.pop(context);
    if (res['success'] == true) {
      _nameCtrl.clear(); _phoneCtrl.clear(); _passCtrl.clear();
      _loadEmployees();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Employee added.'), backgroundColor: EteraTheme.green, behavior: SnackBarBehavior.floating),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message']?.toString() ?? 'Failed'), backgroundColor: EteraTheme.error, behavior: SnackBarBehavior.floating),
      );
    }
  }

  Future<void> _deleteEmployee(int id) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Remove Employee'),
        content: const Text('Are you sure you want to remove this employee?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(onPressed: () => Navigator.pop(context, true),
              child: const Text('Remove', style: TextStyle(color: EteraTheme.error))),
        ],
      ),
    );
    if (ok != true || !mounted) return;
    final res = await ShopService.deleteEmployee(id);
    if (!mounted) return;
    if (res['success'] == true) {
      _loadEmployees();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message']?.toString() ?? 'Failed'), backgroundColor: EteraTheme.error),
      );
    }
  }

  void _showAddEmployeeSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => Padding(
        padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 24),
        child: Form(
          key: _formKey,
          child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
            Container(width: 40, height: 4, margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2))),
            const Text('Add Employee', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
            const SizedBox(height: 16),
            TextFormField(
              controller: _nameCtrl,
              decoration: const InputDecoration(labelText: 'Full Name', prefixIcon: Icon(Icons.person_outline)),
              validator: (v) => (v == null || v.trim().isEmpty) ? 'Required' : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _phoneCtrl,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(labelText: 'Phone Number', prefixIcon: Icon(Icons.phone_outlined)),
              validator: (v) => (v == null || v.trim().isEmpty) ? 'Required' : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _passCtrl,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'Password', prefixIcon: Icon(Icons.lock_outline)),
              validator: (v) => (v == null || v.trim().length < 6) ? 'Min 6 characters' : null,
            ),
            const SizedBox(height: 20),
            SizedBox(width: double.infinity, child: ElevatedButton(
              onPressed: _addingEmployee ? null : _addEmployee,
              style: ElevatedButton.styleFrom(
                backgroundColor: EteraTheme.green,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 13),
              ),
              child: _addingEmployee
                  ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('Add Employee', style: TextStyle(fontWeight: FontWeight.w600)),
            )),
          ]),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    return Scaffold(
      body: RefreshIndicator(
        color: EteraTheme.green,
        onRefresh: _loadEmployees,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 40),
          children: [
            // ── Profile card ───────────────────────────────────────────
            EteraCard(
              child: Column(children: [
                CircleAvatar(
                  radius: 36,
                  backgroundColor: EteraTheme.green.withValues(alpha: 0.15),
                  child: Text(
                    (user?.name ?? 'S')[0].toUpperCase(),
                    style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: EteraTheme.green),
                  ),
                ),
                const SizedBox(height: 12),
                Text(user?.name ?? '—',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                  decoration: BoxDecoration(
                    color: EteraTheme.green.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Text('Spare Part Shop',
                      style: TextStyle(fontSize: 12, color: EteraTheme.green, fontWeight: FontWeight.w600)),
                ),
                const SizedBox(height: 16),
                const Divider(height: 1),
                const SizedBox(height: 12),
                _ProfileRow(icon: Icons.phone_outlined,   label: 'Phone',  value: user?.phoneNumber ?? '—'),
                if ((user?.email ?? '').isNotEmpty)
                  _ProfileRow(icon: Icons.email_outlined,   label: 'Email',  value: user!.email!),
                _ProfileRow(
                  icon: Icons.verified_outlined,
                  label: 'Status',
                  value: user?.approved == true ? 'Approved' : 'Pending',
                  valueColor: user?.approved == true ? EteraTheme.green : Colors.orange,
                ),
              ]),
            ),
            const SizedBox(height: 24),

            // ── Employees ──────────────────────────────────────────────
            Row(children: [
              const Text('Employees', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
              const Spacer(),
              TextButton.icon(
                onPressed: _showAddEmployeeSheet,
                icon: const Icon(Icons.add, size: 18),
                label: const Text('Add'),
                style: TextButton.styleFrom(foregroundColor: EteraTheme.green),
              ),
            ]),
            const SizedBox(height: 8),
            if (_loadingEmployees)
              const Center(child: Padding(
                padding: EdgeInsets.all(24),
                child: CircularProgressIndicator(color: EteraTheme.green),
              ))
            else if (_employees.isEmpty)
              EteraCard(
                child: const Row(children: [
                  Icon(Icons.group_outlined, color: EteraTheme.textMuted),
                  SizedBox(width: 12),
                  Text('No employees yet.', style: TextStyle(color: EteraTheme.textMuted)),
                ]),
              )
            else
              ...(_employees.map((e) {
                final emp = Map<String, dynamic>.from(e as Map);
                final empId = emp['id'] as int? ?? 0;
                return EteraCard(
                  margin: const EdgeInsets.only(bottom: 8),
                  child: Row(children: [
                    CircleAvatar(
                      radius: 18,
                      backgroundColor: EteraTheme.green.withValues(alpha: 0.12),
                      child: Text(
                        (emp['name']?.toString() ?? 'E')[0].toUpperCase(),
                        style: const TextStyle(color: EteraTheme.green, fontWeight: FontWeight.w700),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(emp['name']?.toString() ?? '—',
                          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                      if ((emp['phone']?.toString() ?? '').isNotEmpty)
                        Text(emp['phone'].toString(),
                            style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                    ])),
                    IconButton(
                      icon: const Icon(Icons.delete_outline, color: EteraTheme.error, size: 20),
                      onPressed: () => _deleteEmployee(empId),
                      tooltip: 'Remove',
                    ),
                  ]),
                );
              })),
            const SizedBox(height: 24),

            // ── Logout ────────────────────────────────────────────────
            Row(children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () async {
                    await context.read<AuthProvider>().logout();
                    if (context.mounted) {
                      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
                    }
                  },
                  icon: const Icon(Icons.logout, color: EteraTheme.error),
                  label: const Text('Logout', style: TextStyle(color: EteraTheme.error, fontWeight: FontWeight.w600)),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: EteraTheme.error),
                    padding: const EdgeInsets.symmetric(vertical: 13),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              const NotificationBell(),
            ]),
          ],
        ),
      ),
    );
  }
}

class _ProfileRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final Color? valueColor;
  const _ProfileRow({required this.icon, required this.label, required this.value, this.valueColor});

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 5),
    child: Row(children: [
      Icon(icon, size: 16, color: EteraTheme.textMuted),
      const SizedBox(width: 10),
      Text('$label: ', style: const TextStyle(fontSize: 13, color: EteraTheme.textMuted)),
      Expanded(child: Text(value,
          style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: valueColor))),
    ]),
  );
}
