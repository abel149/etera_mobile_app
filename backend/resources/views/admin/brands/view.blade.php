@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">Brands List</h3>
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
												<a href="/admin/add-brands" type="button" class="btn btn-primary radius-30 "><i class="bx bx-plus me-0"></i> Add Brand
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
								<th>Brand Name</th>
								<th class="w-25 text-end"></th>
							</tr>
						</thead>
						<tbody>
							@foreach($brands as $brand)
							<tr>
								<td>{{$brand->name}}</td>
								<td class="text-end">
									
									
								


										<form action="{{ route('brands.destroy', $brand->id) }}" method="POST" style="display:inline;">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn radius-10 p-1 text-danger" onclick="return confirm('Are you sure you want to delete this brand?')">
												<i class="bx bx-trash me-0"></i>
											</button>
										</form>
									

									

										<button type="button" class="btn radius-10 p-1">
											<a href="{{ route('brands', $brand->id) }}" class="text-decoration-none text-dark">
												<i class="bx bx-edit me-0"></i>
											</a>
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
@endsection
