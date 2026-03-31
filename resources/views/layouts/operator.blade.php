<!doctype html>
<html lang="en" class="light">

<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="icon" href="{{asset('favicon.ico')}}" type="image/x-icon"/>
	<link rel="icon" href="{{asset('assets/images/transparent.svg')}}" type="image/jpeg"/>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
	<link rel="stylesheet" href="{{asset('assets/plugins/notifications/css/lobibox.min.css')}}" />
	<link href="{{asset('assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet"/>
	<link href="{{asset('assets/css/pace.min.css')}}" rel="stylesheet"/>
	<script src="{{asset('assets/js/pace.min.js')}}"></script>
	<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/bootstrap-extended.css')}}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="{{asset('assets/css/app.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/icons.css')}}" rel="stylesheet">
	<link rel="stylesheet" href="{{asset('assets/css/dark-theme.css')}}"/>
	<link rel="stylesheet" href="{{asset('assets/css/semi-dark.css')}}"/>
	<link rel="stylesheet" href="{{asset('assets/css/header-colors.css')}}"/>
	@livewireStyles

	<title>etera | Operator</title>
@include('partials.green-theme')
</head>

<body>
	<div class="wrapper">
		<!-- Sidebar -->
		<div class="sidebar-wrapper" data-simplebar="true">
			<div class="sidebar-header">
				<div>
					<img src="{{asset('assets/images/transparent.svg')}}" class="logo-text" style="max-width: 7.5rem;" alt="etera">
				</div>
				<div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i></div>
			</div>

			<!-- Navigation -->
			<ul class="metismenu" id="menu">
				<li>
					<a href="{{ route('operator.dashboard') }}">
						<div class="parent-icon"><i class='bx bx-home-alt'></i></div>
						<div class="menu-title">Dashboard</div>
					</a>
				</li>

				<li>
					<a href="{{ route('operator.proformas.index') }}">
						<div class="parent-icon"><i class='bx bx-file'></i></div>
						<div class="menu-title">My Proformas</div>
					</a>
				</li>

				<li>
					<a href="{{ route('operator.commissions') }}">
						<div class="parent-icon"><i class='bx bx-money'></i></div>
						<div class="menu-title">Commissions</div>
					</a>
				</li>

				<li>
					<a href="{{ route('operator.balance') }}">
						<div class="parent-icon"><i class='bx bx-wallet'></i></div>
						<div class="menu-title">My Balance</div>
					</a>
				</li>
			</ul>
		</div>
		<!-- End Sidebar -->

		<!-- Header -->
		<header>
			<div class="topbar d-flex align-items-center">
				<nav class="navbar navbar-expand gap-3">
					<div class="mobile-toggle-menu"><i class='bx bx-menu'></i></div>

					<div class="top-menu ms-auto">
						<ul class="navbar-nav align-items-center gap-1">
							<li class="nav-item dark-mode d-sm-flex">
								<a class="nav-link dark-mode-icon" href="javascript:;"><i class='bx bx-moon'></i></a>
							</li>

							<li class="nav-item dropdown dropdown-large">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" data-bs-toggle="dropdown">
									<span class="alert-count">{{auth()->user()->unreadNotifications->count()}}</span>
									<i class='bx bx-bell'></i>
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<a href="javascript:;">
										<div class="msg-header">
											<p class="msg-header-title">Notifications</p>
											<p class="msg-header-badge">{{auth()->user()->unreadNotifications->count()}} New</p>
										</div>
									</a>
									<div class="header-notifications-list">
										@foreach(auth()->user()->unreadNotifications->take(5) as $notification)
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="flex-grow-1">
													<p class="msg-info">{{ $notification->data['message'] ?? 'New notification' }}</p>
												</div>
											</div>
										</a>
										@endforeach
									</div>
								</div>
							</li>
						</ul>
					</div>

					<div class="user-box dropdown px-3">
						<a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<img src="{{asset('assets/images/avatars/avatar-9.jpg')}}" class="user-img" alt="user avatar">
							<div class="user-info">
								<p class="user-name mb-0">{{auth()->user()->name}}</p>
								<p class="designattion mb-0">Operator</p>
							</div>
						</a>
						<ul class="dropdown-menu dropdown-menu-end">
							<li><a class="dropdown-item d-flex align-items-center" href="/profile"><i class="bx bx-user fs-5"></i><span>Profile</span></a></li>
							<li><div class="dropdown-divider mb-0"></div></li>
							<li>
								<form action="{{route('logout')}}" method="POST">
									@csrf
									@method('DELETE')
									<button class="dropdown-item d-flex align-items-center"><i class="bx bx-log-out-circle"></i><span>Logout</span></button>
								</form>
							</li>
						</ul>
					</div>
				</nav>
			</div>
		</header>
		<!-- End Header -->

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

		<div class="overlay toggle-icon"></div>
		<a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>

		<footer class="page-footer text-center">
			<p class="mb-1">© <script>document.write(new Date().getFullYear())</script>. All rights reserved.</p>
			<p class="mb-0">Made by <a href="https://www.primetechplc.com" target="_blank" rel="noopener">Prime Software</a> in collaboration with <strong>Beemnet Abraham</strong>.</p>
		</footer>
	</div>

	<!-- Scripts -->
	<script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.min.js')}}"></script>
	<script src="{{asset('assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
	<script src="{{asset('assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
	<script src="{{asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
	<script src="{{asset('assets/js/app.js')}}"></script>
	<script src="{{asset('assets/plugins/notifications/js/lobibox.min.js')}}"></script>
	<script src="{{asset('assets/plugins/notifications/js/notifications.min.js')}}"></script>

	@if(session('error'))
	<script>
		Lobibox.notify('error', {
			pauseDelayOnHover: true,
			continueDelayOnInactiveTab: false,
			position: 'top right',
			msg: '{{ session("error") }}'
		});
	</script>
	@endif

	@livewireScripts
	@stack('scripts')

@include('partials.etera-scripts')
@include('partials.notification-polling')
@stack('scripts')
</body>
</html>
