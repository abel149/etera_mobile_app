// ─── API response models ────────────────────────────────────────────

class ProformaApplicant {
  final String name;
  final String? phone;
  final String? storeId;
  final String? tinNumber;
  final String? location;
  final String? stampImageUrl;

  ProformaApplicant({
    required this.name,
    this.phone,
    this.storeId,
    this.tinNumber,
    this.location,
    this.stampImageUrl,
  });

  factory ProformaApplicant.fromJson(Map<String, dynamic> json) {
    return ProformaApplicant(
      name: json['name'] ?? 'Unknown',
      phone: json['phone'],
      storeId: json['store_id'],
      tinNumber: json['tin_number'],
      location: json['location'],
      stampImageUrl: json['stamp_image_url'],
    );
  }
}

class PartPricing {
  final int carPartId;
  final double unitPrice;
  final double partTotal;

  PartPricing({
    required this.carPartId,
    required this.unitPrice,
    required this.partTotal,
  });

  factory PartPricing.fromJson(Map<String, dynamic> json) {
    final carPartIdRaw = json['car_part_id'];
    int carPartId = 0;
    if (carPartIdRaw is int) {
      carPartId = carPartIdRaw;
    } else if (carPartIdRaw is String) {
      carPartId = int.tryParse(carPartIdRaw) ?? 0;
    }
    return PartPricing(
      carPartId: carPartId,
      unitPrice: (json['unit_price'] as num?)?.toDouble() ?? 0.0,
      partTotal: (json['part_total'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

class ProformaApplication {
  final int id;
  final String from; // 'shop' or 'garage'
  final ProformaApplicant applicant;
  final double subtotal;
  final double discountPct;
  final double discountAmount;
  final double netTotal;
  final List<PartPricing> partsPricing;

  ProformaApplication({
    required this.id,
    required this.from,
    required this.applicant,
    required this.subtotal,
    required this.discountPct,
    required this.discountAmount,
    required this.netTotal,
    List<PartPricing>? partsPricing,
  }) : partsPricing = partsPricing ?? [];

  factory ProformaApplication.fromJson(Map<String, dynamic> json) {
    final applicantRaw = json['applicant'] as Map<String, dynamic>? ?? {};
    final pricingRaw = json['parts_pricing'] as List? ?? [];
    return ProformaApplication(
      id: json['id'] ?? 0,
      from: json['from'] ?? 'shop',
      applicant: ProformaApplicant.fromJson(applicantRaw),
      subtotal: (json['subtotal'] as num?)?.toDouble() ?? 0.0,
      discountPct: (json['discount_pct'] as num?)?.toDouble() ?? 0.0,
      discountAmount: (json['discount_amount'] as num?)?.toDouble() ?? 0.0,
      netTotal: (json['net_total'] as num?)?.toDouble() ?? 0.0,
      partsPricing: pricingRaw.map((p) => PartPricing.fromJson(p as Map<String, dynamic>)).toList(),
    );
  }
}

class ProformaPartItem {
  final int id;
  final String condition;
  final String number;
  final String grade;
  final String country;
  final int quantity;
  final String component;
  final List<String> photos;

  ProformaPartItem({
    required this.id,
    required this.condition,
    required this.number,
    required this.grade,
    required this.country,
    required this.quantity,
    required this.component,
    required this.photos,
  });

  factory ProformaPartItem.fromJson(Map<String, dynamic> json) {
    return ProformaPartItem(
      id: json['id'] ?? 0,
      condition: json['condition'] ?? '',
      number: json['number'] ?? json['part_number'] ?? '',
      grade: json['grade'] ?? '',
      country: json['country'] ?? '',
      quantity: json['quantity'] ?? 1,
      component: json['component'] ?? '',
      photos: (json['photos'] as List?)?.map((e) => e.toString()).toList() ?? [],
    );
  }
}

class ProformaItem {
  final int id;
  final String fileNumber;
  final String status;
  final int numberOfProformas;
  final String carType;
  final String brandName;
  final String model;
  final String year;
  final String customerName;
  final String customerPhone;
  final String licensePlate;
  final String? chassisNumber;
  final List<ProformaPartItem> parts;
  final List<ProformaApplication> shops;
  final List<ProformaApplication> garages;
  final String createdAt;
  final bool closeRequest;
  final bool canRequestClose;
  final Map<String, dynamic>? invoice;

  ProformaItem({
    required this.id,
    required this.fileNumber,
    required this.status,
    required this.numberOfProformas,
    required this.carType,
    required this.brandName,
    required this.model,
    required this.year,
    required this.customerName,
    required this.customerPhone,
    required this.licensePlate,
    this.chassisNumber,
    required this.parts,
    List<ProformaApplication>? shops,
    List<ProformaApplication>? garages,
    required this.createdAt,
    this.closeRequest = false,
    this.canRequestClose = false,
    this.invoice,
  })  : shops = shops ?? [],
        garages = garages ?? [];

  factory ProformaItem.fromJson(Map<String, dynamic> json) {
    // brand can be a string (ProformaResource), a Map {name:...}, or null
    final brandRaw = json['brand'];
    final String brandName;
    if (brandRaw is Map) {
      brandName = (brandRaw['name'] ?? '').toString();
    } else if (brandRaw is String) {
      brandName = brandRaw;
    } else {
      brandName = (json['brand_name'] ?? '').toString();
    }

    final partsRaw = json['parts'] as List? ?? [];
    final parts = partsRaw
        .map((p) => ProformaPartItem.fromJson(p as Map<String, dynamic>))
        .toList();

    final shopsRaw = json['shops'] as List? ?? [];
    final shops = shopsRaw
        .map((s) => ProformaApplication.fromJson(s as Map<String, dynamic>))
        .toList();

    final garagesRaw = json['garages'] as List? ?? [];
    final garages = garagesRaw
        .map((g) => ProformaApplication.fromJson(g as Map<String, dynamic>))
        .toList();

    // required_shops is '∞' for Etera-Chereta (-1), otherwise int
    int numberOfProformas = 1;
    final rawReq = json['number_of_proformas'] ?? json['required_shops'];
    if (rawReq != null) {
      if (rawReq == '∞' || rawReq == -1) {
        numberOfProformas = -1;
      } else {
        numberOfProformas = int.tryParse(rawReq.toString()) ?? 1;
      }
    }

    return ProformaItem(
      id: json['id'] ?? 0,
      fileNumber: json['file_number'] ?? '',
      status: json['status'] ?? 'pending',
      numberOfProformas: numberOfProformas,
      carType: json['car_type'] ?? '',
      brandName: brandName,
      model: json['model'] ?? '',
      year: json['year'] ?? '',
      customerName: json['customer_name'] ?? '',
      customerPhone: json['customer_phone'] ?? json['customer_phone_number'] ?? '',
      licensePlate: json['license_plate'] ?? json['license_plate_number'] ?? '',
      chassisNumber: json['chassis_number'],
      parts: parts,
      shops: shops,
      garages: garages,
      createdAt: json['created_at'] ?? '',
      closeRequest: json['close_request'] == true,
      canRequestClose: json['can_request_close'] == true,
      invoice: json['invoice'] as Map<String, dynamic>?,
    );
  }

  String get shortDate {
    if (createdAt.isEmpty) return '';
    try {
      final dt = DateTime.parse(createdAt).toLocal();
      return '${dt.day}/${dt.month}/${dt.year}';
    } catch (_) {
      return createdAt.substring(0, 10);
    }
  }
}

// ─── Upload / request models ────────────────────────────────────────

class ProformaPart {
  String condition;
  String name; // part display name (required by API)
  String number; // part number / code
  String grade;
  String country;
  int quantity;
  String component;
  List<String> photoPaths; // local file paths for upload
  List<String> tempPhotoPaths; // uploaded temp folder names for API

  ProformaPart({
    this.condition = 'New',
    this.name = '',
    this.number = '',
    this.grade = '1st grade (Original OEM)',
    this.country = '',
    this.quantity = 1,
    this.component = '',
    List<String>? photoPaths,
    List<String>? tempPhotoPaths,
  })  : photoPaths = photoPaths ?? [],
        tempPhotoPaths = tempPhotoPaths ?? [];
}

class ProformaRequest {
  int numberOfProformas;
  int? eteraCheretaHours;
  int brandId;
  String carType;
  String model;
  String year;
  String customerPhoneNumber;
  String licensePlateNumber;
  String? chassisNumber;
  List<ProformaPart> parts;
  String? voiceNotePath;

  ProformaRequest({
    this.numberOfProformas = 1,
    this.eteraCheretaHours,
    required this.brandId,
    this.carType = 'ICE',
    required this.model,
    required this.year,
    required this.customerPhoneNumber,
    required this.licensePlateNumber,
    this.chassisNumber,
    required this.parts,
    this.voiceNotePath,
  });

  bool get isEteraChereta => numberOfProformas == -1;
}
