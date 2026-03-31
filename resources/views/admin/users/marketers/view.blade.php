{{-- @extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">Marketers List</h3>
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
												<input type="text" class="form-control ps-5 radius-30 " placeholder="Search Marketer..."> <span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
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
												<a href="/admin/add-marketer" type="button" class="btn btn-primary radius-30 "><i class="bx bx-plus me-0"></i> Marketer
										</a>
											</div>
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
			
								<th>Name</th>
								<th>Email</th>
								<th>Register Date</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($marketers as $marketer)
							<tr>
								<td><input class="form-check-input" type="checkbox" value="" id="flexCheckDefault"></td>
								
								<td>
									<div class="d-flex align-items-center">
										<div>
											<h6 class="mb-0 font-14">{{$marketer->name}}</h6>
										</div>
									</div>
								</td>
								<td>{{$marketer->email}}</td>
								<td>{{$marketer->created_at}}</td>
								<td><button type="button" class="btn radius-10 p-1"><i class="bx bx-edit me-0"></i>
										</button>
										<button type="button" class="btn radius-10 p-1 text-danger" data-bs-toggle="modal" data-bs-target="#singleDelete"><i class="bx bx-trash me-0"></i>
										</button>
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
@endsection --}}
@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <h3 class="">Marketers List</h3>
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
                                                <input type="text" class="form-control ps-5 radius-30 " placeholder="Search Marketer..."> <span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
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
                                                <a href="/admin/add-marketer" type="button" class="btn btn-primary radius-30 "><i class="bx bx-plus me-0"></i> Marketer</a>
                                            </div>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive lead-table">
                            <table class="table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                        </th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Register Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($marketers as $marketer)
                                    <tr>

                                        <tr >

                                        <td><input class="form-check-input" type="checkbox" value="" id="flexCheckDefault"></td>

                                        <td>
                                            <div class="d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#marketerDetailModal{{$marketer->id}}">
                                                <div>
                                                    <h6 class="mb-0 font-14">{{$marketer->name}}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{$marketer->email}}</td>
                                        <td>{{$marketer->created_at}}</td>
										<td>
                                            <!-- Edit Button -->
                                            <a href="{{ route('admin.users.marketers.edit', $marketer->id) }}" class="btn radius-10">
                                                <i class="bx bx-edit me-0"></i>
                                            </a>


                                            
                                            <!-- Delete Button (Triggers Modal) -->
                                            <button type="button" class="btn radius-10 text-danger" data-bs-toggle="modal" data-bs-target="#singleDelete{{$marketer->id}}">
                                                <i class="bx bx-trash me-0"></i>
                                            </button>

                                            <!-- Modal for Confirmation -->
                                            <div class="modal fade" id="singleDelete{{$marketer->id}}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Delete</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">Are you sure you want to delete this marketer?</div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
                                                            <form action="{{ route('admin.users.marketers.destroy', $marketer->id) }}" method="POST" style="display:inline;">
                                                                @csrf
                                                                @method('POST')
                                                                <button type="submit" class="btn btn-danger radius-30">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Modal for Confirmation -->

                                        </td>



								<!-- Modal for Full Row Click -->
                                <div class="modal fade" id="marketerDetailModal{{$marketer->id}}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content shadow-lg border-0 rounded-4" style="background: #f8f9fa;">
                                        
                                             
                                                <div class="modal-header" style="background: #7A2CB4; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                                    <h5 class="modal-title fw-bold"  style="color: white;">Marketer Details</h5>
                                                 
                                                    
                                             
                                                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-5">
                                                <div class="mb-4">
                                                    <p class="font-weight-semibold"><strong>Name:</strong> {{$marketer->name}}</p>
                                                </div>
                                                <div class="mb-4">
                                                    <p class="font-weight-semibold"><strong>Email:</strong> {{$marketer->email}}</p>
                                                </div>
                                                <div class="mb-4">
                                                    <p class="font-weight-semibold"><strong>Phone:</strong> {{$marketer->phone_number}}</p>
                                                </div>
                                                <div class="mb-4">
                                                    <p class="font-weight-semibold"><strong>TIN:</strong> {{$marketer->tin_number}}</p>
                                                </div>
                                                <div class="mb-4">
                                                    <p class="font-weight-semibold"><strong>Created:</strong> {{$marketer->created_at}}</p>
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
            </div>
        </div>
        <!--end row-->
    </div>
</div>
</div>
</div>
<!--end page wrapper -->

<!-- Delete Modal for each Marketer -->
{{-- @foreach($marketers as $marketer)
<div class="modal fade" id="deleteModal{{$marketer->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Are you sure you want to delete the marketer <strong>{{$marketer->name}}</strong>?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
                <form action="/admin/delete-marketer/{{$marketer->id}}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger radius-30">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div> --}}
{{-- @endforeach --}}
@endsection
