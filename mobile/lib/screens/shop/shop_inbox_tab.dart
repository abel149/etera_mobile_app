import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/shop_service.dart';
import '../../widgets/etera_card.dart';
import 'shop_proforma_detail_screen.dart';

class ShopInboxTab extends StatefulWidget {
  final ValueNotifier<int>? refreshTrigger;
  const ShopInboxTab({super.key, this.refreshTrigger});

  @override
  State<ShopInboxTab> createState() => _ShopInboxTabState();
}

class _ShopInboxTabState extends State<ShopInboxTab> {
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
    final res = await ShopService.getInbox();
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
              ? _buildError()
              : _items.isEmpty
                  ? _buildEmpty()
                  : ListView.builder(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 32),
                      itemCount: _items.length,
                      itemBuilder: (_, i) => _InboxCard(
                        item: _items[i],
                        onTap: () async {
                          final p = _items[i]['proforma'] as Map? ?? _items[i];
                          final id = (p['id'] as num?)?.toInt();
                          if (id == null) return;
                          await Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => ShopProformaDetailScreen(proformaId: id)),
                          );
                          _load();
                        },
                      ),
                    ),
    );
  }

  Widget _buildError() => ListView(
    physics: const AlwaysScrollableScrollPhysics(),
    children: [
      const SizedBox(height: 120),
      Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
        Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
        const SizedBox(height: 12),
        ElevatedButton(onPressed: _load, child: const Text('Retry')),
      ])),
    ],
  );

  Widget _buildEmpty() => ListView(
    physics: const AlwaysScrollableScrollPhysics(),
    children: [
      const SizedBox(height: 120),
      Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
        Icon(Icons.inbox_outlined, size: 64, color: EteraTheme.green.withValues(alpha: 0.3)),
        const SizedBox(height: 16),
        const Text('Inbox is empty', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        const Text('New proformas will appear here.', style: TextStyle(color: EteraTheme.textMuted)),
      ])),
    ],
  );
}

class _InboxCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback onTap;
  const _InboxCard({required this.item, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final proforma = item['proforma'] as Map? ?? item;
    final brand = (proforma['brand'] as Map?)?['name']?.toString() ?? '—';
    final model = proforma['model']?.toString() ?? '';
    final year = proforma['year']?.toString() ?? '';
    final fileNum = proforma['file_number']?.toString() ?? '';
    final customer = proforma['customer_name']?.toString() ?? '—';
    final dateStr = proforma['created_at']?.toString() ?? '';
    DateTime? dt;
    try { dt = DateTime.parse(dateStr).toLocal(); } catch (_) {}

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: GestureDetector(
        onTap: onTap,
        child: EteraCard(
          child: Row(children: [
            Container(
              width: 42, height: 42,
              decoration: BoxDecoration(
                color: Colors.blue.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.inbox_outlined, size: 20, color: Colors.blue),
            ),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('$brand $model $year',
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
              Text(customer, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
              if (dt != null)
                Text(DateFormat('MMM d, y').format(dt),
                    style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
            ])),
            Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
              Text('#$fileNum',
                  style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
              const Icon(Icons.chevron_right, color: EteraTheme.textMuted, size: 18),
            ]),
          ]),
        ),
      ),
    );
  }
}
