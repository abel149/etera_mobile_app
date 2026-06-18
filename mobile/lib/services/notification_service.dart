import 'dart:convert';
import 'dart:io';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:shared_preferences/shared_preferences.dart';
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

  /// Cached user role — set after login/restore so navigation is role-aware.
  static String? _cachedRole;

  /// Call this after login or session restore to enable role-aware deep-linking.
  static void setUserRole(String? role) {
    _cachedRole = role;
  }

  // ─── Initialise ────────────────────────────────────────────────
  static Future<void> init() async {
    await _messaging.requestPermission(alert: true, badge: true, sound: true);

    // Init local notifications plugin
    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosSettings     = DarwinInitializationSettings();
    await _local.initialize(
      const InitializationSettings(android: androidSettings, iOS: iosSettings),
      onDidReceiveNotificationResponse: (details) {
        final payload = details.payload;
        if (payload != null) _navigateFromString(payload);
      },
    );

    // Android 8+ notification channel
    if (Platform.isAndroid) {
      await _local
          .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
          ?.createNotificationChannel(const AndroidNotificationChannel(
            _channelId, _channelName,
            importance: Importance.high,
            playSound: true,
          ));
    }

    // Foreground FCM → show local notification banner
    FirebaseMessaging.onMessage.listen(_onForegroundMessage);

    // Background tap (app in background when notification arrived)
    FirebaseMessaging.onMessageOpenedApp.listen((msg) => _handleTap(msg.data));

    // Terminated tap (app was killed when notification arrived)
    final initial = await _messaging.getInitialMessage();
    if (initial != null) {
      Future.delayed(const Duration(milliseconds: 600), () => _handleTap(initial.data));
    }
  }

  // ─── Register device token with backend ────────────────────────
  static Future<void> registerToken() async {
    try {
      final token = await _messaging.getToken().catchError((_) => null);
      if (token == null) return;

      await ApiService.post(
        ApiConfig.registerDeviceToken,
        {'device_token': token, 'platform': Platform.isIOS ? 'ios' : 'android'},
        withAuth: true,
      );

      _messaging.onTokenRefresh.listen((newToken) {
        ApiService.post(
          ApiConfig.registerDeviceToken,
          {'device_token': newToken, 'platform': Platform.isIOS ? 'ios' : 'android'},
          withAuth: true,
        );
      });

      // Cache the user role for role-aware notification routing
      try {
        final prefs = await SharedPreferences.getInstance();
        final raw = prefs.getString('user_data');
        if (raw != null) {
          final user = jsonDecode(raw) as Map<String, dynamic>;
          _cachedRole = user['role']?.toString();
        }
      } catch (_) {}
    } catch (e) {
      // Swallow — FCM unavailable in dev/emulator without Google Play
    }
  }

  // ─── Show local notification for foreground FCM ────────────────
  static Future<void> _onForegroundMessage(RemoteMessage message) async {
    final notification = message.notification;
    if (notification == null) return;

    final type       = message.data['type'] ?? '';
    final proformaId = message.data['proforma_id'] ?? '';
    final payload    = '$type:$proformaId';

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

  // ─── Central role-aware navigation logic ───────────────────────
  static void _navigate(String type, String proformaIdStr) {
    final nav = notificationNavigatorKey.currentState;
    if (nav == null) return;

    final int? proformaId = int.tryParse(proformaIdStr);
    final role = _cachedRole ?? '';
    final isAdmin = role == 'admin' || role == 'superadmin';

    // ── Admin / superadmin: all proforma notifications go to detail ──
    if (isAdmin && proformaId != null) {
      switch (type) {
        case 'new_proforma':
        case 'proforma_closed':
        case 'proforma_floated':
        case 'inbox_notification':
        case 'proforma_application':
        case 'proforma_application_received':
          nav.pushNamed('/admin-proforma-detail', arguments: proformaId);
          return;
        case 'approval_pending_signup':
        case 'approval_pending_login':
          nav.pushNamedAndRemoveUntil('/home', (r) => false);
          return;
        default:
          nav.pushNamed('/admin-proforma-detail', arguments: proformaId);
          return;
      }
    }

    if (isAdmin) {
      // Admin notification without a proforma_id (approvals, etc.)
      switch (type) {
        case 'approval_pending_signup':
        case 'approval_pending_login':
          nav.pushNamedAndRemoveUntil('/home', (r) => false);
          break;
        default:
          nav.pushNamed('/notifications');
      }
      return;
    }

    switch (type) {
      // ── Inbox / floated → applicant roles (shop or garage) ────────
      case 'new_proforma':
      case 'inbox_notification':
      case 'proforma_floated':
        if (proformaId != null) {
          if (role == 'garage') {
            nav.pushNamed('/garage-inbox-detail', arguments: proformaId);
          } else {
            nav.pushNamed('/shop-proforma-detail', arguments: proformaId);
          }
        } else {
          nav.pushNamed('/notifications');
        }
        break;

      // ── New application on active proforma → poster roles ────────
      case 'proforma_application':
      case 'proforma_application_received':
        if (proformaId != null) {
          switch (role) {
            case 'garage':
              nav.pushNamed('/garage-file-detail', arguments: proformaId);
              break;
            case 'insurance':
              nav.pushNamed('/insurance-proforma-detail', arguments: proformaId);
              break;
            case 'others':
              nav.pushNamed('/proforma-detail', arguments: proformaId);
              break;
            case 'business_owner':
            case 'employee':
            default:
              nav.pushNamed('/bo-proforma-detail', arguments: proformaId);
          }
        } else {
          nav.pushNamed('/notifications');
        }
        break;

      // ── Admin sent results to owner → received proforma detail ────
      case 'proforma_results_ready':
      case 'proforma_sent_to_owner':
        if (proformaId != null) {
          final String detailUrl;
          switch (role) {
            case 'garage':
              detailUrl = '${ApiConfig.baseUrl}/garage/my-files';
              break;
            case 'insurance':
              detailUrl = '${ApiConfig.baseUrl}/insurance/proformas';
              break;
            case 'others':
              detailUrl = '${ApiConfig.baseUrl}/others/proformas';
              break;
            default:
              detailUrl = '${ApiConfig.baseUrl}/business-owner/proformas';
          }
          nav.pushNamed('/received-proforma-detail', arguments: {
            'id': proformaId,
            'url': detailUrl,
          });
        } else {
          nav.pushNamed('/notifications');
        }
        break;

      // ── Admin → new user pending approval ─────────────────────────
      case 'approval_pending_signup':
      case 'approval_pending_login':
        nav.pushNamedAndRemoveUntil('/home', (r) => false);
        break;

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
