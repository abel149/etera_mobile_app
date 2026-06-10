import 'dart:io';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'api_service.dart';
import '../config/api_config.dart';

/// Handles FCM push notifications (foreground, background, and terminated state).
class NotificationService {
  static final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  static final FlutterLocalNotificationsPlugin _local =
      FlutterLocalNotificationsPlugin();

  static const _channelId = 'etera_channel';
  static const _channelName = 'Etera Notifications';

  // ─── Initialise ───────────────────────────────────────────────
  static Future<void> init() async {
    // Request permission (iOS + Android 13+)
    await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    // Init local notifications
    const androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosSettings = DarwinInitializationSettings();
    await _local.initialize(
      const InitializationSettings(
          android: androidSettings, iOS: iosSettings),
    );

    // Create notification channel (Android 8+)
    if (Platform.isAndroid) {
      await _local
          .resolvePlatformSpecificImplementation<
              AndroidFlutterLocalNotificationsPlugin>()
          ?.createNotificationChannel(
            const AndroidNotificationChannel(
              _channelId,
              _channelName,
              importance: Importance.high,
              playSound: true,
            ),
          );
    }

    // Listen to foreground messages
    FirebaseMessaging.onMessage.listen(_onForegroundMessage);

    // Background message handler is top-level (see main.dart)
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
      // Re-register when token refreshes
      _messaging.onTokenRefresh.listen((newToken) {
        ApiService.post(
          ApiConfig.registerDeviceToken,
          {'device_token': newToken, 'platform': Platform.isIOS ? 'ios' : 'android'},
          withAuth: true,
        );
      });
    } catch (_) {}
  }

  // ─── Show local notification for foreground messages ──────────
  static Future<void> _onForegroundMessage(RemoteMessage message) async {
    final notification = message.notification;
    if (notification == null) return;
    await _local.show(
      notification.hashCode,
      notification.title,
      notification.body,
      NotificationDetails(
        android: AndroidNotificationDetails(
          _channelId,
          _channelName,
          importance: Importance.high,
          priority: Priority.high,
          icon: '@mipmap/ic_launcher',
        ),
        iOS: const DarwinNotificationDetails(),
      ),
    );
  }
}

/// Top-level background message handler (must be top-level function)
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  // Firebase is already initialised in main.dart before this runs
}
