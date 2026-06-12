import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/admin_service.dart';
import '../../widgets/etera_button.dart';
import '../../widgets/etera_card.dart';
import '../../widgets/etera_text_field.dart';

class AdminManagementTab extends StatefulWidget {
  const AdminManagementTab({super.key});

  @override
  State<AdminManagementTab> createState() => _AdminManagementTabState();
}

class _AdminManagementTabState extends State<AdminManagementTab> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _admins = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await AdminService.getAdmins();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = (res['data'] as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
      setState(() { _loading = false; _admins = raw; });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed'; });
    }
  }

  void _openCreateSheet() {
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
        child: _CreateAdminSheet(onCreated: () {
          Navigator.pop(context);
          _load();
        }),
      ),
    );
  }

  Future<void> _delete(Map<String, dynamic> admin) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Delete Admin'),
        content: Text('Delete admin "${admin['name']}"? This cannot be undone.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Delete', style: TextStyle(color: EteraTheme.error)),
          ),
        ],
      ),
    );
    if (confirmed != true) return;

    final res = await AdminService.deleteAdmin(admin['id'] as int);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res['message']?.toString() ?? 'Done'),
      backgroundColor: res['success'] == true ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
    if (res['success'] == true) _load();
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final isSuperAdmin = user?.role == 'superadmin';

    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: _load,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          if (!isSuperAdmin)
            SliverFillRemaining(
              child: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.lock_outline, size: 64, color: EteraTheme.textMuted),
                    const SizedBox(height: 16),
                    const Text('Superadmin Access Required',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 8),
                    const Text('Only superadmins can manage admins.',
                        style: TextStyle(color: EteraTheme.textMuted)),
                  ],
                ),
              ),
            )
          else ...[
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
                child: Row(
                  children: [
                    const Expanded(
                      child: Text('Admin Accounts',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
                    ),
                    FilledButton.icon(
                      onPressed: _openCreateSheet,
                      style: FilledButton.styleFrom(
                        backgroundColor: EteraTheme.green,
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      ),
                      icon: const Icon(Icons.add, size: 18),
                      label: const Text('New Admin', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                    ),
                  ],
                ),
              ),
            ),
            const SliverToBoxAdapter(child: SizedBox(height: 12)),
            if (_loading)
              const SliverToBoxAdapter(
                  child: Center(child: CircularProgressIndicator(color: EteraTheme.green)))
            else if (_error != null)
              SliverFillRemaining(
                child: Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.wifi_off, size: 48, color: EteraTheme.textMuted),
                      const SizedBox(height: 12),
                      Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
                      const SizedBox(height: 16),
                      ElevatedButton(onPressed: _load, child: const Text('Retry')),
                    ],
                  ),
                ),
              )
            else if (_admins.isEmpty)
              const SliverFillRemaining(
                child: Center(
                  child: Text('No admins found', style: TextStyle(color: EteraTheme.textMuted)),
                ),
              )
            else
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (_, i) {
                      final a = _admins[i];
                      final isSelf = a['id'] == user?.id;
                      final isSA   = a['role'] == 'superadmin';
                      return _AdminCard(
                        admin: a,
                        canDelete: !isSelf && !isSA,
                        onDelete: () => _delete(a),
                      );
                    },
                    childCount: _admins.length,
                  ),
                ),
              ),
          ],
        ],
      ),
    );
  }
}

// ─── Admin Card ──────────────────────────────────────────────────────────────

class _AdminCard extends StatelessWidget {
  final Map<String, dynamic> admin;
  final bool canDelete;
  final VoidCallback onDelete;

  const _AdminCard({required this.admin, required this.canDelete, required this.onDelete});

  @override
  Widget build(BuildContext context) {
    final isSA = admin['role'] == 'superadmin';
    final color = isSA ? Colors.deepPurple : EteraTheme.teal;

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Row(
        children: [
          Container(
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Text(
                (admin['name']?.toString() ?? 'A')[0].toUpperCase(),
                style: TextStyle(fontWeight: FontWeight.w700, color: color, fontSize: 18),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(admin['name']?.toString() ?? '—',
                    style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                Text(admin['phone_number']?.toString() ?? '',
                    style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                if ((admin['email']?.toString() ?? '').isNotEmpty)
                  Text(admin['email']!,
                      style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted),
                      overflow: TextOverflow.ellipsis),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  isSA ? 'Superadmin' : 'Admin',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: color),
                ),
              ),
              if (canDelete) ...[
                const SizedBox(height: 6),
                GestureDetector(
                  onTap: onDelete,
                  child: const Icon(Icons.delete_outline, size: 20, color: EteraTheme.error),
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }
}

// ─── Create Admin Sheet ───────────────────────────────────────────────────────

class _CreateAdminSheet extends StatefulWidget {
  final VoidCallback onCreated;
  const _CreateAdminSheet({required this.onCreated});

  @override
  State<_CreateAdminSheet> createState() => _CreateAdminSheetState();
}

class _CreateAdminSheetState extends State<_CreateAdminSheet> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl  = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  bool _saving = false;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _emailCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final res = await AdminService.createAdmin(
      name:        _nameCtrl.text.trim(),
      phoneNumber: _phoneCtrl.text.trim(),
      email:       _emailCtrl.text.trim().isEmpty ? null : _emailCtrl.text.trim(),
    );

    if (!mounted) return;
    setState(() => _saving = false);

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(res['message']?.toString() ?? 'Admin created'),
        backgroundColor: EteraTheme.green,
        behavior: SnackBarBehavior.floating,
      ));
      widget.onCreated();
    } else {
      final errors = res['errors'];
      String msg;
      if (errors is Map) {
        msg = errors.values.expand((v) => v is List ? v : [v]).join('\n');
      } else {
        msg = res['message']?.toString() ?? 'Failed to create admin';
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(msg),
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
            const Text('Create New Admin',
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
            const SizedBox(height: 4),
            const Text('Default password: 123456',
                style: TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
            const SizedBox(height: 20),
            EteraTextField(
              label: 'Full Name',
              hint: 'Enter full name',
              controller: _nameCtrl,
              validator: (v) => (v == null || v.isEmpty) ? 'Required' : null,
            ),
            const SizedBox(height: 14),
            EteraTextField(
              label: 'Phone Number',
              hint: '09XXXXXXXX',
              controller: _phoneCtrl,
              keyboardType: TextInputType.phone,
              validator: (v) {
                if (v == null || v.isEmpty) return 'Required';
                if (!RegExp(r'^\d{10}$').hasMatch(v)) return 'Enter a 10-digit phone number';
                return null;
              },
            ),
            const SizedBox(height: 14),
            EteraTextField(
              label: 'Email (Optional)',
              hint: 'admin@example.com',
              controller: _emailCtrl,
              keyboardType: TextInputType.emailAddress,
            ),
            const SizedBox(height: 24),
            EteraButton(
              label: 'Create Admin',
              loading: _saving,
              onPressed: _save,
            ),
          ],
        ),
      ),
    );
  }
}
