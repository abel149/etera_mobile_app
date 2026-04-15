import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';
import '../services/auth_service.dart';

class AuthProvider extends ChangeNotifier {
  User? _user;
  bool _loading = false;
  String? _error;

  User? get user => _user;
  bool get loading => _loading;
  bool get isLoggedIn => _user != null;
  String? get error => _error;

  // ─── Restore session ──────────────────────────────────────
  Future<bool> tryRestoreSession() async {
    final prefs = await SharedPreferences.getInstance();
    final json = prefs.getString('user_data');
    if (json != null) {
      try {
        _user = User.fromJson(jsonDecode(json));
        notifyListeners();
        return true;
      } catch (_) {
        await prefs.remove('user_data');
      }
    }
    return false;
  }

  // ─── Login ────────────────────────────────────────────────
  Future<AuthResult> login(String phoneNumber, String password) async {
    _loading = true;
    _error = null;
    notifyListeners();

    final result = await AuthService.login(phoneNumber, password);

    if (result.success && result.user != null) {
      _user = result.user;
      await _persistUser(result.user!);
    } else {
      _error = result.message;
    }

    _loading = false;
    notifyListeners();
    return result;
  }

  // ─── Logout ───────────────────────────────────────────────
  Future<void> logout() async {
    await AuthService.logout();
    _user = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('user_data');
    notifyListeners();
  }

  // ─── Set user after registration ──────────────────────────
  void setUser(User user) {
    _user = user;
    _persistUser(user);
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }

  Future<void> _persistUser(User user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('user_data', jsonEncode(user.toJson()));
  }
}
