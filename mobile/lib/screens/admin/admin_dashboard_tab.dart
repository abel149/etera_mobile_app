import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/admin_service.dart';
import '../../widgets/etera_card.dart';

class AdminDashboardTab extends StatefulWidget {
  final VoidCallback? onGoToProformas;
  final VoidCallback? onGoToApprovals;
  final VoidCallback? onGoToEmployees;
  final ValueNotifier<int>? refreshTrigger;

  const AdminDashboardTab({
    super.key,
    this.onGoToProformas,
    this.onGoToApprovals,
    this.onGoToEmployees,
    this.refreshTrigger,
  });

  @override
  State<AdminDashboardTab> createState() => _AdminDashboardTabState();
}

class _AdminDashboardTabState extends State<AdminDashboardTab> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic> _data = {};

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
    final res = await AdminService.getDashboard();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      setState(() { _loading = false; _data = Map<String, dynamic>.from(res['data'] ?? {}); });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load'; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final isSuperAdmin = _data['is_superadmin'] == true || user?.role == 'superadmin';

    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 20, 16, 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    gradient: EteraTheme.primaryGradient,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(Icons.admin_panel_settings, color: Colors.white, size: 26),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Welcome, ${user?.name ?? 'Admin'}',
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
                      ),
                      Text(
                        isSuperAdmin ? 'Superadmin' : 'Admin',
                        style: const TextStyle(fontSize: 13, color: EteraTheme.textMuted),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            if (_loading)
              const Center(child: CircularProgressIndicator(color: EteraTheme.green))
            else if (_error != null)
              _ErrorRetry(error: _error!, onRetry: _load)
            else ...[
              Text('Proforma Pipeline', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: _StatCard(
                    label: 'Pending',
                    value: '${_data['proforma_pending'] ?? 0}',
                    icon: Icons.pending_outlined,
                    color: Colors.orange,
                    onTap: widget.onGoToProformas,
                  )),
                  const SizedBox(width: 12),
                  Expanded(child: _StatCard(
                    label: 'Published',
                    value: '${_data['proforma_published'] ?? 0}',
                    icon: Icons.publish_outlined,
                    color: EteraTheme.green,
                    onTap: widget.onGoToProformas,
                  )),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: _StatCard(
                    label: 'Closed',
                    value: '${_data['proforma_closed'] ?? 0}',
                    icon: Icons.lock_outline,
                    color: EteraTheme.teal,
                    onTap: widget.onGoToProformas,
                  )),
                  const SizedBox(width: 12),
                  Expanded(child: _StatCard(
                    label: 'Completed',
                    value: '${_data['proforma_completed'] ?? 0}',
                    icon: Icons.check_circle_outline,
                    color: Colors.blue,
                    onTap: widget.onGoToProformas,
                  )),
                ],
              ),
              const SizedBox(height: 20),

              // ── Insurance & Others Stats ─────────────────────────
              Text('Proforma Statistics', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 12),
              EteraCard(
                child: Column(children: [
                  _StatRow(label: 'Insurance — Total',     value: '${_data['insurance_total'] ?? 0}',     color: Colors.indigo),
                  const Divider(height: 1),
                  _StatRow(label: 'Insurance — Completed', value: '${_data['insurance_completed'] ?? 0}', color: Colors.indigo),
                  const Divider(height: 1),
                  _StatRow(label: 'Others — Total',        value: '${_data['others_total'] ?? 0}',        color: Colors.purple),
                  const Divider(height: 1),
                  _StatRow(label: 'Others — Completed',    value: '${_data['others_completed'] ?? 0}',    color: Colors.purple),
                ]),
              ),
              const SizedBox(height: 20),

              // ── Superadmin user counts ───────────────────────────────
              if (isSuperAdmin) ...[  
                Text('User Counts', style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 12),
                EteraCard(
                  child: Column(children: [
                    _StatRow(label: 'Total Users',     value: '${_data['total_users'] ?? 0}',     color: EteraTheme.teal),
                    const Divider(height: 1),
                    _StatRow(label: 'Admins',          value: '${_data['admin_count'] ?? 0}',     color: Colors.deepPurple),
                    const Divider(height: 1),
                    _StatRow(label: 'Insurance',       value: '${_data['insurance_users'] ?? 0}', color: Colors.indigo),
                    const Divider(height: 1),
                    _StatRow(label: 'Garages',         value: '${_data['garage_users'] ?? 0}',    color: EteraTheme.green),
                    const Divider(height: 1),
                    _StatRow(label: 'Spare Part Shops',value: '${_data['shop_users'] ?? 0}',      color: Colors.amber.shade700),
                    const Divider(height: 1),
                    _StatRow(label: 'Customers',       value: '${_data['others_users'] ?? 0}',    color: Colors.orange),
                  ]),
                ),
                const SizedBox(height: 20),
              ],

              Text('Quick Actions', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 12),
              EteraCard(
                child: Column(
                  children: [
                    _ActionRow(
                      icon: Icons.receipt_long_outlined,
                      label: 'Manage Proformas',
                      badge: _data['proforma_pending'] as int? ?? 0,
                      color: EteraTheme.green,
                      onTap: widget.onGoToProformas,
                    ),
                    const Divider(height: 1),
                    _ActionRow(
                      icon: Icons.how_to_reg_outlined,
                      label: 'Pending Approvals',
                      badge: _data['pending_approvals'] as int? ?? 0,
                      color: Colors.orange,
                      onTap: widget.onGoToApprovals,
                    ),
                    if (isSuperAdmin) ...[  
                      const Divider(height: 1),
                      _ActionRow(
                        icon: Icons.manage_accounts_outlined,
                        label: 'Manage Admins',
                        badge: 0,
                        color: Colors.deepPurple,
                        onTap: widget.onGoToEmployees,
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

class _StatRow extends StatelessWidget {
  final String label;
  final String value;
  final Color color;

  const _StatRow({required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 10),
      child: Row(children: [
        Container(width: 4, height: 4, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 8),
        Expanded(child: Text(label, style: const TextStyle(fontSize: 13))),
        Text(value, style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: color)),
      ]),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  final VoidCallback? onTap;

  const _StatCard({
    required this.label,
    required this.value,
    required this.icon,
    required this.color,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
          border: Border.all(color: Colors.grey.shade200),
          boxShadow: [
            BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, size: 20, color: color),
            ),
            const SizedBox(height: 10),
            Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: color)),
            const SizedBox(height: 2),
            Text(label, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ],
        ),
      ),
    );
  }
}

class _ActionRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final int badge;
  final Color color;
  final VoidCallback? onTap;

  const _ActionRow({
    required this.icon,
    required this.label,
    required this.badge,
    required this.color,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 12),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, size: 20, color: color),
            ),
            const SizedBox(width: 12),
            Expanded(child: Text(label, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500))),
            if (badge > 0)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text('$badge', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: color)),
              ),
            const SizedBox(width: 8),
            const Icon(Icons.chevron_right, size: 18, color: EteraTheme.textMuted),
          ],
        ),
      ),
    );
  }
}

class _ErrorRetry extends StatelessWidget {
  final String error;
  final VoidCallback onRetry;

  const _ErrorRetry({required this.error, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.wifi_off, size: 48, color: EteraTheme.textMuted),
          const SizedBox(height: 12),
          Text(error, style: const TextStyle(color: EteraTheme.textMuted), textAlign: TextAlign.center),
          const SizedBox(height: 16),
          ElevatedButton(onPressed: onRetry, child: const Text('Retry')),
        ],
      ),
    );
  }
}
