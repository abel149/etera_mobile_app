@extends('layouts.insurance')
@section('content')
<style>
.pin-wrap { position: relative; }
.pin-toggle {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #6c757d;
    padding: 0;
    font-size: 1.1rem;
    z-index: 4;
}
.pin-toggle:hover { color: #0d6efd; }
</style>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">

        <h3 class="mb-1">Price Encryption Setup</h3>
        <p class="text-secondary mb-4">
            When enabled, shop and garage prices are encrypted in the browser before reaching the server.
            <strong>Only you</strong> — using your Encryption PIN — can decrypt them.
            Not the admin, not the developer, nobody else.
        </p>

        {{-- ════ ACTIVE STATUS BANNER ════ --}}
        @if($user->has_encryption)
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
            <i class="bx bx-shield-check fs-3"></i>
            <div>
                <strong>Encryption is active.</strong>
                Shops and garages are sending you encrypted prices.
                Use your PIN when viewing received proformas.
            </div>
        </div>

        {{-- ════ CHANGE PIN CARD (safe — same RSA keys, old proformas stay readable) ════ --}}
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bx bx-key me-2 text-primary"></i> Change Encryption PIN
            </div>
            <div class="card-body">
                <p class="text-secondary small mb-3">
                    Changing your PIN re-wraps the <em>same private key</em> with a new PIN.
                    <strong class="text-success">All previously encrypted proformas remain fully readable</strong>
                    with the new PIN.
                </p>
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Current PIN</label>
                        <div class="pin-wrap">
                            <input type="password" id="cpOldPin" class="form-control pe-4" placeholder="Your current PIN">
                            <button type="button" class="pin-toggle" data-target="cpOldPin"><i class='bx bx-show'></i></button>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">New PIN</label>
                        <div class="pin-wrap">
                            <input type="password" id="cpNewPin" class="form-control pe-4" placeholder="Min 8 characters" minlength="8">
                            <button type="button" class="pin-toggle" data-target="cpNewPin"><i class='bx bx-show'></i></button>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Confirm New PIN</label>
                        <div class="pin-wrap">
                            <input type="password" id="cpNewPinConfirm" class="form-control pe-4" placeholder="Repeat new PIN">
                            <button type="button" class="pin-toggle" data-target="cpNewPinConfirm"><i class='bx bx-show'></i></button>
                        </div>
                    </div>
                </div>
                <button id="btnChangePin" class="btn btn-primary radius-30 px-4">
                    <i class="bx bx-refresh me-2"></i> Change PIN
                </button>
                <div id="cpStatus" class="mt-3 d-none">
                    <div class="d-flex align-items-center gap-2 text-secondary">
                        <div class="spinner-border spinner-border-sm"></div>
                        <span id="cpStatusText">Processing…</span>
                    </div>
                </div>
                <div id="cpSuccess" class="alert alert-success mt-3 d-none">
                    <i class="bx bx-check-circle me-2"></i> PIN changed successfully.
                    All encrypted proformas are still readable with your new PIN.
                </div>
                <div id="cpError" class="alert alert-danger mt-3 d-none"></div>
            </div>
        </div>

        {{-- ════ FORGOT PIN — Recovery Code ════ --}}
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bx bx-help-circle me-2 text-warning"></i> Forgot Your PIN? Use Recovery Code
            </div>
            <div class="card-body">
                <p class="text-secondary small mb-3">
                    If you set up encryption after this feature was released, you received a
                    <strong>one-time Recovery Code</strong>. Enter it below together with a new PIN to
                    regain access. <strong class="text-success">All previously encrypted proformas will remain readable.</strong>
                </p>
                <div id="rcNoKeyWarning" class="alert alert-warning d-none mb-3">
                    <i class="bx bx-error me-2"></i>
                    No recovery key found for your account. You must <strong>Regenerate Keys</strong> below
                    (this will lose access to old encrypted amounts), then you will have a new recovery code.
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label">Recovery Code</label>
                        <div class="pin-wrap">
                            <input type="password" id="rcCode" class="form-control font-monospace pe-4" placeholder="Paste your 48-character recovery code here">
                            <button type="button" class="pin-toggle" data-target="rcCode"><i class='bx bx-show'></i></button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">New PIN</label>
                        <div class="pin-wrap">
                            <input type="password" id="rcNewPin" class="form-control pe-4" placeholder="Min 8 characters" minlength="8">
                            <button type="button" class="pin-toggle" data-target="rcNewPin"><i class='bx bx-show'></i></button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Confirm New PIN</label>
                        <div class="pin-wrap">
                            <input type="password" id="rcNewPinConfirm" class="form-control pe-4" placeholder="Repeat new PIN">
                            <button type="button" class="pin-toggle" data-target="rcNewPinConfirm"><i class='bx bx-show'></i></button>
                        </div>
                    </div>
                </div>
                <button id="btnRecoverPin" class="btn btn-warning radius-30 px-4">
                    <i class="bx bx-shield-check me-2"></i> Recover Access
                </button>
                <div id="rcStatus" class="mt-3 d-none">
                    <div class="d-flex align-items-center gap-2 text-secondary">
                        <div class="spinner-border spinner-border-sm"></div>
                        <span id="rcStatusText">Processing…</span>
                    </div>
                </div>
                <div id="rcSuccess" class="alert alert-success mt-3 d-none">
                    <i class="bx bx-check-circle me-2"></i> PIN recovered successfully. All encrypted proformas are still readable.
                </div>
                <div id="rcError" class="alert alert-danger mt-3 d-none"></div>
            </div>
        </div>

        {{-- ════ DANGER ZONE: Regenerate Keys ════ --}}
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white fw-semibold">
                <i class="bx bx-error-alt me-2"></i> Danger Zone — Regenerate Keys
            </div>
            <div class="card-body">
                <div class="alert alert-danger mb-3 d-flex gap-2">
                    <i class="bx bx-error fs-4 flex-shrink-0 mt-1"></i>
                    <div>
                        <strong>This is a destructive action.</strong>
                        Regenerating keys creates a brand new RSA key pair.
                        <strong>Every previously encrypted proforma price will become permanently unreadable — forever.</strong>
                        Only do this if you have no proformas with encrypted prices yet,
                        or if you are comfortable losing access to old prices.
                    </div>
                </div>
                <button class="btn btn-danger radius-30 px-4" id="btnShowRegen">
                    <i class="bx bx-lock-open me-2"></i> I understand, show regenerate form
                </button>
                <div id="regenForm" class="d-none mt-3">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">New PIN</label>
                            <div class="pin-wrap">
                                <input type="password" id="encPin" class="form-control pe-4" placeholder="Minimum 8 characters" minlength="8">
                                <button type="button" class="pin-toggle" data-target="encPin"><i class='bx bx-show'></i></button>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Confirm New PIN</label>
                            <div class="pin-wrap">
                                <input type="password" id="encPinConfirm" class="form-control pe-4" placeholder="Repeat PIN">
                                <button type="button" class="pin-toggle" data-target="encPinConfirm"><i class='bx bx-show'></i></button>
                            </div>
                        </div>
                    </div>
                    <button id="btnGenerate" class="btn btn-danger radius-30 px-4">
                        <i class="bx bx-refresh me-2"></i> Regenerate Keys Now
                    </button>
                    <div id="genStatus" class="mt-3 d-none">
                        <div class="d-flex align-items-center gap-2 text-secondary">
                            <div class="spinner-border spinner-border-sm"></div>
                            <span id="genStatusText">Generating RSA-2048 key pair…</span>
                        </div>
                    </div>
                    <div id="genSuccess" class="alert alert-success mt-3 d-none">
                        <i class="bx bx-check-circle me-2"></i>
                        <strong>New keys saved.</strong> Remember: old encrypted proformas are no longer accessible.
                    </div>
                    <div id="genError" class="alert alert-danger mt-3 d-none"></div>
                </div>
            </div>
        </div>

        @else
        {{-- ════ FIRST-TIME SETUP ════ --}}
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bx bx-lock-alt me-2 text-primary"></i> Enable Encryption
            </div>
            <div class="card-body">
                <p class="text-secondary small mb-3">
                    Choose a PIN. This PIN is <strong>never sent to the server</strong> — it protects
                    your private key in the browser. If you forget your PIN, encrypted prices
                    cannot be recovered.
                </p>
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Encryption PIN</label>
                        <div class="pin-wrap">
                            <input type="password" id="encPin" class="form-control pe-4" placeholder="Minimum 8 characters" minlength="8">
                            <button type="button" class="pin-toggle" data-target="encPin"><i class='bx bx-show'></i></button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Confirm PIN</label>
                        <div class="pin-wrap">
                            <input type="password" id="encPinConfirm" class="form-control pe-4" placeholder="Repeat PIN">
                            <button type="button" class="pin-toggle" data-target="encPinConfirm"><i class='bx bx-show'></i></button>
                        </div>
                    </div>
                </div>
                <button id="btnGenerate" class="btn btn-primary radius-30 px-4">
                    <i class="bx bx-lock-alt me-2"></i> Enable Encryption
                </button>
                <div id="genStatus" class="mt-3 d-none">
                    <div class="d-flex align-items-center gap-2 text-secondary">
                        <div class="spinner-border spinner-border-sm"></div>
                        <span id="genStatusText">Generating RSA-2048 key pair…</span>
                    </div>
                </div>
                <div id="genSuccess" class="alert alert-success mt-3 d-none">
                    <i class="bx bx-check-circle me-2"></i>
                    <strong>Encryption is now active!</strong>
                    Future price submissions will be encrypted. Enter your PIN when viewing a received proforma.
                </div>
                <div id="genError" class="alert alert-danger mt-3 d-none"></div>
            </div>
        </div>
        @endif

        {{-- ════ How it works ════ --}}
        <div class="card border-0 bg-light">
            <div class="card-body">
                <h6 class="fw-semibold mb-2"><i class="bx bx-info-circle me-1 text-primary"></i> How it works</h6>
                <ol class="text-secondary small mb-0">
                    <li>Your browser generates an <strong>RSA-2048 key pair</strong>.</li>
                    <li>The <strong>public key</strong> is stored on the server — shops/garages use it to encrypt prices.</li>
                    <li>Your <strong>private key</strong> is wrapped with AES-256 (derived from your PIN) and stored encrypted. <em>Nobody can read it without your PIN.</em></li>
                    <li>When you open a received proforma, enter your PIN → browser unwraps the private key → browser decrypts all prices. <strong>Plaintext prices never reach the server.</strong></li>
                    <li><strong>Change PIN</strong> only re-wraps the same key — old proformas stay readable. <strong>Regenerate Keys</strong> creates a new key — old proformas are permanently lost.</li>
                </ol>
            </div>
        </div>

    </div>
</div>

<script>{!! file_get_contents(base_path('resources/js/e2e-encryption.js')) !!}</script>
<script>
// ── Shared helper: POST JSON with CSRF ───────────────────────────
 async function postJSON(url, body) {
    const r = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(body),
    });
    return r.json();
}

// ── Generate / Regenerate Keys ─────────────────────────────────
const btnGenerate = document.getElementById('btnGenerate');
if (btnGenerate) {
    btnGenerate.addEventListener('click', async () => {
        const pin        = document.getElementById('encPin').value.trim();
        const pinConfirm = document.getElementById('encPinConfirm').value.trim();
        const genStatus  = document.getElementById('genStatus');
        const genSuccess = document.getElementById('genSuccess');
        const genError   = document.getElementById('genError');
        const statusText = document.getElementById('genStatusText');

        genSuccess.classList.add('d-none');
        genError.classList.add('d-none');

        if (pin.length < 8) {
            genError.textContent = 'PIN must be at least 8 characters.';
            genError.classList.remove('d-none');
            return;
        }
        if (pin !== pinConfirm) {
            genError.textContent = 'PINs do not match.';
            genError.classList.remove('d-none');
            return;
        }

        genStatus.classList.remove('d-none');
        btnGenerate.disabled = true;

        try {
            statusText.textContent = 'Generating RSA-2048 key pair…';
            const keyPair = await E2EEncryption.generateKeyPair();

            statusText.textContent = 'Exporting public key…';
            const publicKeyB64 = await E2EEncryption.exportPublicKey(keyPair.publicKey);

            statusText.textContent = 'Wrapping private key with PIN…';
            const { encrypted, iv, salt } = await E2EEncryption.encryptPrivateKey(keyPair.privateKey, pin);

            statusText.textContent = 'Generating recovery code…';
            const recoveryCodeBytes = crypto.getRandomValues(new Uint8Array(24));
            const recoveryCode = Array.from(recoveryCodeBytes).map(b => b.toString(16).padStart(2, '0')).join('');
            const { encrypted: rEnc, iv: rIv, salt: rSalt } = await E2EEncryption.encryptPrivateKey(keyPair.privateKey, recoveryCode);

            statusText.textContent = 'Saving to server…';
            const data = await postJSON('{{ route("insurance.encryption.setup.save") }}', {
                public_key:                       publicKeyB64,
                encrypted_private_key:            encrypted,
                key_iv:                           iv,
                key_salt:                         salt,
                recovery_encrypted_private_key:   rEnc,
                recovery_key_iv:                  rIv,
                recovery_key_salt:                rSalt,
            });

            genStatus.classList.add('d-none');
            if (data.success) {
                genSuccess.classList.remove('d-none');
                document.getElementById('encPin').value        = '';
                document.getElementById('encPinConfirm').value = '';
                showRecoveryCodeModal(recoveryCode);
            } else {
                throw new Error(data.message || 'Server error.');
            }
        } catch (err) {
            genStatus.classList.add('d-none');
            genError.textContent = 'Error: ' + err.message;
            genError.classList.remove('d-none');
        } finally {
            btnGenerate.disabled = false;
        }
    });
}

// ── Show regenerate form toggle ────────────────────────────────
const btnShowRegen = document.getElementById('btnShowRegen');
if (btnShowRegen) {
    btnShowRegen.addEventListener('click', () => {
        const f = document.getElementById('regenForm');
        if (f) f.classList.toggle('d-none');
    });
}

// ── Recovery Code Modal ──────────────────────────────────────
function showRecoveryCodeModal(code) {
    const existing = document.getElementById('recoveryCodeModal');
    if (existing) existing.remove();

    const modal = document.createElement('div');
    modal.id = 'recoveryCodeModal';
    modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;';
    modal.innerHTML = `
        <div style="background:#fff;border-radius:12px;padding:28px 32px;max-width:520px;width:100%;box-shadow:0 8px 40px rgba(0,0,0,0.3);">
            <h5 style="margin:0 0 8px;"><i class="bx bx-key" style="color:#f59e0b;"></i> Save Your Recovery Code</h5>
            <p style="font-size:0.88rem;color:#555;margin-bottom:16px;">
                This code is shown <strong>only once</strong> and is never stored in plain text.
                Save it somewhere safe (password manager, printed paper, etc.).<br>
                <strong>If you lose both your PIN and this code, encrypted amounts cannot be recovered.</strong>
            </p>
            <div style="background:#fefce8;border:2px solid #f59e0b;border-radius:8px;padding:14px 16px;font-family:monospace;font-size:1rem;letter-spacing:0.05em;word-break:break-all;margin-bottom:16px;">${code}</div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button id="rcCopyBtn" style="padding:8px 20px;background:#f59e0b;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Copy Code</button>
                <button id="rcCloseBtn" style="padding:8px 20px;background:#e5e7eb;color:#111;border:none;border-radius:6px;cursor:pointer;">I have saved it, close</button>
            </div>
            <p id="rcCopied" style="color:#16a34a;font-size:0.82rem;margin:8px 0 0;display:none;">&#10003; Copied to clipboard!</p>
        </div>
    `;
    document.body.appendChild(modal);

    document.getElementById('rcCopyBtn').addEventListener('click', function() {
        navigator.clipboard.writeText(code).then(() => {
            document.getElementById('rcCopied').style.display = 'block';
        });
    });
    document.getElementById('rcCloseBtn').addEventListener('click', function() {
        modal.remove();
        // Redirect to dashboard after first-time setup (cpOldPin only exists when encryption was already active)
        if (!document.getElementById('cpOldPin')) {
            window.location.href = '/insurance';
        }
    });
}

// ── Change PIN ───────────────────────────────────────────────
const btnChangePin = document.getElementById('btnChangePin');
if (btnChangePin) {
    btnChangePin.addEventListener('click', async () => {
        const oldPin   = document.getElementById('cpOldPin').value.trim();
        const newPin   = document.getElementById('cpNewPin').value.trim();
        const newPin2  = document.getElementById('cpNewPinConfirm').value.trim();
        const cpStatus  = document.getElementById('cpStatus');
        const cpSuccess = document.getElementById('cpSuccess');
        const cpError   = document.getElementById('cpError');
        const cpText    = document.getElementById('cpStatusText');

        cpSuccess.classList.add('d-none');
        cpError.classList.add('d-none');

        if (!oldPin) { cpError.textContent = 'Enter your current PIN.'; cpError.classList.remove('d-none'); return; }
        if (newPin.length < 8) { cpError.textContent = 'New PIN must be at least 8 characters.'; cpError.classList.remove('d-none'); return; }
        if (newPin !== newPin2) { cpError.textContent = 'New PINs do not match.'; cpError.classList.remove('d-none'); return; }

        cpStatus.classList.remove('d-none');
        btnChangePin.disabled = true;

        try {
            // 1. Fetch the current encrypted private key from server
            cpText.textContent = 'Fetching private key from server…';
            const keyResp = await fetch('{{ route("insurance.encryption.private-key") }}');
            if (!keyResp.ok) throw new Error('Could not fetch private key.');
            const keyBlob = await keyResp.json();

            // 2. Re-wrap: decrypt with old PIN then re-encrypt with new PIN
            //    (works at raw bytes level — no extractable:false issue)
            cpText.textContent = 'Re-wrapping key with new PIN…';
            const { encrypted, iv, salt } = await E2EEncryption.rewrapPrivateKey(
                keyBlob.encrypted_private_key, keyBlob.key_iv, keyBlob.key_salt,
                oldPin, newPin
            );

            // 4. Save — only the wrapped key changes, public key stays the same
            cpText.textContent = 'Saving to server…';
            const data = await postJSON('{{ route("insurance.encryption.change-pin") }}', {
                encrypted_private_key: encrypted,
                key_iv:                iv,
                key_salt:              salt,
            });

            cpStatus.classList.add('d-none');
            if (data.success) {
                cpSuccess.classList.remove('d-none');
                document.getElementById('cpOldPin').value        = '';
                document.getElementById('cpNewPin').value        = '';
                document.getElementById('cpNewPinConfirm').value = '';
            } else {
                throw new Error(data.message || 'Server error.');
            }
        } catch (err) {
            cpStatus.classList.add('d-none');
            cpError.textContent = err.message.includes('decrypt') || err.message.includes('operation')
                ? 'Wrong current PIN. Please try again.'
                : 'Error: ' + err.message;
            cpError.classList.remove('d-none');
        } finally {
            btnChangePin.disabled = false;
        }
    });
}

// ── Recover via Recovery Code (Forgot PIN) ────────────────────
const btnRecoverPin = document.getElementById('btnRecoverPin');
if (btnRecoverPin) {
    btnRecoverPin.addEventListener('click', async () => {
        const code     = document.getElementById('rcCode').value.trim();
        const newPin   = document.getElementById('rcNewPin').value.trim();
        const newPin2  = document.getElementById('rcNewPinConfirm').value.trim();
        const rcStatus  = document.getElementById('rcStatus');
        const rcSuccess = document.getElementById('rcSuccess');
        const rcError   = document.getElementById('rcError');
        const rcText    = document.getElementById('rcStatusText');
        const rcWarn    = document.getElementById('rcNoKeyWarning');

        rcSuccess.classList.add('d-none');
        rcError.classList.add('d-none');
        rcWarn.classList.add('d-none');

        if (!code) { rcError.textContent = 'Enter your recovery code.'; rcError.classList.remove('d-none'); return; }
        if (newPin.length < 8) { rcError.textContent = 'New PIN must be at least 8 characters.'; rcError.classList.remove('d-none'); return; }
        if (newPin !== newPin2) { rcError.textContent = 'New PINs do not match.'; rcError.classList.remove('d-none'); return; }

        rcStatus.classList.remove('d-none');
        btnRecoverPin.disabled = true;

        try {
            rcText.textContent = 'Fetching recovery key from server…';
            const resp = await fetch('{{ route("insurance.encryption.recovery-key") }}');
            if (resp.status === 404) {
                rcStatus.classList.add('d-none');
                const body = await resp.json();
                if (body.error && body.error.includes('No recovery key')) {
                    rcWarn.classList.remove('d-none');
                } else {
                    rcError.textContent = body.error || 'Recovery key not found.';
                    rcError.classList.remove('d-none');
                }
                return;
            }
            if (!resp.ok) throw new Error('Could not fetch recovery key.');
            const keyBlob = await resp.json();

            rcText.textContent = 'Re-wrapping private key with new PIN…';
            const { encrypted, iv, salt } = await E2EEncryption.rewrapPrivateKey(
                keyBlob.recovery_encrypted_private_key,
                keyBlob.recovery_key_iv,
                keyBlob.recovery_key_salt,
                code, newPin
            );

            rcText.textContent = 'Saving to server…';
            const data = await postJSON('{{ route("insurance.encryption.change-pin") }}', {
                encrypted_private_key: encrypted,
                key_iv:                iv,
                key_salt:              salt,
            });

            rcStatus.classList.add('d-none');
            if (data.success) {
                rcSuccess.classList.remove('d-none');
                document.getElementById('rcCode').value          = '';
                document.getElementById('rcNewPin').value         = '';
                document.getElementById('rcNewPinConfirm').value  = '';
            } else {
                throw new Error(data.message || 'Server error.');
            }
        } catch (err) {
            rcStatus.classList.add('d-none');
            rcError.textContent = err.message.includes('decrypt') || err.message.includes('operation')
                ? 'Wrong recovery code. Please check and try again.'
                : 'Error: ' + err.message;
            rcError.classList.remove('d-none');
        } finally {
            btnRecoverPin.disabled = false;
        }
    });
}
// ── PIN show/hide toggle ──────────────────────────────────────
document.querySelectorAll('.pin-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = document.getElementById(this.dataset.target);
        if (!input) return;
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bx-show', 'bx-hide');
        } else {
            input.type = 'password';
            icon.classList.replace('bx-hide', 'bx-show');
        }
    });
});
</script>

@endsection
