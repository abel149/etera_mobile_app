import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/insurance_service.dart';
import '../../widgets/etera_card.dart';

class InsurancePartnersTab extends StatefulWidget {
  const InsurancePartnersTab({super.key});

  @override
  State<InsurancePartnersTab> createState() => _InsurancePartnersTabState();
}

class _InsurancePartnersTabState extends State<InsurancePartnersTab> {
  bool _loading = true;
  List<Map<String, dynamic>> _shops = [];
  List<Map<String, dynamic>> _garages = [];
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await InsuranceService.getPartners();
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
        _shops = (data['shop_partners'] as List? ?? [])
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
        _garages = (data['garage_partners'] as List? ?? [])
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
      });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load'; });
    }
  }

  Future<void> _remove(int partnerRecordId) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Remove Partner'),
        content: const Text('Are you sure you want to remove this partner?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: EteraTheme.error),
            child: const Text('Remove'),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    final res = await InsuranceService.removePartner(partnerRecordId);
    if (!mounted) return;
    if (res['success'] == true) {
      _load();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(res['message']?.toString() ?? 'Failed to remove'),
        backgroundColor: EteraTheme.error,
      ));
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: _load,
      child: _loading
          ? const Center(child: CircularProgressIndicator(color: EteraTheme.green))
          : _error != null
              ? Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                  Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
                  const SizedBox(height: 12),
                  ElevatedButton(onPressed: _load, child: const Text('Retry')),
                ]))
              : _shops.isEmpty && _garages.isEmpty
                  ? ListView(physics: const AlwaysScrollableScrollPhysics(), children: [
                      const SizedBox(height: 120),
                      Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                        Icon(Icons.people_outline, size: 64,
                            color: EteraTheme.green.withValues(alpha: 0.3)),
                        const SizedBox(height: 16),
                        const Text('No partners yet',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                        const SizedBox(height: 8),
                        const Text('Partners are added from the web dashboard.',
                            style: TextStyle(color: EteraTheme.textMuted),
                            textAlign: TextAlign.center),
                      ])),
                    ])
                  : ListView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 32),
                      children: [
                        if (_garages.isNotEmpty) ...[
                          _SectionHeader(title: 'Garage Partners (${_garages.length})'),
                          ..._garages.map((p) => _PartnerCard(
                              partner: p,
                              icon: Icons.car_repair,
                              color: EteraTheme.teal,
                              onRemove: () => _remove((p['partner_record_id'] as num).toInt()))),
                          const SizedBox(height: 8),
                        ],
                        if (_shops.isNotEmpty) ...[
                          _SectionHeader(title: 'Shop Partners (${_shops.length})'),
                          ..._shops.map((p) => _PartnerCard(
                              partner: p,
                              icon: Icons.storefront_outlined,
                              color: Colors.blue,
                              onRemove: () => _remove((p['partner_record_id'] as num).toInt()))),
                        ],
                      ],
                    ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  final String title;
  const _SectionHeader({required this.title});

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 8),
    child: Text(title, style: Theme.of(context).textTheme.titleSmall),
  );
}

class _PartnerCard extends StatelessWidget {
  final Map<String, dynamic> partner;
  final IconData icon;
  final Color color;
  final VoidCallback onRemove;
  const _PartnerCard({required this.partner, required this.icon, required this.color, required this.onRemove});

  @override
  Widget build(BuildContext context) {
    final user = partner['user'] as Map? ?? {};
    final name = user['name']?.toString() ?? '—';
    final phone = user['phone_number']?.toString() ?? '';
    final storeId = user['store_id']?.toString() ?? '';

    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: EteraCard(
        child: Row(children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, size: 20, color: color),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
            if (storeId.isNotEmpty)
              Text(storeId, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
            if (phone.isNotEmpty)
              Text(phone, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ])),
          IconButton(
            icon: const Icon(Icons.remove_circle_outline, color: EteraTheme.error, size: 20),
            onPressed: onRemove,
            tooltip: 'Remove',
          ),
        ]),
      ),
    );
  }
}
