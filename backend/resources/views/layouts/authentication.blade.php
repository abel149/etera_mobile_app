<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <link rel="icon" href="{{ asset('assets/images/transparent.svg') }}" type="image/jpeg" />

    <!-- etera Modern Design System -->
    <link href="{{ asset('assets/css/etera-modern.css') }}" rel="stylesheet">

    <!-- etera White & Green Auth Theme -->
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    :root {
        --etera-green:#28a745;--etera-green-dark:#1e7e34;--etera-green-light:#43a047;
        --etera-teal:#28a745;--etera-teal-light:#43a047;
        --etera-gradient:linear-gradient(135deg,#28a745 0%,#20c997 100%);
        --etera-bg-gradient:linear-gradient(135deg,#ffffff 0%,#f1f8e9 50%,#e8f5e9 100%);
        --etera-text:#1a1a2e;--etera-text-muted:#6b7280;--etera-text-soft:#374151;
        --etera-glass-bg:rgba(255,255,255,0.92);--etera-glass-border:rgba(40,167,69,0.2);
        --etera-shadow:0 8px 32px rgba(40,167,69,0.12);--etera-radius:16px;--etera-radius-sm:10px;
        --etera-radius-pill:50px;--etera-transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .etera-dark-body{font-family:'Inter',sans-serif;background:var(--etera-bg-gradient);color:var(--etera-text);min-height:100vh;margin:0;overflow-x:hidden}
    .etera-auth-wrapper{display:flex;min-height:100vh;position:relative;z-index:1}
    .etera-auth-branding{flex:0 0 45%;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:3rem;position:relative;overflow:hidden;background:linear-gradient(135deg,#1b5e20,#2e7d32,#43a047)}
    .etera-auth-branding .etera-heading{color:#fff;-webkit-text-fill-color:#fff;background:none}
    .etera-auth-form-side{flex:1;display:flex;align-items:center;justify-content:center;padding:2rem;background:linear-gradient(180deg,#ffffff 0%,#f9fafb 100%)}
    .etera-auth-card{width:100%;max-width:520px;padding:2.75rem}
    .etera-auth-logo{max-width:200px;margin-bottom:2rem;animation:etera-float 3s ease-in-out infinite}
    .etera-glass-card{background:var(--etera-glass-bg);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid var(--etera-glass-border);border-radius:var(--etera-radius);box-shadow:var(--etera-shadow)}
    .etera-heading{font-weight:800;background:var(--etera-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .etera-subtext{color:var(--etera-text-muted);font-size:1rem;line-height:1.6}
    .etera-input-group{position:relative;margin-bottom:1.25rem}
    .etera-input-group label{display:block;font-size:.85rem;font-weight:600;color:var(--etera-text-soft);margin-bottom:6px}
    .etera-input{width:100%;padding:14px 16px;background:#f9fafb;border:1px solid #d1d5db;border-radius:var(--etera-radius-sm);color:#1a1a2e;font-size:1rem;font-family:'Inter',sans-serif;transition:var(--etera-transition);outline:none;box-sizing:border-box}
    .etera-input::placeholder{color:#9ca3af}
    .etera-input:focus{border-color:var(--etera-green);box-shadow:0 0 0 3px rgba(40,167,69,0.15);background:#fff}
    .etera-input.error{border-color:#dc3545;box-shadow:0 0 0 3px rgba(220,53,69,0.1)}
    .etera-password-wrapper{position:relative}
    .etera-password-wrapper .etera-input{padding-right:48px}
    .etera-password-toggle{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--etera-text-muted);cursor:pointer;padding:4px;line-height:1}
    .etera-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 32px;border-radius:var(--etera-radius-pill);font-size:1rem;font-weight:600;font-family:'Inter',sans-serif;text-decoration:none;transition:var(--etera-transition);cursor:pointer;border:none;position:relative;overflow:hidden}
    .etera-btn-primary{background:var(--etera-gradient);color:#fff;box-shadow:0 4px 20px rgba(40,167,69,0.35)}
    .etera-btn-primary:hover{transform:translateY(-3px);box-shadow:0 8px 30px rgba(40,167,69,0.5);color:#fff}
    .etera-btn-block{width:100%}
    .etera-btn-lg{padding:16px 40px;font-size:1.1rem}
    .etera-btn-loading{pointer-events:none;opacity:0.8}
    .etera-btn-loading::after{content:'';width:18px;height:18px;border:2px solid transparent;border-top-color:currentColor;border-radius:50%;animation:spin 0.6s linear infinite;margin-left:8px}
    @keyframes spin{to{transform:rotate(360deg)}}
    .etera-link{color:var(--etera-green);text-decoration:none;font-weight:600;transition:var(--etera-transition)}
    .etera-link:hover{color:var(--etera-green-dark);text-shadow:none}
    .etera-toggle{display:flex;align-items:center;gap:10px;cursor:pointer;font-size:.9rem;color:var(--etera-text-muted)}
    .etera-toggle input[type="checkbox"]{width:40px;height:22px;appearance:none;background:#d1d5db;border-radius:11px;position:relative;cursor:pointer;transition:var(--etera-transition)}
    .etera-toggle input[type="checkbox"]::after{content:'';position:absolute;width:16px;height:16px;background:#fff;border-radius:50%;top:3px;left:3px;transition:var(--etera-transition)}
    .etera-toggle input[type="checkbox"]:checked{background:var(--etera-gradient)}
    .etera-toggle input[type="checkbox"]:checked::after{left:21px}
    .etera-divider{display:flex;align-items:center;gap:16px;margin:1.5rem 0;color:var(--etera-text-muted);font-size:.85rem}
    .etera-divider::before,.etera-divider::after{content:'';flex:1;height:1px;background:#e5e7eb}
    .etera-error-text{color:#dc3545;font-size:.8rem;margin-top:4px}
    .etera-welcome-banner{display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:14px;background:linear-gradient(135deg,rgba(40,167,69,0.12) 0%,rgba(32,201,151,0.10) 100%);border:1px solid rgba(40,167,69,0.25);color:var(--etera-text);box-shadow:0 10px 30px rgba(40,167,69,0.10)}
    .etera-welcome-icon{width:40px;height:40px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:rgba(40,167,69,0.14);color:var(--etera-green);flex:0 0 auto}
    .etera-welcome-title{font-weight:800;font-size:0.95rem;line-height:1.2;margin:0 0 2px 0}
    .etera-welcome-text{margin:0;color:var(--etera-text-soft);font-size:0.95rem;line-height:1.35}
    /* Role cards — white/green */
    .etera-role-card{background:#fff;border:1px solid #c8e6c9;color:#1a1a2e}
    .etera-role-card:hover{border-color:#28a745;background:rgba(40,167,69,0.04);box-shadow:0 12px 40px rgba(40,167,69,0.12);color:#1a1a2e}
    .etera-role-card .role-icon{background:var(--etera-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .etera-role-card h5{color:#1a1a2e}
    .etera-role-card p{color:#6b7280}
    /* OTP inputs — white/green */
    .etera-otp-group{display:flex;justify-content:center;gap:10px;margin:1.5rem 0}
    .etera-otp-input{width:52px;height:60px;text-align:center;font-size:1.4rem;font-weight:700;font-family:'Inter',sans-serif;background:#f9fafb;border:2px solid #d1d5db;border-radius:12px;color:#1a1a2e;transition:all 0.2s ease;outline:none}
    .etera-otp-input:focus{border-color:#28a745;box-shadow:0 0 0 3px rgba(40,167,69,0.15);background:#fff}
    /* Countdown */
    .etera-countdown{color:#6b7280;font-size:0.9rem;text-align:center}
    .etera-countdown span{color:#28a745;font-weight:700}
    /* Outline button */
    .etera-btn-outline{background:transparent;border:2px solid #d1d5db;color:#374151;border-radius:50px}
    .etera-btn-outline:hover{border-color:#28a745;color:#28a745;background:rgba(40,167,69,0.04)}
    /* Form body inside auth card — scrollable for long forms */
    .etera-auth-card{overflow-y:auto;max-height:90vh}
    .form-body{color:#1a1a2e}
    .form-header{text-align:center;margin-bottom:1.5rem}
    .form-header .logo-text{max-width:100px;margin-bottom:1rem}
    .form-header h3{color:#1a1a2e;font-weight:700;font-size:1.3rem}
    .form-header p{color:#6b7280;font-size:0.95rem}
    /* Background circles */
    .etera-bg-circles{position:fixed;top:0;left:0;width:100%;height:100%;z-index:0;pointer-events:none}
    .etera-bg-circles .circle{position:absolute;border-radius:50%;opacity:0.06;background:var(--etera-green)}
    .etera-bg-circles .circle:nth-child(1){width:500px;height:500px;top:-100px;right:-100px}
    .etera-bg-circles .circle:nth-child(2){width:300px;height:300px;bottom:-50px;left:-50px}
    .etera-bg-circles .circle:nth-child(3){width:200px;height:200px;top:40%;left:60%}
    @keyframes etera-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
    @keyframes etera-fade-in{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
    @media(max-width:1024px){.etera-auth-branding{display:none}.etera-auth-form-side{padding:1.5rem}.etera-auth-card{padding:2rem;max-height:none}}
    </style>

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- React + Babel CDN -->
    @include('partials.react-head')

    <!-- Legacy styles (for complex signup forms with FilePond, Select2, etc.) -->
    @yield('styles')

    <style>
        .world-section {
    min-height: 70vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.cloud-container {
    position: relative;
    width: 500px;
    height: 500px;
}

#brandCanvas {
    width: 100%;
    height: 100%;
}

#brandTags {
    display: none;
}
    </style>
    <title>@yield('title', 'etera — Auto Parts Sourcing')</title>
</head>
<body class="etera-dark-body">

    <!-- Animated Background Circles -->
    <div class="etera-bg-circles">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>

    <!-- Main Content -->
    <div class="etera-auth-wrapper">
        <!-- Left Branding Panel -->
        <div class="etera-auth-branding">
            @yield('branding')
        </div>

        <!-- Right Form Panel -->
        <div class="etera-auth-form-side">
            <div class="etera-glass-card etera-auth-card">
                @if(session('welcome'))
                    <div style="margin-bottom: 1rem;">
                        <div class="alert alert-dismissible fade show mb-0" role="alert" style="background: transparent; border: 0; padding: 0;">
                            <div class="etera-welcome-banner">
                                <div class="etera-welcome-icon" aria-hidden="true">
                                    <i class="bi bi-check2-circle" style="font-size: 1.25rem;"></i>
                                </div>
                                <div style="flex: 1 1 auto; min-width: 0;">
                                    <div class="etera-welcome-title">Welcome to etera</div>
                                    <p class="etera-welcome-text">{{ session('welcome') }}</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    @include('partials.toast')

    <!-- Page-specific scripts -->
    @yield('scripts')
@hasSection('brand_canvas')
<script src="https://www.goat1000.com/tagcanvas.min.js"></script>
@endif
</body>
@hasSection('brand_canvas')
<script>
document.addEventListener("DOMContentLoaded", function() {

    try {
        TagCanvas.Start('brandCanvas', 'brandTags', {
            textColour: null,
            outlineColour: "transparent",
            reverse: true,
            depth: 0.8,
            maxSpeed: 0.05,
            initial: [0.1, -0.1],
            wheelZoom: false,
            imageMode: "image",
            noTagsMessage: false,
            imageScale: 1,
            shadow: "#60A5FA",
            shadowBlur: 10,
            bgRadius: 0
        });
    } catch(e) {
        const canvas = document.getElementById('brandCanvas');
        if (canvas) {
            canvas.style.display = 'none';
        }
    }

});
</script>
@endif
</html>
