import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/garage_service.dart';
import '../../widgets/etera_card.dart';

class GarageBalanceTab extends StatefulWidget {
  const GarageBalanceTab({super.key});

  @override
  State<GarageBalanceTab> createState() => _GarageBalanceTabState();
}

class _GarageBalanceTabState extends State<GarageBalanceTab> {
  bool _loading = true;
  double _balance = 0;
  List<dynamic> _withdrawals = [];
  String? _error;

  // Withdraw form
  final _formKey = GlobalKey<FormState>();
  final _amountCtrl = TextEditingController();
  final _accountCtrl = TextEditingController();
  String? _selectedBank;
  bool _submitting = false;

  static const _banks = [
    'CBE',
    'Abyssiniya',
    'Awash',
    'Dashen',
    'Enat',
    'Wegagen',
    'Tsedey',
  ];

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _amountCtrl.dispose();
    _accountCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final res = await GarageService.getBalance();
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
        _withdrawals = data['withdrawal_requests'] as List? ?? [];
      });
    } else {
      setState(() {
        _loading = false;
        _error = res['message']?.toString() ?? 'Failed to load';
      });
    }
  }

  Future<void> _submitWithdrawal() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _submitting = true);

    final res = await GarageService.submitWithdrawal(
      amount: double.parse(_amountCtrl.text.trim()),
      bankName: _selectedBank!,
      accountNumber: _accountCtrl.text.trim(),
    );

    if (!mounted) return;
    setState(() => _submitting = false);

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Withdrawal request submitted!'),
          backgroundColor: EteraTheme.green,
        ),
      );
      _amountCtrl.clear();
      _accountCtrl.clear();
      setState(() => _selectedBank = null);
      _load();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message']?.toString() ?? 'Failed to submit'),
          backgroundColor: EteraTheme.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: _load,
      child: _loading
          ? const Center(
              child: CircularProgressIndicator(color: EteraTheme.green))
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!,
                          style: const TextStyle(color: EteraTheme.error)),
                      const SizedBox(height: 12),
                      ElevatedButton(
                          onPressed: _load, child: const Text('Retry')),
                    ],
                  ),
                )
              : SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.all(16),
                  child: Center(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 600),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Balance card
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(24),
                            decoration: BoxDecoration(
                              gradient: EteraTheme.primaryGradient,
                              borderRadius:
                                  BorderRadius.circular(EteraTheme.radiusMd),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('Available Balance',
                                    style: TextStyle(
                                        color: Colors.white70, fontSize: 13)),
                                const SizedBox(height: 8),
                                FittedBox(
                                  fit: BoxFit.scaleDown,
                                  alignment: Alignment.centerLeft,
                                  child: Text(
                                    '${_balance.toStringAsFixed(2)} Br',
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 32,
                                      fontWeight: FontWeight.w800,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 24),

                          // Withdraw form
                          Text('Request Withdrawal',
                              style: Theme.of(context).textTheme.titleMedium),
                          const SizedBox(height: 12),
                          EteraCard(
                            child: Form(
                              key: _formKey,
                              child: Column(
                                children: [
                                  TextFormField(
                                    controller: _amountCtrl,
                                    keyboardType:
                                        const TextInputType.numberWithOptions(
                                            decimal: true),
                                    decoration: const InputDecoration(
                                      labelText: 'Amount (Br)',
                                      prefixIcon: Icon(Icons.attach_money),
                                    ),
                                    validator: (v) {
                                      if (v == null || v.trim().isEmpty) {
                                        return 'Required';
                                      }
                                      final a = double.tryParse(v.trim());
                                      if (a == null || a <= 0) {
                                        return 'Enter a valid amount';
                                      }
                                      if (a > _balance) {
                                        return 'Exceeds available balance';
                                      }
                                      return null;
                                    },
                                  ),
                                  const SizedBox(height: 12),
                                  DropdownButtonFormField<String>(
                                    value: _selectedBank,
                                    decoration: const InputDecoration(
                                      labelText: 'Bank',
                                      prefixIcon: Icon(Icons.account_balance),
                                    ),
                                    items: _banks
                                        .map((b) => DropdownMenuItem(
                                            value: b, child: Text(b)))
                                        .toList(),
                                    onChanged: (v) =>
                                        setState(() => _selectedBank = v),
                                    validator: (v) =>
                                        v == null ? 'Select a bank' : null,
                                  ),
                                  const SizedBox(height: 12),
                                  TextFormField(
                                    controller: _accountCtrl,
                                    keyboardType: TextInputType.number,
                                    decoration: const InputDecoration(
                                      labelText: 'Account Number',
                                      prefixIcon: Icon(Icons.credit_card),
                                    ),
                                    validator: (v) {
                                      if (v == null || v.trim().isEmpty) {
                                        return 'Required';
                                      }
                                      return null;
                                    },
                                  ),
                                  const SizedBox(height: 20),
                                  SizedBox(
                                    width: double.infinity,
                                    child: ElevatedButton.icon(
                                      onPressed:
                                          _submitting ? null : _submitWithdrawal,
                                      icon: _submitting
                                          ? const SizedBox(
                                              width: 18,
                                              height: 18,
                                              child: CircularProgressIndicator(
                                                  strokeWidth: 2,
                                                  color: Colors.white),
                                            )
                                          : const Icon(
                                              Icons.send_outlined),
                                      label: Text(_submitting
                                          ? 'Submitting…'
                                          : 'Request Withdrawal'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: EteraTheme.green,
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(
                                            vertical: 14),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),

                          // Withdrawal history
                          if (_withdrawals.isNotEmpty) ...[
                            const SizedBox(height: 24),
                            Text('Withdrawal History',
                                style:
                                    Theme.of(context).textTheme.titleMedium),
                            const SizedBox(height: 12),
                            ..._withdrawals.map((w) {
                              final wr = w as Map;
                              final status =
                                  wr['status']?.toString() ?? 'pending';
                              final amount =
                                  (wr['amount'] ?? 0).toDouble();
                              final bank =
                                  wr['bank_name']?.toString() ?? '—';
                              final acct =
                                  wr['account_number']?.toString() ?? '—';
                              Color statusColor;
                              switch (status) {
                                case 'approved':
                                  statusColor = EteraTheme.green;
                                  break;
                                case 'rejected':
                                  statusColor = EteraTheme.error;
                                  break;
                                default:
                                  statusColor = Colors.orange;
                              }
                              return Padding(
                                padding: const EdgeInsets.only(bottom: 8),
                                child: EteraCard(
                                  child: Row(
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              '${amount.toStringAsFixed(2)} Br',
                                              style: const TextStyle(
                                                fontWeight: FontWeight.w700,
                                                fontSize: 16,
                                              ),
                                            ),
                                            const SizedBox(height: 4),
                                            Text(
                                              '$bank  •  $acct',
                                              style: const TextStyle(
                                                fontSize: 12,
                                                color: EteraTheme.textMuted,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                            horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: statusColor
                                              .withValues(alpha: 0.1),
                                          borderRadius:
                                              BorderRadius.circular(20),
                                        ),
                                        child: Text(
                                          status,
                                          style: TextStyle(
                                            fontSize: 12,
                                            color: statusColor,
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              );
                            }),
                          ],
                        ],
                      ),
                    ),
                  ),
                ),
    );
  }
}
