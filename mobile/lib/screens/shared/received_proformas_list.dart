import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../widgets/etera_card.dart';
import 'received_proforma_detail_screen.dart';

/// Reusable "Received Proformas" list used inside each poster role's proformas tab.
/// Shows only closed/completed proformas — visible only after admin sends to owner.
class ReceivedProformasList extends StatefulWidget {
  final String listUrl;
  final String detailUrl;

  const ReceivedProformasList({
    super.key,
    required this.listUrl,
    required this.detailUrl,
  });

  @override
  State<ReceivedProformasList> createState() => _ReceivedProformasListState();
}

class _ReceivedProformasListState extends State<ReceivedProformasList> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _items = [];
  int _page = 1;
  int _lastPage = 1;
  bool _loadingMore = false;
  final ScrollController _scroll = ScrollController();

  @override
  void initState() {
    super.initState();
    _load();
    _scroll.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scroll.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scroll.position.pixels >=
            _scroll.position.maxScrollExtent - 200 &&
        !_loadingMore &&
        _page < _lastPage) {
      _loadMore();
    }
  }

  List<Map<String, dynamic>> _parseList(dynamic raw) {
    List src;
    if (raw is List) {
      src = raw;
    } else if (raw is Map && raw['data'] is List) {
      src = raw['data'] as List;
    } else {
      src = [];
    }
    return src
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
      _page = 1;
      _items = [];
    });
    final res = await ApiService.get(
        '${widget.listUrl}?page=1', withAuth: true);
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final pg = res['pagination'] as Map? ?? {};
      setState(() {
        _loading = false;
        _items = _parseList(res['data']);
        _lastPage = (pg['last_page'] as int?) ?? 1;
      });
    } else {
      setState(() {
        _loading = false;
        _error = res['message']?.toString() ?? 'Failed to load';
      });
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || _page >= _lastPage) return;
    setState(() => _loadingMore = true);
    final res = await ApiService.get(
        '${widget.listUrl}?page=${_page + 1}', withAuth: true);
    if (!mounted) return;
    if (res['success'] == true) {
      setState(() {
        _page++;
        _items.addAll(_parseList(res['data']));
        _loadingMore = false;
      });
    } else {
      setState(() => _loadingMore = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(
          child: CircularProgressIndicator(color: EteraTheme.green));
    }
    if (_error != null) {
      return Center(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          const Icon(Icons.wifi_off, size: 48, color: EteraTheme.textMuted),
          const SizedBox(height: 12),
          Text(_error!,
              style: const TextStyle(color: EteraTheme.textMuted)),
          const SizedBox(height: 16),
          ElevatedButton(onPressed: _load, child: const Text('Retry')),
        ]),
      );
    }
    if (_items.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            Icon(Icons.inbox_outlined,
                size: 64,
                color: EteraTheme.teal.withValues(alpha: 0.3)),
            const SizedBox(height: 16),
            const Text('No received proformas yet',
                style: TextStyle(
                    fontSize: 16, fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            const Text(
              'Your completed price quotes appear here\nonce admin sends them to you.',
              textAlign: TextAlign.center,
              style: TextStyle(
                  color: EteraTheme.textMuted, fontSize: 13, height: 1.5),
            ),
          ]),
        ),
      );
    }

    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: _load,
      child: ListView.builder(
        controller: _scroll,
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 32),
        itemCount: _items.length + (_loadingMore ? 1 : 0),
        itemBuilder: (ctx, i) {
          if (i == _items.length) {
            return const Center(
              child: Padding(
                padding: EdgeInsets.all(16),
                child: CircularProgressIndicator(color: EteraTheme.green),
              ),
            );
          }
          final item = _items[i];
          return _ReceivedCard(
            item: item,
            onTap: () => Navigator.push(
              ctx,
              MaterialPageRoute(
                builder: (_) => ReceivedProformaDetailScreen(
                  proformaId: item['id'] as int,
                  detailUrl: widget.detailUrl,
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}

// ─── Received proforma card ───────────────────────────────────────
class _ReceivedCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback onTap;

  const _ReceivedCard({required this.item, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final status = item['status']?.toString() ?? 'closed';
    final isCompleted = status.toLowerCase() == 'completed';
    final statusColor =
        isCompleted ? EteraTheme.teal : Colors.orange;

    final brandRaw = item['brand'];
    final String brandName;
    if (brandRaw is Map) {
      brandName = brandRaw['name']?.toString() ?? '';
    } else if (brandRaw is String) {
      brandName = brandRaw;
    } else {
      brandName = item['brand_name']?.toString() ?? '';
    }

    final model = item['model']?.toString() ?? '';
    final year = item['year']?.toString() ?? '';
    final fileNumber = item['file_number']?.toString() ?? '';
    final createdAt = item['created_at']?.toString() ?? '';

    String shortDate = '';
    try {
      final dt = DateTime.parse(createdAt).toLocal();
      shortDate = '${dt.day}/${dt.month}/${dt.year}';
    } catch (_) {}

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(children: [
              Expanded(
                child: Text(
                  '$brandName $model ($year)',
                  style: const TextStyle(
                      fontWeight: FontWeight.w700, fontSize: 15),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  isCompleted ? 'Completed' : 'Closed',
                  style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: statusColor),
                ),
              ),
            ]),
            const SizedBox(height: 4),
            Row(children: [
              const Icon(Icons.confirmation_number_outlined,
                  size: 13, color: EteraTheme.textMuted),
              const SizedBox(width: 4),
              Text('#$fileNumber',
                  style: const TextStyle(
                      fontSize: 12, color: EteraTheme.textMuted)),
              if (shortDate.isNotEmpty) ...[
                const Spacer(),
                Text(shortDate,
                    style: const TextStyle(
                        fontSize: 11, color: EteraTheme.textMuted)),
              ],
            ]),
            const SizedBox(height: 8),
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: EteraTheme.teal.withValues(alpha: 0.07),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.price_check,
                      size: 14, color: EteraTheme.teal),
                  const SizedBox(width: 6),
                  Text(
                    isCompleted
                        ? 'Price quotes ready — tap to view'
                        : 'Tap to view quotes',
                    style: const TextStyle(
                        fontSize: 12,
                        color: EteraTheme.teal,
                        fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
