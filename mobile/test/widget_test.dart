import 'package:flutter_test/flutter_test.dart';
import 'package:etera/main.dart';
import 'package:etera/providers/auth_provider.dart';

void main() {
  testWidgets('App launches and shows login screen', (WidgetTester tester) async {
    await tester.pumpWidget(
      EteraApp(auth: AuthProvider(), startRoute: '/login'),
    );
    await tester.pump();

    expect(find.text('Login'), findsWidgets);
  });
}
