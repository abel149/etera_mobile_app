import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/proforma.dart';
import '../../providers/auth_provider.dart';
import '../../services/business_owner_service.dart';
import '../../widgets/etera_button.dart';
import '../../widgets/etera_card.dart';

class BOProformaDetailScreen extends StatefulWidget {
  const BOProformaDetailScreen({super.key});

  @override
  State<BOProformaDetailScreen> createState() => _BOProformaDetailScreenState();
}

class _BOProformaDetailScreenState extends State<BOProformaDetailScreen> {
  bool _loading = true;
  bool _closing = false;
  ProformaItem? _item;
  Map<String, dynamic>? _invoice;
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
    setState(() {
      _loading = true;
      _error = null;
    });
    final result = await BusinessOwnerService.getProformaDetail(id);
    if (!mounted) return;
    if (result.error == 'unauthorized') {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    setState(() {
      _loading = false;
      _item = result.item;
      _invoice = result.invoice;
      _error = result.error;
    });
  }

  Future<void> _requestClose() async {
    if (_item == null) return;
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Request Close'),
        content: const Text(
            'Are you sure you want to request closing this proforma?'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Confirm',
                style: TextStyle(color: EteraTheme.error)),
          ),
        ],
      ),
    );
    if (confirm != true || !mounted) return;

    setState(() => _closing = true);
    final res = await BusinessOwnerService.requestClose(_item!.id);
    if (!mounted) return;
    setState(() => _closing = false);

    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res['success'] == true
          ? 'Close request submitted.'
          : res['message'] ?? 'Failed'),
      backgroundColor:
          res['success'] == true ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));

    if (res['success'] == true) {
      _load(_item!.id);
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
      return const Center(
          child: CircularProgressIndicator(color: EteraTheme.green));
    }
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.error_outline, size: 48, color: EteraTheme.error),
            const SizedBox(height: 12),
            Text(_error!,
                style: const TextStyle(color: EteraTheme.textMuted)),
          ],
        ),
      );
    }
    if (_item == null) return const SizedBox.shrink();

    final item = _item!;
    final canClose =
        ['active', 'open', 'floating'].contains(item.status.toLowerCase());

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Status + ID
          Row(
            children: [
              Text('#${item.id}',
                  style: const TextStyle(
                      fontSize: 13, color: EteraTheme.textMuted)),
              const Spacer(),
              _StatusBadge(status: item.status),
            ],
          ),
          const SizedBox(height: 16),

          // Invoice banner (if available)
          if (_invoice != null) ...[
            _InvoiceBanner(invoice: _invoice!),
            const SizedBox(height: 16),
          ],

          // Car info
          EteraCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Vehicle Information',
                    style: TextStyle(
                        fontWeight: FontWeight.w700, fontSize: 15)),
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
                _infoRow(
                  'Proformas Requested',
                  item.numberOfProformas == -1
                      ? 'Unlimited (Etera Chereta)'
                      : '${item.numberOfProformas}',
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Parts
          Text('Spare Parts (${item.parts.length})',
              style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 12),

          if (item.parts.isEmpty)
            const Text('No parts listed.',
                style: TextStyle(color: EteraTheme.textMuted))
          else
            ...item.parts
                .asMap()
                .entries
                .map((e) => _PartCard(index: e.key, part: e.value)),

          const SizedBox(height: 24),

          // Received applications
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
            width: 140,
            child: Text(label,
                style: const TextStyle(
                    fontSize: 13, color: EteraTheme.textMuted)),
          ),
          Expanded(
            child: Text(value,
                style: const TextStyle(
                    fontSize: 13, fontWeight: FontWeight.w500)),
          ),
        ],
      ),
    );
  }
}

// ─── Invoice banner ───────────────────────────────────────────────
class _InvoiceBanner extends StatelessWidget {
  final Map<String, dynamic> invoice;
  const _InvoiceBanner({required this.invoice});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: EteraTheme.primaryGradient,
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
      ),
      child: Row(
        children: [
          const Icon(Icons.receipt_outlined, color: Colors.white, size: 22),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Invoice Available',
                    style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w700,
                        fontSize: 14)),
                Text('SKU: ${invoice['sku'] ?? '—'}',
                    style: const TextStyle(
                        color: Colors.white70, fontSize: 12)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Received applications section ───────────────────────────────
class _ReceivedApplicationsSection extends StatelessWidget {
  final List<ProformaApplication> shops;
  final List<ProformaApplication> garages;

  const _ReceivedApplicationsSection(
      {required this.shops, required this.garages});

  @override
  Widget build(BuildContext context) {
    final total = shops.length + garages.length;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Received Applications ($total)',
            style: Theme.of(context).textTheme.titleLarge),
        const SizedBox(height: 12),

        if (total == 0)
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: EteraTheme.bgLight,
              borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
            ),
            child: const Row(
              children: [
                Icon(Icons.hourglass_empty_outlined,
                    color: EteraTheme.textMuted, size: 20),
                SizedBox(width: 12),
                Text('No applications received yet.',
                    style: TextStyle(color: EteraTheme.textMuted)),
              ],
            ),
          )
        else ...[
          if (shops.isNotEmpty) ...[
            _sectionHeader('Spare Parts Shops (${shops.length})',
                Icons.store_outlined, EteraTheme.green),
            const SizedBox(height: 8),
            ...shops.map((a) => _ApplicationCard(application: a)),
          ],
          if (garages.isNotEmpty) ...[
            if (shops.isNotEmpty) const SizedBox(height: 16),
            _sectionHeader('Garages (${garages.length})',
                Icons.build_outlined, EteraTheme.teal),
            const SizedBox(height: 8),
            ...garages.map((a) => _ApplicationCard(application: a)),
          ],
        ],
      ],
    );
  }

  Widget _sectionHeader(String title, IconData icon, Color color) {
    return Row(
      children: [
        Icon(icon, size: 16, color: color),
        const SizedBox(width: 6),
        Text(title,
            style: TextStyle(
                fontSize: 14, fontWeight: FontWeight.w600, color: color)),
      ],
    );
  }
}

// ─── Application card ─────────────────────────────────────────────
class _ApplicationCard extends StatelessWidget {
  final ProformaApplication application;
  const _ApplicationCard({required this.application});

  @override
  Widget build(BuildContext context) {
    return EteraCard(
      margin: const EdgeInsets.only(bottom: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  application.applicant.name,
                  style: const TextStyle(
                      fontWeight: FontWeight.w600, fontSize: 14),
                ),
              ),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  gradient: EteraTheme.primaryGradient,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '${application.netTotal.toStringAsFixed(0)} Br',
                  style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: Colors.white),
                ),
              ),
            ],
          ),
          if (application.applicant.phone != null) ...[
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.phone_outlined,
                    size: 13, color: EteraTheme.textMuted),
                const SizedBox(width: 4),
                Text(application.applicant.phone!,
                    style: const TextStyle(
                        fontSize: 12, color: EteraTheme.textMuted)),
              ],
            ),
          ],
          if (application.applicant.location != null) ...[
            const SizedBox(height: 2),
            Row(
              children: [
                const Icon(Icons.location_on_outlined,
                    size: 13, color: EteraTheme.textMuted),
                const SizedBox(width: 4),
                Text(application.applicant.location!,
                    style: const TextStyle(
                        fontSize: 12, color: EteraTheme.textMuted)),
              ],
            ),
          ],
          if (application.discountPct > 0) ...[
            const SizedBox(height: 8),
            Row(
              children: [
                Text(
                  'Subtotal: ${application.subtotal.toStringAsFixed(0)} Br',
                  style: const TextStyle(
                      fontSize: 12, color: EteraTheme.textMuted),
                ),
                const SizedBox(width: 10),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: EteraTheme.teal.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    '${application.discountPct.toStringAsFixed(0)}% off',
                    style: const TextStyle(
                        fontSize: 11,
                        color: EteraTheme.teal,
                        fontWeight: FontWeight.w600),
                  ),
                ),
              ],
            ),
          ],
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
              style: const TextStyle(
                  fontWeight: FontWeight.w700, fontSize: 14)),
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
                style: const TextStyle(
                    fontSize: 12, color: EteraTheme.textMuted)),
          ),
          Expanded(
            child: Text(value,
                style: const TextStyle(
                    fontSize: 13, fontWeight: FontWeight.w500)),
          ),
        ],
      ),
    );
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
      decoration: BoxDecoration(
          color: bg, borderRadius: BorderRadius.circular(20)),
      child: Text(
        status[0].toUpperCase() + status.substring(1),
        style: TextStyle(
            fontSize: 12, fontWeight: FontWeight.w600, color: text),
      ),
    );
  }
}
