@extends('layouts.insurance')
@section('content')
<div class="row row-cols-12 row-cols-lg-12 row-cols-xl-12">
	<div class="col mx-auto">
		<div class="my-5 my-lg-0 shadow-none">
			<div class="row">
				<div class="col-12 col-lg-4">
					<div class="card radius-10">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between">
								<div>
									<p class="mb-0">Total Files</p>
									<h5 class="mb-0">{{ auth()->check() ? auth()->user()->proformas->count() : 0 }}</h5>
								</div>
								<div id="chart3"></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			@php
				$proformas = auth()->user()->proformas()->orderBy('created_at', 'desc')->get();
			@endphp

			<div class="card radius-10 mt-4">
				<div class="card-body">
					<!-- ✅ Search Bar -->
					<div class="d-flex justify-content-between align-items-center mb-4">
						<h5 class="mb-0">Proforma Files</h5>
						<div class="d-flex align-items-center gap-2">
							<label for="searchInput" class="fw-semibold mb-0">Search:</label>
							<input type="text" id="searchInput" placeholder="Search File, Customer, License Plate or Phone..." class="form-control" />
						</div>
					</div>

					<div class="table-responsive lead-table">
						<table class="table mb-0 align-middle" id="proformaTable">
							<thead class="table-light">
								<tr>
									<th>File #</th>
									<th>Customer Name</th>
									<th>Car Brand</th>
									<th>Model</th>
									<th>Year</th>
									<th>License Plate</th>
									<th>Type</th>
									<th>Phone #</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								@foreach($proformas as $proforma)
								<tr>
									<td>{{ $proforma->file_number }}</td>
									<td><h6 class="mb-0 font-14">{{ $proforma->customer_name }}</h6></td>
									<td>{{ $proforma->brand->name }}</td>
									<td>{{ $proforma->model }}</td>
									<td>{{ $proforma->year }}</td>
									<td>{{ $proforma->license_plate_number }}</td>

                                            <td>
                                                @if($proforma->insured)
                                                    <span class="badge rounded-pill bg-primary w-100"
                                                          data-remaining-time="{{ $proforma->timer_expires_at?->toISOString() }}">
                                                        Insured
                                                    </span>
                                                @else
                                                    <span class="text-muted">Not Insured</span>
                                                @endif
                                            </td>
									<td>{{ $proforma->customer_phone_number }}</td>
									<td class="
										@if($proforma->status == 'pending') text-warning 
										@elseif($proforma->status == 'completed') text-success 
										@elseif($proforma->status == 'rejected') text-danger 
										@else text-secondary 
										@endif">
										{{ $proforma->status }}
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<!-- ✅ Search Script -->
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
	const query = this.value.toLowerCase();
	const rows = document.querySelectorAll('#proformaTable tbody tr');

	rows.forEach(row => {
		const fileNumber = row.cells[0].textContent.toLowerCase();
		const customerName = row.cells[1].textContent.toLowerCase();
		const plateNumber = row.cells[5].textContent.toLowerCase();
		const phone = row.cells[6].textContent.toLowerCase();

		if (fileNumber.includes(query) || customerName.includes(query) || plateNumber.includes(query) || phone.includes(query)) {
			row.style.display = '';
		} else {
			row.style.display = 'none';
		}
	});
});
</script>
@endsection
