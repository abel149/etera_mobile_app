import 'package:flutter_test/flutter_test.dart';
import 'package:etera_mobile/main.dart';

void main() {
  testWidgets('App launches and shows splash screen', (WidgetTester tester) async {
    await tester.pumpWidget(const EteraApp());

    // Splash screen should show the E-Tera branding
    expect(find.text('E-Tera'), findsOneWidget);
    expect(find.text('Auto Parts Sourcing'), findsOneWidget);
  });
}
