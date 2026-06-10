import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/shop_service.dart';
import '../../widgets/etera_card.dart';

class ShopApplicationsTab extends StatefulWidget {
  final ValueNotifier<int>? refreshTrigger;
  const ShopApplicationsTab({super.key, this.refreshTrigger});

  @override
  State<ShopApplicationsTab> createState() => _ShopApplicationsTabState();
}

class _ShopApplicationsTabState extends State<ShopApplicationsTab> {
  bool _loading = true;
  List<Map<String, dynamic>> _items = [];
  String? _error;

  @override
  void initState() {
    super.initState();
    widget.refreshTrigger?.addListener(_load);
    _load();
  }

  @override
  void dispose() {
    widget.refreshTrigger?.removeListener(_load);
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await ShopService.getMyApplications();
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
      setState(() { _loading = false; _items = raw; });
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
              : _items.isEmpty
                  ? Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                      Icon(Icons.description_outlined, size: 64,
                          color: EteraTheme.green.withValues(alpha: 0.3)),
                      const SizedBox(height: 16),
                      const Text('No bids yet', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                      const SizedBox(height: 8),
                      const Text('Submit quotes from the Inbox.',
                          style: TextStyle(color: EteraTheme.textMuted)),
                    ]))
                  : ListView.builder(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 32),
                      itemCount: _items.length,
                      itemBuilder: (_, i) => _AppCard(item: _items[i]),
                    ),
    );
  }
}

class _AppCard extends StatelessWidget {
  final Map<String, dynamic> item;
  const _AppCard({required this.item});

  @override
  Widget build(BuildContext context) {
    final proforma = item['proforma'] as Map? ?? {};
    final brand = (proforma['brand'] as Map?)?['name']?.toString() ?? '—';
    final model = proforma['model']?.toString() ?? '';
    final year = proforma['year']?.toString() ?? '';
    final amount = (item['amount'] as num?)?.toDouble() ?? 0;
    final status = item['status']?.toString() ?? 'pending';
    final dateStr = item['created_at']?.toString() ?? '';
    DateTime? dt;
    try { dt = DateTime.parse(dateStr).toLocal(); } catch (_) {}

    final sColor = status == 'selected'
        ? EteraTheme.green
        : status == 'rejected'
            ? EteraTheme.error
            : Colors.orange;

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: EteraCard(
        child: Row(children: [
          Container(
            width: 42, height: 42,
            decoration: BoxDecoration(
              color: sColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(Icons.storefront_outlined, size: 20, color: sColor),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('$brand $model $year',
                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
            Text('${amount.toStringAsFixed(2)} Br',
                style: const TextStyle(fontSize: 13, color: EteraTheme.green, fontWeight: FontWeight.w600)),
            if (dt != null)
              Text(DateFormat('MMM d, y').format(dt),
                  style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
          ])),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: sColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(status,
                style: TextStyle(fontSize: 11, color: sColor, fontWeight: FontWeight.w600)),
          ),
        ]),
      ),
    );
  }
}
