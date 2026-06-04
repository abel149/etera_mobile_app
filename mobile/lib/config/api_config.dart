class ApiConfig {
  // Change this to your backend URL
  static const String baseUrl = 'https://etapp.usstandardgarage.com/api'; // Android emulator → host localhost
  // For iOS simulator use: 'http://127.0.0.1:8000/api'
  // For physical device use your machine's local IP: 'http://192.168.x.x:8000/api'

  // Auth
  static const String login = '$baseUrl/auth/login';
  static const String logout = '$baseUrl/auth/logout';
  static const String brands = '$baseUrl/brands';

  // Registration
  static const String register = '$baseUrl/register';
  static const String registerIndividual = '$baseUrl/register/individual';
  static const String registerBusinessOwner = '$baseUrl/register/business-owner';
  static const String registerGarageShop = '$baseUrl/register/garage-shop';

  // Proforma
  static const String createProforma = '$baseUrl/create-file';
}
