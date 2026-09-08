
@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">Spare Parts Shops List</h3>
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<form id="searchForm" method="GET" action="{{ url('/admin/spare-part-shops') }}" class="row align-items-end mb-3 g-2">
							<div class="col-lg-3 col-md-4">
								<div class="position-relative">
									<input type="text" name="search" id="tableSearch" class="form-control ps-5 radius-30" placeholder="Search by name, phone or TIN..." value="{{ request('search') }}">
									<span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
								</div>
							</div>
							<div class="col-lg-3 col-md-4">
								<select name="brand_id" id="brandFilter" class="form-select radius-30">
									<option value="">All Brands</option>
									@foreach($brands as $brand)
										<option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="col-auto ms-auto">
								<a href="/admin/add-spare-part-shop" type="button" class="btn btn-primary radius-30"><i class="bx bx-plus me-0"></i> Spare Part Shop</a>
							</div>
						</form>

						<div id="searchableTable">
						<div class="table-responsive lead-table">
							<table class="table mb-0 align-middle">
								<thead class="table-light">
									<tr>
										<th>
											<input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
										</th>
										<th>Name</th>
										<th>Phone</th>
										<th>Tin #</th>
										<th>Registered By</th>
										<th>Register Date</th>
										<th>License Expiry</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									@foreach($shops as $shop)
									@php
										// Example date for 'license_expire_date'
										$licenseExpireDate = \Carbon\Carbon::create($shop->license_expire_date);  // Change this to dynamically fetch the date from the DB
										$currentDate = \Carbon\Carbon::now();

										// Check if the date is expired or expiring soon
										$isExpired = $licenseExpireDate->lessThan($currentDate);  // Expired if less than current date
										$isExpiringSoon = !$isExpired && $licenseExpireDate->lessThanOrEqualTo($currentDate->copy()->addMonth());  // Less than 1 month away, but not expired

										$formattedDate = $licenseExpireDate->format('D M d,Y'); 
									@endphp
									

									 <!--<tr >-->
										<!--<td><input class="form-check-input" type="checkbox" value="" id="flexCheckDefault"></td>-->
										<!--<td>-->
										<!--	<div class="d-flex align-items-center" id="shopRow{{$shop->id}}"  data-bs-toggle="modal" data-bs-target="#shopDetailModal{{$shop->id}}">-->
										<!--		<div>-->
										<!--			<h6 class="mb-0 font-14">{{$shop->name}}</h6>-->
										<!--			<p class="mb-0 font-13 text-secondary">{{$shop->email}}</p>-->
										<!--		</div>-->
										<!--	</div>-->
										<!--</td>-->
										<!--<td>{{$shop->phone_number}}</td>-->
										<!--<td>{{$shop->tin_number}}</td>-->
										<!--<td>No one</td>-->
										<!--<td>{{$shop->created_at}}</td>-->
									
										<!--<td>-->
										<!--	@if($isExpired)-->
										<!--		<div class="badge rounded-pill bg-danger w-100">{{ $formattedDate }}</div>-->
										<!--	@elseif($isExpiringSoon)-->
										<!--		<div class="badge rounded-pill bg-warning w-100">{{ $formattedDate }}</div>-->
										<!--	@else-->
										<!--		<div class="badge rounded-pill bg-success w-100">{{ $formattedDate }}</div>-->
										<!--	@endif-->
										<!--</td>-->
									
										<!--<td>-->
											<!-- Delete button remains outside of the clickable row -->
											<!--<a href="{{ route('edit-shop', $shop->id) }}" class="btn radius-10 p-1">-->
											<!--	<i class="bx bx-edit me-0"></i>-->
											<!--</a>-->
											<!-- Keep Delete button separate so it remains clickable -->
											<!--<button type="button" class="btn radius-10 p-1 text-danger" data-bs-toggle="modal" data-bs-target="#singleDelete{{$shop->id}}">-->
											<!--	<i class="bx bx-trash me-0"></i>-->
											<!--</button>-->
									
						

<!-- Table Row with Clickable Modal -->
<tr data-brand-ids="{{ $shop->brands->pluck('id')->implode(',') }}">
    <td><input class="form-check-input" type="checkbox" value="" id="flexCheckDefault"></td>
    <td>
        <div class="d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#shopDetailModal{{$shop->id}}">
            <div>
                <h6 class="mb-0 font-14">{{$shop->name}}</h6>
                <p class="mb-0 font-13 text-secondary">{{$shop->email}}</p>
            </div>
        </div>
    </td>
    <td>{{$shop->phone_number}}</td>
    <td>{{$shop->tin_number}}</td>
    
    <td>No one</td>
    <td>{{$shop->created_at}}</td>
    <td>
        @if($isExpired)
            <div class="badge rounded-pill bg-danger w-100">{{ $formattedDate }}</div>
        @elseif($isExpiringSoon)
            <div class="badge rounded-pill bg-warning w-100">{{ $formattedDate }}</div>
        @else
            <div class="badge rounded-pill bg-success w-100">{{ $formattedDate }}</div>
        @endif
    </td>
    <td>
        <a href="{{ route('edit-shop', $shop->id) }}" class="btn radius-10 p-1" title="Edit">
            <i class="bx bx-edit me-0"></i>
        </a>
        <button type="button" class="btn radius-10 p-1 text-info" data-bs-toggle="modal" data-bs-target="#shopBrandsModal{{$shop->id}}" title="View Brands">
            <i class="bx bx-purchase-tag me-0"></i>
        </button>
        <button type="button" class="btn radius-10 p-1 text-danger" data-bs-toggle="modal" data-bs-target="#singleDelete{{$shop->id}}">
            <i class="bx bx-trash me-0"></i>
        </button>
    </td>
</tr>

<!-- Modal for Full Row Click -->
<div class="modal fade" id="shopDetailModal{{$shop->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4" style="background: #f8f9fa;">
            <div class="modal-header" style="background: #7A2CB4; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px;">
				<h5 class="modal-title fw-bold"  style="color: white;">Insurance Details</h5>
             
				


				
				<button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-5">
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>Name:</strong> {{$shop->name}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>Email:</strong> {{$shop->email}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>Phone:</strong> {{$shop->phone_number}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>TIN:</strong> {{$shop->tin_number}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>Created:</strong> {{$shop->created_at}}</p>
                </div>
                <div class="mb-4">
                    <p class="font-weight-semibold"><strong>License Expiry:</strong> {{ $formattedDate }}</p>
                </div>
                <div class="row">
                    <!-- Business License Image -->
                    @if($shop->license_image)
                    <div class="col-md-6 mb-3">
                        <p class="font-weight-semibold"><strong>Business License:</strong></p>
                        <img src="{{ asset('storage/' . $shop->license_image) }}" alt="Business License Image" class="img-fluid rounded" style="max-width: 100%; height: auto;">
                    </div>
                    @else
                    <div class="col-md-6 mb-3">
                        <p>No business license image available.</p>
                    </div>
                    @endif

                    <!-- Stamp Image -->
                    @if($shop->stamp_image)
                    <div class="col-md-6 mb-3">
                        <p class="font-weight-semibold"><strong>Stamp:</strong></p>
                        <img src="{{ asset('storage/' . $shop->stamp_image) }}" alt="Stamp Image" class="img-fluid rounded" style="max-width: 100%; height: auto;">
                    </div>
                    @else
                    <div class="col-md-6 mb-3">
                        <p>No stamp image available.</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="modal-footer border-0" style="background: #f1f1f1;">
                <button type="button" class="btn btn-outline-primary radius-30 px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Delete Confirmation -->
<div class="modal fade" id="singleDelete{{$shop->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Are you sure you want to delete this Spare Part Shop?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('delete-shop', $shop->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger radius-30">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Shop Brands -->
<div class="modal fade" id="shopBrandsModal{{$shop->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header" style="background: #17a2b8; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h5 class="modal-title fw-bold" style="color: white;"><i class="bx bx-purchase-tag me-1"></i> Brands — {{ $shop->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                @if($shop->brands->count())
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($shop->brands as $brand)
                            <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 0.9rem;">{{ $brand->name }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No brands assigned to this shop.</p>
                @endif
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary radius-30 px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

										</td>
									</tr>
									
									
									@endforeach
								</tbody>

								
							</table>

						</div>{{-- /table-responsive --}}
						@if($shops->hasPages() || $shops->total() > 0)
						<div class="d-flex flex-column flex-sm-row align-items-center justify-content-between mt-3 px-1 gap-2">
							<div class="text-muted" style="font-size:0.875rem;">
								Showing <strong>{{ $shops->firstItem() ?? 0 }}</strong>
								to <strong>{{ $shops->lastItem() ?? 0 }}</strong>
								of <strong>{{ $shops->total() }}</strong> shops
							</div>
							@if($shops->hasPages())
							@php
								$cur   = $shops->currentPage();
								$last  = $shops->lastPage();
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
							<nav aria-label="Shops pagination">
								<ul class="pagination mb-0" style="gap:4px;">
									<li class="page-item {{ $shops->onFirstPage() ? 'disabled' : '' }}">
										<a class="page-link px-3" href="{{ $shops->appends(['search' => request('search'), 'brand_id' => request('brand_id')])->previousPageUrl() ?? '#' }}" style="border-radius:30px!important;">
											<i class="bx bx-chevron-left"></i> Prev
										</a>
									</li>
									@foreach($pages as $item)
										@if($item['type'] === 'dots')
											<li class="page-item disabled"><span class="page-link" style="border-radius:30px!important;">…</span></li>
										@else
											<li class="page-item {{ $item['n'] === $cur ? 'active' : '' }}">
												<a class="page-link" href="{{ $shops->appends(['search' => request('search'), 'brand_id' => request('brand_id')])->url($item['n']) }}" style="border-radius:30px!important;">{{ $item['n'] }}</a>
											</li>
										@endif
									@endforeach
									<li class="page-item {{ $shops->hasMorePages() ? '' : 'disabled' }}">
										<a class="page-link px-3" href="{{ $shops->appends(['search' => request('search'), 'brand_id' => request('brand_id')])->nextPageUrl() ?? '#' }}" style="border-radius:30px!important;">
											Next <i class="bx bx-chevron-right"></i>
										</a>
									</li>
								</ul>
							</nav>
							@endif
						</div>
						@endif
						</div>{{-- /searchableTable --}}

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

<!-- Selected Delete Modal -->
<div class="modal fade" id="selectedDelete" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Delete Selected Spare Part Shops</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">Are you sure you want to delete the selected Spare Part Shops?</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger radius-30">Delete</button>
			</div>
		</div>
	</div>
</div>
<!-- End Selected Delete Modal -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('searchForm');
    const searchInput = document.getElementById('tableSearch');
    const brandFilter = document.getElementById('brandFilter');
    const searchableTable = document.getElementById('searchableTable');
    if (!form || !searchInput || !searchableTable) return;

    // Restore cursor to end of input after AJAX navigation
    if (searchInput.value) {
        searchInput.focus();
        const len = searchInput.value.length;
        searchInput.setSelectionRange(len, len);
    }

    function clientFilterRows() {
        const query = searchInput.value.toLowerCase().trim();
        const selectedBrand = brandFilter ? brandFilter.value : '';
        const rows = searchableTable.querySelectorAll('tbody tr');
        rows.forEach(function (row) {
            const name = (row.querySelector('td:nth-child(2)')?.textContent || '').toLowerCase();
            const phone = (row.querySelector('td:nth-child(3)')?.textContent || '').toLowerCase();
            const tin = (row.querySelector('td:nth-child(4)')?.textContent || '').toLowerCase();
            const brandIds = (row.getAttribute('data-brand-ids') || '').split(',');
            const matchesText = !query || name.includes(query) || phone.includes(query) || tin.includes(query);
            const matchesBrand = !selectedBrand || brandIds.includes(selectedBrand);
            row.style.display = (matchesText && matchesBrand) ? '' : 'none';
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

    if (brandFilter) {
        brandFilter.addEventListener('change', function () { fetchTable(getSearchUrl()); });
    }

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
