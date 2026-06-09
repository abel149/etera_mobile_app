import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/insurance_service.dart';
import '../../widgets/etera_card.dart';

class InsuranceDashboardTab extends StatefulWidget {
  final VoidCallback? onGoToProformas;
  final ValueNotifier<int>? refreshTrigger;

  const InsuranceDashboardTab({super.key, this.onGoToProformas, this.refreshTrigger});

  @override
  State<InsuranceDashboardTab> createState() => _InsuranceDashboardTabState();
}

class _InsuranceDashboardTabState extends State<InsuranceDashboardTab> {
  bool _loading = true;
  Map<String, dynamic>? _data;
  String? _error;

  @override
  void initState() {
    super.initState();
    widget.refreshTrigger?.addListener(_load);
    _load();
  }

  @override
  void dispose() {
    widget.refreshTrigger?.removeListener(_load);
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await InsuranceService.getDashboard();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true && res['data'] is Map) {
      setState(() { _loading = false; _data = Map<String, dynamic>.from(res['data'] as Map); });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load'; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
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
              : SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.fromLTRB(16, 20, 16, 32),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text('Welcome, ${user?.name ?? ''}',
                        style: Theme.of(context).textTheme.titleLarge),
                    const SizedBox(height: 4),
                    Text('Insurance Dashboard',
                        style: const TextStyle(color: EteraTheme.textMuted, fontSize: 13)),
                    const SizedBox(height: 20),

                    Row(children: [
                      _StatCard(
                        label: 'Total Files',
                        value: '${_data?['total_files'] ?? 0}',
                        icon: Icons.folder_outlined,
                        color: EteraTheme.green,
                        onTap: widget.onGoToProformas,
                      ),
                      const SizedBox(width: 12),
                      _StatCard(
                        label: 'Pending',
                        value: '${_data?['pending'] ?? 0}',
                        icon: Icons.hourglass_empty_outlined,
                        color: Colors.orange,
                        onTap: widget.onGoToProformas,
                      ),
                    ]),
                    const SizedBox(height: 12),
                    Row(children: [
                      _StatCard(
                        label: 'Completed',
                        value: '${_data?['completed'] ?? 0}',
                        icon: Icons.check_circle_outline,
                        color: EteraTheme.teal,
                        onTap: widget.onGoToProformas,
                      ),
                      const Spacer(),
                    ]),
                    const SizedBox(height: 20),

                    // Recent proformas
                    if ((_data?['proformas'] as List?)?.isNotEmpty == true) ...[
                      Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                        Text('Recent Files', style: Theme.of(context).textTheme.titleMedium),
                        TextButton(
                          onPressed: widget.onGoToProformas,
                          child: const Text('See All'),
                        ),
                      ]),
                      const SizedBox(height: 8),
                      ...(_data!['proformas'] as List).take(5).map((p) {
                        final pf = p as Map;
                        final brand = (pf['brand'] as Map?)?['name']?.toString() ?? '';
                        final model = pf['model']?.toString() ?? '';
                        final year = pf['year']?.toString() ?? '';
                        final status = pf['status']?.toString() ?? '';
                        final sColor = status == 'completed'
                            ? EteraTheme.green
                            : status == 'closed'
                                ? EteraTheme.teal
                                : Colors.orange;
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: EteraCard(
                            child: Row(children: [
                              Container(
                                width: 38, height: 38,
                                decoration: BoxDecoration(
                                  color: EteraTheme.green.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Icon(Icons.shield_outlined, size: 18, color: EteraTheme.green),
                              ),
                              const SizedBox(width: 12),
                              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                                Text('$brand $model $year',
                                    style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                                Text(pf['customer_name']?.toString() ?? '',
                                    style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                              ])),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: sColor.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(status,
                                    style: TextStyle(fontSize: 11, color: sColor, fontWeight: FontWeight.w600)),
                              ),
                            ]),
                          ),
                        );
                      }),
                    ],
                  ]),
                ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String label, value;
  final IconData icon;
  final Color color;
  final VoidCallback? onTap;
  const _StatCard({required this.label, required this.value, required this.icon, required this.color, this.onTap});

  @override
  Widget build(BuildContext context) => Expanded(
    child: GestureDetector(
      onTap: onTap,
      child: EteraCard(
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, size: 18, color: color),
          ),
          const SizedBox(height: 10),
          Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: color)),
          Text(label, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
        ]),
      ),
    ),
  );
}
