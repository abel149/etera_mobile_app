@extends('layouts.authentication')

@section('title', 'Login — etera')

@section('branding')
    <img src="{{ asset('assets/images/transparent.svg') }}" class="etera-auth-logo" alt="etera">
    <h2 class="etera-heading etera-heading-lg" style="text-align:center; margin-bottom: 0.5rem;">
        One Platform, All Auto Brands.
    </h2>
    <p class="etera-subtext" style="text-align:center; max-width: 360px; color: rgba(255,255,255,0.85);">
        Sign in to manage your account, track proformas, and receive notifications.
    </p>
@endsection

@section('content')
<div id="login-app"></div>

<script>
    window.__ETERA__  = {
        csrfToken: @json(csrf_token()),
        loginUrl: @json(route('login')),
        signupUrl: '/signup',
        forgotPasswordUrl: '/forgot-password',
        reviewUrl: '/review',
        oldInput: @json(old('email_or_phone', '')),
        errors: @json($errors->toArray()),
        logoUrl: @json(asset('assets/images/transparent.svg')),
        sessionBlocked: @json(session('session_blocked', false)),
        forceLogoutUrl: @json(route('force-logout-other-devices')),
        successMessage: @json(session('success', '')),
    };
</script>

@verbatim
<script type="text/babel">
    const { useState, useRef } = React;

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

    function LoginForm() {
        const data = window.__ETERA__ ;
        const [emailOrPhone, setEmailOrPhone] = useState(data.oldInput);
        const [password, setPassword] = useState('');
        const [showPassword, setShowPassword] = useState(false);
        const [remember, setRemember] = useState(false);
        const [isSubmitting, setIsSubmitting] = useState(false);
        const [passwordError, setPasswordError] = useState('');
        const formRef = useRef(null);

        const hasError = (field) => data.errors && data.errors[field];
        const getError = (field) => data.errors && data.errors[field] ? data.errors[field][0] : '';

        const handleSubmit = (e) => {
            // Client-side password validation: must be at least 6 characters
            if (password.length < 6) {
                e.preventDefault();
                setPasswordError('Password must be at least 6 characters.');
                return;
            }
            setPasswordError('');
            setIsSubmitting(true);
            // Let the form submit normally to Laravel
        };

        return (
            <div style={{ animation: 'etera-fade-in 0.6s ease-out' }}>
                <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
                    <img src={data.logoUrl} alt="etera" style={{ maxWidth: '120px', marginBottom: '1rem' }} className="d-xl-none" />
                    <h2 className="etera-heading" style={{ fontSize: '1.5rem', marginBottom: '0.5rem' }}>Welcome Back</h2>
                    <p className="etera-subtext">Sign in to your etera account</p>
                    {data.successMessage && (
                        <div style={{ background: '#d4edda', color: '#155724', padding: '0.75rem 1rem', borderRadius: '8px', marginTop: '0.75rem', fontSize: '0.9rem' }}>
                            {data.successMessage}
                        </div>
                    )}
                </div>

                <form ref={formRef} action={data.loginUrl} method="POST" onSubmit={handleSubmit}>
                    <input type="hidden" name="_token" value={data.csrfToken} />

                    <div className="etera-input-group">
                        <label>Email or Phone Number</label>
                        <input
                            type="text"
                            name="email_or_phone"
                            className={`etera-input ${hasError('email_or_phone') ? 'error' : ''}`}
                            placeholder="john@etera.com or 0940000000"
                            value={emailOrPhone}
                            onChange={(e) => setEmailOrPhone(e.target.value)}
                            autoFocus
                            required
                        />
                        {hasError('email_or_phone') && (
                            <div className="etera-error-text">{getError('email_or_phone')}</div>
                        )}
                        {data.sessionBlocked && hasError('email_or_phone') && (
                            <div style={{ marginTop: '0.5rem' }}>
                                <button
                                    type="button"
                                    className="etera-btn etera-btn-block"
                                    style={{ background: '#e67e22', color: '#fff', padding: '0.6rem', borderRadius: '8px', border: 'none', cursor: 'pointer', fontWeight: 600, fontSize: '0.85rem' }}
                                    onClick={() => {
                                        if (!emailOrPhone || !password) {
                                            alert('Please enter your credentials first.');
                                            return;
                                        }
                                        const form = document.createElement('form');
                                        form.method = 'POST';
                                        form.action = data.forceLogoutUrl;
                                        const tokenInput = document.createElement('input');
                                        tokenInput.type = 'hidden'; tokenInput.name = '_token'; tokenInput.value = data.csrfToken;
                                        const emailInput = document.createElement('input');
                                        emailInput.type = 'hidden'; emailInput.name = 'email_or_phone'; emailInput.value = emailOrPhone;
                                        const pwInput = document.createElement('input');
                                        pwInput.type = 'hidden'; pwInput.name = 'password'; pwInput.value = password;
                                        form.appendChild(tokenInput);
                                        form.appendChild(emailInput);
                                        form.appendChild(pwInput);
                                        document.body.appendChild(form);
                                        form.submit();
                                    }}
                                >
                                    🔒 Logout Other Devices & Sign In
                                </button>
                            </div>
                        )}
                    </div>

                    <div className="etera-input-group">
                        <label>Password</label>
                        <div className="etera-password-wrapper">
                            <input
                                type={showPassword ? 'text' : 'password'}
                                name="password"
                                className={`etera-input ${passwordError ? 'error' : ''}`}
                                placeholder="Enter your password"
                                value={password}
                                onChange={(e) => { setPassword(e.target.value); if (e.target.value.length >= 6) setPasswordError(''); }}
                                minLength={6}
                                required
                            />
                            <button
                                type="button"
                                className="etera-password-toggle"
                                onClick={() => setShowPassword(!showPassword)}
                                tabIndex={-1}
                            >
                                <EyeIcon open={showPassword} />
                            </button>
                        </div>
                        {passwordError && (
                            <div className="etera-error-text">{passwordError}</div>
                        )}
                        {!passwordError && hasError('password') && (
                            <div className="etera-error-text">{getError('password')}</div>
                        )}
                    </div>

                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '1.5rem' }}>
                        <label className="etera-toggle">
                            <input
                                type="checkbox"
                                name="remember"
                                checked={remember}
                                onChange={(e) => setRemember(e.target.checked)}
                            />
                            <span>Remember me</span>
                        </label>
                        <a href={data.forgotPasswordUrl} className="etera-link" style={{ fontSize: '0.85rem' }}>
                            Forgot Password?
                        </a>
                    </div>

                    <button
                        type="submit"
                        className={`etera-btn etera-btn-primary etera-btn-block etera-btn-lg ${isSubmitting ? 'etera-btn-loading' : ''}`}
                        disabled={isSubmitting}
                    >
                        {isSubmitting ? 'Signing in...' : 'Sign In'}
                    </button>
                </form>

                <div style={{ textAlign: 'center', marginTop: '1.5rem' }}>
                    <p className="etera-subtext" style={{ fontSize: '0.9rem' }}>
                        Don't have an account?{' '}
                        <a href={data.signupUrl} className="etera-link">Sign up here</a>
                    </p>
                </div>

                <div className="etera-divider">or</div>

                <div style={{ textAlign: 'center' }}>
                    <p className="etera-subtext" style={{ fontSize: '0.85rem' }}>
                        Rate and Review Garages{' '}
                        <a href={data.reviewUrl} className="etera-link">here</a>
                    </p>
                </div>
            </div>
        );
    }

    // Mount
    const loginRoot = document.getElementById('login-app');
    if (loginRoot) {
        ReactDOM.createRoot(loginRoot).render(<LoginForm />);
    }
</script>
@endverbatim
@endsection
