import 'package:flutter/material.dart';
import '../../config/theme.dart';
import 'superadmin_users_tab.dart';
import 'superadmin_admins_tab.dart';
import 'superadmin_insurances_tab.dart';
import 'superadmin_shops_tab.dart';
import 'superadmin_garages_tab.dart';
import 'superadmin_operators_tab.dart';
import 'superadmin_marketers_tab.dart';
import 'superadmin_brands_tab.dart';

class SuperadminManageTab extends StatelessWidget {
  final String? activeSection;
  final ValueChanged<String?> onSectionChanged;

  const SuperadminManageTab({
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
      _Item('users',     'Users',      'Manage user accounts',     Icons.group,                Colors.blue),
      _Item('admins',    'Admins',     'Manage admin accounts',    Icons.manage_accounts,      Colors.teal),
      _Item('insurance', 'Insurance',  'Insurance companies',      Icons.shield,               Colors.indigo),
      _Item('shops',     'Shops',      'Manage shop accounts',     Icons.store,                Color(0xFF2E7D32)),
      _Item('garages',   'Garages',    'Manage garage accounts',   Icons.garage,               Color(0xFF00838F)),
      _Item('operators', 'Operators',  'Manage operators',         Icons.engineering,          Colors.orange),
      _Item('marketers', 'Marketers',  'Manage marketers',         Icons.campaign,             Colors.purple),
      _Item('brands',    'Brands',     'Manage vehicle brands',    Icons.category,             Colors.amber),
    ];

    return CustomScrollView(
      slivers: [
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
            child: Text('Management',
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
      case 'users':     return const SuperadminUsersTab();
      case 'admins':    return const SuperadminAdminsTab();
      case 'insurance': return const SuperadminInsurancesTab();
      case 'shops':     return const SuperadminShopsTab();
      case 'garages':   return const SuperadminGaragesTab();
      case 'operators': return const SuperadminOperatorsTab();
      case 'marketers': return const SuperadminMarketersTab();
      case 'brands':    return const SuperadminBrandsTab();
      default:          return const SizedBox.shrink();
    }
  }

  String _title(String s) => const {
    'users': 'Users', 'admins': 'Admins', 'insurance': 'Insurance',
    'shops': 'Shops', 'garages': 'Garages', 'operators': 'Operators',
    'marketers': 'Marketers', 'brands': 'Brands',
  }[s] ?? s;

  IconData _icon(String s) => const {
    'users': Icons.group, 'admins': Icons.manage_accounts, 'insurance': Icons.shield,
    'shops': Icons.store, 'garages': Icons.garage, 'operators': Icons.engineering,
    'marketers': Icons.campaign, 'brands': Icons.category,
  }[s] ?? Icons.list;

  Color _color(String s) {
    switch (s) {
      case 'users':     return Colors.blue;
      case 'admins':    return Colors.teal;
      case 'insurance': return Colors.indigo;
      case 'shops':     return const Color(0xFF2E7D32);
      case 'garages':   return const Color(0xFF00838F);
      case 'operators': return Colors.orange;
      case 'marketers': return Colors.purple;
      case 'brands':    return Colors.amber;
      default:          return Colors.grey;
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
              Text('Back to Management',
                  style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
            ]),
          ),
        ),
      ),
      const Divider(height: 1),
      Expanded(child: child),
    ]);
  }
}
