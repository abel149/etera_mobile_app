@extends('layouts.marketer')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">Insurances List</h3>
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
													<li><a class="dropdown-item" href="#">Date Modified</a></li>
												  </ul>
												</div>
											  </div>
										</div>
										<div class="col">
											<div class="position-relative">
												<a href="/marketer/add-insurance" type="button" class="btn btn-primary radius-30 "><i class="bx bx-plus me-0"></i> Add User
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
								<th>Logo</th>
								<th>Name</th>
								<th>Email</th>
								<th>Register Date</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($insurances as $insurance)
							<tr>
								<td><input class="form-check-input" type="checkbox" value="" id="flexCheckDefault"></td>
								<td>
									<div class="">
										<img src="{{asset('assets/images/avatars/avatar-9.jpg')}}" class="" width="40" height="40" alt="">
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center">
										<div>
											<h6 class="mb-0 font-14">{{$insurance->name}}</h6>
										</div>
									</div>
								</td>
								<td>{{$insurance->email}}</td>
								<td>{{$insurance->created_at}}</td>
								<td>
									
									{{-- <button type="button" class="btn radius-10 p-1"><i class="bx bx-edit me-0"></i>
										</button> --}}

										<a href="{{ route('edit-insurance.marketer', $insurance->id) }}" class="btn radius-10 ">
											<i class="bx bx-edit me-0"></i>
										</a>
										
										{{-- <button type="button" class="btn radius-10 p-1 text-danger" data-bs-toggle="modal" data-bs-target="#singleDelete"><i class="bx bx-trash me-0"></i>
										</button> --}}
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

<!-- Single Delete Modal -->
<div class="modal fade" id="singleDelete" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Delete</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">Are you sure you want to delete this insurance user?</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger radius-30">Delete</button>
			</div>
		</div>
	</div>
</div>
<!-- End Single Delete Modal -->
@endsection
