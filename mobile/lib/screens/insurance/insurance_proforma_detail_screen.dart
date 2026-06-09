import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../services/insurance_service.dart';
import '../../widgets/etera_card.dart';

class InsuranceProformaDetailScreen extends StatefulWidget {
  final int proformaId;
  const InsuranceProformaDetailScreen({super.key, required this.proformaId});

  @override
  State<InsuranceProformaDetailScreen> createState() =>
      _InsuranceProformaDetailScreenState();
}

class _InsuranceProformaDetailScreenState
    extends State<InsuranceProformaDetailScreen> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic>? _proforma;
  List<dynamic> _parts = [];
  List<dynamic> _applications = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await InsuranceService.getProformaDetail(widget.proformaId);
    if (!mounted) return;
    if (res['success'] == true && res['data'] is Map) {
      final data = res['data'] as Map;
      setState(() {
        _loading = false;
        _proforma = Map<String, dynamic>.from(data['proforma'] as Map? ?? data);
        _parts = data['parts'] as List? ?? [];
        _applications = data['applications'] as List? ?? [];
      });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load'; });
    }
  }

  Future<void> _requestClose() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Request Close'),
        content: const Text('Are you sure you want to request closing this file?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: EteraTheme.error),
            child: const Text('Confirm'),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    final res = await InsuranceService.requestClose(widget.proformaId);
    if (!mounted) return;
    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Close request submitted.'), backgroundColor: EteraTheme.green),
      );
      _load();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(res['message']?.toString() ?? 'Failed'),
        backgroundColor: EteraTheme.error,
      ));
    }
  }

  @override
  Widget build(BuildContext context) {
    final p = _proforma;
    final brand = p == null ? '' : ((p['brand'] as Map?)?['name']?.toString() ?? '');
    final model = p?['model']?.toString() ?? '';
    final year = p?['year']?.toString() ?? '';
    final fileNum = p?['file_number']?.toString() ?? '';
    final status = p?['status']?.toString() ?? '';
    final sColor = status == 'completed'
        ? EteraTheme.green
        : status == 'closed'
            ? EteraTheme.teal
            : Colors.orange;

    return Scaffold(
      appBar: AppBar(
        title: Text(fileNum.isNotEmpty ? 'File #$fileNum' : 'File Detail'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          if (status == 'published' || status == 'pending')
            TextButton(
              onPressed: _requestClose,
              child: const Text('Close', style: TextStyle(color: EteraTheme.error)),
            ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: EteraTheme.green))
          : _error != null
              ? Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                  Text(_error!, style: const TextStyle(color: EteraTheme.error)),
                  const SizedBox(height: 12),
                  ElevatedButton(onPressed: _load, child: const Text('Retry')),
                ]))
              : SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    // Status banner
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: sColor.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
                        border: Border.all(color: sColor.withValues(alpha: 0.3)),
                      ),
                      child: Row(children: [
                        Icon(Icons.shield_outlined, color: sColor),
                        const SizedBox(width: 12),
                        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          Text('$brand $model $year',
                              style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
                          const SizedBox(height: 2),
                          Text(status.toUpperCase(),
                              style: TextStyle(fontSize: 12, color: sColor, fontWeight: FontWeight.w700)),
                        ])),
                      ]),
                    ),
                    const SizedBox(height: 16),

                    // Customer / vehicle info
                    EteraCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      const Text('Details', style: TextStyle(fontWeight: FontWeight.w700)),
                      const SizedBox(height: 8),
                      _Row('Customer', p?['customer_name']?.toString() ?? ''),
                      _Row('Phone', p?['customer_phone_number']?.toString() ?? ''),
                      _Row('Plate', p?['license_plate_number']?.toString() ?? ''),
                      _Row('File #', fileNum),
                      _Row('Insured', p?['insured'] == true ? 'Yes' : 'No'),
                    ])),
                    const SizedBox(height: 16),

                    // Parts
                    if (_parts.isNotEmpty) ...[
                      Text('Parts (${_parts.length})', style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 8),
                      ..._parts.map((pt) {
                        final part = pt as Map;
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: EteraCard(child: Row(children: [
                            Container(
                              width: 36, height: 36,
                              decoration: BoxDecoration(
                                color: EteraTheme.green.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Icon(Icons.build_outlined, size: 18, color: EteraTheme.green),
                            ),
                            const SizedBox(width: 12),
                            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              Text(part['name']?.toString() ?? part['number']?.toString() ?? '—',
                                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                              Text('${part['grade'] ?? ''} • Qty: ${part['quantity'] ?? 1}',
                                  style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                            ])),
                          ])),
                        );
                      }),
                      const SizedBox(height: 16),
                    ],

                    // Applications/quotes received
                    if (_applications.isNotEmpty) ...[
                      Text('Quotes Received (${_applications.length})',
                          style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 8),
                      ..._applications.map((a) {
                        final app = a as Map;
                        final applicant = app['applicant'] as Map? ?? {};
                        final amount = (app['amount'] as num?)?.toDouble() ?? 0;
                        final appStatus = app['status']?.toString() ?? '';
                        final aColor = appStatus == 'selected'
                            ? EteraTheme.green
                            : appStatus == 'rejected'
                                ? EteraTheme.error
                                : Colors.orange;
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: EteraCard(child: Row(children: [
                            CircleAvatar(
                              radius: 18,
                              backgroundColor: EteraTheme.green.withValues(alpha: 0.15),
                              child: Text(
                                (applicant['name']?.toString() ?? 'U')[0].toUpperCase(),
                                style: const TextStyle(color: EteraTheme.green, fontWeight: FontWeight.w700),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              Text(applicant['name']?.toString() ?? '—',
                                  style: const TextStyle(fontWeight: FontWeight.w600)),
                              Text('${amount.toStringAsFixed(2)} Br',
                                  style: const TextStyle(fontSize: 13, color: EteraTheme.green,
                                      fontWeight: FontWeight.w600)),
                            ])),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: aColor.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(appStatus,
                                  style: TextStyle(fontSize: 11, color: aColor, fontWeight: FontWeight.w600)),
                            ),
                          ])),
                        );
                      }),
                    ],
                  ]),
                ),
    );
  }
}

class _Row extends StatelessWidget {
  final String label, value;
  const _Row(this.label, this.value);

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(top: 4),
    child: Row(children: [
      Text('$label: ', style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
      Expanded(child: Text(value,
          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600))),
    ]),
  );
}
