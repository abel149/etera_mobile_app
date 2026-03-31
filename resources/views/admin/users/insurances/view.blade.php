
@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">Insurance List</h3>
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
												<input type="text" class="form-control ps-5 radius-30 " placeholder="Search Insurance..."> <span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
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
													<li><a class="dropdown-item" href="#">Date Modified</a></li>
												  </ul>
												</div>
											  </div>
										</div>
										<div class="col">
											<div class="position-relative">
												<a href="/admin/add-insurance" type="button" class="btn btn-primary radius-30 "><i class="bx bx-plus me-0"></i> Insurance
										</a>
											</div>
										</div>
										<div class="col">
										<button type="button" class="btn btn-danger radius-30" data-bs-toggle="modal" data-bs-target="#selectedDelete"><i class="bx bx-trash me-0"></i> Delete</button>
									</div>
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
								<th>Logo</th>
								<th>Name</th>
								<th>Email</th>
								<th>Registered By</th>
								<th>Register Date</th>
								<th>Actions</th>

							</tr>
						</thead>
						<tbody>
							@foreach($insurances as $insurance)
							{{-- <tr> --}}
								<tr >

								<td><input class="form-check-input" type="checkbox" value="" id="flexCheckDefault"></td>
								<td>
									<div class="">
										<img src="{{asset('assets/images/avatars/avatar-9.jpg')}}" class="" width="40" height="40" alt="">
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#insuranceDetailModal{{$insurance->id}}">
										<div>
											<h6 class="mb-0 font-14">{{$insurance->name}}</h6>
										</div>
									</div>
								</td>
								<td>{{$insurance->email}}</td>
								<td>No one</td>
								<td>{{$insurance->created_at}}</td>
					
									<td>
									<a href="{{ route('edit-insurance', $insurance->id) }}" class="btn radius-10 ">
										<i class="bx bx-edit me-0"></i>
									</a>
									

									<!-- Delete Button -->
									<button type="button" class="btn radius-10 text-danger" data-bs-toggle="modal" data-bs-target="#singleDelete{{$insurance->id}}">
										<i class="bx bx-trash me-0"></i>
									</button>

									<!-- Modal for Confirmation -->
									<div class="modal fade" id="singleDelete{{$insurance->id}}" tabindex="-1" aria-hidden="true">
										<div class="modal-dialog modal-dialog-centered">
											<div class="modal-content">
												<div class="modal-header">
													<h5 class="modal-title">Delete</h5>
													<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<div class="modal-body">Are you sure you want to delete this insurance user?</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
													<form action="{{ route('delete-insurance', $insurance->id) }}" method="POST" style="display:inline;">
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
<div class="modal fade" id="insuranceDetailModal{{$insurance->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4" style="background: #f8f9fa;">
           
				<div class="modal-header" style="background: #7A2CB4; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px;">
					<h5 class="modal-title fw-bold"  style="color: white;">Insurance Details</h5>
					
				<button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-5">
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>Name:</strong> {{$insurance->name}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>Email:</strong> {{$insurance->email}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>Phone:</strong> {{$insurance->phone_number}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>TIN:</strong> {{$insurance->tin_number}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>Created:</strong> {{$insurance->created_at}}</p>
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
				<h5 class="modal-title">Delete Insurance Users</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">Are you sure you want to delete the selected insurance users?</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger radius-30">Delete</button>
			</div>
		</div>
	</div>
</div>
<!-- End Selected Delete Modal -->

@endsection
