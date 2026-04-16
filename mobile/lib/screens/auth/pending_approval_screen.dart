import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/etera_button.dart';

class PendingApprovalScreen extends StatelessWidget {
  const PendingApprovalScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Colors.white, Color(0xFFF9FAFB)],
          ),
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Icon
                Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    color: EteraTheme.green.withValues(alpha: 0.1),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.hourglass_top_rounded,
                    color: EteraTheme.green,
                    size: 48,
                  ),
                ),
                const SizedBox(height: 32),

                Text(
                  'Pending Approval',
                  style: Theme.of(context).textTheme.headlineMedium,
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 12),

                Text(
                  'Your account has been registered successfully and is now awaiting admin approval.\n\nYou will be able to log in once your account is approved.',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: EteraTheme.textMuted,
                        height: 1.6,
                      ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 40),

                // Info banner
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: EteraTheme.green.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: EteraTheme.green.withValues(alpha: 0.2)),
                  ),
                  child: const Row(
                    children: [
                      Icon(Icons.info_outline, color: EteraTheme.green, size: 20),
                      SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          'An admin will review your registration shortly. This usually takes 1-2 business days.',
                          style: TextStyle(
                            fontSize: 13,
                            color: EteraTheme.textSoft,
                            height: 1.4,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 40),

                // Back to login
                SizedBox(
                  width: double.infinity,
                  child: EteraButton(
                    label: 'Back to Login',
                    isOutline: true,
                    icon: Icons.arrow_back,
                    onPressed: () {
                      context.read<AuthProvider>().logout();
                      Navigator.pushNamedAndRemoveUntil(
                        context,
                        '/login',
                        (r) => false,
                      );
                    },
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
