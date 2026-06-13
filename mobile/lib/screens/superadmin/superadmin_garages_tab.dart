import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/superadmin_service.dart';
import '../../widgets/etera_card.dart';
import '../../widgets/etera_text_field.dart';
import '../../widgets/etera_button.dart';

class SuperadminGaragesTab extends StatefulWidget {
  const SuperadminGaragesTab({super.key});

  @override
  State<SuperadminGaragesTab> createState() => _SuperadminGaragesTabState();
}

class _SuperadminGaragesTabState extends State<SuperadminGaragesTab> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _garages = [];
  String _search = '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await SuperadminService.getGarages(search: _search.isEmpty ? null : _search);
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = (res['data']['garages'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      setState(() { _loading = false; _garages = raw; });
    } else {
      setState(() {
        _loading = false;
        _error   = res['message']?.toString() ?? 'Failed to load garages';
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
        child: _GarageFormSheet(
          onSaved: () { Navigator.pop(context); _load(); },
        ),
      ),
    );
  }

  void _openEdit(Map<String, dynamic> garage) async {
    final res = await SuperadminService.getGarageDetail(garage['id'] as int);
    if (!mounted) return;
    if (res['success'] == true) {
      final garageData = Map<String, dynamic>.from(res['data']['garage'] as Map);
      showModalBottomSheet(
        context: context,
        isScrollControlled: true,
        useSafeArea: true,
        backgroundColor: Colors.white,
        shape: const RoundedRectangleBorder(
            borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
        builder: (_) => SafeArea(
          top: false,
          child: _GarageFormSheet(
            garage: garageData,
            onSaved: () { Navigator.pop(context); _load(); },
          ),
        ),
      );
    }
  }

  Future<void> _delete(Map<String, dynamic> garage) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Delete Garage'),
        content: Text('Delete garage "${garage['name']}"? This cannot be undone.'),
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
    final res = await SuperadminService.deleteGarage(garage['id'] as int);
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
    return Column(children: [
      // ── Search bar ───────────────────────────────────────────────
      Padding(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
        child: TextField(
          decoration: InputDecoration(
            hintText: 'Search garages...',
            prefixIcon: const Icon(Icons.search, size: 20),
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
          ),
          onChanged: (v) => _search = v,
          onSubmitted: (_) => _load(),
        ),
      ),
      Expanded(child: _body()),
    ]);
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator(color: EteraTheme.teal));
    if (_error != null) {
      return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.wifi_off, size: 48, color: EteraTheme.textMuted),
        const SizedBox(height: 12),
        Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: _load, child: const Text('Retry')),
      ]));
    }
    return RefreshIndicator(
      color: EteraTheme.teal,
      onRefresh: _load,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
              child: Row(children: [
                const Expanded(
                  child: Text('Garages',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
                ),
                FilledButton.icon(
                  onPressed: _openCreate,
                  style: FilledButton.styleFrom(
                    backgroundColor: EteraTheme.teal,
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  ),
                  icon: const Icon(Icons.add, size: 18),
                  label: const Text('New Garage',
                      style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                ),
              ]),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 8)),
          if (_garages.isEmpty)
            const SliverFillRemaining(child: Center(
              child: Text('No garages found', style: TextStyle(color: EteraTheme.textMuted)),
            ))
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (_, i) {
                    final g = _garages[i];
                    return _GarageCard(
                      garage: g,
                      onEdit:   () => _openEdit(g),
                      onDelete: () => _delete(g),
                    );
                  },
                  childCount: _garages.length,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

// ─── Garage Card ────────────────────────────────────────────────────────────────
class _GarageCard extends StatelessWidget {
  final Map<String, dynamic> garage;
  final VoidCallback onEdit;
  final VoidCallback onDelete;
  const _GarageCard({required this.garage, required this.onEdit, required this.onDelete});

  @override
  Widget build(BuildContext context) {
    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Row(children: [
        Container(
          width: 42, height: 42,
          decoration: BoxDecoration(
            color: EteraTheme.teal.withValues(alpha: 0.12),
            shape: BoxShape.circle,
          ),
          child: Center(child: Text(
            (garage['name']?.toString() ?? 'G')[0].toUpperCase(),
            style: const TextStyle(fontWeight: FontWeight.w700, color: EteraTheme.teal, fontSize: 18),
          )),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(garage['name']?.toString() ?? '—',
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
          Text(garage['phone_number']?.toString() ?? '',
              style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          if ((garage['location']?.toString() ?? '').isNotEmpty)
            Text(garage['location']!,
                style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
          if ((garage['tin_number']?.toString() ?? '').isNotEmpty)
            Text('TIN: ${garage['tin_number']}',
                style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
        ])),
        const SizedBox(width: 8),
        Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
            decoration: BoxDecoration(
              color: EteraTheme.teal.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Text('Garage',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: EteraTheme.teal)),
          ),
          const SizedBox(height: 6),
          Row(mainAxisSize: MainAxisSize.min, children: [
            GestureDetector(
              onTap: onEdit,
              child: const Padding(
                padding: EdgeInsets.all(4),
                child: Icon(Icons.edit_outlined, size: 18, color: EteraTheme.teal),
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

// ─── Garage Form Sheet (Create / Edit) ───────────────────────────────────────────
class _GarageFormSheet extends StatefulWidget {
  final Map<String, dynamic>? garage;
  final VoidCallback onSaved;
  const _GarageFormSheet({this.garage, required this.onSaved});

  @override
  State<_GarageFormSheet> createState() => _GarageFormSheetState();
}

class _GarageFormSheetState extends State<_GarageFormSheet> {
  final _formKey   = GlobalKey<FormState>();
  late final TextEditingController _nameCtrl;
  late final TextEditingController _phoneCtrl;
  late final TextEditingController _emailCtrl;
  late final TextEditingController _locationCtrl;
  late final TextEditingController _tinCtrl;
  late final TextEditingController _passCtrl;
  File? _licenseImage;
  File? _stampImage;
  bool _saving = false;

  bool get _isEdit => widget.garage != null;

  @override
  void initState() {
    super.initState();
    _nameCtrl    = TextEditingController(text: widget.garage?['name']?.toString() ?? '');
    _phoneCtrl   = TextEditingController(text: widget.garage?['phone_number']?.toString() ?? '');
    _emailCtrl   = TextEditingController(text: widget.garage?['email']?.toString() ?? '');
    _locationCtrl = TextEditingController(text: widget.garage?['location']?.toString() ?? '');
    _tinCtrl     = TextEditingController(text: widget.garage?['tin_number']?.toString() ?? '');
    _passCtrl    = TextEditingController();
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _emailCtrl.dispose();
    _locationCtrl.dispose();
    _tinCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickImage(bool isLicense) async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.gallery);
    if (picked != null && mounted) {
      setState(() {
        if (isLicense) _licenseImage = File(picked.path);
        else _stampImage = File(picked.path);
      });
    }
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final Map<String, dynamic> res;
    if (_isEdit) {
      res = await SuperadminService.updateGarage(
        widget.garage!['id'] as int,
        name:        _nameCtrl.text.trim(),
        phoneNumber: _phoneCtrl.text.trim(),
        location:    _locationCtrl.text.trim(),
        tinNumber:   _tinCtrl.text.trim(),
        email:       _emailCtrl.text.trim().isEmpty ? null : _emailCtrl.text.trim(),
        licenseImage: _licenseImage,
        stampImage:   _stampImage,
      );
    } else {
      res = await SuperadminService.createGarage(
        name:        _nameCtrl.text.trim(),
        phoneNumber: _phoneCtrl.text.trim(),
        location:    _locationCtrl.text.trim(),
        tinNumber:   _tinCtrl.text.trim(),
        email:       _emailCtrl.text.trim().isEmpty ? null : _emailCtrl.text.trim(),
        password:    _passCtrl.text.trim().isEmpty ? null : _passCtrl.text.trim(),
        licenseImage: _licenseImage,
        stampImage:   _stampImage,
      );
    }

    if (!mounted) return;
    setState(() => _saving = false);

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(res['message']?.toString() ?? (_isEdit ? 'Updated!' : 'Garage created')),
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
          Text(_isEdit ? 'Edit Garage' : 'Create New Garage',
              style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
          if (!_isEdit)
            const Text('Default password: 123456',
                style: TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          const SizedBox(height: 20),
          EteraTextField(
            label: 'Garage Name', hint: 'Enter garage name',
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
            label: 'Email (Optional)', hint: 'garage@example.com',
            controller: _emailCtrl,
            keyboardType: TextInputType.emailAddress,
          ),
          const SizedBox(height: 14),
          EteraTextField(
            label: 'Location', hint: 'City, Area',
            controller: _locationCtrl,
            validator: (v) => (v == null || v.isEmpty) ? 'Required' : null,
          ),
          const SizedBox(height: 14),
          EteraTextField(
            label: 'TIN Number', hint: 'Tax Identification Number',
            controller: _tinCtrl,
            validator: (v) => (v == null || v.isEmpty) ? 'Required' : null,
          ),
          if (!_isEdit) ...[
            const SizedBox(height: 14),
            EteraTextField(
              label: 'Password (Optional)', hint: 'Leave empty for default 123456',
              controller: _passCtrl,
              obscureText: true,
            ),
          ],
          const SizedBox(height: 16),
          _ImagePickerRow(
            label: 'License Image',
            file: _licenseImage,
            onPick: () => _pickImage(true),
          ),
          const SizedBox(height: 12),
          _ImagePickerRow(
            label: 'Stamp Image',
            file: _stampImage,
            onPick: () => _pickImage(false),
          ),
          const SizedBox(height: 24),
          EteraButton(
            label: _isEdit ? 'Save Changes' : 'Create Garage',
            loading: _saving,
            onPressed: _save,
          ),
        ]),
      ),
    );
  }
}

// ─── Image Picker Row ───────────────────────────────────────────────────────────
class _ImagePickerRow extends StatelessWidget {
  final String label;
  final File? file;
  final VoidCallback onPick;
  const _ImagePickerRow({required this.label, required this.file, required this.onPick});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onPick,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          border: Border.all(color: file != null ? EteraTheme.teal : Colors.grey.shade300),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Row(children: [
          Icon(Icons.image_outlined,
              size: 20, color: file != null ? EteraTheme.teal : Colors.grey),
          const SizedBox(width: 10),
          Expanded(child: Text(
            file != null ? file!.path.split('/').last : label,
            style: TextStyle(
              fontSize: 13,
              color: file != null ? EteraTheme.teal : EteraTheme.textMuted,
            ),
          )),
          if (file != null)
            Icon(Icons.check_circle, size: 18, color: EteraTheme.teal),
        ]),
      ),
    );
  }
}
