import 'package:flutter/material.dart';
import '../config/theme.dart';

/// Step indicator matching the web's `.bs-stepper` design.
class StepIndicator extends StatelessWidget {
  final int currentStep;
  final int totalSteps;
  final List<String> titles;

  const StepIndicator({
    super.key,
    required this.currentStep,
    required this.totalSteps,
    required this.titles,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: List.generate(totalSteps * 2 - 1, (index) {
        if (index.isOdd) {
          // Connector line
          final stepBefore = index ~/ 2;
          return Expanded(
            child: Container(
              height: 2,
              color: stepBefore < currentStep
                  ? EteraTheme.green
                  : EteraTheme.borderGreen,
            ),
          );
        }

        final step = index ~/ 2;
        final isActive = step == currentStep;
        final isCompleted = step < currentStep;

        return Column(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                gradient: isActive || isCompleted
                    ? EteraTheme.primaryGradient
                    : null,
                color: isActive || isCompleted ? null : const Color(0xFFF1F8E9),
                shape: BoxShape.circle,
                border: Border.all(
                  color: isActive || isCompleted
                      ? EteraTheme.green
                      : EteraTheme.borderGreen,
                  width: 2,
                ),
              ),
              child: Center(
                child: isCompleted
                    ? const Icon(Icons.check, color: Colors.white, size: 18)
                    : Text(
                        '${step + 1}',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: isActive ? Colors.white : const Color(0xFF555555),
                        ),
                      ),
              ),
            ),
            const SizedBox(height: 4),
            Text(
              titles[step],
              style: TextStyle(
                fontSize: 10,
                fontWeight: isActive ? FontWeight.w600 : FontWeight.w400,
                color: isActive ? EteraTheme.textPrimary : EteraTheme.textMuted,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        );
      }),
    );
  }
}
