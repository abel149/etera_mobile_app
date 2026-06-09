import 'package:flutter/material.dart';
import '../config/theme.dart';

/// Glassmorphism card matching the web's `.etera-glass-card`.
class EteraCard extends StatelessWidget {
  final Widget child;
  final EdgeInsets? padding;
  final EdgeInsets? margin;

  const EteraCard({
    super.key,
    required this.child,
    this.padding,
    this.margin,
  });

  @override
  Widget build(BuildContext context) {
    final radius = BorderRadius.circular(EteraTheme.radiusLg);
    return Container(
      margin: margin ?? const EdgeInsets.symmetric(vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.92),
        borderRadius: radius,
        border: Border.all(color: EteraTheme.borderGreen),
        boxShadow: EteraTheme.cardShadow,
      ),
      // ClipRRect clips ink ripples to the card shape.
      // Material(transparency) provides a proper ancestor for ListTile ink effects.
      child: ClipRRect(
        borderRadius: radius,
        child: Material(
          type: MaterialType.transparency,
          child: Padding(
            padding: padding ?? const EdgeInsets.all(20),
            child: child,
          ),
        ),
      ),
    );
  }
}
