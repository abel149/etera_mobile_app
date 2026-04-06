@extends('layouts.authentication')

@section('title', 'Reset Password — etera')

@section('branding')
    <img src="{{ asset('assets/images/transparent.svg') }}" class="etera-auth-logo" alt="etera">
    <h2 class="etera-heading etera-heading-lg" style="text-align:center; margin-bottom: 0.5rem;">
        Create New Password
    </h2>
    <p class="etera-subtext" style="text-align:center; max-width: 360px;">
        Choose a strong password to protect your account. Your password must be exactly 6 digits.
    </p>
@endsection

@section('content')
<div id="reset-password-app"></div>

<script>
    window.__ETERA__  = {
        csrfToken: @json(csrf_token()),
        resetPasswordUrl: @json(url('/reset-password')),
        loginUrl: '/login',
        token: @json($token ?? request('token')),
        email: @json($email ?? request('email')),
        logoUrl: @json(asset('assets/images/transparent.svg')),
    };
</script>

@verbatim
<script type="text/babel">
    const { useState } = React;

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

    function getPasswordStrength(pwd) {
        if (!pwd) return { level: 0, label: '', color: '' };
        let score = 0;
        if (pwd.length >= 4) score++;
        if (pwd.length >= 6) score++;
        if (/[A-Z]/.test(pwd)) score++;
        if (/[0-9]/.test(pwd)) score++;
        if (/[^A-Za-z0-9]/.test(pwd)) score++;

        if (score <= 2) return { level: 1, label: 'Weak', color: 'weak' };
        if (score <= 3) return { level: 2, label: 'Medium', color: 'medium' };
        return { level: 3, label: 'Strong', color: 'strong' };
    }

    function ResetPasswordForm() {
        const data = window.__ETERA__ ;
        const [password, setPassword] = useState('');
        const [confirmPassword, setConfirmPassword] = useState('');
        const [showPwd, setShowPwd] = useState(false);
        const [showConfirm, setShowConfirm] = useState(false);
        const [isSubmitting, setIsSubmitting] = useState(false);

        const strength = getPasswordStrength(password);
        const passwordsMatch = confirmPassword && password === confirmPassword;
        const passwordsMismatch = confirmPassword && password !== confirmPassword;

        const handleSubmit = () => {
            setIsSubmitting(true);
        };

        return (
            <div style={{ animation: 'etera-fade-in 0.6s ease-out' }}>
                <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
                    <img src={data.logoUrl} alt="etera" style={{ maxWidth: '100px', marginBottom: '1rem' }} className="d-xl-none" />
                    <div style={{ marginBottom: '0.5rem' }}>
                        <svg width="48" height="48" fill="none" stroke="currentColor" strokeWidth="1.5" viewBox="0 0 24 24" style={{ color: 'rgba(13, 148, 136, 0.8)' }}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"/>
                        </svg>
                    </div>
                    <h2 className="etera-heading" style={{ fontSize: '1.5rem', marginBottom: '0.5rem' }}>
                        Create New Password
                    </h2>
                    <p className="etera-subtext" style={{ maxWidth: '320px', margin: '0 auto' }}>
                        Enter your new password (6 digits)
                    </p>
                </div>

                <form action={data.resetPasswordUrl} method="POST" onSubmit={handleSubmit}>
                    <input type="hidden" name="_token" value={data.csrfToken} />
                    <input type="hidden" name="token" value={data.token} />
                    <input type="hidden" name="email" value={data.email} />

                    <div className="etera-input-group">
                        <label>New Password</label>
                        <div className="etera-password-wrapper">
                            <input
                                type={showPwd ? 'text' : 'password'}
                                name="password"
                                className="etera-input"
                                placeholder="Enter new password"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                minLength="6"
                                maxLength="6"
                                required
                                autoFocus
                            />
                            <button type="button" className="etera-password-toggle" onClick={() => setShowPwd(!showPwd)} tabIndex={-1}>
                                <EyeIcon open={showPwd} />
                            </button>
                        </div>
                        {password && (
                            <>
                                <div className="etera-pwd-strength">
                                    {[1, 2, 3].map(i => (
                                        <div key={i} className={`etera-pwd-bar ${i <= strength.level ? `active ${strength.color}` : ''}`}></div>
                                    ))}
                                </div>
                                <div className={`etera-pwd-label`} style={{ color: strength.color === 'weak' ? '#ef4444' : strength.color === 'medium' ? '#f59e0b' : '#10b981' }}>
                                    {strength.label}
                                </div>
                            </>
                        )}
                    </div>

                    <div className="etera-input-group">
                        <label>Confirm Password</label>
                        <div className="etera-password-wrapper">
                            <input
                                type={showConfirm ? 'text' : 'password'}
                                name="password_confirmation"
                                className={`etera-input ${passwordsMismatch ? 'error' : ''}`}
                                placeholder="Confirm password"
                                value={confirmPassword}
                                onChange={(e) => setConfirmPassword(e.target.value)}
                                minLength="6"
                                maxLength="6"
                                required
                            />
                            <button type="button" className="etera-password-toggle" onClick={() => setShowConfirm(!showConfirm)} tabIndex={-1}>
                                <EyeIcon open={showConfirm} />
                            </button>
                        </div>
                        {passwordsMatch && (
                            <div style={{ color: '#10b981', fontSize: '0.8rem', marginTop: '4px' }}>✓ Passwords match</div>
                        )}
                        {passwordsMismatch && (
                            <div className="etera-error-text">Passwords do not match</div>
                        )}
                    </div>

                    <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                        <button
                            type="submit"
                            className={`etera-btn etera-btn-primary etera-btn-block ${isSubmitting ? 'etera-btn-loading' : ''}`}
                            disabled={isSubmitting}
                        >
                            {isSubmitting ? 'Resetting...' : 'Change Password'}
                        </button>

                        <a href={data.loginUrl} className="etera-btn etera-btn-outline etera-btn-block" style={{ textAlign: 'center' }}>
                            Back to Login
                        </a>
                    </div>
                </form>
            </div>
        );
    }

    // Mount
    const root = document.getElementById('reset-password-app');
    if (root) {
        ReactDOM.createRoot(root).render(<ResetPasswordForm />);
    }
</script>
@endverbatim
@endsection