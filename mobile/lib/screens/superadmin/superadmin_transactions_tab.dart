import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/superadmin_service.dart';
import '../../widgets/etera_card.dart';

class SuperadminTransactionsTab extends StatefulWidget {
  const SuperadminTransactionsTab({super.key});

  @override
  State<SuperadminTransactionsTab> createState() => _SuperadminTransactionsTabState();
}

class _SuperadminTransactionsTabState extends State<SuperadminTransactionsTab> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _transactions = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await SuperadminService.getTransactions();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = (res['data']['transactions'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      setState(() { _loading = false; _transactions = raw; });
    } else {
      setState(() {
        _loading = false;
        _error   = res['message']?.toString() ?? 'Failed to load transactions';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      color: Colors.blueGrey,
      onRefresh: _load,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
              child: const Text('Transactions',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 8)),
          if (_loading)
            const SliverToBoxAdapter(
                child: Center(child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(color: Colors.blueGrey),
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
          else if (_transactions.isEmpty)
            const SliverFillRemaining(child: Center(
              child: Text('No transactions found', style: TextStyle(color: EteraTheme.textMuted)),
            ))
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (_, i) {
                    final t = _transactions[i];
                    return _TransactionCard(transaction: t);
                  },
                  childCount: _transactions.length,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

// ─── Transaction Card ────────────────────────────────────────────────────────────
class _TransactionCard extends StatelessWidget {
  final Map<String, dynamic> transaction;
  const _TransactionCard({required this.transaction});

  @override
  Widget build(BuildContext context) {
    final type = transaction['type']?.toString() ?? '';
    final amount = (transaction['amount'] as num?)?.toDouble() ?? 0.0;
    final balanceAfter = (transaction['balance_after'] as num?)?.toDouble() ?? 0.0;
    final user = transaction['user']?.toString() ?? '—';
    final userRole = transaction['user_role']?.toString() ?? '';
    final reference = transaction['reference']?.toString() ?? '';
    final isPaid = transaction['is_paid'];
    final date = transaction['date']?.toString() ?? '';

    final isInvoice = type.toLowerCase() == 'invoice';
    final color = isInvoice ? Colors.blue : EteraTheme.green;

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Row(children: [
        Container(
          width: 42, height: 42,
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.12),
            shape: BoxShape.circle,
          ),
          child: Center(child: Icon(
            isInvoice ? Icons.receipt_long : Icons.payments,
            size: 20,
            color: color,
          )),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(reference.isNotEmpty ? reference : 'Transaction',
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
              maxLines: 1, overflow: TextOverflow.ellipsis),
          Text(user,
              style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          if (userRole.isNotEmpty)
            Text(userRole,
                style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
        ])),
        const SizedBox(width: 8),
        Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
          Text('${isInvoice ? '-' : '+'}${amount.toStringAsFixed(2)}',
              style: TextStyle(
                fontWeight: FontWeight.w700,
                fontSize: 14,
                color: isInvoice ? Colors.blue : EteraTheme.green,
              )),
          Text('Bal: ${balanceAfter.toStringAsFixed(2)}',
              style: const TextStyle(fontSize: 10, color: EteraTheme.textMuted)),
          if (isPaid != null)
            Container(
              margin: const EdgeInsets.only(top: 4),
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: (isPaid == true ? EteraTheme.green : Colors.orange).withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(
                isPaid == true ? 'Paid' : 'Pending',
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.w600,
                  color: isPaid == true ? EteraTheme.green : Colors.orange,
                ),
              ),
            ),
        ]),
      ]),
    );
  }
}
