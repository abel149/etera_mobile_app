import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../services/encryption_service.dart';
import '../../utils/e2e_crypto.dart';

/// Full encryption management screen for insurance users.
///
/// Mirrors the web's insurance/encryption-setup page:
///   • Check current status
///   • Initial setup (generate keys → enter PIN → optional recovery → save)
///   • Change PIN (re-wrap the same private key)
///   • Recovery key management
class InsuranceEncryptionScreen extends StatefulWidget {
  const InsuranceEncryptionScreen({super.key});

  @override
  State<InsuranceEncryptionScreen> createState() => _InsuranceEncryptionScreenState();
}

class _InsuranceEncryptionScreenState extends State<InsuranceEncryptionScreen> {
  bool _loading = true;
  bool _hasEncryption = false;
  bool _hasRecovery   = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadStatus();
  }

  Future<void> _loadStatus() async {
    setState(() { _loading = true; _error = null; });
    final res = await EncryptionService.getStatus();
    if (!mounted) return;
    if (res['success'] == true) {
      setState(() {
        _loading       = false;
        _hasEncryption = res['has_encryption'] == true;
        _hasRecovery   = res['has_recovery']   == true;
      });
    } else {
      setState(() { _loading = false; _error = res['message']?.toString() ?? 'Failed to load status'; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Encryption'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: EteraTheme.green))
          : _error != null
              ? _ErrorView(message: _error!, onRetry: _loadStatus)
              : _hasEncryption
                  ? _ManageView(
                      hasRecovery: _hasRecovery,
                      onChanged:   _loadStatus,
                    )
                  : _SetupView(onSetupComplete: _loadStatus),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Setup view — first-time encryption key generation
// ─────────────────────────────────────────────────────────────────────────────
class _SetupView extends StatefulWidget {
  final VoidCallback onSetupComplete;
  const _SetupView({required this.onSetupComplete});

  @override
  State<_SetupView> createState() => _SetupViewState();
}

class _SetupViewState extends State<_SetupView> {
  final _formKey = GlobalKey<FormState>();
  final _pinCtrl          = TextEditingController();
  final _pinConfirmCtrl   = TextEditingController();
  final _recoveryCtrl     = TextEditingController();
  bool _obscurePin      = true;
  bool _obscureConfirm  = true;
  bool _useRecovery     = false;
  bool _generating      = false;

  @override
  void dispose() {
    _pinCtrl.dispose();
    _pinConfirmCtrl.dispose();
    _recoveryCtrl.dispose();
    super.dispose();
  }

  Future<void> _setup() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _generating = true);

    try {
      // 1. Generate RSA key pair in a background isolate
      final keys = await generateKeyPair();

      // 2. Wrap private key with PIN
      final pinWrapped = wrapPrivateKey(keys.privateKeyPem, _pinCtrl.text.trim());

      // 3. Optionally wrap private key with recovery phrase
      WrappedKey? recoveryWrapped;
      if (_useRecovery && _recoveryCtrl.text.trim().isNotEmpty) {
        recoveryWrapped = wrapPrivateKey(keys.privateKeyPem, _recoveryCtrl.text.trim());
      }

      // 4. Save to server
      final body = {
        'public_key':            keys.publicKeyPem,
        'encrypted_private_key': pinWrapped.encryptedPrivateKey,
        'key_iv':                pinWrapped.keyIv,
        'key_salt':              pinWrapped.keySalt,
        if (recoveryWrapped != null) ...{
          'recovery_encrypted_private_key': recoveryWrapped.encryptedPrivateKey,
          'recovery_key_iv':               recoveryWrapped.keyIv,
          'recovery_key_salt':             recoveryWrapped.keySalt,
        },
      };
      final res = await EncryptionService.saveKeys(body);

      if (!mounted) return;
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Encryption set up successfully!'),
          backgroundColor: EteraTheme.green,
          behavior: SnackBarBehavior.floating,
        ));
        widget.onSetupComplete();
      } else {
        _showError(res['message']?.toString() ?? 'Setup failed. Please try again.');
      }
    } catch (e) {
      if (!mounted) return;
      _showError('Encryption error: $e');
    } finally {
      if (mounted) setState(() => _generating = false);
    }
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 40),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Header banner
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: EteraTheme.primaryGradient,
                borderRadius: BorderRadius.circular(EteraTheme.radiusLg),
              ),
              child: const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(Icons.lock_outlined, color: Colors.white, size: 32),
                  SizedBox(height: 10),
                  Text('Set Up Encryption',
                      style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w700)),
                  SizedBox(height: 8),
                  Text(
                    'An RSA-2048 key pair will be generated on your device. '
                    'Your private key is protected by a PIN — the server never stores it in plaintext.',
                    style: TextStyle(color: Colors.white70, fontSize: 13, height: 1.5),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 28),

            // How it works
            _InfoTile(Icons.vpn_key_outlined, 'RSA key pair generated locally on your device'),
            _InfoTile(Icons.pin_outlined, 'Your PIN encrypts the private key before upload'),
            _InfoTile(Icons.shield_outlined, 'Shop and garage prices are encrypted before reaching the server'),
            const SizedBox(height: 28),

            // PIN field
            Text('Encryption PIN', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
            const SizedBox(height: 6),
            const Text('Choose a PIN to protect your private key.', style: TextStyle(color: EteraTheme.textMuted, fontSize: 13)),
            const SizedBox(height: 14),
            TextFormField(
              controller: _pinCtrl,
              obscureText: _obscurePin,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: 'PIN (min 4 digits)',
                border: const OutlineInputBorder(),
                suffixIcon: IconButton(
                  icon: Icon(_obscurePin ? Icons.visibility_off_outlined : Icons.visibility_outlined),
                  onPressed: () => setState(() => _obscurePin = !_obscurePin),
                ),
              ),
              validator: (v) {
                if (v == null || v.isEmpty) return 'PIN is required';
                if (v.length < 4) return 'At least 4 digits';
                return null;
              },
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _pinConfirmCtrl,
              obscureText: _obscureConfirm,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: 'Confirm PIN',
                border: const OutlineInputBorder(),
                suffixIcon: IconButton(
                  icon: Icon(_obscureConfirm ? Icons.visibility_off_outlined : Icons.visibility_outlined),
                  onPressed: () => setState(() => _obscureConfirm = !_obscureConfirm),
                ),
              ),
              validator: (v) {
                if (v != _pinCtrl.text) return 'PINs do not match';
                return null;
              },
            ),
            const SizedBox(height: 24),

            // Recovery toggle
            SwitchListTile(
              value: _useRecovery,
              onChanged: (v) => setState(() => _useRecovery = v),
              activeThumbColor: EteraTheme.green,
              activeTrackColor: EteraTheme.green.withValues(alpha: 0.5),
              title: const Text('Enable Recovery Key', style: TextStyle(fontWeight: FontWeight.w600)),
              subtitle: const Text('A separate passphrase to recover access if you forget your PIN.', style: TextStyle(fontSize: 12)),
              contentPadding: EdgeInsets.zero,
            ),

            if (_useRecovery) ...[
              const SizedBox(height: 12),
              TextFormField(
                controller: _recoveryCtrl,
                decoration: const InputDecoration(
                  labelText: 'Recovery Passphrase',
                  hintText: 'e.g. 12 random words or a strong phrase',
                  border: OutlineInputBorder(),
                ),
                validator: (v) {
                  if (_useRecovery && (v == null || v.trim().length < 8)) {
                    return 'Recovery passphrase must be at least 8 characters';
                  }
                  return null;
                },
              ),
            ],
            const SizedBox(height: 32),

            if (_generating)
              const Column(
                children: [
                  CircularProgressIndicator(color: EteraTheme.green),
                  SizedBox(height: 14),
                  Text('Generating RSA-2048 key pair…\nThis may take a few seconds.',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: EteraTheme.textMuted, fontSize: 13, height: 1.5)),
                ],
              )
            else
              ElevatedButton.icon(
                onPressed: _setup,
                icon: const Icon(Icons.lock_open_outlined),
                label: const Text('Generate Keys & Set Up Encryption',
                    style: TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: EteraTheme.green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Manage view — shown when encryption is already set up
// ─────────────────────────────────────────────────────────────────────────────
class _ManageView extends StatelessWidget {
  final bool hasRecovery;
  final VoidCallback onChanged;

  const _ManageView({required this.hasRecovery, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 40),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Status card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: EteraTheme.green.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(EteraTheme.radiusLg),
              border: Border.all(color: EteraTheme.green.withValues(alpha: 0.25)),
            ),
            child: const Row(
              children: [
                Icon(Icons.verified_user_outlined, color: EteraTheme.green, size: 32),
                SizedBox(width: 14),
                Expanded(
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text('Encryption Active', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: EteraTheme.green)),
                    SizedBox(height: 4),
                    Text('Shops and garages will encrypt their prices before submission.',
                        style: TextStyle(fontSize: 13, color: EteraTheme.textMuted, height: 1.4)),
                  ]),
                ),
              ],
            ),
          ),
          const SizedBox(height: 28),

          // Actions
          _ActionTile(
            icon: Icons.pin_outlined,
            title: 'Change PIN',
            subtitle: 'Re-wrap your private key with a new PIN.',
            onTap: () async {
              await Navigator.push(context, MaterialPageRoute(builder: (_) => const _ChangePinScreen()));
              onChanged();
            },
          ),
          const SizedBox(height: 12),

          _ActionTile(
            icon: hasRecovery ? Icons.backup_outlined : Icons.backup_outlined,
            title: hasRecovery ? 'Recovery Key Active' : 'Set Up Recovery Key',
            subtitle: hasRecovery
                ? 'A recovery passphrase is saved. You can update it by regenerating keys.'
                : 'Add a recovery passphrase to restore access if you forget your PIN.',
            trailing: hasRecovery
                ? const Icon(Icons.check_circle, color: EteraTheme.green, size: 20)
                : const Icon(Icons.chevron_right),
            onTap: hasRecovery ? null : () {
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                content: Text('To add a recovery key, regenerate your keys from setup.'),
                behavior: SnackBarBehavior.floating,
              ));
            },
          ),
          const SizedBox(height: 28),

          // Info section
          const Text('How it works', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
          const SizedBox(height: 12),
          _InfoTile(Icons.business_outlined, 'When a shop or garage applies to your proforma, they encrypt their price with your public key.'),
          _InfoTile(Icons.lock_outlined, 'Only you can decrypt the submitted prices using your private key and PIN.'),
          _InfoTile(Icons.visibility_off_outlined, 'The server stores only encrypted prices — Etera staff cannot read the actual amounts.'),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Change PIN screen
// ─────────────────────────────────────────────────────────────────────────────
class _ChangePinScreen extends StatefulWidget {
  const _ChangePinScreen();

  @override
  State<_ChangePinScreen> createState() => _ChangePinScreenState();
}

class _ChangePinScreenState extends State<_ChangePinScreen> {
  final _formKey       = GlobalKey<FormState>();
  final _oldPinCtrl    = TextEditingController();
  final _newPinCtrl    = TextEditingController();
  final _confirmCtrl   = TextEditingController();
  bool _obscureOld     = true;
  bool _obscureNew     = true;
  bool _obscureConfirm = true;
  bool _saving         = false;

  @override
  void dispose() {
    _oldPinCtrl.dispose();
    _newPinCtrl.dispose();
    _confirmCtrl.dispose();
    super.dispose();
  }

  Future<void> _change() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    try {
      // 1. Download the current encrypted private key blob
      final pkRes = await EncryptionService.getPrivateKey();
      if (pkRes['success'] != true) {
        _showError(pkRes['message']?.toString() ?? 'Failed to fetch private key.');
        return;
      }

      // 2. Unwrap with old PIN to get the plaintext private key PEM
      final privateKeyPem = unwrapPrivateKey(
        pkRes['encrypted_private_key'] as String,
        pkRes['key_iv']               as String,
        pkRes['key_salt']             as String,
        _oldPinCtrl.text.trim(),
      );

      // 3. Re-wrap with new PIN
      final wrapped = wrapPrivateKey(privateKeyPem, _newPinCtrl.text.trim());

      // 4. Save new blob (public key unchanged)
      final saveRes = await EncryptionService.changePin({
        'encrypted_private_key': wrapped.encryptedPrivateKey,
        'key_iv':                wrapped.keyIv,
        'key_salt':              wrapped.keySalt,
      });

      if (!mounted) return;
      if (saveRes['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('PIN changed successfully.'),
          backgroundColor: EteraTheme.green,
          behavior: SnackBarBehavior.floating,
        ));
        Navigator.pop(context);
      } else {
        _showError(saveRes['message']?.toString() ?? 'PIN change failed.');
      }
    } on FormatException {
      _showError('Incorrect current PIN. Please try again.');
    } catch (e) {
      if (!mounted) return;
      _showError('Error: $e');
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  void _showError(String msg) {
    if (!mounted) return;
    setState(() => _saving = false);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: EteraTheme.error,
      behavior: SnackBarBehavior.floating,
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Change PIN'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 24, 20, 40),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text(
                'Your PIN protects your private key. When you change it, the key is re-encrypted with the new PIN. '
                'Previously encrypted proformas remain readable.',
                style: TextStyle(color: EteraTheme.textMuted, fontSize: 13, height: 1.5),
              ),
              const SizedBox(height: 24),

              _PinField(ctrl: _oldPinCtrl, label: 'Current PIN', obscure: _obscureOld,
                  onToggle: () => setState(() => _obscureOld = !_obscureOld)),
              const SizedBox(height: 14),
              _PinField(ctrl: _newPinCtrl, label: 'New PIN', obscure: _obscureNew,
                  onToggle: () => setState(() => _obscureNew = !_obscureNew),
                  validator: (v) {
                    if (v == null || v.length < 4) return 'At least 4 digits';
                    return null;
                  }),
              const SizedBox(height: 14),
              _PinField(ctrl: _confirmCtrl, label: 'Confirm New PIN', obscure: _obscureConfirm,
                  onToggle: () => setState(() => _obscureConfirm = !_obscureConfirm),
                  validator: (v) {
                    if (v != _newPinCtrl.text) return 'PINs do not match';
                    return null;
                  }),
              const SizedBox(height: 32),

              ElevatedButton.icon(
                onPressed: _saving ? null : _change,
                icon: _saving
                    ? const SizedBox(width: 18, height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Icon(Icons.lock_reset_outlined),
                label: Text(_saving ? 'Changing…' : 'Change PIN',
                    style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: EteraTheme.green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Small reusable widgets
// ─────────────────────────────────────────────────────────────────────────────
class _InfoTile extends StatelessWidget {
  final IconData icon;
  final String   text;
  const _InfoTile(this.icon, this.text);

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 10),
    child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Icon(icon, size: 18, color: EteraTheme.green),
      const SizedBox(width: 10),
      Expanded(child: Text(text, style: const TextStyle(fontSize: 13, color: EteraTheme.textSoft, height: 1.4))),
    ]),
  );
}

class _ActionTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final Widget? trailing;
  final VoidCallback? onTap;

  const _ActionTile({required this.icon, required this.title, required this.subtitle, this.trailing, this.onTap});

  @override
  Widget build(BuildContext context) => Material(
    color: Colors.white,
    borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
    child: InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(EteraTheme.radiusMd),
          border: Border.all(color: Colors.grey.shade200),
        ),
        child: Row(children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(
              color: EteraTheme.green.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: EteraTheme.green, size: 20),
          ),
          const SizedBox(width: 14),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(title, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
            const SizedBox(height: 2),
            Text(subtitle, style: const TextStyle(fontSize: 12, color: EteraTheme.textMuted, height: 1.3)),
          ])),
          trailing ?? (onTap != null ? const Icon(Icons.chevron_right, color: EteraTheme.textMuted) : const SizedBox.shrink()),
        ]),
      ),
    ),
  );
}

class _PinField extends StatelessWidget {
  final TextEditingController ctrl;
  final String label;
  final bool obscure;
  final VoidCallback onToggle;
  final String? Function(String?)? validator;

  const _PinField({
    required this.ctrl, required this.label, required this.obscure, required this.onToggle, this.validator,
  });

  @override
  Widget build(BuildContext context) => TextFormField(
    controller: ctrl,
    obscureText: obscure,
    keyboardType: TextInputType.number,
    decoration: InputDecoration(
      labelText: label,
      border: const OutlineInputBorder(),
      suffixIcon: IconButton(
        icon: Icon(obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined),
        onPressed: onToggle,
      ),
    ),
    validator: validator ?? (v) {
      if (v == null || v.isEmpty) return 'Required';
      return null;
    },
  );
}

class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorView({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(24),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.wifi_off, size: 48, color: EteraTheme.textMuted),
        const SizedBox(height: 12),
        Text(message, textAlign: TextAlign.center, style: const TextStyle(color: EteraTheme.textMuted)),
        const SizedBox(height: 20),
        ElevatedButton(onPressed: onRetry, child: const Text('Retry')),
      ]),
    ),
  );
}
