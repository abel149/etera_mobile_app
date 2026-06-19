import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/proforma.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../widgets/etera_card.dart';

/// Formal invoice view of a completed proforma.
/// Shows per-shop/garage invoice with stamp, parts pricing, subtotal/discount/total.
/// Also shows the Etera billing invoice card when proforma is completed.
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
          if (data['invoice'] is Map) j['invoice'] = data['invoice'];
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
          Row(children: [
            Text('#${item.fileNumber}',
                style: const TextStyle(fontSize: 13, color: EteraTheme.textMuted)),
            const Spacer(),
            _StatusBadge(status: item.status),
          ]),
          const SizedBox(height: 16),

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
                _infoRow('Customer', item.customerName.isNotEmpty ? item.customerName : item.customerPhone),
                _infoRow('Phone', item.customerPhone),
                if (item.chassisNumber != null) _infoRow('Chassis No.', item.chassisNumber!),
                _infoRow('Submitted', item.shortDate),
              ],
            ),
          ),
          const SizedBox(height: 24),

          if (allQuotes.isNotEmpty) ...[
            Row(children: [
              const Icon(Icons.receipt_long, size: 18, color: EteraTheme.green),
              const SizedBox(width: 8),
              Text('Price Quotes (${allQuotes.length})',
                  style: Theme.of(context).textTheme.titleLarge),
            ]),
            const SizedBox(height: 4),
            const Text('Sorted by price — cheapest first',
                style: TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
            const SizedBox(height: 16),
            ...allQuotes.asMap().entries.map((e) => _InvoiceCard(
                  rank: e.key + 1,
                  application: e.value,
                  parts: item.parts,
                  isBest: e.key == 0,
                )),
          ] else
            EteraCard(
              child: const Row(children: [
                Icon(Icons.hourglass_empty_outlined, color: EteraTheme.textMuted, size: 20),
                SizedBox(width: 12),
                Text('No price quotes received yet.',
                    style: TextStyle(color: EteraTheme.textMuted)),
              ]),
            ),

          if (item.invoice != null) ...[
            const SizedBox(height: 24),
            _BillingInvoiceCard(invoice: item.invoice!, fileNumber: item.fileNumber),
          ],

          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        SizedBox(
          width: 120,
          child: Text(label, style: const TextStyle(fontSize: 13, color: EteraTheme.textMuted)),
        ),
        Expanded(
          child: Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
        ),
      ]),
    );
  }
}

// ─── Formal invoice card per shop/garage ─────────────────────────
class _InvoiceCard extends StatelessWidget {
  final int rank;
  final ProformaApplication application;
  final List<ProformaPartItem> parts;
  final bool isBest;

  const _InvoiceCard({
    required this.rank,
    required this.application,
    required this.parts,
    required this.isBest,
  });

  @override
  Widget build(BuildContext context) {
    final isShop = application.from == 'shop';
    final typeColor = isShop ? EteraTheme.green : EteraTheme.teal;
    final typeLabel = isShop ? 'Spare Part Shop' : 'Garage';
    final typeIcon = isShop ? Icons.store_outlined : Icons.build_outlined;
    final ap = application.applicant;
    final hasPricing = application.partsPricing.isNotEmpty;

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: EteraTheme.bgLight,
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
        border: Border.all(
          color: isBest
              ? EteraTheme.green.withValues(alpha: 0.5)
              : Colors.white.withValues(alpha: 0.08),
          width: isBest ? 1.5 : 1,
        ),
      ),
      child: Stack(children: [
        // Stamp watermark
        if (ap.stampImageUrl != null)
          Positioned(
            bottom: 12, right: 12,
            child: Opacity(
              opacity: 0.18,
              child: ClipOval(
                child: Image.network(
                  ap.stampImageUrl!,
                  width: 80, height: 80, fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                ),
              ),
            ),
          ),

        Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          // Header
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            decoration: BoxDecoration(
              gradient: isBest ? EteraTheme.primaryGradient : null,
              color: isBest ? null : EteraTheme.bgLight,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(EteraTheme.radiusMd)),
            ),
            child: Row(children: [
              Container(
                width: 32, height: 32,
                decoration: BoxDecoration(
                  color: isBest ? Colors.white.withValues(alpha: 0.2) : EteraTheme.green.withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                ),
                child: Center(child: Text('#$rank',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700,
                        color: isBest ? Colors.white : EteraTheme.green))),
              ),
              const SizedBox(width: 10),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(ap.name, style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15,
                    color: isBest ? Colors.white : null)),
                Row(children: [
                  Icon(typeIcon, size: 11, color: isBest ? Colors.white70 : typeColor),
                  const SizedBox(width: 3),
                  Text(typeLabel, style: TextStyle(fontSize: 11, color: isBest ? Colors.white70 : typeColor)),
                ]),
              ])),
              if (isBest)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(10)),
                  child: const Row(mainAxisSize: MainAxisSize.min, children: [
                    Icon(Icons.star_rounded, size: 12, color: Colors.white),
                    SizedBox(width: 3),
                    Text('Best', style: TextStyle(fontSize: 11, color: Colors.white, fontWeight: FontWeight.w600)),
                  ]),
                ),
            ]),
          ),

          // Shop info chips
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
            child: Wrap(spacing: 20, runSpacing: 6, children: [
              if (ap.storeId != null) _chip(Icons.badge_outlined, 'Store ID', ap.storeId!),
              if (ap.tinNumber != null) _chip(Icons.numbers, 'TIN', ap.tinNumber!),
              if (ap.phone != null) _chip(Icons.phone_outlined, 'Phone', ap.phone!),
              if (ap.location != null) _chip(Icons.location_on_outlined, 'Location', ap.location!),
            ]),
          ),

          // Parts pricing table
          if (hasPricing) ...[
            const Padding(padding: EdgeInsets.fromLTRB(16, 8, 16, 4), child: Divider(height: 1)),
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 0),
              child: DataTable(
                headingRowHeight: 36, dataRowMinHeight: 34, dataRowMaxHeight: 44,
                columnSpacing: 14,
                headingTextStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: EteraTheme.teal),
                dataTextStyle: const TextStyle(fontSize: 12),
                columns: const [
                  DataColumn(label: Text('#')),
                  DataColumn(label: Text('Part / No.')),
                  DataColumn(label: Text('Qty')),
                  DataColumn(label: Text('Unit Price'), numeric: true),
                  DataColumn(label: Text('Total'), numeric: true),
                ],
                rows: parts.asMap().entries.map((e) {
                  final part = e.value;
                  final pricing = application.partsPricing.cast<PartPricing?>().firstWhere(
                    (p) => p?.carPartId == part.id,
                    orElse: () => application.partsPricing.length > e.key ? application.partsPricing[e.key] : null,
                  );
                  return DataRow(cells: [
                    DataCell(Text('${e.key + 1}')),
                    DataCell(Text(part.number.isNotEmpty ? part.number : part.grade, overflow: TextOverflow.ellipsis)),
                    DataCell(Text('${part.quantity}')),
                    DataCell(Text(pricing != null ? '${pricing.unitPrice.toStringAsFixed(2)} Br' : '—')),
                    DataCell(Text(pricing != null ? '${pricing.partTotal.toStringAsFixed(2)} Br' : '—',
                        style: const TextStyle(fontWeight: FontWeight.w500))),
                  ]);
                }).toList(),
              ),
            ),
          ],

          // Totals
          const Padding(padding: EdgeInsets.fromLTRB(16, 8, 16, 4), child: Divider(height: 1)),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
            child: Column(children: [
              _totalRow('Subtotal', '${application.subtotal.toStringAsFixed(2)} ETB'),
              if (application.discountPct > 0)
                _totalRow('Discount (${application.discountPct.toStringAsFixed(0)}%)',
                    '- ${application.discountAmount.toStringAsFixed(2)} ETB', color: EteraTheme.teal),
              const Divider(height: 16),
              _totalRow('GRAND TOTAL', '${application.netTotal.toStringAsFixed(2)} ETB',
                  isBold: true, color: EteraTheme.green, fontSize: 15),
              const SizedBox(height: 4),
              const Text('* Prices exclude VAT',
                  style: TextStyle(fontSize: 10, color: EteraTheme.textMuted)),
            ]),
          ),
        ]),
      ]),
    );
  }

  Widget _chip(IconData icon, String label, String value) {
    return Row(mainAxisSize: MainAxisSize.min, children: [
      Icon(icon, size: 12, color: EteraTheme.textMuted),
      const SizedBox(width: 4),
      Text('$label: ', style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
      Text(value, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500)),
    ]);
  }

  Widget _totalRow(String label, String value,
      {bool isBold = false, Color? color, double fontSize = 13}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(children: [
        Expanded(child: Text(label, style: TextStyle(fontSize: fontSize,
            fontWeight: isBold ? FontWeight.w700 : FontWeight.w400,
            color: color ?? EteraTheme.textMuted))),
        Text(value, style: TextStyle(fontSize: fontSize,
            fontWeight: isBold ? FontWeight.w700 : FontWeight.w500, color: color)),
      ]),
    );
  }
}

// ─── Etera Billing Invoice Card ───────────────────────────────────
class _BillingInvoiceCard extends StatelessWidget {
  final Map<String, dynamic> invoice;
  final String fileNumber;

  const _BillingInvoiceCard({required this.invoice, required this.fileNumber});

  @override
  Widget build(BuildContext context) {
    final type = invoice['type']?.toString() ?? 'regular';
    final subtotal = (invoice['subtotal'] as num?)?.toDouble() ?? 0.0;
    final vatAmount = (invoice['vat_amount'] as num?)?.toDouble() ?? 0.0;
    final totalAmount = (invoice['total_amount'] as num?)?.toDouble() ?? 0.0;
    final isPaid = invoice['is_paid'] == true;
    final sku = invoice['sku']?.toString() ?? '';
    String dateStr = '';
    try { dateStr = DateTime.parse(invoice['created_at'] ?? '').toLocal().toString().substring(0, 10); } catch (_) {}

    final typeLabel = type == 'etera_chereta' ? 'Etera Chereta Service'
        : type == 'insurance' ? 'Insurance Proforma Service'
        : 'Proforma Service';

    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
        border: Border.all(color: EteraTheme.teal.withValues(alpha: 0.4), width: 1.5),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          decoration: BoxDecoration(
            color: EteraTheme.teal.withValues(alpha: 0.1),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(EteraTheme.radiusMd)),
          ),
          child: Row(children: [
            const Icon(Icons.receipt_long, color: EteraTheme.teal, size: 20),
            const SizedBox(width: 10),
            const Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Etera Service Invoice',
                  style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: EteraTheme.teal)),
              Text('Official receipt from Etera',
                  style: TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
            ])),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: isPaid ? EteraTheme.green.withValues(alpha: 0.15) : Colors.orange.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(isPaid ? 'PAID' : 'PENDING',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700,
                      color: isPaid ? EteraTheme.green : Colors.orange)),
            ),
          ]),
        ),

        Padding(
          padding: const EdgeInsets.all(16),
          child: Column(children: [
            if (sku.isNotEmpty)
              Row(children: [
                const Text('SKU: ', style: TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                Text(sku, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                const Spacer(),
                if (dateStr.isNotEmpty)
                  Text(dateStr, style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
              ]),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: EteraTheme.bgLight, borderRadius: BorderRadius.circular(8)),
              child: Row(children: [
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(typeLabel, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                  Text('Proforma #$fileNumber', style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
                ])),
                Text('${subtotal.toStringAsFixed(2)} ETB',
                    style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 13)),
              ]),
            ),
            const SizedBox(height: 12),
            const Divider(height: 1),
            const SizedBox(height: 10),
            _row('Subtotal', '${subtotal.toStringAsFixed(2)} ETB'),
            _row('VAT (15%)', '${vatAmount.toStringAsFixed(2)} ETB'),
            const Divider(height: 16),
            _row('TOTAL', '${totalAmount.toStringAsFixed(2)} ETB', isBold: true, color: EteraTheme.teal, fontSize: 15),
            const SizedBox(height: 8),
            const Text('TIN: 0094205503  |  Tel: 011-470-7566',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 10, color: EteraTheme.textMuted)),
          ]),
        ),
      ]),
    );
  }

  Widget _row(String label, String value, {bool isBold = false, Color? color, double fontSize = 13}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(children: [
        Expanded(child: Text(label, style: TextStyle(fontSize: fontSize,
            fontWeight: isBold ? FontWeight.w700 : FontWeight.w400,
            color: color ?? EteraTheme.textMuted))),
        Text(value, style: TextStyle(fontSize: fontSize,
            fontWeight: isBold ? FontWeight.w700 : FontWeight.w500, color: color)),
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
