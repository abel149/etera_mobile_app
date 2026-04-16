class User {
  final int id;
  final String name;
  final String? email;
  final String phoneNumber;
  final String role;
  final String? storeId;
  final bool approved;
  final double balance;
  final String? location;
  final DateTime? createdAt;

  User({
    required this.id,
    required this.name,
    this.email,
    required this.phoneNumber,
    required this.role,
    this.storeId,
    required this.approved,
    required this.balance,
    this.location,
    this.createdAt,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String?,
      phoneNumber: json['phone_number'] as String,
      role: json['role'] as String,
      storeId: json['store_id'] as String?,
      approved: json['approved'] == true || json['approved'] == 1,
      balance: (json['balance'] ?? 0).toDouble(),
      location: json['location'] as String?,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'])
          : null,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'email': email,
        'phone_number': phoneNumber,
        'role': role,
        'store_id': storeId,
        'approved': approved,
        'balance': balance,
        'location': location,
        'created_at': createdAt?.toIso8601String(),
      };

  String get roleLabel {
    switch (role) {
      case 'individual':
        return 'Individual';
      case 'business_owner':
        return 'Business Owner';
      case 'garage':
        return 'Garage';
      case 'shop':
        return 'Spare Part Shop';
      case 'insurance':
        return 'Insurance';
      case 'employee':
        return 'Employee';
      case 'marketer':
        return 'Marketer';
      default:
        return role;
    }
  }
}
