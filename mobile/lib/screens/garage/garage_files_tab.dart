import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/proforma.dart';
import '../../providers/auth_provider.dart';
import '../../services/garage_service.dart';
import '../../widgets/etera_card.dart';

class GarageFilesTab extends StatefulWidget {
  final ValueNotifier<int>? refreshTrigger;
  const GarageFilesTab({super.key, this.refreshTrigger});

  @override
  State<GarageFilesTab> createState() => _GarageFilesTabState();
}

class _GarageFilesTabState extends State<GarageFilesTab>
    with SingleTickerProviderStateMixin {
  late final TabController _tabCtrl;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Material(
          color: Theme.of(context).scaffoldBackgroundColor,
          child: TabBar(
            controller: _tabCtrl,
            labelColor: EteraTheme.green,
            unselectedLabelColor: EteraTheme.textMuted,
            indicatorColor: EteraTheme.green,
            tabs: const [
              Tab(text: 'My Files'),
              Tab(text: 'Received'),
            ],
          ),
        ),
        Expanded(
          child: TabBarView(
            controller: _tabCtrl,
            children: [
              _MyFilesList(refreshTrigger: widget.refreshTrigger),
              const _ReceivedList(),
            ],
          ),
        ),
      ],
    );
  }
}

// ─── My Files list ───────────────────────────────────────────────
class _MyFilesList extends StatefulWidget {
  final ValueNotifier<int>? refreshTrigger;
  const _MyFilesList({this.refreshTrigger});

  @override
  State<_MyFilesList> createState() => _MyFilesListState();
}

class _MyFilesListState extends State<_MyFilesList> {
  bool _loading = true;
  List<ProformaItem> _items = [];
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
    setState(() { _loading = true; _error = null; });
    final result = await GarageService.getMyFiles();
    if (!mounted) return;
    if (result.error == 'unauthorized') {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    setState(() { _loading = false; _items = result.items; _error = result.error; });
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator(color: EteraTheme.green));
    }
    if (_error != null) {
      return _CenteredMsg(msg: _error!, isError: true, onRetry: _load);
    }
    if (_items.isEmpty) {
      return _CenteredMsg(
        msg: 'No files created yet.\nTap + to create one.',
        icon: Icons.folder_open_outlined,
      );
    }
    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: _load,
      child: LayoutBuilder(builder: (context, constraints) {
        final hPad = constraints.maxWidth < 380 ? 12.0 : 16.0;
        return ListView.builder(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: EdgeInsets.symmetric(horizontal: hPad, vertical: 12),
          itemCount: _items.length,
          itemBuilder: (_, i) => _FileCard(item: _items[i]),
        );
      }),
    );
  }
}

// ─── Received list ────────────────────────────────────────────────
class _ReceivedList extends StatefulWidget {
  const _ReceivedList();

  @override
  State<_ReceivedList> createState() => _ReceivedListState();
}

class _ReceivedListState extends State<_ReceivedList> {
  bool _loading = true;
  List<ProformaItem> _items = [];
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final result = await GarageService.getReceivedProformas();
    if (!mounted) return;
    if (result.error == 'unauthorized') {
      context.read<AuthProvider>().logout();
      Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
      return;
    }
    setState(() { _loading = false; _items = result.items; _error = result.error; });
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator(color: EteraTheme.green));
    }
    if (_error != null) {
      return _CenteredMsg(msg: _error!, isError: true, onRetry: _load);
    }
    if (_items.isEmpty) {
      return const _CenteredMsg(
        msg: 'No completed proformas yet.',
        icon: Icons.inbox_outlined,
      );
    }
    return RefreshIndicator(
      color: EteraTheme.green,
      onRefresh: _load,
      child: LayoutBuilder(builder: (context, constraints) {
        final hPad = constraints.maxWidth < 380 ? 12.0 : 16.0;
        return ListView.builder(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: EdgeInsets.symmetric(horizontal: hPad, vertical: 12),
          itemCount: _items.length,
          itemBuilder: (_, i) => _FileCard(item: _items[i]),
        );
      }),
    );
  }
}

// ─── Shared centered message ──────────────────────────────────────
class _CenteredMsg extends StatelessWidget {
  final String msg;
  final bool isError;
  final VoidCallback? onRetry;
  final IconData icon;

  const _CenteredMsg({
    required this.msg,
    this.isError = false,
    this.onRetry,
    this.icon = Icons.folder_open_outlined,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(isError ? Icons.wifi_off : icon,
                size: 48,
                color: isError ? EteraTheme.error : EteraTheme.textMuted),
            const SizedBox(height: 16),
            Text(msg,
                style: TextStyle(
                    color: isError ? EteraTheme.error : EteraTheme.textMuted),
                textAlign: TextAlign.center),
            if (isError && onRetry != null) ...[
              const SizedBox(height: 12),
              ElevatedButton(onPressed: onRetry, child: const Text('Retry')),
            ],
          ],
        ),
      ),
    );
  }
}

// ─── File card ────────────────────────────────────────────────────
class _FileCard extends StatelessWidget {
  final ProformaItem item;
  const _FileCard({required this.item});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: EteraCard(
        onTap: () => Navigator.pushNamed(
          context,
          '/garage-file-detail',
          arguments: item.id,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    '${item.brandName} ${item.model} ${item.year}',
                    style: const TextStyle(
                        fontWeight: FontWeight.w700, fontSize: 15),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                _StatusBadge(status: item.status),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                const Icon(Icons.confirmation_number_outlined,
                    size: 14, color: EteraTheme.textMuted),
                const SizedBox(width: 4),
                Text(item.fileNumber,
                    style: const TextStyle(
                        fontSize: 12, color: EteraTheme.textMuted)),
                const Spacer(),
                if (item.customerName.isNotEmpty) ...[
                  const Icon(Icons.person_outline,
                      size: 14, color: EteraTheme.textMuted),
                  const SizedBox(width: 4),
                  Text(item.customerName,
                      style: const TextStyle(
                          fontSize: 12, color: EteraTheme.textMuted),
                      overflow: TextOverflow.ellipsis),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color color;
    switch (status.toLowerCase()) {
      case 'published':
      case 'opened':
        color = Colors.blue;
        break;
      case 'pending':
        color = Colors.orange;
        break;
      case 'closed':
        color = EteraTheme.error;
        break;
      case 'completed':
        color = EteraTheme.green;
        break;
      default:
        color = EteraTheme.textMuted;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(status,
          style: TextStyle(
              fontSize: 11, color: color, fontWeight: FontWeight.w600)),
    );
  }
}
