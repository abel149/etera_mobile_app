import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/others_service.dart';
import '../../widgets/etera_card.dart';

class OthersDashboardTab extends StatefulWidget {
  const OthersDashboardTab({super.key});

  @override
  State<OthersDashboardTab> createState() => _OthersDashboardTabState();
}

class _OthersDashboardTabState extends State<OthersDashboardTab> {
  bool _loading = true;
  Map<String, dynamic>? _data;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await OthersService.getDashboard();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      _handleUnauthorized();
      return;
    }
    setState(() {
      _loading = false;
      if (res['success'] == true) {
        // dashboard() returns: {success, total_proformas, data:[...proforma list...]}
        // data is a direct List (not paginated)
        final rawList = res['data'];
        final List dataList = rawList is List ? rawList : [];
        _data = {
          'total_proformas': res['total_proformas'] ?? dataList.length,
          'active_proformas': dataList.where((p) {
            final s = ((p as Map)['status'] ?? '').toString().toLowerCase();
            return ['active', 'open', 'floating', 'published'].contains(s);
          }).length,
          'closed_proformas': dataList.where((p) {
            final s = ((p as Map)['status'] ?? '').toString().toLowerCase();
            return ['closed', 'completed'].contains(s);
          }).length,
        };
      } else {
        _error = res['message'] ?? 'Failed to load dashboard';
      }
    });
  }

  void _handleUnauthorized() {
    context.read<AuthProvider>().logout();
    Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Welcome header
            Text(
              'Welcome, ${user?.name ?? 'User'}',
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            const SizedBox(height: 4),
            Text(
              user?.roleLabel ?? '',
              style: const TextStyle(fontSize: 14, color: EteraTheme.textMuted),
            ),
            const SizedBox(height: 24),

            if (_loading)
              const _StatsShimmer()
            else if (_error != null)
              _ErrorBanner(message: _error!, onRetry: _load)
            else ...[
              // Stats row
              Row(
                children: [
                  Expanded(
                    child: _StatCard(
                      title: 'Total Files',
                      value: '${_data?['total_files'] ?? _data?['total_proformas'] ?? 0}',
                      icon: Icons.folder_outlined,
                      color: EteraTheme.green,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _StatCard(
                      title: 'Active',
                      value: '${_data?['active_files'] ?? _data?['active_proformas'] ?? 0}',
                      icon: Icons.pending_actions,
                      color: EteraTheme.teal,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _StatCard(
                      title: 'Closed',
                      value: '${_data?['closed_files'] ?? _data?['closed_proformas'] ?? 0}',
                      icon: Icons.check_circle_outline,
                      color: Colors.grey,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _StatCard(
                      title: 'Balance',
                      value: '${((_data?['balance'] ?? user?.balance ?? 0) as num).toStringAsFixed(0)} Br',
                      icon: Icons.account_balance_wallet_outlined,
                      color: const Color(0xFF6C63FF),
                    ),
                  ),
                ],
              ),
            ],

            const SizedBox(height: 28),

            // Quick actions
            Text('Quick Actions', style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 12),

            EteraCard(
              child: ListTile(
                contentPadding: EdgeInsets.zero,
                leading: Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    gradient: EteraTheme.primaryGradient,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.add_circle_outline, color: Colors.white),
                ),
                title: const Text('Request Proforma', style: TextStyle(fontWeight: FontWeight.w600)),
                subtitle: const Text('Submit a new spare parts request', style: TextStyle(fontSize: 12)),
                trailing: const Icon(Icons.arrow_forward_ios, size: 16, color: EteraTheme.textMuted),
                onTap: () => Navigator.pushNamed(context, '/create-proforma'),
              ),
            ),

            EteraCard(
              child: ListTile(
                contentPadding: EdgeInsets.zero,
                leading: Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: EteraTheme.teal.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.receipt_long, color: EteraTheme.teal),
                ),
                title: const Text('My Proformas', style: TextStyle(fontWeight: FontWeight.w600)),
                subtitle: const Text('Track your submitted requests', style: TextStyle(fontSize: 12)),
                trailing: const Icon(Icons.arrow_forward_ios, size: 16, color: EteraTheme.textMuted),
                onTap: () {},
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Stat card ────────────────────────────────────────────────────
class _StatCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;

  const _StatCard({required this.title, required this.value, required this.icon, required this.color});

  @override
  Widget build(BuildContext context) {
    return EteraCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 22),
          const SizedBox(height: 12),
          Text(title, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: EteraTheme.textPrimary),
          ),
        ],
      ),
    );
  }
}

// ─── Loading shimmer ─────────────────────────────────────────────
class _StatsShimmer extends StatelessWidget {
  const _StatsShimmer();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Row(children: [
          Expanded(child: _shimmerBox(height: 90)),
          const SizedBox(width: 12),
          Expanded(child: _shimmerBox(height: 90)),
        ]),
        const SizedBox(height: 12),
        Row(children: [
          Expanded(child: _shimmerBox(height: 90)),
          const SizedBox(width: 12),
          Expanded(child: _shimmerBox(height: 90)),
        ]),
      ],
    );
  }

  Widget _shimmerBox({required double height}) {
    return Container(
      height: height,
      decoration: BoxDecoration(
        color: const Color(0xFFEEEEEE),
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
      ),
    );
  }
}

// ─── Error banner ────────────────────────────────────────────────
class _ErrorBanner extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;

  const _ErrorBanner({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: EteraTheme.error.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
        border: Border.all(color: EteraTheme.error.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          const Icon(Icons.wifi_off, color: EteraTheme.error, size: 20),
          const SizedBox(width: 12),
          Expanded(child: Text(message, style: const TextStyle(fontSize: 13))),
          TextButton(
            onPressed: onRetry,
            child: const Text('Retry', style: TextStyle(color: EteraTheme.green)),
          ),
        ],
      ),
    );
  }
}
