import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/admin_service.dart';
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

  final _roles = [null, 'insurance', 'business_owner', 'garage', 'shop', 'others'];
  final _roleLabels = ['All', 'Insurance', 'Business', 'Garage', 'Shop', 'Individual'];

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
    final res = await AdminService.getAllUsers(
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
      final pg = res['pagination'] as Map? ?? {};
      setState(() {
        _loading = false;
        _users = raw;
        _lastPage = (pg['last_page'] as int?) ?? 1;
      });
    } else {
      setState(() {
        _loading = false;
        _error = res['message']?.toString() ?? 'Failed to load users';
      });
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || _page >= _lastPage) return;
    setState(() => _loadingMore = true);
    final res = await AdminService.getAllUsers(
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
    final res = await AdminService.approveUser(user['id'] as int);
    if (!mounted) return;
    _showSnack(
      res['message']?.toString() ?? (res['success'] == true ? 'Approved!' : 'Failed'),
      res['success'] == true,
    );
    if (res['success'] == true) {
      setState(() => _users[index] = {..._users[index], 'approved': true});
    }
  }

  Future<void> _reject(Map<String, dynamic> user, int index) async {
    final confirmed = await _confirm('Reject user "${user['name']}"?');
    if (!confirmed) return;
    final res = await AdminService.rejectUser(user['id'] as int);
    if (!mounted) return;
    _showSnack(
      res['message']?.toString() ?? (res['success'] == true ? 'Rejected' : 'Failed'),
      res['success'] == true,
    );
    if (res['success'] == true) {
      setState(() => _users[index] = {..._users[index], 'approved': false});
    }
  }

  Future<void> _delete(Map<String, dynamic> user, int index) async {
    final confirmed = await _confirm(
        'Delete user "${user['name']}"? This cannot be undone.');
    if (!confirmed) return;
    final res = await AdminService.deleteUser(user['id'] as int);
    if (!mounted) return;
    _showSnack(
      res['message']?.toString() ?? (res['success'] == true ? 'Deleted' : 'Failed'),
      res['success'] == true,
    );
    if (res['success'] == true) setState(() => _users.removeAt(index));
  }

  Future<bool> _confirm(String message) async {
    return await showDialog<bool>(
          context: context,
          builder: (_) => AlertDialog(
            title: const Text('Confirm'),
            content: Text(message),
            actions: [
              TextButton(
                  onPressed: () => Navigator.pop(context, false),
                  child: const Text('Cancel')),
              TextButton(
                onPressed: () => Navigator.pop(context, true),
                child: const Text('Confirm',
                    style: TextStyle(color: EteraTheme.error)),
              ),
            ],
          ),
        ) ??
        false;
  }

  void _showSnack(String msg, bool success) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: success ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        _RoleFilterBar(
          roles: _roles,
          labels: _roleLabels,
          selected: _roleFilter,
          onSelect: (r) {
            setState(() => _roleFilter = r);
            _load();
          },
        ),
        _StatusFilterBar(
          selected: _statusFilter,
          onSelect: (s) {
            setState(() => _statusFilter = s);
            _load();
          },
        ),
        Expanded(child: _body()),
      ],
    );
  }

  Widget _body() {
    if (_loading) {
      return const Center(
          child: CircularProgressIndicator(color: Colors.deepPurple));
    }
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.wifi_off, size: 48, color: EteraTheme.textMuted),
            const SizedBox(height: 12),
            Text(_error!, style: const TextStyle(color: EteraTheme.textMuted)),
            const SizedBox(height: 16),
            ElevatedButton(onPressed: _load, child: const Text('Retry')),
          ],
        ),
      );
    }
    if (_users.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.group_outlined,
                size: 64, color: Colors.deepPurple.withValues(alpha: 0.3)),
            const SizedBox(height: 16),
            const Text('No users found',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            const Text('Try changing the filters',
                style: TextStyle(color: EteraTheme.textMuted)),
          ],
        ),
      );
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
              child:
                  Center(child: CircularProgressIndicator(color: Colors.deepPurple)),
            );
          }
          final user = _users[i];
          return _UserCard(
            user: user,
            onApprove: () => _approve(user, i),
            onReject:  () => _reject(user, i),
            onDelete:  () => _delete(user, i),
          );
        },
      ),
    );
  }
}

// ─── Role Filter Bar ──────────────────────────────────────────────────────────
class _RoleFilterBar extends StatelessWidget {
  final List<String?> roles;
  final List<String> labels;
  final String? selected;
  final ValueChanged<String?> onSelect;
  const _RoleFilterBar(
      {required this.roles,
      required this.labels,
      required this.selected,
      required this.onSelect});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 48,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        itemCount: roles.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (_, i) {
          final sel = selected == roles[i];
          return ChoiceChip(
            label: Text(labels[i]),
            selected: sel,
            selectedColor: Colors.deepPurple,
            labelStyle: TextStyle(
              color: sel ? Colors.white : EteraTheme.textMuted,
              fontSize: 12,
              fontWeight: sel ? FontWeight.w600 : FontWeight.normal,
            ),
            onSelected: (_) => onSelect(roles[i]),
          );
        },
      ),
    );
  }
}

// ─── Status Filter Bar ────────────────────────────────────────────────────────
class _StatusFilterBar extends StatelessWidget {
  final String? selected;
  final ValueChanged<String?> onSelect;
  const _StatusFilterBar({required this.selected, required this.onSelect});

  @override
  Widget build(BuildContext context) {
    final statuses = [null, 'pending', 'approved'];
    final labels   = ['All Status', 'Pending', 'Approved'];
    final colors   = [Colors.grey, Colors.orange, EteraTheme.green];
    return SizedBox(
      height: 44,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
        itemCount: statuses.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (_, i) {
          final sel = selected == statuses[i];
          return ChoiceChip(
            label: Text(labels[i]),
            selected: sel,
            selectedColor: colors[i],
            labelStyle: TextStyle(
              color: sel ? Colors.white : EteraTheme.textMuted,
              fontSize: 11,
              fontWeight: sel ? FontWeight.w600 : FontWeight.normal,
            ),
            onSelected: (_) => onSelect(statuses[i]),
          );
        },
      ),
    );
  }
}

// ─── User Card ────────────────────────────────────────────────────────────────
class _UserCard extends StatelessWidget {
  final Map<String, dynamic> user;
  final VoidCallback onApprove;
  final VoidCallback onReject;
  final VoidCallback onDelete;
  const _UserCard(
      {required this.user,
      required this.onApprove,
      required this.onReject,
      required this.onDelete});

  @override
  Widget build(BuildContext context) {
    final role     = user['role']?.toString() ?? '';
    final approved = user['approved'] == true;
    final roleColor = _roleColor(role);
    final brands = (user['brands'] as List?)?.cast<String>() ?? [];

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 20,
                backgroundColor: roleColor.withValues(alpha: 0.15),
                child: Text(
                  (user['name']?.toString() ?? 'U')[0].toUpperCase(),
                  style: TextStyle(
                      fontWeight: FontWeight.w700,
                      color: roleColor,
                      fontSize: 16),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(user['name']?.toString() ?? '—',
                        style: const TextStyle(
                            fontWeight: FontWeight.w600, fontSize: 14)),
                    Text(user['phone_number']?.toString() ?? '',
                        style: const TextStyle(
                            fontSize: 12, color: EteraTheme.textMuted)),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                    decoration: BoxDecoration(
                      color: roleColor.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(_roleLabel(role),
                        style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                            color: roleColor)),
                  ),
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                    decoration: BoxDecoration(
                      color: approved
                          ? EteraTheme.green.withValues(alpha: 0.1)
                          : Colors.orange.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      approved ? 'Approved' : 'Pending',
                      style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color: approved ? EteraTheme.green : Colors.orange),
                    ),
                  ),
                ],
              ),
            ],
          ),
          if ((user['tin_number']?.toString() ?? '').isNotEmpty) ...[
            const SizedBox(height: 6),
            _infoRow(Icons.receipt_outlined,
                'TIN: ${user['tin_number']}'),
          ],
          if ((user['location']?.toString() ?? '').isNotEmpty) ...[
            const SizedBox(height: 2),
            _infoRow(Icons.location_on_outlined,
                user['location']!.toString(), maxLines: 1),
          ],
          if (brands.isNotEmpty) ...[
            const SizedBox(height: 2),
            _infoRow(Icons.directions_car_outlined, brands.join(', '),
                maxLines: 1),
          ],
          if ((user['email']?.toString() ?? '').isNotEmpty) ...[
            const SizedBox(height: 2),
            _infoRow(Icons.email_outlined, user['email']!.toString()),
          ],
          const SizedBox(height: 10),
          const Divider(height: 1),
          const SizedBox(height: 8),
          Row(
            children: [
              if (!approved)
                Expanded(
                  child: OutlinedButton(
                    onPressed: onApprove,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: EteraTheme.green,
                      side: const BorderSide(color: EteraTheme.green),
                      padding: const EdgeInsets.symmetric(vertical: 7),
                    ),
                    child: const Text('Approve',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                  ),
                ),
              if (!approved) const SizedBox(width: 8),
              if (approved)
                Expanded(
                  child: OutlinedButton(
                    onPressed: onReject,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.orange,
                      side: const BorderSide(color: Colors.orange),
                      padding: const EdgeInsets.symmetric(vertical: 7),
                    ),
                    child: const Text('Revoke',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                  ),
                ),
              if (approved) const SizedBox(width: 8),
              Expanded(
                child: OutlinedButton(
                  onPressed: onDelete,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: EteraTheme.error,
                    side: const BorderSide(color: EteraTheme.error),
                    padding: const EdgeInsets.symmetric(vertical: 7),
                  ),
                  child: const Text('Delete',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _infoRow(IconData icon, String text, {int maxLines = 2}) {
    return Row(
      children: [
        Icon(icon, size: 13, color: EteraTheme.textMuted),
        const SizedBox(width: 4),
        Expanded(
          child: Text(text,
              style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted),
              maxLines: maxLines,
              overflow: TextOverflow.ellipsis),
        ),
      ],
    );
  }

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
