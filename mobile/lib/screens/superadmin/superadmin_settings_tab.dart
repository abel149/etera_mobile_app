import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/superadmin_service.dart';
import '../../widgets/etera_card.dart';
import '../../widgets/etera_text_field.dart';
import '../../widgets/etera_button.dart';

class SuperadminSettingsTab extends StatefulWidget {
  const SuperadminSettingsTab({super.key});

  @override
  State<SuperadminSettingsTab> createState() => _SuperadminSettingsTabState();
}

class _SuperadminSettingsTabState extends State<SuperadminSettingsTab> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic>? _currentCost;
  Map<String, dynamic>? _commission;
  List<Map<String, dynamic>> _emailSettings = [];

  // Cost controllers
  late final TextEditingController _cost1Ctrl;
  late final TextEditingController _cost2Ctrl;
  late final TextEditingController _cost3Ctrl;
  late final TextEditingController _cost4Ctrl;
  late final TextEditingController _costCheretaCtrl;
  late final TextEditingController _costInsuranceCtrl;

  // Commission controllers
  late final TextEditingController _commShopCtrl;
  late final TextEditingController _commGarageCtrl;
  late final TextEditingController _commInsuranceCtrl;
  late final TextEditingController _commOthersCtrl;

  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _cost1Ctrl = TextEditingController();
    _cost2Ctrl = TextEditingController();
    _cost3Ctrl = TextEditingController();
    _cost4Ctrl = TextEditingController();
    _costCheretaCtrl = TextEditingController();
    _costInsuranceCtrl = TextEditingController();
    _commShopCtrl = TextEditingController();
    _commGarageCtrl = TextEditingController();
    _commInsuranceCtrl = TextEditingController();
    _commOthersCtrl = TextEditingController();
    _load();
  }

  @override
  void dispose() {
    _cost1Ctrl.dispose();
    _cost2Ctrl.dispose();
    _cost3Ctrl.dispose();
    _cost4Ctrl.dispose();
    _costCheretaCtrl.dispose();
    _costInsuranceCtrl.dispose();
    _commShopCtrl.dispose();
    _commGarageCtrl.dispose();
    _commInsuranceCtrl.dispose();
    _commOthersCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await SuperadminService.getSettings();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final currentCost = res['currentCost'] as Map<String, dynamic>?;
      final commission = res['commission'] as Map<String, dynamic>?;
      final emailSettings = (res['emailSettings'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();

      setState(() {
        _loading = false;
        _currentCost = currentCost;
        _commission = commission;
        _emailSettings = emailSettings;

        if (currentCost != null) {
          _cost1Ctrl.text = (currentCost['1_proforma_cost'] ?? 0).toString();
          _cost2Ctrl.text = (currentCost['2_proforma_cost'] ?? 0).toString();
          _cost3Ctrl.text = (currentCost['3_proforma_cost'] ?? 0).toString();
          _cost4Ctrl.text = (currentCost['4_proforma_cost'] ?? 0).toString();
          _costCheretaCtrl.text = (currentCost['etera_chereta_cost'] ?? 0).toString();
          _costInsuranceCtrl.text = (currentCost['insurance_proforma'] ?? 0).toString();
        }
        if (commission != null) {
          _commShopCtrl.text = (commission['shopPay'] ?? 0).toString();
          _commGarageCtrl.text = (commission['garagePay'] ?? 0).toString();
          _commInsuranceCtrl.text = (commission['insurancePay'] ?? 0).toString();
          _commOthersCtrl.text = (commission['othersPay'] ?? 0).toString();
        }
      });
    } else {
      setState(() {
        _loading = false;
        _error   = res['message']?.toString() ?? 'Failed to load settings';
      });
    }
  }

  Future<void> _saveCost() async {
    setState(() => _saving = true);
    final res = await SuperadminService.storeCost({
      '1_proforma_cost': double.tryParse(_cost1Ctrl.text) ?? 0,
      '2_proforma_cost': double.tryParse(_cost2Ctrl.text) ?? 0,
      '3_proforma_cost': double.tryParse(_cost3Ctrl.text) ?? 0,
      '4_proforma_cost': double.tryParse(_cost4Ctrl.text) ?? 0,
      'etera_chereta_cost': double.tryParse(_costCheretaCtrl.text) ?? 0,
      'insurance_proforma': double.tryParse(_costInsuranceCtrl.text) ?? 0,
    });
    if (!mounted) return;
    setState(() => _saving = false);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res['message']?.toString() ?? 'Done'),
      backgroundColor: res['success'] == true ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
    if (res['success'] == true) _load();
  }

  Future<void> _saveCommission() async {
    setState(() => _saving = true);
    final res = await SuperadminService.storeCommission({
      'shopPay': double.tryParse(_commShopCtrl.text) ?? 0,
      'garagePay': double.tryParse(_commGarageCtrl.text) ?? 0,
      'insurancePay': double.tryParse(_commInsuranceCtrl.text) ?? 0,
      'othersPay': double.tryParse(_commOthersCtrl.text) ?? 0,
    });
    if (!mounted) return;
    setState(() => _saving = false);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res['message']?.toString() ?? 'Done'),
      backgroundColor: res['success'] == true ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
    if (res['success'] == true) _load();
  }

  Future<void> _toggleEmail(String key, String description) async {
    final res = await SuperadminService.toggleEmail(key);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res['message']?.toString() ?? 'Done'),
      backgroundColor: res['success'] == true ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
    if (res['success'] == true) _load();
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      color: Colors.blueGrey,
      onRefresh: _load,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
              child: const Text('Settings',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 8)),
          if (_loading)
            const SliverToBoxAdapter(
                child: Center(child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(color: Colors.blueGrey),
                )))
          else if (_error != null)
            SliverFillRemaining(child: Center(child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.wifi_off, size: 48, color: EteraTheme.textMuted),
                const SizedBox(height: 12),
                Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
                const SizedBox(height: 16),
                ElevatedButton(onPressed: _load, child: const Text('Retry')),
              ],
            )))
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
              sliver: SliverList(
                delegate: SliverChildListDelegate([
                  // ── Cost Settings ───────────────────────────────────────────
                  EteraCard(
                    margin: const EdgeInsets.only(bottom: 12),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Row(children: [
                        const Icon(Icons.attach_money, color: Colors.blueGrey),
                        const SizedBox(width: 8),
                        const Text('Proforma Costs',
                            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
                        const Spacer(),
                        if (_currentCost != null)
                          Text('Updated: ${_currentCost!['created_at']?.toString().split('T')[0] ?? ''}',
                              style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
                      ]),
                      const SizedBox(height: 16),
                      _CostField(label: '1 Proforma Cost', controller: _cost1Ctrl),
                      const SizedBox(height: 12),
                      _CostField(label: '2 Proforma Cost', controller: _cost2Ctrl),
                      const SizedBox(height: 12),
                      _CostField(label: '3 Proforma Cost', controller: _cost3Ctrl),
                      const SizedBox(height: 12),
                      _CostField(label: '4 Proforma Cost', controller: _cost4Ctrl),
                      const SizedBox(height: 12),
                      _CostField(label: 'Etera Chereta Cost', controller: _costCheretaCtrl),
                      const SizedBox(height: 12),
                      _CostField(label: 'Insurance Proforma', controller: _costInsuranceCtrl),
                      const SizedBox(height: 16),
                      EteraButton(
                        label: 'Save Costs',
                        loading: _saving,
                        onPressed: _saveCost,
                      ),
                    ]),
                  ),
                  // ── Commission Settings ─────────────────────────────────────
                  EteraCard(
                    margin: const EdgeInsets.only(bottom: 12),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      const Row(children: [
                        Icon(Icons.percent, color: Colors.blueGrey),
                        SizedBox(width: 8),
                        Text('Commission Rates',
                            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
                      ]),
                      const SizedBox(height: 16),
                      _CostField(label: 'Shop Pay', controller: _commShopCtrl),
                      const SizedBox(height: 12),
                      _CostField(label: 'Garage Pay', controller: _commGarageCtrl),
                      const SizedBox(height: 12),
                      _CostField(label: 'Insurance Pay', controller: _commInsuranceCtrl),
                      const SizedBox(height: 12),
                      _CostField(label: 'Others Pay', controller: _commOthersCtrl),
                      const SizedBox(height: 16),
                      EteraButton(
                        label: 'Save Commissions',
                        loading: _saving,
                        onPressed: _saveCommission,
                      ),
                    ]),
                  ),
                  // ── Email Settings ────────────────────────────────────────────
                  EteraCard(
                    margin: const EdgeInsets.only(bottom: 12),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      const Row(children: [
                        Icon(Icons.email, color: Colors.blueGrey),
                        SizedBox(width: 8),
                        Text('Email Notifications',
                            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
                      ]),
                      const SizedBox(height: 16),
                      ..._emailSettings.map((e) {
                        final key = e['key']?.toString() ?? '';
                        final description = e['description']?.toString() ?? key;
                        final enabled = e['enabled'] == true;
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: SwitchListTile(
                            title: Text(description, style: const TextStyle(fontSize: 13)),
                            value: enabled,
                            onChanged: (_) => _toggleEmail(key, description),
                            activeColor: EteraTheme.teal,
                            contentPadding: EdgeInsets.zero,
                          ),
                        );
                      }),
                    ]),
                  ),
                ]),
              ),
            ),
        ],
      ),
    );
  }
}

class _CostField extends StatelessWidget {
  final String label;
  final TextEditingController controller;
  const _CostField({required this.label, required this.controller});

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      keyboardType: TextInputType.number,
      decoration: InputDecoration(
        labelText: label,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
      ),
    );
  }
}
