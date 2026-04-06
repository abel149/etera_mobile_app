@extends('layouts.business-owner')
@section('content')
<h3 class="">Partners List</h3>
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
									</div>
								</form>
							</div>
						</div>

				<div class="table-responsive lead-table">
					<table class="table mb-0 align-middle">
						<thead class="table-light">
							<tr>
								<th>Name</th>
								<th>Role</th>
								<th>Tin #</th>
								<th>Phone Number</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
            @foreach (auth()->user()->partners as $partner)
							<tr>
								<td>
									<div class="d-flex align-items-center">
										<div>
											<h6 class="mb-0 font-14">{{$partner->partner->name}}</h6>
											<p class="mb-0 font-13 text-secondary">{{$partner->partner->email}}</p>
										</div>
									</div>
								</td>
								<td>{{ucfirst($partner->partner->role)}}</td>
								<td>{{ucfirst($partner->partner->tin_number)}}</td>
								<td>{{$partner->partner->phone_number}}</td>
								<td>
                <form action="{{route('partners.destroy', $partner->id)}}" method="POST">
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

@endsection
