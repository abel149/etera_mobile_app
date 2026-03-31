@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">Car Part List</h3>
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="row align-items-right">
							<div class="col-lg-9 col-xl-10">
								<form class="">
									<div class="row row-cols-auto g-2">
										<div class="col">
											<div class="btn-group" role="group" aria-label="Button group with nested dropdown">
												<button type="button" class="btn btn-white radius-30">
												<i class="bx bx-filter"></i> Filter</button>
												<div class="btn-group" role="group">
												  <button id="btnGroupDrop1" type="button" class="btn btn-white radius-30 dropdown-toggle dropdown-toggle-nocaret px-1" data-bs-toggle="dropdown" aria-expanded="false">
													<i class='bx bx-chevron-down'></i>
												  </button>
												  {{-- <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
													<li><a class="dropdown-item" href="#">Body Parts (Inner)</a></li>
													<li><a class="dropdown-item" href="#">Body Parts (Outer)</a></li>
													<li><a class="dropdown-item" href="#">Mechanical Parts</a></li>


												  </ul> --}}

												  <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
													<li><a class="dropdown-item" href="{{ route('parts.index', ['component' => 'Body Parts (Inner)']) }}">Body Parts (Inner)</a></li>
													<li><a class="dropdown-item" href="{{ route('parts.index', ['component' => 'Body Parts (Outer)']) }}">Body Parts (Outer)</a></li>
													<li><a class="dropdown-item" href="{{ route('parts.index', ['component' => 'Mechanical Parts']) }}">Mechanical Parts</a></li>
													<li><hr class="dropdown-divider"></li>
													<li><a class="dropdown-item" href="{{ route('parts.index') }}">Show All</a></li>
												</ul>
												
												</div>
											  </div>

											  
										</div>
										<div class="col">
											<div class="position-relative">
												<a href="/admin/add-parts" type="button" class="btn btn-primary radius-30 "><i class="bx bx-plus me-0"></i> Add Parts
										</a>
											</div>

											
										</div>

									</div>
								</form>
							</div>
						</div>

				<div class="table-responsive lead-table">
					{{-- <table class="table mb-0 align-middle">
						<thead class="table-light">
							<tr>
								<th>Car Part Name</th>
								<th class="w-25 text-end"></th>
							</tr>
						</thead>
						<tbody>
							@foreach($carParts as $part)
							<tr>
								<td>{{$part->name}}</td>
								<td class="text-end">
									
									
										 <!-- Delete Button -->
										 <form action="{{ route('parts.destroy', $part->id) }}" method="POST" style="display:inline;">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn radius-10 p-1 text-danger" onclick="return confirm('Are you sure you want to delete this part?')">
												<i class="bx bx-trash me-0"></i>
											</button>
										</form>
									

									

										<button type="button" class="btn radius-10 p-1">
											<a href="{{ route('parts', $part->id) }}" class="text-decoration-none text-dark">
												<i class="bx bx-edit me-0"></i>
											</a>
										</button>
										
										
										
								
									</td>
							</tr>
							@endforeach
						</tbody>
					</table> --}}
					<table class="table mb-0 align-middle">
						<thead class="table-light">
							<tr>
								<th>Car Part Name</th>
								<th>Component</th> <!-- New column -->
								<th class="w-25 text-end"></th>
							</tr>
						</thead>
						<tbody>
							@foreach($carParts as $part)
							<tr>
								<td>{{ $part->name }}</td>
								<td>{{ $part->component }}</td> <!-- Display component -->
								<td class="text-end">
									<!-- Delete Button -->
									<form action="{{ route('parts.destroy', $part->id) }}" method="POST" style="display:inline;">
										@csrf
										@method('DELETE')
										<button type="submit" class="btn radius-10 p-1 text-danger" onclick="return confirm('Are you sure you want to delete this part?')">
											<i class="bx bx-trash me-0"></i>
										</button>
									</form>
					
									<button type="button" class="btn radius-10 p-1">
										<a href="{{ route('parts', $part->id) }}" class="text-decoration-none text-dark">
											<i class="bx bx-edit me-0"></i>
										</a>
									</button>
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
					
					
				</div>
				<!-- Place pagination OUTSIDE the table -->
<div class="d-flex justify-content-end mt-3">
	{{ $carParts->links('pagination::bootstrap-5') }}
</div>
			</div>
			
		</div>
		<!--end row-->


	</div>
</div>
</div>
</div>
<!--end page wrapper -->
@endsection
