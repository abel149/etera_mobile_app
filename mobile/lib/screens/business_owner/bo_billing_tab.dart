import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/business_owner_service.dart';
import '../../widgets/etera_card.dart';

class BOBillingTab extends StatefulWidget {
  const BOBillingTab({super.key});

  @override
  State<BOBillingTab> createState() => _BOBillingTabState();
}

class _BOBillingTabState extends State<BOBillingTab> {
  bool _loading = true;
  String? _error;
  String _plan = 'per_invoice';
  Map<String, dynamic>? _currentPeriod;
  List<Map<String, dynamic>> _statements = [];
  bool _updatingPlan = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final res = await BusinessOwnerService.getBillingOverview();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      setState(() {
        _loading = false;
        _plan = res['billing_plan'] ?? 'per_invoice';
        _currentPeriod = res['current_period'] is Map
            ? Map<String, dynamic>.from(res['current_period'] as Map)
            : null;
        final rawStatements = res['statements'];
        _statements = rawStatements is List
            ? rawStatements
                .map((s) => Map<String, dynamic>.from(s as Map))
                .toList()
            : [];
      });
    } else {
      setState(() {
        _loading = false;
        _error = res['message']?.toString() ?? 'Failed to load billing info';
      });
    }
  }

  Future<void> _changePlan(String newPlan) async {
    setState(() => _updatingPlan = true);
    final res = await BusinessOwnerService.updateBillingPlan(newPlan);
    if (!mounted) return;
    setState(() => _updatingPlan = false);
    if (res['success'] == true) {
      setState(() => _plan = newPlan);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text('Plan updated to ${_planLabel(newPlan)}'),
        backgroundColor: EteraTheme.green,
        behavior: SnackBarBehavior.floating,
      ));
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(res['message']?.toString() ?? 'Update failed'),
        backgroundColor: EteraTheme.error,
        behavior: SnackBarBehavior.floating,
      ));
    }
  }

  void _openPlanSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 40, height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text('Choose Billing Plan',
                  style: Theme.of(context).textTheme.titleLarge),
              const SizedBox(height: 16),
              _PlanOption(
                label: 'Per Invoice',
                value: 'per_invoice',
                description: 'Pay only when you receive a proforma invoice.',
                selected: _plan == 'per_invoice',
                onTap: () {
                  Navigator.pop(context);
                  if (_plan != 'per_invoice') _changePlan('per_invoice');
                },
              ),
              const SizedBox(height: 10),
              _PlanOption(
                label: 'Weekly',
                value: 'weekly',
                description: 'Billed weekly based on all invoices in the period.',
                selected: _plan == 'weekly',
                onTap: () {
                  Navigator.pop(context);
                  if (_plan != 'weekly') _changePlan('weekly');
                },
              ),
              const SizedBox(height: 10),
              _PlanOption(
                label: 'Monthly',
                value: 'monthly',
                description: 'Consolidated monthly billing statement.',
                selected: _plan == 'monthly',
                onTap: () {
                  Navigator.pop(context);
                  if (_plan != 'monthly') _changePlan('monthly');
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _openStatementDetail(Map<String, dynamic> statement) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) =>
            _StatementDetailScreen(sku: statement['sku'] as String),
      ),
    );
  }

  String _planLabel(String plan) {
    switch (plan) {
      case 'monthly':
        return 'Monthly';
      case 'weekly':
        return 'Weekly';
      default:
        return 'Per Invoice';
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: _load,
      child: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(
          child: CircularProgressIndicator(color: EteraTheme.green));
    }
    if (_error != null) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.2),
          Center(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.wifi_off, size: 48, color: EteraTheme.textMuted),
                const SizedBox(height: 12),
                Text(_error!,
                    style: const TextStyle(color: EteraTheme.textMuted),
                    textAlign: TextAlign.center),
                const SizedBox(height: 16),
                ElevatedButton(onPressed: _load, child: const Text('Retry')),
              ],
            ),
          ),
        ],
      );
    }

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(20),
      children: [
        // ─── Billing Plan ───────────────────────────────────────
        Text('Billing Plan', style: Theme.of(context).textTheme.titleLarge),
        const SizedBox(height: 12),
        EteraCard(
          child: Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  gradient: EteraTheme.primaryGradient,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.receipt_outlined, color: Colors.white),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(_planLabel(_plan),
                        style: const TextStyle(
                            fontWeight: FontWeight.w700, fontSize: 15)),
                    const Text('Active plan',
                        style: TextStyle(
                            fontSize: 12, color: EteraTheme.textMuted)),
                  ],
                ),
              ),
              _updatingPlan
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: EteraTheme.green))
                  : TextButton(
                      onPressed: _openPlanSheet,
                      child: const Text('Change',
                          style: TextStyle(
                              color: EteraTheme.green,
                              fontWeight: FontWeight.w600)),
                    ),
            ],
          ),
        ),

        const SizedBox(height: 24),

        // ─── Current Period ─────────────────────────────────────
        if (_currentPeriod != null) ...[
          Text('Current Period', style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 12),
          _CurrentPeriodCard(data: _currentPeriod!),
          const SizedBox(height: 24),
        ],

        // ─── Recent Statements ──────────────────────────────────
        Text('Billing Statements', style: Theme.of(context).textTheme.titleLarge),
        const SizedBox(height: 12),

        if (_statements.isEmpty)
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: EteraTheme.bgLight,
              borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
            ),
            child: const Row(
              children: [
                Icon(Icons.receipt_long_outlined,
                    color: EteraTheme.textMuted, size: 20),
                SizedBox(width: 12),
                Text('No billing statements yet.',
                    style: TextStyle(color: EteraTheme.textMuted)),
              ],
            ),
          )
        else
          ...(_statements
              .map((s) => _StatementCard(
                    statement: s,
                    onTap: () => _openStatementDetail(s),
                  ))
              .toList()),

        const SizedBox(height: 32),
      ],
    );
  }
}

// ─── Plan option tile ─────────────────────────────────────────────
class _PlanOption extends StatelessWidget {
  final String label;
  final String value;
  final String description;
  final bool selected;
  final VoidCallback onTap;

  const _PlanOption({
    required this.label,
    required this.value,
    required this.description,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: selected
              ? EteraTheme.green.withValues(alpha: 0.07)
              : Colors.grey.shade50,
          borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
          border: Border.all(
            color: selected ? EteraTheme.green : Colors.grey.shade200,
            width: selected ? 1.5 : 1,
          ),
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label,
                      style: TextStyle(
                          fontWeight: FontWeight.w700,
                          color: selected
                              ? EteraTheme.green
                              : EteraTheme.textPrimary)),
                  const SizedBox(height: 2),
                  Text(description,
                      style: const TextStyle(
                          fontSize: 12, color: EteraTheme.textMuted)),
                ],
              ),
            ),
            if (selected)
              const Icon(Icons.check_circle, color: EteraTheme.green),
          ],
        ),
      ),
    );
  }
}

// ─── Current period card ─────────────────────────────────────────
class _CurrentPeriodCard extends StatelessWidget {
  final Map<String, dynamic> data;
  const _CurrentPeriodCard({required this.data});

  @override
  Widget build(BuildContext context) {
    final count = data['proforma_count'] ?? data['count'] ?? 0;
    final total = (data['total_amount'] ?? data['total'] ?? 0).toDouble();
    final vat = (data['vat_amount'] ?? 0).toDouble();
    final subtotal = (data['subtotal'] ?? (total - vat)).toDouble();
    final status = (data['status'] ?? 'open').toString();
    final start = data['period_start']?.toString() ?? '';
    final end = data['period_end']?.toString() ?? '';

    return EteraCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  start.isNotEmpty && end.isNotEmpty
                      ? '$start → $end'
                      : 'Current billing period',
                  style: const TextStyle(
                      fontSize: 12, color: EteraTheme.textMuted),
                ),
              ),
              _StatusBadge(status: status),
            ],
          ),
          const Divider(height: 20),
          Row(
            children: [
              Expanded(
                child: _stat('Proformas', '$count'),
              ),
              Expanded(
                child: _stat(
                    'Subtotal', '${subtotal.toStringAsFixed(2)} Br'),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: _stat('VAT (15%)', '${vat.toStringAsFixed(2)} Br'),
              ),
              Expanded(
                child: _stat('Total Due',
                    '${total.toStringAsFixed(2)} Br',
                    highlight: true),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _stat(String label, String value, {bool highlight = false}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label,
            style: const TextStyle(
                fontSize: 11, color: EteraTheme.textMuted)),
        const SizedBox(height: 2),
        Text(
          value,
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w700,
            color: highlight ? EteraTheme.green : EteraTheme.textPrimary,
          ),
        ),
      ],
    );
  }
}

// ─── Statement row card ───────────────────────────────────────────
class _StatementCard extends StatelessWidget {
  final Map<String, dynamic> statement;
  final VoidCallback onTap;
  const _StatementCard({required this.statement, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final sku = statement['sku']?.toString() ?? '';
    final periodType = statement['period_type']?.toString() ?? '';
    final start = statement['period_start']?.toString() ?? '';
    final end = statement['period_end']?.toString() ?? '';
    final count = statement['proforma_count'] ?? 0;
    final total =
        (statement['total_amount'] ?? 0).toDouble();
    final status = statement['status']?.toString() ?? 'open';

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
        child: Padding(
          padding: const EdgeInsets.all(4),
          child: Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: EteraTheme.green.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.receipt_long,
                    color: EteraTheme.green, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      start.isNotEmpty && end.isNotEmpty
                          ? '$start → $end'
                          : sku,
                      style: const TextStyle(
                          fontWeight: FontWeight.w600, fontSize: 13),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '${_periodLabel(periodType)} · $count proforma(s)',
                      style: const TextStyle(
                          fontSize: 12, color: EteraTheme.textMuted),
                    ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    '${total.toStringAsFixed(2)} Br',
                    style: const TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 13,
                        color: EteraTheme.textPrimary),
                  ),
                  const SizedBox(height: 4),
                  _StatusBadge(status: status),
                ],
              ),
              const SizedBox(width: 8),
              const Icon(Icons.arrow_forward_ios,
                  size: 14, color: EteraTheme.textMuted),
            ],
          ),
        ),
      ),
    );
  }

  String _periodLabel(String type) {
    switch (type) {
      case 'monthly':
        return 'Monthly';
      case 'weekly':
        return 'Weekly';
      default:
        return 'Per Invoice';
    }
  }
}

// ─── Status badge ─────────────────────────────────────────────────
class _StatusBadge extends StatelessWidget {
  final String status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color text;
    switch (status.toLowerCase()) {
      case 'paid':
        bg = EteraTheme.green.withValues(alpha: 0.12);
        text = EteraTheme.green;
        break;
      case 'open':
      case 'pending':
        bg = EteraTheme.teal.withValues(alpha: 0.12);
        text = EteraTheme.teal;
        break;
      case 'overdue':
        bg = EteraTheme.error.withValues(alpha: 0.1);
        text = EteraTheme.error;
        break;
      default:
        bg = Colors.grey.withValues(alpha: 0.12);
        text = Colors.grey.shade600;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration:
          BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
      child: Text(
        status[0].toUpperCase() + status.substring(1),
        style: TextStyle(
            fontSize: 10, fontWeight: FontWeight.w600, color: text),
      ),
    );
  }
}

// ─── Statement Detail Screen ──────────────────────────────────────
class _StatementDetailScreen extends StatefulWidget {
  final String sku;
  const _StatementDetailScreen({required this.sku});

  @override
  State<_StatementDetailScreen> createState() =>
      _StatementDetailScreenState();
}

class _StatementDetailScreenState extends State<_StatementDetailScreen> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic>? _statement;
  List<Map<String, dynamic>> _invoices = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final res =
        await BusinessOwnerService.getStatementDetail(widget.sku);
    if (!mounted) return;
    if (res['success'] == true) {
      setState(() {
        _loading = false;
        _statement = res['statement'] is Map
            ? Map<String, dynamic>.from(res['statement'] as Map)
            : null;
        final raw = res['invoices'];
        _invoices = raw is List
            ? raw
                .map((i) => Map<String, dynamic>.from(i as Map))
                .toList()
            : [];
      });
    } else {
      setState(() {
        _loading = false;
        _error = res['message']?.toString() ?? 'Failed to load';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Statement · ${widget.sku}'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: _loading
          ? const Center(
              child: CircularProgressIndicator(color: EteraTheme.green))
          : _error != null
              ? Center(
                  child: Text(_error!,
                      style: const TextStyle(color: EteraTheme.textMuted)))
              : RefreshIndicator(
                  color: EteraTheme.green,
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.all(20),
                    children: [
                      if (_statement != null) ...[
                        _CurrentPeriodCard(data: _statement!),
                        const SizedBox(height: 24),
                      ],
                      Text('Proforma Invoices (${_invoices.length})',
                          style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 12),
                      if (_invoices.isEmpty)
                        const Text('No invoices in this period.',
                            style: TextStyle(color: EteraTheme.textMuted))
                      else
                        ..._invoices.map((inv) => _InvoiceRow(invoice: inv)),
                      const SizedBox(height: 32),
                    ],
                  ),
                ),
    );
  }
}

// ─── Invoice row inside statement detail ─────────────────────────
class _InvoiceRow extends StatelessWidget {
  final Map<String, dynamic> invoice;
  const _InvoiceRow({required this.invoice});

  @override
  Widget build(BuildContext context) {
    final brand = invoice['brand']?.toString() ?? '';
    final model = invoice['car_model']?.toString() ?? '';
    final file = invoice['file_number']?.toString() ?? '';
    final requestedBy = invoice['requested_by']?.toString() ?? '';
    final total = (invoice['total_amount'] ?? 0).toDouble();
    final vat = (invoice['vat_amount'] ?? 0).toDouble();
    final subtotal = (invoice['subtotal'] ?? (total - vat)).toDouble();
    final isPaid = invoice['is_paid'] == true;

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  '$brand $model'.trim().isEmpty ? file : '$brand $model',
                  style: const TextStyle(
                      fontWeight: FontWeight.w600, fontSize: 14),
                ),
              ),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: isPaid
                      ? EteraTheme.green.withValues(alpha: 0.12)
                      : EteraTheme.teal.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  isPaid ? 'Paid' : 'Pending',
                  style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                      color: isPaid ? EteraTheme.green : EteraTheme.teal),
                ),
              ),
            ],
          ),
          if (file.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text('File: $file',
                style: const TextStyle(
                    fontSize: 12, color: EteraTheme.textMuted)),
          ],
          if (requestedBy.isNotEmpty) ...[
            const SizedBox(height: 2),
            Text('By: $requestedBy',
                style: const TextStyle(
                    fontSize: 12, color: EteraTheme.textMuted)),
          ],
          const Divider(height: 16),
          Row(
            children: [
              Expanded(
                  child: _mini('Subtotal',
                      '${subtotal.toStringAsFixed(2)} Br')),
              Expanded(
                  child:
                      _mini('VAT', '${vat.toStringAsFixed(2)} Br')),
              Expanded(
                child: _mini(
                  'Total',
                  '${total.toStringAsFixed(2)} Br',
                  bold: true,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _mini(String label, String value, {bool bold = false}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label,
            style: const TextStyle(
                fontSize: 10, color: EteraTheme.textMuted)),
        Text(
          value,
          style: TextStyle(
              fontSize: 12,
              fontWeight: bold ? FontWeight.w700 : FontWeight.w500,
              color:
                  bold ? EteraTheme.green : EteraTheme.textPrimary),
        ),
      ],
    );
  }
}
