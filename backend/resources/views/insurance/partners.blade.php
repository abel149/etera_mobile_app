@extends('layouts.insurance')
@section('content')
<h3 class="">Partners List</h3>
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
												<input type="text" class="form-control ps-5 radius-30 " placeholder="Search Partner..."> <span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
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
											<button type="button" class="btn btn-primary radius-30" data-bs-toggle="modal" data-bs-target="#add"> Add Partner</button>
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
								<th>Name</th>
								<th>Store Number</th>
								<th>Role</th>
								<th>Tin #</th>
								<th>Phone Number</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
            @foreach (auth()->user()->partners ?? [] as $partner)
							<tr>
								<td>
									<div class="d-flex align-items-center">
										<div>
											<h6 class="mb-0 font-14">{{$partner->partner->name}}</h6>
											<p class="mb-0 font-13 text-secondary">{{$partner->partner->email}}</p>
										</div>
									</div>
								</td>
								<td class="font-bold"">{{$partner->partner->store_id}}</td>
								<td>{{ucfirst($partner->partner->role)}}</td>
								<td>{{ucfirst($partner->partner->tin_number)}}</td>
								<td>{{$partner->partner->phone_number}}</td>
								<td>
                <form action="partners/{{$partner->id}}" method="POST">
                    @method('DELETE')
                    @csrf
                    				  
									  <button type="submit" class="btn radius-10 p-1 text-danger"><i class="bx bx-trash me-0">Remove</i></button>
                  </form>
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

@php
$partners = auth()->user()->partners;
$partnerIds = $partners ? $partners->pluck('partner_id') : collect();
$availablePartners = \App\Models\User::whereIn('role', ['shop','garage'])->whereNotIn('id', $partnerIds)->get();
@endphp
<!-- Add Modal -->
<div class="modal fade" id="add" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Add Partners</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
      <form action="{{route('partners.add')}}" method="POST">
        @csrf
        @method('POST')
			<div class="modal-body">
				<label for="multiple-select-garage" class="form-label">Select Your Partners</label>
								<select class="form-select" name="partners[]" id="multiple1" data-placeholder="Choose anything" multiple>
                  @foreach($availablePartners as $partner)
                    <option value="{{$partner->id}}">{{$partner->store_id}} - {{$partner->name}}</option>
                    @endforeach
								</select>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-primary radius-30">Add</button>
			</div>
      </form>
		</div>
	</div>
</div>
<!-- End Add Modal -->

<!-- Modal -->
<div class="modal fade" id="details" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Modal title</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row g-3">
				<div class="col-lg-6">
                    <label for="input1" class="form-label">Name</label>
                    <input name="name" type="text" class="form-control" id="input1" placeholder="Your Company">
                </div>
                <div class="col-lg-6">
                    <label for="input7" class="form-label">Email</label>
                    <input name="email" type="email" class="form-control" id="input7" placeholder="Your Email">
                </div>
                <div class="col-lg-6">
                    <label for="input2" class="form-label">Phone Number</label>
                    <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09...">
                </div>
                <div class="col-lg-6">
                    <label for="input3" class="form-label">Tin #</label>
                    <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Your Company Tin #">
                </div>
                <div class="col-lg-6">
                    <label for="input4" class="form-label">Location / Address</label>
                    <input name="location" type="text" class="form-control" id="input4" placeholder="">
                </div>
                <div class="col-lg-6">
                    <label for="multiple-select-clear-field" class="form-label">Car Brands To Serve</label>
                    <select required name="brands[]" class="form-select" id="multiple-select-clear-field" data-placeholder="Add Brands..." multiple>
                        <option value="">...</option>
                    </select>
                </div>
                <div class="col-lg-6">
                    <label for="input6" class="form-label">Business License Proc. Number</label>
                    <input name="business_license_number" type="text" class="form-control" id="input6" placeholder="Proclamation Number">
                </div>
                <div class="col-lg-6">
                    <label for="input6" class="form-label">Business License Expiry Date</label>
                    <input name="license_expire_date" type="date" class="form-control" id="input6" placeholder="Select Date">
                </div>
                <div class="col-lg-8">
                    <label for="input6" class="form-label">Business License Image</label>
                    <div class="text-center"><img src="{{asset('assets/images/avatars/avatar-1.png')}}"></div>
                </div>
                <div class="col-lg-4">
                    <label for="input6" class="form-label">Stamp Image</label>
                    <div class="text-center"><img src="{{asset('assets/images/avatars/avatar-1.png')}}"></div>

                </div>
            	</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-danger radius-30">Remove</button>
			</div>
		</div>
	</div>
</div>
<!-- Add Modal -->
<div class="modal fade" id="add" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Add Partners</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
      <form action="{{route('partners.add')}}" method="POST">
        @csrf
        @method('POST')
			<div class="modal-body">
				<label for="multiple-select-garage" class="form-label">Select Your Partners</label>
								<select class="form-select" name="partners[]" id="multiple1" data-placeholder="Choose anything" multiple>
                  @foreach($availablePartners as $partner)
                    <option value="{{$partner->id}}">{{$partner->store_id}} - {{$partner->name}}</option>
                    @endforeach
								</select>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-primary radius-30">Add</button>
			</div>
      </form>
		</div>
	</div>
</div>
<!-- End Add Modal -->
@endsection
