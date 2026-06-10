import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/shop_service.dart';
import '../../widgets/etera_card.dart';

class ShopDashboardTab extends StatefulWidget {
  final VoidCallback? onGoToInbox;
  final VoidCallback? onGoToApplications;
  final ValueNotifier<int>? refreshTrigger;

  const ShopDashboardTab({
    super.key,
    this.onGoToInbox,
    this.onGoToApplications,
    this.refreshTrigger,
  });

  @override
  State<ShopDashboardTab> createState() => _ShopDashboardTabState();
}

class _ShopDashboardTabState extends State<ShopDashboardTab> {
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
    final res = await ShopService.getDashboard();
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
              ? _ErrView(msg: _error!, onRetry: _load)
              : SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.fromLTRB(16, 20, 16, 32),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text('Welcome, ${user?.name ?? ''}',
                        style: Theme.of(context).textTheme.titleLarge),
                    const SizedBox(height: 4),
                    Text(user?.storeId ?? user?.phoneNumber ?? '',
                        style: const TextStyle(color: EteraTheme.textMuted, fontSize: 13)),
                    const SizedBox(height: 20),

                    // Stat cards
                    Row(children: [
                      _StatCard(
                        label: 'Inbox',
                        value: '${_data?['inbox_count'] ?? 0}',
                        icon: Icons.inbox_outlined,
                        color: Colors.blue,
                        onTap: widget.onGoToInbox,
                      ),
                      const SizedBox(width: 12),
                      _StatCard(
                        label: 'Total Bids',
                        value: '${_data?['total'] ?? 0}',
                        icon: Icons.description_outlined,
                        color: EteraTheme.green,
                        onTap: widget.onGoToApplications,
                      ),
                    ]),
                    const SizedBox(height: 12),
                    Row(children: [
                      _StatCard(
                        label: 'Won',
                        value: '${_data?['completed'] ?? 0}',
                        icon: Icons.check_circle_outline,
                        color: EteraTheme.teal,
                        onTap: widget.onGoToApplications,
                      ),
                      const SizedBox(width: 12),
                      _StatCard(
                        label: 'Pending',
                        value: '${_data?['pending'] ?? 0}',
                        icon: Icons.hourglass_empty_outlined,
                        color: Colors.orange,
                        onTap: widget.onGoToApplications,
                      ),
                    ]),
                    const SizedBox(height: 20),

                    // Balance card
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: EteraTheme.primaryGradient,
                        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
                      ),
                      child: Row(children: [
                        const Icon(Icons.account_balance_wallet, color: Colors.white70, size: 28),
                        const SizedBox(width: 16),
                        Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          const Text('Wallet Balance',
                              style: TextStyle(color: Colors.white70, fontSize: 12)),
                          Text(
                            '${((_data?['balance'] ?? 0) as num).toStringAsFixed(2)} Br',
                            style: const TextStyle(
                                color: Colors.white, fontSize: 22, fontWeight: FontWeight.w800),
                          ),
                        ]),
                      ]),
                    ),
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
  Widget build(BuildContext context) {
    return Expanded(
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
            Text(value,
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: color)),
            Text(label, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ]),
        ),
      ),
    );
  }
}

class _ErrView extends StatelessWidget {
  final String msg;
  final VoidCallback onRetry;
  const _ErrView({required this.msg, required this.onRetry});

  @override
  Widget build(BuildContext context) => Center(
    child: Column(mainAxisSize: MainAxisSize.min, children: [
      Text(msg, style: const TextStyle(color: EteraTheme.textMuted), textAlign: TextAlign.center),
      const SizedBox(height: 12),
      ElevatedButton(onPressed: onRetry, child: const Text('Retry')),
    ]),
  );
}
