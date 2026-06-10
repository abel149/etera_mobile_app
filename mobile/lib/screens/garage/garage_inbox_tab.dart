import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/garage_service.dart';
import '../../widgets/etera_card.dart';
import 'garage_inbox_detail_screen.dart';

class GarageInboxTab extends StatefulWidget {
  final ValueNotifier<int>? refreshTrigger;
  const GarageInboxTab({super.key, this.refreshTrigger});

  @override
  State<GarageInboxTab> createState() => _GarageInboxTabState();
}

class _GarageInboxTabState extends State<GarageInboxTab> {
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
    setState(() {
      _loading = true;
      _error = null;
    });
    final result = await GarageService.getInbox();
    if (!mounted) return;
    if (result.error == 'unauthorized') {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    setState(() {
      _loading = false;
      _items = result.items;
      _error = result.error;
    });
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
              ? _ErrorView(message: _error!, onRetry: _load)
              : _items.isEmpty
                  ? _EmptyView(onRefresh: _load)
                  : LayoutBuilder(
                      builder: (context, constraints) {
                        final hPad = constraints.maxWidth < 380 ? 12.0 : 16.0;
                        return ListView.builder(
                          physics: const AlwaysScrollableScrollPhysics(),
                          padding: EdgeInsets.symmetric(
                              horizontal: hPad, vertical: 12),
                          itemCount: _items.length,
                          itemBuilder: (context, i) {
                            final item = _items[i];
                            final proforma = item['proforma'] as Map? ?? {};
                            final proformaId =
                                (item['inbox_id'] ?? proforma['id']) as int?;
                            return _InboxCard(
                              proforma: proforma,
                              receivedAt: item['received_at']?.toString(),
                              onTap: proformaId == null
                                  ? null
                                  : () async {
                                      final result = await Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (_) =>
                                              GarageInboxDetailScreen(
                                                  proformaId: proformaId),
                                        ),
                                      );
                                      if (result == true) _load();
                                    },
                            );
                          },
                        );
                      },
                    ),
    );
  }
}

// ─── Inbox card ───────────────────────────────────────────────────
class _InboxCard extends StatelessWidget {
  final Map proforma;
  final String? receivedAt;
  final VoidCallback? onTap;

  const _InboxCard(
      {required this.proforma,
      required this.receivedAt,
      required this.onTap});

  @override
  Widget build(BuildContext context) {
    final brand = proforma['brand']?.toString() ?? '—';
    final model = proforma['model']?.toString() ?? '—';
    final year = proforma['year']?.toString() ?? '—';
    final status = proforma['status']?.toString() ?? '';
    final fileNum = proforma['file_number']?.toString() ?? '—';

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: EteraCard(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    '$brand $model $year',
                    style: const TextStyle(
                        fontWeight: FontWeight.w700, fontSize: 15),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                _StatusBadge(status: status),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                const Icon(Icons.confirmation_number_outlined,
                    size: 14, color: EteraTheme.textMuted),
                const SizedBox(width: 4),
                Text(fileNum,
                    style: const TextStyle(
                        fontSize: 12, color: EteraTheme.textMuted)),
                const Spacer(),
                if (receivedAt != null)
                  Row(
                    children: [
                      const Icon(Icons.access_time,
                          size: 13, color: EteraTheme.textMuted),
                      const SizedBox(width: 3),
                      Text(
                        _formatDate(receivedAt!),
                        style: const TextStyle(
                            fontSize: 11, color: EteraTheme.textMuted),
                      ),
                    ],
                  ),
              ],
            ),
            const SizedBox(height: 6),
            const Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                Text('Tap to view & quote',
                    style: TextStyle(
                        fontSize: 11,
                        color: EteraTheme.green,
                        fontWeight: FontWeight.w500)),
                SizedBox(width: 4),
                Icon(Icons.arrow_forward_ios,
                    size: 11, color: EteraTheme.green),
              ],
            ),
          ],
        ),
      ),
    );
  }

  String _formatDate(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      final months = [
        'Jan','Feb','Mar','Apr','May','Jun',
        'Jul','Aug','Sep','Oct','Nov','Dec'
      ];
      return '${dt.day} ${months[dt.month - 1]}';
    } catch (_) {
      return iso;
    }
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color color;
    switch (status.toLowerCase()) {
      case 'published':
      case 'opened':
        color = Colors.blue;
        break;
      case 'pending':
        color = Colors.orange;
        break;
      case 'closed':
        color = EteraTheme.error;
        break;
      case 'completed':
        color = EteraTheme.green;
        break;
      default:
        color = EteraTheme.textMuted;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(status,
          style:
              TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w600)),
    );
  }
}

// ─── Empty & error views ──────────────────────────────────────────
class _EmptyView extends StatelessWidget {
  final VoidCallback onRefresh;
  const _EmptyView({required this.onRefresh});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.inbox_outlined, size: 56, color: EteraTheme.textMuted),
            const SizedBox(height: 16),
            const Text('Inbox is empty',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            const Text('Proformas sent to you will appear here',
                style: TextStyle(color: EteraTheme.textMuted, fontSize: 13),
                textAlign: TextAlign.center),
            const SizedBox(height: 20),
            TextButton.icon(
              onPressed: onRefresh,
              icon: const Icon(Icons.refresh),
              label: const Text('Refresh'),
            ),
          ],
        ),
      ),
    );
  }
}

class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorView({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.wifi_off, size: 40, color: EteraTheme.error),
          const SizedBox(height: 12),
          Text(message, style: const TextStyle(color: EteraTheme.error)),
          const SizedBox(height: 12),
          ElevatedButton(onPressed: onRetry, child: const Text('Retry')),
        ],
      ),
    );
  }
}
