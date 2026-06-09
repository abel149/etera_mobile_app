import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/insurance_service.dart';
import '../../widgets/etera_card.dart';

class InsuranceBalanceTab extends StatefulWidget {
  const InsuranceBalanceTab({super.key});

  @override
  State<InsuranceBalanceTab> createState() => _InsuranceBalanceTabState();
}

class _InsuranceBalanceTabState extends State<InsuranceBalanceTab> {
  bool _loading = true;
  Map<String, dynamic> _summary = {};
  List<dynamic> _transactions = [];
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await InsuranceService.getBalance();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      setState(() {
        _loading = false;
        _summary = Map<String, dynamic>.from(res['summary'] as Map? ?? {});
        _transactions = res['transactions'] as List? ?? [];
      });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load'; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final totalIn = (_summary['total_earned_from_etera'] ?? 0).toDouble();
    final totalOut = (_summary['total_paid_to_etera'] ?? 0).toDouble();

    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: _load,
      child: _loading
          ? const Center(child: CircularProgressIndicator(color: EteraTheme.green))
          : _error != null
              ? Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                  Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
                  const SizedBox(height: 12),
                  ElevatedButton(onPressed: _load, child: const Text('Retry')),
                ]))
              : SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    // Summary cards
                    Row(children: [
                      _BCard(
                        label: 'Total Earned',
                        value: totalIn,
                        sub: 'from Etera',
                        color: EteraTheme.green,
                        icon: Icons.arrow_downward,
                      ),
                      const SizedBox(width: 12),
                      _BCard(
                        label: 'Total Invoiced',
                        value: totalOut,
                        sub: 'to Etera',
                        color: EteraTheme.error,
                        icon: Icons.arrow_upward,
                      ),
                    ]),
                    const SizedBox(height: 12),
                    Row(children: [
                      _BCard(
                        label: 'Pending In',
                        value: (_summary['pending_from_etera'] ?? 0).toDouble(),
                        sub: 'awaiting payment',
                        color: Colors.orange,
                        icon: Icons.hourglass_empty_outlined,
                      ),
                      const SizedBox(width: 12),
                      _BCard(
                        label: 'Pending Out',
                        value: (_summary['pending_to_etera'] ?? 0).toDouble(),
                        sub: 'unpaid invoices',
                        color: Colors.deepOrange,
                        icon: Icons.receipt_outlined,
                      ),
                    ]),
                    const SizedBox(height: 24),

                    // Transactions
                    Text('Transactions', style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 12),
                    if (_transactions.isEmpty)
                      Center(child: Padding(
                        padding: const EdgeInsets.only(top: 32),
                        child: Column(mainAxisSize: MainAxisSize.min, children: [
                          Icon(Icons.receipt_long_outlined, size: 48,
                              color: EteraTheme.green.withValues(alpha: 0.3)),
                          const SizedBox(height: 12),
                          const Text('No transactions yet',
                              style: TextStyle(color: EteraTheme.textMuted)),
                        ]),
                      ))
                    else
                      ..._transactions.map((t) {
                        final tx = t as Map;
                        final isIn = tx['flow'] == 'in';
                        final amount = (tx['amount'] ?? 0).toDouble();
                        final ref = tx['reference']?.toString() ?? '';
                        final isPaid = tx['is_paid'] == true;
                        final dateStr = tx['date']?.toString() ?? '';
                        DateTime? dt;
                        try { dt = DateTime.parse(dateStr).toLocal(); } catch (_) {}

                        return Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: EteraCard(
                            child: Row(children: [
                              Container(
                                width: 38, height: 38,
                                decoration: BoxDecoration(
                                  color: (isIn ? EteraTheme.green : EteraTheme.error)
                                      .withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Icon(isIn ? Icons.arrow_downward : Icons.arrow_upward,
                                    size: 16, color: isIn ? EteraTheme.green : EteraTheme.error),
                              ),
                              const SizedBox(width: 12),
                              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                                Text(ref, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                                if (dt != null)
                                  Text(DateFormat('MMM d, y').format(dt),
                                      style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
                              ])),
                              Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
                                Text(
                                  '${isIn ? '+' : '-'}${amount.toStringAsFixed(2)} Br',
                                  style: TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 13,
                                      color: isIn ? EteraTheme.green : EteraTheme.error),
                                ),
                                Text(isPaid ? 'Paid' : 'Pending',
                                    style: TextStyle(
                                        fontSize: 10,
                                        color: isPaid ? EteraTheme.green : Colors.orange)),
                              ]),
                            ]),
                          ),
                        );
                      }),
                  ]),
                ),
    );
  }
}

class _BCard extends StatelessWidget {
  final String label, sub;
  final double value;
  final Color color;
  final IconData icon;
  const _BCard({required this.label, required this.value, required this.sub, required this.color, required this.icon});

  @override
  Widget build(BuildContext context) => Expanded(
    child: Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.07),
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Icon(icon, size: 18, color: color),
        const SizedBox(height: 8),
        FittedBox(
          child: Text('${value.toStringAsFixed(0)} Br',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: color)),
        ),
        Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: color)),
        Text(sub, style: const TextStyle(fontSize: 10, color: EteraTheme.textMuted)),
      ]),
    ),
  );
}
