@extends('layouts.authentication')

@section('title', 'Forgot Password — etera')

@section('branding')
    <img src="{{ asset('assets/images/transparent.svg') }}" class="etera-auth-logo" alt="etera">
    <h2 class="etera-heading etera-heading-lg" style="text-align:center; margin-bottom: 0.5rem;">
        Reset Your Password
    </h2>
    <p class="etera-subtext" style="text-align:center; max-width: 360px;">
        Receive a one-time code via SMS or reset via Telegram.
    </p>
@endsection

@section('content')
<div id="forgot-password-app"></div>

<script>
    window.__ETERA__  = {
        csrfToken: @json(csrf_token()),
        forgotPasswordUrl: @json(url('/forgot-password')),
        forgotPasswordTelegramUrl: @json(url('/forgot-password-telegram')),
        smsForgotUrl: @json(url('/auth/forgot-password')),
        smsResetUrl: @json(url('/auth/reset-password')),
        loginUrl: '/login',
        oldEmail: @json(old('email', '')),
        flashSuccess: @json(session('success')),
        logoUrl: @json(asset('assets/images/transparent.svg')),
    };
</script>

@verbatim
<script type="text/babel">
    const { useState, useRef } = React;

    function LockIcon() {
        return (
            <svg width="44" height="44" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" viewBox="0 0 24 24" style={{ color: 'rgba(13, 148, 136, 0.8)' }}>
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        );
    }

    function EyeIcon({ open }) {
        if (open) {
            return (
                <svg width="20" height="20" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" viewBox="0 0 24 24">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            );
        }
        return (
            <svg width="20" height="20" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" viewBox="0 0 24 24">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
            </svg>
        );
    }

    /* ─────────────────────────────────────────────
       SMS Tab: multi-step OTP flow
    ───────────────────────────────────────────── */
    function SmsTab() {
        const data = window.__ETERA__;
        const [step, setStep] = useState(1);         // 1 = phone, 2 = otp+password, 3 = success
        const [phone, setPhone] = useState('');
        const [otp, setOtp] = useState('');
        const [password, setPassword] = useState('');
        const [confirmPassword, setConfirmPassword] = useState('');
        const [showPwd, setShowPwd] = useState(false);
        const [showConfirm, setShowConfirm] = useState(false);
        const [loading, setLoading] = useState(false);
        const [error, setError] = useState('');
        const [info, setInfo] = useState('');

        const passwordsMatch = confirmPassword && password === confirmPassword;
        const passwordsMismatch = confirmPassword && password !== confirmPassword;

        const sendOtp = async (e) => {
            e.preventDefault();
            setError('');
            setLoading(true);
            try {
                const res = await fetch(data.smsForgotUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': data.csrfToken,
                    },
                    body: JSON.stringify({ identifier: phone }),
                });
                const json = await res.json();
                if (json.success) {
                    setInfo('A 6-digit code has been sent to your phone.');
                    setStep(2);
                } else {
                    setError(json.message || 'Could not send code. Please try again.');
                }
            } catch (err) {
                setError('Network error. Please try again.');
            } finally {
                setLoading(false);
            }
        };

        const resetPassword = async (e) => {
            e.preventDefault();
            setError('');
            if (password !== confirmPassword) {
                setError('Passwords do not match.');
                return;
            }
            if (password.length !== 6) {
                setError('Password must be exactly 6 digits.');
                return;
            }
            setLoading(true);
            try {
                const res = await fetch(data.smsResetUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': data.csrfToken,
                    },
                    body: JSON.stringify({
                        identifier: phone,
                        otp: otp,
                        password: password,
                        password_confirmation: confirmPassword,
                    }),
                });
                const json = await res.json();
                if (json.success) {
                    setStep(3);
                } else {
                    setError(json.message || 'Invalid or expired code. Please try again.');
                }
            } catch (err) {
                setError('Network error. Please try again.');
            } finally {
                setLoading(false);
            }
        };

        const alertStyle = (type) => ({
            marginBottom: '1rem',
            padding: '0.85rem 1rem',
            borderRadius: '12px',
            fontSize: '0.9rem',
            background: type === 'error' ? 'rgba(239,68,68,0.09)' : 'rgba(16,185,129,0.09)',
            border: `1px solid ${type === 'error' ? 'rgba(239,68,68,0.3)' : 'rgba(16,185,129,0.3)'}`,
            color: type === 'error' ? '#991b1b' : 'rgba(6,95,70,0.95)',
        });

        if (step === 3) {
            return (
                <div style={{ textAlign: 'center', animation: 'etera-fade-in 0.5s ease-out' }}>
                    <div style={{ marginBottom: '1.25rem' }}>
                        <svg width="56" height="56" fill="none" stroke="#10b981" strokeWidth="1.5" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                    </div>
                    <h3 className="etera-heading" style={{ fontSize: '1.3rem', marginBottom: '0.5rem' }}>Password Reset!</h3>
                    <p className="etera-subtext" style={{ marginBottom: '1.5rem' }}>
                        Your password has been updated. You can now sign in.
                    </p>
                    <a href={data.loginUrl} className="etera-btn etera-btn-primary etera-btn-block" style={{ textAlign: 'center' }}>
                        Go to Login
                    </a>
                </div>
            );
        }

        if (step === 2) {
            return (
                <form onSubmit={resetPassword} style={{ animation: 'etera-fade-in 0.4s ease-out' }}>
                    {info && <div style={alertStyle('success')}>{info}</div>}
                    {error && <div style={alertStyle('error')}>{error}</div>}

                    <div className="etera-input-group">
                        <label>6-Digit Code</label>
                        <input
                            type="text"
                            inputMode="numeric"
                            className="etera-input"
                            placeholder="Enter the code sent to your phone"
                            value={otp}
                            onChange={(e) => setOtp(e.target.value.replace(/\D/g, '').slice(0, 6))}
                            maxLength={6}
                            required
                            autoFocus
                        />
                    </div>

                    <div className="etera-input-group">
                        <label>New Password <span style={{ color: '#6b7280', fontWeight: 400, fontSize: '0.8rem' }}>(6 digits)</span></label>
                        <div className="etera-password-wrapper">
                            <input
                                type={showPwd ? 'text' : 'password'}
                                className="etera-input"
                                placeholder="Enter new 6-digit password"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                minLength={6}
                                maxLength={6}
                                required
                            />
                            <button type="button" className="etera-password-toggle" onClick={() => setShowPwd(!showPwd)} tabIndex={-1}>
                                <EyeIcon open={showPwd} />
                            </button>
                        </div>
                    </div>

                    <div className="etera-input-group">
                        <label>Confirm Password</label>
                        <div className="etera-password-wrapper">
                            <input
                                type={showConfirm ? 'text' : 'password'}
                                className={`etera-input ${passwordsMismatch ? 'error' : ''}`}
                                placeholder="Confirm password"
                                value={confirmPassword}
                                onChange={(e) => setConfirmPassword(e.target.value)}
                                minLength={6}
                                maxLength={6}
                                required
                            />
                            <button type="button" className="etera-password-toggle" onClick={() => setShowConfirm(!showConfirm)} tabIndex={-1}>
                                <EyeIcon open={showConfirm} />
                            </button>
                        </div>
                        {passwordsMatch && <div style={{ color: '#10b981', fontSize: '0.8rem', marginTop: '4px' }}>✓ Passwords match</div>}
                        {passwordsMismatch && <div className="etera-error-text">Passwords do not match</div>}
                    </div>

                    <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                        <button
                            type="submit"
                            className={`etera-btn etera-btn-primary etera-btn-block ${loading ? 'etera-btn-loading' : ''}`}
                            disabled={loading}
                        >
                            {loading ? 'Resetting...' : 'Reset Password'}
                        </button>
                        <button
                            type="button"
                            className="etera-btn etera-btn-outline etera-btn-block"
                            onClick={() => { setStep(1); setError(''); setInfo(''); setOtp(''); setPassword(''); setConfirmPassword(''); }}
                        >
                            ← Change Phone Number
                        </button>
                    </div>
                </form>
            );
        }

        return (
            <form onSubmit={sendOtp} style={{ animation: 'etera-fade-in 0.4s ease-out' }}>
                {error && <div style={alertStyle('error')}>{error}</div>}

                <div className="etera-input-group">
                    <label>Phone Number</label>
                    <input
                        type="text"
                        inputMode="tel"
                        className="etera-input"
                        placeholder="09... or +251..."
                        value={phone}
                        onChange={(e) => setPhone(e.target.value)}
                        required
                        autoFocus
                    />
                    <div className="etera-subtext" style={{ marginTop: '6px', fontSize: '0.82rem' }}>
                        A 6-digit code will be sent to this number via SMS.
                    </div>
                </div>

                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                    <button
                        type="submit"
                        className={`etera-btn etera-btn-primary etera-btn-block ${loading ? 'etera-btn-loading' : ''}`}
                        disabled={loading}
                    >
                        {loading ? 'Sending Code...' : 'Send SMS Code'}
                    </button>
                    <a href={data.loginUrl} className="etera-btn etera-btn-outline etera-btn-block" style={{ textAlign: 'center' }}>
                        <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" viewBox="0 0 24 24" style={{ marginRight: '6px' }}>
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                        Back to Login
                    </a>
                </div>
            </form>
        );
    }

    /* ─────────────────────────────────────────────
       Telegram Tab: existing flow unchanged
    ───────────────────────────────────────────── */
    function TelegramTab() {
        const data = window.__ETERA__;
        const [phone, setPhone] = useState('');
        const [isSubmitting, setIsSubmitting] = useState(false);
        const [successMessage] = useState(data.flashSuccess || '');

        return (
            <form action={data.forgotPasswordTelegramUrl} method="POST" onSubmit={() => setIsSubmitting(true)}>
                <input type="hidden" name="_token" value={data.csrfToken} />

                {!!successMessage && (
                    <div style={{
                        marginBottom: '0.75rem',
                        padding: '0.85rem 1rem',
                        borderRadius: '14px',
                        background: 'rgba(16, 185, 129, 0.10)',
                        border: '1px solid rgba(16, 185, 129, 0.28)',
                        color: 'rgba(6, 95, 70, 0.95)',
                        fontSize: '0.95rem',
                    }}>
                        {successMessage}
                    </div>
                )}

                <div className="etera-input-group">
                    <label>Phone Number</label>
                    <input
                        type="text"
                        name="phone_number"
                        className="etera-input"
                        placeholder="09..."
                        value={phone}
                        onChange={(e) => setPhone(e.target.value)}
                        required
                    />
                    <div className="etera-subtext" style={{ marginTop: '6px', fontSize: '0.82rem' }}>
                        You must have Telegram connected to your account to receive the reset link.
                    </div>
                </div>

                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                    <button
                        type="submit"
                        className={`etera-btn etera-btn-primary etera-btn-block ${isSubmitting ? 'etera-btn-loading' : ''}`}
                        disabled={isSubmitting}
                    >
                        {isSubmitting ? 'Sending...' : 'Send Telegram Reset Link'}
                    </button>
                    <a href={data.loginUrl} className="etera-btn etera-btn-outline etera-btn-block" style={{ textAlign: 'center' }}>
                        <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" viewBox="0 0 24 24" style={{ marginRight: '6px' }}>
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                        Back to Login
                    </a>
                </div>
            </form>
        );
    }

    /* ─────────────────────────────────────────────
       Root Component with Tabs
    ───────────────────────────────────────────── */
    function ForgotPasswordForm() {
        const data = window.__ETERA__;
        const [tab, setTab] = useState('sms');

        const tabBase = {
            flex: 1,
            padding: '0.55rem 0',
            border: 'none',
            borderRadius: '10px',
            fontSize: '0.88rem',
            fontWeight: 600,
            cursor: 'pointer',
            transition: 'all 0.2s',
        };
        const activeTab = { ...tabBase, background: 'rgba(13,148,136,0.15)', color: 'rgb(13,148,136)', boxShadow: '0 1px 4px rgba(13,148,136,0.12)' };
        const inactiveTab = { ...tabBase, background: 'transparent', color: '#6b7280' };

        return (
            <div style={{ animation: 'etera-fade-in 0.6s ease-out' }}>
                <div style={{ textAlign: 'center', marginBottom: '1.5rem' }}>
                    <img src={data.logoUrl} alt="etera" style={{ maxWidth: '90px', marginBottom: '0.75rem' }} className="d-xl-none" />
                    <div style={{ marginBottom: '0.75rem' }}><LockIcon /></div>
                    <h2 className="etera-heading" style={{ fontSize: '1.5rem', marginBottom: '0.4rem' }}>Forgot Password?</h2>
                    <p className="etera-subtext" style={{ maxWidth: '300px', margin: '0 auto' }}>
                        Choose how you'd like to reset your password.
                    </p>
                </div>

                <div style={{ display: 'flex', gap: '0.4rem', background: 'rgba(0,0,0,0.04)', borderRadius: '12px', padding: '0.3rem', marginBottom: '1.5rem' }}>
                    <button style={tab === 'sms' ? activeTab : inactiveTab} onClick={() => setTab('sms')} type="button">
                        📱 Via SMS
                    </button>
                    <button style={tab === 'telegram' ? activeTab : inactiveTab} onClick={() => setTab('telegram')} type="button">
                        ✈️ Via Telegram
                    </button>
                </div>

                {tab === 'sms' ? <SmsTab /> : <TelegramTab />}
            </div>
        );
    }

    // Mount
    const root = document.getElementById('forgot-password-app');
    if (root) {
        ReactDOM.createRoot(root).render(<ForgotPasswordForm />);
    }
</script>
@endverbatim
@endsection