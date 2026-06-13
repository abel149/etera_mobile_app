import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/superadmin_service.dart';
import '../../widgets/etera_card.dart';

class SuperadminRatingsTab extends StatefulWidget {
  const SuperadminRatingsTab({super.key});

  @override
  State<SuperadminRatingsTab> createState() => _SuperadminRatingsTabState();
}

class _SuperadminRatingsTabState extends State<SuperadminRatingsTab> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _users = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await SuperadminService.getRatings();
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = (res['data']['users'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      setState(() { _loading = false; _users = raw; });
    } else {
      setState(() {
        _loading = false;
        _error   = res['message']?.toString() ?? 'Failed to load ratings';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      color: Colors.amber,
      onRefresh: _load,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
              child: const Text('Ratings',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 8)),
          if (_loading)
            const SliverToBoxAdapter(
                child: Center(child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(color: Colors.amber),
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
          else if (_users.isEmpty)
            const SliverFillRemaining(child: Center(
              child: Text('No ratings found', style: TextStyle(color: EteraTheme.textMuted)),
            ))
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (_, i) {
                    final u = _users[i];
                    return _RatingCard(user: u);
                  },
                  childCount: _users.length,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

// ─── Rating Card ────────────────────────────────────────────────────────────────
class _RatingCard extends StatelessWidget {
  final Map<String, dynamic> user;
  const _RatingCard({required this.user});

  @override
  Widget build(BuildContext context) {
    final avgRating = double.tryParse(
      user['reviews_avg_rating']?.toString() ?? '0',
    ) ?? 0.0;

    final reviewCount = int.tryParse(
      user['reviews_count']?.toString() ?? '0',
    ) ?? 0;

    final reviews = (user['reviews'] as List? ?? [])
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();

    return EteraCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            width: 42, height: 42,
            decoration: BoxDecoration(
              color: _getRoleColor(user['role']?.toString() ?? '').withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: Center(child: Text(
              (user['name']?.toString() ?? 'U')[0].toUpperCase(),
              style: TextStyle(fontWeight: FontWeight.w700, color: _getRoleColor(user['role']?.toString() ?? ''), fontSize: 18),
            )),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(user['name']?.toString() ?? '—',
                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
            Text(user['role']?.toString() ?? '',
                style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          ])),
          const SizedBox(width: 8),
          Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
            Row(children: [
              const Icon(Icons.star, size: 16, color: Colors.amber),
              const SizedBox(width: 4),
              Text(avgRating.toStringAsFixed(1),
                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
            ]),
            Text('$reviewCount review${reviewCount == 1 ? '' : 's'}',
                style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
          ]),
        ]),
        if (reviews.isNotEmpty) ...[
          const SizedBox(height: 12),
          const Divider(height: 1),
          const SizedBox(height: 8),
          ...reviews.take(3).map((r) => Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Icon(Icons.star, size: 12, color: Colors.amber),
              const SizedBox(width: 6),
              Expanded(child: Text(
                r['review']?.toString() ?? '',
                style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted),
                maxLines: 2, overflow: TextOverflow.ellipsis,
              )),
            ]),
          )),
          if (reviews.length > 3)
            Padding(
              padding: const EdgeInsets.only(top: 4),
              child: Text('+${reviews.length - 3} more reviews',
                  style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
            ),
        ],
      ]),
    );
  }

  Color _getRoleColor(String role) {
    switch (role.toLowerCase()) {
      case 'garage': return EteraTheme.teal;
      case 'shop': return EteraTheme.green;
      default: return Colors.grey;
    }
  }
}
