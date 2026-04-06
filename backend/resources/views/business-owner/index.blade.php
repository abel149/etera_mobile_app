@extends('layouts.business-owner')
@section('content')
<div class="row row-cols-12 row-cols-lg-12 row-cols-xl-12">
					<div class="col mx-auto">
						<div class=" my-5 my-lg-0 shadow-none ">
					  <div class="row">
			<div class="col-12 col-lg-6">
				<div class="card radius-10">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-0">Total Proformas</p>
								<h5 class="mb-0">{{auth()->check() ? auth()->user()->proformas->count() : 0}}</h5>
							</div>
							<div id="chart1"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="card radius-10">
			<div class="card-body">
				<div class="table-responsive lead-table">
					<table class="table mb-0 align-middle">
						<thead class="table-light">
							<tr>
								<th>File #</th>
								<th>Customer Name</th>
								<th>Car Brand</th>
								<th>Model</th>
								<th>Year</th>
								<th>License Plate</th>
								<th>Phone #</th>
								<th>Submitted applications</th>
								<th>Actions</th>
							</tr>
						</thead>
						          @php
            $proformas = auth()->user()->proformas()->orderBy('created_at','desc')->get(); 
                        @endphp
						<tbody>
							@foreach($proformas as $proforma)
							<tr>
								<td>{{$proforma->file_number}}</td>
								<td>
									<div class="d-flex align-items-center">
											<h6 class="mb-0 font-14">{{$proforma->customer_name}}</h6>
									</div>
								</td>
								<td>{{$proforma->brand?->name}}</td>
								<td>{{$proforma->model}}</td>
								<td>{{$proforma->year}}</td>
								<td>{{$proforma->license_plate_number}}</td>
								<td>{{$proforma->customer_phone_number}}</td>
								 @php
								    $myApplicationsCount = $proforma->applications()
									    ->where('proforma_id', $proforma->id)->count();
									@endphp
									<td> {{ $myApplicationsCount }}/{{ $proforma->required_number_of_shops == 0 ? '∞' : $proforma->required_number_of_shops }}</td>
								<td>
								   
									@if($proforma->status === 'published' && !$proforma->close_request &&  $myApplicationsCount > 0)
									<form action="{{ route('business-owner.proforma.request-close', ['proforma' => $proforma->id]) }}" method="POST">
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
				<!--end row-->

@push('scripts')
<script>
(function() {
    function refreshTable() {
        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTbody = doc.querySelector('tbody');
            const currentTbody = document.querySelector('tbody');
            if (newTbody && currentTbody) {
                currentTbody.innerHTML = newTbody.innerHTML;
            }
            // Update total count
            const newCount = doc.querySelector('h5.mb-0');
            const currentCount = document.querySelector('h5.mb-0');
            if (newCount && currentCount) {
                currentCount.textContent = newCount.textContent;
            }
        })
        .catch(err => console.warn('Table refresh error:', err));
    }
    setInterval(refreshTable, 30000);
    console.log('✅ Business owner table polling started (every 30s)');
})();
</script>
@endpush

@endsection
