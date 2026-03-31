<!doctype html>

<html lang="en" class="light">



<head>

	<!-- Required meta tags -->

	<meta charset="utf-8">

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


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

	<link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2.min.css')}}" />

	<link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2-bootstrap-5-theme.min.css')}}" />

	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">

	<link href="{{asset('assets/css/app.css')}}" rel="stylesheet">

	<link href="{{asset('assets/css/icons.css')}}" rel="stylesheet">

	<!-- Theme Style CSS -->

	<link rel="stylesheet" href="{{asset('assets/css/dark-theme.css')}}"/>

	<link rel="stylesheet" href="{{asset('assets/css/semi-dark.css')}}"/>

	<link rel="stylesheet" href="{{asset('assets/css/header-colors.css')}}"/>
<!-- FilePond CSS -->
	<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
  	<link href="https://unpkg.com/filepond-plugin-file-preview/dist/filepond-plugin-file-preview.min.css" rel="stylesheet">
	<link href="https://unpkg.com/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.css" rel="stylesheet"/>
	@livewireStyles



	<style type="text/css">

	.tbl td:last-child {

      white-space: nowrap;

      width: 1%; /* Makes the column take as little space as possible based on content */

    }

</style>

	<title> etera | Accountant</title>

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

					<a href="/finance">

						<div class="parent-icon"><i class='bx bx-home-alt'></i>

						</div>

						<div class="menu-title">Dashboard</div>

					</a>

				</li>

				<!--<li>-->

				<!--	<a href="/finance/settings">-->

				<!--		<div class="parent-icon"><i class="bx bx-cog"></i>-->

				<!--		</div>-->

				<!--		<div class="menu-title">Settings</div>-->

				<!--	</a>-->

				<!--</li>-->



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

					  <div class="top-menu ms-auto">

						<ul class="navbar-nav align-items-center gap-1">

							

							<li class="nav-item dark-mode  d-sm-flex">

								<a class="nav-link dark-mode-icon" href="javascript:;"><i class='bx bx-moon'></i>

								</a>

							</li>


							<li class="nav-item dropdown dropdown-large">

								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" data-bs-toggle="dropdown"><span class="alert-count">{{auth()->user()->unreadNotifications->count()}}</span>

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

                  @foreach(auth()->user()->unreadNotifications as $notification)
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

                    @endforeach
																		</div>

									<a href="javascript:;">

										<div class="text-center msg-footer">

											<button @if(auth()->user()->unreadNotifications->count() == 0) disabled @endif
                      class="btn btn-primary w-100">Mark All As Read</button>

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

								<p class="designattion mb-0">Accountant</p>

							</div>

						</a>

						<ul class="dropdown-menu dropdown-menu-end">

							<li><a class="dropdown-item d-flex align-items-center" href="/admin/profile"><i class="bx bx-user fs-5"></i><span>Profile</span></a>

							</li>

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

    <p class="mb-0">
        <!--Made with <span class="fa fa-heart text-danger"></span> by -->
        <!--<a href="https://www.primetechplc.com" target="_blank" rel="noopener">-->
        <!--    Prime Software-->
        <!--</a> -->
        <!--in collaboration with <strong>Beemnet Abraham</strong>.-->
    </p>
</footer>


	</div>

	<!--end wrapper-->





	<!-- search modal -->

    <div class="modal" id="SearchModal" tabindex="-1">

		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">

		  <div class="modal-content">

			<div class="modal-header gap-2">

			  <div class="position-relative popup-search w-100">

				<input class="form-control form-control-lg ps-5 border border-3 border-primary" type="search" placeholder="Search">

				<span class="position-absolute top-50 search-show ms-3 translate-middle-y start-0 top-50 fs-4"><i class='bx bx-search'></i></span>

			  </div>

			  <button type="button" class="btn-close d-md-none" data-bs-dismiss="modal" aria-label="Close"></button>

			</div>

			<div class="modal-body">

				<div class="search-list">

				   <p class="mb-1">Html Templates</p>

				   <div class="list-group">

					  <a href="javascript:;" class="list-group-item list-group-item-action active align-items-center d-flex gap-2 py-1"><i class='bx bxl-angular fs-4'></i>Best Html Templates</a>

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-vuejs fs-4'></i>Html5 Templates</a>

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-magento fs-4'></i>Responsive Html5 Templates</a>

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-shopify fs-4'></i>eCommerce Html Templates</a>

				   </div>

				   <p class="mb-1 mt-3">Web Designe Company</p>

				   <div class="list-group">

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-windows fs-4'></i>Best Html Templates</a>

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-dropbox fs-4' ></i>Html5 Templates</a>

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-opera fs-4'></i>Responsive Html5 Templates</a>

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-wordpress fs-4'></i>eCommerce Html Templates</a>

				   </div>

				   <p class="mb-1 mt-3">Software Development</p>

				   <div class="list-group">

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-mailchimp fs-4'></i>Best Html Templates</a>

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-zoom fs-4'></i>Html5 Templates</a>

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-sass fs-4'></i>Responsive Html5 Templates</a>

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-vk fs-4'></i>eCommerce Html Templates</a>

				   </div>

				   <p class="mb-1 mt-3">Online Shoping Portals</p>

				   <div class="list-group">

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-slack fs-4'></i>Best Html Templates</a>

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-skype fs-4'></i>Html5 Templates</a>

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-twitter fs-4'></i>Responsive Html5 Templates</a>

					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-vimeo fs-4'></i>eCommerce Html Templates</a>

				   </div>

				</div>

			</div>

		  </div>

		</div>

	  </div>

    <!-- end search modal -->









	<!--start switcher-->

	<div class="switcher-wrapper">

		<div class="switcher-btn"> <i class='bx bx-cog bx-spin'></i>

		</div>

		<div class="switcher-body">

			<div class="d-flex align-items-center">

				<h5 class="mb-0 text-uppercase">Theme Customizer</h5>

				<button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>

			</div>

			<hr/>

			<h6 class="mb-0">Theme Styles</h6>

			<hr/>

			<div class="d-flex align-items-center justify-content-between">

				<div class="form-check">

					<input class="form-check-input" type="radio" name="flexRadioDefault" id="lightmode">

					<label class="form-check-label" for="lightmode">Light</label>

				</div>

				<div class="form-check">

					<input class="form-check-input" type="radio" name="flexRadioDefault" id="darkmode">

					<label class="form-check-label" for="darkmode">Dark</label>

				</div>

				<div class="form-check">

					<input class="form-check-input" type="radio" name="flexRadioDefault" id="semidark" checked>

					<label class="form-check-label" for="semidark">Semi Dark</label>

				</div>

			</div>

			<hr/>

			<div class="form-check">

				<input class="form-check-input" type="radio" id="minimaltheme" name="flexRadioDefault">

				<label class="form-check-label" for="minimaltheme">Minimal Theme</label>

			</div>

			<hr/>

			<h6 class="mb-0">Header Colors</h6>

			<hr/>

			<div class="header-colors-indigators">

				<div class="row row-cols-auto g-3">

					<div class="col">

						<div class="indigator headercolor1" id="headercolor1"></div>

					</div>

					<div class="col">

						<div class="indigator headercolor2" id="headercolor2"></div>

					</div>

					<div class="col">

						<div class="indigator headercolor3" id="headercolor3"></div>

					</div>

					<div class="col">

						<div class="indigator headercolor4" id="headercolor4"></div>

					</div>

					<div class="col">

						<div class="indigator headercolor5" id="headercolor5"></div>

					</div>

					<div class="col">

						<div class="indigator headercolor6" id="headercolor6"></div>

					</div>

					<div class="col">

						<div class="indigator headercolor7" id="headercolor7"></div>

					</div>

					<div class="col">

						<div class="indigator headercolor8" id="headercolor8"></div>

					</div>

				</div>

			</div>

			<hr/>

			<h6 class="mb-0">Sidebar Colors</h6>

			<hr/>

			<div class="header-colors-indigators">

				<div class="row row-cols-auto g-3">

					<div class="col">

						<div class="indigator sidebarcolor1" id="sidebarcolor1"></div>

					</div>

					<div class="col">

						<div class="indigator sidebarcolor2" id="sidebarcolor2"></div>

					</div>

					<div class="col">

						<div class="indigator sidebarcolor3" id="sidebarcolor3"></div>

					</div>

					<div class="col">

						<div class="indigator sidebarcolor4" id="sidebarcolor4"></div>

					</div>

					<div class="col">

						<div class="indigator sidebarcolor5" id="sidebarcolor5"></div>

					</div>

					<div class="col">

						<div class="indigator sidebarcolor6" id="sidebarcolor6"></div>

					</div>

					<div class="col">

						<div class="indigator sidebarcolor7" id="sidebarcolor7"></div>

					</div>

					<div class="col">

						<div class="indigator sidebarcolor8" id="sidebarcolor8"></div>

					</div>

				</div>

			</div>

		</div>

	</div>

	<!--end switcher-->

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

	 <!-- FilePond Plugin for Image Preview (optional) -->
  <script src="{{asset('assets/plugins/filepond-image-preview/filepond-plugin-image-preview.min.js')}}"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-crop/dist/filepond-plugin-image-crop.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.js"></script>
  <script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js"></script>
  <script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.js"></script>
  <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js"></script>
  <script src="{{asset('assets/plugins/filepond/filepond.js')}}"></script>

  <script>
	document.addEventListener("DOMContentLoaded", function() {
    const csrfToken = document.querySelector('input[type="hidden"').value;

    const licenseBusiness = FilePond
    const stampBusiness = FilePond
    const licenseGarage = FilePond
    const stampGarage = FilePond
    const licenseShop = FilePond
    const stampShop = FilePond
	console.log(csrfToken)
    // Create a FilePond instance
  	licenseBusiness.create(document.querySelector('#licenseBusiness')).setOptions({
      allowMultiple:true,
      credits:false,
      imageResizeMode:'contain',
      imagePreviewMaxFileSize:'3MB',
      acceptedFileTypes: ['image/*'],
        server: {
            process: '/upload/image',
            revert: '/delete',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        }
    });
    stampBusiness.create(document.querySelector('#stampBusiness')).setOptions({
      allowMultiple:true,
      credits:false,
      imageResizeMode:'contain',
      imagePreviewMaxFileSize:'3MB',
      acceptedFileTypes: ['image/*'],
        server: {
            process: '/upload/image',
            revert: '/delete',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        }
    });
    licenseGarage.create(document.querySelector('#licenseGarage')).setOptions({
      allowMultiple:true,
      credits:false,
      imageResizeMode:'contain',
      imagePreviewMaxFileSize:'3MB',
      acceptedFileTypes: ['image/*'],
        server: {
            process: '/upload/image',
            revert: '/delete',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        }
    });
    stampGarage.create(document.querySelector('#stampGarage')).setOptions({
      allowMultiple:true,
      credits:false,
      imageResizeMode:'contain',
      imagePreviewMaxFileSize:'3MB',
      acceptedFileTypes: ['image/*'],
        server: {
            process: '/upload/image',
            revert: '/delete',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        }
    });
    licenseShop.create(document.querySelector('#licenseShop')).setOptions({
      allowMultiple:true,
      credits:false,
      imageResizeMode:'contain',
      imagePreviewMaxFileSize:'3MB',
      acceptedFileTypes: ['image/*'],
        server: {
            process: '/upload/image',
            revert: '/delete',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        }
    });
    stampShop.create(document.querySelector('#stampShop')).setOptions({
      allowMultiple:true,
      credits:false,
      imageResizeMode:'contain',
      imagePreviewMaxFileSize:'3MB',
      acceptedFileTypes: ['image/*'],
        server: {
            process: '/upload/image',
            revert: '/delete',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        }
    });

	audio.create(document.querySelector('#audio')).setOptions({
      allowMultiple:true,
      credits:false,
      imageResizeMode:'contain',
      imagePreviewMaxFileSize:'3MB',
      //acceptedFileTypes: ['image/*'],
        server: {
            process: '/upload/audio',
            revert: '/delete',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        }
    });

	video.create(document.querySelector('#video')).setOptions({
      allowMultiple:true,
      credits:false,
      imageResizeMode:'contain',
      imagePreviewMaxFileSize:'3MB',
      //acceptedFileTypes: ['image/*'],
        server: {
            process: '/upload/video',
            revert: '/delete',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        }
    });
  })

  </script>
	<script>

		  $(function () {

    

		    $("input[data-bootstrap-switch]").each(function(){

		      $(this).bootstrapSwitch('state', $(this).prop('checked'));

		    })



		  })

		new PerfectScrollbar('.dashboard-top-countries');

	</script>

	@livewireScripts

	<script>
		// Function to update remaining time countdown for Etera-Chereta proformas
		function updateRemainingTime() {
			const timeElements = document.querySelectorAll('[data-remaining-time]');
			
			timeElements.forEach(element => {
				const expiresAt = element.getAttribute('data-remaining-time');
				if (expiresAt) {
					const now = new Date();
					const expiry = new Date(expiresAt);
					
					if (expiry > now) {
						const diff = Math.floor((expiry - now) / 1000); // difference in seconds
						const hours = Math.floor(diff / 3600);
						const minutes = Math.floor((diff % 3600) / 60);
						
						element.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
						
						// Add warning class when less than 1 hour remaining
						if (diff < 3600) {
							element.classList.remove('bg-primary');
							element.classList.add('bg-warning');
						}
						
						// Add danger class when less than 15 minutes remaining
						if (diff < 900) {
							element.classList.remove('bg-warning');
							element.classList.add('bg-danger');
						}
					} else {
						element.textContent = 'Expired';
						element.classList.remove('bg-primary', 'bg-warning');
						element.classList.add('bg-secondary');
					}
				}
			});
		}

		// Update time every minute
		setInterval(updateRemainingTime, 60000);
		
		// Initial update
		document.addEventListener('DOMContentLoaded', function() {
			updateRemainingTime();
			
			// Initialize tooltips
			var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
			var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
				return new bootstrap.Tooltip(tooltipTriggerEl);
			});
		});
	</script>

@include('partials.etera-scripts')
@include('partials.notification-polling')
@stack('scripts')

</body>



</html>
