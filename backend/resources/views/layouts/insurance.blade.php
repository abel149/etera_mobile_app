<!doctype html>
<html lang="en" class="light">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
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
	<link href="{{asset('assets/plugins/bs-stepper/css/bs-stepper.css')}}" rel="stylesheet" />
	<!-- loader-->
	<link href="{{asset('assets/css/pace.min.css')}}" rel="stylesheet"/>
	<script src="{{asset('assets/js/pace.min.js')}}"></script>
	<!-- Bootstrap CSS -->
	<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/bootstrap-extended.css')}}" rel="stylesheet">
	<link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2.min.css')}}" />

	<link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2-bootstrap-5-theme.min.css')}}" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
	

	{{-- invoices --}}


<!-- Stylesheet
======================= -->
{{-- <link rel="stylesheet" href="{{asset('assets/invoice/vendor/bootstrap/css/bootstrap.min.css')}}"/>
<link rel="stylesheet" href="{{asset('assets/invoice/vendor/font-awesome/css/all.min.css')}}"/>
<link rel="stylesheet" href="{{asset('assets/invoice/css/stylesheet.css')}}"/> --}}


	<style type="text/css">
		/* ============================================
		   INSURANCE — BLACK TEXT OVERRIDES
		   (Admin-matching white/green theme via green-theme partial)
		   ============================================ */

		/* Force black text across all elements */
		body, .wrapper, .page-wrapper, .page-content { color: #1a1a1a !important; }
		h1, h2, h3, h4, h5, h6 { color: #1a1a1a !important; }
		p { color: #333 !important; }
		label, .form-label { color: #1a1a1a !important; }
		.form-control, .form-select { color: #1a1a1a !important; }
		.card, .card-body, .card-header { color: #1a1a1a !important; }
		.table, .table th, .table td { color: #1a1a1a !important; }
		.text-dark { color: #1a1a1a !important; }
		.text-muted { color: #555 !important; }
		.dropdown-item { color: #333 !important; }
		.dropdown-item:hover, .dropdown-item:focus { color: #1a1a1a !important; }
		.modal-content, .modal-body { color: #1a1a1a !important; }
		.accordion-button { color: #1a1a1a !important; }
		.accordion-body { color: #333 !important; }
		.user-name, .user-info p { color: #1a1a1a !important; }
		.menu-title { color: inherit !important; }
		.msg-name { color: #1a1a1a !important; }
		.msg-info { color: #555 !important; }

		/* Custom legacy */
		.bg-amber { background-color: #FFE500; }
		.bg-green { background-color: #29cc52; }
		.bg-dark-amber { background-color: #d6ba06; }
		.bg-dark-green { background-color: #068f28; }
		.bg-dark-primary { background-color: #3B5998; }
		.circle { height: 80px; width: 80px; }
	</style>
	<title>etera - Insurances</title>
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
					<a href="/insurance">
						<div class="parent-icon"><i class='bx bx-home-alt'></i>
						</div>
						<div class="menu-title">Dashboard</div>
					</a>
				</li>
				<li>
					<a href="/insurance/create-file">
						<div class="parent-icon"><i class="bx bx-message-square-edit"></i>
						</div>
						<div class="menu-title">Request Proforma</div>
					</a>
				</li>
				<li>
				<a href="/insurance/received-proformas">
				    <div class="parent-icon"><i class="bx bx-file"></i></div>
				    <div class="menu-title">
				        Received Files
				        @if(auth()->user()->getReceivedProformasCount() > 0)
				        <span class="badge bg-primary ms-2">
				            {{ auth()->user()->getReceivedProformasCount() }}
				            </span>
				            @endif
				     </div>
				</a>

				</li>
				<li>
					<a href="/insurance/partners">
						<div class="parent-icon"><i class="bx bx-user"></i>
						</div>
						<div class="menu-title">Partners</div>
					</a>
				</li>






				<li>

					{{-- <a href="javascript:;" class="has-arrow">

						<div class="parent-icon"><i class='bx bx-group'></i>

						</div>

						{{-- <div class="menu-title">Register  Partners</div> --}}

					{{-- </a>  --}}

					<ul>


						<!--<li> <a href="/insurance/spare-part-shops"><i class='bx bx-radio-circle'></i>Spare part shops</a>-->

						<!--</li>-->

						<!--<li> <a href="/insurance/garages"><i class='bx bx-radio-circle'></i>Garages</a>-->

						</li>


           

					</ul>

				</li>









				<li>
					<a href="/insurance/balance">
						<div class="parent-icon"><i class="bx bx-money"></i>
						</div>
						<div class="menu-title">Balance</div>
					</a>
				</li>




			</ul>
			<!--end navigation-->
		</div>
		<!--end sidebar wrapper -->
		<!--start header -->
		<header>
  <!-- optional spacer used by sidebar toggle logic -->
  <div class="app-container p-2 my-2"></div>

  <div class="topbar d-flex align-items-center">
    <nav class="navbar navbar-expand gap-3 w-100">

      <!-- Mobile hamburger -->
      <button type="button" class="mobile-toggle-menu btn btn-link p-0">
        <i class="bx bx-menu fs-4"></i>
      </button>

      <!-- Right side icons -->
      <div class="ms-auto d-flex align-items-center gap-3">

        <!-- Dark mode toggle -->
        <div class="nav-item dark-mode d-none d-sm-flex">
          <a class="nav-link dark-mode-icon" href="javascript:void(0);">
            <i class="bx bx-moon fs-4"></i>
          </a>
        </div>

        <!-- Messages / notifications placeholder -->
        <div class="nav-item dropdown dropdown-large">
          <div class="header-message-list">
            <!-- messages injected here later -->
          </div>
        </div>

        <!-- User dropdown -->
        <div class="user-box dropdown">
          <a class="d-flex align-items-center nav-link dropdown-toggle gap-2 dropdown-toggle-nocaret"
             href="#"
             role="button"
             data-bs-toggle="dropdown"
             aria-expanded="false">

            <img src="{{ asset('assets/images/avatars/avatar-9.jpg') }}"
                 class="user-img"
                 alt="user avatar">

            <div class="user-info d-none d-sm-block">
              <p class="user-name mb-0">{{ auth()->user()->name }}</p>
              <p class="designattion mb-0">{{ auth()->user()->role }}</p>
            </div>
          </a>

          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item d-flex align-items-center" href="/insurance/profile">
                <i class="bx bx-user fs-5 me-2"></i>
                <span>Profile</span>
              </a>
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="/insurance/balance">
                <i class="bx bx-dollar-circle fs-5 me-2"></i>
                <span>Earnings</span>
              </a>
            </li>

            <li><div class="dropdown-divider mb-0"></div></li>

            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                @method('DELETE')
                <button class="dropdown-item d-flex align-items-center">
                  <i class="bx bx-log-out-circle fs-5 me-2"></i>
                  <span>Logout</span>
                </button>
              </form>
            </li>
          </ul>
        </div>

      </div>
    </nav>
  </div>
</header>

		<!--end header -->
      		  <div class="page-wrapper">
            <div class="page-content">
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
			</div>
		</div>
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
        <!--Made by -->
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

	<!-- Bootstrap JS -->
	<script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
	<!--plugins-->
	<script src="{{asset('assets/js/jquery.min.js')}}"></script>
	<script src="{{asset('assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
	<script src="{{asset('assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
	<script src="{{asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
	<script src="{{asset('assets/plugins/bs-stepper/js/bs-stepper.min.js')}}"></script>
	<script src="{{asset('assets/plugins/bs-stepper/js/main.js')}}"></script>
	<script src="{{asset('assets/plugins/form-repeater/repeater.js')}}"></script>
	<script src="{{asset('assets/plugins/notifications/js/lobibox.min.js')}}"></script>
	<script src="{{asset('assets/plugins/notifications/js/notifications.min.js')}}"></script>
	<script src="{{asset('assets/plugins/notifications/js/notification-custom-script.js')}}"></script>
	<script src="{{asset('assets/plugins/Drag-And-Drop/dist/imageuploadify.min.js')}}"></script>

	<script src="{{asset('assets/js/jquery.min.js')}}"></script>
	<script src="{{asset('assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
	<script src="{{asset('assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
	<script src="{{asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
	<script src="{{asset('assets/plugins/Drag-And-Drop/dist/imageuploadify.min.js')}}"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="{{asset('assets/plugins/select2/js/select2-custom.js')}}"></script>

	 <!-- FilePond JS -->

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
	    const csrfTokenEl = document.querySelector('input[name="_token"]');
	    const csrfToken = csrfTokenEl ? csrfTokenEl.value : document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const image = FilePond
    const video = FilePond
    const audio = FilePond
	console.log(csrfToken)
    // Create a FilePond instance
  	image.create(document.querySelector('#image')).setOptions({
    allowMultiple: true,
    credits: false,
    imageResizeMode: 'contain',
    imagePreviewMaxFileSize: '3MB',
    acceptedFileTypes: ['image/*'],

    server: {
        process: {
            url: '/upload/image',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },

            // IMPORTANT!!!
            onload: (response) => {
                return response; // FilePond stores returned folder name
            }
        },
        revert: '/delete'
    }
});


	audio.create(document.querySelector('#audio')).setOptions({
      allowMultiple:true,
      credits:false,
      imageResizeMode:'contain',
      imagePreviewMaxFileSize:'3MB',
      acceptedFileTypes: ['audio/mp3'],
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
		$(document).ready(function () {
			$('#image-uploadify').imageuploadify();
		})
	</script>
	<script>
        /* Create Repeater */
        $("#repeater").createRepeater({
            showFirstItemToDefault: true,
        });
    </script>
	<!--Password show & hide js -->
	<script>
		$(document).ready(function () {
			$("#show_hide_password a").on('click', function (event) {
				event.preventDefault();
				if ($('#show_hide_password input').attr("type") == "text") {
					$('#show_hide_password input').attr('type', 'password');
					$('#show_hide_password i').addClass("bx-hide");
					$('#show_hide_password i').removeClass("bx-show");
				} else if ($('#show_hide_password input').attr("type") == "password") {
					$('#show_hide_password input').attr('type', 'text');
					$('#show_hide_password i').removeClass("bx-hide");
					$('#show_hide_password i').addClass("bx-show");
				}
			});
		});
	</script>
	<!--app JS-->
	<script src="{{asset('assets/js/app.js')}}"></script>
	<script src="https://unpkg.com/feather-icons"></script>
	<script>
		feather.replace()
	</script>
@include('partials.etera-scripts')
@include('partials.notification-polling')
@stack('scripts')

</body>

</html>
