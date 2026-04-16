import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../config/theme.dart';
import '../../models/brand.dart';
import '../../services/auth_service.dart';
import '../../widgets/etera_button.dart';
import '../../widgets/etera_text_field.dart';

class GarageShopRegisterScreen extends StatefulWidget {
  const GarageShopRegisterScreen({super.key});

  @override
  State<GarageShopRegisterScreen> createState() => _GarageShopRegisterScreenState();
}

class _GarageShopRegisterScreenState extends State<GarageShopRegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _locationCtrl = TextEditingController();
  final _tinCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();
  final _bankNameCtrl = TextEditingController();
  final _accountCtrl = TextEditingController();
  final _licenseExpireCtrl = TextEditingController();

  String _role = 'garage';
  bool _obscure = true;
  bool _terms = false;
  bool _loading = false;
  File? _licenseImage;
  File? _stampImage;
  List<Brand> _brands = [];
  final Set<int> _selectedBrands = {};
  final _picker = ImagePicker();

  @override
  void initState() {
    super.initState();
    _loadBrands();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final arg = ModalRoute.of(context)?.settings.arguments;
      if (arg is String && (arg == 'garage' || arg == 'shop')) {
        setState(() => _role = arg);
      }
    });
  }

  Future<void> _loadBrands() async {
    final brands = await AuthService.fetchBrands();
    if (mounted) setState(() => _brands = brands);
  }

  Future<void> _pickImage(bool isLicense) async {
    final picked = await _picker.pickImage(source: ImageSource.gallery, imageQuality: 80);
    if (picked != null) {
      setState(() {
        if (isLicense) {
          _licenseImage = File(picked.path);
        } else {
          _stampImage = File(picked.path);
        }
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_licenseImage == null || _stampImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please upload both license and stamp images')),
      );
      return;
    }
    if (_role == 'shop' && _selectedBrands.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select at least one brand')),
      );
      return;
    }
    if (!_terms) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please accept the terms and conditions')),
      );
      return;
    }

    setState(() => _loading = true);

    final result = await AuthService.registerGarageShop(
      name: _nameCtrl.text.trim(),
      phoneNumber: _phoneCtrl.text.trim(),
      role: _role,
      location: _locationCtrl.text.trim(),
      tinNumber: _tinCtrl.text.trim(),
      licenseExpireDate: _licenseExpireCtrl.text.isNotEmpty ? _licenseExpireCtrl.text : null,
      email: _emailCtrl.text.trim(),
      password: _passwordCtrl.text,
      passwordConfirmation: _confirmCtrl.text,
      licenseImage: _licenseImage!,
      stampImage: _stampImage!,
      brandIds: _selectedBrands.toList(),
      bankName: _bankNameCtrl.text.trim().isNotEmpty ? _bankNameCtrl.text.trim() : null,
      accountNumber: _accountCtrl.text.trim().isNotEmpty ? _accountCtrl.text.trim() : null,
    );

    if (!mounted) return;
    setState(() => _loading = false);

    if (result.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Registration successful! Awaiting admin approval.'),
          backgroundColor: EteraTheme.green,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
      Navigator.pushNamedAndRemoveUntil(context, '/pending', (r) => false);
    } else {
      final errorMsg = result.errors != null
          ? result.errors!.values.expand((v) => v is List ? v : [v]).join('\n')
          : result.message;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(errorMsg),
          backgroundColor: EteraTheme.error,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
    }
  }

  Widget _imagePickerTile(String label, File? file, bool isLicense) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft),
        ),
        const SizedBox(height: 6),
        GestureDetector(
          onTap: () => _pickImage(isLicense),
          child: Container(
            height: 120,
            width: double.infinity,
            decoration: BoxDecoration(
              color: const Color(0xFFF9FDF7),
              borderRadius: BorderRadius.circular(EteraTheme.radiusSm),
              border: Border.all(color: EteraTheme.borderGreen),
            ),
            child: file != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(EteraTheme.radiusSm),
                    child: Image.file(file, fit: BoxFit.cover),
                  )
                : Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.camera_alt_outlined, color: EteraTheme.green.withValues(alpha: 0.6), size: 32),
                      const SizedBox(height: 4),
                      Text(
                        'Tap to upload',
                        style: TextStyle(fontSize: 12, color: EteraTheme.textMuted.withValues(alpha: 0.8)),
                      ),
                    ],
                  ),
          ),
        ),
      ],
    );
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _locationCtrl.dispose();
    _tinCtrl.dispose();
    _emailCtrl.dispose();
    _passwordCtrl.dispose();
    _confirmCtrl.dispose();
    _bankNameCtrl.dispose();
    _accountCtrl.dispose();
    _licenseExpireCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_role == 'garage' ? 'Garage Registration' : 'Shop Registration'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Role toggle
                Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F8E9),
                    borderRadius: BorderRadius.circular(EteraTheme.radiusSm),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: GestureDetector(
                          onTap: () => setState(() => _role = 'garage'),
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              gradient: _role == 'garage' ? EteraTheme.primaryGradient : null,
                              borderRadius: BorderRadius.circular(EteraTheme.radiusSm),
                            ),
                            child: Center(
                              child: Text(
                                'Garage',
                                style: TextStyle(
                                  fontWeight: FontWeight.w600,
                                  color: _role == 'garage' ? Colors.white : EteraTheme.textMuted,
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        child: GestureDetector(
                          onTap: () => setState(() => _role = 'shop'),
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              gradient: _role == 'shop' ? EteraTheme.primaryGradient : null,
                              borderRadius: BorderRadius.circular(EteraTheme.radiusSm),
                            ),
                            child: Center(
                              child: Text(
                                'Spare Part Shop',
                                style: TextStyle(
                                  fontWeight: FontWeight.w600,
                                  color: _role == 'shop' ? Colors.white : EteraTheme.textMuted,
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                EteraTextField(
                  label: 'Full Name',
                  hint: 'Enter full name',
                  controller: _nameCtrl,
                  validator: (v) => v == null || v.isEmpty ? 'Required' : null,
                ),
                const SizedBox(height: 16),
                EteraTextField(
                  label: 'Phone Number',
                  hint: '0940000000',
                  controller: _phoneCtrl,
                  keyboardType: TextInputType.phone,
                  maxLength: 10,
                  validator: (v) {
                    if (v == null || v.isEmpty) return 'Required';
                    if (!RegExp(r'^\d{10}$').hasMatch(v)) return 'Must be exactly 10 digits';
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                EteraTextField(
                  label: 'Location',
                  hint: 'Enter location',
                  controller: _locationCtrl,
                  validator: (v) => v == null || v.isEmpty ? 'Required' : null,
                ),
                const SizedBox(height: 16),
                EteraTextField(
                  label: 'TIN Number',
                  hint: 'Enter TIN number',
                  controller: _tinCtrl,
                  validator: (v) => v == null || v.isEmpty ? 'Required' : null,
                ),
                const SizedBox(height: 16),
                EteraTextField(
                  label: 'License Expiry Date (Optional)',
                  hint: 'YYYY-MM-DD',
                  controller: _licenseExpireCtrl,
                  readOnly: true,
                  onTap: () async {
                    final date = await showDatePicker(
                      context: context,
                      initialDate: DateTime.now().add(const Duration(days: 365)),
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now().add(const Duration(days: 3650)),
                    );
                    if (date != null) {
                      _licenseExpireCtrl.text =
                          '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
                    }
                  },
                ),
                const SizedBox(height: 16),

                // Image uploads
                Row(
                  children: [
                    Expanded(child: _imagePickerTile('License Image *', _licenseImage, true)),
                    const SizedBox(width: 12),
                    Expanded(child: _imagePickerTile('Stamp Image *', _stampImage, false)),
                  ],
                ),
                const SizedBox(height: 16),

                // Brand selection (for shops)
                if (_role == 'shop' || _brands.isNotEmpty) ...[
                  const Text(
                    'Select Brands',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft),
                  ),
                  if (_role == 'shop')
                    const Text(
                      'Required — select at least one brand',
                      style: TextStyle(fontSize: 11, color: EteraTheme.textMuted),
                    ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: _brands.map((brand) {
                      final selected = _selectedBrands.contains(brand.id);
                      return FilterChip(
                        label: Text(brand.name),
                        selected: selected,
                        onSelected: (v) {
                          setState(() {
                            if (v) {
                              _selectedBrands.add(brand.id);
                            } else {
                              _selectedBrands.remove(brand.id);
                            }
                          });
                        },
                        selectedColor: EteraTheme.green.withValues(alpha: 0.15),
                        checkmarkColor: EteraTheme.green,
                        labelStyle: TextStyle(
                          color: selected ? EteraTheme.green : EteraTheme.textSoft,
                          fontWeight: selected ? FontWeight.w600 : FontWeight.w400,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
                          side: BorderSide(
                            color: selected ? EteraTheme.green : EteraTheme.borderGreen,
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                  const SizedBox(height: 16),
                ],

                EteraTextField(
                  label: 'Email (Optional)',
                  hint: 'john@example.com',
                  controller: _emailCtrl,
                  keyboardType: TextInputType.emailAddress,
                ),
                const SizedBox(height: 16),
                EteraTextField(
                  label: 'Password (6 digits)',
                  hint: 'Enter 6-digit password',
                  controller: _passwordCtrl,
                  obscureText: _obscure,
                  maxLength: 6,
                  keyboardType: TextInputType.number,
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscure ? Icons.visibility_off : Icons.visibility,
                      color: EteraTheme.textMuted,
                      size: 20,
                    ),
                    onPressed: () => setState(() => _obscure = !_obscure),
                  ),
                  validator: (v) {
                    if (v == null || v.length != 6) return 'Must be exactly 6 characters';
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                EteraTextField(
                  label: 'Confirm Password',
                  hint: 'Re-enter password',
                  controller: _confirmCtrl,
                  obscureText: true,
                  maxLength: 6,
                  keyboardType: TextInputType.number,
                  validator: (v) {
                    if (v != _passwordCtrl.text) return 'Passwords do not match';
                    return null;
                  },
                ),
                const SizedBox(height: 16),

                // Bank info (optional)
                const Text(
                  'Bank Information (Optional)',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: EteraTheme.textPrimary),
                ),
                const SizedBox(height: 8),
                EteraTextField(
                  label: 'Bank Name',
                  hint: 'e.g. Commercial Bank of Ethiopia',
                  controller: _bankNameCtrl,
                ),
                const SizedBox(height: 16),
                EteraTextField(
                  label: 'Account Number',
                  hint: 'Enter account number',
                  controller: _accountCtrl,
                  keyboardType: TextInputType.number,
                ),
                const SizedBox(height: 16),

                Row(
                  children: [
                    Checkbox(
                      value: _terms,
                      onChanged: (v) => setState(() => _terms = v ?? false),
                      activeColor: EteraTheme.green,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                    ),
                    const Flexible(
                      child: Text(
                        'I agree to the Terms and Conditions',
                        style: TextStyle(fontSize: 13, color: EteraTheme.textMuted),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 24),

                EteraButton(
                  label: 'Register',
                  loading: _loading,
                  onPressed: _submit,
                ),
                const SizedBox(height: 32),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
