import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../services/shop_service.dart';
import '../../widgets/etera_card.dart';

class ShopProformaDetailScreen extends StatefulWidget {
  final int proformaId;
  const ShopProformaDetailScreen({super.key, required this.proformaId});

  @override
  State<ShopProformaDetailScreen> createState() => _ShopProformaDetailScreenState();
}

class _ShopProformaDetailScreenState extends State<ShopProformaDetailScreen> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic>? _proforma;
  List<dynamic> _parts = [];
  bool _alreadyApplied = false;
  bool _submitting = false;

  final _formKey = GlobalKey<FormState>();
  final _amountCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _amountCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await ShopService.getProformaDetail(widget.proformaId);
    if (!mounted) return;
    if (res['success'] == true && res['data'] is Map) {
      final data = res['data'] as Map;
      setState(() {
        _loading = false;
        _proforma = Map<String, dynamic>.from(data['proforma'] as Map? ?? data);
        _parts = data['parts'] as List? ?? [];
        _alreadyApplied = res['already_applied'] == true;
      });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load'; });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _submitting = true);
    final res = await ShopService.applyProforma(widget.proformaId, {
      'amount': double.parse(_amountCtrl.text.trim()),
    });
    if (!mounted) return;
    setState(() => _submitting = false);
    if (res['success'] == true) {
      setState(() => _alreadyApplied = true);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Quote submitted successfully!'),
            backgroundColor: EteraTheme.green),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message']?.toString() ?? 'Failed to submit'),
            backgroundColor: EteraTheme.error),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final p = _proforma;
    final brand = p == null ? '' : ((p['brand'] as Map?)?['name']?.toString() ?? '');
    final model = p?['model']?.toString() ?? '';
    final year = p?['year']?.toString() ?? '';
    final fileNum = p?['file_number']?.toString() ?? '';

    return Scaffold(
      appBar: AppBar(
        title: Text(fileNum.isNotEmpty ? 'File #$fileNum' : 'Proforma Detail'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
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
                    // Vehicle info
                    EteraCard(
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Text('$brand $model $year',
                            style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 18)),
                        const SizedBox(height: 8),
                        _InfoRow(label: 'Customer', value: p?['customer_name']?.toString() ?? ''),
                        _InfoRow(label: 'Phone', value: p?['customer_phone_number']?.toString() ?? ''),
                        _InfoRow(label: 'Plate', value: p?['license_plate_number']?.toString() ?? ''),
                        if ((p?['chassis_number']?.toString() ?? '').isNotEmpty)
                          _InfoRow(label: 'Chassis', value: p!['chassis_number'].toString()),
                      ]),
                    ),
                    const SizedBox(height: 16),

                    // Parts
                    if (_parts.isNotEmpty) ...[
                      Text('Parts Required (${_parts.length})',
                          style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 8),
                      ..._parts.map((pt) {
                        final part = pt as Map;
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: EteraCard(
                            child: Row(children: [
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
                            ]),
                          ),
                        );
                      }),
                      const SizedBox(height: 16),
                    ],

                    // Submit quote form
                    if (!_alreadyApplied) ...[
                      Text('Submit Quote', style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 12),
                      EteraCard(
                        child: Form(
                          key: _formKey,
                          child: Column(children: [
                            TextFormField(
                              controller: _amountCtrl,
                              keyboardType: const TextInputType.numberWithOptions(decimal: true),
                              decoration: const InputDecoration(
                                  labelText: 'Your Price (Br)',
                                  prefixIcon: Icon(Icons.attach_money)),
                              validator: (v) {
                                if (v == null || v.trim().isEmpty) return 'Required';
                                final a = double.tryParse(v.trim());
                                if (a == null || a <= 0) return 'Enter a valid amount';
                                return null;
                              },
                            ),
                            const SizedBox(height: 16),
                            SizedBox(
                              width: double.infinity,
                              child: ElevatedButton.icon(
                                onPressed: _submitting ? null : _submit,
                                icon: _submitting
                                    ? const SizedBox(width: 18, height: 18,
                                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                    : const Icon(Icons.send_outlined),
                                label: Text(_submitting ? 'Submitting…' : 'Submit Quote'),
                                style: ElevatedButton.styleFrom(
                                    backgroundColor: EteraTheme.green,
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(vertical: 14)),
                              ),
                            ),
                          ]),
                        ),
                      ),
                    ] else
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: EteraTheme.green.withValues(alpha: 0.08),
                          borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
                          border: Border.all(color: EteraTheme.green.withValues(alpha: 0.3)),
                        ),
                        child: const Row(children: [
                          Icon(Icons.check_circle, color: EteraTheme.green),
                          SizedBox(width: 12),
                          Text('You have already submitted a quote.',
                              style: TextStyle(fontWeight: FontWeight.w600)),
                        ]),
                      ),
                  ]),
                ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final String label, value;
  const _InfoRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(top: 4),
    child: Row(children: [
      Text('$label: ', style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
      Expanded(child: Text(value, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600))),
    ]),
  );
}
