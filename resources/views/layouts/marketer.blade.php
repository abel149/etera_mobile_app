<!doctype html>
<html lang="en" class="light">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="{{asset('favicon.ico')}}" type="image/x-icon"/>
	<link rel="icon" href="{{asset('assets/images/transparent.svg')}}" type="image/jpeg"/>
	<!--plugins-->
	<link rel="stylesheet" href="{{asset('assets/plugins/notifications/css/lobibox.min.css')}}" />
	<link href="{{asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet"/>
	<link href="{{asset('assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/Drag-And-Drop/dist/imageuploadify.min.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet"/>
	<!-- loader-->
	<link href="{{asset('assets/css/pace.min.css')}}" rel="stylesheet"/>
	<script src="{{asset('assets/js/pace.min.js')}}"></script>
	<!-- Bootstrap CSS -->
	<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/bootstrap-extended.css')}}" rel="stylesheet">
	<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

	<link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2.min.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2-bootstrap-5-theme.min.css')}}" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

	<link href="{{asset('assets/css/app.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/icons.css')}}" rel="stylesheet">
	<!-- Theme Style CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/dark-theme.css')}}"/>
	<link rel="stylesheet" href="{{asset('assets/css/semi-dark.css')}}"/>
	<link rel="stylesheet" href="{{asset('assets/css/header-colors.css')}}"/>
	@livewireStyles

	<style type="text/css">
	.tbl td:last-child {
      white-space: nowrap;
      width: 1%; /* Makes the column take as little space as possible based on content */
      
		/* Layout */
		html, body {
			height: 100%;
			margin: 0;
			padding: 0;
		}

		/* Flexbox Layout */
		body {
			display: flex;
			flex-direction: column;
		}

		.content {
			flex: 1; /* Takes up remaining space */
		}

		footer {
			background-color: #333;
			color: #fff;
			text-align: center;
			padding: 10px 0;
		}

		/* Header Styling */
		#header-container {
			background: #fff;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
			position: relative;
			z-index: 1000;
		}

		#logo img {
			max-width: 7.5rem;
		}

		.nav-list {
			display: flex;
			align-items: center;
			margin: 0;
			padding: 0;
			list-style: none;
		}

		.nav-list li {
			margin-right: 20px;
		}

		.nav-item {
			color: #333;
			text-decoration: none;
			font-weight: 500;
			padding: 10px 0;
			position: relative;
			transition: color 0.3s;
		}

		.nav-item:hover {
			color: #2a41e8;
		}

		.nav-item.active {
			color: #2a41e8;
		}

		.nav-item.active:after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 0;
			width: 100%;
			height: 3px;
			background: #2a41e8;
		}

		/* User Menu */
		.header-widget {
			display: flex;
			align-items: center;
		}

		.user-avatar {
			width: 40px;
			height: 40px;
			border-radius: 50%;
			overflow: hidden;
		}

		.user-avatar img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.header-notifications-dropdown {
			position: absolute;
			top: 100%;
			right: 0;
			width: 280px;
			background: #fff;
			border-radius: 4px;
			box-shadow: 0 5px 25px rgba(0,0,0,0.1);
			z-index: 1001;
			display: none;
		}

		.header-notifications:hover .header-notifications-dropdown,
		.user-menu:hover .header-notifications-dropdown {
			display: block;
		}

		/* Badge Styling (for unread messages) */
		.badge {
			background-color: #E74C3C;
			color: #fff;
			padding: 2px 8px;
			border-radius: 50%;
			font-size: 12px;
			margin-left: 8px;
		}

		/* Pagination */
		.pagination-container {
			margin-top: 20px; /* Add margin to the top of the pagination */
			margin-bottom: 30px; /* Add margin to the bottom to give spacing from the bottom of the page */
		}

		.pagination {
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 10px;
			list-style: none;
			padding: 0;
			margin: 0;
		}

		.pagination li {
			padding: 5px 10px;
			border: 1px solid #ddd;
			border-radius: 4px;
			cursor: pointer;
		}

		.pagination li:hover {
			background-color: #f0f0f0;
		}

		.pagination .active {
			background-color: #007bff;
			color: #fff;
		}

		.pagination li.disabled {
			cursor: not-allowed;
			opacity: 0.5;
		}

		/* Footer - Updated to match the second code */
		.footer-top-section {
			background: #333; /* Changed to match the second code */
			color: #fff;
			padding: 40px 0;
		}

		.footer-bottom-section {
			background: #333; /* Changed to match the second code */
			color: #fff;
			padding: 20px 0;
		}

		.footer-social-links {
			display: flex;
			list-style: none;
			padding: 0;
			margin: 0;
		}

		.footer-social-links li {
			margin-right: 15px;
		}

		.footer-social-links a {
			color: #fff;
			font-size: 18px;
		}

		/* Remove bold text styles */
		.user-name span,
		.notification-text strong,
		.footer-bottom-section strong {
			font-weight: normal; /* Changed from bold to normal */
		}

		/* Responsive */
		@media (max-width: 992px) {
			.nav-list {
				flex-direction: column;
				align-items: flex-start;
			}
			
			.nav-list li {
				margin-right: 0;
				margin-bottom: 10px;
			}
			
			
		}

		/* Ensure modals work properly */
		.modal {
			z-index: 1060;
		}

		.modal-backdrop {
			z-index: 1050;
			pointer-events: none !important; /* Allow interactions with modal controls above */
		}
		/* Commission Info Icon */
.commission-info-wrapper {
    display: inline-block;
    margin-left: 10px;
}

.commission-info-icon {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
    color: #fff;
    background: linear-gradient(45deg, #00ff99, #0066ff);
    box-shadow: 0 0 8px rgba(0, 255, 153, 0.8);
    animation: commissionGlow 1.5s infinite alternate;
    user-select: none;
}

/* Flashing green & blue */
@keyframes commissionGlow {
    0% {
        box-shadow: 0 0 6px #00ff99;
        background: #00ff99;
    }
    100% {
        box-shadow: 0 0 14px #0066ff;
        background: #0066ff;
    }
}
/* Commission tooltip – white background & big text */
.tooltip-inner {
    background-color: #ffffff !important;
    color: #000000 !important;
    font-size: 18px;
    font-weight: 600;
    padding: 14px 18px;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    max-width: 320px;
    text-align: center;
}

/* Tooltip arrow (Bootstrap 4) */
.tooltip.bs-tooltip-bottom .arrow::before,
.tooltip.bs-tooltip-top .arrow::before,
.tooltip.bs-tooltip-left .arrow::before,
.tooltip.bs-tooltip-right .arrow::before {
    border-bottom-color: #ffffff !important;
    border-top-color: #ffffff !important;
    border-left-color: #ffffff !important;
    border-right-color: #ffffff !important;
}


    }
</style>
	<title> etera | Marketer</title>
@include('partials.green-theme')
</head>

<body>
	<!--wrapper-->
	<div class="wrapper">
		<!--sidebar wrapper -->
		<div class="sidebar-wrapper" data-simplebar="true">
			<div class="sidebar-header">
				<div>

					<img src="{{asset('assets/images/transparent.svg')}}" class="logo-text" style="max-width: 7.5rem;" alt="etera">

				</div>
				<div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
				</div>
			 </div>
			<!--navigation-->
			<ul class="metismenu" id="menu">
				<li>
					<a href="/marketer">
						<div class="parent-icon"><i class='bx bx-home-alt'></i>
						</div>
						<div class="menu-title">Dashboard</div>
					</a>
				</li>
				<li>
					<a href="/marketer/proformas">
						<div class="parent-icon"><i class="bx bx-file"></i>
						</div>
						<div class="menu-title">Proformas</div>
					</a>
				</li>
				<li>
					<a href="javascript:;" class="has-arrow">
						<div class="parent-icon"><i class='bx bx-group'></i>
						</div>
						<div class="menu-title">Users</div>
					</a>
					<ul>
						<li> <a href="/marketer/insurances"><i class='bx bx-radio-circle'></i>Insurances</a>
						</li>
						<li> <a href="/marketer/spare-part-shops"><i class='bx bx-radio-circle'></i>Spare part shops</a>
						</li>
						<li> <a href="/marketer/garages"><i class='bx bx-radio-circle'></i>Garages</a>
						</li>
						<li> <a href="/marketer/business-owners"><i class='bx bx-radio-circle'></i>Others</a>
						</li>
					</ul>
				</li>
			</ul>
			<!--end navigation-->
		</div>
		<!--end sidebar wrapper -->
		<!--start header -->
		<header>
			<!-- Minimize side bar -->
			<div class="app-container p-2 my-2"></div>
			<!-- end minimize side bar -->
			<div class="topbar d-flex align-items-center">
				<nav class="navbar navbar-expand gap-3">
					<div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
					</div>

					<!--   <div class="position-relative search-bar d-lg-block d-none" data-bs-toggle="modal" data-bs-target="#SearchModal">
						<input class="form-control px-5" disabled type="search" placeholder="Search">
						<span class="position-absolute top-50 search-show ms-3 translate-middle-y start-0 top-50 fs-5"><i class='bx bx-search'></i></span>
					  </div> -->


					  <div class="top-menu ms-auto">
						<ul class="navbar-nav align-items-center gap-1">
							
							<li class="nav-item dark-mode d-none d-sm-flex">
								<a class="nav-link dark-mode-icon" href="javascript:;"><i class='bx bx-moon'></i>
								</a>
							</li>
							<li class="nav-item dropdown dropdown-large">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" data-bs-toggle="dropdown"><span class="alert-count">7</span>
									<i class='bx bx-bell'></i>
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<a href="javascript:;">
										<div class="msg-header">
											<p class="msg-header-title">Notifications</p>
											<p class="msg-header-badge">8 New</p>
										</div>
									</a>
									<div class="header-notifications-list">
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="user-online">
													<img src="{{asset('assets/images/avatars/avatar-1.png')}}" class="msg-avatar" alt="user avatar">
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">Daisy Anderson<span class="msg-time float-end">5 sec
												ago</span></h6>
													<p class="msg-info">The standard chunk of lorem</p>
												</div>
											</div>
										</a>
									</div>
									<a href="javascript:;">
										<div class="text-center msg-footer">
											<button class="btn btn-primary w-100">View All Notifications</button>
										</div>
									</a>
								</div>
							</li>
							<li class="nav-item dropdown dropdown-large">
									<div class="header-message-list">
										
									</div>
							</li>
						</ul>
					</div>
					<div class="user-box dropdown px-3">
						<a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<img src="{{asset('assets/images/avatars/avatar-9.jpg')}}" class="user-img" alt="user avatar">
							<div class="user-info">
								<p class="user-name mb-0">{{auth()->user()->name}}</p>
								<p class="designattion mb-0">{{auth()->user()->role}}</p>
							</div>
						</a>
						<ul class="dropdown-menu dropdown-menu-end">
				
							<li>
								<div class="dropdown-divider mb-0"></div>
							</li>
<li><form action="{{route('logout')}}" method="POST">
							        @csrf
							        @method('DELETE')
							    <button class="dropdown-item d-flex align-items-center" ><i class="bx bx-log-out-circle"></i><span>Logout</span></button>
							    </form>
							</li>

						</ul>
					</div>
				</nav>
			</div>
		</header>
		<!--end header -->
		<!-- Success Messages -->
		@if(session('success'))
		<div class="px-3 px-md-4 mt-3" style="position: sticky; top: 80px; z-index: 1050;">
			<div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
				{{ session('success') }}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		</div>
		@endif
		@yield('content')
		<!--start overlay-->
		 <div class="overlay toggle-icon"></div>
		<!--end overlay-->
		<!--Start Back To Top Button-->
		  <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<!--End Back To Top Button-->
		
		<footer class="page-footer text-center">
    <p class="mb-1">
        © <script>document.write(new Date().getFullYear())</script>. All rights reserved.
    </p>

<!--    <p class="mb-0">
        Made by 
        <a href="https://www.primetechplc.com" target="_blank" rel="noopener">
            Prime Software
        </a> 
        in collaboration with <strong>Beemnet Abraham</strong>.
    </p> -->
</footer>
	</div>
	<!--end wrapper-->

	<!-- Bootstrap JS -->
    <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script> 
	<!--plugins-->
	<script src="{{asset('assets/js/jquery.min.js')}}"></script>
	<script src="{{asset('assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
	<script src="{{asset('assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
	<script src="{{asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
	<script src="{{asset('assets/plugins/Drag-And-Drop/dist/imageuploadify.min.js')}}"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="{{asset('assets/plugins/select2/js/select2-custom.js')}}"></script>
	<script>
		$(document).ready(function () {
			$('#image-uploadify').imageuploadify();
		})
	</script>
	<!-- Vector map JavaScript -->
	<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js')}}"></script>
	<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js')}}"></script>
	<script src="{{asset('assets/plugins/chartjs/js/chart.js')}}"></script>
	<script src="{{asset('assets/plugins/sparkline-charts/jquery.sparkline.min.js')}}"></script>
	<script src="{{asset('assets/js/index.js')}}"></script>
	<!--notification js -->
	<script src="{{asset('assets/plugins/notifications/js/lobibox.min.js')}}"></script>
	<script src="{{asset('assets/plugins/notifications/js/notifications.min.js')}}"></script>
	<script src="{{asset('assets/plugins/notifications/js/notification-custom-script.js')}}"></script>
	<!--app JS-->
	<script src="{{asset('assets/js/app.js')}}"></script>
	<script>
		  $(function () {
    
		    $("input[data-bootstrap-switch]").each(function(){
		      $(this).bootstrapSwitch('state', $(this).prop('checked'));
		    })

		  })
		new PerfectScrollbar('.dashboard-top-countries');
	</script>
	@livewireScripts

@include('partials.etera-scripts')
@include('partials.notification-polling')
@stack('scripts')

</body>

</html>
