import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/garage_service.dart';
import '../../widgets/etera_card.dart';

class GarageDashboardTab extends StatefulWidget {
  final VoidCallback? onGoToInbox;
  final VoidCallback? onGoToBids;
  final VoidCallback? onGoToFiles;
  final ValueNotifier<int>? refreshTrigger;
  final void Function(int)? onInboxCountLoaded;

  const GarageDashboardTab({
    super.key,
    this.onGoToInbox,
    this.onGoToBids,
    this.onGoToFiles,
    this.refreshTrigger,
    this.onInboxCountLoaded,
  });

  @override
  State<GarageDashboardTab> createState() => _GarageDashboardTabState();
}

class _GarageDashboardTabState extends State<GarageDashboardTab> {
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
    setState(() {
      _loading = true;
      _error = null;
    });
    final res = await GarageService.getDashboard();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true && res['data'] is Map) {
      final data = Map<String, dynamic>.from(res['data'] as Map);
      setState(() {
        _loading = false;
        _data = data;
      });
      widget.onInboxCountLoaded?.call((data['inbox_count'] ?? 0) as int);
    } else {
      setState(() {
        _loading = false;
        _error = res['message']?.toString() ?? 'Failed to load dashboard';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: _load,
      child: LayoutBuilder(
        builder: (context, constraints) {
          final isSmall = constraints.maxWidth < 380;
          final hPad = isSmall ? 14.0 : 20.0;
          return SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: EdgeInsets.symmetric(horizontal: hPad, vertical: 20),
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 600),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Welcome, ${user?.name ?? 'User'}',
                      style: isSmall
                          ? Theme.of(context).textTheme.headlineSmall
                          : Theme.of(context).textTheme.headlineMedium,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      user?.roleLabel ?? '',
                      style: const TextStyle(
                          fontSize: 14, color: EteraTheme.textMuted),
                    ),
                    const SizedBox(height: 24),

                    if (_loading)
                      const _StatsShimmer()
                    else if (_error != null)
                      _ErrorBanner(message: _error!, onRetry: _load)
                    else ...[
                      // Balance card
                      _BalanceCard(
                        balance: (_data!['balance'] ?? 0).toDouble(),
                      ),
                      const SizedBox(height: 12),

                      // Stats grid
                      Row(children: [
                        Expanded(
                          child: _StatCard(
                            title: 'Inbox',
                            value: '${_data!['inbox_count'] ?? 0}',
                            icon: Icons.inbox_outlined,
                            color: Colors.blue,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _StatCard(
                            title: 'Total Bids',
                            value: '${_data!['total'] ?? 0}',
                            icon: Icons.how_to_vote_outlined,
                            color: EteraTheme.green,
                          ),
                        ),
                      ]),
                      const SizedBox(height: 12),
                      Row(children: [
                        Expanded(
                          child: _StatCard(
                            title: 'Active',
                            value: '${_data!['pending_count'] ?? 0}',
                            icon: Icons.pending_outlined,
                            color: Colors.orange,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _StatCard(
                            title: 'Completed',
                            value: '${_data!['completed_count'] ?? 0}',
                            icon: Icons.check_circle_outline,
                            color: Colors.teal,
                          ),
                        ),
                      ]),
                    ],

                    const SizedBox(height: 28),
                    Text('Quick Actions',
                        style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 12),

                    // Inbox quick action
                    EteraCard(
                      child: ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: _QuickActionIcon(
                            icon: Icons.inbox, color: Colors.blue),
                        title: const Text('Inbox',
                            style: TextStyle(fontWeight: FontWeight.w600)),
                        subtitle: const Text('View proformas sent to you',
                            style: TextStyle(fontSize: 12)),
                        trailing: const Icon(Icons.arrow_forward_ios,
                            size: 16, color: EteraTheme.textMuted),
                        onTap: widget.onGoToInbox,
                      ),
                    ),
                    const SizedBox(height: 8),

                    // My Bids quick action
                    EteraCard(
                      child: ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: _QuickActionIcon(
                            icon: Icons.how_to_vote, color: EteraTheme.green),
                        title: const Text('My Bids',
                            style: TextStyle(fontWeight: FontWeight.w600)),
                        subtitle: const Text('Track your submitted quotes',
                            style: TextStyle(fontSize: 12)),
                        trailing: const Icon(Icons.arrow_forward_ios,
                            size: 16, color: EteraTheme.textMuted),
                        onTap: widget.onGoToBids,
                      ),
                    ),
                    const SizedBox(height: 8),

                    // My Files quick action
                    EteraCard(
                      child: ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: _QuickActionIcon(
                            icon: Icons.folder_outlined, color: Colors.purple),
                        title: const Text('My Files',
                            style: TextStyle(fontWeight: FontWeight.w600)),
                        subtitle: const Text('Proformas you created',
                            style: TextStyle(fontSize: 12)),
                        trailing: const Icon(Icons.arrow_forward_ios,
                            size: 16, color: EteraTheme.textMuted),
                        onTap: widget.onGoToFiles,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}

// ─── Balance card ─────────────────────────────────────────────────
class _BalanceCard extends StatelessWidget {
  final double balance;
  const _BalanceCard({required this.balance});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: EteraTheme.primaryGradient,
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Wallet Balance',
              style: TextStyle(color: Colors.white70, fontSize: 13)),
          const SizedBox(height: 8),
          FittedBox(
            fit: BoxFit.scaleDown,
            alignment: Alignment.centerLeft,
            child: Text(
              '${balance.toStringAsFixed(2)} Br',
              style: const TextStyle(
                color: Colors.white,
                fontSize: 28,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Quick action icon ────────────────────────────────────────────
class _QuickActionIcon extends StatelessWidget {
  final IconData icon;
  final Color color;
  const _QuickActionIcon({required this.icon, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 44,
      height: 44,
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Icon(icon, color: color),
    );
  }
}

// ─── Stat card ────────────────────────────────────────────────────
class _StatCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;

  const _StatCard(
      {required this.title,
      required this.value,
      required this.icon,
      required this.color});

  @override
  Widget build(BuildContext context) {
    return EteraCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 22),
          const SizedBox(height: 12),
          Text(title,
              style:
                  const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          const SizedBox(height: 4),
          FittedBox(
            fit: BoxFit.scaleDown,
            alignment: Alignment.centerLeft,
            child: Text(
              value,
              style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                  color: EteraTheme.textPrimary),
            ),
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
        _shimmerBox(height: 90),
        const SizedBox(height: 12),
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

// ─── Error banner ─────────────────────────────────────────────────
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
          Expanded(
              child: Text(message, style: const TextStyle(fontSize: 13))),
          TextButton(
            onPressed: onRetry,
            child: const Text('Retry',
                style: TextStyle(color: EteraTheme.green)),
          ),
        ],
      ),
    );
  }
}
