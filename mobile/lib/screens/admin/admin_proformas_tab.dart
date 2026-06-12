import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/admin_service.dart';
import '../../widgets/etera_card.dart';

class AdminProformasTab extends StatefulWidget {
  const AdminProformasTab({super.key});

  @override
  State<AdminProformasTab> createState() => _AdminProformasTabState();
}

class _AdminProformasTabState extends State<AdminProformasTab> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _items = [];
  int _page = 1;
  int _lastPage = 1;
  bool _loadingMore = false;
  String? _statusFilter;

  final _statuses = [null, 'pending', 'published', 'closed', 'completed'];
  final _statusLabels = ['All', 'Pending', 'Published', 'Closed', 'Completed'];

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
    if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 200 &&
        !_loadingMore &&
        _page < _lastPage) {
      _loadMore();
    }
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; _page = 1; _items = []; });
    final res = await AdminService.getProformas(status: _statusFilter, page: 1);
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = (res['data'] as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
      final pg = res['pagination'] as Map? ?? {};
      setState(() {
        _loading = false;
        _items = raw;
        _lastPage = (pg['last_page'] as int?) ?? 1;
      });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load'; });
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || _page >= _lastPage) return;
    setState(() { _loadingMore = true; });
    final res = await AdminService.getProformas(status: _statusFilter, page: _page + 1);
    if (!mounted) return;
    if (res['success'] == true) {
      final raw = (res['data'] as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
      setState(() {
        _page++;
        _items.addAll(raw);
        _loadingMore = false;
      });
    } else {
      setState(() { _loadingMore = false; });
    }
  }

  Future<void> _float(Map<String, dynamic> item) async {
    final id = item['id'] as int;
    final res = await AdminService.floatProforma(id);
    if (!mounted) return;
    final msg = res['message']?.toString() ?? (res['success'] == true ? 'Floated!' : 'Failed');
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: res['success'] == true ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
    if (res['success'] == true) _load();
  }

  Future<void> _close(Map<String, dynamic> item) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Close Proforma'),
        content: Text('Close proforma #${item['file_number']}?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Close', style: TextStyle(color: EteraTheme.error)),
          ),
        ],
      ),
    );
    if (confirmed != true) return;

    final id = item['id'] as int;
    final res = await AdminService.closeProforma(id);
    if (!mounted) return;
    final msg = res['message']?.toString() ?? (res['success'] == true ? 'Closed!' : 'Failed');
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: res['success'] == true ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
    if (res['success'] == true) _load();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Status filter chips
        SizedBox(
          height: 48,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            itemCount: _statuses.length,
            separatorBuilder: (_, __) => const SizedBox(width: 8),
            itemBuilder: (_, i) {
              final selected = _statusFilter == _statuses[i];
              return ChoiceChip(
                label: Text(_statusLabels[i]),
                selected: selected,
                selectedColor: EteraTheme.green,
                labelStyle: TextStyle(
                  color: selected ? Colors.white : EteraTheme.textMuted,
                  fontSize: 12,
                  fontWeight: selected ? FontWeight.w600 : FontWeight.normal,
                ),
                onSelected: (_) {
                  setState(() => _statusFilter = _statuses[i]);
                  _load();
                },
              );
            },
          ),
        ),

        Expanded(
          child: _loading
              ? const Center(child: CircularProgressIndicator(color: EteraTheme.green))
              : _error != null
                  ? _errorView()
                  : _items.isEmpty
                      ? _emptyView()
                      : RefreshIndicator(
                          color: EteraTheme.green,
                          onRefresh: _load,
                          child: ListView.builder(
                            controller: _scroll,
                            padding: const EdgeInsets.fromLTRB(16, 4, 16, 32),
                            itemCount: _items.length + (_loadingMore ? 1 : 0),
                            itemBuilder: (_, i) {
                              if (i == _items.length) {
                                return const Center(
                                    child: Padding(
                                      padding: EdgeInsets.all(16),
                                      child: CircularProgressIndicator(color: EteraTheme.green),
                                    ));
                              }
                              return _ProformaCard(
                                item: _items[i],
                                onFloat: () => _float(_items[i]),
                                onClose: () => _close(_items[i]),
                              );
                            },
                          ),
                        ),
        ),
      ],
    );
  }

  Widget _errorView() {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.wifi_off, size: 48, color: EteraTheme.textMuted),
          const SizedBox(height: 12),
          Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
          const SizedBox(height: 16),
          ElevatedButton(onPressed: _load, child: const Text('Retry')),
        ],
      ),
    );
  }

  Widget _emptyView() {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.receipt_long_outlined, size: 64, color: EteraTheme.green.withValues(alpha: 0.3)),
          const SizedBox(height: 16),
          const Text('No proformas', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
          const SizedBox(height: 8),
          const Text('No proformas match this filter', style: TextStyle(color: EteraTheme.textMuted)),
        ],
      ),
    );
  }
}

// ─── Card ────────────────────────────────────────────────────────────────────

class _ProformaCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback onFloat;
  final VoidCallback onClose;

  const _ProformaCard({required this.item, required this.onFloat, required this.onClose});

  @override
  Widget build(BuildContext context) {
    final status  = item['status'] as String? ?? 'pending';
    final isPending   = status == 'pending';
    final isPublished = status == 'published';
    final canAct = isPending || isPublished;

    final statusColor = _statusColor(status);

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  '#${item['file_number'] ?? 'N/A'}',
                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  status.toUpperCase(),
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: statusColor),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            item['customer_name']?.toString() ?? 'N/A',
            style: const TextStyle(fontSize: 13, color: EteraTheme.textMuted),
          ),
          if ((item['model']?.toString() ?? '').isNotEmpty) ...[
            const SizedBox(height: 2),
            Text(
              '${item['model']} ${item['year'] ?? ''}',
              style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted),
            ),
          ],
          const SizedBox(height: 4),
          Row(
            children: [
              const Icon(Icons.person_outline, size: 14, color: EteraTheme.textMuted),
              const SizedBox(width: 4),
              Text(item['from']?.toString() ?? 'Unknown',
                  style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
              const Spacer(),
              Text(_formatDate(item['created_at']?.toString()),
                  style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
            ],
          ),
          if (canAct) ...[
            const SizedBox(height: 10),
            const Divider(height: 1),
            const SizedBox(height: 8),
            Row(
              children: [
                if (isPending)
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: onFloat,
                      icon: const Icon(Icons.publish, size: 16),
                      label: const Text('Float'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: EteraTheme.green,
                        side: const BorderSide(color: EteraTheme.green),
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        textStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                      ),
                    ),
                  ),
                if (isPending) const SizedBox(width: 8),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: onClose,
                    icon: const Icon(Icons.lock_outline, size: 16),
                    label: const Text('Close'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: EteraTheme.error,
                      side: const BorderSide(color: EteraTheme.error),
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      textStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Color _statusColor(String s) {
    switch (s) {
      case 'pending':   return Colors.orange;
      case 'published': return EteraTheme.green;
      case 'closed':    return EteraTheme.teal;
      case 'completed': return Colors.blue;
      default:          return EteraTheme.textMuted;
    }
  }

  String _formatDate(String? iso) {
    if (iso == null) return '';
    try {
      final dt = DateTime.parse(iso).toLocal();
      return '${dt.day}/${dt.month}/${dt.year}';
    } catch (_) {
      return '';
    }
  }
}
