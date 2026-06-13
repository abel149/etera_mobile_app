import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/superadmin_service.dart';
import '../../widgets/etera_card.dart';
import '../../widgets/etera_text_field.dart';
import '../../widgets/etera_button.dart';

class SuperadminBrandsTab extends StatefulWidget {
  const SuperadminBrandsTab({super.key});

  @override
  State<SuperadminBrandsTab> createState() => _SuperadminBrandsTabState();
}

class _SuperadminBrandsTabState extends State<SuperadminBrandsTab> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _brands = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await SuperadminService.getBrands();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = (res['data']['brands'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      setState(() { _loading = false; _brands = raw; });
    } else {
      setState(() {
        _loading = false;
        _error   = res['message']?.toString() ?? 'Failed to load brands';
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
        child: _BrandFormSheet(
          onSaved: () { Navigator.pop(context); _load(); },
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      color: Colors.amber,
      onRefresh: _load,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
              child: Row(children: [
                const Expanded(
                  child: Text('Brands',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
                ),
                FilledButton.icon(
                  onPressed: _openCreate,
                  style: FilledButton.styleFrom(
                    backgroundColor: Colors.amber,
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  ),
                  icon: const Icon(Icons.add, size: 18),
                  label: const Text('New Brand',
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
                  child: CircularProgressIndicator(color: Colors.amber),
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
          else if (_brands.isEmpty)
            const SliverFillRemaining(child: Center(
              child: Text('No brands found', style: TextStyle(color: EteraTheme.textMuted)),
            ))
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (_, i) {
                    final b = _brands[i];
                    return _BrandCard(brand: b);
                  },
                  childCount: _brands.length,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

// ─── Brand Card ────────────────────────────────────────────────────────────────
class _BrandCard extends StatelessWidget {
  final Map<String, dynamic> brand;
  const _BrandCard({required this.brand});

  @override
  Widget build(BuildContext context) {
    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Row(children: [
        Container(
          width: 42, height: 42,
          decoration: BoxDecoration(
            color: Colors.amber.withValues(alpha: 0.12),
            shape: BoxShape.circle,
          ),
          child: Center(child: Text(
            (brand['name']?.toString() ?? 'B')[0].toUpperCase(),
            style: const TextStyle(fontWeight: FontWeight.w700, color: Colors.amber, fontSize: 18),
          )),
        ),
        const SizedBox(width: 12),
        Expanded(child: Text(brand['name']?.toString() ?? '—',
            style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14))),
        const SizedBox(width: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
          decoration: BoxDecoration(
            color: Colors.amber.withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(8),
          ),
          child: const Text('Brand',
              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.amber)),
        ),
      ]),
    );
  }
}

// ─── Brand Form Sheet (Create) ───────────────────────────────────────────────────
class _BrandFormSheet extends StatefulWidget {
  final VoidCallback onSaved;
  const _BrandFormSheet({required this.onSaved});

  @override
  State<_BrandFormSheet> createState() => _BrandFormSheetState();
}

class _BrandFormSheetState extends State<_BrandFormSheet> {
  final _formKey   = GlobalKey<FormState>();
  late final TextEditingController _nameCtrl;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _nameCtrl = TextEditingController();
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final res = await SuperadminService.createBrand(_nameCtrl.text.trim());

    if (!mounted) return;
    setState(() => _saving = false);

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(res['message']?.toString() ?? 'Brand created'),
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
          const Text('Create New Brand',
              style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
          const SizedBox(height: 20),
          EteraTextField(
            label: 'Brand Name', hint: 'Enter brand name',
            controller: _nameCtrl,
            validator: (v) => (v == null || v.isEmpty) ? 'Required' : null,
          ),
          const SizedBox(height: 24),
          EteraButton(
            label: 'Create Brand',
            loading: _saving,
            onPressed: _save,
          ),
        ]),
      ),
    );
  }
}
