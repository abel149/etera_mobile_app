import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/etera_card.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;

    return Scaffold(
      appBar: AppBar(
        title: const Text('E-Tera'),
        automaticallyImplyLeading: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            onPressed: () {},
          ),
          PopupMenuButton<String>(
            icon: const Icon(Icons.more_vert),
            onSelected: (value) {
              if (value == 'logout') {
                auth.logout();
                Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
              }
            },
            itemBuilder: (context) => [
              const PopupMenuItem(value: 'logout', child: Text('Logout')),
            ],
          ),
        ],
      ),
      body: IndexedStack(
        index: _currentIndex,
        children: [
          _DashboardTab(user: user),
          const _ProformaPlaceholder(),
          const _ProfileTab(),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => Navigator.pushNamed(context, '/create-proforma'),
        backgroundColor: EteraTheme.green,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('New Proforma', style: TextStyle(fontWeight: FontWeight.w600)),
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (i) => setState(() => _currentIndex = i),
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.dashboard_outlined), activeIcon: Icon(Icons.dashboard), label: 'Home'),
          BottomNavigationBarItem(icon: Icon(Icons.receipt_long_outlined), activeIcon: Icon(Icons.receipt_long), label: 'Proformas'),
          BottomNavigationBarItem(icon: Icon(Icons.person_outline), activeIcon: Icon(Icons.person), label: 'Profile'),
        ],
      ),
    );
  }
}

// ─── Dashboard Tab ─────────────────────────────────────────────────
class _DashboardTab extends StatelessWidget {
  final dynamic user;
  const _DashboardTab({this.user});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Welcome
          Text(
            'Welcome, ${user?.name ?? 'User'}',
            style: Theme.of(context).textTheme.headlineMedium,
          ),
          const SizedBox(height: 4),
          Text(
            user?.roleLabel ?? '',
            style: TextStyle(fontSize: 14, color: EteraTheme.textMuted),
          ),
          const SizedBox(height: 24),

          // Stats row
          Row(
            children: [
              Expanded(
                child: _StatCard(
                  title: 'Balance',
                  value: '${user?.balance ?? 0} Birr',
                  icon: Icons.account_balance_wallet_outlined,
                  color: EteraTheme.green,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _StatCard(
                  title: 'Store ID',
                  value: user?.storeId ?? 'N/A',
                  icon: Icons.store_outlined,
                  color: EteraTheme.teal,
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),

          // Quick actions
          Text(
            'Quick Actions',
            style: Theme.of(context).textTheme.titleLarge,
          ),
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
              subtitle: const Text('Create a new proforma request', style: TextStyle(fontSize: 12)),
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
              subtitle: const Text('View your proforma history', style: TextStyle(fontSize: 12)),
              trailing: const Icon(Icons.arrow_forward_ios, size: 16, color: EteraTheme.textMuted),
              onTap: () {},
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Stat Card Widget ─────────────────────────────────────────────
class _StatCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;

  const _StatCard({
    required this.title,
    required this.value,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return EteraCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: color, size: 20),
              const Spacer(),
            ],
          ),
          const SizedBox(height: 12),
          Text(title, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: EteraTheme.textPrimary,
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Proformas Placeholder ─────────────────────────────────────────
class _ProformaPlaceholder extends StatelessWidget {
  const _ProformaPlaceholder();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.receipt_long_outlined, size: 64, color: EteraTheme.green.withValues(alpha: 0.3)),
          const SizedBox(height: 16),
          Text(
            'Proforma List',
            style: Theme.of(context).textTheme.titleLarge,
          ),
          const SizedBox(height: 8),
          Text(
            'Your submitted proformas will appear here',
            style: TextStyle(color: EteraTheme.textMuted),
          ),
        ],
      ),
    );
  }
}

// ─── Profile Tab ──────────────────────────────────────────────────
class _ProfileTab extends StatelessWidget {
  const _ProfileTab();

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          // Avatar
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              gradient: EteraTheme.primaryGradient,
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Text(
                (user?.name ?? 'U')[0].toUpperCase(),
                style: const TextStyle(fontSize: 32, fontWeight: FontWeight.w700, color: Colors.white),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Text(user?.name ?? 'User', style: Theme.of(context).textTheme.titleLarge),
          Text(user?.roleLabel ?? '', style: TextStyle(color: EteraTheme.textMuted)),
          const SizedBox(height: 24),

          EteraCard(
            child: Column(
              children: [
                _profileRow(Icons.phone, 'Phone', user?.phoneNumber ?? ''),
                const Divider(height: 24),
                _profileRow(Icons.email, 'Email', user?.email ?? 'Not set'),
                const Divider(height: 24),
                _profileRow(Icons.location_on, 'Location', user?.location ?? 'Not set'),
                const Divider(height: 24),
                _profileRow(Icons.store, 'Store ID', user?.storeId ?? 'N/A'),
                const Divider(height: 24),
                _profileRow(Icons.account_balance_wallet, 'Balance', '${user?.balance ?? 0} Birr'),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _profileRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, color: EteraTheme.green, size: 20),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: const TextStyle(fontSize: 11, color: EteraTheme.textMuted)),
              Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
            ],
          ),
        ),
      ],
    );
  }
}
