import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/superadmin_service.dart';
import '../../widgets/etera_card.dart';

class SuperadminProformasTab extends StatefulWidget {
  const SuperadminProformasTab({super.key});

  @override
  State<SuperadminProformasTab> createState() => _SuperadminProformasTabState();
}

class _SuperadminProformasTabState extends State<SuperadminProformasTab> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _proformas = [];
  String? _statusFilter;

  final _statuses      = [null, 'pending', 'published', 'closed', 'completed'];
  final _statusLabels  = ['All', 'Pending', 'Published', 'Closed', 'Completed'];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await SuperadminService.getProformas();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = (res['data'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      setState(() { _loading = false; _proformas = raw; });
    } else {
      setState(() {
        _loading = false;
        _error   = res['message']?.toString() ?? 'Failed to load proformas';
      });
    }
  }

  List<Map<String, dynamic>> get _filtered {
    if (_statusFilter == null) return _proformas;
    return _proformas.where((p) => p['status'] == _statusFilter).toList();
  }

  Future<void> _float(Map<String, dynamic> p, int index) async {
    final res = await SuperadminService.floatProforma(p['id'] as int);
    if (!mounted) return;
    _snack(res['message']?.toString() ?? (res['success'] == true ? 'Floated!' : 'Failed'),
        res['success'] == true);
    if (res['success'] == true) {
      setState(() {
        final all = List<Map<String, dynamic>>.from(_proformas);
        final idx = all.indexWhere((x) => x['id'] == p['id']);
        if (idx != -1) all[idx] = {...all[idx], 'status': 'published'};
        _proformas = all;
      });
    }
  }

  Future<void> _close(Map<String, dynamic> p) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Close Proforma'),
        content: Text('Close proforma ${p['file_number']}?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Close', style: TextStyle(color: EteraTheme.error)),
          ),
        ],
      ),
    ) ?? false;
    if (!ok) return;
    final res = await SuperadminService.closeProforma(p['id'] as int);
    if (!mounted) return;
    _snack(res['message']?.toString() ?? (res['success'] == true ? 'Closed' : 'Failed'),
        res['success'] == true);
    if (res['success'] == true) {
      setState(() {
        final all = List<Map<String, dynamic>>.from(_proformas);
        final idx = all.indexWhere((x) => x['id'] == p['id']);
        if (idx != -1) all[idx] = {...all[idx], 'status': 'closed'};
        _proformas = all;
      });
    }
  }

  void _snack(String msg, bool ok) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: ok ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      // ── Status filter ─────────────────────────────────────────
      SizedBox(
        height: 48,
        child: ListView.separated(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          itemCount: _statuses.length,
          separatorBuilder: (_, __) => const SizedBox(width: 8),
          itemBuilder: (_, i) {
            final sel = _statusFilter == _statuses[i];
            return ChoiceChip(
              label: Text(_statusLabels[i]),
              selected: sel,
              selectedColor: Colors.deepPurple,
              labelStyle: TextStyle(
                color: sel ? Colors.white : EteraTheme.textMuted,
                fontSize: 12,
                fontWeight: sel ? FontWeight.w600 : FontWeight.normal,
              ),
              onSelected: (_) => setState(() => _statusFilter = _statuses[i]),
            );
          },
        ),
      ),
      Expanded(child: _body()),
    ]);
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator(color: Colors.deepPurple));
    if (_error != null) {
      return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.wifi_off, size: 48, color: EteraTheme.textMuted),
        const SizedBox(height: 12),
        Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: _load, child: const Text('Retry')),
      ]));
    }
    final items = _filtered;
    if (items.isEmpty) {
      return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
        Icon(Icons.receipt_long_outlined,
            size: 64, color: Colors.deepPurple.withValues(alpha: 0.3)),
        const SizedBox(height: 16),
        const Text('No proformas', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
      ]));
    }
    return RefreshIndicator(
      color: Colors.deepPurple,
      onRefresh: _load,
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(16, 4, 16, 32),
        itemCount: items.length,
        itemBuilder: (_, i) {
          final p = items[i];
          return _ProformaCard(
            proforma: p,
            onFloat: () => _float(p, i),
            onClose: () => _close(p),
          );
        },
      ),
    );
  }
}

// ─── Proforma Card ────────────────────────────────────────────────────────────
class _ProformaCard extends StatelessWidget {
  final Map<String, dynamic> proforma;
  final VoidCallback onFloat;
  final VoidCallback onClose;
  const _ProformaCard({required this.proforma, required this.onFloat, required this.onClose});

  @override
  Widget build(BuildContext context) {
    final status      = proforma['status']?.toString() ?? 'pending';
    final statusColor = _statusColor(status);
    final canFloat    = status == 'pending';
    final canClose    = status == 'published';

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(
              color: Colors.deepPurple.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.description_outlined, size: 20, color: Colors.deepPurple),
          ),
          const SizedBox(width: 10),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(proforma['file_number']?.toString() ?? 'N/A',
                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
            Text(proforma['customer_name']?.toString() ?? '',
                style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ])),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: statusColor.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(status.toUpperCase(),
                style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: statusColor)),
          ),
        ]),
        if ((proforma['from']?.toString() ?? '').isNotEmpty) ...[
          const SizedBox(height: 6),
          Row(children: [
            const Icon(Icons.person_outline, size: 13, color: EteraTheme.textMuted),
            const SizedBox(width: 4),
            Text('From: ${proforma['from']}',
                style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ]),
        ],
        if ((proforma['model']?.toString() ?? '').isNotEmpty) ...[
          const SizedBox(height: 2),
          Row(children: [
            const Icon(Icons.directions_car_outlined, size: 13, color: EteraTheme.textMuted),
            const SizedBox(width: 4),
            Text('${proforma['model']} ${proforma['year'] ?? ''}',
                style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ]),
        ],
        if (canFloat || canClose) ...[
          const SizedBox(height: 10),
          const Divider(height: 1),
          const SizedBox(height: 8),
          Row(children: [
            if (canFloat)
              Expanded(child: ElevatedButton(
                onPressed: onFloat,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.deepPurple,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(vertical: 8),
                ),
                child: const Text('Float', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
              )),
            if (canClose)
              Expanded(child: OutlinedButton(
                onPressed: onClose,
                style: OutlinedButton.styleFrom(
                  foregroundColor: EteraTheme.error,
                  side: const BorderSide(color: EteraTheme.error),
                  padding: const EdgeInsets.symmetric(vertical: 8),
                ),
                child: const Text('Close', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
              )),
          ]),
        ],
      ]),
    );
  }

  Color _statusColor(String s) {
    switch (s) {
      case 'pending':   return Colors.orange;
      case 'published': return Colors.deepPurple;
      case 'closed':    return EteraTheme.error;
      case 'completed': return EteraTheme.green;
      default:          return EteraTheme.textMuted;
    }
  }
}
