import 'package:flutter/material.dart';

import '../../config/theme.dart';
import '../../services/auth_service.dart';
import '../../widgets/etera_button.dart';
import '../../widgets/etera_text_field.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _identifierCtrl = TextEditingController();
  final _otpCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();
  bool _codeSent = false;
  bool _loading = false;
  bool _obscure = true;

  @override
  void dispose() {
    _identifierCtrl.dispose();
    _otpCtrl.dispose();
    _passwordCtrl.dispose();
    _confirmCtrl.dispose();
    super.dispose();
  }

  Future<void> _requestCode() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _loading = true);
    final result = await AuthService.requestPasswordReset(
      _identifierCtrl.text.trim(),
    );

    if (!mounted) return;
    setState(() {
      _loading = false;
      if (result.success) _codeSent = true;
    });
    _showMessage(result.message, success: result.success);
  }

  Future<void> _resetPassword() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _loading = true);
    final result = await AuthService.resetPassword(
      identifier: _identifierCtrl.text.trim(),
      otp: _otpCtrl.text.trim(),
      password: _passwordCtrl.text,
      passwordConfirmation: _confirmCtrl.text,
    );

    if (!mounted) return;
    setState(() => _loading = false);

    if (result.success) {
      _showMessage(result.message, success: true);
      Navigator.pop(context);
      return;
    }

    _showMessage(result.message);
  }

  void _showMessage(String message, {bool success = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: success ? EteraTheme.green : EteraTheme.error,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Reset Password')),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 24),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    _codeSent ? 'Enter Reset Code' : 'Forgot Password',
                    style: Theme.of(context).textTheme.headlineSmall,
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    _codeSent
                        ? 'Use the SMS code sent to your account phone number.'
                        : 'Enter your phone number or store ID to receive a reset code.',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: EteraTheme.textMuted,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 32),
                  EteraTextField(
                    label: 'Phone Number or Store ID',
                    hint: '0940000000 or ES-0001',
                    controller: _identifierCtrl,
                    keyboardType: TextInputType.text,
                    readOnly: _codeSent,
                    validator: (v) {
                      if (v == null || v.trim().isEmpty) return 'Required';
                      return null;
                    },
                  ),
                  if (_codeSent) ...[
                    const SizedBox(height: 16),
                    EteraTextField(
                      label: 'Reset Code',
                      hint: '6 digit code',
                      controller: _otpCtrl,
                      keyboardType: TextInputType.number,
                      maxLength: 6,
                      validator: (v) {
                        if (v == null || v.trim().length != 6) {
                          return 'Enter the 6 digit code';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    EteraTextField(
                      label: 'New Password',
                      hint: '6 characters',
                      controller: _passwordCtrl,
                      obscureText: _obscure,
                      maxLength: 6,
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscure ? Icons.visibility_off : Icons.visibility,
                          color: EteraTheme.textMuted,
                          size: 20,
                        ),
                        onPressed: () => setState(() => _obscure = !_obscure),
                      ),
                      validator: (v) {
                        if (v == null || v.length != 6) {
                          return 'Password must be 6 characters';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    EteraTextField(
                      label: 'Confirm Password',
                      hint: 'Re-enter password',
                      controller: _confirmCtrl,
                      obscureText: _obscure,
                      maxLength: 6,
                      validator: (v) {
                        if (v != _passwordCtrl.text) {
                          return 'Passwords do not match';
                        }
                        return null;
                      },
                    ),
                  ],
                  const SizedBox(height: 28),
                  EteraButton(
                    label: _codeSent ? 'Reset Password' : 'Send Code',
                    loading: _loading,
                    onPressed: _codeSent ? _resetPassword : _requestCode,
                  ),
                  if (_codeSent) ...[
                    const SizedBox(height: 12),
                    TextButton(
                      onPressed: _loading
                          ? null
                          : () {
                              setState(() {
                                _codeSent = false;
                                _otpCtrl.clear();
                                _passwordCtrl.clear();
                                _confirmCtrl.clear();
                              });
                            },
                      child: const Text('Use a different phone or store ID'),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
