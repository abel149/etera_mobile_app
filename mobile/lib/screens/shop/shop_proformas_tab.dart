import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/shop_service.dart';
import '../../widgets/etera_card.dart';
import 'shop_proforma_detail_screen.dart';

class ShopProformasTab extends StatefulWidget {
  final ValueNotifier<int>? refreshTrigger;
  const ShopProformasTab({super.key, this.refreshTrigger});

  @override
  State<ShopProformasTab> createState() => _ShopProformasTabState();
}

class _ShopProformasTabState extends State<ShopProformasTab> {
  bool _loading = true;
  List<Map<String, dynamic>> _items = [];
  int _currentPage = 1;
  int _lastPage = 1;
  bool _loadingMore = false;
  String? _error;

  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    widget.refreshTrigger?.addListener(_reload);
    _load(page: 1);
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    widget.refreshTrigger?.removeListener(_reload);
    _scrollController.dispose();
    super.dispose();
  }

  void _reload() => _load(page: 1);

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      if (!_loadingMore && _currentPage < _lastPage) {
        _load(page: _currentPage + 1, append: true);
      }
    }
  }

  Future<void> _load({int page = 1, bool append = false}) async {
    if (append) {
      setState(() => _loadingMore = true);
    } else {
      setState(() { _loading = true; _error = null; });
    }

    final res = await ShopService.getProformas(page: page);
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
      final pagination = res['pagination'] as Map? ?? {};
      setState(() {
        _loading = false;
        _loadingMore = false;
        _currentPage = (pagination['current_page'] as num?)?.toInt() ?? 1;
        _lastPage = (pagination['last_page'] as num?)?.toInt() ?? 1;
        _items = append ? [..._items, ...raw] : raw;
      });
    } else {
      setState(() {
        _loading = false;
        _loadingMore = false;
        _error = res['message']?.toString() ?? 'Failed to load proformas';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: () => _load(page: 1),
      child: _loading
          ? const Center(child: CircularProgressIndicator(color: EteraTheme.green))
          : _error != null
              ? _buildError()
              : _items.isEmpty
                  ? _buildEmpty()
                  : ListView.builder(
                      controller: _scrollController,
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 32),
                      itemCount: _items.length + (_loadingMore ? 1 : 0),
                      itemBuilder: (_, i) {
                        if (i == _items.length) {
                          return const Center(
                            child: Padding(
                              padding: EdgeInsets.all(16),
                              child: CircularProgressIndicator(color: EteraTheme.green),
                            ),
                          );
                        }
                        return _ProformaCard(
                          item: _items[i],
                          onTap: () async {
                            final proforma = _items[i]['proforma'] as Map? ?? _items[i];
                            final id = (proforma['id'] as num?)?.toInt()
                                ?? (_items[i]['id'] as num?)?.toInt();
                            if (id == null) return;
                            await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => ShopProformaDetailScreen(proformaId: id),
                              ),
                            );
                            _load(page: 1);
                          },
                        );
                      },
                    ),
    );
  }

  Widget _buildError() => ListView(
    physics: const AlwaysScrollableScrollPhysics(),
    children: [
      const SizedBox(height: 120),
      Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.error_outline, size: 48, color: EteraTheme.error),
        const SizedBox(height: 12),
        Text(_error!, style: const TextStyle(color: EteraTheme.textMuted),
            textAlign: TextAlign.center),
        const SizedBox(height: 12),
        ElevatedButton(onPressed: () => _load(page: 1), child: const Text('Retry')),
      ])),
    ],
  );

  Widget _buildEmpty() => ListView(
    physics: const AlwaysScrollableScrollPhysics(),
    children: [
      const SizedBox(height: 120),
      Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
        Icon(Icons.description_outlined, size: 64,
            color: EteraTheme.green.withValues(alpha: 0.3)),
        const SizedBox(height: 16),
        const Text('No proformas available',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        const Text('Floated proformas matching your brands will appear here.',
            style: TextStyle(color: EteraTheme.textMuted),
            textAlign: TextAlign.center),
      ])),
    ],
  );
}

class _ProformaCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback onTap;
  const _ProformaCard({required this.item, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final proforma = item['proforma'] as Map? ?? item;
    final brand = (item['brand'] as Map?)?['name']?.toString()
        ?? (proforma['brand'] as Map?)?['name']?.toString()
        ?? '—';
    final model = item['model']?.toString() ?? proforma['model']?.toString() ?? '';
    final year = item['year']?.toString() ?? proforma['year']?.toString() ?? '';
    final fileNum = item['file_number']?.toString()
        ?? proforma['file_number']?.toString()
        ?? '';
    final customer = item['customer_name']?.toString()
        ?? proforma['customer_name']?.toString()
        ?? '—';
    final partsCount = (item['parts_count'] as num?)?.toInt() ?? 0;
    final alreadyApplied = item['already_applied'] == true;
    final status = item['status']?.toString() ?? proforma['status']?.toString() ?? '';
    final dateStr = item['created_at']?.toString() ?? proforma['created_at']?.toString() ?? '';
    DateTime? dt;
    try { dt = DateTime.parse(dateStr).toLocal(); } catch (_) {}

    final statusColor = status == 'published'
        ? EteraTheme.green
        : status == 'opened'
            ? Colors.blue
            : EteraTheme.textMuted;

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: GestureDetector(
        onTap: alreadyApplied ? null : onTap,
        child: EteraCard(
          child: Row(children: [
            Container(
              width: 42, height: 42,
              decoration: BoxDecoration(
                color: statusColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                alreadyApplied ? Icons.check_circle_outline : Icons.description_outlined,
                size: 20,
                color: alreadyApplied ? EteraTheme.green : statusColor,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('$brand $model $year',
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
              Text(customer,
                  style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
              Row(children: [
                Text('$partsCount part${partsCount == 1 ? '' : 's'}',
                    style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
                if (dt != null) ...[
                  const Text(' • ', style: TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
                  Text(DateFormat('MMM d, y').format(dt),
                      style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
                ],
              ]),
            ])),
            Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
              if (fileNum.isNotEmpty)
                Text('#$fileNum',
                    style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
              const SizedBox(height: 4),
              if (alreadyApplied)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: EteraTheme.green.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Text('Applied',
                      style: TextStyle(fontSize: 10, color: EteraTheme.green,
                          fontWeight: FontWeight.w600)),
                )
              else
                const Icon(Icons.chevron_right, color: EteraTheme.textMuted, size: 18),
            ]),
          ]),
        ),
      ),
    );
  }
}
