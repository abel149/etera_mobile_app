import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/proforma.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../widgets/etera_card.dart';

/// Full proforma detail WITH ranked price quotes.
/// Used from the "Received" tab — only shown after admin clicks "Send to Owner".
class ReceivedProformaDetailScreen extends StatefulWidget {
  final int proformaId;
  final String detailUrl;

  const ReceivedProformaDetailScreen({
    super.key,
    required this.proformaId,
    required this.detailUrl,
  });

  @override
  State<ReceivedProformaDetailScreen> createState() =>
      _ReceivedProformaDetailScreenState();
}

class _ReceivedProformaDetailScreenState
    extends State<ReceivedProformaDetailScreen> {
  bool _loading = true;
  String? _error;
  ProformaItem? _item;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final res = await ApiService.get(
      '${widget.detailUrl}/${widget.proformaId}',
      withAuth: true,
    );
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true && res['data'] != null) {
      try {
        final data = res['data'] as Map<String, dynamic>;
        Map<String, dynamic> j;
        if (data.containsKey('proforma')) {
          j = Map<String, dynamic>.from(
              data['proforma'] as Map<String, dynamic>);
          if (data['parts'] is List) j['parts'] = data['parts'];
          if (data['shops'] is List) j['shops'] = data['shops'];
          if (data['garages'] is List) j['garages'] = data['garages'];
        } else {
          j = data;
        }
        setState(() {
          _loading = false;
          _item = ProformaItem.fromJson(j);
        });
      } catch (e) {
        setState(() {
          _loading = false;
          _error = 'Parse error: $e';
        });
      }
    } else {
      setState(() {
        _loading = false;
        _error = res['message']?.toString() ?? 'Failed to load';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Received Proforma'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: _loading
          ? const Center(
              child: CircularProgressIndicator(color: EteraTheme.green))
          : _error != null
              ? _buildError()
              : _item == null
                  ? const SizedBox.shrink()
                  : _buildBody(_item!),
    );
  }

  Widget _buildError() {
    return Center(
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.error_outline, size: 48, color: EteraTheme.error),
        const SizedBox(height: 12),
        Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: _load, child: const Text('Retry')),
      ]),
    );
  }

  Widget _buildBody(ProformaItem item) {
    final allQuotes = [...item.shops, ...item.garages]
      ..sort((a, b) => a.netTotal.compareTo(b.netTotal));

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ID + Status row
          Row(children: [
            Text('#${item.fileNumber}',
                style: const TextStyle(
                    fontSize: 13, color: EteraTheme.textMuted)),
            const Spacer(),
            _StatusBadge(status: item.status),
          ]),
          const SizedBox(height: 16),

          // Vehicle info
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
                _infoRow('Customer Phone', item.customerPhone),
                if (item.chassisNumber != null)
                  _infoRow('Chassis No.', item.chassisNumber!),
                _infoRow('Submitted', item.shortDate),
                _infoRow(
                  'Quotes Requested',
                  item.numberOfProformas == -1
                      ? 'Unlimited (Etera-Chereta)'
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
            ...item.parts.asMap().entries.map(
                  (e) => _PartCard(index: e.key, part: e.value),
                ),

          const SizedBox(height: 24),

          // Price quotes section
          _QuotesSection(
            quotes: allQuotes,
            shopCount: item.shops.length,
            garageCount: item.garages.length,
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

// ─── Quotes section ───────────────────────────────────────────────
class _QuotesSection extends StatelessWidget {
  final List<ProformaApplication> quotes;
  final int shopCount;
  final int garageCount;

  const _QuotesSection({
    required this.quotes,
    required this.shopCount,
    required this.garageCount,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(children: [
          Text('Price Quotes (${quotes.length})',
              style: Theme.of(context).textTheme.titleLarge),
        ]),
        if (shopCount > 0 || garageCount > 0) ...[
          const SizedBox(height: 3),
          Row(children: [
            if (shopCount > 0) ...[
              const Icon(Icons.store_outlined,
                  size: 13, color: EteraTheme.green),
              const SizedBox(width: 3),
              Text('$shopCount shop${shopCount > 1 ? 's' : ''}',
                  style: const TextStyle(
                      fontSize: 12, color: EteraTheme.textMuted)),
            ],
            if (shopCount > 0 && garageCount > 0)
              const Text('  ·  ',
                  style: TextStyle(color: EteraTheme.textMuted)),
            if (garageCount > 0) ...[
              const Icon(Icons.build_outlined,
                  size: 13, color: EteraTheme.teal),
              const SizedBox(width: 3),
              Text('$garageCount garage${garageCount > 1 ? 's' : ''}',
                  style: const TextStyle(
                      fontSize: 12, color: EteraTheme.textMuted)),
            ],
          ]),
        ],
        const SizedBox(height: 4),
        const Text('Sorted by final price — cheapest first',
            style: TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
        const SizedBox(height: 12),
        if (quotes.isEmpty)
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: EteraTheme.bgLight,
              borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
            ),
            child: const Row(children: [
              Icon(Icons.hourglass_empty_outlined,
                  color: EteraTheme.textMuted, size: 20),
              SizedBox(width: 12),
              Text('No price quotes received.',
                  style: TextStyle(color: EteraTheme.textMuted)),
            ]),
          )
        else
          ...quotes.asMap().entries.map((e) => _QuoteCard(
                rank: e.key + 1,
                application: e.value,
                isBest: e.key == 0,
              )),
      ],
    );
  }
}

// ─── Quote card ───────────────────────────────────────────────────
class _QuoteCard extends StatelessWidget {
  final int rank;
  final ProformaApplication application;
  final bool isBest;

  const _QuoteCard({
    required this.rank,
    required this.application,
    required this.isBest,
  });

  @override
  Widget build(BuildContext context) {
    final isShop = application.from == 'shop';
    final typeColor = isShop ? EteraTheme.green : EteraTheme.teal;
    final typeIcon =
        isShop ? Icons.store_outlined : Icons.build_outlined;
    final typeLabel = isShop ? 'Spare Part Shop' : 'Garage';

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            // Rank badge
            Container(
              width: 30,
              height: 30,
              decoration: BoxDecoration(
                color: isBest
                    ? EteraTheme.green.withValues(alpha: 0.15)
                    : EteraTheme.bgLight,
                shape: BoxShape.circle,
              ),
              child: Center(
                child: Text(
                  '#$rank',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color:
                        isBest ? EteraTheme.green : EteraTheme.textMuted,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    application.applicant.name,
                    style: const TextStyle(
                        fontWeight: FontWeight.w600, fontSize: 14),
                  ),
                  Row(children: [
                    Icon(typeIcon, size: 11, color: typeColor),
                    const SizedBox(width: 3),
                    Text(typeLabel,
                        style:
                            TextStyle(fontSize: 11, color: typeColor)),
                  ]),
                ],
              ),
            ),
            // Price pill
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                gradient: isBest ? EteraTheme.primaryGradient : null,
                color: isBest ? null : EteraTheme.bgLight,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                '${application.netTotal.toStringAsFixed(0)} Br',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: isBest ? Colors.white : EteraTheme.textMuted,
                ),
              ),
            ),
          ]),

          if (application.applicant.phone != null) ...[
            const SizedBox(height: 6),
            Row(children: [
              const Icon(Icons.phone_outlined,
                  size: 13, color: EteraTheme.textMuted),
              const SizedBox(width: 4),
              Text(application.applicant.phone!,
                  style: const TextStyle(
                      fontSize: 12, color: EteraTheme.textMuted)),
            ]),
          ],

          if (application.discountPct > 0) ...[
            const SizedBox(height: 6),
            Row(children: [
              Text(
                'Subtotal: ${application.subtotal.toStringAsFixed(0)} Br',
                style: const TextStyle(
                    fontSize: 12, color: EteraTheme.textMuted),
              ),
              const SizedBox(width: 8),
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
            ]),
          ],

          if (application.applicant.location != null) ...[
            const SizedBox(height: 4),
            Row(children: [
              const Icon(Icons.location_on_outlined,
                  size: 13, color: EteraTheme.textMuted),
              const SizedBox(width: 4),
              Expanded(
                child: Text(application.applicant.location!,
                    style: const TextStyle(
                        fontSize: 12, color: EteraTheme.textMuted)),
              ),
            ]),
          ],

          if (isBest) ...[
            const SizedBox(height: 8),
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: EteraTheme.green.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(6),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.star_rounded,
                      size: 13, color: EteraTheme.green),
                  SizedBox(width: 4),
                  Text('Best price',
                      style: TextStyle(
                          fontSize: 11,
                          color: EteraTheme.green,
                          fontWeight: FontWeight.w600)),
                ],
              ),
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
          _row('Part No.', part.number),
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
                itemBuilder: (_, i) => ClipRRect(
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
                      child: const Icon(
                          Icons.image_not_supported_outlined,
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
      child: Row(children: [
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
      ]),
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
      case 'completed':
        bg = EteraTheme.teal.withValues(alpha: 0.12);
        text = EteraTheme.teal;
        break;
      case 'closed':
        bg = Colors.orange.withValues(alpha: 0.12);
        text = Colors.orange;
        break;
      case 'published':
        bg = EteraTheme.green.withValues(alpha: 0.12);
        text = EteraTheme.green;
        break;
      default:
        bg = EteraTheme.bgLight;
        text = EteraTheme.textMuted;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
      decoration:
          BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
      child: Text(
        status[0].toUpperCase() + status.substring(1),
        style: TextStyle(
            fontSize: 12, fontWeight: FontWeight.w600, color: text),
      ),
    );
  }
}
