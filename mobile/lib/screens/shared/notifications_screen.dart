import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../shop/shop_proforma_detail_screen.dart';
import '../superadmin/admin_proforma_detail_screen.dart';
import '../business_owner/bo_proforma_detail_screen.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _notifications = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final res = await ApiService.get(ApiConfig.notifications, withAuth: true);
    if (!mounted) return;
    if (res['unauthorized'] == true) {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    if (res['success'] == true) {
      final raw = res['data'] as List? ?? [];
      setState(() {
        _loading = false;
        _notifications =
            raw.map((e) => Map<String, dynamic>.from(e as Map)).toList();
      });
      // Mark all as read
      ApiService.put(ApiConfig.notificationsMarkRead, {}, withAuth: true);
    } else {
      setState(() {
        _loading = false;
        _error = res['message']?.toString() ?? 'Failed to load';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: RefreshIndicator(
        color: EteraTheme.green,
        onRefresh: _load,
        child: _loading
            ? const Center(
                child: CircularProgressIndicator(color: EteraTheme.green))
            : _error != null
                ? ListView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    children: [
                      SizedBox(
                          height: MediaQuery.of(context).size.height * 0.2),
                      Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.wifi_off,
                                size: 48, color: EteraTheme.textMuted),
                            const SizedBox(height: 12),
                            Text(_error!,
                                style: const TextStyle(
                                    color: EteraTheme.textMuted)),
                            const SizedBox(height: 16),
                            ElevatedButton(
                                onPressed: _load,
                                child: const Text('Retry')),
                          ],
                        ),
                      ),
                    ],
                  )
                : _notifications.isEmpty
                    ? ListView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        children: [
                          SizedBox(
                              height:
                                  MediaQuery.of(context).size.height * 0.2),
                          Center(
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.notifications_none_outlined,
                                    size: 64,
                                    color: EteraTheme.green
                                        .withValues(alpha: 0.3)),
                                const SizedBox(height: 16),
                                const Text('No notifications yet',
                                    style: TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.w600)),
                                const SizedBox(height: 8),
                                const Text(
                                    'You\'ll see alerts here when something happens.',
                                    style: TextStyle(
                                        color: EteraTheme.textMuted,
                                        fontSize: 13),
                                    textAlign: TextAlign.center),
                              ],
                            ),
                          ),
                        ],
                      )
                    : ListView.builder(
                        physics: const AlwaysScrollableScrollPhysics(),
                        padding: const EdgeInsets.fromLTRB(16, 12, 16, 32),
                        itemCount: _notifications.length,
                        itemBuilder: (ctx, i) => _NotificationCard(
                          data: _notifications[i],
                          role: context.read<AuthProvider>().user?.role ?? '',
                        ),
                      ),
      ),
    );
  }
}

class _NotificationCard extends StatelessWidget {
  final Map<String, dynamic> data;
  final String role;
  const _NotificationCard({required this.data, required this.role});

  void _onTap(BuildContext context) {
    final type       = data['data']?['type']?.toString() ?? '';
    final proformaId = int.tryParse(data['data']?['proforma_id']?.toString() ?? '');

    switch (type) {
      case 'new_proforma':
      case 'inbox_notification':
      case 'proforma_floated':
        if (proformaId != null) {
          Navigator.push(context, MaterialPageRoute(
            builder: (_) => ShopProformaDetailScreen(proformaId: proformaId),
          ));
        }
        break;
      case 'proforma_application':
      case 'proforma_application_received':
      case 'proforma_results_ready':
        if (proformaId != null) {
          if (role == 'garage') {
            Navigator.pushNamed(context, '/garage-file-detail', arguments: proformaId);
          } else {
            Navigator.push(context, MaterialPageRoute(
              builder: (_) => const BOProformaDetailScreen(),
              settings: RouteSettings(arguments: proformaId),
            ));
          }
        }
        break;
      case 'proforma_closed':
        if (proformaId != null && (role == 'admin' || role == 'superadmin')) {
          Navigator.push(context, MaterialPageRoute(
            builder: (_) => AdminProformaDetailScreen(proformaId: proformaId),
          ));
        } else if (proformaId != null) {
          Navigator.push(context, MaterialPageRoute(
            builder: (_) => const BOProformaDetailScreen(),
            settings: RouteSettings(arguments: proformaId),
          ));
        }
        break;
      default:
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    final title = data['data']?['title']?.toString() ??
        data['title']?.toString() ??
        'Notification';
    final message = data['data']?['message']?.toString() ??
        data['message']?.toString() ??
        '';
    final isRead = data['read_at'] != null;
    final createdAt = data['created_at']?.toString() ?? '';
    final type = data['data']?['type']?.toString() ?? '';

    IconData icon;
    Color iconColor;
    switch (type) {
      case 'proforma_application':
      case 'proforma_application_received':
        icon = Icons.description_outlined;
        iconColor = EteraTheme.green;
        break;
      case 'inbox':
        icon = Icons.inbox_outlined;
        iconColor = Colors.blue;
        break;
      case 'approval_pending_signup':
        icon = Icons.person_add_outlined;
        iconColor = Colors.orange;
        break;
      case 'approval_pending_login':
        icon = Icons.login_outlined;
        iconColor = Colors.red.shade400;
        break;
      case 'new_proforma':
        icon = Icons.receipt_long_outlined;
        iconColor = EteraTheme.teal;
        break;
      default:
        icon = Icons.notifications_outlined;
        iconColor = EteraTheme.teal;
    }

    return InkWell(
      onTap: () => _onTap(context),
      borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
      child: Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: isRead ? Colors.white : EteraTheme.green.withValues(alpha: 0.04),
        borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
        border: Border.all(
          color: isRead
              ? Colors.grey.shade200
              : EteraTheme.green.withValues(alpha: 0.25),
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: iconColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, size: 20, color: iconColor),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(title,
                            style: TextStyle(
                                fontWeight: isRead
                                    ? FontWeight.w500
                                    : FontWeight.w700,
                                fontSize: 14)),
                      ),
                      if (!isRead)
                        Container(
                          width: 8,
                          height: 8,
                          decoration: const BoxDecoration(
                            color: EteraTheme.green,
                            shape: BoxShape.circle,
                          ),
                        ),
                    ],
                  ),
                  if (message.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(message,
                        style: const TextStyle(
                            fontSize: 13, color: EteraTheme.textMuted),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis),
                  ],
                  if (createdAt.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(_formatTime(createdAt),
                        style: const TextStyle(
                            fontSize: 11, color: EteraTheme.textMuted)),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    ),
    );
  }

  String _formatTime(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      final now = DateTime.now();
      final diff = now.difference(dt);
      if (diff.inMinutes < 1) return 'Just now';
      if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
      if (diff.inHours < 24) return '${diff.inHours}h ago';
      if (diff.inDays < 7) return '${diff.inDays}d ago';
      return '${dt.day}/${dt.month}/${dt.year}';
    } catch (_) {
      return iso;
    }
  }
}
