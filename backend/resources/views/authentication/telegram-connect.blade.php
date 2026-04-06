@extends('layouts.authentication')

@section('title', 'Connect Telegram — etera')

@section('branding')
<div class="text-center">
    <img src="{{ asset('assets/images/transparent.svg') }}" alt="etera" style="width: 120px; border-radius: 20px;" class="mb-3">
    <h2 style="color: #fff; font-weight: 700;">Connect Telegram</h2>
    <p style="color: rgba(255,255,255,0.85);">Get instant notifications on your phone</p>
</div>
@endsection

@section('content')
<style>
    .tg-connect-wrapper {
        text-align: center;
        padding: 1rem 0;
    }
    .tg-icon-wrapper {
        width: 88px;
        height: 88px;
        margin: 0 auto 1.5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #0088cc15, #0088cc25);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: tg-pulse 2s ease-in-out infinite;
    }
    .tg-icon-wrapper i {
        font-size: 44px;
        color: #0088cc;
    }
    @keyframes tg-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(0,136,204,0.2); }
        50% { box-shadow: 0 0 0 16px rgba(0,136,204,0); }
    }
    .tg-title {
        font-weight: 700;
        font-size: 1.35rem;
        color: #1a1a2e;
        margin-bottom: 0.5rem;
    }
    .tg-subtitle {
        color: #6b7280;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1.75rem;
        max-width: 340px;
        margin-left: auto;
        margin-right: auto;
    }
    .tg-btn-connect {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 14px 24px;
        background: linear-gradient(135deg, #0088cc, #00aaee);
        color: #fff;
        border: none;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: 0 4px 18px rgba(0,136,204,0.3);
    }
    .tg-btn-connect:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(0,136,204,0.4);
        color: #fff;
    }
    .tg-btn-connect:active {
        transform: translateY(0);
    }
    .tg-btn-connect i {
        font-size: 1.3rem;
    }
    .tg-btn-skip {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 12px 24px;
        background: transparent;
        color: #6b7280;
        border: 2px solid #e5e7eb;
        border-radius: 50px;
        font-size: 0.95rem;
        font-weight: 500;
        font-family: 'Inter', sans-serif;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .tg-btn-skip:hover {
        border-color: #28a745;
        color: #28a745;
        background: rgba(40,167,69,0.04);
    }
    .tg-buttons {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 1.5rem;
    }
    .tg-steps {
        text-align: left;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 14px;
        padding: 1rem 1.25rem;
        margin-bottom: 0;
    }
    .tg-steps-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: #0284c7;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.6rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .tg-step {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
        color: #475569;
        line-height: 1.5;
    }
    .tg-step:last-child {
        margin-bottom: 0;
    }
    .tg-step-num {
        flex-shrink: 0;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0088cc, #00aaee);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 1px;
    }

    /* Mobile adjustments */
    @media (max-width: 480px) {
        .tg-connect-wrapper {
            padding: 0.5rem 0;
        }
        .tg-icon-wrapper {
            width: 72px;
            height: 72px;
            margin-bottom: 1rem;
        }
        .tg-icon-wrapper i {
            font-size: 36px;
        }
        .tg-title {
            font-size: 1.2rem;
        }
        .tg-subtitle {
            font-size: 0.88rem;
            margin-bottom: 1.25rem;
        }
        .tg-btn-connect {
            padding: 13px 20px;
            font-size: 0.95rem;
        }
        .tg-btn-skip {
            padding: 11px 20px;
            font-size: 0.9rem;
        }
        .tg-steps {
            padding: 0.85rem 1rem;
        }
    }
</style>

<div class="tg-connect-wrapper">
    {{-- Telegram Icon --}}
    <div class="tg-icon-wrapper">
        <i class="bx bxl-telegram"></i>
    </div>

    {{-- Heading --}}
    <h4 class="tg-title">Connect Your Telegram</h4>
    <p class="tg-subtitle">
        Link your Telegram account to receive <strong>instant proforma notifications</strong> directly on your phone.
    </p>

    {{-- Buttons --}}
    <div class="tg-buttons">
        <a href="{{ $telegramLink }}" target="_blank" class="tg-btn-connect">
            <i class="bx bxl-telegram"></i> Open Telegram & Connect
        </a>

        <a href="{{ $skipUrl }}" class="tg-btn-skip">
            Skip for Now
        </a>
    </div>

    {{-- Steps Guide --}}
    <div class="tg-steps">
        <div class="tg-steps-title">
            <i class="bx bx-info-circle"></i> How it works
        </div>
        <div class="tg-step">
            <span class="tg-step-num">1</span>
            <span>Tap <strong>"Open Telegram & Connect"</strong> above</span>
        </div>
        <div class="tg-step">
            <span class="tg-step-num">2</span>
            <span>Press <strong>"Start"</strong> in the Telegram chat</span>
        </div>
        <div class="tg-step">
            <span class="tg-step-num">3</span>
            <span>Done! You'll receive notifications instantly</span>
        </div>
    </div>
</div>
@endsection
