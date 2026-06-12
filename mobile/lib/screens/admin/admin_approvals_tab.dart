import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/admin_service.dart';
import '../../widgets/etera_card.dart';

class AdminApprovalsTab extends StatefulWidget {
  const AdminApprovalsTab({super.key});

  @override
  State<AdminApprovalsTab> createState() => _AdminApprovalsTabState();
}

class _AdminApprovalsTabState extends State<AdminApprovalsTab> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _items = [];
  int _page = 1;
  int _lastPage = 1;
  bool _loadingMore = false;
  String? _roleFilter;

  final _roles      = [null, 'others', 'business_owner', 'garage', 'shop'];
  final _roleLabels = ['All', 'Individual', 'Business', 'Garage', 'Shop'];

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
    setState(() { _loading = true; _error = null; _page = 1; _items = []; });
    final res = await AdminService.getPendingApprovals(role: _roleFilter, page: 1);
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = (res['data'] as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
      final pg = res['pagination'] as Map? ?? {};
      setState(() {
        _loading = false;
        _items = raw;
        _lastPage = (pg['last_page'] as int?) ?? 1;
      });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load'; });
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || _page >= _lastPage) return;
    setState(() { _loadingMore = true; });
    final res = await AdminService.getPendingApprovals(role: _roleFilter, page: _page + 1);
    if (!mounted) return;
    if (res['success'] == true) {
      final raw = (res['data'] as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
      setState(() { _page++; _items.addAll(raw); _loadingMore = false; });
    } else {
      setState(() { _loadingMore = false; });
    }
  }

  Future<void> _approve(int id, int index) async {
    final res = await AdminService.approveUser(id);
    if (!mounted) return;
    final msg = res['message']?.toString() ?? (res['success'] == true ? 'Approved!' : 'Failed');
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: res['success'] == true ? EteraTheme.green : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
    if (res['success'] == true) setState(() => _items.removeAt(index));
  }

  Future<void> _reject(int id, int index) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Reject User'),
        content: const Text('Reject this user registration?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Reject', style: TextStyle(color: EteraTheme.error)),
          ),
        ],
      ),
    );
    if (confirmed != true) return;

    final res = await AdminService.rejectUser(id);
    if (!mounted) return;
    final msg = res['message']?.toString() ?? (res['success'] == true ? 'Rejected' : 'Failed');
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: res['success'] == true ? EteraTheme.teal : EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
    if (res['success'] == true) setState(() => _items.removeAt(index));
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        SizedBox(
          height: 48,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            itemCount: _roles.length,
            separatorBuilder: (_, __) => const SizedBox(width: 8),
            itemBuilder: (_, i) {
              final selected = _roleFilter == _roles[i];
              return ChoiceChip(
                label: Text(_roleLabels[i]),
                selected: selected,
                selectedColor: EteraTheme.green,
                labelStyle: TextStyle(
                  color: selected ? Colors.white : EteraTheme.textMuted,
                  fontSize: 12,
                  fontWeight: selected ? FontWeight.w600 : FontWeight.normal,
                ),
                onSelected: (_) {
                  setState(() => _roleFilter = _roles[i]);
                  _load();
                },
              );
            },
          ),
        ),
        Expanded(
          child: _loading
              ? const Center(child: CircularProgressIndicator(color: EteraTheme.green))
              : _error != null
                  ? _errorView()
                  : _items.isEmpty
                      ? _emptyView()
                      : RefreshIndicator(
                          color: EteraTheme.green,
                          onRefresh: _load,
                          child: ListView.builder(
                            controller: _scroll,
                            padding: const EdgeInsets.fromLTRB(16, 4, 16, 32),
                            itemCount: _items.length + (_loadingMore ? 1 : 0),
                            itemBuilder: (_, i) {
                              if (i == _items.length) {
                                return const Center(
                                    child: Padding(
                                      padding: EdgeInsets.all(16),
                                      child: CircularProgressIndicator(color: EteraTheme.green),
                                    ));
                              }
                              final item = _items[i];
                              final id   = item['id'] as int;
                              return _ApprovalCard(
                                item: item,
                                onApprove: () => _approve(id, i),
                                onReject:  () => _reject(id, i),
                              );
                            },
                          ),
                        ),
        ),
      ],
    );
  }

  Widget _errorView() => Center(
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

  Widget _emptyView() => Center(
    child: Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(Icons.check_circle_outline, size: 64, color: EteraTheme.green.withValues(alpha: 0.3)),
        const SizedBox(height: 16),
        const Text('All clear!', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        const Text('No pending approvals', style: TextStyle(color: EteraTheme.textMuted)),
      ],
    ),
  );
}

// ─── Approval Card ────────────────────────────────────────────────────────────

class _ApprovalCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback onApprove;
  final VoidCallback onReject;

  const _ApprovalCard({required this.item, required this.onApprove, required this.onReject});

  @override
  Widget build(BuildContext context) {
    final role = item['role']?.toString() ?? '';
    final roleLabel = _roleLabel(role);
    final roleColor = _roleColor(role);

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: roleColor.withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    (item['name']?.toString() ?? 'U')[0].toUpperCase(),
                    style: TextStyle(fontWeight: FontWeight.w700, color: roleColor, fontSize: 18),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(item['name']?.toString() ?? '—',
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                    Text(item['phone_number']?.toString() ?? '',
                        style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: roleColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(roleLabel,
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: roleColor)),
              ),
            ],
          ),
          if ((item['tin_number']?.toString() ?? '').isNotEmpty) ...[
            const SizedBox(height: 6),
            Row(
              children: [
                const Icon(Icons.receipt_outlined, size: 14, color: EteraTheme.textMuted),
                const SizedBox(width: 4),
                Text('TIN: ${item['tin_number']}',
                    style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
              ],
            ),
          ],
          if ((item['location']?.toString() ?? '').isNotEmpty) ...[
            const SizedBox(height: 2),
            Row(
              children: [
                const Icon(Icons.location_on_outlined, size: 14, color: EteraTheme.textMuted),
                const SizedBox(width: 4),
                Expanded(child: Text(item['location']!,
                    style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted),
                    overflow: TextOverflow.ellipsis)),
              ],
            ),
          ],
          const SizedBox(height: 10),
          const Divider(height: 1),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: onReject,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: EteraTheme.error,
                    side: const BorderSide(color: EteraTheme.error),
                    padding: const EdgeInsets.symmetric(vertical: 8),
                  ),
                  child: const Text('Reject', style: TextStyle(fontWeight: FontWeight.w600)),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: ElevatedButton(
                  onPressed: onApprove,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: EteraTheme.green,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    elevation: 0,
                  ),
                  child: const Text('Approve', style: TextStyle(fontWeight: FontWeight.w600)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  String _roleLabel(String r) {
    switch (r) {
      case 'others':        return 'Individual';
      case 'business_owner':return 'Business';
      case 'garage':        return 'Garage';
      case 'shop':          return 'Shop';
      default:              return r;
    }
  }

  Color _roleColor(String r) {
    switch (r) {
      case 'others':        return Colors.purple;
      case 'business_owner':return Colors.blue;
      case 'garage':        return EteraTheme.teal;
      case 'shop':          return EteraTheme.green;
      default:              return EteraTheme.textMuted;
    }
  }
}
