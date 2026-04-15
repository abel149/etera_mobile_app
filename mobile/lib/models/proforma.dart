class ProformaPart {
  String condition;
  String number;
  String grade;
  String country;
  int quantity;
  String component;
  List<String> photoPaths; // local file paths for upload

  ProformaPart({
    this.condition = 'New',
    this.number = '',
    this.grade = '1st grade (Original OEM)',
    this.country = '',
    this.quantity = 1,
    this.component = '',
    List<String>? photoPaths,
  }) : photoPaths = photoPaths ?? [];
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
