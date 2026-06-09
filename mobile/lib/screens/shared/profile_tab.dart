import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/user.dart';
import '../../providers/auth_provider.dart';
import '../../services/others_service.dart';
import '../../widgets/etera_button.dart';
import '../../widgets/etera_card.dart';
import '../../widgets/etera_text_field.dart';

class SharedProfileTab extends StatefulWidget {
  const SharedProfileTab({super.key});

  @override
  State<SharedProfileTab> createState() => _SharedProfileTabState();
}

class _SharedProfileTabState extends State<SharedProfileTab> {
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final result = await OthersService.getProfile();
    if (!mounted) return;
    if (result.error == 'unauthorized') {
      _handleUnauthorized();
      return;
    }
    if (result.user != null) {
      context.read<AuthProvider>().setUser(result.user!);
    }
    setState(() {
      _loading = false;
      _error = result.error;
    });
  }

  void _handleUnauthorized() {
    context.read<AuthProvider>().logout();
    Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
  }

  Future<void> _logout() async {
    await context.read<AuthProvider>().logout();
    if (!mounted) return;
    Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
  }

  void _openEditSheet(User user) {
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
            _load();
          },
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    final width = MediaQuery.of(context).size.width;
    final hPad = width > 600 ? width * 0.12 : 20.0;
    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: EdgeInsets.fromLTRB(hPad, 20, hPad, 40),
        child: Column(
          children: [
            const SizedBox(height: 8),

            // Avatar
            Container(
              width: 84,
              height: 84,
              decoration: BoxDecoration(
                gradient: EteraTheme.primaryGradient,
                shape: BoxShape.circle,
              ),
              child: Center(
                child: Text(
                  (user?.name ?? 'U')[0].toUpperCase(),
                  style: const TextStyle(
                    fontSize: 34, fontWeight: FontWeight.w700, color: Colors.white,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 12),
            Text(user?.name ?? '—', style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 4),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
              decoration: BoxDecoration(
                color: EteraTheme.green.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                user?.roleLabel ?? '',
                style: const TextStyle(
                  fontSize: 12, fontWeight: FontWeight.w600, color: EteraTheme.green,
                ),
              ),
            ),
            const SizedBox(height: 24),

            if (_loading)
              const Center(child: CircularProgressIndicator(color: EteraTheme.green))
            else if (_error != null)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: EteraTheme.error.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.error_outline, color: EteraTheme.error, size: 18),
                    const SizedBox(width: 8),
                    Expanded(child: Text(_error!, style: const TextStyle(fontSize: 13))),
                    TextButton(onPressed: _load, child: const Text('Retry')),
                  ],
                ),
              )
            else
              EteraCard(
                child: Column(
                  children: [
                    _row(Icons.phone, 'Phone', user?.phoneNumber ?? '—'),
                    const Divider(height: 24),
                    _row(Icons.email_outlined, 'Email', user?.email ?? 'Not set'),
                    const Divider(height: 24),
                    _row(Icons.location_on_outlined, 'Location', user?.location ?? 'Not set'),
                    if (user?.storeId != null) ...[
                      const Divider(height: 24),
                      _row(Icons.store_outlined, 'Store ID', user!.storeId!),
                    ],
                    if (user?.tinNumber != null) ...[
                      const Divider(height: 24),
                      _row(Icons.receipt_outlined, 'TIN Number', user!.tinNumber!),
                    ],
                    const Divider(height: 24),
                    _row(Icons.account_balance_wallet_outlined, 'Balance',
                        '${(user?.balance ?? 0).toStringAsFixed(2)} Birr'),
                  ],
                ),
              ),

            const SizedBox(height: 20),

            if (user != null && !_loading)
              SizedBox(
                width: double.infinity,
                child: EteraButton(
                  label: 'Edit Profile',
                  icon: Icons.edit_outlined,
                  isOutline: true,
                  onPressed: () => _openEditSheet(user),
                ),
              ),

            const SizedBox(height: 12),

            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _logout,
                icon: const Icon(Icons.logout, size: 18, color: EteraTheme.error),
                label: const Text('Logout', style: TextStyle(color: EteraTheme.error)),
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: EteraTheme.error),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
              ),
            ),
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }

  Widget _row(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, color: EteraTheme.green, size: 20),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
              Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
            ],
          ),
        ),
      ],
    );
  }
}

// ─── Edit profile bottom sheet ────────────────────────────────────
class _EditProfileSheet extends StatefulWidget {
  final User user;
  final VoidCallback onSaved;

  const _EditProfileSheet({required this.user, required this.onSaved});

  @override
  State<_EditProfileSheet> createState() => _EditProfileSheetState();
}

class _EditProfileSheetState extends State<_EditProfileSheet> {
  final _formKey = GlobalKey<FormState>();
  late final _nameCtrl = TextEditingController(text: widget.user.name);
  late final _emailCtrl = TextEditingController(text: widget.user.email ?? '');
  late final _locationCtrl = TextEditingController(text: widget.user.location ?? '');
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
      final msg = res['message'] ?? 'Update failed';
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(msg.toString()),
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
            EteraTextField(
              label: 'Full Name',
              hint: 'Enter your name',
              controller: _nameCtrl,
              validator: (v) => v == null || v.isEmpty ? 'Required' : null,
            ),
            const SizedBox(height: 16),
            EteraTextField(
              label: 'Email (Optional)',
              hint: 'your@email.com',
              controller: _emailCtrl,
              keyboardType: TextInputType.emailAddress,
            ),
            const SizedBox(height: 16),
            EteraTextField(
              label: 'Location (Optional)',
              hint: 'Enter your location',
              controller: _locationCtrl,
            ),
            const SizedBox(height: 24),
            EteraButton(
              label: 'Save Changes',
              loading: _saving,
              onPressed: _save,
            ),
          ],
        ),
      ),
    );
  }
}
