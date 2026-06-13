import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/superadmin_service.dart';
import '../../widgets/etera_card.dart';
import '../../widgets/etera_text_field.dart';
import '../../widgets/etera_button.dart';

class SuperadminMarketersTab extends StatefulWidget {
  const SuperadminMarketersTab({super.key});

  @override
  State<SuperadminMarketersTab> createState() => _SuperadminMarketersTabState();
}

class _SuperadminMarketersTabState extends State<SuperadminMarketersTab> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _marketers = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await SuperadminService.getMarketers();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = (res['data']['marketers'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      setState(() { _loading = false; _marketers = raw; });
    } else {
      setState(() {
        _loading = false;
        _error   = res['message']?.toString() ?? 'Failed to load marketers';
      });
    }
  }

  void _openCreate() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => SafeArea(
        top: false,
        child: _MarketerFormSheet(
          onSaved: () { Navigator.pop(context); _load(); },
        ),
      ),
    );
  }

  void _openEdit(Map<String, dynamic> marketer) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => SafeArea(
        top: false,
        child: _MarketerFormSheet(
          marketer: marketer,
          onSaved: () { Navigator.pop(context); _load(); },
        ),
      ),
    );
  }

  Future<void> _delete(Map<String, dynamic> marketer) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Delete Marketer'),
        content: Text('Delete marketer "${marketer['name']}"? This cannot be undone.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Delete', style: TextStyle(color: EteraTheme.error)),
          ),
        ],
      ),
    ) ?? false;
    if (!ok) return;
    final res = await SuperadminService.deleteMarketer(marketer['id'] as int);
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
    return RefreshIndicator(
      color: Colors.purple,
      onRefresh: _load,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
              child: Row(children: [
                const Expanded(
                  child: Text('Marketers',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
                ),
                FilledButton.icon(
                  onPressed: _openCreate,
                  style: FilledButton.styleFrom(
                    backgroundColor: Colors.purple,
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  ),
                  icon: const Icon(Icons.add, size: 18),
                  label: const Text('New Marketer',
                      style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                ),
              ]),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 8)),
          if (_loading)
            const SliverToBoxAdapter(
                child: Center(child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(color: Colors.purple),
                )))
          else if (_error != null)
            SliverFillRemaining(child: Center(child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.wifi_off, size: 48, color: EteraTheme.textMuted),
                const SizedBox(height: 12),
                Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
                const SizedBox(height: 16),
                ElevatedButton(onPressed: _load, child: const Text('Retry')),
              ],
            )))
          else if (_marketers.isEmpty)
            const SliverFillRemaining(child: Center(
              child: Text('No marketers found', style: TextStyle(color: EteraTheme.textMuted)),
            ))
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (_, i) {
                    final m = _marketers[i];
                    return _MarketerCard(
                      marketer: m,
                      onEdit:   () => _openEdit(m),
                      onDelete: () => _delete(m),
                    );
                  },
                  childCount: _marketers.length,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

// ─── Marketer Card ───────────────────────────────────────────────────────────────
class _MarketerCard extends StatelessWidget {
  final Map<String, dynamic> marketer;
  final VoidCallback onEdit;
  final VoidCallback onDelete;
  const _MarketerCard({required this.marketer, required this.onEdit, required this.onDelete});

  @override
  Widget build(BuildContext context) {
    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Row(children: [
        Container(
          width: 42, height: 42,
          decoration: BoxDecoration(
            color: Colors.purple.withValues(alpha: 0.12),
            shape: BoxShape.circle,
          ),
          child: Center(child: Text(
            (marketer['name']?.toString() ?? 'M')[0].toUpperCase(),
            style: const TextStyle(fontWeight: FontWeight.w700, color: Colors.purple, fontSize: 18),
          )),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(marketer['name']?.toString() ?? '—',
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
          Text(marketer['phone_number']?.toString() ?? '',
              style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          if ((marketer['email']?.toString() ?? '').isNotEmpty)
            Text(marketer['email']!,
                style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
        ])),
        const SizedBox(width: 8),
        Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
            decoration: BoxDecoration(
              color: Colors.purple.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Text('Marketer',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.purple)),
          ),
          const SizedBox(height: 6),
          Row(mainAxisSize: MainAxisSize.min, children: [
            GestureDetector(
              onTap: onEdit,
              child: const Padding(
                padding: EdgeInsets.all(4),
                child: Icon(Icons.edit_outlined, size: 18, color: Colors.purple),
              ),
            ),
            GestureDetector(
              onTap: onDelete,
              child: const Padding(
                padding: EdgeInsets.all(4),
                child: Icon(Icons.delete_outline, size: 18, color: EteraTheme.error),
              ),
            ),
          ]),
        ]),
      ]),
    );
  }
}

// ─── Marketer Form Sheet (Create / Edit) ───────────────────────────────────────────
class _MarketerFormSheet extends StatefulWidget {
  final Map<String, dynamic>? marketer;
  final VoidCallback onSaved;
  const _MarketerFormSheet({this.marketer, required this.onSaved});

  @override
  State<_MarketerFormSheet> createState() => _MarketerFormSheetState();
}

class _MarketerFormSheetState extends State<_MarketerFormSheet> {
  final _formKey   = GlobalKey<FormState>();
  late final TextEditingController _nameCtrl;
  late final TextEditingController _phoneCtrl;
  late final TextEditingController _emailCtrl;
  late final TextEditingController _passCtrl;
  bool _saving = false;

  bool get _isEdit => widget.marketer != null;

  @override
  void initState() {
    super.initState();
    _nameCtrl  = TextEditingController(text: widget.marketer?['name']?.toString() ?? '');
    _phoneCtrl = TextEditingController(text: widget.marketer?['phone_number']?.toString() ?? '');
    _emailCtrl = TextEditingController(text: widget.marketer?['email']?.toString() ?? '');
    _passCtrl  = TextEditingController();
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final Map<String, dynamic> res;
    if (_isEdit) {
      res = await SuperadminService.updateMarketer(
        widget.marketer!['id'] as int,
        name:        _nameCtrl.text.trim(),
        phoneNumber: _phoneCtrl.text.trim(),
        email:       _emailCtrl.text.trim().isEmpty ? null : _emailCtrl.text.trim(),
      );
    } else {
      res = await SuperadminService.createMarketer(
        name:        _nameCtrl.text.trim(),
        phoneNumber: _phoneCtrl.text.trim(),
        email:       _emailCtrl.text.trim().isEmpty ? null : _emailCtrl.text.trim(),
        password:    _passCtrl.text.trim().isEmpty ? null : _passCtrl.text.trim(),
      );
    }

    if (!mounted) return;
    setState(() => _saving = false);

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(res['message']?.toString() ?? (_isEdit ? 'Updated!' : 'Marketer created')),
        backgroundColor: EteraTheme.green,
        behavior: SnackBarBehavior.floating,
      ));
      widget.onSaved();
    } else {
      final errors = res['errors'];
      String msg;
      if (errors is Map) {
        msg = errors.values.expand((v) => v is List ? v : [v]).join('\n');
      } else {
        msg = res['message']?.toString() ?? 'Failed';
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
        child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          Center(child: Container(
            width: 40, height: 4,
            decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)),
          )),
          const SizedBox(height: 16),
          Text(_isEdit ? 'Edit Marketer' : 'Create New Marketer',
              style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
          if (!_isEdit)
            const Text('Default password: 123456',
                style: TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          const SizedBox(height: 20),
          EteraTextField(
            label: 'Full Name', hint: 'Enter full name',
            controller: _nameCtrl,
            validator: (v) => (v == null || v.isEmpty) ? 'Required' : null,
          ),
          const SizedBox(height: 14),
          EteraTextField(
            label: 'Phone Number', hint: '09XXXXXXXX',
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
            label: 'Email (Optional)', hint: 'marketer@example.com',
            controller: _emailCtrl,
            keyboardType: TextInputType.emailAddress,
          ),
          if (!_isEdit) ...[
            const SizedBox(height: 14),
            EteraTextField(
              label: 'Password (Optional)', hint: 'Leave empty for default 123456',
              controller: _passCtrl,
              obscureText: true,
            ),
          ],
          const SizedBox(height: 24),
          EteraButton(
            label: _isEdit ? 'Save Changes' : 'Create Marketer',
            loading: _saving,
            onPressed: _save,
          ),
        ]),
      ),
    );
  }
}
