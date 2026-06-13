import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/superadmin_service.dart';
import '../../widgets/etera_card.dart';

class SuperadminAnalyticsTab extends StatefulWidget {
  const SuperadminAnalyticsTab({super.key});

  @override
  State<SuperadminAnalyticsTab> createState() => _SuperadminAnalyticsTabState();
}

class _SuperadminAnalyticsTabState extends State<SuperadminAnalyticsTab> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _allUsers = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await SuperadminService.getAnalytics();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = (res['data']['allUsers'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      setState(() { _loading = false; _allUsers = raw; });
    } else {
      setState(() {
        _loading = false;
        _error   = res['message']?.toString() ?? 'Failed to load analytics';
      });
    }
  }

  Future<void> _markAsPaid(int userId, String userName) async {
    final res = await SuperadminService.markAsPaid(userId);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res['message']?.toString() ?? 'Done'),
      backgroundColor: res['success'] == true ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
    if (res['success'] == true) _load();
  }

  Future<void> _receivePayment(int userId, String userName) async {
    final res = await SuperadminService.receivePayment(userId);
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
      color: Colors.deepOrange,
      onRefresh: _load,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
              child: const Text('Analytics',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 8)),
          if (_loading)
            const SliverToBoxAdapter(
                child: Center(child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(color: Colors.deepOrange),
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
          else if (_allUsers.isEmpty)
            const SliverFillRemaining(child: Center(
              child: Text('No analytics data found', style: TextStyle(color: EteraTheme.textMuted)),
            ))
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (_, i) {
                    final u = _allUsers[i];
                    return _AnalyticsCard(
                      userData: u,
                      onMarkPaid: () => _markAsPaid(u['user']['id'], u['user']['name']),
                      onReceivePayment: () => _receivePayment(u['user']['id'], u['user']['name']),
                    );
                  },
                  childCount: _allUsers.length,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

// ─── Analytics Card ────────────────────────────────────────────────────────────────
class _AnalyticsCard extends StatelessWidget {
  final Map<String, dynamic> userData;
  final VoidCallback onMarkPaid;
  final VoidCallback onReceivePayment;
  const _AnalyticsCard({
    required this.userData,
    required this.onMarkPaid,
    required this.onReceivePayment,
  });

  @override
  Widget build(BuildContext context) {
    final user = userData['user'] as Map<String, dynamic>?;
    final role = userData['role']?.toString() ?? '';
    final totalEarned = (userData['total_earned'] as num?)?.toDouble() ?? 0.0;
    final totalPaid = (userData['total_paid'] as num?)?.toDouble() ?? 0.0;
    final remaining = (userData['remaining'] as num?)?.toDouble() ?? 0.0;
    final filledApps = (userData['filled_applications'] as int?) ?? 0;
    final filledProformas = (userData['filled_proformas'] as int?) ?? 0;

    final isInsurance = role.toLowerCase() == 'insurance';
    final invoiceTotal = (userData['insurance_proforma_total'] as num?)?.toDouble() ?? 0.0;
    final invoicePaid = (userData['insurance_proforma_paid'] as num?)?.toDouble() ?? 0.0;
    final invoiceUnpaid = (userData['insurance_proforma_unpaid'] as num?)?.toDouble() ?? 0.0;

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            width: 42, height: 42,
            decoration: BoxDecoration(
              color: _getRoleColor(role).withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: Center(child: Text(
              (user?['name']?.toString() ?? 'U')[0].toUpperCase(),
              style: TextStyle(fontWeight: FontWeight.w700, color: _getRoleColor(role), fontSize: 18),
            )),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(user?['name']?.toString() ?? '—',
                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
            Text(role,
                style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ])),
        ]),
        const SizedBox(height: 12),
        // Earnings row
        Row(children: [
          Expanded(child: _StatItem(label: 'Earned', value: totalEarned.toStringAsFixed(2), color: EteraTheme.green)),
          Expanded(child: _StatItem(label: 'Paid', value: totalPaid.toStringAsFixed(2), color: Colors.blue)),
          Expanded(child: _StatItem(label: 'Remaining', value: remaining.toStringAsFixed(2), color: Colors.orange)),
        ]),
        const SizedBox(height: 8),
        // Activity row
        Row(children: [
          Expanded(child: _StatItem(label: 'Apps', value: '$filledApps', color: Colors.purple)),
          Expanded(child: _StatItem(label: 'Proformas', value: '$filledProformas', color: Colors.teal)),
        ]),
        if (isInsurance && invoiceTotal > 0) ...[
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: Colors.indigo.withValues(alpha: 0.05),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Text('Insurance Invoices', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.indigo)),
              const SizedBox(height: 4),
              Row(children: [
                Expanded(child: _StatItem(label: 'Total', value: invoiceTotal.toStringAsFixed(2), color: Colors.indigo)),
                Expanded(child: _StatItem(label: 'Paid', value: invoicePaid.toStringAsFixed(2), color: EteraTheme.green)),
                Expanded(child: _StatItem(label: 'Unpaid', value: invoiceUnpaid.toStringAsFixed(2), color: Colors.red)),
              ]),
            ]),
          ),
        ],
        if (remaining > 0) ...[
          const SizedBox(height: 12),
          Wrap(spacing: 8, children: [
            OutlinedButton.icon(
              onPressed: onMarkPaid,
              icon: const Icon(Icons.check_circle_outline, size: 16),
              label: const Text('Mark Paid', style: TextStyle(fontSize: 12)),
              style: OutlinedButton.styleFrom(
                foregroundColor: EteraTheme.green,
                side: const BorderSide(color: EteraTheme.green),
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              ),
            ),
            if (isInsurance)
              OutlinedButton.icon(
                onPressed: onReceivePayment,
                icon: const Icon(Icons.payments_outlined, size: 16),
                label: const Text('Receive Payment', style: TextStyle(fontSize: 12)),
                style: OutlinedButton.styleFrom(
                  foregroundColor: Colors.indigo,
                  side: const BorderSide(color: Colors.indigo),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                ),
              ),
          ]),
        ],
      ]),
    );
  }

  Color _getRoleColor(String role) {
    switch (role.toLowerCase()) {
      case 'garage': return EteraTheme.teal;
      case 'shop': return EteraTheme.green;
      case 'insurance': return Colors.indigo;
      case 'operator': return Colors.orange;
      default: return Colors.grey;
    }
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
      Text(value, style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: color)),
      Text(label, style: const TextStyle(fontSize: 10, color: EteraTheme.textMuted)),
    ]);
  }
}
