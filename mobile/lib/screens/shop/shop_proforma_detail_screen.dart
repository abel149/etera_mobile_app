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
  List<Map<String, dynamic>> _parts = [];
  bool _alreadyApplied = false;
  bool _submitting = false;

  final _formKey = GlobalKey<FormState>();
  final _discountCtrl = TextEditingController(text: '0');
  List<TextEditingController> _priceControllers = [];
  double _liveTotal = 0;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _discountCtrl.dispose();
    for (final c in _priceControllers) { c.dispose(); }
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await ShopService.getProformaDetail(widget.proformaId);
    if (!mounted) return;
    if (res['success'] == true && res['data'] is Map) {
      final data = res['data'] as Map;
      final rawParts = (data['parts'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      for (final c in _priceControllers) { c.dispose(); }
      final controllers = rawParts.map((_) {
        final c = TextEditingController();
        c.addListener(_recalc);
        return c;
      }).toList();
      _discountCtrl.addListener(_recalc);
      setState(() {
        _loading = false;
        _proforma = Map<String, dynamic>.from(data['proforma'] as Map? ?? data);
        _parts = rawParts;
        _priceControllers = controllers;
        _alreadyApplied = data['already_applied'] == true ||
            res['already_applied'] == true;
        _liveTotal = 0;
      });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load'; });
    }
  }

  void _recalc() {
    double subtotal = 0;
    for (int i = 0; i < _parts.length; i++) {
      final qty = ((_parts[i]['quantity'] as num?)?.toDouble() ?? 1);
      final unitPrice = double.tryParse(_priceControllers[i].text.trim()) ?? 0;
      subtotal += unitPrice * qty;
    }
    final discount = double.tryParse(_discountCtrl.text.trim()) ?? 0;
    final discountedTotal = subtotal - (subtotal * discount / 100);
    setState(() => _liveTotal = discountedTotal < 0 ? 0 : discountedTotal);
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    final parts = <Map<String, dynamic>>[];
    for (int i = 0; i < _parts.length; i++) {
      final unitPrice = double.tryParse(_priceControllers[i].text.trim()) ?? 0;
      if (unitPrice > 0) {
        parts.add({
          'proforma_part_id': _parts[i]['id'],
          'unit_price': unitPrice,
        });
      }
    }
    if (parts.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Enter at least one part price.'),
            backgroundColor: EteraTheme.error),
      );
      return;
    }

    setState(() => _submitting = true);
    final res = await ShopService.applyProforma(widget.proformaId, {
      'discount': double.tryParse(_discountCtrl.text.trim()) ?? 0,
      'parts': parts,
    });
    if (!mounted) return;
    setState(() => _submitting = false);
    if (res['success'] == true) {
      setState(() => _alreadyApplied = true);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Quote submitted successfully!'),
            backgroundColor: EteraTheme.green, behavior: SnackBarBehavior.floating),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message']?.toString() ?? 'Failed to submit'),
            backgroundColor: EteraTheme.error, behavior: SnackBarBehavior.floating),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final p = _proforma;
    String brand = '';
    if (p != null) {
      final bd = p['brand'];
      brand = bd is Map ? (bd['name']?.toString() ?? '') : (bd?.toString() ?? '');
    }
    final fileNum = p?['file_number']?.toString() ?? '';
    final model = p?['model']?.toString() ?? '';
    final year = p?['year']?.toString() ?? '';

    return Scaffold(
      appBar: AppBar(
        title: Text(fileNum.isNotEmpty ? 'File #$fileNum' : 'Proforma Detail'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loading ? null : _load),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: EteraTheme.green))
          : _error != null
              ? Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                  const Icon(Icons.error_outline, size: 48, color: EteraTheme.error),
                  const SizedBox(height: 12),
                  Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
                  const SizedBox(height: 16),
                  ElevatedButton(onPressed: _load, child: const Text('Retry')),
                ]))
              : SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 40),
                  child: Form(
                    key: _formKey,
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

                      // ── Vehicle info ──────────────────────────────────
                      EteraCard(
                        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          Row(children: [
                            Container(
                              width: 42, height: 42,
                              decoration: BoxDecoration(
                                color: EteraTheme.green.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(Icons.directions_car_outlined, color: EteraTheme.green),
                            ),
                            const SizedBox(width: 12),
                            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              Text('$brand $model $year',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
                              Text('File #$fileNum', style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                            ])),
                          ]),
                          const SizedBox(height: 10),
                          const Divider(height: 1),
                          const SizedBox(height: 10),
                          _InfoRow(label: 'Customer', value: p?['customer_name']?.toString() ?? ''),
                          _InfoRow(label: 'Phone',    value: p?['customer_phone_number']?.toString() ?? ''),
                          _InfoRow(label: 'Plate',    value: p?['license_plate_number']?.toString() ?? ''),
                          if ((p?['chassis_number']?.toString() ?? '').isNotEmpty)
                            _InfoRow(label: 'Chassis', value: p!['chassis_number'].toString()),
                          _InfoRow(label: 'Car Type', value: p?['car_type']?.toString() ?? ''),
                        ]),
                      ),
                      const SizedBox(height: 20),

                      // ── Already applied banner ────────────────────────
                      if (_alreadyApplied)
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
                            Expanded(child: Text('You have already submitted a quote for this proforma.',
                                style: TextStyle(fontWeight: FontWeight.w600))),
                          ]),
                        ),

                      // ── Parts + price inputs ─────────────────────────
                      if (_parts.isNotEmpty) ...[
                        Row(children: [
                          Text('Parts Required (${_parts.length})',
                              style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
                          const Spacer(),
                          if (!_alreadyApplied)
                            Text('Enter unit price per part',
                                style: TextStyle(fontSize: 11, color: EteraTheme.green.withValues(alpha: 0.8))),
                        ]),
                        const SizedBox(height: 10),
                        ...List.generate(_parts.length, (i) {
                          final part = _parts[i];
                          final qty = (part['quantity'] as num?)?.toInt() ?? 1;
                          final name = part['name']?.toString() ?? '';
                          final number = part['number']?.toString() ?? '';
                          final grade = part['grade']?.toString() ?? '';
                          final condition = part['condition']?.toString() ?? '';
                          final component = part['component']?.toString() ?? '';
                          final label = name.isNotEmpty ? name : (component.isNotEmpty ? component : number);

                          return EteraCard(
                            margin: const EdgeInsets.only(bottom: 10),
                            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              // Part header
                              Row(children: [
                                Container(
                                  width: 30, height: 30,
                                  decoration: BoxDecoration(
                                    color: EteraTheme.green.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(7),
                                  ),
                                  child: Center(
                                    child: Text('${i + 1}',
                                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: EteraTheme.green)),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                                  Text(label.isNotEmpty ? label : 'Part #${i + 1}',
                                      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                                  Wrap(spacing: 8, children: [
                                    if (number.isNotEmpty) _Chip('# $number'),
                                    if (grade.isNotEmpty)  _Chip(grade),
                                    if (condition.isNotEmpty) _Chip(condition),
                                    _Chip('Qty: $qty'),
                                  ]),
                                ])),
                              ]),
                              // Price input (only when not yet applied)
                              if (!_alreadyApplied) ...[
                                const SizedBox(height: 10),
                                TextFormField(
                                  controller: _priceControllers[i],
                                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                  decoration: InputDecoration(
                                    labelText: 'Unit Price (Br) × $qty',
                                    hintText: '0.00',
                                    prefixIcon: const Icon(Icons.attach_money, size: 18),
                                    suffixText: qty > 1
                                        ? '= ${((double.tryParse(_priceControllers[i].text.trim()) ?? 0) * qty).toStringAsFixed(0)} Br'
                                        : null,
                                    isDense: true,
                                  ),
                                  validator: (v) {
                                    if (v == null || v.trim().isEmpty) return null;
                                    if (double.tryParse(v.trim()) == null) return 'Invalid number';
                                    return null;
                                  },
                                ),
                              ],
                            ]),
                          );
                        }),
                      ],

                      // ── Discount + total ─────────────────────────────
                      if (!_alreadyApplied) ...[
                        const SizedBox(height: 6),
                        EteraCard(
                          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                            const Text('Discount & Total',
                                style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                            const SizedBox(height: 10),
                            TextFormField(
                              controller: _discountCtrl,
                              keyboardType: const TextInputType.numberWithOptions(decimal: true),
                              decoration: const InputDecoration(
                                labelText: 'Discount %',
                                hintText: '0',
                                prefixIcon: Icon(Icons.percent, size: 18),
                                isDense: true,
                              ),
                              validator: (v) {
                                if (v == null || v.trim().isEmpty) return null;
                                final d = double.tryParse(v.trim());
                                if (d == null || d < 0 || d > 100) return '0–100 only';
                                return null;
                              },
                            ),
                            const SizedBox(height: 14),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              decoration: BoxDecoration(
                                gradient: EteraTheme.primaryGradient,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Row(children: [
                                const Text('Total Quote:', style: TextStyle(color: Colors.white, fontSize: 13)),
                                const Spacer(),
                                Text('${_liveTotal.toStringAsFixed(2)} Br',
                                    style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800)),
                              ]),
                            ),
                          ]),
                        ),
                        const SizedBox(height: 20),

                        // ── Submit button ──────────────────────────────
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton.icon(
                            onPressed: _submitting ? null : _submit,
                            icon: _submitting
                                ? const SizedBox(width: 18, height: 18,
                                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                : const Icon(Icons.send_outlined),
                            label: Text(_submitting ? 'Submitting…' : 'Submit Quote',
                                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
                            style: ElevatedButton.styleFrom(
                                backgroundColor: EteraTheme.green,
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(vertical: 15)),
                          ),
                        ),
                      ],
                    ]),
                  ),
                ),
    );
  }
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
class _InfoRow extends StatelessWidget {
  final String label, value;
  const _InfoRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(top: 5),
    child: Row(children: [
      SizedBox(width: 75, child: Text('$label:', style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted))),
      Expanded(child: Text(value, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600))),
    ]),
  );
}

class _Chip extends StatelessWidget {
  final String text;
  const _Chip(this.text);

  @override
  Widget build(BuildContext context) => Container(
    margin: const EdgeInsets.only(top: 3),
    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
    decoration: BoxDecoration(
      color: EteraTheme.bgLight,
      borderRadius: BorderRadius.circular(6),
    ),
    child: Text(text, style: const TextStyle(fontSize: 11, color: EteraTheme.textSoft)),
  );
}
