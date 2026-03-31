@extends('layouts.sparepart')
@section('content')

<div class="container margin-top-20 margin-bottom-45">
	<div class="row">
		<div class="col-xl-12 col-lg-12 content-left-offset">

			<div class="notify-box margin-top-15">
				{{-- <div class="switch-container">
					<label class="switch"><input type="checkbox"><span class="switch-button"></span><span class="switch-text">Turn on email alerts for this search</span></label>
				</div> --}}
				<div class="switch-container">
					<h3 class="page-title">Inboxed Proforma List</h3>
				</div>
				<div class="sort-by">
					<span>Sort by:</span>
					<select class="selectpicker hide-tick">
						<option>Relevance</option>
						<option>Newest</option>
						<option>Oldest</option>
						<option>Random</option>
					</select>
				</div>
			</div>

			<div class="listings-container compact-list-layout margin-top-35">

				<!-- Proforma Listing -->
				{{-- @foreach($proformas as $proforma) --}}
				<a href="/received-details" class="job-listing with-apply-button">

					<!-- Job Listing Details -->
					<div class="job-listing-details">

						<!-- Logo -->
						<div class="job-listing-company-logo">
							<img src="{{asset('asset/images/company-logo-01.png')}}" alt="">
						</div>

						<!-- Details -->
						<div class="job-listing-description">
							<h3 class="job-listing-title">Name</h3>

							<!-- Job Listing Footer -->
							<div class="job-listing-footer">
								<ul>
									<li><i class="icon-material-outline-directions-car"></i> Brand, model </li>
									<li><i class="icon-material-outline-access-time"></i>date</li>
								</ul>
							</div>
						</div>

						<!-- Bookmark -->
						{{-- @if(!$proforma->userAlreadyApplied(auth()->user()->id) 
					&&( (auth()->user()->role == 'shop' && $proforma->applicationsFromShops()->count() != 3) 
					|| (auth()->user()->role == 'garage' && $proforma->applicationsFromGarages()->count() != 3))) --}}
						<span class="list-apply-button radius-30 ripple-effect">Details</span>
						{{-- @endif --}}
					</div>
				</a>
				{{-- @endforeach --}}
			</div>


			<!-- Pagination -->
			<div class="clearfix"></div>
			<div class="row">
				<div class="col-md-12">
					<!-- Pagination -->
                {{-- {{$proformas->links()}} --}}
								</div>
			</div>
			<!-- Pagination / End -->

		</div>
	</div>
</div>


@endsection
