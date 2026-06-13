import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/superadmin_service.dart';
import '../../widgets/etera_card.dart';

class SuperadminUsersTab extends StatefulWidget {
  const SuperadminUsersTab({super.key});

  @override
  State<SuperadminUsersTab> createState() => _SuperadminUsersTabState();
}

class _SuperadminUsersTabState extends State<SuperadminUsersTab> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _users = [];
  int _page = 1;
  int _lastPage = 1;
  bool _loadingMore = false;
  String? _roleFilter;
  String? _statusFilter;

  // Stats banner
  Map<String, dynamic> _stats = {};

  final _roles      = [null, 'business_owner', 'garage', 'shop', 'others'];
  final _roleLabels = ['All', 'Business', 'Garage', 'Shop', 'Individual'];

  final ScrollController _scroll = ScrollController();

  @override
  void initState() {
    super.initState();
    _load();
    _scroll.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scroll.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 200 &&
        !_loadingMore && _page < _lastPage) {
      _loadMore();
    }
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; _page = 1; _users = []; });
    final res = await SuperadminService.getUsers(
        role: _roleFilter, status: _statusFilter, page: 1);
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
      final pg    = res['pagination'] as Map? ?? {};
      final stats = res['stats'] as Map? ?? {};
      setState(() {
        _loading   = false;
        _users     = raw;
        _lastPage  = (pg['last_page'] as int?) ?? 1;
        _stats     = Map<String, dynamic>.from(stats);
      });
    } else {
      setState(() {
        _loading = false;
        _error   = res['message']?.toString() ?? 'Failed to load users';
      });
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || _page >= _lastPage) return;
    setState(() => _loadingMore = true);
    final res = await SuperadminService.getUsers(
        role: _roleFilter, status: _statusFilter, page: _page + 1);
    if (!mounted) return;
    if (res['success'] == true) {
      final raw = (res['data'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      setState(() { _page++; _users.addAll(raw); _loadingMore = false; });
    } else {
      setState(() => _loadingMore = false);
    }
  }

  Future<void> _approve(Map<String, dynamic> user, int index) async {
    final res = await SuperadminService.approveUser(user['id'] as int);
    if (!mounted) return;
    _snack(res['message']?.toString() ?? (res['success'] == true ? 'Approved!' : 'Failed'),
        res['success'] == true);
    if (res['success'] == true) {
      setState(() => _users[index] = {..._users[index], 'approved': true});
    }
  }

  Future<void> _revoke(Map<String, dynamic> user, int index) async {
    final ok = await _confirm('Revoke approval for "${user['name']}"?');
    if (!ok) return;
    final res = await SuperadminService.revokeUser(user['id'] as int);
    if (!mounted) return;
    _snack(res['message']?.toString() ?? (res['success'] == true ? 'Revoked' : 'Failed'),
        res['success'] == true);
    if (res['success'] == true) {
      setState(() => _users[index] = {..._users[index], 'approved': false});
    }
  }

  Future<void> _delete(Map<String, dynamic> user, int index) async {
    final ok = await _confirm('Delete user "${user['name']}"?\nThis cannot be undone.');
    if (!ok) return;
    final res = await SuperadminService.deleteUser(user['id'] as int);
    if (!mounted) return;
    _snack(res['message']?.toString() ?? (res['success'] == true ? 'Deleted' : 'Failed'),
        res['success'] == true);
    if (res['success'] == true) setState(() => _users.removeAt(index));
  }

  Future<bool> _confirm(String msg) async =>
      await showDialog<bool>(
        context: context,
        builder: (_) => AlertDialog(
          title: const Text('Confirm'),
          content: Text(msg),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
            TextButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Confirm', style: TextStyle(color: EteraTheme.error)),
            ),
          ],
        ),
      ) ?? false;

  void _snack(String msg, bool ok) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: ok ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      // ── Stats bar ──────────────────────────────────────────────
      if (_stats.isNotEmpty)
        Container(
          color: Colors.deepPurple.withValues(alpha: 0.06),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Row(mainAxisAlignment: MainAxisAlignment.spaceAround, children: [
            _MiniStat('Pending', '${_stats['pending'] ?? 0}', Colors.orange),
            _MiniStat('Approved', '${_stats['approved'] ?? 0}', EteraTheme.green),
            _MiniStat('Business', '${_stats['business_owners'] ?? 0}', Colors.blue),
            _MiniStat('Gar+Shop', '${_stats['garages_shops'] ?? 0}', EteraTheme.teal),
          ]),
        ),
      // ── Role filter ────────────────────────────────────────────
      SizedBox(
        height: 48,
        child: ListView.separated(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          itemCount: _roles.length,
          separatorBuilder: (_, __) => const SizedBox(width: 8),
          itemBuilder: (_, i) {
            final sel = _roleFilter == _roles[i];
            return ChoiceChip(
              label: Text(_roleLabels[i]),
              selected: sel,
              selectedColor: Colors.deepPurple,
              labelStyle: TextStyle(
                color: sel ? Colors.white : EteraTheme.textMuted,
                fontSize: 12,
                fontWeight: sel ? FontWeight.w600 : FontWeight.normal,
              ),
              onSelected: (_) { setState(() => _roleFilter = _roles[i]); _load(); },
            );
          },
        ),
      ),
      // ── Status filter ──────────────────────────────────────────
      SizedBox(
        height: 44,
        child: ListView.separated(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
          itemCount: 3,
          separatorBuilder: (_, __) => const SizedBox(width: 8),
          itemBuilder: (_, i) {
            final statuses = [null, 'pending', 'approved'];
            final labels   = ['All Status', 'Pending', 'Approved'];
            final colors   = [Colors.grey, Colors.orange, EteraTheme.green];
            final sel = _statusFilter == statuses[i];
            return ChoiceChip(
              label: Text(labels[i]),
              selected: sel,
              selectedColor: colors[i],
              labelStyle: TextStyle(
                color: sel ? Colors.white : EteraTheme.textMuted,
                fontSize: 11,
                fontWeight: sel ? FontWeight.w600 : FontWeight.normal,
              ),
              onSelected: (_) { setState(() => _statusFilter = statuses[i]); _load(); },
            );
          },
        ),
      ),
      Expanded(child: _body()),
    ]);
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator(color: Colors.deepPurple));
    if (_error != null) {
      return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.wifi_off, size: 48, color: EteraTheme.textMuted),
        const SizedBox(height: 12),
        Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: _load, child: const Text('Retry')),
      ]));
    }
    if (_users.isEmpty) {
      return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
        Icon(Icons.group_outlined, size: 64, color: Colors.deepPurple.withValues(alpha: 0.3)),
        const SizedBox(height: 16),
        const Text('No users found', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        const Text('Try changing the filters', style: TextStyle(color: EteraTheme.textMuted)),
      ]));
    }
    return RefreshIndicator(
      color: Colors.deepPurple,
      onRefresh: _load,
      child: ListView.builder(
        controller: _scroll,
        padding: const EdgeInsets.fromLTRB(16, 4, 16, 32),
        itemCount: _users.length + (_loadingMore ? 1 : 0),
        itemBuilder: (_, i) {
          if (i == _users.length) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Center(child: CircularProgressIndicator(color: Colors.deepPurple)),
            );
          }
          final u = _users[i];
          return _UserCard(
            user: u,
            onApprove: () => _approve(u, i),
            onRevoke:  () => _revoke(u, i),
            onDelete:  () => _delete(u, i),
          );
        },
      ),
    );
  }
}

// ─── Mini Stat ────────────────────────────────────────────────────────────────
class _MiniStat extends StatelessWidget {
  final String label;
  final String value;
  final Color color;
  const _MiniStat(this.label, this.value, this.color);

  @override
  Widget build(BuildContext context) {
    return Column(mainAxisSize: MainAxisSize.min, children: [
      Text(value, style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: color)),
      Text(label, style: const TextStyle(fontSize: 10, color: EteraTheme.textMuted)),
    ]);
  }
}

// ─── User Card ────────────────────────────────────────────────────────────────
class _UserCard extends StatelessWidget {
  final Map<String, dynamic> user;
  final VoidCallback onApprove;
  final VoidCallback onRevoke;
  final VoidCallback onDelete;
  const _UserCard({required this.user, required this.onApprove, required this.onRevoke, required this.onDelete});

  @override
  Widget build(BuildContext context) {
    final role     = user['role']?.toString() ?? '';
    final approved = user['approved'] == true;
    final rc       = _roleColor(role);
    final brands   = (user['brands'] as List?)?.cast<String>() ?? [];

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          CircleAvatar(
            radius: 20,
            backgroundColor: rc.withValues(alpha: 0.15),
            child: Text((user['name']?.toString() ?? 'U')[0].toUpperCase(),
                style: TextStyle(fontWeight: FontWeight.w700, color: rc, fontSize: 16)),
          ),
          const SizedBox(width: 10),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(user['name']?.toString() ?? '—',
                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
            Text(user['phone_number']?.toString() ?? '',
                style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ])),
          Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
            _Badge(_roleLabel(role), rc),
            const SizedBox(height: 4),
            _Badge(approved ? 'Approved' : 'Pending',
                approved ? EteraTheme.green : Colors.orange),
          ]),
        ]),
        if ((user['tin_number']?.toString() ?? '').isNotEmpty) ...[
          const SizedBox(height: 6),
          _info(Icons.receipt_outlined, 'TIN: ${user['tin_number']}'),
        ],
        if ((user['location']?.toString() ?? '').isNotEmpty) ...[
          const SizedBox(height: 2),
          _info(Icons.location_on_outlined, user['location']!.toString()),
        ],
        if (brands.isNotEmpty) ...[
          const SizedBox(height: 2),
          _info(Icons.directions_car_outlined, brands.join(', ')),
        ],
        if ((user['email']?.toString() ?? '').isNotEmpty) ...[
          const SizedBox(height: 2),
          _info(Icons.email_outlined, user['email']!.toString()),
        ],
        const SizedBox(height: 10),
        const Divider(height: 1),
        const SizedBox(height: 8),
        Row(children: [
          if (!approved)
            Expanded(child: OutlinedButton(
              onPressed: onApprove,
              style: OutlinedButton.styleFrom(
                foregroundColor: EteraTheme.green,
                side: const BorderSide(color: EteraTheme.green),
                padding: const EdgeInsets.symmetric(vertical: 6),
              ),
              child: const Text('Approve', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
            ))
          else
            Expanded(child: OutlinedButton(
              onPressed: onRevoke,
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.orange,
                side: const BorderSide(color: Colors.orange),
                padding: const EdgeInsets.symmetric(vertical: 6),
              ),
              child: const Text('Revoke', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
            )),
          const SizedBox(width: 8),
          Expanded(child: OutlinedButton(
            onPressed: onDelete,
            style: OutlinedButton.styleFrom(
              foregroundColor: EteraTheme.error,
              side: const BorderSide(color: EteraTheme.error),
              padding: const EdgeInsets.symmetric(vertical: 6),
            ),
            child: const Text('Delete', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
          )),
        ]),
      ]),
    );
  }

  Widget _info(IconData icon, String text) => Row(children: [
    Icon(icon, size: 13, color: EteraTheme.textMuted),
    const SizedBox(width: 4),
    Expanded(child: Text(text,
        style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted),
        maxLines: 1, overflow: TextOverflow.ellipsis)),
  ]);

  String _roleLabel(String r) {
    switch (r) {
      case 'others':         return 'Individual';
      case 'business_owner': return 'Business';
      case 'garage':         return 'Garage';
      case 'shop':           return 'Shop';
      case 'insurance':      return 'Insurance';
      default:               return r;
    }
  }

  Color _roleColor(String r) {
    switch (r) {
      case 'others':         return Colors.purple;
      case 'business_owner': return Colors.blue;
      case 'garage':         return EteraTheme.teal;
      case 'shop':           return EteraTheme.green;
      case 'insurance':      return Colors.indigo;
      default:               return EteraTheme.textMuted;
    }
  }
}

class _Badge extends StatelessWidget {
  final String label;
  final Color color;
  const _Badge(this.label, this.color);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(label,
          style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: color)),
    );
  }
}
