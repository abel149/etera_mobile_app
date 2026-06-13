import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/superadmin_service.dart';
import '../../widgets/etera_card.dart';

class SuperadminDashboardTab extends StatefulWidget {
  final VoidCallback? onGoToProformas;
  final ValueChanged<String>? onGoToManage;
  final ValueChanged<String>? onGoToReports;

  const SuperadminDashboardTab({
    super.key,
    this.onGoToProformas,
    this.onGoToManage,
    this.onGoToReports,
  });

  @override
  State<SuperadminDashboardTab> createState() => _SuperadminDashboardTabState();
}

class _SuperadminDashboardTabState extends State<SuperadminDashboardTab> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic> _stats = {};
  List<Map<String, dynamic>> _proformas = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await SuperadminService.getDashboard();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['stats'] != null || res['success'] == true) {
      final stats = res['stats'] as Map? ?? {};
      final raw   = res['proformas'] as List? ?? [];
      setState(() {
        _loading  = false;
        _stats    = Map<String, dynamic>.from(stats);
        _proformas = raw.map((e) => Map<String, dynamic>.from(e as Map)).toList();
      });
    } else {
      setState(() {
        _loading = false;
        _error   = res['message']?.toString() ?? 'Failed to load dashboard';
      });
    }
  }

  Future<void> _float(int id, int index) async {
    final res = await SuperadminService.floatProforma(id);
    if (!mounted) return;
    _snack(res['message']?.toString() ?? (res['success'] == true ? 'Floated!' : 'Failed'), res['success'] == true);
    if (res['success'] == true) {
      setState(() => _proformas[index] = {..._proformas[index], 'status': 'published'});
    }
  }

  Future<void> _close(int id, int index) async {
    final ok = await _confirm('Close this proforma?');
    if (!ok) return;
    final res = await SuperadminService.closeProforma(id);
    if (!mounted) return;
    _snack(res['message']?.toString() ?? (res['success'] == true ? 'Closed' : 'Failed'), res['success'] == true);
    if (res['success'] == true) {
      setState(() => _proformas[index] = {..._proformas[index], 'status': 'closed'});
    }
  }

  Future<bool> _confirm(String msg) async =>
      await showDialog<bool>(
        context: context,
        builder: (_) => AlertDialog(
          title: const Text('Confirm'),
          content: Text(msg),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
            TextButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Confirm', style: TextStyle(color: EteraTheme.error)),
            ),
          ],
        ),
      ) ?? false;

  void _snack(String msg, bool ok) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: ok ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

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

    return RefreshIndicator(
      color: Colors.deepPurple,
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 20, 16, 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Header ─────────────────────────────────────────────
            Row(children: [
              Container(
                width: 48, height: 48,
                decoration: BoxDecoration(
                  color: Colors.deepPurple.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(Icons.admin_panel_settings, color: Colors.deepPurple, size: 26),
              ),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('Welcome, ${user?.name ?? 'Superadmin'}',
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
                const Text('Superadmin Dashboard',
                    style: TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
              ])),
            ]),
            const SizedBox(height: 24),

            // ── Proforma Stats ──────────────────────────────────────
            Text('Proforma Statistics', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 12),
            Row(children: [
              Expanded(child: _StatCard(
                label: 'Insurance', icon: Icons.shield_outlined,
                total: _stats['insurance_total'] as int? ?? 0,
                done: _stats['insurance_completed'] as int? ?? 0,
                color: Colors.indigo,
              )),
              const SizedBox(width: 10),
              Expanded(child: _StatCard(
                label: 'Others', icon: Icons.people_outline,
                total: _stats['others_total'] as int? ?? 0,
                done: _stats['others_completed'] as int? ?? 0,
                color: Colors.purple,
              )),
            ]),
            const SizedBox(height: 20),

            // ── Quick Actions ───────────────────────────────────────
            Text('Management', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 12),
            EteraCard(child: Column(children: [
              _ActionRow(
                icon: Icons.receipt_long_outlined, label: 'All Proformas',
                color: Colors.deepPurple, onTap: widget.onGoToProformas,
              ),
              const Divider(height: 1),
              _ActionRow(
                icon: Icons.group_outlined, label: 'Users',
                color: Colors.blue, onTap: () => widget.onGoToManage?.call('users'),
              ),
              const Divider(height: 1),
              _ActionRow(
                icon: Icons.manage_accounts_outlined, label: 'Admins',
                color: Colors.teal, onTap: () => widget.onGoToManage?.call('admins'),
              ),
              const Divider(height: 1),
              _ActionRow(
                icon: Icons.shield_outlined, label: 'Insurance',
                color: Colors.indigo, onTap: () => widget.onGoToManage?.call('insurance'),
              ),
              const Divider(height: 1),
              _ActionRow(
                icon: Icons.store_outlined, label: 'Shops',
                color: EteraTheme.green, onTap: () => widget.onGoToManage?.call('shops'),
              ),
              const Divider(height: 1),
              _ActionRow(
                icon: Icons.garage_outlined, label: 'Garages',
                color: EteraTheme.teal, onTap: () => widget.onGoToManage?.call('garages'),
              ),
              const Divider(height: 1),
              _ActionRow(
                icon: Icons.engineering_outlined, label: 'Operators',
                color: Colors.orange, onTap: () => widget.onGoToManage?.call('operators'),
              ),
              const Divider(height: 1),
              _ActionRow(
                icon: Icons.campaign_outlined, label: 'Marketers',
                color: Colors.purple, onTap: () => widget.onGoToManage?.call('marketers'),
              ),
              const Divider(height: 1),
              _ActionRow(
                icon: Icons.category_outlined, label: 'Brands',
                color: Colors.amber, onTap: () => widget.onGoToManage?.call('brands'),
              ),
            ])),
            const SizedBox(height: 20),
            Text('Reports', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 12),
            EteraCard(child: Column(children: [
              _ActionRow(
                icon: Icons.bar_chart_outlined, label: 'Analytics',
                color: Colors.deepOrange, onTap: () => widget.onGoToReports?.call('analytics'),
              ),
              const Divider(height: 1),
              _ActionRow(
                icon: Icons.account_balance_wallet_outlined, label: 'Transactions',
                color: Colors.blueGrey, onTap: () => widget.onGoToReports?.call('transactions'),
              ),
              const Divider(height: 1),
              _ActionRow(
                icon: Icons.star_outlined, label: 'Ratings',
                color: Colors.amber, onTap: () => widget.onGoToReports?.call('ratings'),
              ),
              const Divider(height: 1),
              _ActionRow(
                icon: Icons.settings_outlined, label: 'Settings',
                color: Colors.blueGrey, onTap: () => widget.onGoToReports?.call('settings'),
              ),
            ])),
            const SizedBox(height: 20),

            // ── Recent Proformas ────────────────────────────────────
            Row(children: [
              Expanded(child: Text('Recent Proformas',
                  style: Theme.of(context).textTheme.titleMedium)),
              TextButton(
                onPressed: widget.onGoToProformas,
                child: const Text('View all', style: TextStyle(color: Colors.deepPurple, fontSize: 12)),
              ),
            ]),
            const SizedBox(height: 8),
            if (_proformas.isEmpty)
              const Center(child: Padding(
                padding: EdgeInsets.all(24),
                child: Text('No proformas yet', style: TextStyle(color: EteraTheme.textMuted)),
              ))
            else
              ...List.generate(_proformas.take(10).length, (i) {
                final p = _proformas[i];
                return _ProformaRow(
                  proforma: p,
                  onFloat: () => _float(p['id'] as int, i),
                  onClose: () => _close(p['id'] as int, i),
                );
              }),
          ],
        ),
      ),
    );
  }
}

// ─── Stat Card ────────────────────────────────────────────────────────────────
class _StatCard extends StatelessWidget {
  final String label;
  final IconData icon;
  final int total;
  final int done;
  final Color color;
  const _StatCard({required this.label, required this.icon, required this.total, required this.done, required this.color});

  @override
  Widget build(BuildContext context) {
    return EteraCard(
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Icon(icon, size: 18, color: color),
          const SizedBox(width: 6),
          Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: color)),
        ]),
        const SizedBox(height: 10),
        Text('$total', style: TextStyle(fontSize: 26, fontWeight: FontWeight.w800, color: color)),
        Text('Total', style: const TextStyle(fontSize: 10, color: EteraTheme.textMuted)),
        const SizedBox(height: 4),
        Text('$done completed', style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
      ]),
    );
  }
}

// ─── Action Row ───────────────────────────────────────────────────────────────
class _ActionRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback? onTap;
  const _ActionRow({required this.icon, required this.label, required this.color, this.onTap});

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 4, vertical: 0),
      leading: CircleAvatar(
        radius: 18,
        backgroundColor: color.withValues(alpha: 0.12),
        child: Icon(icon, size: 18, color: color),
      ),
      title: Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
      trailing: Icon(Icons.chevron_right, size: 18, color: color),
      onTap: onTap,
    );
  }
}

// ─── Proforma Row ─────────────────────────────────────────────────────────────
class _ProformaRow extends StatelessWidget {
  final Map<String, dynamic> proforma;
  final VoidCallback onFloat;
  final VoidCallback onClose;
  const _ProformaRow({required this.proforma, required this.onFloat, required this.onClose});

  @override
  Widget build(BuildContext context) {
    final status = proforma['status']?.toString() ?? 'pending';
    final statusColor = _statusColor(status);

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 8),
      child: Row(children: [
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(proforma['file_number']?.toString() ?? 'N/A',
              style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
          Text(proforma['customer_name']?.toString() ?? '',
              style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
          Text(proforma['from']?.toString() ?? '',
              style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
        ])),
        const SizedBox(width: 8),
        Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: statusColor.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(status, style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: statusColor)),
          ),
          const SizedBox(height: 4),
          if (status == 'pending')
            GestureDetector(
              onTap: onFloat,
              child: const Text('Float', style: TextStyle(fontSize: 11, color: Colors.deepPurple, fontWeight: FontWeight.w600)),
            )
          else if (status == 'published')
            GestureDetector(
              onTap: onClose,
              child: const Text('Close', style: TextStyle(fontSize: 11, color: EteraTheme.error, fontWeight: FontWeight.w600)),
            ),
        ]),
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
