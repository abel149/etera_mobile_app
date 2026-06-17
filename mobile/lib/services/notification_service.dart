import 'dart:io';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'api_service.dart';
import '../config/api_config.dart';

/// Global navigator key — used to navigate from notification taps outside widget tree.
final GlobalKey<NavigatorState> notificationNavigatorKey = GlobalKey<NavigatorState>();

/// Handles FCM push notifications (foreground, background, and terminated state).
class NotificationService {
  static final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  static final FlutterLocalNotificationsPlugin _local =
      FlutterLocalNotificationsPlugin();

  static const _channelId   = 'etera_channel';
  static const _channelName = 'Etera Notifications';

  // ─── Initialise ────────────────────────────────────────────────
  static Future<void> init() async {
    await _messaging.requestPermission(alert: true, badge: true, sound: true);

    // Init local notifications plugin
    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosSettings     = DarwinInitializationSettings();
    await _local.initialize(
      const InitializationSettings(android: androidSettings, iOS: iosSettings),
      onDidReceiveNotificationResponse: (details) {
        // Local notification tapped (foreground)
        final payload = details.payload;
        if (payload != null) _navigateFromString(payload);
      },
    );

    // Android 8+ channel
    if (Platform.isAndroid) {
      await _local
          .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
          ?.createNotificationChannel(const AndroidNotificationChannel(
            _channelId, _channelName,
            importance: Importance.high,
            playSound: true,
          ));
    }

    // Foreground messages → show local notification
    FirebaseMessaging.onMessage.listen(_onForegroundMessage);

    // Background tap (app was in background when notification arrived)
    FirebaseMessaging.onMessageOpenedApp.listen((msg) => _handleTap(msg.data));

    // Terminated tap (app was killed when notification arrived)
    final initial = await _messaging.getInitialMessage();
    if (initial != null) {
      // Delay slightly so the widget tree is ready
      Future.delayed(const Duration(milliseconds: 600), () => _handleTap(initial.data));
    }
  }

  // ─── Register device token with backend ────────────────────────
  static Future<void> registerToken() async {
    try {
      final token = await _messaging.getToken().catchError((_) => null);
      if (token == null) {
        print('[NotificationService] FCM token is null');
        return;
      }
      print('[NotificationService] Registering FCM token: ${token.substring(0, 20)}...');
      final res = await ApiService.post(
        ApiConfig.registerDeviceToken,
        {'device_token': token, 'platform': Platform.isIOS ? 'ios' : 'android'},
        withAuth: true,
      );
      print('[NotificationService] Token registration result: $res');
      _messaging.onTokenRefresh.listen((newToken) {
        print('[NotificationService] Token refreshed, re-registering...');
        ApiService.post(
          ApiConfig.registerDeviceToken,
          {'device_token': newToken, 'platform': Platform.isIOS ? 'ios' : 'android'},
          withAuth: true,
        );
      });
    } catch (e) {
      print('[NotificationService] Token registration error: $e');
    }
  }

  // ─── Show local notification for foreground FCM ────────────────
  static Future<void> _onForegroundMessage(RemoteMessage message) async {
    final notification = message.notification;
    if (notification == null) return;

    // Build a payload string so local-notification tap can also navigate
    final type        = message.data['type'] ?? '';
    final proformaId  = message.data['proforma_id'] ?? '';
    final payload     = '$type:$proformaId';

    await _local.show(
      notification.hashCode,
      notification.title,
      notification.body,
      NotificationDetails(
        android: AndroidNotificationDetails(
          _channelId, _channelName,
          importance: Importance.high,
          priority: Priority.high,
          icon: '@mipmap/ic_launcher',
        ),
        iOS: const DarwinNotificationDetails(),
      ),
      payload: payload,
    );
  }

  // ─── Navigate from FCM data map ────────────────────────────────
  static void _handleTap(Map<String, dynamic> data) {
    final type       = data['type']?.toString() ?? '';
    final proformaId = data['proforma_id']?.toString() ?? '';
    _navigate(type, proformaId);
  }

  // ─── Navigate from local notification payload string ──────────
  static void _navigateFromString(String payload) {
    final parts      = payload.split(':');
    final type       = parts.isNotEmpty ? parts[0] : '';
    final proformaId = parts.length > 1 ? parts[1] : '';
    _navigate(type, proformaId);
  }

  // ─── Central navigation logic ──────────────────────────────────
  static void _navigate(String type, String proformaIdStr) {
    final nav = notificationNavigatorKey.currentState;
    if (nav == null) return;

    final int? proformaId = int.tryParse(proformaIdStr);

    switch (type) {
      // Shop / Garage → view the proforma they received
      case 'new_proforma':
      case 'inbox_notification':
      case 'proforma_floated':
        if (proformaId != null) {
          nav.pushNamed('/shop-proforma-detail', arguments: proformaId);
        } else {
          nav.pushNamed('/notifications');
        }
        break;

      // Poster → view their proforma detail (application received / results ready)
      case 'proforma_application':
      case 'proforma_application_received':
      case 'proforma_results_ready':
        if (proformaId != null) {
          nav.pushNamed('/bo-proforma-detail', arguments: proformaId);
        } else {
          nav.pushNamed('/notifications');
        }
        break;

      // Admin → proforma was closed; open admin detail to send to owner
      case 'proforma_closed':
        if (proformaId != null) {
          nav.pushNamed('/admin-proforma-detail', arguments: proformaId);
        } else {
          nav.pushNamed('/notifications');
        }
        break;

      // Admin → new user pending approval
      case 'approval_pending_signup':
      case 'approval_pending_login':
        nav.pushNamed('/admin-approvals');
        break;

      // Default → notifications list
      default:
        nav.pushNamed('/notifications');
    }
  }
}

/// Top-level background message handler — must be a top-level function.
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  // Firebase is already initialised in main.dart before this runs.
}
