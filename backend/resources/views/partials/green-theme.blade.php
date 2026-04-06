{{-- etera White & Green Gradient Theme Override --}}
{{-- Scoped to .wrapper (admin/dashboard layouts) to avoid breaking auth/etera-modern pages --}}
<style>
/* ====== etera White & Green Gradient Theme (Dashboard Only) ====== */

/* Sidebar */
html.light .wrapper .sidebar-wrapper { background: linear-gradient(180deg, #ffffff 0%, #e8f5e9 100%) !important; }
html.light .wrapper .sidebar-wrapper .metismenu a { color: #2e7d32 !important; }
html.light .wrapper .sidebar-wrapper .metismenu a:hover,
html.light .wrapper .sidebar-wrapper .metismenu .mm-active > a { background: rgba(40,167,69,0.12) !important; color: #1b5e20 !important; }
html.light .wrapper .sidebar-header { background: transparent !important; }

/* Top bar */
html.light .wrapper .topbar { background: linear-gradient(135deg, #ffffff, #f1f8e9) !important; border-bottom: 2px solid #c8e6c9; }

/* Page background */
html.light .wrapper .page-wrapper { background: linear-gradient(135deg, #fafffe 0%, #e8f5e9 50%, #f1f8e9 100%) !important; min-height: 100vh; }
html.light .wrapper .page-footer { background: transparent !important; color: #2e7d32 !important; }

/* Cards (inside wrapper only) */
html.light .wrapper .card { border: 1px solid #c8e6c9 !important; box-shadow: 0 2px 12px rgba(40,167,69,0.08) !important; }
html.light .wrapper .card-header.bg-primary { background: linear-gradient(135deg, #28a745, #20c997) !important; border: none; }
html.light .wrapper .card-header.bg-success { background: linear-gradient(135deg, #2e7d32, #43a047) !important; border: none; }
html.light .wrapper .card-header.bg-info { background: linear-gradient(135deg, #00897b, #26a69a) !important; border: none; }

/* Buttons (inside wrapper only) */
html.light .wrapper .btn-primary { background: linear-gradient(135deg, #28a745, #20c997) !important; border: none !important; }
html.light .wrapper .btn-primary:hover { background: linear-gradient(135deg, #1e7e34, #17a2b8) !important; }
html.light .wrapper .btn-success { background: linear-gradient(135deg, #2e7d32, #43a047) !important; border: none !important; }

/* Misc dashboard elements */
html.light .wrapper .back-to-top { background: #28a745 !important; }
html.light .wrapper .form-check-input:checked { background-color: #28a745 !important; border-color: #28a745 !important; }
html.light .wrapper .badge.bg-primary { background: linear-gradient(135deg, #28a745, #20c997) !important; }

/* Spare-part / modern dashboard specific */
html.light .ep-sidebar { background: linear-gradient(180deg, #ffffff 0%, #e8f5e9 100%) !important; }
html.light .ep-topbar { background: linear-gradient(135deg, #ffffff, #f1f8e9) !important; border-bottom: 2px solid #c8e6c9; }
html.light .ep-main-content { background: linear-gradient(135deg, #fafffe 0%, #e8f5e9 50%, #f1f8e9 100%) !important; }

html.dark-theme body { background: radial-gradient(1200px circle at 10% 0%, rgba(34,197,94,0.10), transparent 45%), linear-gradient(180deg, #0b1220 0%, #070b13 100%) !important; color: rgba(229,231,235,0.90) !important; }
html.dark-theme a { color: rgba(167,243,208,0.95); }
html.dark-theme a:hover { color: rgba(229,231,235,0.98); }

html.dark-theme p,
html.dark-theme span,
html.dark-theme li,
html.dark-theme td,
html.dark-theme th,
html.dark-theme label,
html.dark-theme .form-label { color: rgba(229,231,235,0.88); }
html.dark-theme small,
html.dark-theme .small,
html.dark-theme .text-muted,
html.dark-theme .form-text { color: rgba(148,163,184,0.85) !important; }
html.dark-theme h1,
html.dark-theme h2,
html.dark-theme h3,
html.dark-theme h4,
html.dark-theme h5,
html.dark-theme h6,
html.dark-theme .page-title { color: rgba(243,244,246,0.97) !important; letter-spacing: 0.2px; }
html.dark-theme hr { border-color: rgba(148,163,184,0.18) !important; opacity: 1; }

html.dark-theme .wrapper .page-wrapper { background: radial-gradient(1200px circle at 10% 0%, rgba(34,197,94,0.10), transparent 45%), radial-gradient(900px circle at 90% 10%, rgba(16,185,129,0.10), transparent 40%), linear-gradient(180deg, #0b1220 0%, #070b13 100%) !important; min-height: 100vh; }
html.dark-theme .wrapper .page-content { color: #e5e7eb; }
html.dark-theme .wrapper .page-footer { background: transparent !important; color: rgba(229,231,235,0.70) !important; border-top: 1px solid rgba(148,163,184,0.18); }

html.dark-theme .wrapper .sidebar-wrapper { background: linear-gradient(180deg, #0b1220 0%, #0a0f1a 70%, #070b13 100%) !important; border-right: 1px solid rgba(148,163,184,0.18); }
html.dark-theme .wrapper .sidebar-header { background: transparent !important; border-bottom: 1px solid rgba(148,163,184,0.18); }
html.dark-theme .wrapper .sidebar-wrapper .metismenu a { color: rgba(229,231,235,0.86) !important; }
html.dark-theme .wrapper .sidebar-wrapper .metismenu a:hover { background: rgba(34,197,94,0.10) !important; color: #eafff2 !important; }
html.dark-theme .wrapper .sidebar-wrapper .metismenu .mm-active > a { background: rgba(34,197,94,0.16) !important; color: #eafff2 !important; box-shadow: inset 0 0 0 1px rgba(34,197,94,0.22); }
html.dark-theme .wrapper .sidebar-wrapper .metismenu .mm-active > a i { color: rgba(34,197,94,0.95) !important; }

html.dark-theme .wrapper .topbar { background: rgba(11,18,32,0.82) !important; backdrop-filter: blur(10px); border-bottom: 1px solid rgba(148,163,184,0.18) !important; }
html.dark-theme .wrapper .topbar .navbar .nav-link { color: rgba(229,231,235,0.85) !important; }
html.dark-theme .wrapper .topbar .navbar .nav-link:hover { color: #ffffff !important; }

html.dark-theme .wrapper .card { background: rgba(17,24,39,0.72) !important; border: 1px solid rgba(148,163,184,0.16) !important; box-shadow: 0 12px 28px rgba(0,0,0,0.35) !important; }
html.dark-theme .wrapper .card-header,
html.dark-theme .wrapper .card-footer { background: rgba(17,24,39,0.55) !important; border-color: rgba(148,163,184,0.16) !important; }
html.dark-theme .wrapper .card-title,
html.dark-theme .wrapper .card h1,
html.dark-theme .wrapper .card h2,
html.dark-theme .wrapper .card h3,
html.dark-theme .wrapper .card h4,
html.dark-theme .wrapper .card h5,
html.dark-theme .wrapper .card h6 { color: rgba(243,244,246,0.95) !important; }

html.dark-theme .wrapper .btn-primary { background: linear-gradient(135deg, #22c55e, #10b981) !important; border: none !important; box-shadow: 0 10px 18px rgba(16,185,129,0.20); }
html.dark-theme .wrapper .btn-primary:hover { background: linear-gradient(135deg, #16a34a, #0ea5a4) !important; }
html.dark-theme .wrapper .btn-success { background: linear-gradient(135deg, #16a34a, #22c55e) !important; border: none !important; }
html.dark-theme .wrapper .btn-outline-primary { color: rgba(229,231,235,0.92) !important; border-color: rgba(34,197,94,0.55) !important; }
html.dark-theme .wrapper .btn-outline-primary:hover { background: rgba(34,197,94,0.16) !important; }

html.dark-theme .wrapper .form-control,
html.dark-theme .wrapper .form-select { background-color: rgba(2,6,23,0.55) !important; color: rgba(243,244,246,0.92) !important; border-color: rgba(148,163,184,0.22) !important; }
html.dark-theme .wrapper .form-control:focus,
html.dark-theme .wrapper .form-select:focus { border-color: rgba(34,197,94,0.55) !important; box-shadow: 0 0 0 0.2rem rgba(34,197,94,0.18) !important; }
html.dark-theme .wrapper .form-control::placeholder { color: rgba(148,163,184,0.75) !important; }
html.dark-theme .wrapper .input-group-text { background: rgba(17,24,39,0.70) !important; color: rgba(229,231,235,0.82) !important; border-color: rgba(148,163,184,0.18) !important; }
html.dark-theme .wrapper .form-check-input { background-color: rgba(2,6,23,0.55) !important; border-color: rgba(148,163,184,0.35) !important; }
html.dark-theme .wrapper .form-check-input:checked { background-color: #22c55e !important; border-color: #22c55e !important; }

html.dark-theme .wrapper .table { color: rgba(229,231,235,0.88) !important; }
html.dark-theme .wrapper .table thead th { background: rgba(2,6,23,0.55) !important; color: rgba(243,244,246,0.95) !important; border-color: rgba(148,163,184,0.20) !important; }
html.dark-theme .wrapper .table td,
html.dark-theme .wrapper .table th { border-color: rgba(148,163,184,0.12) !important; }
html.dark-theme .wrapper .table-hover tbody tr:hover { background-color: rgba(34,197,94,0.08) !important; }

html.dark-theme .wrapper .dropdown-menu { background: rgba(15,23,42,0.96) !important; border: 1px solid rgba(148,163,184,0.18) !important; box-shadow: 0 18px 40px rgba(0,0,0,0.45) !important; }
html.dark-theme .wrapper .dropdown-item { color: rgba(229,231,235,0.88) !important; }
html.dark-theme .wrapper .dropdown-item:hover,
html.dark-theme .wrapper .dropdown-item:focus { background: rgba(34,197,94,0.12) !important; color: rgba(243,244,246,0.98) !important; }
html.dark-theme .wrapper .dropdown-divider { border-top-color: rgba(148,163,184,0.16) !important; }

html.dark-theme .wrapper .modal-content { background: rgba(15,23,42,0.98) !important; color: rgba(229,231,235,0.90) !important; border: 1px solid rgba(148,163,184,0.18) !important; }
html.dark-theme .wrapper .modal-header,
html.dark-theme .wrapper .modal-footer { border-color: rgba(148,163,184,0.16) !important; }
html.dark-theme .wrapper .btn-close { filter: invert(1) grayscale(100%); opacity: 0.85; }

html.dark-theme .wrapper .alert { background: rgba(17,24,39,0.65) !important; color: rgba(243,244,246,0.92) !important; border-color: rgba(148,163,184,0.22) !important; }
html.dark-theme .wrapper .alert-success { border-color: rgba(34,197,94,0.35) !important; }
html.dark-theme .wrapper .alert-danger { border-color: rgba(239,68,68,0.35) !important; }
html.dark-theme .wrapper .alert-warning { border-color: rgba(245,158,11,0.35) !important; }
html.dark-theme .wrapper .alert-info { border-color: rgba(59,130,246,0.35) !important; }

html.dark-theme .wrapper .pagination .page-link { background: rgba(2,6,23,0.55) !important; color: rgba(229,231,235,0.88) !important; border-color: rgba(148,163,184,0.18) !important; }
html.dark-theme .wrapper .pagination .page-link:hover { background: rgba(34,197,94,0.12) !important; color: rgba(243,244,246,0.98) !important; border-color: rgba(34,197,94,0.35) !important; }
html.dark-theme .wrapper .pagination .page-item.active .page-link { background: linear-gradient(135deg, #22c55e, #10b981) !important; border-color: rgba(34,197,94,0.55) !important; color: #07130e !important; }
html.dark-theme .wrapper .pagination .page-item.disabled .page-link { background: rgba(2,6,23,0.35) !important; color: rgba(148,163,184,0.55) !important; }

html.dark-theme .wrapper .select2-container--bootstrap-5 .select2-selection { background-color: rgba(2,6,23,0.55) !important; border-color: rgba(148,163,184,0.22) !important; color: rgba(243,244,246,0.92) !important; }
html.dark-theme .wrapper .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { color: rgba(243,244,246,0.92) !important; }
html.dark-theme .wrapper .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder { color: rgba(148,163,184,0.75) !important; }
html.dark-theme .wrapper .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice { background: rgba(34,197,94,0.16) !important; border-color: rgba(34,197,94,0.30) !important; color: rgba(243,244,246,0.92) !important; }
html.dark-theme .wrapper .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove { color: rgba(243,244,246,0.85) !important; }
html.dark-theme .wrapper .select2-container--bootstrap-5.select2-container--focus .select2-selection { border-color: rgba(34,197,94,0.55) !important; box-shadow: 0 0 0 0.2rem rgba(34,197,94,0.18) !important; }
html.dark-theme .wrapper .select2-dropdown { background: rgba(15,23,42,0.98) !important; border-color: rgba(148,163,184,0.18) !important; }
html.dark-theme .wrapper .select2-container--bootstrap-5 .select2-results__option { color: rgba(229,231,235,0.88) !important; }
html.dark-theme .wrapper .select2-container--bootstrap-5 .select2-results__option--highlighted { background: rgba(34,197,94,0.12) !important; color: rgba(243,244,246,0.98) !important; }
html.dark-theme .wrapper .select2-search--dropdown .select2-search__field { background: rgba(2,6,23,0.55) !important; color: rgba(243,244,246,0.92) !important; border-color: rgba(148,163,184,0.22) !important; }

html.dark-theme .wrapper .etera-toast-container { position: fixed; top: 18px; right: 18px; z-index: 2000; display: flex; flex-direction: column; gap: 10px; }
html.dark-theme .wrapper .etera-toast { background: rgba(15,23,42,0.96); color: rgba(243,244,246,0.92); border: 1px solid rgba(148,163,184,0.18); border-radius: 14px; padding: 12px 14px 12px 12px; box-shadow: 0 18px 40px rgba(0,0,0,0.45); display: flex; align-items: center; gap: 10px; overflow: hidden; }
html.dark-theme .wrapper .etera-toast-icon { width: 26px; height: 26px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: rgba(34,197,94,0.14); color: rgba(34,197,94,0.95); flex: 0 0 auto; }
html.dark-theme .wrapper .etera-toast-message { line-height: 1.35; color: rgba(243,244,246,0.92); }
html.dark-theme .wrapper .etera-toast-close { background: transparent; border: none; color: rgba(148,163,184,0.9); font-size: 18px; margin-left: auto; padding: 0 6px; }
html.dark-theme .wrapper .etera-toast-close:hover { color: rgba(243,244,246,0.98); }
html.dark-theme .wrapper .etera-toast-progress { position: absolute; left: 0; bottom: 0; height: 3px; width: 100%; transform-origin: left; background: linear-gradient(90deg, #22c55e, #10b981); animation-name: etera-toast-progress; animation-timing-function: linear; }
@keyframes etera-toast-progress { from { transform: scaleX(1); } to { transform: scaleX(0); } }

html.dark-theme .sp-header,
html.dark-theme #header-container { background: rgba(11,18,32,0.82) !important; backdrop-filter: blur(10px); border-bottom: 1px solid rgba(148,163,184,0.18) !important; box-shadow: 0 12px 28px rgba(0,0,0,0.35) !important; }
html.dark-theme .sp-nav-link,
html.dark-theme .nav-item { color: rgba(229,231,235,0.88) !important; }
html.dark-theme .sp-nav-link:hover,
html.dark-theme .nav-item:hover { background: rgba(34,197,94,0.12) !important; color: rgba(243,244,246,0.98) !important; }
html.dark-theme .sp-nav-link.active { background: rgba(34,197,94,0.18) !important; color: rgba(243,244,246,0.98) !important; }

html.dark-theme .sp-nav { background: rgba(11,18,32,0.96) !important; border-bottom: 1px solid rgba(148,163,184,0.18) !important; box-shadow: 0 18px 44px rgba(0,0,0,0.45) !important; }
html.dark-theme .sp-nav.open { background: rgba(11,18,32,0.96) !important; }
html.dark-theme .sp-nav.open .sp-nav-link { color: rgba(243,244,246,0.92) !important; }
html.dark-theme .sp-nav.open .sp-nav-link:hover { background: rgba(34,197,94,0.12) !important; }

html.dark-theme .mobile-toggle-menu,
html.dark-theme .toggle-icon,
html.dark-theme .toggle-icon i,
html.dark-theme .mobile-toggle-menu i,
html.dark-theme .sp-mobile-toggle,
html.dark-theme .sp-mobile-toggle i { color: rgba(243,244,246,0.92) !important; }
html.dark-theme .mobile-toggle-menu:hover i,
html.dark-theme .toggle-icon:hover i,
html.dark-theme .sp-mobile-toggle:hover i { color: rgba(34,197,94,0.95) !important; }

html.dark-theme .sp-footer { background: rgba(11,18,32,0.82) !important; backdrop-filter: blur(10px); border-top: 1px solid rgba(148,163,184,0.18) !important; }
html.dark-theme .sp-footer-brand { color: rgba(243,244,246,0.95) !important; }
html.dark-theme .sp-footer-copy { color: rgba(229,231,235,0.70) !important; }
html.dark-theme .sp-footer-social a { color: rgba(229,231,235,0.80) !important; }
html.dark-theme .sp-footer-social a:hover { color: rgba(34,197,94,0.95) !important; }

html.dark-theme .job-listing,
html.dark-theme .job-listing.with-apply-button { background: rgba(17,24,39,0.72) !important; border: 1px solid rgba(148,163,184,0.16) !important; box-shadow: 0 12px 28px rgba(0,0,0,0.35) !important; }
html.dark-theme .job-listing:hover { box-shadow: 0 18px 44px rgba(0,0,0,0.45) !important; border-color: rgba(34,197,94,0.30) !important; }
html.dark-theme .job-listing-title { color: rgba(243,244,246,0.95) !important; }
html.dark-theme .job-listing-text,
html.dark-theme .job-listing-description,
html.dark-theme .job-overview-inner,
html.dark-theme .job-overview-inner ul li span { color: rgba(229,231,235,0.78) !important; }
html.dark-theme .job-listing-footer ul li { color: rgba(229,231,235,0.70) !important; }
html.dark-theme .job-listing-footer ul li i,
html.dark-theme .job-overview-inner ul li i,
html.dark-theme .single-page-header-inner i { color: rgba(34,197,94,0.95) !important; }

html.dark-theme .sidebar-container,
html.dark-theme .sidebar-widget,
html.dark-theme .single-page-section,
html.dark-theme .boxed-widget,
html.dark-theme .boxed-widget-headline,
html.dark-theme .dashboard-box { background: rgba(17,24,39,0.72) !important; border: 1px solid rgba(148,163,184,0.16) !important; }
html.dark-theme .boxed-widget-headline,
html.dark-theme .job-overview-headline { color: rgba(243,244,246,0.95) !important; border-bottom-color: rgba(148,163,184,0.16) !important; }

html.dark-theme .with-border,
html.dark-theme input.with-border,
html.dark-theme textarea.with-border,
html.dark-theme select.with-border { background: rgba(2,6,23,0.55) !important; color: rgba(243,244,246,0.92) !important; border-color: rgba(148,163,184,0.22) !important; }
html.dark-theme .with-border:focus,
html.dark-theme input.with-border:focus,
html.dark-theme textarea.with-border:focus,
html.dark-theme select.with-border:focus { border-color: rgba(34,197,94,0.55) !important; box-shadow: 0 0 0 0.2rem rgba(34,197,94,0.18) !important; }
html.dark-theme .with-border::placeholder,
html.dark-theme input.with-border::placeholder,
html.dark-theme textarea.with-border::placeholder { color: rgba(148,163,184,0.75) !important; }

html.dark-theme .apply-now-button,
html.dark-theme .button,
html.dark-theme .button.ripple-effect { background: linear-gradient(135deg, #22c55e, #10b981) !important; color: #07130e !important; border: none !important; box-shadow: 0 10px 18px rgba(16,185,129,0.20) !important; }
html.dark-theme .apply-now-button:hover,
html.dark-theme .button:hover,
html.dark-theme .button.ripple-effect:hover { filter: brightness(0.96); }
html.dark-theme .list-apply-button { background: rgba(34,197,94,0.14) !important; color: rgba(229,231,235,0.92) !important; border-color: rgba(34,197,94,0.30) !important; }
html.dark-theme .list-apply-button:hover { background: rgba(34,197,94,0.20) !important; }

html.dark-theme .bg-white,
html.dark-theme .bg-light { background-color: rgba(17,24,39,0.72) !important; }

html.dark-theme .card { background: rgba(17,24,39,0.72) !important; border: 1px solid rgba(148,163,184,0.16) !important; box-shadow: 0 12px 28px rgba(0,0,0,0.35) !important; }
html.dark-theme .card-header,
html.dark-theme .card-footer { background: rgba(17,24,39,0.55) !important; border-color: rgba(148,163,184,0.16) !important; }
html.dark-theme .card-body { color: rgba(229,231,235,0.88) !important; }
html.dark-theme .card-title,
html.dark-theme .card h1,
html.dark-theme .card h2,
html.dark-theme .card h3,
html.dark-theme .card h4,
html.dark-theme .card h5,
html.dark-theme .card h6 { color: rgba(243,244,246,0.95) !important; }

html.dark-theme .table { color: rgba(229,231,235,0.88) !important; }
html.dark-theme .table thead th { background: rgba(2,6,23,0.55) !important; color: rgba(243,244,246,0.95) !important; border-color: rgba(148,163,184,0.20) !important; }
html.dark-theme .table td,
html.dark-theme .table th { border-color: rgba(148,163,184,0.12) !important; }
html.dark-theme .table-striped > tbody > tr:nth-of-type(odd) > * { background-color: rgba(2,6,23,0.30) !important; color: rgba(229,231,235,0.88) !important; }
html.dark-theme .table-hover tbody tr:hover > * { background-color: rgba(34,197,94,0.08) !important; color: rgba(243,244,246,0.98) !important; }

html.dark-theme .table-responsive,
html.dark-theme .lead-table { background: transparent !important; }

html.dark-theme .page-breadcrumb { color: rgba(229,231,235,0.88) !important; }
html.dark-theme .page-breadcrumb .product-show i { color: rgba(148,163,184,0.85) !important; }

html.dark-theme .breadcrumb,
html.dark-theme .breadcrumb-item { color: rgba(148,163,184,0.9) !important; }
html.dark-theme .breadcrumb-item.active { color: rgba(243,244,246,0.92) !important; }
html.dark-theme .breadcrumb-item + .breadcrumb-item::before { color: rgba(148,163,184,0.65) !important; }

html.dark-theme .badge { border: 1px solid rgba(148,163,184,0.16); }
html.dark-theme .badge.bg-light { background: rgba(2,6,23,0.45) !important; color: rgba(229,231,235,0.88) !important; }

html.dark-theme .list-group-item { background: rgba(17,24,39,0.65) !important; border-color: rgba(148,163,184,0.16) !important; color: rgba(229,231,235,0.88) !important; }
html.dark-theme .list-group-item:hover { background: rgba(34,197,94,0.08) !important; }

html.dark-theme .text-secondary { color: rgba(148,163,184,0.90) !important; }
html.dark-theme .text-dark,
html.dark-theme .text-black { color: rgba(243,244,246,0.95) !important; }

html.dark-theme .btn.btn-white,
html.dark-theme .btn-white { background: rgba(2,6,23,0.35) !important; color: rgba(229,231,235,0.88) !important; border-color: rgba(148,163,184,0.18) !important; }
html.dark-theme .btn.btn-white:hover,
html.dark-theme .btn-white:hover { background: rgba(34,197,94,0.10) !important; border-color: rgba(34,197,94,0.35) !important; color: rgba(243,244,246,0.98) !important; }

html.dark-theme .dropdown-menu { background: rgba(15,23,42,0.96) !important; border: 1px solid rgba(148,163,184,0.18) !important; box-shadow: 0 18px 40px rgba(0,0,0,0.45) !important; }
html.dark-theme .dropdown-item { color: rgba(229,231,235,0.88) !important; }
html.dark-theme .dropdown-item:hover,
html.dark-theme .dropdown-item:focus { background: rgba(34,197,94,0.12) !important; color: rgba(243,244,246,0.98) !important; }
html.dark-theme .dropdown-divider { border-top-color: rgba(148,163,184,0.16) !important; }

html.dark-theme .table-light,
html.dark-theme thead.table-light,
html.dark-theme .table thead.table-light th { background: rgba(2,6,23,0.55) !important; color: rgba(243,244,246,0.95) !important; border-color: rgba(148,163,184,0.20) !important; }

html.dark-theme .badge.bg-light.text-dark,
html.dark-theme .badge.bg-light.text-black { background: rgba(2,6,23,0.45) !important; color: rgba(229,231,235,0.90) !important; border-color: rgba(148,163,184,0.16) !important; }

html.dark-theme .nav-tabs { border-bottom-color: rgba(148,163,184,0.18) !important; }
html.dark-theme .nav-tabs .nav-link { color: rgba(229,231,235,0.82) !important; border-color: transparent !important; }
html.dark-theme .nav-tabs .nav-link:hover { color: rgba(243,244,246,0.98) !important; background: rgba(34,197,94,0.08) !important; border-color: rgba(34,197,94,0.25) !important; }
html.dark-theme .nav-tabs .nav-link.active { color: rgba(243,244,246,0.98) !important; background: rgba(17,24,39,0.75) !important; border-color: rgba(148,163,184,0.18) rgba(148,163,184,0.18) transparent !important; }

html.dark-theme .bs-stepper,
html.dark-theme .bs-stepper-content { color: rgba(229,231,235,0.88) !important; }
html.dark-theme .bs-stepper .step-trigger { background: transparent !important; color: rgba(229,231,235,0.88) !important; }
html.dark-theme .bs-stepper .step-trigger:hover { background: rgba(34,197,94,0.10) !important; }
html.dark-theme .bs-stepper .bs-stepper-circle { background: rgba(34,197,94,0.14) !important; color: rgba(243,244,246,0.95) !important; box-shadow: inset 0 0 0 1px rgba(34,197,94,0.28) !important; }
html.dark-theme .steper-title { color: rgba(243,244,246,0.95) !important; }
html.dark-theme .steper-sub-title { color: rgba(229,231,235,0.68) !important; }

html.dark-theme .part-item { background-color: rgba(2,6,23,0.40) !important; border-color: rgba(148,163,184,0.18) !important; }
html.dark-theme .part-item:hover { background-color: rgba(2,6,23,0.52) !important; box-shadow: 0 12px 28px rgba(0,0,0,0.35) !important; }
html.dark-theme .image-upload-section { border-color: rgba(148,163,184,0.30) !important; background: rgba(2,6,23,0.28) !important; }
html.dark-theme .image-upload-section:hover { border-color: rgba(34,197,94,0.55) !important; }

html.dark-theme .wrapper .back-to-top { background: linear-gradient(135deg, #22c55e, #10b981) !important; box-shadow: 0 12px 22px rgba(16,185,129,0.22); }

html.dark-theme .ep-sidebar { background: linear-gradient(180deg, #0b1220 0%, #070b13 100%) !important; border-right: 1px solid rgba(148,163,184,0.18); }
html.dark-theme .ep-topbar { background: rgba(11,18,32,0.82) !important; backdrop-filter: blur(10px); border-bottom: 1px solid rgba(148,163,184,0.18) !important; }
html.dark-theme .ep-main-content { background: radial-gradient(1200px circle at 10% 0%, rgba(34,197,94,0.10), transparent 45%), linear-gradient(180deg, #0b1220 0%, #070b13 100%) !important; }
</style>

<script>
(function () {
	var STORAGE_KEY = 'etera-theme';
	var THEMES = { light: 'light', dark: 'dark-theme', semidark: 'semi-dark' };

	function normalizeTheme(value) {
		if (value === THEMES.dark || value === 'dark') return THEMES.dark;
		if (value === THEMES.semidark || value === 'semidark') return THEMES.semidark;
		return THEMES.light;
	}

	function applyTheme(theme) {
		var t = normalizeTheme(theme);
		var html = document.documentElement;
		html.classList.remove(THEMES.light, THEMES.dark, THEMES.semidark);
		html.classList.add(t);
		try { localStorage.setItem(STORAGE_KEY, t); } catch (e) {}

		var lightRadio = document.getElementById('lightmode');
		var darkRadio = document.getElementById('darkmode');
		var semiRadio = document.getElementById('semidark');
		if (lightRadio) lightRadio.checked = (t === THEMES.light);
		if (darkRadio) darkRadio.checked = (t === THEMES.dark);
		if (semiRadio) semiRadio.checked = (t === THEMES.semidark);
	}

	function getStoredTheme() {
		try { return normalizeTheme(localStorage.getItem(STORAGE_KEY)); } catch (e) { return THEMES.light; }
	}

	applyTheme(getStoredTheme());

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	function init() {
		var lightRadio = document.getElementById('lightmode');
		var darkRadio = document.getElementById('darkmode');
		var semiRadio = document.getElementById('semidark');

		if (lightRadio) lightRadio.addEventListener('change', function () { if (this.checked) applyTheme(THEMES.light); });
		if (darkRadio) darkRadio.addEventListener('change', function () { if (this.checked) applyTheme(THEMES.dark); });
		if (semiRadio) semiRadio.addEventListener('change', function () { if (this.checked) applyTheme(THEMES.semidark); });

		var toggles = document.querySelectorAll('.dark-mode-icon');
		for (var i = 0; i < toggles.length; i++) {
			toggles[i].addEventListener('click', function (e) {
				e.preventDefault();
				var current = normalizeTheme(document.documentElement.classList.contains(THEMES.dark) ? THEMES.dark : (document.documentElement.classList.contains(THEMES.semidark) ? THEMES.semidark : THEMES.light));
				applyTheme(current === THEMES.dark ? THEMES.light : THEMES.dark);
			});
		}
	}
})();
</script>
