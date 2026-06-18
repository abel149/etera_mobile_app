import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../config/api_config.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/insurance_service.dart';
import '../../widgets/etera_card.dart';
import 'insurance_proforma_detail_screen.dart';
import '../shared/received_proformas_list.dart';

class InsuranceProformasTab extends StatefulWidget {
  final ValueNotifier<int>? refreshTrigger;
  const InsuranceProformasTab({super.key, this.refreshTrigger});

  @override
  State<InsuranceProformasTab> createState() => _InsuranceProformasTabState();
}

class _InsuranceProformasTabState extends State<InsuranceProformasTab>
    with SingleTickerProviderStateMixin {
  bool _loading = true;
  List<Map<String, dynamic>> _items = [];
  String? _error;
  String _filter = 'all';
  late final TabController _tabCtrl;

  final _filters = ['all', 'pending', 'published', 'closed', 'completed'];

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
    widget.refreshTrigger?.addListener(_load);
    _load();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    widget.refreshTrigger?.removeListener(_load);
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await InsuranceService.getProformas(
        status: _filter == 'all' ? null : _filter);
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = (res['data'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      setState(() { _loading = false; _items = raw; });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load'; });
    }
  }

  Widget _buildMyFilesTab() {
    return Column(children: [
      // Filter chips
      SizedBox(
        height: 44,
        child: ListView.separated(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          scrollDirection: Axis.horizontal,
          itemCount: _filters.length,
          separatorBuilder: (_, __) => const SizedBox(width: 8),
          itemBuilder: (_, i) {
            final f = _filters[i];
            final selected = _filter == f;
            return ChoiceChip(
              label: Text(f[0].toUpperCase() + f.substring(1)),
              selected: selected,
              onSelected: (_) {
                setState(() => _filter = f);
                _load();
              },
              selectedColor: EteraTheme.green,
              labelStyle: TextStyle(
                  color: selected ? Colors.white : EteraTheme.textMuted,
                  fontSize: 12),
            );
          },
        ),
      ),
      Expanded(
        child: RefreshIndicator(
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
                  : _items.isEmpty
                      ? ListView(physics: const AlwaysScrollableScrollPhysics(), children: [
                          const SizedBox(height: 100),
                          Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                            Icon(Icons.shield_outlined, size: 64,
                                color: EteraTheme.green.withValues(alpha: 0.3)),
                            const SizedBox(height: 16),
                            const Text('No files yet',
                                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                            const SizedBox(height: 8),
                            const Text('Tap + to create a new insurance file.',
                                style: TextStyle(color: EteraTheme.textMuted)),
                          ])),
                        ])
                      : ListView.builder(
                          physics: const AlwaysScrollableScrollPhysics(),
                          padding: const EdgeInsets.fromLTRB(16, 8, 16, 32),
                          itemCount: _items.length,
                          itemBuilder: (_, i) => _ProformaCard(
                            item: _items[i],
                            onTap: () async {
                              final id = (_items[i]['id'] as num?)?.toInt();
                              if (id == null) return;
                              await Navigator.push(context,
                                  MaterialPageRoute(builder: (_) =>
                                      InsuranceProformaDetailScreen(proformaId: id)));
                              _load();
                            },
                          ),
                        ),
        ),
      ),
    ]);
  }

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      Material(
        color: Theme.of(context).scaffoldBackgroundColor,
        child: TabBar(
          controller: _tabCtrl,
          labelColor: EteraTheme.green,
          unselectedLabelColor: EteraTheme.textMuted,
          indicatorColor: EteraTheme.green,
          tabs: const [Tab(text: 'My Files'), Tab(text: 'Received')],
        ),
      ),
      Expanded(
        child: TabBarView(
          controller: _tabCtrl,
          children: [
            _buildMyFilesTab(),
            ReceivedProformasList(
              listUrl: ApiConfig.insuranceReceivedProformas,
              detailUrl: '${ApiConfig.baseUrl}/insurance/proformas',
            ),
          ],
        ),
      ),
    ]);
  }
}

class _ProformaCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback onTap;
  const _ProformaCard({required this.item, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final brand = item['brand']?.toString() ?? '—';
    final model = item['model']?.toString() ?? '';
    final year = item['year']?.toString() ?? '';
    final fileNum = item['file_number']?.toString() ?? '';
    final customer = item['customer_name']?.toString() ?? '—';
    final status = item['status']?.toString() ?? '';
    final dateStr = item['created_at']?.toString() ?? '';
    DateTime? dt;
    try { dt = DateTime.parse(dateStr).toLocal(); } catch (_) {}

    final sColor = status == 'completed'
        ? EteraTheme.green
        : status == 'closed'
            ? EteraTheme.teal
            : Colors.orange;

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: GestureDetector(
        onTap: onTap,
        child: EteraCard(
          child: Row(children: [
            Container(
              width: 42, height: 42,
              decoration: BoxDecoration(
                color: EteraTheme.green.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.shield_outlined, size: 20, color: EteraTheme.green),
            ),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('$brand $model $year',
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
              Text(customer, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
              if (dt != null)
                Text(DateFormat('MMM d, y').format(dt),
                    style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
            ])),
            Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: sColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(status,
                    style: TextStyle(fontSize: 11, color: sColor, fontWeight: FontWeight.w600)),
              ),
              const SizedBox(height: 4),
              Text('#$fileNum', style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
            ]),
          ]),
        ),
      ),
    );
  }
}
