import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/user.dart';
import '../../providers/auth_provider.dart';
import '../../services/insurance_service.dart';
import '../../widgets/etera_button.dart';
import '../../widgets/etera_card.dart';
import '../../widgets/etera_text_field.dart';

class InsuranceEmployeesTab extends StatefulWidget {
  const InsuranceEmployeesTab({super.key});

  @override
  State<InsuranceEmployeesTab> createState() => _InsuranceEmployeesTabState();
}

class _InsuranceEmployeesTabState extends State<InsuranceEmployeesTab> {
  bool _loading = true;
  List<User> _employees = [];
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final res = await InsuranceService.getEmployees();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = res['data'] as List? ?? [];
      setState(() {
        _loading = false;
        _employees = raw.map((e) => User.fromJson(e as Map<String, dynamic>)).toList();
        _error = null;
      });
    } else {
      setState(() {
        _loading = false;
        _error = res['message']?.toString() ?? 'Failed to load employees';
      });
    }
  }

  Future<void> _confirmDelete(User employee) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Remove Employee'),
        content: Text(
            'Are you sure you want to remove ${employee.name} from your team?'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Remove',
                style: TextStyle(color: EteraTheme.error)),
          ),
        ],
      ),
    );
    if (confirm != true || !mounted) return;

    final res = await InsuranceService.deleteEmployee(employee.id);
    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res['success'] == true
          ? 'Employee removed successfully.'
          : res['message'] ?? 'Failed to remove employee'),
      backgroundColor:
          res['success'] == true ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));

    if (res['success'] == true) _load();
  }

  void _openAddSheet() {
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
        child: _AddEmployeeSheet(
          onAdded: () {
            Navigator.pop(context);
            _load();
          },
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.transparent,
      floatingActionButton: FloatingActionButton.extended(
        heroTag: 'insurance_employees_fab',
        onPressed: _openAddSheet,
        backgroundColor: EteraTheme.green,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.person_add_outlined),
        label: const Text('Add Employee',
            style: TextStyle(fontWeight: FontWeight.w600)),
      ),
      body: RefreshIndicator(
        color: EteraTheme.green,
        onRefresh: _load,
        child: _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(
          child: CircularProgressIndicator(color: EteraTheme.green));
    }
    if (_error != null) {
      return Center(
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
      );
    }
    if (_employees.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.2),
          Center(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.group_outlined,
                    size: 64,
                    color: EteraTheme.green.withValues(alpha: 0.3)),
                const SizedBox(height: 16),
                const Text('No employees yet',
                    style: TextStyle(
                        fontSize: 16, fontWeight: FontWeight.w600)),
                const SizedBox(height: 8),
                const Text(
                  'Tap the button below to add your first employee',
                  style: TextStyle(
                      color: EteraTheme.textMuted, fontSize: 13),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ],
      );
    }

    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
      itemCount: _employees.length,
      itemBuilder: (context, i) {
        final emp = _employees[i];
        return EteraCard(
          margin: const EdgeInsets.only(bottom: 12),
          child: ListTile(
            contentPadding:
                const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            leading: CircleAvatar(
              radius: 22,
              backgroundColor: EteraTheme.green.withValues(alpha: 0.12),
              child: Text(
                emp.name[0].toUpperCase(),
                style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: EteraTheme.green),
              ),
            ),
            title: Text(emp.name,
                style: const TextStyle(fontWeight: FontWeight.w600)),
            subtitle: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 2),
                Text(emp.phoneNumber,
                    style: const TextStyle(
                        fontSize: 13, color: EteraTheme.textMuted)),
                if (emp.email != null)
                  Text(emp.email!,
                      style: const TextStyle(
                          fontSize: 12, color: EteraTheme.textMuted)),
              ],
            ),
            trailing: IconButton(
              icon: const Icon(Icons.delete_outline, color: EteraTheme.error),
              tooltip: 'Remove employee',
              onPressed: () => _confirmDelete(emp),
            ),
          ),
        );
      },
    );
  }
}

// ─── Add employee bottom sheet ─────────────────────────────────────
class _AddEmployeeSheet extends StatefulWidget {
  final VoidCallback onAdded;
  const _AddEmployeeSheet({required this.onAdded});

  @override
  State<_AddEmployeeSheet> createState() => _AddEmployeeSheetState();
}

class _AddEmployeeSheetState extends State<_AddEmployeeSheet> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _confirmPassCtrl = TextEditingController();
  bool _obscurePass = true;
  bool _obscureConfirm = true;
  bool _saving = false;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    _confirmPassCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final res = await InsuranceService.createEmployee({
      'name': _nameCtrl.text.trim(),
      'phone_number': _phoneCtrl.text.trim(),
      'email': _emailCtrl.text.trim().isEmpty ? null : _emailCtrl.text.trim(),
      'password': _passCtrl.text,
      'password_confirmation': _confirmPassCtrl.text,
    });

    if (!mounted) return;
    setState(() => _saving = false);

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Employee added successfully.'),
        backgroundColor: EteraTheme.green,
        behavior: SnackBarBehavior.floating,
      ));
      widget.onAdded();
    } else {
      final errors = res['errors'];
      String msg = res['message']?.toString() ?? 'Failed to create employee';
      if (errors is Map) {
        msg = (errors.values.first as List).first.toString();
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
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),
            Text('Add Employee', style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 20),

            EteraTextField(
              label: 'Full Name',
              hint: 'Employee name',
              controller: _nameCtrl,
              validator: (v) =>
                  v == null || v.trim().isEmpty ? 'Required' : null,
            ),
            const SizedBox(height: 14),

            EteraTextField(
              label: 'Phone Number',
              hint: '09xxxxxxxx',
              controller: _phoneCtrl,
              keyboardType: TextInputType.phone,
              validator: (v) {
                if (v == null || v.trim().isEmpty) return 'Required';
                if (!RegExp(r'^\d{10}$').hasMatch(v.trim())) {
                  return 'Must be 10 digits';
                }
                return null;
              },
            ),
            const SizedBox(height: 14),

            EteraTextField(
              label: 'Email (Optional)',
              hint: 'employee@email.com',
              controller: _emailCtrl,
              keyboardType: TextInputType.emailAddress,
            ),
            const SizedBox(height: 14),

            EteraTextField(
              label: 'Password',
              hint: 'Min 6 characters',
              controller: _passCtrl,
              obscureText: _obscurePass,
              suffixIcon: IconButton(
                icon: Icon(_obscurePass
                    ? Icons.visibility_off_outlined
                    : Icons.visibility_outlined),
                onPressed: () =>
                    setState(() => _obscurePass = !_obscurePass),
              ),
              validator: (v) {
                if (v == null || v.isEmpty) return 'Required';
                if (v.length < 6) return 'Minimum 6 characters';
                return null;
              },
            ),
            const SizedBox(height: 14),

            EteraTextField(
              label: 'Confirm Password',
              hint: 'Repeat password',
              controller: _confirmPassCtrl,
              obscureText: _obscureConfirm,
              suffixIcon: IconButton(
                icon: Icon(_obscureConfirm
                    ? Icons.visibility_off_outlined
                    : Icons.visibility_outlined),
                onPressed: () =>
                    setState(() => _obscureConfirm = !_obscureConfirm),
              ),
              validator: (v) {
                if (v == null || v.isEmpty) return 'Required';
                if (v != _passCtrl.text) return 'Passwords do not match';
                return null;
              },
            ),
            const SizedBox(height: 24),

            EteraButton(
              label: 'Add Employee',
              loading: _saving,
              onPressed: _submit,
            ),
          ],
        ),
      ),
    );
  }
}
