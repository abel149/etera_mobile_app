import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/shop_service.dart';
import '../../widgets/etera_card.dart';

class ShopBalanceTab extends StatefulWidget {
  const ShopBalanceTab({super.key});

  @override
  State<ShopBalanceTab> createState() => _ShopBalanceTabState();
}

class _ShopBalanceTabState extends State<ShopBalanceTab> {
  bool _loading = true;
  double _balance = 0;
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
    final res = await ShopService.getBalance();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true && res['data'] is Map) {
      final data = res['data'] as Map;
      setState(() {
        _loading = false;
        _balance = (data['balance'] ?? 0).toDouble();
        _summary = Map<String, dynamic>.from(data['summary'] as Map? ?? {});
        _transactions = data['transactions'] as List? ?? [];
      });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load'; });
    }
  }

  @override
  Widget build(BuildContext context) {
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
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        gradient: EteraTheme.primaryGradient,
                        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
                      ),
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        const Text('Available Balance',
                            style: TextStyle(color: Colors.white70, fontSize: 13)),
                        const SizedBox(height: 8),
                        Text('${_balance.toStringAsFixed(2)} Br',
                            style: const TextStyle(
                                color: Colors.white, fontSize: 32, fontWeight: FontWeight.w800)),
                      ]),
                    ),
                    const SizedBox(height: 16),

                    if (_summary.isNotEmpty) ...[
                      Row(children: [
                        _Chip(
                          label: 'Earned',
                          value: (_summary['total_earned_from_etera'] ?? 0).toDouble(),
                          color: EteraTheme.green,
                        ),
                        const SizedBox(width: 8),
                        _Chip(
                          label: 'Pending',
                          value: (_summary['pending_from_etera'] ?? 0).toDouble(),
                          color: Colors.orange,
                        ),
                      ]),
                      const SizedBox(height: 20),
                    ],

                    if (_transactions.isNotEmpty) ...[
                      Text('Transactions', style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 12),
                      ..._transactions.map((t) {
                        final tx = t as Map;
                        final isIn = tx['flow'] == 'in';
                        final amount = (tx['amount'] ?? 0).toDouble();
                        final ref = tx['reference']?.toString() ?? '';
                        final dateStr = tx['date']?.toString() ?? '';
                        DateTime? dt;
                        try { dt = DateTime.parse(dateStr).toLocal(); } catch (_) {}
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: EteraCard(
                            child: Row(children: [
                              Container(
                                width: 36, height: 36,
                                decoration: BoxDecoration(
                                  color: (isIn ? EteraTheme.green : EteraTheme.error)
                                      .withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Icon(
                                  isIn ? Icons.arrow_downward : Icons.arrow_upward,
                                  size: 16,
                                  color: isIn ? EteraTheme.green : EteraTheme.error,
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                                Text(ref, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                                if (dt != null)
                                  Text(DateFormat('MMM d, y').format(dt),
                                      style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
                              ])),
                              Text(
                                '${isIn ? '+' : '-'}${amount.toStringAsFixed(2)} Br',
                                style: TextStyle(
                                    fontWeight: FontWeight.w700,
                                    color: isIn ? EteraTheme.green : EteraTheme.error),
                              ),
                            ]),
                          ),
                        );
                      }),
                    ] else
                      Center(child: Padding(
                        padding: const EdgeInsets.only(top: 40),
                        child: Column(mainAxisSize: MainAxisSize.min, children: [
                          Icon(Icons.account_balance_wallet_outlined, size: 48,
                              color: EteraTheme.green.withValues(alpha: 0.3)),
                          const SizedBox(height: 12),
                          const Text('No transactions yet',
                              style: TextStyle(color: EteraTheme.textMuted)),
                        ]),
                      )),
                  ]),
                ),
    );
  }
}

class _Chip extends StatelessWidget {
  final String label;
  final double value;
  final Color color;
  const _Chip({required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) => Expanded(
    child: Container(
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 10),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Column(children: [
        Text(label, style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w600)),
        const SizedBox(height: 4),
        FittedBox(child: Text('${value.toStringAsFixed(0)} Br',
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: color))),
      ]),
    ),
  );
}
