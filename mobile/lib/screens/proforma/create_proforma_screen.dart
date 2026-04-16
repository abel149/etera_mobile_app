import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../config/theme.dart';
import '../../models/brand.dart';
import '../../models/proforma.dart';
import '../../services/auth_service.dart';
import '../../services/proforma_service.dart';
import '../../widgets/etera_button.dart';
import '../../widgets/etera_card.dart';
import '../../widgets/etera_text_field.dart';
import '../../widgets/step_indicator.dart';

class CreateProformaScreen extends StatefulWidget {
  const CreateProformaScreen({super.key});

  @override
  State<CreateProformaScreen> createState() => _CreateProformaScreenState();
}

class _CreateProformaScreenState extends State<CreateProformaScreen> {
  int _step = 0;
  final _formKeys = List.generate(4, (_) => GlobalKey<FormState>());
  bool _loading = false;

  // Step 1 — Basic Info
  int _numberOfProformas = 1;
  int _eteraHours = 24;
  String _carType = 'ICE';
  int? _brandId;
  final _modelCtrl = TextEditingController();
  String _year = '#N/A';

  // Step 2 — Car Spec
  final _phoneCtrl = TextEditingController();
  final _plateCtrl = TextEditingController();
  final _chassisCtrl = TextEditingController();

  // Step 3 — Parts
  final List<_PartEntry> _parts = [_PartEntry()];

  // Brands
  List<Brand> _brands = [];

  @override
  void initState() {
    super.initState();
    _loadBrands();
  }

  Future<void> _loadBrands() async {
    final brands = await AuthService.fetchBrands();
    if (mounted) {
      setState(() {
        _brands = brands;
        if (brands.isNotEmpty) _brandId = brands.first.id;
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

  Future<void> _submit() async {
    setState(() => _loading = true);

    final req = ProformaRequest(
      numberOfProformas: _numberOfProformas,
      eteraCheretaHours: _numberOfProformas == -1 ? _eteraHours : null,
      brandId: _brandId!,
      carType: _carType,
      model: _modelCtrl.text.trim(),
      year: _year,
      customerPhoneNumber: _phoneCtrl.text.trim(),
      licensePlateNumber: _plateCtrl.text.trim(),
      chassisNumber: _chassisCtrl.text.trim().isNotEmpty ? _chassisCtrl.text.trim() : null,
      parts: _parts
          .map((p) => ProformaPart(
                condition: p.condition,
                number: p.numberCtrl.text.trim(),
                grade: p.grade,
                country: p.countryCtrl.text.trim(),
                quantity: int.tryParse(p.quantityCtrl.text) ?? 1,
                component: p.component,
                photoPaths: p.images.map((f) => f.path).toList(),
              ))
          .toList(),
    );

    final result = await ProformaService.createProforma(req);

    if (!mounted) return;
    setState(() => _loading = false);

    if (result['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Proforma submitted successfully!'),
          backgroundColor: EteraTheme.green,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Failed'),
          backgroundColor: EteraTheme.error,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
    }
  }

  @override
  void dispose() {
    _modelCtrl.dispose();
    _phoneCtrl.dispose();
    _plateCtrl.dispose();
    _chassisCtrl.dispose();
    for (final p in _parts) {
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
            // Step indicator
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
              child: StepIndicator(
                currentStep: _step,
                totalSteps: 4,
                titles: const ['Basic', 'Car', 'Parts', 'Submit'],
              ),
            ),

            // Step content
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24),
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

          // Number of proformas
          const Text('Number of Proforma Invoices', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft)),
          const SizedBox(height: 6),
          DropdownButtonFormField<int>(
            initialValue: _numberOfProformas,
            items: const [
              DropdownMenuItem(value: 1, child: Text('1 Proforma')),
              DropdownMenuItem(value: 2, child: Text('2 Proformas')),
              DropdownMenuItem(value: 3, child: Text('3 Proformas')),
              DropdownMenuItem(value: -1, child: Text('Unlimited (Etera Chereta)')),
            ],
            onChanged: (v) => setState(() => _numberOfProformas = v!),
            decoration: const InputDecoration(),
          ),
          const SizedBox(height: 16),

          // Timer for Etera Chereta
          if (_numberOfProformas == -1) ...[
            const Text('Timer Duration', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft)),
            const SizedBox(height: 6),
            DropdownButtonFormField<int>(
              initialValue: _eteraHours,
              items: const [
                DropdownMenuItem(value: 4, child: Text('4 hours')),
                DropdownMenuItem(value: 8, child: Text('8 hours')),
                DropdownMenuItem(value: 12, child: Text('12 hours')),
                DropdownMenuItem(value: 24, child: Text('24 hours')),
                DropdownMenuItem(value: 48, child: Text('48 hours')),
                DropdownMenuItem(value: 72, child: Text('72 hours')),
              ],
              onChanged: (v) => setState(() => _eteraHours = v!),
              decoration: const InputDecoration(),
            ),
            const SizedBox(height: 16),
          ],

          // Car type
          const Text('Car Type', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft)),
          const SizedBox(height: 6),
          DropdownButtonFormField<String>(
            initialValue: _carType,
            items: const [
              DropdownMenuItem(value: 'ICE', child: Text('ICE (Gas)')),
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
          const SizedBox(height: 24),

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
            label: 'Phone Number',
            hint: '0900000000',
            controller: _phoneCtrl,
            keyboardType: TextInputType.phone,
            validator: (v) => v == null || v.isEmpty ? 'Required' : null,
          ),
          const SizedBox(height: 16),

          EteraTextField(
            label: 'License Plate Number',
            hint: '2AA-12345',
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

          // Condition
          DropdownButtonFormField<String>(
            initialValue: part.condition,
            items: const [DropdownMenuItem(value: 'New', child: Text('New'))],
            onChanged: (v) => part.condition = v!,
            decoration: const InputDecoration(labelText: 'Condition'),
          ),
          const SizedBox(height: 12),

          // Part name/number
          TextFormField(
            controller: part.numberCtrl,
            decoration: const InputDecoration(labelText: 'Part Name (Part Number)', hintText: 'e.g: Boost Sensor (008-900734)'),
            validator: (v) => v == null || v.isEmpty ? 'Required' : null,
          ),
          const SizedBox(height: 12),

          // Grade
          DropdownButtonFormField<String>(
            initialValue: part.grade,
            items: const [
              DropdownMenuItem(value: '1st grade (Original OEM)', child: Text('1st grade (OEM)')),
              DropdownMenuItem(value: '2nd grade (After market)', child: Text('2nd grade (Aftermarket)')),
              DropdownMenuItem(value: '3rd grade', child: Text('3rd grade')),
              DropdownMenuItem(value: '4th grade (Local)', child: Text('4th grade (Local)')),
            ],
            onChanged: (v) => part.grade = v!,
            decoration: const InputDecoration(labelText: 'Parts Grade'),
          ),
          const SizedBox(height: 12),

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

          // Component
          DropdownButtonFormField<String>(
            initialValue: part.component.isNotEmpty ? part.component : null,
            items: const [
              DropdownMenuItem(value: 'Body Parts', child: Text('Body Parts')),
              DropdownMenuItem(value: 'Mechanical Parts', child: Text('Mechanical Parts')),
            ],
            onChanged: (v) => part.component = v!,
            decoration: const InputDecoration(labelText: 'Component'),
            validator: (v) => v == null || v.isEmpty ? 'Required' : null,
          ),
          const SizedBox(height: 12),

          // Images
          const Text('Images (Optional, max 3)', style: TextStyle(fontSize: 12, color: EteraTheme.textMuted)),
          const SizedBox(height: 6),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              ...part.images.asMap().entries.map((e) => Stack(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: Image.file(e.value, width: 60, height: 60, fit: BoxFit.cover),
                      ),
                      Positioned(
                        top: -4,
                        right: -4,
                        child: GestureDetector(
                          onTap: () => setState(() => part.images.removeAt(e.key)),
                          child: Container(
                            width: 20,
                            height: 20,
                            decoration: const BoxDecoration(color: EteraTheme.error, shape: BoxShape.circle),
                            child: const Icon(Icons.close, size: 12, color: Colors.white),
                          ),
                        ),
                      ),
                    ],
                  )),
              if (part.images.length < 3)
                GestureDetector(
                  onTap: () async {
                    final picked = await ImagePicker().pickImage(source: ImageSource.gallery, imageQuality: 80);
                    if (picked != null) setState(() => part.images.add(File(picked.path)));
                  },
                  child: Container(
                    width: 60,
                    height: 60,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F8E9),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: EteraTheme.borderGreen),
                    ),
                    child: const Icon(Icons.add_photo_alternate_outlined, color: EteraTheme.green),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }

  // ─── Step 4: Review & Submit ──────────────────────────────────────
  Widget _buildStep4() {
    return Form(
      key: _formKeys[3],
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('Review & Submit', style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 4),
          const Text('Review your proforma request', style: TextStyle(color: EteraTheme.textMuted, fontSize: 13)),
          const SizedBox(height: 20),

          // Summary
          EteraCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Summary', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
                const SizedBox(height: 12),
                _summaryRow('Proformas', _numberOfProformas == -1 ? 'Unlimited' : '$_numberOfProformas'),
                _summaryRow('Car Type', _carType),
                _summaryRow('Brand', _brands.where((b) => b.id == _brandId).map((b) => b.name).firstOrNull ?? ''),
                _summaryRow('Model', _modelCtrl.text),
                _summaryRow('Year', _year),
                _summaryRow('Phone', _phoneCtrl.text),
                _summaryRow('Plate', _plateCtrl.text),
                _summaryRow('Parts', '${_parts.length} part(s)'),
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

  Widget _summaryRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          SizedBox(
            width: 100,
            child: Text(label, style: const TextStyle(color: EteraTheme.textMuted, fontSize: 13)),
          ),
          Expanded(child: Text(value, style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 14))),
        ],
      ),
    );
  }
}

// ─── Part entry helper ──────────────────────────────────────────────
class _PartEntry {
  String condition = 'New';
  final TextEditingController numberCtrl = TextEditingController();
  String grade = '1st grade (Original OEM)';
  final TextEditingController countryCtrl = TextEditingController();
  final TextEditingController quantityCtrl = TextEditingController(text: '1');
  String component = '';
  List<File> images = [];
}
