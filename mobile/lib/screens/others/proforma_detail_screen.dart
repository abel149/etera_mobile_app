import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/proforma.dart';
import 'dart:math' as math;
import '../../providers/auth_provider.dart';
import '../../services/others_service.dart';
import '../../widgets/etera_button.dart';
import '../../widgets/etera_card.dart';

class ProformaDetailScreen extends StatefulWidget {
  const ProformaDetailScreen({super.key});

  @override
  State<ProformaDetailScreen> createState() => _ProformaDetailScreenState();
}

class _ProformaDetailScreenState extends State<ProformaDetailScreen> {
  bool _loading = true;
  bool _closing = false;
  ProformaItem? _item;
  String? _error;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final id = ModalRoute.of(context)?.settings.arguments as int?;
    if (id != null && _item == null && _loading) {
      _load(id);
    }
  }

  Future<void> _load(int id) async {
    setState(() { _loading = true; _error = null; });
    final result = await OthersService.getProformaDetail(id);
    if (!mounted) return;
    if (result.error == 'unauthorized') {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    setState(() {
      _loading = false;
      _item = result.item;
      _error = result.error;
    });
  }

  Future<void> _requestClose() async {
    if (_item == null) return;
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Request Close'),
        content: const Text('Are you sure you want to request closing this proforma?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Confirm', style: TextStyle(color: EteraTheme.error)),
          ),
        ],
      ),
    );
    if (confirm != true || !mounted) return;

    setState(() => _closing = true);
    final res = await OthersService.requestClose(_item!.id);
    if (!mounted) return;
    setState(() => _closing = false);

    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res['success'] == true ? 'Close request submitted.' : res['message'] ?? 'Failed'),
      backgroundColor: res['success'] == true ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));

    if (res['success'] == true) {
      _load(_item!.id); // refresh
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Proforma Detail'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator(color: EteraTheme.green));
    }
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.error_outline, size: 48, color: EteraTheme.error),
            const SizedBox(height: 12),
            Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
          ],
        ),
      );
    }
    if (_item == null) return const SizedBox.shrink();

    final item = _item!;
    final canClose = ['active', 'open', 'floating'].contains(item.status.toLowerCase());

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Status + ID
          Row(
            children: [
              Text('#${item.id}', style: const TextStyle(fontSize: 13, color: EteraTheme.textMuted)),
              const Spacer(),
              _StatusBadge(status: item.status),
            ],
          ),
          const SizedBox(height: 16),

          // Car info card
          EteraCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Vehicle Information',
                    style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                const SizedBox(height: 12),
                _infoRow('Brand', item.brandName),
                _infoRow('Model', item.model),
                _infoRow('Year', item.year),
                _infoRow('Car Type', item.carType),
                _infoRow('License Plate', item.licensePlate),
                if (item.chassisNumber != null)
                  _infoRow('Chassis', item.chassisNumber!),
                _infoRow('Customer Phone', item.customerPhone),
                _infoRow('Submitted', item.shortDate),
                _infoRow('Proformas Requested', '${item.numberOfProformas == -1 ? 'Unlimited' : item.numberOfProformas}'),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Parts
          Text('Spare Parts (${item.parts.length})',
              style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 12),

          if (item.parts.isEmpty)
            const Text('No parts listed.', style: TextStyle(color: EteraTheme.textMuted))
          else
            ...item.parts.asMap().entries.map((e) => _PartCard(index: e.key, part: e.value)),

          const SizedBox(height: 24),

          // ── Received Applications ──────────────────────────────────
          _ReceivedApplicationsSection(
            shops: item.shops,
            garages: item.garages,
          ),

          const SizedBox(height: 24),

          // Request close button
          if (canClose)
            EteraButton(
              label: 'Request Close',
              icon: Icons.close,
              loading: _closing,
              isOutline: true,
              onPressed: _requestClose,
            ),

          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 130,
            child: Text(label,
                style: const TextStyle(fontSize: 13, color: EteraTheme.textMuted)),
          ),
          Expanded(
            child: Text(value,
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
          ),
        ],
      ),
    );
  }
}

// ─── Part card ────────────────────────────────────────────────────
class _PartCard extends StatelessWidget {
  final int index;
  final ProformaPartItem part;

  const _PartCard({required this.index, required this.part});

  @override
  Widget build(BuildContext context) {
    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Part #${index + 1}',
              style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
          const SizedBox(height: 8),
          _row('Part', part.number),
          _row('Condition', part.condition),
          _row('Grade', part.grade),
          _row('Country', part.country),
          _row('Quantity', '${part.quantity}'),
          _row('Component', part.component),
          if (part.photos.isNotEmpty) ...[
            const SizedBox(height: 8),
            SizedBox(
              height: 70,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: part.photos.length,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (context, i) => ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.network(
                    part.photos[i],
                    width: 70,
                    height: 70,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      width: 70,
                      height: 70,
                      color: EteraTheme.bgLight,
                      child: const Icon(Icons.image_not_supported_outlined,
                          color: EteraTheme.textMuted),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        children: [
          SizedBox(
            width: 90,
            child: Text(label,
                style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ),
          Expanded(
            child: Text(value,
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
          ),
        ],
      ),
    );
  }
}

// ─── Status badge (reused from proformas tab) ─────────────────────
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
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
      child: Text(
        status[0].toUpperCase() + status.substring(1),
        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: text),
      ),
    );
  }
}
