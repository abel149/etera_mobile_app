import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../config/theme.dart';
import '../../services/auth_service.dart';
import '../../widgets/etera_button.dart';
import '../../widgets/etera_text_field.dart';

class BusinessOwnerRegisterScreen extends StatefulWidget {
  const BusinessOwnerRegisterScreen({super.key});

  @override
  State<BusinessOwnerRegisterScreen> createState() =>
      _BusinessOwnerRegisterScreenState();
}

class _BusinessOwnerRegisterScreenState
    extends State<BusinessOwnerRegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _locationCtrl = TextEditingController();
  final _tinCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();
  File? _licenseImage;
  File? _stampImage;
  bool _obscure = true;
  bool _terms = false;
  bool _loading = false;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _locationCtrl.dispose();
    _tinCtrl.dispose();
    _emailCtrl.dispose();
    _passwordCtrl.dispose();
    _confirmCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickImage(bool isLicense) async {
    final picked = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 80,
    );
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

  Widget _imagePickerTile(String label, File? file, bool isLicense) {
    return GestureDetector(
      onTap: () => _pickImage(isLicense),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: file != null ? EteraTheme.green : EteraTheme.borderGreen,
          ),
        ),
        child: Row(
          children: [
            Icon(
              file != null ? Icons.check_circle : Icons.upload_file,
              color: file != null ? EteraTheme.green : EteraTheme.textMuted,
              size: 20,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                file != null ? file.path.split('/').last : label,
                style: TextStyle(
                  fontSize: 14,
                  color: file != null ? EteraTheme.textPrimary : EteraTheme.textMuted,
                ),
                overflow: TextOverflow.ellipsis,
              ),
            ),
            Text(
              'Browse',
              style: TextStyle(
                fontSize: 12,
                color: EteraTheme.green,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_licenseImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please upload your business license')),
      );
      return;
    }
    if (_stampImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please upload your stamp image')),
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

    final result = await AuthService.registerBusinessOwner(
      name: _nameCtrl.text.trim(),
      phoneNumber: _phoneCtrl.text.trim(),
      location: _locationCtrl.text.trim(),
      tinNumber: _tinCtrl.text.trim(),
      licenseImage: _licenseImage!,
      stampImage: _stampImage!,
      email: _emailCtrl.text.trim(),
      password: _passwordCtrl.text,
      passwordConfirmation: _confirmCtrl.text,
    );

    if (!mounted) return;
    setState(() => _loading = false);

    if (result.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Registration submitted! Awaiting admin approval.'),
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Business Owner Registration'),
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
                EteraTextField(
                  label: 'Full Name',
                  hint: 'Enter your full name',
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
                    if (!RegExp(r'^\d{10}$').hasMatch(v)) {
                      return 'Must be exactly 10 digits';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                EteraTextField(
                  label: 'Location',
                  hint: 'Enter your business location',
                  controller: _locationCtrl,
                  validator: (v) => v == null || v.isEmpty ? 'Required' : null,
                ),
                const SizedBox(height: 16),
                EteraTextField(
                  label: 'TIN Number',
                  hint: 'Enter your TIN number',
                  controller: _tinCtrl,
                  validator: (v) => v == null || v.isEmpty ? 'Required' : null,
                ),
                const SizedBox(height: 16),
                EteraTextField(
                  label: 'Email (Optional)',
                  hint: 'business@example.com',
                  controller: _emailCtrl,
                  keyboardType: TextInputType.emailAddress,
                ),
                const SizedBox(height: 16),

                const Text(
                  'Business License',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft),
                ),
                const SizedBox(height: 6),
                _imagePickerTile('Upload business license image', _licenseImage, true),
                const SizedBox(height: 16),

                const Text(
                  'Stamp Image',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: EteraTheme.textSoft),
                ),
                const SizedBox(height: 6),
                _imagePickerTile('Upload stamp image', _stampImage, false),
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

                Row(
                  children: [
                    Checkbox(
                      value: _terms,
                      onChanged: (v) => setState(() => _terms = v ?? false),
                      activeColor: EteraTheme.green,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(4),
                      ),
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
