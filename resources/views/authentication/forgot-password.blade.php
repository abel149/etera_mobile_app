@extends('layouts.authentication')

@section('title', 'Forgot Password — etera')

@section('branding')
    <img src="{{ asset('assets/images/transparent.svg') }}" class="etera-auth-logo" alt="etera">
    <h2 class="etera-heading etera-heading-lg" style="text-align:center; margin-bottom: 0.5rem;">
        Reset Your Password
    </h2>
    <p class="etera-subtext" style="text-align:center; max-width: 360px;">
        Enter your registered phone number and we'll send you a reset link via Telegram.
    </p>
@endsection

@section('content')
<div id="forgot-password-app"></div>

<script>
    window.__ETERA__  = {
        csrfToken: @json(csrf_token()),
        forgotPasswordUrl: @json(url('/forgot-password')),
        forgotPasswordTelegramUrl: @json(url('/forgot-password-telegram')),
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
            <svg width="48" height="48" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" viewBox="0 0 24 24" style={{ color: 'rgba(13, 148, 136, 0.8)' }}>
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        );
    }

    function ForgotPasswordForm() {
        const data = window.__ETERA__ ;
        const [phone, setPhone] = useState('');
        const [isSubmitting, setIsSubmitting] = useState(false);
        const [successMessage] = useState(data.flashSuccess || '');

        const handleSubmit = () => {
            setIsSubmitting(true);
        };

        return (
            <div style={{ animation: 'etera-fade-in 0.6s ease-out' }}>
                <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
                    <img src={data.logoUrl} alt="etera" style={{ maxWidth: '100px', marginBottom: '1rem' }} className="d-xl-none" />
                    <div style={{ marginBottom: '1rem' }}>
                        <LockIcon />
                    </div>
                    <h2 className="etera-heading" style={{ fontSize: '1.5rem', marginBottom: '0.5rem' }}>
                        Forgot Password?
                    </h2>
                    <p className="etera-subtext" style={{ maxWidth: '320px', margin: '0 auto' }}>
                        Enter your registered phone number to receive a password reset link
                    </p>
                </div>

                <form action={data.forgotPasswordTelegramUrl} method="POST" onSubmit={handleSubmit}>
                    <input type="hidden" name="_token" value={data.csrfToken} />

                    {!!successMessage && (
                        <div
                            style={{
                                marginBottom: '0.75rem',
                                padding: '0.85rem 1rem',
                                borderRadius: '14px',
                                background: 'rgba(16, 185, 129, 0.10)',
                                border: '1px solid rgba(16, 185, 129, 0.28)',
                                color: 'rgba(6, 95, 70, 0.95)',
                                fontSize: '0.95rem',
                            }}
                        >
                            {successMessage || 'Telegram reset notification sent.'}
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
                            autoFocus
                        />
                        <div className="etera-subtext" style={{ marginTop: '6px', fontSize: '0.85rem' }}>
                            You must have Telegram connected to your account.
                        </div>
                    </div>

                    <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem', marginTop: '0.5rem' }}>
                        <button
                            type="submit"
                            className={`etera-btn etera-btn-primary etera-btn-block ${isSubmitting ? 'etera-btn-loading' : ''}`}
                            disabled={isSubmitting}
                        >
                            {isSubmitting ? 'Sending...' : 'Send Telegram Reset Link'}
                        </button>

                        <a href={data.loginUrl} className="etera-btn etera-btn-outline etera-btn-block" style={{ textAlign: 'center' }}>
                            <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" viewBox="0 0 24 24">
                                <line x1="19" y1="12" x2="5" y2="12"/>
                                <polyline points="12 19 5 12 12 5"/>
                            </svg>
                            Back to Login
                        </a>
                    </div>
                </form>
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