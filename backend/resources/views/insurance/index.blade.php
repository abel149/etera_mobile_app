@extends('layouts.insurance')
@section('content')
<div class="row row-cols-12 row-cols-lg-12 row-cols-xl-12">
	<div class="col mx-auto">
		<div class="my-5 my-lg-0 shadow-none">
			<div class="row g-3">
				<div class="col-12 col-lg-3">
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
				<div class="col-12 col-lg-3">
					<div class="card radius-10">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between">
								<div>
									<p class="mb-0">Completed Files</p>
									<h5 class="mb-0">{{ auth()->check() ? auth()->user()->proformas->where('status', 'completed')->count() : 0 }}</h5>
								</div>
								<div id="chart4"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12 col-lg-3">
					<div class="card radius-10">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between">
								<div>
									<p class="mb-0">Pending Files</p>
									<h5 class="mb-0">{{ auth()->check() ? auth()->user()->proformas->whereIn('status', ['pending', 'opened', 'published'])->count() : 0 }}</h5>
								</div>
								<div id="chart5"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12 col-lg-3">
					<div class="card radius-10">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between">
								<div>
									<p class="mb-0">Closed Files</p>
									<h5 class="mb-0">{{ auth()->check() ? auth()->user()->proformas->where('status', 'closed')->count() : 0 }}</h5>
								</div>
								<div id="chart6"></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="card radius-10 mt-4">
				<div class="card-body">
					<!-- ✅ Search Bar -->
					<form id="searchForm" method="GET" action="{{ url('/insurance') }}" class="d-flex justify-content-between align-items-center mb-4">
						<h5 class="mb-0">Proforma Files</h5>
						<div class="d-flex align-items-center gap-2">
							<label for="searchInput" class="fw-semibold mb-0">Search:</label>
							<input type="text" name="search" id="searchInput" placeholder="Search File, Customer, License Plate or Phone..." class="form-control" value="{{ request('search') }}" />
						</div>
					</form>

					<div id="searchableTable">
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
									<th>Parts Progress</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								@foreach($proformas as $proforma)
								<tr>
									<td>{{ $proforma->file_number }}</td>
									<td><h6 class="mb-0 font-14">{{ $proforma->customer_name }}</h6></td>
									<td>{{ $proforma->brand->name ?? 'N/A' }}</td>
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
									<td>
										@php $progress = $proforma->partsPricingProgress(); @endphp
										@if($progress['total'] > 0)
											<span class="badge {{ $progress['filled'] >= $progress['total'] ? 'bg-success' : 'bg-warning' }}">{{ $progress['filled'] }}/{{ $progress['total'] }}</span>
										@else
											<span class="text-muted">N/A</span>
										@endif
									</td>
									<td>
										<a href="{{ url('/insurance/proforma/' . $proforma->id . '/manage-inboxes') }}" class="btn btn-sm btn-outline-primary" title="Manage Inboxes">
											<i class="bx bx-show"></i>
										</a>
										@if(in_array($proforma->status, ['published','pending','opened']) && !$proforma->close_request && $proforma->applications()->count() > 0)
											<form action="{{ route('insurance.proforma.request-close', ['proforma' => $proforma->id]) }}" method="POST" class="d-inline">
												@csrf
												<button type="submit" class="btn btn-primary btn-sm">Request Close Proforma</button>
											</form>
										@elseif($proforma->close_request && in_array($proforma->status, ['published','pending','opened']))
											<span class="fw-bold">Close Requested</span>
										@endif
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>{{-- /table-responsive --}}
					@if($proformas->hasPages())
					<div class="d-flex flex-column flex-sm-row align-items-center justify-content-between mt-3 px-1 gap-2">
						<div class="text-muted" style="font-size:0.875rem;">
							Showing <strong>{{ $proformas->firstItem() ?? 0 }}</strong>
							to <strong>{{ $proformas->lastItem() ?? 0 }}</strong>
							of <strong>{{ $proformas->total() }}</strong> proformas
						</div>
						@php
							$cur   = $proformas->currentPage();
							$last  = $proformas->lastPage();
							$range = collect();
							for ($p = 1; $p <= $last; $p++) {
								if ($p === 1 || $p === $last || abs($p - $cur) <= 1) {
									$range->push(['type' => 'page', 'n' => $p]);
								} elseif (abs($p - $cur) === 2) {
									$range->push(['type' => 'dots']);
								}
							}
							$pages = collect();
							$prevDot = false;
							foreach ($range as $item) {
								if ($item['type'] === 'dots') {
									if (!$prevDot) $pages->push($item);
									$prevDot = true;
								} else {
									$pages->push($item);
									$prevDot = false;
								}
							}
						@endphp
						<nav aria-label="Proformas pagination">
							<ul class="pagination mb-0" style="gap:4px;">
								<li class="page-item {{ $proformas->onFirstPage() ? 'disabled' : '' }}">
									<a class="page-link radius-30 px-3" href="{{ $proformas->previousPageUrl() ?? '#' }}" style="border-radius:30px!important;">
										<i class="bx bx-chevron-left"></i> Prev
									</a>
								</li>
								@foreach($pages as $item)
									@if($item['type'] === 'dots')
										<li class="page-item disabled"><span class="page-link" style="border-radius:30px!important;">…</span></li>
									@else
										<li class="page-item {{ $item['n'] === $cur ? 'active' : '' }}">
											<a class="page-link radius-30" href="{{ $proformas->url($item['n']) }}" style="border-radius:30px!important;">{{ $item['n'] }}</a>
										</li>
									@endif
								@endforeach
								<li class="page-item {{ $proformas->hasMorePages() ? '' : 'disabled' }}">
									<a class="page-link radius-30 px-3" href="{{ $proformas->nextPageUrl() ?? '#' }}" style="border-radius:30px!important;">
										Next <i class="bx bx-chevron-right"></i>
									</a>
								</li>
							</ul>
						</nav>
					</div>
					@endif
					</div>{{-- /searchableTable --}}
				</div>
			</div>
		</div>
	</div>
</div>

<!-- ✅ Search Script (seamless AJAX) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const searchableTable = document.getElementById('searchableTable');
    if (!form || !searchInput || !searchableTable) return;

    if (searchInput.value) {
        searchInput.focus();
        const len = searchInput.value.length;
        searchInput.setSelectionRange(len, len);
    }

    function clientFilterRows() {
        const query = searchInput.value.toLowerCase().trim();
        const rows = searchableTable.querySelectorAll('tbody tr');
        rows.forEach(function (row) {
            const fileNumber   = (row.cells[0]?.textContent || '').toLowerCase();
            const customerName = (row.cells[1]?.textContent || '').toLowerCase();
            const plateNumber  = (row.cells[5]?.textContent || '').toLowerCase();
            const phone        = (row.cells[7]?.textContent || '').toLowerCase();
            row.style.display = (
                !query ||
                fileNumber.includes(query) ||
                customerName.includes(query) ||
                plateNumber.includes(query) ||
                phone.includes(query)
            ) ? '' : 'none';
        });
    }

    async function fetchTable(url) {
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('fetch failed');
            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newContent = doc.getElementById('searchableTable');
            if (newContent) {
                searchableTable.innerHTML = newContent.innerHTML;
                history.pushState({}, '', url);
                bindPaginationLinks();
            }
        } catch (e) {
            form.submit();
        }
    }

    function getSearchUrl() {
        const params = new URLSearchParams(new FormData(form));
        return form.action + '?' + params.toString();
    }

    let debounceTimer;
    searchInput.addEventListener('input', function () {
        clientFilterRows();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () { fetchTable(getSearchUrl()); }, 700);
    });

    function bindPaginationLinks() {
        searchableTable.querySelectorAll('nav a.page-link[href]:not([href="#"])').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                fetchTable(this.href);
            });
        });
    }

    bindPaginationLinks();
});
</script>
@endsection
