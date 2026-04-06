<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<!-- Favicon -->
	<link rel="icon" href="{{asset('favicon.ico')}}" type="image/x-icon"/>
	<link rel="icon" href="{{asset('assets/images/transparent.svg')}}" type="image/jpeg"/>

	<!-- etera Modern Design System -->
	<link href="{{ asset('assets/css/etera-modern.css') }}" rel="stylesheet">

	<!-- Bootstrap CSS -->
	<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/bootstrap-extended.css')}}" rel="stylesheet">

	<!-- Icons -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
	<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
	<link rel="stylesheet" href="{{asset('assets/css/icons.css')}}">

	<!-- Additional Plugins -->
	<link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2.min.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2-bootstrap-5-theme.min.css')}}" />
	{{-- lobibox removed – replaced by React toast system --}}

	<!-- FilePond core -->
	<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
	<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
	<link href="https://unpkg.com/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.css" rel="stylesheet"/>

	@livewireStyles

	<style>
		/* ============================================
		   SPAREPART LAYOUT — WHITE/GREEN THEME + BLACK TEXT
		   (Matching admin layout style)
		   ============================================ */

		html, body {
			height: 100%;
			margin: 0;
			padding: 0;
			font-family: 'Inter', sans-serif;
			background: linear-gradient(135deg, #fafffe 0%, #e8f5e9 50%, #f1f8e9 100%);
			color: #1a1a1a;
		}

		body {
			display: flex;
			flex-direction: column;
		}

		.sp-main-content {
			flex: 1;
		}

		/* ---- Header ---- */
		.sp-header {
			background: linear-gradient(135deg, #ffffff, #f1f8e9);
			border-bottom: 2px solid #c8e6c9;
			position: sticky;
			top: 0;
			z-index: 1000;
			padding: 0 24px;
		}

		.sp-header-inner {
			max-width: 1200px;
			margin: 0 auto;
			display: flex;
			align-items: center;
			justify-content: space-between;
			height: 64px;
		}

		.sp-logo img {
			max-width: 7rem;
			transition: opacity 0.2s;
		}

		.sp-logo img:hover {
			opacity: 0.85;
		}

		/* Nav */
		.sp-nav {
			display: flex;
			align-items: center;
			gap: 4px;
			list-style: none;
			margin: 0;
			padding: 0;
		}

		.sp-nav-link {
			color: #2e7d32;
			text-decoration: none;
			font-weight: 500;
			font-size: 0.9rem;
			padding: 8px 16px;
			border-radius: 8px;
			transition: all 0.25s ease;
			position: relative;
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}

		.sp-nav-link:hover {
			color: #1b5e20;
			background: rgba(40, 167, 69, 0.12);
		}

		.sp-nav-link.active {
			color: #1b5e20;
			background: rgba(40, 167, 69, 0.15);
		}

		.sp-nav-link.active::after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 50%;
			transform: translateX(-50%);
			width: 20px;
			height: 3px;
			border-radius: 3px;
			background: linear-gradient(135deg, #28a745, #20c997);
		}

		.sp-badge {
			background: rgba(239, 68, 68, 0.9);
			color: #fff;
			padding: 2px 7px;
			border-radius: 50px;
			font-size: 0.7rem;
			font-weight: 700;
			min-width: 18px;
			text-align: center;
			line-height: 1.3;
		}

		.sp-badge-primary {
			background: rgba(40, 167, 69, 0.9);
		}

		.sp-badge-warning {
			background: rgba(245, 158, 11, 0.9);
		}

		/* User menu */
		.sp-user-menu {
			position: relative;
		}

		.sp-user-trigger {
			display: flex;
			align-items: center;
			gap: 10px;
			cursor: pointer;
			padding: 6px 12px;
			border-radius: 10px;
			transition: background 0.2s;
			text-decoration: none;
			color: #1a1a1a;
			white-space: nowrap;
		}

		/* Hide Bootstrap caret; we use a custom chevron icon for consistent UI */
		.sp-user-trigger.dropdown-toggle::after {
			display: none;
		}

		.sp-user-caret {
			display: inline-flex;
			align-items: center;
			color: #2e7d32;
			font-size: 0.9rem;
			margin-left: 6px;
			line-height: 1;
		}

		.sp-user-trigger:hover {
			background: rgba(40, 167, 69, 0.08);
			color: #1a1a1a;
		}

		.sp-avatar {
			position: relative;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 36px;
			height: 36px;
			border-radius: 50%;
			overflow: hidden;
			border: 2px solid rgba(40, 167, 69, 0.4);
			background: rgba(40, 167, 69, 0.12);
		}

		.sp-avatar .sp-avatar-icon {
			color: #2e7d32;
			font-size: 18px;
			line-height: 1;
		}

		.sp-user-name {
			font-size: 0.85rem;
			font-weight: 600;
			color: #1a1a1a;
		}

		.sp-user-role {
			font-size: 0.7rem;
			color: #2e7d32;
			display: block;
		}

		.sp-user-menu .dropdown-menu {
			width: 220px;
			border: 1px solid #c8e6c9;
			border-radius: 12px;
			box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
			padding: 8px;
		}

		.sp-user-menu .dropdown-item {
			display: flex;
			align-items: center;
			gap: 10px;
			width: 100%;
			padding: 10px 14px;
			border-radius: 8px;
			font-size: 0.875rem;
			color: #333;
			text-decoration: none;
			border: none;
			cursor: pointer;
			transition: all 0.2s;
			font-family: 'Inter', sans-serif;
		}

		.sp-user-menu .dropdown-item:hover,
		.sp-user-menu .dropdown-item:focus {
			color: #1b5e20;
			background: rgba(40, 167, 69, 0.1);
		}

		.sp-user-menu form {
			margin: 0;
		}

		/* Mobile nav toggle */
		.sp-mobile-toggle {
			display: none;
			background: none;
			border: none;
			color: #1a1a1a;
			font-size: 1.5rem;
			cursor: pointer;
			padding: 4px;
		}

		/* ---- Commission Banner ---- */
		.sp-commission-banner {
			display: none !important;
			max-width: 1200px;
			margin: 16px auto 0;
			padding: 12px 20px;
			background: rgba(40, 167, 69, 0.08);
			border: 1px solid #c8e6c9;
			border-radius: 10px;
			font-size: 0.9rem;
			color: #333;
			text-align: center;
		}

		.sp-commission-banner strong {
			color: #2e7d32;
		}

		/* ---- Content Area ---- */
		.sp-content-wrapper {
			max-width: 1200px;
			margin: 0 auto;
			padding: 24px 16px;
		}

		/* ---- Force Black Text Across All Elements ---- */
		body, .wrapper, .page-wrapper, .page-content { color: #1a1a1a !important; }
		h1, h2, h3, h4, h5, h6 { color: #1a1a1a !important; }
		p { color: #333 !important; }
		label, .form-label { color: #1a1a1a !important; font-weight: 600; font-size: 0.85rem; }
		.text-dark { color: #1a1a1a !important; }
		.text-muted { color: #555 !important; }
		.text-secondary { color: #555 !important; }
		a { color: #2e7d32; }
		a:hover { color: #1b5e20; }
		hr { border-color: #c8e6c9; }

		/* Cards */
		.card {
			background: #fff !important;
			border: 1px solid #c8e6c9 !important;
			border-radius: 14px !important;
			color: #1a1a1a !important;
			box-shadow: 0 2px 12px rgba(40, 167, 69, 0.08) !important;
		}

		.card-header {
			background: linear-gradient(135deg, rgba(40, 167, 69, 0.06), rgba(32, 201, 151, 0.06)) !important;
			border-bottom: 1px solid #c8e6c9 !important;
			color: #1a1a1a !important;
		}

		.card-body { color: #1a1a1a !important; }

		.card-footer {
			background: #fafff9 !important;
			border-top: 1px solid #c8e6c9 !important;
		}

		/* Tables */
		.table {
			--bs-table-bg: transparent !important;
			--bs-table-color: #1a1a1a !important;
			--bs-table-border-color: #c8e6c9 !important;
			color: #1a1a1a !important;
		}

		.table th, .table td { color: #1a1a1a !important; }

		.table thead th {
			background: rgba(40, 167, 69, 0.08) !important;
			color: #2e7d32 !important;
			font-size: 0.8rem;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.05em;
			border-bottom: 1px solid #c8e6c9 !important;
		}

		.table td { border-bottom: 1px solid #e8f5e9 !important; vertical-align: middle; }
		.table-hover tbody tr:hover { background: rgba(40, 167, 69, 0.04) !important; }
		.table-striped > tbody > tr:nth-of-type(odd) > * { background: rgba(40, 167, 69, 0.03); }
		.table-light, .table-light th, .table-light td { background: rgba(40, 167, 69, 0.08) !important; color: #2e7d32 !important; }
		.table-bordered, .table-bordered th, .table-bordered td { border-color: #c8e6c9 !important; }

		table th { background: rgba(40, 167, 69, 0.08) !important; color: #2e7d32 !important; }
		table td { color: #1a1a1a !important; }
		table tr:nth-child(odd) { background: rgba(40, 167, 69, 0.02) !important; }
		tfoot tr:nth-child(odd) { background: rgba(40, 167, 69, 0.06) !important; color: #2e7d32 !important; }

		.bg-light { background: #f1f8e9 !important; color: #1a1a1a !important; }

		/* Forms */
		.form-control, .form-select {
			background: #fff !important;
			border: 1px solid #c8e6c9 !important;
			color: #1a1a1a !important;
			border-radius: 10px;
		}

		.form-control:focus, .form-select:focus {
			border-color: #28a745 !important;
			box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.15) !important;
			background: #fff !important;
			color: #1a1a1a !important;
		}

		.form-control::placeholder { color: #999 !important; }
		.input-group-text { background: #f1f8e9 !important; border: 1px solid #c8e6c9 !important; color: #555 !important; }

		/* Buttons */
		.btn-primary { background: linear-gradient(135deg, #28a745, #20c997) !important; border: none !important; border-radius: 10px !important; font-weight: 600 !important; box-shadow: 0 4px 16px rgba(40, 167, 69, 0.2) !important; transition: all 0.3s ease !important; color: #fff !important; }
		.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(40, 167, 69, 0.3) !important; }
		.btn-outline-primary { border-color: #28a745 !important; color: #2e7d32 !important; border-radius: 10px !important; }
		.btn-outline-primary:hover { background: rgba(40, 167, 69, 0.1) !important; color: #1b5e20 !important; }
		.btn-success { background: linear-gradient(135deg, #2e7d32, #43a047) !important; border: none !important; border-radius: 10px !important; color: #fff !important; }
		.btn-danger { background: rgba(239, 68, 68, 0.9) !important; border: none !important; border-radius: 10px !important; color: #fff !important; }
		.btn-warning { background: rgba(245, 158, 11, 0.9) !important; border: none !important; border-radius: 10px !important; color: #1a1a1a !important; }
		.btn-info { background: rgba(59, 130, 246, 0.9) !important; border: none !important; border-radius: 10px !important; color: #fff !important; }
		.btn-light, .btn-secondary { background: #f1f8e9 !important; border: 1px solid #c8e6c9 !important; color: #1a1a1a !important; border-radius: 10px !important; }
		.btn-light:hover, .btn-secondary:hover { background: #e8f5e9 !important; }
		.btn-outline-secondary { color: #555 !important; border-color: #c8e6c9 !important; }
		.btn-outline-secondary:hover { background: #f1f8e9 !important; color: #1a1a1a !important; }

		/* Badges */
		.badge.bg-primary { background: linear-gradient(135deg, #28a745, #20c997) !important; }
		.badge.bg-success { background: rgba(46, 125, 50, 0.8) !important; }
		.badge.bg-danger  { background: rgba(239, 68, 68, 0.8) !important; }
		.badge.bg-warning { background: rgba(245, 158, 11, 0.9) !important; color: #1a1a1a !important; }
		.badge.bg-info    { background: rgba(59, 130, 246, 0.8) !important; }
		.badge.bg-secondary { background: #e8e8e8 !important; color: #555 !important; }
		.badge.bg-light { color: #555 !important; border: 1px solid #c8e6c9 !important; }

		/* Modals */
		.modal-content { background: #fff !important; border: 1px solid #c8e6c9 !important; color: #1a1a1a !important; border-radius: 16px !important; }
		.modal-header { border-bottom: 1px solid #e8f5e9 !important; color: #1a1a1a !important; }
		.modal-header .modal-title { color: #1a1a1a !important; }
		.modal-body { color: #1a1a1a !important; }
		.modal-footer { border-top: 1px solid #e8f5e9 !important; }
		.modal-header[style*="background-color"] { background: rgba(40, 167, 69, 0.08) !important; border-bottom: 1px solid #c8e6c9 !important; }

		/* Alerts */
		.alert { border-radius: 10px !important; }
		.alert-success { background: rgba(40, 167, 69, 0.1) !important; color: #2e7d32 !important; border: 1px solid #c8e6c9 !important; }
		.alert-danger { background: rgba(239, 68, 68, 0.1) !important; color: #dc3545 !important; border: 1px solid rgba(239, 68, 68, 0.3) !important; }
		.alert-danger ul { color: #dc3545 !important; }
		.alert-warning { background: rgba(245, 158, 11, 0.1) !important; color: #856404 !important; border: 1px solid rgba(245, 158, 11, 0.3) !important; }
		.alert-info { background: rgba(59, 130, 246, 0.1) !important; color: #0c5460 !important; border: 1px solid rgba(59, 130, 246, 0.3) !important; }

		/* Accordion */
		.accordion-button { background: #f9fdf7 !important; color: #1a1a1a !important; }
		.accordion-body { background: #fff !important; color: #333 !important; }

		/* Pagination */
		.page-link, .pagination .page-link { background: #fff !important; border: 1px solid #c8e6c9 !important; color: #2e7d32 !important; border-radius: 8px !important; }
		.page-item.active .page-link, .pagination .page-item.active .page-link { background: linear-gradient(135deg, #28a745, #20c997) !important; border-color: transparent !important; color: #fff !important; }
		.page-link:hover, .pagination .page-link:hover { background: #e8f5e9 !important; color: #1b5e20 !important; }

		/* Select2 */
		.select2-container--default .select2-selection--single,
		.select2-container--default .select2-selection--multiple { background: #fff !important; border: 1px solid #c8e6c9 !important; color: #1a1a1a !important; border-radius: 10px !important; min-height: 42px; }
		.select2-container--default .select2-selection--single .select2-selection__rendered { color: #1a1a1a !important; }
		.select2-container--default .select2-selection--multiple .select2-selection__choice { background: rgba(40, 167, 69, 0.12) !important; border: 1px solid #c8e6c9 !important; color: #1a1a1a !important; border-radius: 6px !important; }
		.select2-dropdown { background: #fff !important; border-color: #c8e6c9 !important; }
		.select2-results__option { color: #1a1a1a !important; }
		.select2-results__option--highlighted { background: rgba(40, 167, 69, 0.12) !important; }
		.select2-search__field { background: #fff !important; color: #1a1a1a !important; }

		/* FilePond */
		.filepond--root { margin-bottom: 0; }
		.filepond--panel-root { background: #f9fdf7 !important; border: 1px solid #c8e6c9 !important; border-radius: 10px !important; }
		.filepond--drop-label { color: #555 !important; }
		.filepond--drop-label label { color: #555 !important; }
		.filepond--label-action { color: #2e7d32 !important; text-decoration: underline !important; }
		.filepond--item-panel { background: rgba(40, 167, 69, 0.12) !important; }

		/* Legacy dashboard-box */
		.dashboard-box { background: #fff; border: 1px solid #c8e6c9; border-radius: 14px; overflow: hidden; margin-bottom: 20px; }
		.dashboard-box .headline { background: rgba(40, 167, 69, 0.06); padding: 16px 20px; border-bottom: 1px solid #c8e6c9; }
		.dashboard-box .headline h3 { color: #1a1a1a !important; font-size: 1rem; font-weight: 700; margin: 0; }
		.dashboard-box .headline h3 i { color: #2e7d32; margin-right: 8px; }
		.dashboard-box .content { padding: 20px; }
		.content.with-padding { padding: 20px; }

		/* Submit fields */
		.submit-field { margin-bottom: 18px; }
		.submit-field h5 { color: #1a1a1a !important; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; }

		/* Legacy .with-border inputs */
		input.with-border, textarea.with-border, select.with-border {
			background: #fff !important; border: 1px solid #c8e6c9 !important; color: #1a1a1a !important;
			border-radius: 10px; padding: 10px 14px; width: 100%;
			font-family: 'Inter', sans-serif; font-size: 0.9rem; transition: all 0.25s ease; outline: none;
		}
		input.with-border:focus, textarea.with-border:focus, select.with-border:focus {
			border-color: #28a745 !important; box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.15);
		}
		input.with-border::placeholder { color: #999 !important; }

		/* Legacy .button */
		.button { background: linear-gradient(135deg, #28a745, #20c997) !important; color: #fff !important; border: none !important;
			padding: 12px 24px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;
			display: inline-block; text-decoration: none; font-size: 0.9rem; box-shadow: 0 4px 16px rgba(40, 167, 69, 0.2); }
		.button:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(40, 167, 69, 0.3) !important; color: #fff !important; }
		.button.gray { background: #f1f8e9 !important; border: 1px solid #c8e6c9 !important; box-shadow: none; color: #333 !important; }
		.button.gray:hover { background: #e8f5e9 !important; }
		.button.small { padding: 6px 14px; font-size: 0.8rem; }
		.button.red, .remove-repeater.btn-danger { background: rgba(220, 53, 69, 0.1) !important; color: #dc3545 !important; border: 1px solid rgba(220, 53, 69, 0.3) !important; }
		.button.red:hover, .remove-repeater.btn-danger:hover { background: rgba(220, 53, 69, 0.18) !important; }

		/* Notify box */
		.notify-box { display: flex; align-items: center; justify-content: space-between; background: #fff; border: 1px solid #c8e6c9; border-radius: 14px; padding: 16px 20px; margin-bottom: 16px; }
		.switch-container h3.page-title, h3.page-title { color: #1a1a1a !important; font-size: 1.1rem; font-weight: 700; margin: 0; }
		.sort-by { display: flex; align-items: center; gap: 8px; color: #555; font-size: 0.85rem; }
		.sort-by select, .selectpicker { background: #fff !important; border: 1px solid #c8e6c9 !important; color: #1a1a1a !important; border-radius: 8px; padding: 6px 12px; font-size: 0.85rem; }

		/* Job Listing Cards */
		.job-listing, a.job-listing { background: #fff !important; border: 1px solid #c8e6c9 !important; border-radius: 14px !important;
			margin-bottom: 12px !important; padding: 16px 20px !important; display: flex !important; align-items: center !important;
			text-decoration: none !important; transition: all 0.25s ease !important; color: #1a1a1a !important; }
		.job-listing:hover, a.job-listing:hover { border-color: #28a745 !important; background: #f9fdf7 !important; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(40, 167, 69, 0.08); }
		.job-listing-details { display: flex; align-items: center; width: 100%; gap: 16px; }
		.job-listing-company-logo { width: 48px; height: 48px; flex-shrink: 0; border-radius: 10px; overflow: hidden; background: #f1f8e9; }
		.job-listing-company-logo img { width: 100%; height: 100%; object-fit: cover; }
		.job-listing-description { flex: 1; }
		.job-listing-title { color: #1a1a1a !important; font-size: 1rem !important; font-weight: 600 !important; margin: 0 0 4px !important; }
		.job-listing-footer ul { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 16px; }
		.job-listing-footer ul li { color: #555; font-size: 0.82rem; display: flex; align-items: center; gap: 4px; }
		.job-listing-footer ul li i { color: #2e7d32; font-size: 0.9rem; }
		.job-listing-text { color: #555 !important; }
		.list-apply-button { background: rgba(40, 167, 69, 0.1) !important; color: #2e7d32 !important; border: 1px solid #c8e6c9 !important;
			padding: 8px 20px !important; border-radius: 50px !important; font-size: 0.82rem !important; font-weight: 600 !important;
			white-space: nowrap !important; flex-shrink: 0 !important; text-decoration: none !important; transition: all 0.25s ease !important; }
		.list-apply-button:hover { background: rgba(40, 167, 69, 0.18) !important; color: #1b5e20 !important; }

		/* Counters */
		.counters-container { display: flex; gap: 16px; flex-wrap: wrap; }
		.single-counter { flex: 1; min-width: 180px; background: #fff; border: 1px solid #c8e6c9; border-radius: 14px; padding: 20px; display: flex; align-items: center; gap: 14px; transition: all 0.25s ease; }
		.single-counter:hover { border-color: #28a745; background: #f9fdf7; }
		.single-counter i { font-size: 2rem; color: #2e7d32; }
		.counter-inner h3 { color: #1a1a1a; font-size: 1.4rem; font-weight: 800; margin: 0; }
		.counter-title { color: #555; font-size: 0.8rem; }

		/* Keyword select */
		.keyword-select { background: #fff !important; color: #1a1a1a !important; border: 1px solid #c8e6c9 !important; border-radius: 10px; width: 100%; }

		/* Borders */
		.border-start.border-warning { border-color: #f59e0b !important; }
		.border-start.border-success { border-color: #28a745 !important; }
		.border-start.border-primary { border-color: #28a745 !important; }
		.border-start.border-danger  { border-color: #ef4444 !important; }
		.border-top.border-warning { border-color: #f59e0b !important; }
		.border-top.border-success { border-color: #28a745 !important; }
		.border-top.border-primary { border-color: #28a745 !important; }
		.border-top.border-danger  { border-color: #ef4444 !important; }

		/* Text color utilities */
		.text-primary { color: #2e7d32 !important; }
		.text-success { color: #28a745 !important; }
		.text-warning { color: #f59e0b !important; }
		.text-danger  { color: #ef4444 !important; }

		.shadow-sm { box-shadow: 0 2px 12px rgba(40, 167, 69, 0.08) !important; }

		/* Legacy margins */
		.margin-top-15 { margin-top: 15px; } .margin-top-20 { margin-top: 20px; }
		.margin-top-30 { margin-top: 30px; } .margin-top-35 { margin-top: 35px; }
		.margin-top-45 { margin-top: 45px; } .margin-bottom-45 { margin-bottom: 45px; }

		/* Profile pic */
		.profile-pic, .pp { width: 100px; height: 100px; border-radius: 12px; object-fit: cover; border: 2px solid #c8e6c9; }
		.upload-button, .ub { cursor: pointer; }
		.fullscreen-modal { background-color: rgba(0, 0, 0, 0.9) !important; }

		/* Livewire */
		[wire\:id] .card, [wire\:id] .card-header, [wire\:id] .card-body { background: #fff !important; color: #1a1a1a !important; border-color: #c8e6c9 !important; }
		[wire\:id] input, [wire\:id] select, [wire\:id] textarea { background: #fff !important; border: 1px solid #c8e6c9 !important; color: #1a1a1a !important; border-radius: 10px !important; }
		[wire\:id] input:focus, [wire\:id] select:focus, [wire\:id] textarea:focus { border-color: #28a745 !important; box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.15) !important; }
		[wire\:id] label { color: #1a1a1a !important; font-weight: 600; font-size: 0.85rem; }
		[wire\:id] .text-dark { color: #1a1a1a !important; }

		.ripple-effect { position: relative; overflow: hidden; }

		/* Sidebar widget */
		.sidebar-widget { background: #fff; border: 1px solid #c8e6c9; border-radius: 14px; overflow: hidden; }
		.job-overview { background: transparent; }
		.job-overview-headline { background: rgba(40, 167, 69, 0.06); color: #2e7d32 !important; padding: 14px 20px; font-weight: 700; font-size: 0.95rem; border-bottom: 1px solid #c8e6c9; }
		.job-overview-inner ul { list-style: none; padding: 0; margin: 0; }
		.job-overview-inner ul li { padding: 12px 20px; border-bottom: 1px solid #e8f5e9; color: #1a1a1a; }
		.job-overview-inner ul li i { color: #2e7d32; margin-right: 8px; }
		.job-overview-inner ul li span { display: block; font-size: 0.78rem; color: #555; margin-bottom: 2px; }
		.job-overview-inner ul li h5 { font-size: 0.9rem !important; font-weight: 600; margin: 0; }

		/* Single page header */
		.single-page-header { background: #f9fdf7 !important; padding: 30px 0; }
		.single-page-header-inner { display: flex; align-items: center; gap: 20px; }
		.header-image img { width: 60px; height: 60px; border-radius: 12px; object-fit: cover; border: 2px solid #c8e6c9; }
		.header-details h3, .header-details h5 { color: #1a1a1a !important; }
		.header-details ul { list-style: none; padding: 0; margin: 8px 0 0; display: flex; flex-wrap: wrap; gap: 16px; }
		.header-details ul li { color: #555; font-size: 0.85rem; }
		.header-details ul li i { color: #2e7d32; margin-right: 4px; }
		.section-headline h3 { color: #1a1a1a !important; font-weight: 700; }

		/* Basic table */
		.basic-table { border: 1px solid #c8e6c9 !important; }
		.basic-table th { background: rgba(40, 167, 69, 0.08) !important; color: #2e7d32 !important; border-bottom: 1px solid #c8e6c9 !important; }
		.basic-table td { border-bottom: 1px solid #e8f5e9 !important; color: #1a1a1a !important; }
		.basic-table tr:hover { background: rgba(40, 167, 69, 0.03) !important; }
		.basic-table tfoot tr { background: rgba(40, 167, 69, 0.06) !important; }
		.basic-table input[type="number"] { background: #fff !important; border: 1px solid #c8e6c9 !important; color: #1a1a1a !important; border-radius: 8px; }
		.basic-table input[type="number"]:disabled { background: #f5f5f5 !important; color: #999 !important; }

		/* Invoice */
		.invoice-card { background: #fff !important; border: 1px solid #c8e6c9 !important; }
		.invoice-details { color: #1a1a1a !important; }
		.invoice-details strong { color: #2e7d32 !important; }
		.invoice-table thead th { background: rgba(40, 167, 69, 0.08) !important; color: #2e7d32 !important; }
		.invoice-table td { border-bottom: 1px solid #e8f5e9 !important; color: #1a1a1a !important; }
		.invoice-summary { background: #f9fdf7 !important; color: #1a1a1a !important; }

		/* Apply now button */
		.apply-now-button { background: linear-gradient(135deg, #28a745, #20c997) !important; color: #fff !important; border: none !important; border-radius: 50px; font-weight: 600; box-shadow: 0 4px 16px rgba(40, 167, 69, 0.2); }
		.apply-now-button:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(40, 167, 69, 0.3) !important; }

		/* Notification notice */
		.notification.notice { background: #f9fdf7 !important; border: 1px solid #c8e6c9 !important; border-radius: 12px; padding: 20px; color: #555 !important; }

		/* Select dropdowns */
		select, select.form-select, select.with-border, select.form-control {
			-webkit-appearance: none; -moz-appearance: none; appearance: none;
			background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333333' d='M6 8.825L.587 3.412l.826-.824L6 7.175l4.587-4.587.826.824z'/%3E%3C/svg%3E") !important;
			background-repeat: no-repeat !important; background-position: right 14px center !important; background-size: 12px !important;
			padding-right: 36px !important; background-color: #fff !important; border: 1px solid #c8e6c9 !important; color: #1a1a1a !important; border-radius: 10px !important;
		}
		select option { background: #fff; color: #1a1a1a; }

		/* BS-Stepper */
		.bs-stepper { background: transparent !important; }
		.bs-stepper .bs-stepper-circle { background: #f1f8e9 !important; color: #555 !important; border: 2px solid #c8e6c9 !important; }
		.bs-stepper .step.active .bs-stepper-circle { background: #28a745 !important; border-color: #28a745 !important; color: #fff !important; }
		.bs-stepper-line { background-color: #c8e6c9 !important; }
		.steper-title { color: #1a1a1a !important; }
		.steper-sub-title { color: #555 !important; }
		.bs-stepper-content { padding: 24px 20px !important; }
		.bs-stepper-content label, .bs-stepper-content .form-label, .dashboard-box label { display: block; font-weight: 600; font-size: 0.88rem; color: #1a1a1a !important; margin-bottom: 8px; margin-top: 4px; }
		.bs-stepper-content .row.g-3 > [class*="col"] { margin-bottom: 18px; }
		.bs-stepper-content select, .dashboard-box select { width: 100%; padding: 11px 36px 11px 14px !important; font-size: 0.9rem; }
		.bs-stepper-content select:focus, .dashboard-box select:focus { border-color: #28a745 !important; box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.15) !important; }
		.bs-stepper-content input.form-control, .bs-stepper-content .form-control { padding: 11px 14px !important; font-size: 0.9rem; }
		.bs-stepper-content .button { padding: 12px 28px !important; font-size: 0.95rem; font-weight: 600; margin-top: 10px; }
		.bs-stepper-content .button.gray { background: #f1f8e9 !important; color: #555 !important; border: 1px solid #c8e6c9 !important; }
		.bs-stepper-content .button.gray:hover { background: #e8f5e9 !important; color: #1a1a1a !important; }
		.bs-stepper .card-header { padding: 16px 20px !important; border-bottom: 1px solid #c8e6c9 !important; }
		.bs-stepper .card-body { padding: 0 !important; }
		.bs-stepper-content hr { border-color: #c8e6c9 !important; margin: 15px 0; }
		.bs-stepper-content h3 { font-size: 1.15rem !important; color: #333 !important; font-weight: 500; }
		.bs-stepper-content h5 { font-size: 0.9rem !important; color: #2e7d32 !important; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
		.bs-stepper-content .card { background: #fff !important; border: 1px solid #c8e6c9 !important; border-radius: 14px !important; }
		.bs-stepper-content .card .card-body { padding: 24px !important; }
		.bs-stepper-content .card .card-title { color: #1a1a1a !important; font-size: 1.05rem !important; font-weight: 700 !important; }
		.bs-stepper-content .card .card-text { color: #555 !important; font-size: 0.88rem; }

		/* Voice recorder */
		.voice-recorder-container { background: #fff; border: 1px solid #c8e6c9; border-radius: 14px; padding: 20px; margin-top: 10px; }
		.recording-active { color: #dc3545 !important; font-size: 0.88rem; }
		.recording-indicator { width: 12px; height: 12px; background-color: #dc3545; border-radius: 50%; animation: recPulse 1.5s ease-in-out infinite; flex-shrink: 0; }
		@keyframes recPulse { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.3); opacity: 0.4; } 100% { transform: scale(1); opacity: 1; } }
		#startRecording { background: linear-gradient(135deg, #28a745, #20c997) !important; border: none !important; color: #fff !important; font-weight: 600; padding: 10px 24px !important; font-size: 0.9rem; }
		#startRecording:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(40, 167, 69, 0.2); }
		#stopRecording { background: rgba(220, 53, 69, 0.1) !important; border: 1px solid rgba(220, 53, 69, 0.3) !important; color: #dc3545 !important; font-weight: 600; padding: 10px 24px !important; }
		#stopRecording:not(:disabled):hover { background: rgba(220, 53, 69, 0.18) !important; }
		#stopRecording:disabled { opacity: 0.4; cursor: not-allowed; }
		#deleteRecording { background: rgba(220, 53, 69, 0.08) !important; border: 1px solid rgba(220, 53, 69, 0.25) !important; color: #dc3545 !important; font-weight: 600; padding: 8px 20px !important; }
		#deleteRecording:hover { background: rgba(220, 53, 69, 0.15) !important; }
		audio { width: 100%; max-width: 400px; border-radius: 10px; outline: none; }
		#audioPreview { background: #f9fdf7; border: 1px solid #c8e6c9; border-radius: 12px; padding: 16px; display: flex; align-items: center; flex-wrap: wrap; gap: 12px; }
		#recordingStatus { background: rgba(220, 53, 69, 0.05); border: 1px solid rgba(220, 53, 69, 0.15); border-radius: 10px; padding: 12px 16px; }

		/* Overlay */
		#garageSubmitOverlay { background: rgba(255, 255, 255, 0.85) !important; }
		#garageSubmitOverlay .fw-bold { color: #1a1a1a !important; }

		/* Commission info glow */
		.commission-info-icon { width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
			font-weight: bold; font-size: 14px; cursor: pointer; color: #fff; background: linear-gradient(135deg, #28a745, #20c997); box-shadow: 0 0 8px rgba(40, 167, 69, 0.4); user-select: none; }

		/* Padding utilities */
		.padding-left-20 { padding-left: 20px; }
		.padding-right-20 { padding-right: 20px; }

		/* ---- Footer ---- */
		.sp-footer {
			background: linear-gradient(135deg, #ffffff, #f1f8e9);
			border-top: 2px solid #c8e6c9;
			padding: 24px 0;
			margin-top: auto;
		}

		.sp-footer-inner { max-width: 1200px; margin: 0 auto; padding: 0 16px; display: flex; align-items: center; justify-content: space-between; }
		.sp-footer-brand { font-size: 1.2rem; font-weight: 800; color: #2e7d32; }
		.sp-footer-copy { color: #555; font-size: 0.8rem; }
		.sp-footer-social { display: flex; gap: 12px; list-style: none; margin: 0; padding: 0; }
		.sp-footer-social a { color: #555; font-size: 1.1rem; transition: color 0.2s; }
		.sp-footer-social a:hover { color: #2e7d32; }

		/* Tooltip */
		.tooltip-inner { background-color: #fff !important; color: #1a1a1a !important; font-size: 0.85rem; padding: 10px 14px; border-radius: 8px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); border: 1px solid #c8e6c9; }

		/* ---- Responsive ---- */
		@media (max-width: 992px) {
			.sp-nav { display: none; position: absolute; top: 64px; left: 0; right: 0; background: #fff; flex-direction: column; padding: 16px; border-bottom: 2px solid #c8e6c9; gap: 4px; }
			.sp-nav.open { display: flex; }
			.sp-mobile-toggle { display: block; }
			.sp-user-name-text { display: none; }
			.sp-footer-inner { flex-direction: column; gap: 12px; text-align: center; }
		}

		@media (max-width: 575px) {
			.container { padding-left: 12px; padding-right: 12px; }
			.dashboard-box .content.with-padding { padding: 15px 12px !important; }
			.dashboard-box .headline h3 { font-size: 1rem; }
			.notify-box { flex-direction: column !important; align-items: flex-start !important; gap: 10px; }
			.page-title { font-size: 1.1rem !important; }
			.sort-by { width: 100%; }
			.sort-by select { width: 100% !important; }
			.table-container, .job-listing-footer { overflow-x: auto; -webkit-overflow-scrolling: touch; }
			.basic-table { min-width: 600px; }
			.job-listing-details { flex-direction: column !important; gap: 10px; }
			.list-apply-button { width: 100% !important; text-align: center; margin-top: 10px; }
			.single-page-header-inner { flex-direction: column !important; text-align: center; }
			.header-details ul { justify-content: center; }
			.sidebar-col, .sidebar-container, .content-left-offset { margin-left: 0 !important; padding-left: 0 !important; }
			.submit-field { margin-bottom: 15px; }
			.button.big { width: 100%; text-align: center; }
			.bs-stepper .step-trigger { padding: 8px !important; }
			.bs-stepper-line { display: none !important; }
			.bs-stepper .d-lg-flex { flex-direction: column !important; gap: 8px; }
			.bs-stepper-circle { width: 35px !important; height: 35px !important; font-size: 14px !important; }
			.steper-title { font-size: 0.85rem !important; }
			.steper-sub-title { font-size: 0.75rem !important; }
			.fun-facts-container .fun-fact { min-width: 100% !important; }
		}

		@media (max-width: 991px) {
			.sidebar-col { margin-left: 0 !important; margin-bottom: 20px; }
			.content-left-offset { margin-left: 0 !important; padding-left: 0 !important; }
			.bs-stepper .step-trigger { padding: 10px !important; }
		}

		@media (max-width: 1199px) {
			.container { max-width: 100%; }
		}

		/* Animated Background Circles — now light */
		.etera-bg-circles { display: none; }
	</style>

	@if(auth()->check() && auth()->user()->role == 'garage')
		<title>etera - Garages</title>
	@elseif(auth()->check() && auth()->user()->role == 'shop')
		<title>etera - Spare Part Shops</title>
	@endif

	<!-- React + Babel CDN -->
	@include('partials.react-head')
	@include('partials.green-theme')

	@if(auth()->check() && auth()->user()->role == 'garage')
		<title>etera - Garages</title>
	@elseif(auth()->check() && auth()->user()->role == 'shop')
		<title>etera - Spare Part Shops</title>
	@endif

	<!-- React + Babel CDN -->
	@include('partials.react-head')

</head>

<body>

@php
    $commission = \App\Models\Commission::first();
    $role = auth()->user()->role ?? null;
    $payAmount = match ($role) {
        'garage' => $commission?->garagePay,
        'shop'   => $commission?->shopPay,
        default  => null,
    };
@endphp

<!-- Animated Background Circles -->
<div class="etera-bg-circles">
	<div class="circle"></div>
	<div class="circle"></div>
	<div class="circle"></div>
</div>

<!-- Header -->
<header class="sp-header">
	<div class="sp-header-inner">
		<!-- Logo -->
		<a href="/spare-part-shops" class="sp-logo">
			<img src="{{asset('assets/images/transparent.svg')}}" alt="etera">
		</a>

		<!-- Mobile Toggle -->
		<button class="sp-mobile-toggle" id="sp-mobile-toggle" aria-label="Toggle navigation">
			<i class="bi bi-list"></i>
		</button>

		<!-- Navigation -->
		<ul class="sp-nav" id="sp-nav">
			@if(auth()->user()?->role == 'shop')
				<li><a href="/spare-part-shops" class="sp-nav-link @yield('applications')">Dashboard</a></li>
				<li><a href="/spare-part-shops/proformas" class="sp-nav-link @yield('insurance')">Proformas</a></li>
				<li><a href="/spare-part-shops/balance" class="sp-nav-link @yield('balance')">Balance</a></li>
				<li>
					<a href="/spare-part-shops/inbox" class="sp-nav-link @yield('inbox')">
						Inbox
						@if(auth()->user()->getInboxCount() > 0)
							<span class="sp-badge">{{ auth()->user()->getInboxCount() }}</span>
						@endif
					</a>
				</li>
			@endif

			@if(auth()->user()?->role == 'garage')
				<li><a href="/garage" class="sp-nav-link @yield('applications')">Dashboard</a></li>
				<li><a href="/garage/proformas" class="sp-nav-link @yield('insurance')">Proformas</a></li>
				<li><a href="/garage/my-files" class="sp-nav-link @yield('post')">My Files</a></li>
				<li><a href="/garage/create-file" class="sp-nav-link">Request Proforma</a></li>
				<li>
					<a href="/garage/received-proformas" class="sp-nav-link @yield('received')">
						Received
						@if(auth()->user()->getReceivedProformasCount() > 0)
							<span class="sp-badge sp-badge-primary">{{ auth()->user()->getReceivedProformasCount() }}</span>
						@endif
						@if(auth()->user()->getReturnedFromAdminCount() > 0)
							<span class="sp-badge">{{ auth()->user()->getReturnedFromAdminCount() }}</span>
						@endif
					</a>
				</li>
				<li><a href="/garage/balance" class="sp-nav-link @yield('balance')">Balance</a></li>
				<li>
					<a href="/garage/inbox" class="sp-nav-link @yield('inbox')">
						Inbox
						@if(auth()->user()->getInboxCount() > 0)
							<span class="sp-badge sp-badge-warning">{{ auth()->user()->getInboxCount() }}</span>
						@elseif(auth()->user()->unReadMessages() > 0)
							<span class="sp-badge">{{ auth()->user()->unReadMessages() }}</span>
						@endif
					</a>
				</li>
			@endif
		</ul>

		<div class="nav-item dark-mode d-sm-flex" style="margin-left: 10px;">
			<a class="nav-link dark-mode-icon" href="javascript:;" aria-label="Toggle dark mode">
				<i class='bx bx-moon'></i>
			</a>
		</div>

		<!-- User Menu -->
		<div class="dropdown sp-user-menu">
			<a href="#" class="sp-user-trigger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
				<div class="sp-avatar">
					<i class="bi bi-person-fill sp-avatar-icon" aria-hidden="true"></i>
				</div>
				<div class="sp-user-name-text">
					<span class="sp-user-name">{{ucfirst(auth()->user()->name)}}</span>
					<span class="sp-user-role">{{ucfirst(auth()->user()->role)}}</span>
				</div>
				<i class="bi bi-chevron-down sp-user-caret" aria-hidden="true"></i>
			</a>
			<ul class="dropdown-menu dropdown-menu-end">
				@if(auth()->user()->role == 'garage')
					<li><a class="dropdown-item" href="/garage/profile"><i class="bi bi-gear"></i> Settings</a></li>
				@elseif(auth()->user()->role == 'shop')
					<li><a class="dropdown-item" href="/spare-part-shops/profile"><i class="bi bi-gear"></i> Settings</a></li>
				@endif
				<li><div class="dropdown-divider mb-0"></div></li>
				<li>
					<form action="{{route('logout')}}" method="POST">
						@method("DELETE")
						@csrf
						<button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Logout</button>
					</form>
				</li>
			</ul>
		</div>
	</div>
</header>

@php
    $othersPay = $commission?->othersPay;
@endphp

<!-- Commission Banner -->
@if($payAmount || $othersPay)
<div class="sp-commission-banner">
	@if($payAmount && $othersPay)
		💰 Earn <strong>{{ $payAmount }} birr</strong> for every <strong>Insurance Proforma</strong> and <strong>{{ $othersPay }} birr</strong> for every <strong>Others Proforma</strong> you fill out!
	@elseif($payAmount)
		💰 Earn <strong>{{ $payAmount }} birr</strong> for every <strong>Insurance Proforma</strong> you fill out!
	@elseif($othersPay)
		💰 Earn <strong>{{ $othersPay }} birr</strong> for every <strong>Others Proforma</strong> you fill out!
	@endif
</div>
@endif

<!-- Success Messages -->
@if(session('success'))
<div class="px-3 px-md-4 mt-3" style="position: sticky; top: 80px; z-index: 1050;">
	<div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
		{{ session('success') }}
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
</div>
@endif

<!-- Main Content -->
<div class="sp-main-content">
	<div class="sp-content-wrapper">
		@yield('content')
	</div>
</div>

<!-- Footer -->
<footer class="sp-footer">
	<div class="sp-footer-inner">
		<span class="sp-footer-brand">etera</span>
		<span class="sp-footer-copy">© <script>document.write(new Date().getFullYear())</script>. All rights reserved.</span>
		<ul class="sp-footer-social">
			<li><a href="#"><i class="bi bi-facebook"></i></a></li>
			<li><a href="#"><i class="bi bi-telegram"></i></a></li>
			<li><a href="#"><i class="bi bi-tiktok"></i></a></li>
		</ul>
	</div>
</footer>

<!-- Toast Notifications (React) -->
@include('partials.toast')

<!-- CSRF Auto-Refresh -->
@include('partials.csrf-refresh')

<!-- Scripts (jQuery → Bootstrap → plugins) -->
<script src="{{asset('asset/js/jquery-3.4.1.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>

<script>
	// Mobile nav toggle
	document.getElementById('sp-mobile-toggle')?.addEventListener('click', function() {
		document.getElementById('sp-nav')?.classList.toggle('open');
	});

	// Bootstrap tooltip init
	document.addEventListener('DOMContentLoaded', function () {
		var tooltipList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
		tooltipList.map(function (el) { return new bootstrap.Tooltip(el); });

		// Allow modal buttons to receive clicks
		document.querySelectorAll('.modal .btn, .modal .btn-close, .modal [data-bs-dismiss], .modal [data-bs-slide]')
			.forEach(function(el){ el.style.pointerEvents = 'auto'; });
	});
</script>

@hasSection('minimalScripts')
@else
<script src="{{asset('asset/js/mmenu.min.js')}}"></script>
<script src="{{asset('asset/js/tippy.all.min.js')}}"></script>
<script src="{{asset('asset/js/simplebar.min.js')}}"></script>
<script src="{{asset('asset/js/bootstrap-slider.min.js')}}"></script>
<script src="{{asset('asset/js/bootstrap-select.min.js')}}"></script>
<script src="{{asset('asset/js/snackbar.js')}}"></script>
<script src="{{asset('asset/js/clipboard.min.js')}}"></script>
<script src="{{asset('asset/js/counterup.min.js')}}"></script>
<script src="{{asset('asset/js/magnific-popup.min.js')}}"></script>
<script src="{{asset('asset/js/slick.min.js')}}"></script>
<script src="{{asset('asset/js/custom.js')}}"></script>
@endif

<!-- Additional Plugin Scripts -->
<script src="{{asset('assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
<script src="{{asset('assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
<script src="{{asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
<script src="{{asset('assets/plugins/Drag-And-Drop/dist/imageuploadify.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{asset('assets/plugins/select2/js/select2-custom.js')}}"></script>
{{-- lobibox/notification scripts removed – replaced by React toast system --}}
<script src="{{asset('assets/js/app.js')}}"></script>

<!-- FilePond Scripts -->
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-crop/dist/filepond-plugin-image-crop.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js"></script>
<script src="{{asset('assets/plugins/filepond/filepond.js')}}"></script>

@livewireScripts

<script>
	// FilePond initialization
	document.addEventListener("DOMContentLoaded", function() {
		const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

		// Fix PerfectScrollbar initialization error
		try {
			if (typeof PerfectScrollbar !== 'undefined') {
				const scrollElement = document.querySelector('.perfect-scrollbar');
				if (scrollElement) new PerfectScrollbar(scrollElement);
			}
		} catch (e) { /* ignore */ }

		// Initialize FilePond instances
		const filePondInstances = ['licenseBusiness', 'stampBusiness', 'licenseGarage', 'stampGarage', 'licenseShop', 'stampShop', 'audio', 'video'];
		filePondInstances.forEach(id => {
			const element = document.querySelector(`#${id}`);
			if (element) {
				FilePond.create(element).setOptions({
					allowMultiple: true,
					credits: false,
					imageResizeMode: 'contain',
					imagePreviewMaxFileSize: '3MB',
					acceptedFileTypes: id === 'audio' || id === 'video' ? null : ['image/*'],
					server: {
						process: `/upload/${id === 'audio' ? 'audio' : id === 'video' ? 'video' : 'image'}`,
						revert: '/delete',
						headers: { 'X-CSRF-TOKEN': csrfToken }
					}
				});
			}
		});

		// Image uploadify initialization
		const imageUploadify = document.getElementById('image-uploadify');
		if (imageUploadify && typeof $.fn.imageuploadify !== 'undefined') {
			$(imageUploadify).imageuploadify();
		}
	});

	// Remaining time countdown for Etera-Chereta proformas
	function updateRemainingTime() {
		document.querySelectorAll('[data-remaining-time]').forEach(element => {
			const expiresAt = element.getAttribute('data-remaining-time');
			if (!expiresAt) return;
			const now = new Date();
			const expiry = new Date(expiresAt);

			if (expiry > now) {
				const diff = Math.floor((expiry - now) / 1000);
				const hours = Math.floor(diff / 3600);
				const minutes = Math.floor((diff % 3600) / 60);
				element.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

				if (diff < 3600) { element.classList.remove('bg-primary'); element.classList.add('bg-warning'); }
				if (diff < 900) { element.classList.remove('bg-warning'); element.classList.add('bg-danger'); }
			} else {
				element.textContent = 'Expired';
				element.classList.remove('bg-primary', 'bg-warning');
				element.classList.add('bg-secondary');
			}
		});
	}

	setInterval(updateRemainingTime, 60000);
	document.addEventListener('DOMContentLoaded', function() {
		updateRemainingTime();

		// Initialize tooltips
		var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
		tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); });

		// Auto-dismiss success flash messages after 5 seconds
		const alerts = document.querySelectorAll('.alert.alert-success');
		if (alerts.length > 0) {
			setTimeout(() => {
				alerts.forEach(el => {
					el.style.transition = 'opacity .4s ease';
					el.style.opacity = '0';
					setTimeout(() => el.remove(), 400);
				});
			}, 5000);
		}
	});

	// Google Autocomplete
	function initAutocomplete() {
		try {
			var options = { types: ['(cities)'] };
			var input = document.getElementById('autocomplete-input');
			if (!input) return;
			var autocomplete = new google.maps.places.Autocomplete(input, options);
		} catch (e) { /* ignore */ }
	}

	document.addEventListener('DOMContentLoaded', function() {
		const introBanner = document.querySelector('.intro-banner-search-form');
		if (introBanner) {
			setTimeout(function(){ $(".pac-container").prependTo(".intro-search-field.with-autocomplete"); }, 300);
		}
	});
</script>

<!-- Google API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAaoOT9ioUE4SA8h-anaFyU4K63a7H-7bc&libraries=places&callback=initAutocomplete&loading=async"></script>

<!-- Auto-Logout after 30 minutes inactivity -->
<script>
(function() {
    var IDLE_TIMEOUT = 30 * 60 * 1000;
    var idleTimer;
    function resetTimer() {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(logoutUser, IDLE_TIMEOUT);
    }
    function logoutUser() {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/logout';
        form.innerHTML = '<input type="hidden" name="_method" value="DELETE">' +
                         '<input type="hidden" name="_token" value="' +
                         (document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}') + '">';
        document.body.appendChild(form);
        form.submit();
    }
    ['mousemove', 'mousedown', 'keypress', 'scroll', 'touchstart', 'click'].forEach(function(evt) {
        document.addEventListener(evt, resetTimer, { passive: true });
    });
    resetTimer();
})();
</script>

@include('partials.etera-scripts')
@include('partials.notification-polling')
@stack('scripts')
</body>
</html>
