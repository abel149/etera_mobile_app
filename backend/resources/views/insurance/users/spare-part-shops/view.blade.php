@extends('layouts.marketer')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">Spare Parts Shops List</h3>
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="row align-items-right">
							<div class="col-lg-9 col-xl-10">
								<form class="">
									<div class="row row-cols-lg-2 row-cols-xl-auto g-2">
										<div class="col">
											<div class="position-relative">
												<input type="text" class="form-control ps-5 radius-30 " placeholder="Search Product..."> <span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
											</div>
										</div>
										<div class="col">
											<div class="btn-group" role="group" aria-label="Button group with nested dropdown">
												<button type="button" class="btn btn-white radius-30">
												<i class="bx bx-filter"></i> Filter</button>
												<div class="btn-group" role="group">
												  <button id="btnGroupDrop1" type="button" class="btn btn-white radius-30 dropdown-toggle dropdown-toggle-nocaret px-1" data-bs-toggle="dropdown" aria-expanded="false">
													<i class='bx bx-chevron-down'></i>
												  </button>
												  <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
													<li><a class="dropdown-item" href="#">Name</a></li>
													<li><a class="dropdown-item" href="#">Tin #</a></li>
													<li><a class="dropdown-item" href="#">Date Modified</a></li>
												  </ul>
												</div>
											  </div>
										</div>
										<div class="col">
											<div class="position-relative">
												<a href="/marketer/add-spare-part-shop" type="button" class="btn btn-primary radius-30 "><i class="bx bx-plus me-0"></i> Add User
										</a>
											</div>
										</div>
										{{-- <div class="col">
										<button type="button" class="btn btn-danger radius-30" data-bs-toggle="modal" data-bs-target="#selectedDelete"><i class="bx bx-trash me-0"></i> Delete</button>
									</div> --}}
									</div>
								</form>
							</div>
						</div>

				<div class="table-responsive lead-table">
					<table class="table mb-0 align-middle">
						<thead class="table-light">
							<tr>
								<th >
									<input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
						  		</th>
								<th>Name</th>
								<th>Phone</th>
								<th>Tin #</th>
								<th>Register Date</th>
								<th>License Expiry</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($shops as $shop)
							@php
							    
							    // Example date for 'license_expire_date'
							    $licenseExpireDate = \Carbon\Carbon::create($shop->license_expire_date);  // Change this to dynamically fetch the date from the DB
							    $currentDate = \Carbon\Carbon::now();
							    
							     // Check if the date is expired or expiring soon
    $isExpired = $licenseExpireDate->lessThan($currentDate);  // Expired if less than current date
    $isExpiringSoon = !$isExpired && $licenseExpireDate->lessThanOrEqualTo($currentDate->copy()->addMonth());  // Less than 1 month away, but not expired
    
							    $formattedDate = $licenseExpireDate->format('D M d,Y'); 
							@endphp
							<tr>
								<td><input class="form-check-input" type="checkbox" value="" id="flexCheckDefault"></td>
								<td>
									<div class="d-flex align-items-center">
										<div>
											<h6 class="mb-0 font-14">{{$shop->name}}</h6>
											<p class="mb-0 font-13 text-secondary">{{$shop->email}}</p>
										</div>
									</div>
								</td>
								<td>{{$shop->phone_number}}</td>
								<td>{{$shop->tin_number}}</td>
								<td>{{$shop->created_at}}</td>

								<td>
									 @if($isExpired)
        <!-- Red badge for expired -->
        <div class="badge rounded-pill bg-danger w-100">{{ $formattedDate }}</div>
    @elseif($isExpiringSoon)
        <!-- Yellow badge for expiring soon -->
        <div class="badge rounded-pill bg-warning w-100">{{ $formattedDate }}</div>
    @else
        <!-- Default color for valid date -->
        <div class="badge rounded-pill bg-success w-100">{{ $formattedDate }}</div>
    @endif
								</td>
								<td>
										{{-- <button type="button" class="btn radius-10 p-1 text-danger" data-bs-toggle="modal" data-bs-target="#singleDelete"><i class="bx bx-trash me-0"></i>
										</button> --}}

										<a href="{{ route('edit-shop.marketer', $shop->id) }}" class="btn radius-10 p-1">
											<i class="bx bx-edit me-0"></i>
										</a>
										
								</td>

							</tr>
							@endforeach
													</tbody>
					</table>
				</div>
			</div>
		</div>
		<!--end row-->


	</div>
</div>
</div>
</div>
<!--end page wrapper -->

<!-- Selected Delete Modal -->
<div class="modal fade" id="selectedDelete" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Delete Spare Part Shop Users</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">Are you sure you want to delete the selected Spare Part Shop users?</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger radius-30">Delete</button>
			</div>
		</div>
	</div>
</div>
<!-- End Selected Delete Modal -->

<!-- Single Delete Modal -->
<div class="modal fade" id="singleDelete" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Delete</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">Are you sure you want to delete this Spare Part Shop user?</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger radius-30">Delete</button>
			</div>
		</div>
	</div>
</div>
<!-- End Single Delete Modal -->
@endsection
