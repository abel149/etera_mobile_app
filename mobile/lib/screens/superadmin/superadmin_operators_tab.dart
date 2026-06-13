import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/superadmin_service.dart';
import '../../widgets/etera_card.dart';
import '../../widgets/etera_text_field.dart';
import '../../widgets/etera_button.dart';

class SuperadminOperatorsTab extends StatefulWidget {
  const SuperadminOperatorsTab({super.key});

  @override
  State<SuperadminOperatorsTab> createState() => _SuperadminOperatorsTabState();
}

class _SuperadminOperatorsTabState extends State<SuperadminOperatorsTab> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _operators = [];
  List<Map<String, dynamic>> _managers = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await SuperadminService.getOperators();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final rawOps = (res['data']['operators'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      final rawMgrs = (res['data']['managers'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      setState(() { _loading = false; _operators = rawOps; _managers = rawMgrs; });
    } else {
      setState(() {
        _loading = false;
        _error   = res['message']?.toString() ?? 'Failed to load operators';
      });
    }
  }

  void _openManagerDialog(Map<String, dynamic> operator) {
    final currentManager = operator['my_manager']?['manager'];
    int? selectedManagerId = currentManager?['id'] as int?;

    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Assign Manager'),
        content: DropdownButtonFormField<int>(
          value: selectedManagerId,
          decoration: const InputDecoration(labelText: 'Select Manager'),
          items: _managers.map((m) {
            return DropdownMenuItem<int>(
              value: m['id'] as int,
              child: Text(m['name']?.toString() ?? '—'),
            );
          }).toList(),
          onChanged: (v) => selectedManagerId = v,
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          FilledButton(
            onPressed: () async {
              if (selectedManagerId == null) return;
              Navigator.pop(context);
              final res = await SuperadminService.assignManager(
                operator['id'] as int,
                selectedManagerId!,
              );
              if (!mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                content: Text(res['message']?.toString() ?? 'Done'),
                backgroundColor: res['success'] == true ? EteraTheme.green : EteraTheme.error,
                behavior: SnackBarBehavior.floating,
              ));
              if (res['success'] == true) _load();
            },
            child: const Text('Assign'),
          ),
        ],
      ),
    );
  }

  void _openQuotaDialog(Map<String, dynamic> operator) {
    final controller = TextEditingController(text: (operator['file_quota'] ?? 0).toString());

    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Set File Quota'),
        content: TextField(
          controller: controller,
          keyboardType: TextInputType.number,
          decoration: const InputDecoration(labelText: 'File Quota (0-1000)'),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          FilledButton(
            onPressed: () async {
              final quota = int.tryParse(controller.text);
              if (quota == null || quota < 0 || quota > 1000) return;
              Navigator.pop(context);
              final res = await SuperadminService.setQuota(operator['id'] as int, quota);
              if (!mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                content: Text(res['message']?.toString() ?? 'Done'),
                backgroundColor: res['success'] == true ? EteraTheme.green : EteraTheme.error,
                behavior: SnackBarBehavior.floating,
              ));
              if (res['success'] == true) _load();
            },
            child: const Text('Save'),
          ),
        ],
      ),
    );
  }

  void _openCommissionDialog(Map<String, dynamic> operator) {
    final controller = TextEditingController(text: (operator['commission_per_file'] ?? 0).toString());

    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Set Commission per File'),
        content: TextField(
          controller: controller,
          keyboardType: TextInputType.number,
          decoration: const InputDecoration(labelText: 'Commission per File'),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          FilledButton(
            onPressed: () async {
              final commission = double.tryParse(controller.text);
              if (commission == null || commission < 0) return;
              Navigator.pop(context);
              final res = await SuperadminService.setCommission(operator['id'] as int, commission);
              if (!mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                content: Text(res['message']?.toString() ?? 'Done'),
                backgroundColor: res['success'] == true ? EteraTheme.green : EteraTheme.error,
                behavior: SnackBarBehavior.floating,
              ));
              if (res['success'] == true) _load();
            },
            child: const Text('Save'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      color: Colors.orange,
      onRefresh: _load,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
              child: Row(children: [
                const Expanded(
                  child: Text('Operators',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
                ),
              ]),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 8)),
          if (_loading)
            const SliverToBoxAdapter(
                child: Center(child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(color: Colors.orange),
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
          else if (_operators.isEmpty)
            const SliverFillRemaining(child: Center(
              child: Text('No operators found', style: TextStyle(color: EteraTheme.textMuted)),
            ))
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (_, i) {
                    final op = _operators[i];
                    return _OperatorCard(
                      operator: op,
                      onAssignManager: () => _openManagerDialog(op),
                      onSetQuota: () => _openQuotaDialog(op),
                      onSetCommission: () => _openCommissionDialog(op),
                    );
                  },
                  childCount: _operators.length,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

// ─── Operator Card ───────────────────────────────────────────────────────────────
class _OperatorCard extends StatelessWidget {
  final Map<String, dynamic> operator;
  final VoidCallback onAssignManager;
  final VoidCallback onSetQuota;
  final VoidCallback onSetCommission;
  const _OperatorCard({
    required this.operator,
    required this.onAssignManager,
    required this.onSetQuota,
    required this.onSetCommission,
  });

  @override
  Widget build(BuildContext context) {
    final manager = operator['my_manager']?['manager'];
    final totalQuota = operator['total_quota'] ?? 0;
    final usedQuota = operator['used_quota'] ?? 0;
    final availableQuota = operator['available_quota'] ?? 0;
    final totalCommissions = operator['total_commissions'] ?? 0;

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            width: 42, height: 42,
            decoration: BoxDecoration(
              color: Colors.orange.withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: Center(child: Text(
              (operator['name']?.toString() ?? 'O')[0].toUpperCase(),
              style: const TextStyle(fontWeight: FontWeight.w700, color: Colors.orange, fontSize: 18),
            )),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(operator['name']?.toString() ?? '—',
                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
            Text(operator['phone_number']?.toString() ?? '',
                style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ])),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
            decoration: BoxDecoration(
              color: Colors.orange.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Text('Operator',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.orange)),
          ),
        ]),
        const SizedBox(height: 12),
        // Stats row
        Row(children: [
          Expanded(child: _StatItem(label: 'Quota', value: '$totalQuota', color: Colors.blue)),
          Expanded(child: _StatItem(label: 'Used', value: '$usedQuota', color: Colors.orange)),
          Expanded(child: _StatItem(label: 'Available', value: '$availableQuota', color: EteraTheme.green)),
        ]),
        const SizedBox(height: 8),
        // Manager row
        Row(children: [
          const Icon(Icons.person_outline, size: 16, color: EteraTheme.textMuted),
          const SizedBox(width: 6),
          Expanded(child: Text(
            manager != null ? 'Manager: ${manager['name']}' : 'No manager assigned',
            style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted),
          )),
          if (manager != null)
            const Icon(Icons.check_circle, size: 14, color: EteraTheme.green),
        ]),
        const SizedBox(height: 8),
        // Commission row
        Row(children: [
          const Icon(Icons.payments_outlined, size: 16, color: EteraTheme.textMuted),
          const SizedBox(width: 6),
          Expanded(child: Text(
            'Commission per file: ${operator['commission_per_file'] ?? 0}',
            style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted),
          )),
          Text('Total earned: $totalCommissions',
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: EteraTheme.green)),
        ]),
        const SizedBox(height: 12),
        // Actions
        Wrap(spacing: 8, children: [
          OutlinedButton.icon(
            onPressed: onAssignManager,
            icon: const Icon(Icons.person_add_outlined, size: 16),
            label: const Text('Assign Manager', style: TextStyle(fontSize: 12)),
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.blue,
              side: const BorderSide(color: Colors.blue),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            ),
          ),
          OutlinedButton.icon(
            onPressed: onSetQuota,
            icon: const Icon(Icons.format_list_numbered, size: 16),
            label: const Text('Set Quota', style: TextStyle(fontSize: 12)),
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.orange,
              side: const BorderSide(color: Colors.orange),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            ),
          ),
          OutlinedButton.icon(
            onPressed: onSetCommission,
            icon: const Icon(Icons.percent, size: 16),
            label: const Text('Commission', style: TextStyle(fontSize: 12)),
            style: OutlinedButton.styleFrom(
              foregroundColor: EteraTheme.green,
              side: const BorderSide(color: EteraTheme.green),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            ),
          ),
        ]),
      ]),
    );
  }
}

class _StatItem extends StatelessWidget {
  final String label;
  final String value;
  final Color color;
  const _StatItem({required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      Text(value, style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: color)),
      Text(label, style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
    ]);
  }
}
