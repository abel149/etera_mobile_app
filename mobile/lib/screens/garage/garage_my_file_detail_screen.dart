import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/garage_service.dart';
import '../../widgets/etera_card.dart';

class GarageMyFileDetailScreen extends StatefulWidget {
  const GarageMyFileDetailScreen({super.key});

  @override
  State<GarageMyFileDetailScreen> createState() =>
      _GarageMyFileDetailScreenState();
}

class _GarageMyFileDetailScreenState extends State<GarageMyFileDetailScreen> {
  bool _loading = true;
  bool _closing = false;
  Map<String, dynamic>? _proforma;
  List<dynamic> _parts = [];
  List<dynamic> _shops = [];
  List<dynamic> _garages = [];
  Map<String, dynamic>? _invoice;
  String? _error;
  int? _id;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final id = ModalRoute.of(context)?.settings.arguments as int?;
    if (id != null && _id == null) {
      _id = id;
      _load(id);
    }
  }

  Future<void> _load(int id) async {
    setState(() { _loading = true; _error = null; });
    final res = await GarageService.getMyFileDetail(id);
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true && res['data'] is Map) {
      final data = res['data'] as Map;
      setState(() {
        _loading = false;
        _proforma = data['proforma'] is Map
            ? Map<String, dynamic>.from(data['proforma'] as Map)
            : null;
        _parts = data['parts'] as List? ?? [];
        _shops = data['shops'] as List? ?? [];
        _garages = data['garages'] as List? ?? [];
        _invoice = data['invoice'] is Map
            ? Map<String, dynamic>.from(data['invoice'] as Map)
            : null;
      });
    } else {
      setState(() {
        _loading = false;
        _error = res['message']?.toString() ?? 'Failed to load';
      });
    }
  }

  Future<void> _requestClose() async {
    if (_id == null) return;
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
            child:
                const Text('Confirm', style: TextStyle(color: EteraTheme.error)),
          ),
        ],
      ),
    );
    if (confirm != true || !mounted) return;
    setState(() => _closing = true);
    final res = await GarageService.requestClose(_id!);
    if (!mounted) return;
    setState(() => _closing = false);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res['message']?.toString() ??
          (res['success'] == true ? 'Close request submitted.' : 'Failed.')),
      backgroundColor:
          res['success'] == true ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
    if (res['success'] == true) _load(_id!);
  }

  @override
  Widget build(BuildContext context) {
    final status = (_proforma?['status'] ?? '').toString();
    final canClose = !['closed', 'completed'].contains(status.toLowerCase()) &&
        _proforma?['close_request'] != true;

    return Scaffold(
      appBar: AppBar(
        title: Text(_proforma != null
            ? '${_proforma!['brand'] is Map ? _proforma!['brand']['name'] : _proforma!['brand'] ?? ''} ${_proforma!['model'] ?? ''}'
            : 'File Detail'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          if (!_loading && _proforma != null && canClose)
            TextButton(
              onPressed: _closing ? null : _requestClose,
              child: _closing
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: EteraTheme.error))
                  : const Text('Close',
                      style: TextStyle(
                          color: EteraTheme.error, fontWeight: FontWeight.w600)),
            ),
        ],
      ),
      body: _loading
          ? const Center(
              child: CircularProgressIndicator(color: EteraTheme.green))
          : _error != null
              ? Center(
                  child: Column(mainAxisSize: MainAxisSize.min, children: [
                    Text(_error!,
                        style: const TextStyle(color: EteraTheme.textMuted)),
                    const SizedBox(height: 12),
                    ElevatedButton(
                        onPressed: () => _load(_id!),
                        child: const Text('Retry')),
                  ]))
              : RefreshIndicator(
                  color: EteraTheme.green,
                  onRefresh: () => _load(_id!),
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      _ProformaHeader(proforma: _proforma!, invoice: _invoice),
                      const SizedBox(height: 16),
                      if (_parts.isNotEmpty) ...[
                        _SectionTitle('Parts (${_parts.length})'),
                        ..._parts.map((p) => _PartCard(part: p as Map)),
                        const SizedBox(height: 16),
                      ],
                      if (_shops.isNotEmpty) ...[
                        _SectionTitle('Shop Quotes (${_shops.length})'),
                        ..._shops.map((s) => _ApplicationCard(app: s as Map)),
                        const SizedBox(height: 16),
                      ],
                      if (_garages.isNotEmpty) ...[
                        _SectionTitle('Garage Quotes (${_garages.length})'),
                        ..._garages
                            .map((g) => _ApplicationCard(app: g as Map)),
                        const SizedBox(height: 16),
                      ],
                      if (_shops.isEmpty && _garages.isEmpty)
                        const Center(
                          child: Padding(
                            padding: EdgeInsets.all(24),
                            child: Text('No quotes received yet.',
                                style:
                                    TextStyle(color: EteraTheme.textMuted)),
                          ),
                        ),
                      const SizedBox(height: 32),
                    ],
                  ),
                ),
    );
  }
}

// ─── Proforma header card ─────────────────────────────────────────
class _ProformaHeader extends StatelessWidget {
  final Map<String, dynamic> proforma;
  final Map<String, dynamic>? invoice;
  const _ProformaHeader({required this.proforma, this.invoice});

  @override
  Widget build(BuildContext context) {
    final brandRaw = proforma['brand'];
    final brand = brandRaw is Map
        ? brandRaw['name']?.toString() ?? ''
        : brandRaw?.toString() ?? '';
    final model = proforma['model']?.toString() ?? '';
    final year = proforma['year']?.toString() ?? '';
    final status = proforma['status']?.toString() ?? '';
    final fileNumber = proforma['file_number']?.toString() ?? '';
    final custPhone =
        proforma['customer_phone_number']?.toString() ?? '';
    final plate = proforma['license_plate_number']?.toString() ?? '';
    final closeRequest = proforma['close_request'] == true;

    return EteraCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Text('$brand $model $year',
                    style: const TextStyle(
                        fontWeight: FontWeight.w700, fontSize: 17)),
              ),
              _StatusBadge(status: status),
            ],
          ),
          const SizedBox(height: 12),
          _Row(Icons.confirmation_number_outlined, 'File', fileNumber),
          if (custPhone.isNotEmpty)
            _Row(Icons.phone_outlined, 'Phone', custPhone),
          if (plate.isNotEmpty)
            _Row(Icons.directions_car_outlined, 'Plate', plate),
          if (closeRequest) ...[
            const SizedBox(height: 8),
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color: Colors.orange.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.warning_amber_outlined,
                      size: 14, color: Colors.orange),
                  SizedBox(width: 6),
                  Text('Close request pending',
                      style: TextStyle(
                          fontSize: 12,
                          color: Colors.orange,
                          fontWeight: FontWeight.w500)),
                ],
              ),
            ),
          ],
          if (invoice != null) ...[
            const SizedBox(height: 12),
            const Divider(),
            const SizedBox(height: 8),
            Row(
              children: [
                const Icon(Icons.receipt_outlined,
                    size: 16, color: EteraTheme.green),
                const SizedBox(width: 6),
                Text('Invoice: ${invoice!['sku'] ?? ''}',
                    style: const TextStyle(
                        color: EteraTheme.green,
                        fontWeight: FontWeight.w600,
                        fontSize: 13)),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _Row(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(top: 6),
      child: Row(
        children: [
          Icon(icon, size: 14, color: EteraTheme.textMuted),
          const SizedBox(width: 6),
          Text('$label: ',
              style: const TextStyle(
                  fontSize: 12, color: EteraTheme.textMuted)),
          Expanded(
            child: Text(value,
                style: const TextStyle(
                    fontSize: 12, fontWeight: FontWeight.w500),
                overflow: TextOverflow.ellipsis),
          ),
        ],
      ),
    );
  }
}

// ─── Part card ────────────────────────────────────────────────────
class _PartCard extends StatelessWidget {
  final Map part;
  const _PartCard({required this.part});

  @override
  Widget build(BuildContext context) {
    final component = part['component']?.toString() ?? '';
    final condition = part['condition']?.toString() ?? '';
    final number = part['number']?.toString() ?? '';
    final qty = part['quantity']?.toString() ?? '1';
    final grade = part['grade']?.toString() ?? '';

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: EteraTheme.green.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(Icons.settings_outlined,
                size: 18, color: EteraTheme.green),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(component,
                    style: const TextStyle(
                        fontWeight: FontWeight.w600, fontSize: 14)),
                if (number.isNotEmpty)
                  Text('#$number',
                      style: const TextStyle(
                          fontSize: 12, color: EteraTheme.textMuted)),
                const SizedBox(height: 4),
                Wrap(
                  spacing: 8,
                  children: [
                    _Chip(condition),
                    _Chip(grade),
                    _Chip('Qty: $qty'),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Chip extends StatelessWidget {
  final String label;
  const _Chip(this.label);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: EteraTheme.bgLight,
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(label,
          style:
              const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
    );
  }
}

// ─── Application (quote) card ─────────────────────────────────────
class _ApplicationCard extends StatelessWidget {
  final Map app;
  const _ApplicationCard({required this.app});

  @override
  Widget build(BuildContext context) {
    final applicant = app['applicant'] is Map ? app['applicant'] as Map : {};
    final name = applicant['name']?.toString() ?? '—';
    final amount = (app['amount'] ?? app['net_total'] ?? 0).toDouble();
    final discount = (app['discount'] ?? app['discount_pct'] ?? 0).toDouble();
    final from = app['from']?.toString() ?? '';

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(14),
      child: Row(
        children: [
          CircleAvatar(
            radius: 18,
            backgroundColor: EteraTheme.green.withValues(alpha: 0.1),
            child: Text(name.isNotEmpty ? name[0].toUpperCase() : '?',
                style: const TextStyle(
                    color: EteraTheme.green, fontWeight: FontWeight.w700)),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name,
                    style: const TextStyle(fontWeight: FontWeight.w600)),
                Text(from,
                    style: const TextStyle(
                        fontSize: 12, color: EteraTheme.textMuted)),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text('${amount.toStringAsFixed(2)} Br',
                  style: const TextStyle(
                      fontWeight: FontWeight.w700, color: EteraTheme.green)),
              if (discount > 0)
                Text('${discount.toStringAsFixed(0)}% off',
                    style: const TextStyle(
                        fontSize: 11, color: Colors.orange)),
            ],
          ),
        ],
      ),
    );
  }
}

// ─── Helpers ──────────────────────────────────────────────────────
class _SectionTitle extends StatelessWidget {
  final String title;
  const _SectionTitle(this.title);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child:
          Text(title, style: Theme.of(context).textTheme.titleMedium),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color color;
    switch (status.toLowerCase()) {
      case 'opened':
      case 'published':
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
