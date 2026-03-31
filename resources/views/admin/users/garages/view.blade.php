@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">Garages List</h3>
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="row align-items-right">
							<div class="col-lg-9 col-xl-10">
								<form class="">
									<div class="row row-cols-auto g-2">
										<div class="col">
											<div class="position-relative">
												<input type="text" class="form-control ps-5 radius-30 " placeholder="Search Garage..."> <span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
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
												<a href="/admin/add-garage" type="button" class="btn btn-primary radius-30 "><i class="bx bx-plus me-0"></i> Garage
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
								<th>Registered By</th>
								<th>Register Date</th>
								<th>License Expired</th>
								<th>Actions</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($garages as $garage)
							@php
							    
							    // Example date for 'license_expire_date'
							    $licenseExpireDate = \Carbon\Carbon::create($garage->license_expire_date);  // Change this to dynamically fetch the date from the DB
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
										<div  data-bs-toggle="modal" data-bs-target="#garageDetailModal{{$garage->id}}" >
											<h6 class="mb-0 font-14">{{$garage->name}}</h6>
											<p class="mb-0 font-13 text-secondary">{{$garage->email}}</p>
										</div>
									</div>
								</td>
								<td>{{$garage->phone_number}}</td>
								<td>{{$garage->tin_number}}</td>
								<td>No one</td>
								<td>{{$garage->created_at}}</td>
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
				

									<a href="{{ route('edit-garage', $garage->id) }}" class="btn radius-10">
										<i class="bx bx-edit me-0"></i>
									</a>
									
									

									<!-- Delete Button -->
									<button type="button" class="btn radius-10 text-danger" data-bs-toggle="modal" data-bs-target="#singleDelete{{$garage->id}}">
										<i class="bx bx-trash me-0"></i>
									</button>

									<!-- Modal for Confirmation -->
									<div class="modal fade" id="singleDelete{{$garage->id}}" tabindex="-1" aria-hidden="true">
										<div class="modal-dialog modal-dialog-centered">
											<div class="modal-content">
												<div class="modal-header">
													<h5 class="modal-title">Delete</h5>
													<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<div class="modal-body">Are you sure you want to delete this garage user?</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
													<form action="{{ route('delete-garage', $garage->id) }}" method="POST" style="display:inline;">
														@csrf
														<button type="submit" class="btn btn-danger radius-30">Delete</button>
													</form>
												</div>
											</div>
										</div>
									</div>
									<!-- End Modal for Confirmation -->

								</td>





								<!-- Modal for Full Row Click -->
<div class="modal fade" id="garageDetailModal{{$garage->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4" style="background: #f8f9fa;">
           
				
				<div class="modal-header" style="background: #7A2CB4; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px;">
					<h5 class="modal-title fw-bold"  style="color: white;">Garage Details</h5>
					
				
				<button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-5">
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>Name:</strong> {{$garage->name}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>Email:</strong> {{$garage->email}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>Phone:</strong> {{$garage->phone_number}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>TIN:</strong> {{$garage->tin_number}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>Created:</strong> {{$garage->created_at}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>License Expiry:</strong> {{ $formattedDate }}</p>
                </div>
                <div class="row">
                    <!-- Business License Image -->
                    @if($garage->license_image)
                    <div class="col-md-6 mb-3">
                        <p class="font-weight-semibold"><strong>Business License:</strong></p>
                        <img src="{{ asset('storage/' . $garage->license_image) }}" alt="Business License Image" class="img-fluid rounded" style="max-width: 100%; height: auto;">
                    </div>
                    @else
                    <div class="col-md-6 mb-3">
                        <p>No business license image available.</p>
                    </div>
                    @endif

                    <!-- Stamp Image -->
                    @if($garage->stamp_image)
                    <div class="col-md-6 mb-3">
                        <p class="font-weight-semibold"><strong>Stamp:</strong></p>
                        <img src="{{ asset('storage/' . $garage->stamp_image) }}" alt="Stamp Image" class="img-fluid rounded" style="max-width: 100%; height: auto;">
                    </div>
                    @else
                    <div class="col-md-6 mb-3">
                        <p>No stamp image available.</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="modal-footer border-0" style="background: #f1f1f1;">
                <button type="button" class="btn btn-outline-primary radius-30 px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
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
				<h5 class="modal-title">Delete Garage Users</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">Are you sure you want to delete the selected garage users?</div>
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
			<div class="modal-body">Are you sure you want to delete this garage user?</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger radius-30">Delete</button>
			</div>
		</div>
	</div>
</div>
<!-- End Single Delete Modal -->
@endsection
