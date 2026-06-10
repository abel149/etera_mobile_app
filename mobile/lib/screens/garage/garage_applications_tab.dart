import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/garage_service.dart';
import '../../widgets/etera_card.dart';

class GarageApplicationsTab extends StatefulWidget {
  final ValueNotifier<int>? refreshTrigger;
  const GarageApplicationsTab({super.key, this.refreshTrigger});

  @override
  State<GarageApplicationsTab> createState() => _GarageApplicationsTabState();
}

class _GarageApplicationsTabState extends State<GarageApplicationsTab> {
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
    final result = await GarageService.getMyApplications();
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
              ? _centeredMsg(_error!, isError: true)
              : _items.isEmpty
                  ? _centeredMsg('No bids submitted yet')
                  : LayoutBuilder(
                      builder: (context, constraints) {
                        final hPad = constraints.maxWidth < 380 ? 12.0 : 16.0;
                        return ListView.builder(
                          physics: const AlwaysScrollableScrollPhysics(),
                          padding: EdgeInsets.symmetric(
                              horizontal: hPad, vertical: 12),
                          itemCount: _items.length,
                          itemBuilder: (context, i) =>
                              _ApplicationCard(item: _items[i]),
                        );
                      },
                    ),
    );
  }

  Widget _centeredMsg(String msg, {bool isError = false}) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              isError ? Icons.wifi_off : Icons.how_to_vote_outlined,
              size: 48,
              color: isError ? EteraTheme.error : EteraTheme.textMuted,
            ),
            const SizedBox(height: 16),
            Text(msg,
                style: TextStyle(
                    color: isError ? EteraTheme.error : EteraTheme.textMuted),
                textAlign: TextAlign.center),
            if (isError) ...[
              const SizedBox(height: 12),
              ElevatedButton(onPressed: _load, child: const Text('Retry')),
            ],
          ],
        ),
      ),
    );
  }
}

// ─── Application card ─────────────────────────────────────────────
class _ApplicationCard extends StatelessWidget {
  final Map<String, dynamic> item;
  const _ApplicationCard({required this.item});

  @override
  Widget build(BuildContext context) {
    final proforma = item['proforma'] as Map? ?? {};
    final brand = proforma['brand']?.toString() ?? '—';
    final model = proforma['model']?.toString() ?? '—';
    final year = proforma['year']?.toString() ?? '—';
    final status = proforma['status']?.toString() ?? '';
    final fileNum = proforma['file_number']?.toString() ?? '—';
    final amount = (item['amount'] ?? 0).toDouble();
    final discount = (item['discount'] ?? 0).toDouble();
    final submittedAt = item['submitted_at']?.toString();

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: EteraCard(
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
              ],
            ),
            const Divider(height: 16),
            Row(
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Your Quote',
                        style: TextStyle(
                            fontSize: 11, color: EteraTheme.textMuted)),
                    const SizedBox(height: 2),
                    Text(
                      '${amount.toStringAsFixed(2)} Br',
                      style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 16,
                          color: EteraTheme.green),
                    ),
                  ],
                ),
                if (discount > 0) ...[
                  const SizedBox(width: 20),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Discount',
                          style: TextStyle(
                              fontSize: 11, color: EteraTheme.textMuted)),
                      const SizedBox(height: 2),
                      Text('${discount.toStringAsFixed(0)}%',
                          style: const TextStyle(
                              fontWeight: FontWeight.w600,
                              fontSize: 15,
                              color: Colors.orange)),
                    ],
                  ),
                ],
                const Spacer(),
                if (submittedAt != null)
                  Text(
                    _formatDate(submittedAt),
                    style: const TextStyle(
                        fontSize: 11, color: EteraTheme.textMuted),
                  ),
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
          style: TextStyle(
              fontSize: 11, color: color, fontWeight: FontWeight.w600)),
    );
  }
}
