import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/proforma.dart';
import '../../providers/auth_provider.dart';
import '../../services/business_owner_service.dart';
import '../../widgets/etera_card.dart';

class BOProformasTab extends StatefulWidget {
  final ValueNotifier<int>? refreshTrigger;
  const BOProformasTab({super.key, this.refreshTrigger});

  @override
  State<BOProformasTab> createState() => _BOProformasTabState();
}

class _BOProformasTabState extends State<BOProformasTab> {
  bool _loading = true;
  List<ProformaItem> _items = [];
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
    final result = await BusinessOwnerService.getProformas();
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
      child: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(
          child: CircularProgressIndicator(color: EteraTheme.green));
    }
    if (_error != null) {
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
    if (_items.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.25),
          Center(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.receipt_long_outlined,
                    size: 64,
                    color: EteraTheme.green.withValues(alpha: 0.3)),
                const SizedBox(height: 16),
                const Text('No proformas yet',
                    style: TextStyle(
                        fontSize: 16, fontWeight: FontWeight.w600)),
                const SizedBox(height: 8),
                const Text(
                  'Pull down to refresh or create a new request',
                  style: TextStyle(
                      color: EteraTheme.textMuted, fontSize: 13),
                ),
              ],
            ),
          ),
        ],
      );
    }
    return LayoutBuilder(
      builder: (context, constraints) {
        final hPad = constraints.maxWidth < 380 ? 12.0 : 16.0;
        return ListView.builder(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: EdgeInsets.symmetric(horizontal: hPad, vertical: 12),
          itemCount: _items.length,
          itemBuilder: (context, i) => _ProformaCard(
            item: _items[i],
            onTap: () async {
              await Navigator.pushNamed(context, '/bo-proforma-detail',
                  arguments: _items[i].id);
              _load();
            },
          ),
        );
      },
    );
  }
}

// ─── Proforma card ─────────────────────────────────────────────────
class _ProformaCard extends StatelessWidget {
  final ProformaItem item;
  final VoidCallback onTap;

  const _ProformaCard({required this.item, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return EteraCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
        child: Padding(
          padding: const EdgeInsets.all(4),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      '${item.brandName} ${item.model} ${item.year}',
                      style: const TextStyle(
                          fontWeight: FontWeight.w700, fontSize: 15),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  _StatusBadge(status: item.status),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  const Icon(Icons.confirmation_number_outlined,
                      size: 14, color: EteraTheme.textMuted),
                  const SizedBox(width: 4),
                  Text(item.licensePlate,
                      style: const TextStyle(
                          fontSize: 13, color: EteraTheme.textMuted)),
                  const SizedBox(width: 16),
                  const Icon(Icons.build_outlined,
                      size: 14, color: EteraTheme.textMuted),
                  const SizedBox(width: 4),
                  Text('${item.parts.length} part(s)',
                      style: const TextStyle(
                          fontSize: 13, color: EteraTheme.textMuted)),
                ],
              ),
              const SizedBox(height: 6),
              Row(
                children: [
                  const Icon(Icons.directions_car_outlined,
                      size: 14, color: EteraTheme.textMuted),
                  const SizedBox(width: 4),
                  Text(item.carType,
                      style: const TextStyle(
                          fontSize: 13, color: EteraTheme.textMuted)),
                  const Spacer(),
                  Text(item.shortDate,
                      style: const TextStyle(
                          fontSize: 12, color: EteraTheme.textMuted)),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Status badge ──────────────────────────────────────────────────
class _StatusBadge extends StatelessWidget {
  final String status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color text;
    switch (status.toLowerCase()) {
      case 'active':
      case 'open':
      case 'floating':
        bg = EteraTheme.green.withValues(alpha: 0.12);
        text = EteraTheme.green;
        break;
      case 'closed':
      case 'completed':
        bg = Colors.grey.withValues(alpha: 0.15);
        text = Colors.grey.shade600;
        break;
      case 'cancelled':
      case 'rejected':
        bg = EteraTheme.error.withValues(alpha: 0.1);
        text = EteraTheme.error;
        break;
      default:
        bg = EteraTheme.teal.withValues(alpha: 0.12);
        text = EteraTheme.teal;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
          color: bg, borderRadius: BorderRadius.circular(20)),
      child: Text(
        status[0].toUpperCase() + status.substring(1),
        style: TextStyle(
            fontSize: 11, fontWeight: FontWeight.w600, color: text),
      ),
    );
  }
}
