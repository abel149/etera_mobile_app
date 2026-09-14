import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../services/encryption_service.dart';
import '../../services/garage_service.dart';
import '../../utils/e2e_crypto.dart';
import '../../widgets/etera_card.dart';

class GarageInboxDetailScreen extends StatefulWidget {
  final int proformaId;
  const GarageInboxDetailScreen({super.key, required this.proformaId});

  @override
  State<GarageInboxDetailScreen> createState() =>
      _GarageInboxDetailScreenState();
}

class _GarageInboxDetailScreenState extends State<GarageInboxDetailScreen> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic>? _proforma;
  List<dynamic> _parts        = [];
  bool _alreadyApplied        = false;
  bool _submitting            = false;

  // Encryption state
  bool   _encryptionRequired  = false;
  String _insurancePublicKey  = '';

  final _formKey      = GlobalKey<FormState>();
  final _amountCtrl   = TextEditingController();
  final _discountCtrl = TextEditingController(text: '0');

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _amountCtrl.dispose();
    _discountCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });

    // Fetch proforma detail and public-key info in parallel
    final results = await Future.wait([
      GarageService.getProformaDetail(widget.proformaId),
      EncryptionService.getPublicKey(widget.proformaId),
    ]);

    final res   = results[0];
    final pkRes = results[1];

    if (!mounted) return;

    if (pkRes['success'] == true) {
      _encryptionRequired = pkRes['has_encryption'] == true;
      _insurancePublicKey = pkRes['public_key']?.toString() ?? '';
    }

    if (res['success'] == true && res['data'] is Map) {
      final data = res['data'] as Map;
      setState(() {
        _loading         = false;
        _proforma        = Map<String, dynamic>.from(data['proforma'] as Map? ?? {});
        _parts           = data['parts'] as List? ?? [];
        _alreadyApplied  = res['already_applied'] == true || data['already_applied'] == true;
      });
    } else {
      setState(() {
        _loading = false;
        _error   = res['message']?.toString() ?? 'Failed to load';
      });
    }
  }

  // ── Plain submission ──────────────────────────────────────────────────────
  Future<void> _submitPlain() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _submitting = true);

    final amount   = double.tryParse(_amountCtrl.text.trim())   ?? 0;
    final discount = double.tryParse(_discountCtrl.text.trim()) ?? 0;

    final res = await GarageService.applyProforma(
      proformaId: widget.proformaId,
      amount: amount,
      discount: discount > 0 ? discount : null,
    );

    if (!mounted) return;
    setState(() => _submitting = false);

    if (res['success'] == true) {
      _showSnack('Quote submitted successfully!');
      Navigator.pop(context, true);
    } else {
      _showSnack(res['message']?.toString() ?? 'Failed to submit quote', error: true);
    }
  }

  // ── Encrypted submission ──────────────────────────────────────────────────
  Future<void> _submitEncrypted() async {
    if (!_formKey.currentState!.validate()) return;
    if (_insurancePublicKey.isEmpty) {
      _showSnack('Could not load the insurance public key. Please refresh.', error: true);
      return;
    }

    final amount = double.tryParse(_amountCtrl.text.trim()) ?? 0;
    if (amount <= 0) {
      _showSnack('Enter a valid amount before submitting.', error: true);
      return;
    }

    setState(() => _submitting = true);
    try {
      final encrypted = encryptAmount(amount, _insurancePublicKey);
      final discount  = double.tryParse(_discountCtrl.text.trim()) ?? 0;

      final res = await GarageService.applyProformaEncrypted(
        proformaId:      widget.proformaId,
        encryptedAmount: encrypted.encryptedAmount,
        discount:        discount > 0 ? discount : null,
      );

      if (!mounted) return;
      setState(() => _submitting = false);

      if (res['success'] == true) {
        _showSnack('Encrypted quote submitted successfully!');
        Navigator.pop(context, true);
      } else {
        _showSnack(res['message']?.toString() ?? 'Submission failed.', error: true);
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _submitting = false);
      _showSnack('Encryption error: $e', error: true);
    }
  }

  void _showSnack(String msg, {bool error = false}) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: error ? EteraTheme.error : EteraTheme.green,
      behavior: SnackBarBehavior.floating,
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_proforma != null
            ? (_proforma!['file_number']?.toString() ?? 'Proforma Detail')
            : 'Proforma Detail'),
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
                  Text(_error!, style: const TextStyle(color: EteraTheme.error)),
                  const SizedBox(height: 12),
                  ElevatedButton(onPressed: _load, child: const Text('Retry')),
                ]))
              : _buildContent(),
    );
  }

  Widget _buildContent() {
    final p      = _proforma!;
    final status = p['status']?.toString() ?? '';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

        // ── Already applied banner ──────────────────────────────────
        if (_alreadyApplied)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(12),
            margin: const EdgeInsets.only(bottom: 16),
            decoration: BoxDecoration(
              color: EteraTheme.green.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
              border: Border.all(color: EteraTheme.green.withValues(alpha: 0.3)),
            ),
            child: const Row(children: [
              Icon(Icons.check_circle, color: EteraTheme.green, size: 20),
              SizedBox(width: 8),
              Text('You have already submitted a quote',
                  style: TextStyle(color: EteraTheme.green, fontWeight: FontWeight.w600)),
            ]),
          ),

        // ── Encryption notice ───────────────────────────────────────
        if (_encryptionRequired && !_alreadyApplied)
          Container(
            margin: const EdgeInsets.only(bottom: 16),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.orange.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
              border: Border.all(color: Colors.orange.withValues(alpha: 0.3)),
            ),
            child: const Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Icon(Icons.lock_outlined, color: Colors.orange, size: 18),
              SizedBox(width: 10),
              Expanded(child: Text(
                'This insurance user has enabled E2E encryption. '
                'Your price quote will be encrypted before submission — '
                'only the insurance user can read the actual amount.',
                style: TextStyle(fontSize: 13, color: Colors.orange, height: 1.4),
              )),
            ]),
          ),

        // ── Vehicle info ────────────────────────────────────────────
        EteraCard(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              const Icon(Icons.directions_car_outlined, color: EteraTheme.green, size: 20),
              const SizedBox(width: 8),
              const Text('Vehicle',
                  style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
              const Spacer(),
              _StatusChip(status: status),
            ]),
            const Divider(height: 20),
            _InfoRow('Brand',   p['brand']?.toString()              ?? '—'),
            _InfoRow('Model',   p['model']?.toString()              ?? '—'),
            _InfoRow('Year',    p['year']?.toString()               ?? '—'),
            _InfoRow('Type',    p['car_type']?.toString()           ?? '—'),
            if ((p['license_plate_number']?.toString() ?? '').isNotEmpty)
              _InfoRow('Plate', p['license_plate_number'].toString()),
            if ((p['chassis_number']?.toString() ?? '').isNotEmpty)
              _InfoRow('Chassis', p['chassis_number'].toString()),
          ]),
        ),
        const SizedBox(height: 12),

        // ── Parts ───────────────────────────────────────────────────
        if (_parts.isNotEmpty) ...[
          Text('Parts (${_parts.length})',
              style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          ..._parts.map((part) {
            final mp = part as Map;
            return Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: EteraCard(
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(mp['name']?.toString() ?? '—',
                      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                  const SizedBox(height: 6),
                  Wrap(spacing: 8, runSpacing: 4, children: [
                    _Tag(mp['component']?.toString() ?? ''),
                    _Tag(mp['condition']?.toString() ?? ''),
                    _Tag('Qty: ${mp['quantity'] ?? 1}'),
                    if ((mp['country']?.toString() ?? '').isNotEmpty)
                      _Tag(mp['country'].toString()),
                  ]),
                ]),
              ),
            );
          }),
          const SizedBox(height: 12),
        ],

        // ── Quote form ──────────────────────────────────────────────
        if (!_alreadyApplied && ['pending', 'opened', 'published'].contains(status)) ...[
          Text('Submit Your Quote',
              style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 12),
          EteraCard(
            child: Form(
              key: _formKey,
              child: Column(children: [
                TextFormField(
                  controller: _amountCtrl,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration: InputDecoration(
                    labelText: _encryptionRequired
                        ? 'Total Amount — Br (will be encrypted)'
                        : 'Total Amount (Br)',
                    prefixIcon: const Icon(Icons.attach_money),
                    suffixIcon: _encryptionRequired
                        ? const Padding(
                            padding: EdgeInsets.only(right: 10),
                            child: Icon(Icons.lock_outlined, size: 18, color: Colors.orange),
                          )
                        : null,
                  ),
                  validator: (v) {
                    if (v == null || v.trim().isEmpty) return 'Required';
                    if (double.tryParse(v.trim()) == null) return 'Enter a valid number';
                    if ((double.tryParse(v.trim()) ?? 0) <= 0) return 'Must be greater than 0';
                    return null;
                  },
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _discountCtrl,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration: const InputDecoration(
                    labelText: 'Discount % (optional)',
                    prefixIcon: Icon(Icons.percent),
                  ),
                  validator: (v) {
                    if (v == null || v.trim().isEmpty) return null;
                    final d = double.tryParse(v.trim());
                    if (d == null) return 'Enter a valid number';
                    if (d < 0 || d > 100) return '0–100 only';
                    return null;
                  },
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _submitting
                        ? null
                        : _encryptionRequired
                            ? _submitEncrypted
                            : _submitPlain,
                    icon: _submitting
                        ? const SizedBox(width: 18, height: 18,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : Icon(_encryptionRequired ? Icons.lock_outlined : Icons.send_outlined),
                    label: Text(
                      _submitting
                          ? 'Submitting…'
                          : _encryptionRequired
                              ? 'Submit Encrypted Quote'
                              : 'Submit Quote',
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: EteraTheme.green,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                  ),
                ),
              ]),
            ),
          ),
        ],
      ]),
    );
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
class _InfoRow extends StatelessWidget {
  final String label;
  final String value;
  const _InfoRow(this.label, this.value);

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 3),
    child: Row(children: [
      SizedBox(width: 80, child: Text(label,
          style: const TextStyle(fontSize: 13, color: EteraTheme.textMuted))),
      Expanded(child: Text(value,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
          overflow: TextOverflow.ellipsis)),
    ]),
  );
}

class _Tag extends StatelessWidget {
  final String text;
  const _Tag(this.text);

  @override
  Widget build(BuildContext context) => text.isEmpty ? const SizedBox.shrink() : Container(
    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
    decoration: BoxDecoration(
      color: EteraTheme.green.withValues(alpha: 0.1),
      borderRadius: BorderRadius.circular(6),
    ),
    child: Text(text, style: const TextStyle(fontSize: 11, color: EteraTheme.green)),
  );
}

class _StatusChip extends StatelessWidget {
  final String status;
  const _StatusChip({required this.status});

  @override
  Widget build(BuildContext context) {
    final color = switch (status.toLowerCase()) {
      'published' || 'opened' => Colors.blue,
      'pending'               => Colors.orange,
      'closed'                => EteraTheme.error,
      'completed'             => EteraTheme.green,
      _                       => EteraTheme.textMuted,
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color:  color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.4)),
      ),
      child: Text(status, style: TextStyle(fontSize: 12, color: color, fontWeight: FontWeight.w600)),
    );
  }
}
