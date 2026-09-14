import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../services/encryption_service.dart';
import '../../services/insurance_service.dart';
import '../../utils/e2e_crypto.dart';
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
  List<dynamic> _parts    = [];
  List<dynamic> _shops    = [];
  List<dynamic> _garages  = [];
  Map<String, dynamic>? _invoice;

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
        _loading  = false;
        _proforma = Map<String, dynamic>.from(data['proforma'] as Map? ?? data);
        _parts    = data['parts']   as List? ?? [];
        _shops    = data['shops']   as List? ?? [];
        _garages  = data['garages'] as List? ?? [];
        _invoice  = data['invoice'] as Map<String, dynamic>?;
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
    final p            = _proforma;
    final brand        = p?['brand']?.toString() ?? '';
    final model        = p?['model']?.toString() ?? '';
    final year         = p?['year']?.toString() ?? '';
    final fileNum      = p?['file_number']?.toString() ?? '';
    final status       = p?['status']?.toString() ?? '';
    final closeRequest = p?['close_request'] == true;
    final canRequest   = p?['can_request_close'] == true;
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
          if (canRequest)
            TextButton(
              onPressed: _requestClose,
              child: const Text('Request Close', style: TextStyle(color: EteraTheme.error)),
            )
          else if (closeRequest)
            const Padding(
              padding: EdgeInsets.only(right: 12),
              child: Center(
                child: Text('Close Requested', style: TextStyle(fontSize: 12, color: Colors.orange)),
              ),
            ),
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
              : RefreshIndicator(
                  color: EteraTheme.green,
                  onRefresh: _load,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

                      // ── Status banner ───────────────────────────────────
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

                      // ── Customer / vehicle details ───────────────────────
                      EteraCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        const Text('Details', style: TextStyle(fontWeight: FontWeight.w700)),
                        const SizedBox(height: 8),
                        _Row('Customer', p?['customer_name']?.toString()        ?? ''),
                        _Row('Phone',    p?['customer_phone_number']?.toString() ?? ''),
                        _Row('Plate',    p?['license_plate_number']?.toString()  ?? ''),
                        _Row('File #',   fileNum),
                        _Row('Insured',  p?['insured'] == true ? 'Yes' : 'No'),
                      ])),
                      const SizedBox(height: 16),

                      // ── Parts ────────────────────────────────────────────
                      if (_parts.isNotEmpty) ...[
                        Text('Parts (${_parts.length})',
                            style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
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

                      // ── Shop Quotes ──────────────────────────────────────
                      if (_shops.isNotEmpty) ...[
                        _SectionHeader(
                          icon: Icons.store_outlined,
                          title: 'Shop Quotes (${_shops.length})',
                        ),
                        const SizedBox(height: 8),
                        ..._shops.map((app) => _ApplicationCard(
                          application: Map<String, dynamic>.from(app as Map),
                          proformaId: widget.proformaId,
                          onDecrypted: _load,
                        )),
                        const SizedBox(height: 16),
                      ],

                      // ── Garage Quotes ────────────────────────────────────
                      if (_garages.isNotEmpty) ...[
                        _SectionHeader(
                          icon: Icons.build_circle_outlined,
                          title: 'Garage Quotes (${_garages.length})',
                        ),
                        const SizedBox(height: 8),
                        ..._garages.map((app) => _ApplicationCard(
                          application: Map<String, dynamic>.from(app as Map),
                          proformaId: widget.proformaId,
                          onDecrypted: _load,
                        )),
                        const SizedBox(height: 16),
                      ],

                      // ── Invoice ─────────────────────────────────────────
                      if (_invoice != null) ...[
                        _SectionHeader(icon: Icons.receipt_long_outlined, title: 'Invoice'),
                        const SizedBox(height: 8),
                        EteraCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          _Row('SKU',     _invoice!['sku']?.toString() ?? ''),
                          _Row('Subtotal', '${_invoice!['subtotal']} Br'),
                          _Row('VAT',      '${_invoice!['vat_amount']} Br'),
                          _Row('Total',    '${_invoice!['total_amount']} Br'),
                          _Row('Paid',     _invoice!['is_paid'] == true ? 'Yes' : 'No'),
                        ])),
                        const SizedBox(height: 16),
                      ],

                      // ── Status explanation ───────────────────────────────
                      _StatusBanner(status: status, closeRequest: closeRequest),
                    ]),
                  ),
                ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Application card — shows a shop or garage quote, handles encrypted amounts
// ─────────────────────────────────────────────────────────────────────────────
class _ApplicationCard extends StatefulWidget {
  final Map<String, dynamic> application;
  final int proformaId;
  final VoidCallback onDecrypted;

  const _ApplicationCard({
    required this.application,
    required this.proformaId,
    required this.onDecrypted,
  });

  @override
  State<_ApplicationCard> createState() => _ApplicationCardState();
}

class _ApplicationCardState extends State<_ApplicationCard> {
  bool _decrypting = false;
  String? _decryptedAmount;
  Map<int, double> _decryptedParts = {}; // proforma_part_id → decrypted unit_price
  String? _decryptError;

  bool get _isEncrypted => widget.application['amount_is_encrypted'] == true;

  Future<void> _decrypt() async {
    setState(() { _decrypting = true; _decryptError = null; });

    // 1. Ask user for their PIN
    final pin = await _showPinDialog();
    if (pin == null || !mounted) {
      setState(() => _decrypting = false);
      return;
    }

    try {
      // 2. Fetch the encrypted private key blob
      final pkRes = await EncryptionService.getPrivateKey();
      if (pkRes['success'] != true) {
        setState(() { _decrypting = false; _decryptError = 'Could not fetch private key.'; });
        return;
      }

      // 3. Unwrap with PIN
      final privateKeyPem = unwrapPrivateKey(
        pkRes['encrypted_private_key'] as String,
        pkRes['key_iv']               as String,
        pkRes['key_salt']             as String,
        pin,
      );

      // 4. Decrypt individual part prices (shop path)
      final parts = (widget.application['parts_pricing'] as List?) ?? [];
      final decryptedParts = <int, double>{};

      for (final p in parts) {
        final part = p as Map;
        final partId = int.tryParse(part['proforma_part_id']?.toString() ?? '') ?? 0;
        final isPartEncrypted = part['price_is_encrypted'] == true;
        if (isPartEncrypted) {
          final encUnit = part['encrypted_unit_price'] as String? ?? '';
          if (encUnit.isNotEmpty) {
            decryptedParts[partId] = decryptAmount(encUnit, privateKeyPem);
          }
        }
      }

      // 5. Decrypt total amount (garage path) or compute from parts (shop path)
      String? totalAmount;
      final encryptedAmount = widget.application['encrypted_amount'] as String? ?? '';
      if (encryptedAmount.isNotEmpty) {
        final amount = decryptAmount(encryptedAmount, privateKeyPem);
        totalAmount = '${amount.toStringAsFixed(2)} Br';
      } else if (decryptedParts.isNotEmpty) {
        final discount = (widget.application['discount_pct'] as num?)?.toDouble() ?? 0;
        double subtotal = 0;
        for (final p in parts) {
          final part = p as Map;
          final partId = int.tryParse(part['proforma_part_id']?.toString() ?? '') ?? 0;
          if (decryptedParts.containsKey(partId)) {
            subtotal += decryptedParts[partId]!;
          }
        }
        final net = subtotal - (subtotal * discount / 100);
        totalAmount = '${net.toStringAsFixed(2)} Br';
      }

      if (!mounted) return;
      setState(() {
        _decrypting       = false;
        _decryptedAmount  = totalAmount;
        _decryptedParts   = decryptedParts;
      });
    } on FormatException {
      if (!mounted) return;
      setState(() { _decrypting = false; _decryptError = 'Wrong PIN. Please try again.'; });
    } catch (e) {
      if (!mounted) return;
      setState(() { _decrypting = false; _decryptError = 'Decryption failed: $e'; });
    }
  }

  Future<String?> _showPinDialog() {
    final ctrl = TextEditingController();
    return showDialog<String>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Enter Your Encryption PIN'),
        content: TextField(
          controller: ctrl,
          obscureText: true,
          keyboardType: TextInputType.number,
          decoration: const InputDecoration(
            labelText: 'PIN',
            border: OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, ctrl.text.trim()),
            style: ElevatedButton.styleFrom(backgroundColor: EteraTheme.green),
            child: const Text('Decrypt', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final app        = widget.application;
    final applicant  = app['applicant'] as Map?;
    final name       = applicant?['name']?.toString() ?? '—';
    final storeId    = applicant?['store_id']?.toString() ?? '';
    final netTotal   = (app['net_total'] as num?)?.toDouble() ?? 0;
    final discount   = (app['discount_pct'] as num?)?.toDouble() ?? 0;
    final from       = app['from']?.toString() ?? '';

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: EteraCard(
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          // Header row
          Row(children: [
            Container(
              width: 38, height: 38,
              decoration: BoxDecoration(
                color: EteraTheme.green.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(from == 'shop' ? Icons.store_outlined : Icons.build_circle_outlined,
                  size: 18, color: EteraTheme.green),
            ),
            const SizedBox(width: 10),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(name, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
              if (storeId.isNotEmpty)
                Text('ID: $storeId', style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
            ])),
            // Encrypted badge
            if (_isEncrypted && _decryptedAmount == null)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: Colors.orange.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Row(mainAxisSize: MainAxisSize.min, children: [
                  Icon(Icons.lock_outlined, size: 13, color: Colors.orange),
                  SizedBox(width: 4),
                  Text('Encrypted', style: TextStyle(fontSize: 11, color: Colors.orange, fontWeight: FontWeight.w600)),
                ]),
              )
            else
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: EteraTheme.green.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  _decryptedAmount ?? '${netTotal.toStringAsFixed(2)} Br',
                  style: const TextStyle(fontSize: 13, color: EteraTheme.green, fontWeight: FontWeight.w700),
                ),
              ),
          ]),

          // Discount info
          if (discount > 0) ...[
            const SizedBox(height: 6),
            Text('Discount: ${discount.toStringAsFixed(1)}%',
                style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ],

          // Part pricing (for shop applications)
          if ((app['parts_pricing'] as List?)?.isNotEmpty == true) ...[
            const SizedBox(height: 8),
            const Divider(height: 1),
            const SizedBox(height: 8),
            ...(app['parts_pricing'] as List).map((p) {
              final part = p as Map;
              final partId = int.tryParse(part['proforma_part_id']?.toString() ?? '') ?? 0;
              final isPartEncrypted = part['price_is_encrypted'] == true;
              final decryptedUnit = _decryptedParts[partId];
              final displayPrice = isPartEncrypted
                  ? (decryptedUnit != null
                      ? '${decryptedUnit.toStringAsFixed(2)} Br'
                      : '🔒 Encrypted')
                  : '${(part['part_total'] as num?)?.toStringAsFixed(2) ?? '—'} Br';
              return Padding(
                padding: const EdgeInsets.only(bottom: 4),
                child: Row(children: [
                  const Icon(Icons.circle, size: 6, color: EteraTheme.textMuted),
                  const SizedBox(width: 8),
                  Text('Part #${part['car_part_id']}',
                      style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                  const Spacer(),
                  Text(displayPrice,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: isPartEncrypted && decryptedUnit == null
                            ? Colors.orange
                            : null,
                      )),
                ]),
              );
            }),
          ],

          // Decrypt button
          if (_isEncrypted && _decryptedAmount == null) ...[
            const SizedBox(height: 10),
            if (_decryptError != null)
              Padding(
                padding: const EdgeInsets.only(bottom: 6),
                child: Text(_decryptError!, style: const TextStyle(color: EteraTheme.error, fontSize: 12)),
              ),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _decrypting ? null : _decrypt,
                icon: _decrypting
                    ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2))
                    : const Icon(Icons.lock_open_outlined, size: 16),
                label: Text(_decrypting ? 'Decrypting…' : 'Decrypt Amount',
                    style: const TextStyle(fontSize: 13)),
                style: OutlinedButton.styleFrom(
                  foregroundColor: EteraTheme.green,
                  side: const BorderSide(color: EteraTheme.green),
                  padding: const EdgeInsets.symmetric(vertical: 8),
                ),
              ),
            ),
          ],
        ]),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Small helpers
// ─────────────────────────────────────────────────────────────────────────────
class _SectionHeader extends StatelessWidget {
  final IconData icon;
  final String title;
  const _SectionHeader({required this.icon, required this.title});

  @override
  Widget build(BuildContext context) => Row(children: [
    Icon(icon, size: 18, color: EteraTheme.green),
    const SizedBox(width: 8),
    Text(title, style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
  ]);
}

class _Row extends StatelessWidget {
  final String label, value;
  const _Row(this.label, this.value);

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(top: 4),
    child: Row(children: [
      Text('$label: ', style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
      Expanded(child: Text(value, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600))),
    ]),
  );
}

class _StatusBanner extends StatelessWidget {
  final String status;
  final bool closeRequest;
  const _StatusBanner({required this.status, required this.closeRequest});

  @override
  Widget build(BuildContext context) {
    late IconData icon;
    late Color color;
    late String message;

    switch (status.toLowerCase()) {
      case 'pending':
        icon    = Icons.schedule_outlined;
        color   = Colors.orange;
        message = 'Waiting for admin to review and publish your request.';
        break;
      case 'published':
        icon    = Icons.how_to_vote_outlined;
        color   = EteraTheme.green;
        message = closeRequest
            ? 'Close requested — admin will finalize shortly.'
            : 'Active: shops and garages are submitting quotes.';
        break;
      case 'closed':
        icon    = Icons.hourglass_empty_outlined;
        color   = EteraTheme.teal;
        message = 'All quotes received. Waiting for admin to send results.';
        break;
      case 'completed':
        icon    = Icons.check_circle_outline;
        color   = EteraTheme.teal;
        message = 'Done! View the price quotes above.';
        break;
      default:
        icon    = Icons.info_outline;
        color   = EteraTheme.textMuted;
        message = status.isNotEmpty ? 'Status: $status' : '';
    }

    if (message.isEmpty) return const SizedBox.shrink();

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
        border: Border.all(color: color.withValues(alpha: 0.25)),
      ),
      child: Row(children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(width: 10),
        Expanded(child: Text(message, style: TextStyle(fontSize: 13, color: color, height: 1.4))),
      ]),
    );
  }
}
