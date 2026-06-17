import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/superadmin_service.dart';
import '../../widgets/etera_card.dart';

class AdminProformaDetailScreen extends StatefulWidget {
  final int proformaId;
  const AdminProformaDetailScreen({super.key, required this.proformaId});

  @override
  State<AdminProformaDetailScreen> createState() => _AdminProformaDetailScreenState();
}

class _AdminProformaDetailScreenState extends State<AdminProformaDetailScreen> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic>? _data;
  bool _actioning = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await SuperadminService.getProformaDetail(widget.proformaId);
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      setState(() { _loading = false; _data = Map<String, dynamic>.from(res['data'] as Map); });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load'; });
    }
  }

  // ── Actions ────────────────────────────────────────────────────────────────

  Future<void> _float() async {
    final ok = await _confirm('Float Proforma',
        'Float #${_data!['file_number']} so shops/garages can apply?');
    if (!ok) return;
    setState(() => _actioning = true);
    final res = await SuperadminService.floatProforma(widget.proformaId);
    if (!mounted) return;
    setState(() => _actioning = false);
    _snack(res['message']?.toString() ?? (res['success'] == true ? 'Floated!' : 'Failed'), res['success'] == true);
    if (res['success'] == true) _load();
  }

  Future<void> _close() async {
    final ok = await _confirm('Close Proforma',
        'Close #${_data!['file_number']}? This will send billing info to the poster.');
    if (!ok) return;
    setState(() => _actioning = true);
    final res = await SuperadminService.closeProforma(widget.proformaId);
    if (!mounted) return;
    setState(() => _actioning = false);
    _snack(res['message']?.toString() ?? (res['success'] == true ? 'Closed' : 'Failed'), res['success'] == true);
    if (res['success'] == true) _load();
  }

  Future<void> _sendToOwner() async {
    final ok = await _confirm('Send to Owner',
        'Close & send proforma results to the owner? This sends billing email and marks it closed.');
    if (!ok) return;
    setState(() => _actioning = true);
    final res = await SuperadminService.sendToOwner(widget.proformaId);
    if (!mounted) return;
    setState(() => _actioning = false);
    _snack(res['message']?.toString() ?? (res['success'] == true ? 'Sent to owner!' : 'Failed'), res['success'] == true);
    if (res['success'] == true) _load();
  }

  Future<void> _openInboxDialog({required bool isShops}) async {
    final available = isShops
        ? ((_data!['available_shops'] as List?) ?? [])
        : ((_data!['available_garages'] as List?) ?? []);
    final alreadyInboxed = Set<int>.from(
        ((_data!['inboxed_user_ids'] as List?) ?? []).map((e) => int.tryParse(e.toString()) ?? 0));

    final selected = Set<int>.from(alreadyInboxed);

    final result = await showModalBottomSheet<Set<int>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => _InboxPickerSheet(
        title: isShops ? 'Select Spare Part Shops' : 'Select Garages',
        users: available.map((u) => Map<String, dynamic>.from(u as Map)).toList(),
        alreadyInboxed: alreadyInboxed,
        initialSelected: selected,
      ),
    );

    if (result == null || !mounted) return;

    final newIds = result.difference(alreadyInboxed).toList();
    if (newIds.isEmpty) {
      _snack('No new users selected', false);
      return;
    }

    setState(() => _actioning = true);
    final res = isShops
        ? await SuperadminService.inboxShops(widget.proformaId, newIds)
        : await SuperadminService.inboxGarages(widget.proformaId, newIds);
    if (!mounted) return;
    setState(() => _actioning = false);
    _snack(res['message']?.toString() ?? (res['success'] == true ? 'Sent!' : 'Failed'), res['success'] == true);
    if (res['success'] == true) _load();
  }

  Future<bool> _confirm(String title, String message) async {
    return await showDialog<bool>(
          context: context,
          builder: (ctx) => AlertDialog(
            title: Text(title),
            content: Text(message),
            actions: [
              TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
              ElevatedButton(
                onPressed: () => Navigator.pop(ctx, true),
                style: ElevatedButton.styleFrom(backgroundColor: Colors.deepPurple),
                child: const Text('Confirm', style: TextStyle(color: Colors.white)),
              ),
            ],
          ),
        ) ??
        false;
  }

  void _snack(String msg, bool ok) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: ok ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
  }

  // ── Build ──────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_data?['file_number']?.toString() ?? 'Proforma Detail'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          if (!_loading && _data != null)
            IconButton(icon: const Icon(Icons.refresh), onPressed: _load),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_loading) return const Center(child: CircularProgressIndicator(color: Colors.deepPurple));
    if (_error != null) {
      return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.error_outline, size: 48, color: EteraTheme.error),
        const SizedBox(height: 12),
        Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: _load, child: const Text('Retry')),
      ]));
    }
    if (_data == null) return const SizedBox.shrink();

    final d = _data!;
    final status = d['status']?.toString() ?? 'pending';
    final closeRequest = d['close_request'] == true;
    final canFloat = status == 'pending';
    final canClose = status == 'published';
    final canSendToOwner = status == 'published';
    final applications = (d['applications'] as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
    final parts = (d['parts'] as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();

    return Stack(
      children: [
        RefreshIndicator(
          color: Colors.deepPurple,
          onRefresh: _load,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 120),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [

                // ── Status row ──────────────────────────────────────────
                Row(children: [
                  _StatusBadge(status: status),
                  const SizedBox(width: 8),
                  if (closeRequest)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.orange.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Row(mainAxisSize: MainAxisSize.min, children: [
                        Icon(Icons.flag_outlined, size: 12, color: Colors.orange),
                        SizedBox(width: 4),
                        Text('Close Requested', style: TextStyle(fontSize: 11, color: Colors.orange, fontWeight: FontWeight.w600)),
                      ]),
                    ),
                ]),
                const SizedBox(height: 16),

                // ── Proforma info ────────────────────────────────────────
                EteraCard(
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    const Text('Proforma Info', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                    const SizedBox(height: 12),
                    _row('File #', d['file_number']?.toString() ?? ''),
                    _row('From', d['from']?.toString() ?? ''),
                    _row('Poster', d['poster_name']?.toString() ?? '—'),
                    _row('Phone', d['poster_phone']?.toString() ?? '—'),
                    _row('Customer', d['customer_name']?.toString() ?? ''),
                    _row('Cust. Phone', d['customer_phone']?.toString() ?? ''),
                    _row('Brand', d['brand']?.toString() ?? ''),
                    _row('Model', '${d['model'] ?? ''} ${d['year'] ?? ''}'),
                    _row('Car Type', d['car_type']?.toString() ?? ''),
                    _row('Req. Shops', '${d['required_shops'] ?? 0}'),
                    _row('Req. Garages', '${d['required_garages'] ?? 0}'),
                    if (d['proforma_type'] != null)
                      _row('Type', d['proforma_type']!.toString()),
                  ]),
                ),
                const SizedBox(height: 16),

                // ── Parts ────────────────────────────────────────────────
                Text('Parts (${parts.length})', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
                const SizedBox(height: 10),
                if (parts.isEmpty)
                  const Text('No parts listed.', style: TextStyle(color: EteraTheme.textMuted))
                else
                  ...parts.asMap().entries.map((e) => _PartTile(index: e.key, part: e.value)),
                const SizedBox(height: 16),

                // ── Applications ─────────────────────────────────────────
                Row(children: [
                  Text('Applications (${applications.length})',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
                  const Spacer(),
                  if (status == 'published') ...[
                    _actionChip(Icons.store_outlined, 'Inbox Shops',   Colors.teal,   () => _openInboxDialog(isShops: true)),
                    const SizedBox(width: 6),
                    _actionChip(Icons.build_outlined, 'Inbox Garages', Colors.indigo, () => _openInboxDialog(isShops: false)),
                  ],
                ]),
                const SizedBox(height: 10),
                if (applications.isEmpty)
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(color: EteraTheme.bgLight, borderRadius: BorderRadius.circular(12)),
                    child: const Row(children: [
                      Icon(Icons.hourglass_empty_outlined, color: EteraTheme.textMuted, size: 18),
                      SizedBox(width: 10),
                      Text('No applications received yet.', style: TextStyle(color: EteraTheme.textMuted, fontSize: 13)),
                    ]),
                  )
                else
                  ...applications.asMap().entries.map((e) => _ApplicationTile(index: e.key + 1, app: e.value)),

                const SizedBox(height: 32),
              ],
            ),
          ),
        ),

        // ── Action bar (bottom) ──────────────────────────────────────────
        if (!_loading && _data != null)
          Positioned(
            bottom: 0, left: 0, right: 0,
            child: Container(
              padding: const EdgeInsets.fromLTRB(16, 10, 16, 24),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.06), blurRadius: 12, offset: const Offset(0, -4))],
              ),
              child: _actioning
                  ? const Center(child: CircularProgressIndicator(color: Colors.deepPurple))
                  : Row(children: [
                      if (canFloat)
                        Expanded(child: ElevatedButton.icon(
                          onPressed: _float,
                          icon: const Icon(Icons.upload_outlined, size: 18),
                          label: const Text('Float', style: TextStyle(fontWeight: FontWeight.w600)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.deepPurple,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                        )),
                      if (canClose) ...[
                        Expanded(child: OutlinedButton.icon(
                          onPressed: _close,
                          icon: const Icon(Icons.close, size: 18),
                          label: const Text('Close', style: TextStyle(fontWeight: FontWeight.w600)),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: EteraTheme.error,
                            side: const BorderSide(color: EteraTheme.error),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                        )),
                        if (applications.isNotEmpty) ...[
                          const SizedBox(width: 10),
                          Expanded(child: ElevatedButton.icon(
                            onPressed: canSendToOwner ? _sendToOwner : null,
                            icon: const Icon(Icons.send_outlined, size: 18),
                            label: const Text('Send to Owner', style: TextStyle(fontWeight: FontWeight.w600)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: EteraTheme.green,
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 12),
                            ),
                          )),
                        ],
                      ],
                      if (!canFloat && !canClose)
                        Expanded(child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            color: EteraTheme.bgLight,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            status == 'closed' ? 'Proforma Closed' : status == 'completed' ? 'Completed' : 'No actions available',
                            style: const TextStyle(color: EteraTheme.textMuted, fontWeight: FontWeight.w500),
                          ),
                        )),
                    ]),
            ),
          ),
      ],
    );
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        SizedBox(width: 110, child: Text(label, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted))),
        Expanded(child: Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500))),
      ]),
    );
  }

  Widget _actionChip(IconData icon, String label, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
        child: Row(mainAxisSize: MainAxisSize.min, children: [
          Icon(icon, size: 13, color: color),
          const SizedBox(width: 4),
          Text(label, style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w600)),
        ]),
      ),
    );
  }
}

// ─── Status Badge ─────────────────────────────────────────────────────────────
class _StatusBadge extends StatelessWidget {
  final String status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color color;
    switch (status) {
      case 'pending':   color = Colors.orange; break;
      case 'published': color = Colors.deepPurple; break;
      case 'closed':    color = EteraTheme.error; break;
      case 'completed': color = EteraTheme.green; break;
      default:          color = EteraTheme.textMuted;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
      decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(20)),
      child: Text(
        status[0].toUpperCase() + status.substring(1),
        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: color),
      ),
    );
  }
}

// ─── Part Tile ────────────────────────────────────────────────────────────────
class _PartTile extends StatelessWidget {
  final int index;
  final Map<String, dynamic> part;
  const _PartTile({required this.index, required this.part});

  @override
  Widget build(BuildContext context) {
    return EteraCard(
      margin: const EdgeInsets.only(bottom: 8),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text('Part #${index + 1}', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
        const SizedBox(height: 6),
        Wrap(spacing: 16, runSpacing: 2, children: [
          _chip('# ${part['number'] ?? '—'}'),
          if ((part['name'] ?? '').toString().isNotEmpty) _chip(part['name'].toString()),
          _chip('Grade: ${part['grade'] ?? '—'}'),
          _chip('Qty: ${part['quantity'] ?? 1}'),
          if ((part['condition'] ?? '').toString().isNotEmpty) _chip(part['condition'].toString()),
          if ((part['country'] ?? '').toString().isNotEmpty) _chip(part['country'].toString()),
          if ((part['component'] ?? '').toString().isNotEmpty) _chip(part['component'].toString()),
        ]),
      ]),
    );
  }

  Widget _chip(String text) => Container(
        margin: const EdgeInsets.only(top: 2),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(color: EteraTheme.bgLight, borderRadius: BorderRadius.circular(6)),
        child: Text(text, style: const TextStyle(fontSize: 11, color: EteraTheme.textPrimary)),
      );
}

// ─── Application Tile ─────────────────────────────────────────────────────────
class _ApplicationTile extends StatelessWidget {
  final int index;
  final Map<String, dynamic> app;
  const _ApplicationTile({required this.index, required this.app});

  @override
  Widget build(BuildContext context) {
    final from = app['from']?.toString() ?? '';
    final color = from == 'shop' ? EteraTheme.teal : Colors.indigo;
    final finalPrice = (app['final_price'] as num?)?.toDouble() ?? 0;
    final discount = (app['discount'] as num?)?.toDouble() ?? 0;
    final prices = (app['prices'] as List? ?? []);

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 8),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            width: 28, height: 28,
            decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(7)),
            child: Center(child: Text('$index', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: color))),
          ),
          const SizedBox(width: 10),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(app['applicant_name']?.toString() ?? 'Unknown',
                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
            Row(children: [
              Icon(from == 'shop' ? Icons.store_outlined : Icons.build_outlined, size: 12, color: color),
              const SizedBox(width: 3),
              Text(from.toUpperCase(), style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w600)),
            ]),
          ])),
          Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(gradient: EteraTheme.primaryGradient, borderRadius: BorderRadius.circular(16)),
              child: Text('${finalPrice.toStringAsFixed(0)} Br',
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Colors.white)),
            ),
            if (discount > 0) ...[
              const SizedBox(height: 2),
              Text('${discount.toStringAsFixed(0)}% off', style: const TextStyle(fontSize: 11, color: EteraTheme.teal)),
            ],
          ]),
        ]),
        if ((app['applicant_phone']?.toString() ?? '').isNotEmpty) ...[
          const SizedBox(height: 6),
          Row(children: [
            const Icon(Icons.phone_outlined, size: 13, color: EteraTheme.textMuted),
            const SizedBox(width: 4),
            Text(app['applicant_phone'].toString(), style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ]),
        ],
        if ((app['applicant_location']?.toString() ?? '').isNotEmpty) ...[
          const SizedBox(height: 2),
          Row(children: [
            const Icon(Icons.location_on_outlined, size: 13, color: EteraTheme.textMuted),
            const SizedBox(width: 4),
            Text(app['applicant_location'].toString(), style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ]),
        ],
        if (prices.isNotEmpty) ...[
          const SizedBox(height: 8),
          const Divider(height: 1),
          const SizedBox(height: 6),
          const Text('Part Prices:', style: TextStyle(fontSize: 11, color: EteraTheme.textMuted, fontWeight: FontWeight.w600)),
          const SizedBox(height: 4),
          ...prices.map((p) {
            final mp = Map<String, dynamic>.from(p as Map);
            return Padding(
              padding: const EdgeInsets.only(bottom: 2),
              child: Row(children: [
                Text('Qty ${mp['quantity'] ?? 1}  ×  ${(mp['unit_price'] as num?)?.toStringAsFixed(0)} Br',
                    style: const TextStyle(fontSize: 12)),
                const Spacer(),
                Text('= ${(mp['part_total'] as num?)?.toStringAsFixed(0)} Br',
                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
              ]),
            );
          }),
        ],
      ]),
    );
  }
}

// ─── Inbox Picker Sheet ───────────────────────────────────────────────────────
class _InboxPickerSheet extends StatefulWidget {
  final String title;
  final List<Map<String, dynamic>> users;
  final Set<int> alreadyInboxed;
  final Set<int> initialSelected;
  const _InboxPickerSheet({
    required this.title,
    required this.users,
    required this.alreadyInboxed,
    required this.initialSelected,
  });

  @override
  State<_InboxPickerSheet> createState() => _InboxPickerSheetState();
}

class _InboxPickerSheetState extends State<_InboxPickerSheet> {
  late Set<int> _selected;
  String _search = '';

  @override
  void initState() {
    super.initState();
    _selected = Set.from(widget.initialSelected);
  }

  List<Map<String, dynamic>> get _filtered {
    if (_search.isEmpty) return widget.users;
    final q = _search.toLowerCase();
    return widget.users.where((u) =>
        (u['name']?.toString().toLowerCase() ?? '').contains(q) ||
        (u['location']?.toString().toLowerCase() ?? '').contains(q)).toList();
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      expand: false,
      initialChildSize: 0.75,
      maxChildSize: 0.95,
      builder: (_, controller) => Padding(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
        child: Column(children: [
          Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2))),
          const SizedBox(height: 12),
          Text(widget.title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
          const SizedBox(height: 10),
          TextField(
            decoration: InputDecoration(
              hintText: 'Search by name or location…',
              prefixIcon: const Icon(Icons.search, size: 20),
              filled: true,
              fillColor: EteraTheme.bgLight,
              contentPadding: const EdgeInsets.symmetric(vertical: 10),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
            ),
            onChanged: (v) => setState(() => _search = v),
          ),
          const SizedBox(height: 10),
          Expanded(child: ListView.builder(
            controller: controller,
            itemCount: _filtered.length,
            itemBuilder: (_, i) {
              final u = _filtered[i];
              final id = int.tryParse(u['id'].toString()) ?? 0;
              final alreadySent = widget.alreadyInboxed.contains(id);
              return CheckboxListTile(
                value: _selected.contains(id),
                onChanged: alreadySent ? null : (v) {
                  setState(() {
                    if (v == true) { _selected.add(id); } else { _selected.remove(id); }
                  });
                },
                title: Text(u['name']?.toString() ?? '', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
                subtitle: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  if ((u['phone']?.toString() ?? '').isNotEmpty)
                    Text(u['phone'].toString(), style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                  if ((u['location']?.toString() ?? '').isNotEmpty)
                    Text(u['location'].toString(), style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                  if (alreadySent)
                    const Text('Already inboxed', style: TextStyle(fontSize: 11, color: EteraTheme.teal, fontWeight: FontWeight.w600)),
                ]),
                activeColor: Colors.deepPurple,
                controlAffinity: ListTileControlAffinity.trailing,
              );
            },
          )),
          const SizedBox(height: 10),
          SizedBox(width: double.infinity, child: ElevatedButton(
            onPressed: () => Navigator.pop(context, _selected),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.deepPurple,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 13),
            ),
            child: Text('Send to ${_selected.difference(widget.alreadyInboxed).length} Selected', style: const TextStyle(fontWeight: FontWeight.w600)),
          )),
          const SizedBox(height: 16),
        ]),
      ),
    );
  }
}
