import 'package:flutter/material.dart';
import '../../config/theme.dart';
import 'superadmin_analytics_tab.dart';
import 'superadmin_transactions_tab.dart';
import 'superadmin_ratings_tab.dart';
import 'superadmin_settings_tab.dart';

class SuperadminReportsTab extends StatelessWidget {
  final String? activeSection;
  final ValueChanged<String?> onSectionChanged;

  const SuperadminReportsTab({
    super.key,
    this.activeSection,
    required this.onSectionChanged,
  });

  @override
  Widget build(BuildContext context) {
    if (activeSection != null) {
      return _SectionWrapper(
        title: _title(activeSection!),
        icon: _icon(activeSection!),
        color: _color(activeSection!),
        onBack: () => onSectionChanged(null),
        child: _buildSection(activeSection!),
      );
    }
    return _buildHub(context);
  }

  Widget _buildHub(BuildContext context) {
    const items = [
      _Item('analytics',    'Analytics',    'User earnings & payments',   Icons.bar_chart,                Colors.deepOrange),
      _Item('transactions', 'Transactions', 'All financial transactions', Icons.account_balance_wallet,   Colors.blueGrey),
      _Item('ratings',      'Ratings',      'Garage & shop ratings',      Icons.star,                     Colors.amber),
      _Item('settings',     'Settings',     'Costs, commissions & emails',Icons.settings,                 Color(0xFF546E7A)),
    ];

    return CustomScrollView(
      slivers: [
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
            child: Text('Reports & Settings',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
          ),
        ),
        SliverPadding(
          padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
          sliver: SliverGrid(
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 1.3,
            ),
            delegate: SliverChildBuilderDelegate(
              (_, i) => _ItemCard(item: items[i], onTap: () => onSectionChanged(items[i].key)),
              childCount: items.length,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildSection(String section) {
    switch (section) {
      case 'analytics':    return const SuperadminAnalyticsTab();
      case 'transactions': return const SuperadminTransactionsTab();
      case 'ratings':      return const SuperadminRatingsTab();
      case 'settings':     return const SuperadminSettingsTab();
      default:             return const SizedBox.shrink();
    }
  }

  String _title(String s) => const {
    'analytics': 'Analytics', 'transactions': 'Transactions',
    'ratings': 'Ratings',     'settings': 'Settings',
  }[s] ?? s;

  IconData _icon(String s) => const {
    'analytics': Icons.bar_chart, 'transactions': Icons.account_balance_wallet,
    'ratings': Icons.star,        'settings': Icons.settings,
  }[s] ?? Icons.list;

  Color _color(String s) {
    switch (s) {
      case 'analytics':    return Colors.deepOrange;
      case 'transactions': return Colors.blueGrey;
      case 'ratings':      return Colors.amber;
      case 'settings':     return const Color(0xFF546E7A);
      default:             return Colors.grey;
    }
  }
}

// ─── Data ──────────────────────────────────────────────────────────────────────
class _Item {
  final String key;
  final String label;
  final String description;
  final IconData icon;
  final Color color;
  const _Item(this.key, this.label, this.description, this.icon, this.color);
}

// ─── Card ──────────────────────────────────────────────────────────────────────
class _ItemCard extends StatelessWidget {
  final _Item item;
  final VoidCallback onTap;
  const _ItemCard({required this.item, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: item.color.withValues(alpha: 0.08),
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: item.color.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(item.icon, color: item.color, size: 22),
              ),
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(item.label,
                    style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: item.color)),
                Text(item.description,
                    style: const TextStyle(fontSize: 10, color: EteraTheme.textMuted),
                    maxLines: 1, overflow: TextOverflow.ellipsis),
              ]),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Section wrapper with back header ─────────────────────────────────────────
class _SectionWrapper extends StatelessWidget {
  final String title;
  final IconData icon;
  final Color color;
  final VoidCallback onBack;
  final Widget child;
  const _SectionWrapper({
    required this.title, required this.icon, required this.color,
    required this.onBack, required this.child,
  });

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      Material(
        color: Colors.white,
        elevation: 0,
        child: InkWell(
          onTap: onBack,
          child: Padding(
            padding: const EdgeInsets.fromLTRB(12, 10, 16, 10),
            child: Row(children: [
              Icon(Icons.arrow_back_ios_new, size: 15, color: color),
              const SizedBox(width: 10),
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, size: 16, color: color),
              ),
              const SizedBox(width: 10),
              Text(title,
                  style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: color)),
              const Spacer(),
              const Text('Back to Reports',
                  style: TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
            ]),
          ),
        ),
      ),
      const Divider(height: 1),
      Expanded(child: child),
    ]);
  }
}
