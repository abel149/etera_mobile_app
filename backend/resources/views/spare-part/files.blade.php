@extends('layouts.sparepart')
@section('content')
<div class="container" style="max-width: 1200px;">
	<div class="row">
		<div class="col-12">
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
						<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
							<h5 class="mb-0">Proforma Files</h5>
							<div class="d-flex align-items-center gap-2">
								<label for="searchInput" class="fw-semibold mb-0">Search:</label>
								<input type="text" id="searchInput" class="form-control" style="width: 200px; min-width: 120px;" />
							</div>
						</div>

						<div class="table-responsive lead-table">
							<table class="table mb-0 align-middle" id="proformaTable">
								<thead class="table-light">
									<tr>
										<th>File #</th>
										<th>Car</th>
										<th>Status</th>
										<th>Submitted PIs</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									@foreach($proformas as $proforma)
									<tr>
										<td>{{ $proforma->file_number }}</td>
										<td>{{ $proforma->year }} {{ $proforma->brand?->name }} {{ $proforma->model }}( {{ $proforma->license_plate_number }})</td>
										
										<td class="
											@if($proforma->status == 'pending') text-warning 
											@elseif($proforma->status == 'completed') text-success
											@elseif($proforma->status == 'rejected') text-danger 
											@else text-secondary 
											@endif">
											{{ $proforma->status }}
										</td>
										@php        
										    $myApplicationsCount = $proforma->applications()
										    ->where('proforma_id', $proforma->id)->count();
										    @endphp
										<td> {{ $myApplicationsCount }}/{{ $proforma->required_number_of_shops == 0 ? '∞' : $proforma->required_number_of_shops }}</td>
										<td>
										    
										    @if($proforma->status === 'published' && !$proforma->close_request &&  $myApplicationsCount > 0)
										    <form action="{{ route('garage.proforma.request-close', ['proforma' => $proforma->id]) }}" method="POST">
										        @csrf
										        <button class="btn btn-sm btn-primary">
                                                Request Close Proforma
                                            </button>
                                        </form>
                                        @elseif($proforma->close_request && $proforma->status === 'published')
                                            <span class="fw-bold">Close Requested</span>
                                        @else
                                        <span class="text-muted">No Action Required</span>
                                        @endif
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
</div>

<!-- ✅ Search Script -->
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
	const query = this.value.toLowerCase();
	const rows = document.querySelectorAll('#proformaTable tbody tr');

	rows.forEach(row => {
		const text = row.textContent.toLowerCase();
		row.style.display = text.includes(query) ? '' : 'none';
	});
});
</script>
@endsection
