import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_sound/flutter_sound.dart';
import 'package:image_picker/image_picker.dart';
import 'package:path_provider/path_provider.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/brand.dart';
import '../../models/user.dart';
import '../../providers/auth_provider.dart';
import '../../services/auth_service.dart';
import '../../services/insurance_service.dart';
import '../../widgets/etera_button.dart';
import '../../widgets/etera_card.dart';
import '../../widgets/etera_text_field.dart';
import '../../widgets/step_indicator.dart';

class InsuranceCreateProformaScreen extends StatefulWidget {
  const InsuranceCreateProformaScreen({super.key});

  @override
  State<InsuranceCreateProformaScreen> createState() => _InsuranceCreateProformaScreenState();
}

class _InsuranceCreateProformaScreenState extends State<InsuranceCreateProformaScreen> {
  int _step = 0;
  final _formKeys = List.generate(4, (_) => GlobalKey<FormState>());
  bool _loading = false;

  // Step 1 — Basic Info
  final _fileNumberCtrl = TextEditingController();
  bool _insured = false;
  String _carType = 'ICE';
  int? _brandId;
  final _modelCtrl = TextEditingController();
  String _year = '#N/A';
  String _proformaType = 'insurance_standard';
  int _numberOfShops = 3;
  int _numberOfGarages = 3;

  // Step 2 — Car Spec
  final _customerNameCtrl = TextEditingController();
  final _customerPhoneCtrl = TextEditingController();
  final _customerEmailCtrl = TextEditingController();
  final _plateCtrl = TextEditingController();
  final _chassisCtrl = TextEditingController();

  // Step 3 — Parts
  final List<_PartEntry> _parts = [_PartEntry()];
  List<User> _sparePartPartners = [];
  List<User> _garagePartners = [];
  Set<int> _selectedShopPartners = {};
  Set<int> _selectedGaragePartners = {};

  // Step 4 — Media
  List<File> _images = [];
  File? _video;
  File? _audio;
  String? _voiceNotePath;
  bool _isRecording = false;
  bool _recorderReady = false;
  final FlutterSoundRecorder _recorder = FlutterSoundRecorder();

  // Brands
  List<Brand> _brands = [];

  @override
  void initState() {
    super.initState();
    _loadData();
    _initRecorder();
  }

  Future<void> _initRecorder() async {
    final status = await Permission.microphone.request();
    if (status == PermissionStatus.granted) {
      await _recorder.openRecorder();
      if (mounted) setState(() => _recorderReady = true);
    }
  }

  Future<void> _loadData() async {
    final brands = await AuthService.fetchBrands();
    final partnersRes = await InsuranceService.getAvailablePartners();
    
    if (mounted) {
      setState(() {
        _brands = brands;
        if (brands.isNotEmpty) _brandId = brands.first.id;
        
        if (partnersRes['success'] == true) {
          final shops = partnersRes['data']['shops'] as List? ?? [];
          final garages = partnersRes['data']['garages'] as List? ?? [];
          _sparePartPartners = shops.map((e) => User.fromJson(e as Map<String, dynamic>)).toList();
          _garagePartners = garages.map((e) => User.fromJson(e as Map<String, dynamic>)).toList();
        }
      });
    }
  }

  List<String> get _years {
    final list = ['#N/A'];
    for (int y = DateTime.now().year; y >= 1990; y--) {
      list.add(y.toString());
    }
    return list;
  }

  void _nextStep() {
    if (_formKeys[_step].currentState?.validate() ?? false) {
      setState(() => _step++);
    }
  }

  void _prevStep() {
    setState(() => _step--);
  }

  Future<void> _pickImages() async {
    final picker = ImagePicker();
    final picked = await picker.pickMultiImage(
      imageQuality: 70,
      maxWidth: 1200,
    );
    if (picked != null) {
      setState(() => _images = picked.map((x) => File(x.path)).toList());
    }
  }

  Future<void> _pickVideo() async {
    final picker = ImagePicker();
    final picked = await picker.pickVideo(source: ImageSource.gallery);
    if (picked != null) {
      setState(() => _video = File(picked.path));
    }
  }

  Future<void> _pickAudio() async {
    final picker = ImagePicker();
    final picked = await picker.pickVideo(source: ImageSource.gallery);
    if (picked != null) {
      setState(() => _audio = File(picked.path));
    }
  }

  Future<void> _startRecording() async {
    if (!_recorderReady) {
      await _initRecorder();
      if (!_recorderReady) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Microphone permission denied')),
          );
        }
        return;
      }
    }
    try {
      final dir = await getTemporaryDirectory();
      final path = '${dir.path}/voice_${DateTime.now().millisecondsSinceEpoch}.aac';
      await _recorder.startRecorder(toFile: path, codec: Codec.aacADTS);
      setState(() => _isRecording = true);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Recording error: $e')),
        );
      }
    }
  }

  Future<void> _stopRecording() async {
    final path = await _recorder.stopRecorder();
    setState(() {
      _isRecording = false;
      _voiceNotePath = path;
    });
  }

  Future<void> _submit() async {
    setState(() => _loading = true);

    final body = {
      'file_number': _fileNumberCtrl.text.trim(),
      'insured': _insured ? 1 : 0,
      'car_type': _carType,
      'brand_id': _brandId,
      'model': _modelCtrl.text.trim(),
      'year': _year,
      'proforma_type': _proformaType,
      'number_of_spare_parts': _proformaType == 'insurance_garage_only' ? null : _numberOfShops,
      'number_of_garages': _proformaType == 'insurance_garage_only' ? _numberOfGarages : null,
      'customer_name': _customerNameCtrl.text.trim(),
      'customer_phone_number': _customerPhoneCtrl.text.trim(),
      'customer_email': _customerEmailCtrl.text.trim().isEmpty ? null : _customerEmailCtrl.text.trim(),
      'license_plate_number': _plateCtrl.text.trim(),
      'chassis_number': _chassisCtrl.text.trim().isEmpty ? null : _chassisCtrl.text.trim(),
      'parts': _parts.map((p) => {
        'name': p.nameCtrl.text.trim(),
        'number': p.numberCtrl.text.trim(),
        'grade': p.grade,
        'country': p.countryCtrl.text.trim(),
        'quantity': int.tryParse(p.quantityCtrl.text) ?? 1,
        'condition': p.condition,
        'component': p.component,
      }).toList(),
      'spare_part_partners': _selectedShopPartners.toList(),
      'garage_partners': _selectedGaragePartners.toList(),
    };

    if (_voiceNotePath != null) {
      final vFile = File(_voiceNotePath!);
      if (await vFile.exists()) {
        final bytes = await vFile.readAsBytes();
        body['voice_note'] = 'data:audio/aac;base64,${base64Encode(bytes)}';
      }
    }

    final res = await InsuranceService.createProforma(body);

    if (!mounted) return;
    setState(() => _loading = false);

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Proforma created successfully!'),
          backgroundColor: EteraTheme.green,
          behavior: SnackBarBehavior.floating,
        ),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Failed to create proforma'),
          backgroundColor: EteraTheme.error,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  void dispose() {
    _recorder.closeRecorder();
    _fileNumberCtrl.dispose();
    _modelCtrl.dispose();
    _customerNameCtrl.dispose();
    _customerPhoneCtrl.dispose();
    _customerEmailCtrl.dispose();
    _plateCtrl.dispose();
    _chassisCtrl.dispose();
    for (final p in _parts) {
      p.nameCtrl.dispose();
      p.numberCtrl.dispose();
      p.countryCtrl.dispose();
      p.quantityCtrl.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Request Proforma'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
              child: StepIndicator(
                currentStep: _step,
                totalSteps: 4,
                titles: const ['Basic', 'Car', 'Parts', 'Media'],
              ),
            ),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 32),
                child: [
                  _buildStep1(),
                  _buildStep2(),
                  _buildStep3(),
                  _buildStep4(),
                ][_step],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ─── Step 1: Basic Info ───────────────────────────────────────────
  Widget _buildStep1() {
    return Form(
      key: _formKeys[0],
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('Basic Information', style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 4),
          const Text('Enter the proforma details', style: TextStyle(color: EteraTheme.textMuted, fontSize: 13)),
          const SizedBox(height: 20),

          // File Number
          EteraTextField(
            label: 'File Number',
            hint: 'Enter file number',
            controller: _fileNumberCtrl,
          ),
          const SizedBox(height: 16),

          // Is Insured Checkbox
          Row(
            children: [
              Checkbox(
                value: _insured,
                onChanged: (v) => setState(() => _insured = v ?? false),
                activeColor: EteraTheme.green,
              ),
              const Text('Is Insured', style: TextStyle(fontWeight: FontWeight.w500)),
            ],
          ),
          const SizedBox(height: 16),

          // Car Type
          const Text('Car Type', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft)),
          const SizedBox(height: 6),
          DropdownButtonFormField<String>(
            initialValue: _carType,
            items: const [
              DropdownMenuItem(value: 'ICE', child: Text('ICE')),
              DropdownMenuItem(value: 'EV', child: Text('EV')),
              DropdownMenuItem(value: 'Hybrid', child: Text('Hybrid')),
              DropdownMenuItem(value: 'Others', child: Text('Others')),
            ],
            onChanged: (v) => setState(() => _carType = v!),
            decoration: const InputDecoration(),
          ),
          const SizedBox(height: 16),

          // Brand
          const Text('Brand', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft)),
          const SizedBox(height: 6),
          DropdownButtonFormField<int>(
            initialValue: _brandId,
            items: _brands
                .map((b) => DropdownMenuItem(value: b.id, child: Text(b.name)))
                .toList(),
            onChanged: (v) => setState(() => _brandId = v),
            decoration: const InputDecoration(),
            validator: (v) => v == null ? 'Required' : null,
          ),
          const SizedBox(height: 16),

          // Model
          EteraTextField(
            label: 'Model',
            hint: 'Example: Yaris',
            controller: _modelCtrl,
            validator: (v) => v == null || v.isEmpty ? 'Required' : null,
          ),
          const SizedBox(height: 16),

          // Year
          const Text('Year', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft)),
          const SizedBox(height: 6),
          DropdownButtonFormField<String>(
            initialValue: _year,
            items: _years.map((y) => DropdownMenuItem(value: y, child: Text(y))).toList(),
            onChanged: (v) => setState(() => _year = v!),
            decoration: const InputDecoration(),
          ),
          const SizedBox(height: 16),

          // Proforma Type
          const Text('Proforma Type', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft)),
          const SizedBox(height: 6),
          Column(
            children: [
              _ProformaTypeCard(
                label: 'Standard',
                subtitle: 'Shops + Garages',
                value: 'insurance_standard',
                selected: _proformaType == 'insurance_standard',
                icon: Icons.business,
                onTap: () => setState(() => _proformaType = 'insurance_standard'),
              ),
              const SizedBox(height: 8),
              _ProformaTypeCard(
                label: 'Shop Only',
                subtitle: 'Spare Part Shops',
                value: 'insurance_shop_only',
                selected: _proformaType == 'insurance_shop_only',
                icon: Icons.store,
                onTap: () => setState(() => _proformaType = 'insurance_shop_only'),
              ),
              const SizedBox(height: 8),
              _ProformaTypeCard(
                label: 'Garage Only',
                subtitle: 'Repair Garages',
                value: 'insurance_garage_only',
                selected: _proformaType == 'insurance_garage_only',
                icon: Icons.build,
                onTap: () => setState(() => _proformaType = 'insurance_garage_only'),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Number of Shops/Garages
          if (_proformaType != 'insurance_garage_only') ...[
            const Text('Number of Required Shops', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft)),
            const SizedBox(height: 6),
            DropdownButtonFormField<int>(
              initialValue: _numberOfShops,
              items: const [
                DropdownMenuItem(value: 1, child: Text('1 Shop')),
                DropdownMenuItem(value: 2, child: Text('2 Shops')),
                DropdownMenuItem(value: 3, child: Text('3 Shops')),
                DropdownMenuItem(value: 4, child: Text('4 Shops')),
                DropdownMenuItem(value: 5, child: Text('5 Shops')),
              ],
              onChanged: (v) => setState(() => _numberOfShops = v!),
              decoration: const InputDecoration(),
            ),
            const SizedBox(height: 16),
          ] else ...[
            const Text('Number of Required Garages', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft)),
            const SizedBox(height: 6),
            DropdownButtonFormField<int>(
              initialValue: _numberOfGarages,
              items: const [
                DropdownMenuItem(value: 1, child: Text('1 Garage')),
                DropdownMenuItem(value: 2, child: Text('2 Garages')),
                DropdownMenuItem(value: 3, child: Text('3 Garages')),
                DropdownMenuItem(value: 4, child: Text('4 Garages')),
                DropdownMenuItem(value: 5, child: Text('5 Garages')),
              ],
              onChanged: (v) => setState(() => _numberOfGarages = v!),
              decoration: const InputDecoration(),
            ),
            const SizedBox(height: 16),
          ],

          EteraButton(
            label: 'Next',
            icon: Icons.arrow_forward,
            onPressed: _nextStep,
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  // ─── Step 2: Car Spec ─────────────────────────────────────────────
  Widget _buildStep2() {
    return Form(
      key: _formKeys[1],
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('Car Information', style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 4),
          const Text('Enter the car details', style: TextStyle(color: EteraTheme.textMuted, fontSize: 13)),
          const SizedBox(height: 20),

          EteraTextField(
            label: 'Owner Name',
            hint: 'Customer Name',
            controller: _customerNameCtrl,
            validator: (v) => v == null || v.isEmpty ? 'Required' : null,
          ),
          const SizedBox(height: 16),

          EteraTextField(
            label: 'Phone Number',
            hint: '0900000000',
            controller: _customerPhoneCtrl,
            keyboardType: TextInputType.phone,
            validator: (v) => v == null || v.isEmpty ? 'Required' : null,
          ),
          const SizedBox(height: 16),

          EteraTextField(
            label: 'Email (Optional)',
            hint: 'customer@email.com',
            controller: _customerEmailCtrl,
            keyboardType: TextInputType.emailAddress,
          ),
          const SizedBox(height: 16),

          EteraTextField(
            label: 'License Plate Number',
            hint: 'Example: 3OR-B22662',
            controller: _plateCtrl,
            validator: (v) => v == null || v.isEmpty ? 'Required' : null,
          ),
          const SizedBox(height: 16),

          EteraTextField(
            label: 'Chassis Number (Optional)',
            hint: 'Enter Chassis Number',
            controller: _chassisCtrl,
            maxLength: 17,
          ),
          const SizedBox(height: 24),

          Row(
            children: [
              Expanded(
                child: EteraButton(
                  label: 'Previous',
                  icon: Icons.arrow_back,
                  isOutline: true,
                  onPressed: _prevStep,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: EteraButton(
                  label: 'Next',
                  icon: Icons.arrow_forward,
                  onPressed: _nextStep,
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  // ─── Step 3: Parts ────────────────────────────────────────────────
  Widget _buildStep3() {
    return Form(
      key: _formKeys[2],
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Spare Parts', style: Theme.of(context).textTheme.titleLarge),
                    const Text('Add the required spare parts', style: TextStyle(color: EteraTheme.textMuted, fontSize: 13)),
                  ],
                ),
              ),
              TextButton.icon(
                onPressed: () => setState(() => _parts.add(_PartEntry())),
                icon: const Icon(Icons.add, color: EteraTheme.green),
                label: const Text('Add', style: TextStyle(color: EteraTheme.green, fontWeight: FontWeight.w600)),
              ),
            ],
          ),
          const SizedBox(height: 12),

          ...List.generate(_parts.length, (i) => _buildPartCard(i)),
          const SizedBox(height: 24),

          // Partners
          if (_proformaType != 'insurance_garage_only') ...[
            const Text('Spare Part Shop Partners (Optional)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade300),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                children: _sparePartPartners.map((partner) {
                  final isSelected = _selectedShopPartners.contains(partner.id);
                  return CheckboxListTile(
                    title: Text(partner.name),
                    subtitle: Text(partner.phoneNumber),
                    value: isSelected,
                    onChanged: (v) {
                      setState(() {
                        if (v == true) {
                          _selectedShopPartners.add(partner.id);
                        } else {
                          _selectedShopPartners.remove(partner.id);
                        }
                      });
                    },
                    controlAffinity: ListTileControlAffinity.leading,
                    dense: true,
                  );
                }).toList(),
              ),
            ),
            const SizedBox(height: 16),
          ],

          if (_proformaType != 'insurance_shop_only') ...[
            const Text('Garage Partners (Optional)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade300),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                children: _garagePartners.map((partner) {
                  final isSelected = _selectedGaragePartners.contains(partner.id);
                  return CheckboxListTile(
                    title: Text(partner.name),
                    subtitle: Text(partner.phoneNumber),
                    value: isSelected,
                    onChanged: (v) {
                      setState(() {
                        if (v == true) {
                          _selectedGaragePartners.add(partner.id);
                        } else {
                          _selectedGaragePartners.remove(partner.id);
                        }
                      });
                    },
                    controlAffinity: ListTileControlAffinity.leading,
                    dense: true,
                  );
                }).toList(),
              ),
            ),
            const SizedBox(height: 16),
          ],

          Row(
            children: [
              Expanded(
                child: EteraButton(label: 'Previous', icon: Icons.arrow_back, isOutline: true, onPressed: _prevStep),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: EteraButton(label: 'Next', icon: Icons.arrow_forward, onPressed: _nextStep),
              ),
            ],
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildPartCard(int index) {
    final part = _parts[index];
    return EteraCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text('Part #${index + 1}', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
              const Spacer(),
              if (_parts.length > 1)
                IconButton(
                  icon: const Icon(Icons.delete_outline, color: EteraTheme.error, size: 20),
                  onPressed: () => setState(() => _parts.removeAt(index)),
                ),
            ],
          ),
          const SizedBox(height: 12),

          // Part Name
          TextFormField(
            controller: part.nameCtrl,
            decoration: const InputDecoration(labelText: 'Part Name'),
            validator: (v) => v == null || v.isEmpty ? 'Required' : null,
          ),
          const SizedBox(height: 12),

          // Part Number
          TextFormField(
            controller: part.numberCtrl,
            decoration: const InputDecoration(labelText: 'Part Number'),
          ),
          const SizedBox(height: 12),

          // Grade
          DropdownButtonFormField<String>(
            initialValue: part.grade,
            items: const [
              DropdownMenuItem(value: '1st Grade(Original OEM)', child: Text('1st Grade (Original OEM)')),
              DropdownMenuItem(value: '2nd Grade(After market)', child: Text('2nd Grade (After market)')),
              DropdownMenuItem(value: '3rd Grade', child: Text('3rd Grade')),
              DropdownMenuItem(value: '4th grade (Local)', child: Text('4th Grade (Local)')),
            ],
            onChanged: (v) => part.grade = v!,
            decoration: const InputDecoration(labelText: 'Grade'),
          ),
          const SizedBox(height: 12),

          // Country & Qty
          Row(
            children: [
              Expanded(
                flex: 2,
                child: TextFormField(
                  controller: part.countryCtrl,
                  decoration: const InputDecoration(labelText: 'Country'),
                  validator: (v) => v == null || v.isEmpty ? 'Required' : null,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: TextFormField(
                  controller: part.quantityCtrl,
                  decoration: const InputDecoration(labelText: 'Qty'),
                  keyboardType: TextInputType.number,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Condition
          DropdownButtonFormField<String>(
            initialValue: part.condition,
            items: const [
              DropdownMenuItem(value: 'New', child: Text('New')),
              DropdownMenuItem(value: 'Used', child: Text('Used')),
            ],
            onChanged: (v) => part.condition = v!,
            decoration: const InputDecoration(labelText: 'Condition'),
          ),
          const SizedBox(height: 12),

          // Component
          DropdownButtonFormField<String>(
            initialValue: part.component.isNotEmpty ? part.component : null,
            items: const [
              DropdownMenuItem(value: 'Body Parts', child: Text('Body Parts')),
              DropdownMenuItem(value: 'Mechanical Parts', child: Text('Mechanical Parts')),
            ],
            onChanged: (v) => part.component = v!,
            decoration: const InputDecoration(labelText: 'Component'),
          ),
        ],
      ),
    );
  }

  // ─── Step 4: Media ────────────────────────────────────────────────
  Widget _buildStep4() {
    return Form(
      key: _formKeys[3],
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('Information for Garage (Optional)', style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 4),
          const Text('Upload car information media files', style: TextStyle(color: EteraTheme.textMuted, fontSize: 13)),
          const SizedBox(height: 20),

          // Images
          EteraCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Images', style: TextStyle(fontWeight: FontWeight.w600)),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    ..._images.asMap().entries.map((e) => Stack(
                          clipBehavior: Clip.none,
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Image.file(e.value, width: 72, height: 72, fit: BoxFit.cover),
                            ),
                            Positioned(
                              top: -6,
                              right: -6,
                              child: GestureDetector(
                                onTap: () => setState(() => _images.removeAt(e.key)),
                                child: Container(
                                  width: 22,
                                  height: 22,
                                  decoration: const BoxDecoration(color: EteraTheme.error, shape: BoxShape.circle),
                                  child: const Icon(Icons.close, size: 13, color: Colors.white),
                                ),
                              ),
                            ),
                          ],
                        )),
                    GestureDetector(
                      onTap: _pickImages,
                      child: Container(
                        width: 72,
                        height: 72,
                        decoration: BoxDecoration(
                          color: const Color(0xFFF1F8E9),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: EteraTheme.borderGreen),
                        ),
                        child: const Icon(Icons.add_photo_alternate_outlined, color: EteraTheme.green, size: 28),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Video
          EteraCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Video', style: TextStyle(fontWeight: FontWeight.w600)),
                const SizedBox(height: 8),
                if (_video != null)
                  Row(
                    children: [
                      const Icon(Icons.video_file, color: EteraTheme.green),
                      const SizedBox(width: 8),
                      Expanded(child: Text(_video!.path.split('/').last)),
                      IconButton(
                        icon: const Icon(Icons.delete_outline, color: EteraTheme.error),
                        onPressed: () => setState(() => _video = null),
                      ),
                    ],
                  )
                else
                  TextButton.icon(
                    onPressed: _pickVideo,
                    icon: const Icon(Icons.upload_file, color: EteraTheme.green),
                    label: const Text('Upload Video'),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Audio
          EteraCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Audio', style: TextStyle(fontWeight: FontWeight.w600)),
                const SizedBox(height: 8),
                if (_audio != null)
                  Row(
                    children: [
                      const Icon(Icons.audio_file, color: EteraTheme.green),
                      const SizedBox(width: 8),
                      Expanded(child: Text(_audio!.path.split('/').last)),
                      IconButton(
                        icon: const Icon(Icons.delete_outline, color: EteraTheme.error),
                        onPressed: () => setState(() => _audio = null),
                      ),
                    ],
                  )
                else
                  TextButton.icon(
                    onPressed: _pickAudio,
                    icon: const Icon(Icons.upload_file, color: EteraTheme.green),
                    label: const Text('Upload Audio'),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Voice Note
          EteraCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Voice Note (Optional)', style: TextStyle(fontWeight: FontWeight.w600)),
                const SizedBox(height: 4),
                const Text('Record a voice description for the garage', style: TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                const SizedBox(height: 12),
                if (_isRecording)
                  Row(
                    children: [
                      Container(
                        width: 10,
                        height: 10,
                        decoration: const BoxDecoration(color: Colors.red, shape: BoxShape.circle),
                      ),
                      const SizedBox(width: 8),
                      const Expanded(child: Text('Recording in progress...', style: TextStyle(color: Colors.red, fontWeight: FontWeight.w500))),
                      ElevatedButton.icon(
                        onPressed: _stopRecording,
                        icon: const Icon(Icons.stop, size: 18),
                        label: const Text('Stop'),
                        style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
                      ),
                    ],
                  )
                else if (_voiceNotePath != null)
                  Row(
                    children: [
                      const Icon(Icons.check_circle, color: EteraTheme.green),
                      const SizedBox(width: 8),
                      const Expanded(child: Text('Voice note recorded', style: TextStyle(color: EteraTheme.green, fontWeight: FontWeight.w500))),
                      TextButton.icon(
                        onPressed: () => setState(() => _voiceNotePath = null),
                        icon: const Icon(Icons.delete_outline, color: EteraTheme.error, size: 18),
                        label: const Text('Delete', style: TextStyle(color: EteraTheme.error)),
                      ),
                    ],
                  )
                else
                  TextButton.icon(
                    onPressed: _startRecording,
                    icon: const Icon(Icons.mic, color: EteraTheme.green),
                    label: const Text('Start Recording', style: TextStyle(color: EteraTheme.green)),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          Row(
            children: [
              Expanded(
                child: EteraButton(label: 'Previous', icon: Icons.arrow_back, isOutline: true, onPressed: _prevStep),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: EteraButton(label: 'Submit', icon: Icons.send, loading: _loading, onPressed: _submit),
              ),
            ],
          ),
          const SizedBox(height: 32),
        ],
      ),
    );
  }
}

// ─── Proforma Type Card ─────────────────────────────────────────────
class _ProformaTypeCard extends StatelessWidget {
  final String label;
  final String subtitle;
  final String value;
  final bool selected;
  final IconData icon;
  final VoidCallback onTap;

  const _ProformaTypeCard({
    required this.label,
    required this.subtitle,
    required this.value,
    required this.selected,
    required this.icon,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: selected
              ? EteraTheme.green.withValues(alpha: 0.1)
              : Colors.grey.shade50,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: selected ? EteraTheme.green : Colors.grey.shade300,
            width: selected ? 2 : 1,
          ),
        ),
        child: Row(
          children: [
            Icon(icon, color: selected ? EteraTheme.green : Colors.grey.shade600),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label, style: TextStyle(fontWeight: FontWeight.w700, color: selected ? EteraTheme.green : Colors.grey.shade800)),
                  Text(subtitle, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
                ],
              ),
            ),
            if (selected)
              const Icon(Icons.check_circle, color: EteraTheme.green),
          ],
        ),
      ),
    );
  }
}

// ─── Part entry helper ──────────────────────────────────────────────
class _PartEntry {
  String condition = 'New';
  final TextEditingController nameCtrl = TextEditingController();
  final TextEditingController numberCtrl = TextEditingController();
  String grade = '1st Grade(Original OEM)';
  final TextEditingController countryCtrl = TextEditingController();
  final TextEditingController quantityCtrl = TextEditingController(text: '1');
  String component = '';
}
