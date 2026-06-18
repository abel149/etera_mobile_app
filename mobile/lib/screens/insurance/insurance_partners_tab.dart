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
  List<Map<String, dynamic>> _availableShops = [];
  List<Map<String, dynamic>> _availableGarages = [];
  bool _addingPartner = false;

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

  Future<void> _loadAvailablePartners() async {
    final res = await InsuranceService.getAvailablePartners();
    if (!mounted) return;
    if (res['success'] == true && res['data'] is Map) {
      final data = res['data'] as Map;
      setState(() {
        _availableShops = (data['shops'] as List? ?? [])
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
        _availableGarages = (data['garages'] as List? ?? [])
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
      });
    }
  }

  Future<void> _addPartners(List<int> partnerIds) async {
    setState(() => _addingPartner = true);
    final res = await InsuranceService.addPartners(partnerIds);
    if (!mounted) return;
    setState(() => _addingPartner = false);
    if (res['success'] == true) {
      _load();
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Partner(s) added successfully'),
        backgroundColor: EteraTheme.green,
        behavior: SnackBarBehavior.floating,
      ));
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(res['message']?.toString() ?? 'Failed to add partners'),
        backgroundColor: EteraTheme.error,
        behavior: SnackBarBehavior.floating,
      ));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
      floatingActionButton: Padding(
        padding: const EdgeInsets.only(bottom: 80, right: 16),
        child: FloatingActionButton(
          onPressed: _openAddPartnerDialog,
          backgroundColor: EteraTheme.green,
          child: const Icon(Icons.add, color: Colors.white),
        ),
      ),
      body: RefreshIndicator(
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
                        const Text('Tap + to add partners from the network.',
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
      ),
    );
  }

  Future<void> _openAddPartnerDialog() async {
    await _loadAvailablePartners();
    if (!mounted) return;
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _AddPartnerForm(
        availableShops: _availableShops,
        availableGarages: _availableGarages,
        existingShopIds: _shops.map((p) => (p['user'] as Map)['id'] as int).toSet(),
        existingGarageIds: _garages.map((p) => (p['user'] as Map)['id'] as int).toSet(),
        onAdd: _addPartners,
        adding: _addingPartner,
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

// ─── Add Partner Form (bottom sheet) ─────────────────────────────────────────
class _AddPartnerForm extends StatefulWidget {
  final List<Map<String, dynamic>> availableShops;
  final List<Map<String, dynamic>> availableGarages;
  final Set<int> existingShopIds;
  final Set<int> existingGarageIds;
  final Function(List<int>) onAdd;
  final bool adding;
  const _AddPartnerForm({
    required this.availableShops,
    required this.availableGarages,
    required this.existingShopIds,
    required this.existingGarageIds,
    required this.onAdd,
    required this.adding,
  });

  @override
  State<_AddPartnerForm> createState() => _AddPartnerFormState();
}

class _AddPartnerFormState extends State<_AddPartnerForm> {
  final Set<int> _selectedShopIds = {};
  final Set<int> _selectedGarageIds = {};

  @override
  Widget build(BuildContext context) {
    final filteredShops = widget.availableShops
        .where((s) => !widget.existingShopIds.contains(s['id'] as int))
        .toList();
    final filteredGarages = widget.availableGarages
        .where((g) => !widget.existingGarageIds.contains(g['id'] as int))
        .toList();

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const SizedBox(height: 8),
          Center(
            child: Container(
              width: 40, height: 4,
              decoration: BoxDecoration(
                color: Colors.grey.shade300,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Add Partners',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Close'),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          const Divider(height: 1),
          SizedBox(
            height: 300,
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              children: [
                if (filteredShops.isEmpty && filteredGarages.isEmpty)
                  const Center(
                    child: Padding(
                      padding: EdgeInsets.all(32),
                      child: Text('No available partners to add.',
                          style: TextStyle(color: EteraTheme.textMuted)),
                    ),
                  ),
                if (filteredShops.isNotEmpty) ...[
                  const Text('Shops',
                      style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                  const SizedBox(height: 8),
                  ...filteredShops.map((s) => _PartnerCheckbox(
                        item: s,
                        isSelected: _selectedShopIds.contains(s['id'] as int),
                        onChanged: (v) {
                          setState(() {
                            if (v == true) {
                              _selectedShopIds.add(s['id'] as int);
                            } else {
                              _selectedShopIds.remove(s['id'] as int);
                            }
                          });
                        },
                      )),
                  const SizedBox(height: 16),
                ],
                if (filteredGarages.isNotEmpty) ...[
                  const Text('Garages',
                      style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                  const SizedBox(height: 8),
                  ...filteredGarages.map((g) => _PartnerCheckbox(
                        item: g,
                        isSelected: _selectedGarageIds.contains(g['id'] as int),
                        onChanged: (v) {
                          setState(() {
                            if (v == true) {
                              _selectedGarageIds.add(g['id'] as int);
                            } else {
                              _selectedGarageIds.remove(g['id'] as int);
                            }
                          });
                        },
                      )),
                ],
              ],
            ),
          ),
          const Divider(height: 1),
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
            child: SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: (_selectedShopIds.isEmpty && _selectedGarageIds.isEmpty) || widget.adding
                    ? null
                    : () {
                        final allIds = [..._selectedShopIds, ..._selectedGarageIds];
                        widget.onAdd(allIds);
                      },
                style: ElevatedButton.styleFrom(
                  backgroundColor: EteraTheme.green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: widget.adding
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation(Colors.white),
                        ),
                      )
                    : Text('Add ${_selectedShopIds.length + _selectedGarageIds.length} Partner(s)',
                        style: const TextStyle(fontWeight: FontWeight.w600)),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _PartnerCheckbox extends StatelessWidget {
  final Map<String, dynamic> item;
  final bool isSelected;
  final Function(bool?) onChanged;
  const _PartnerCheckbox({
    required this.item,
    required this.isSelected,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    final name = item['name']?.toString() ?? '';
    final phone = item['phone_number']?.toString() ?? '';
    final location = item['location']?.toString() ?? '';

    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Container(
        decoration: BoxDecoration(
          color: isSelected ? EteraTheme.green.withValues(alpha: 0.08) : EteraTheme.bgLight,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: isSelected ? EteraTheme.green : Colors.transparent,
            width: 1,
          ),
        ),
        child: CheckboxListTile(
          value: isSelected,
          onChanged: onChanged,
          title: Text(name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
          subtitle: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (phone.isNotEmpty) Text(phone, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
              if (location.isNotEmpty) Text(location, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
            ],
          ),
          activeColor: EteraTheme.green,
          controlAffinity: ListTileControlAffinity.leading,
          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
        ),
      ),
    );
  }
}
