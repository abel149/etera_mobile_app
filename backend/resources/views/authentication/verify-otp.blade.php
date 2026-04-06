@extends('layouts.authentication')

@section('title', 'Verify Email — etera')

@section('branding')
    <img src="{{ asset('assets/images/transparent.svg') }}" class="etera-auth-logo" alt="etera">
    <h2 class="etera-heading etera-heading-lg" style="text-align:center; margin-bottom: 0.5rem;">
        Verify Your Email
    </h2>
    <p class="etera-subtext" style="text-align:center; max-width: 360px;">
        We've sent a 6-digit verification code to your email. Check your inbox and enter the code below.
    </p>
@endsection

@section('content')
<div id="verify-otp-app"></div>

<script>
    window.__ETERA__  = {
        csrfToken: @json(csrf_token()),
        verifyOtpUrl: @json(url('/verify-otp')),
        resendOtpUrl: @json(url('/resend-otp')),
        loginUrl: '/login',
        otpEmail: @json(session('otp_email', '')),
        logoUrl: @json(asset('assets/images/transparent.svg')),
    };
</script>

@verbatim
<script type="text/babel">
    const { useState, useEffect, useRef, useCallback } = React;

    function OtpInput({ value, onChange, onPaste }) {
        const inputRefs = useRef([]);

        const handleChange = (index, e) => {
            const val = e.target.value;
            if (!/^\d*$/.test(val)) return; // digits only

            const newValue = value.split('');
            newValue[index] = val.slice(-1);
            const newOtp = newValue.join('');
            onChange(newOtp);

            // Auto-focus next input
            if (val && index < 5) {
                inputRefs.current[index + 1]?.focus();
            }
        };

        const handleKeyDown = (index, e) => {
            if (e.key === 'Backspace' && !value[index] && index > 0) {
                inputRefs.current[index - 1]?.focus();
            }
        };

        const handlePasteEvent = (e) => {
            e.preventDefault();
            const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
            if (pasted.length > 0) {
                onChange(pasted.padEnd(6, '').slice(0, 6));
                const focusIdx = Math.min(pasted.length, 5);
                inputRefs.current[focusIdx]?.focus();
            }
        };

        return (
            <div className="etera-otp-group" onPaste={handlePasteEvent}>
                {[0, 1, 2, 3, 4, 5].map((i) => (
                    <input
                        key={i}
                        ref={(el) => inputRefs.current[i] = el}
                        type="text"
                        inputMode="numeric"
                        maxLength="1"
                        className="etera-otp-input"
                        value={value[i] || ''}
                        onChange={(e) => handleChange(i, e)}
                        onKeyDown={(e) => handleKeyDown(i, e)}
                        autoFocus={i === 0}
                    />
                ))}
            </div>
        );
    }

    function VerifyOtpPage() {
        const data = window.__ETERA__ ;
        const [otp, setOtp] = useState('');
        const [isSubmitting, setIsSubmitting] = useState(false);
        const [countdown, setCountdown] = useState(60);
        const [canResend, setCanResend] = useState(false);
        const formRef = useRef(null);

        // Countdown timer
        useEffect(() => {
            if (countdown <= 0) {
                setCanResend(true);
                return;
            }
            const timer = setInterval(() => {
                setCountdown((prev) => prev - 1);
            }, 1000);
            return () => clearInterval(timer);
        }, [countdown]);

        const handleSubmit = () => {
            setIsSubmitting(true);
        };

        const maskedEmail = data.otpEmail 
            ? data.otpEmail.replace(/(.{2})(.*)(@.*)/, '$1***$3')
            : 'your email';

        return (
            <div style={{ animation: 'etera-fade-in 0.6s ease-out' }}>
                <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
                    <img src={data.logoUrl} alt="etera" style={{ maxWidth: '100px', marginBottom: '1rem' }} className="d-xl-none" />
                    <div style={{ marginBottom: '0.75rem' }}>
                        <svg width="48" height="48" fill="none" stroke="currentColor" strokeWidth="1.5" viewBox="0 0 24 24" style={{ color: 'rgba(13, 148, 136, 0.8)' }}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                    </div>
                    <h2 className="etera-heading" style={{ fontSize: '1.5rem', marginBottom: '0.5rem' }}>
                        Verify Your Email
                    </h2>
                    <p className="etera-subtext" style={{ maxWidth: '320px', margin: '0 auto' }}>
                        We sent a 6-digit code to <span style={{ color: 'var(--etera-teal-light)', fontWeight: 600 }}>{maskedEmail}</span>
                    </p>
                </div>

                <form action={data.verifyOtpUrl} method="POST" onSubmit={handleSubmit} ref={formRef}>
                    <input type="hidden" name="_token" value={data.csrfToken} />
                    <input type="hidden" name="email" value={data.otpEmail} />
                    <input type="hidden" name="otp" value={otp} />

                    <OtpInput value={otp} onChange={setOtp} />

                    <button
                        type="submit"
                        className={`etera-btn etera-btn-primary etera-btn-block etera-btn-lg ${isSubmitting ? 'etera-btn-loading' : ''}`}
                        disabled={isSubmitting || otp.length < 6}
                        style={{ marginTop: '0.5rem' }}
                    >
                        {isSubmitting ? 'Verifying...' : 'Verify'}
                    </button>
                </form>

                <div style={{ textAlign: 'center', marginTop: '1.5rem' }}>
                    {canResend ? (
                        <form action={data.resendOtpUrl} method="POST" style={{ display: 'inline' }}>
                            <input type="hidden" name="_token" value={data.csrfToken} />
                            <input type="hidden" name="email" value={data.otpEmail} />
                            <button type="submit" className="etera-link" style={{ background: 'none', border: 'none', cursor: 'pointer', fontSize: '0.9rem' }}>
                                Didn't receive the code? <strong>Resend OTP</strong>
                            </button>
                        </form>
                    ) : (
                        <p className="etera-countdown">
                            Resend code in <span>{countdown}s</span>
                        </p>
                    )}
                </div>

                <div style={{ textAlign: 'center', marginTop: '1rem' }}>
                    <a href={data.loginUrl} className="etera-link" style={{ fontSize: '0.85rem' }}>
                        ← Back to Login
                    </a>
                </div>
            </div>
        );
    }

    // Mount
    const root = document.getElementById('verify-otp-app');
    if (root) {
        ReactDOM.createRoot(root).render(<VerifyOtpPage />);
    }
</script>
@endverbatim
@endsection
