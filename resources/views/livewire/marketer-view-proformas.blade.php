<div>
    <style>
        /* ===========================
           Responsive Styles Only
        =========================== */
        .sidebar-container { display: flex; flex-direction: column; gap: 20px; }
        .sidebar { background-color: rgba(255, 255, 255, 0.04); padding: 24px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .sidebar-col { margin-left: 0; } /* reset negative margin for mobile */
        .filter-label { font-weight: 600; color: var(--etera-text-soft); margin-bottom: 10px; font-size: 15px; }
        .btn-xs { padding: 8px 12px !important; font-size: 14px !important; line-height: 1.3; }
        .btn-group .btn { border-radius: 6px !important; margin-bottom: 6px; flex: 1; }
        .job-listing { display: flex; flex-direction: column; background: rgba(255, 255, 255, 0.04); border-radius: 12px; padding: 24px; margin-bottom: 18px; transition: 0.3s ease; border: 1px solid rgba(255, 255, 255, 0.08); }
        .job-listing:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.12); transform: translateY(-2px); }
        .list-apply-button { background: var(--etera-teal); color: white; padding: 10px 20px; font-size: 15px; font-weight: 600; border-radius: 30px; display: inline-block; margin-top: 15px; text-align: center; }
        .list-apply-button:hover { background: rgba(13, 148, 136, 0.25); }
        .form-control-sm { font-size: 15px; padding: 10px 12px; }
        .pagination-container { display: flex; justify-content: center; margin-top: 20px; }
        .sort-by { flex-wrap: wrap; gap: 10px; }

        /* ===========================
           Responsive Media Queries
        =========================== */
        @media (max-width: 991px) {
            .row { flex-direction: column; }
            .sidebar-col { order: 1; margin-bottom: 20px; }
            .content-left-offset { order: 2; }
            .sidebar { padding: 20px; border-radius: 12px; }
            .btn-xs { font-size: 15px !important; padding: 10px 14px !important; }
            .filter-label { font-size: 16px; }
            .job-listing { padding: 20px; }
            .list-apply-button { width: 100%; }
        }

        @media (max-width: 576px) {
            .page-title { font-size: 20px; }
            .notify-box { flex-direction: column; align-items: flex-start; gap: 10px; }
            .btn-group .btn { width: 100% !important; margin-bottom: 10px; }
            .job-listing-description h3 { font-size: 16px; }
            .job-listing { padding: 15px; }
            .list-apply-button { padding: 10px; font-size: 14px; }
            .sort-by { flex-direction: column; align-items: flex-start; }
        }
    </style>

        <div class="page-content">
            <main class="container">
                <div class="row flex-wrap">
                    <!-- Sidebar Filters -->
                    <div class="col-xl-4 col-lg-5 mb-4 sidebar-col">
                        <div class="sidebar">
                            <div class="sidebar-container">

                                <!-- License Plate Search -->
                                <div>
                                    <h4 class="filter-label">Search by License Plate</h4>
                                    <input
                                        wire:model.debounce.300ms="filters.license"
                                        type="text"
                                        class="form-control form-control-sm"
                                        placeholder="Enter license plate">
                                </div>

                                <!-- Proforma Type Filter -->
                                <div class="mt-3">
                                    <h4 class="filter-label">Proforma Type</h4>
                                    <div class="btn-group w-100" role="group">
                                        <button wire:click="$set('filters.type','default')" class="btn btn-xs w-100 {{ $filters['type'] === 'default' ? 'btn-primary' : 'btn-outline-primary' }}">Default</button>
                                        <button wire:click="$set('filters.type','insurance')" class="btn btn-xs w-100 {{ $filters['type'] === 'insurance' ? 'btn-primary' : 'btn-outline-primary' }}">Insurance</button>
                                        <button wire:click="$set('filters.type','others')" class="btn btn-xs w-100 {{ $filters['type'] === 'others' ? 'btn-primary' : 'btn-outline-primary' }}">Others</button>
                                    </div>
                                </div>

                                <!-- Car Type Filter -->
                                <div class="mt-3">
                                    <h4 class="filter-label">Car Type</h4>
                                    <div class="btn-group w-100" role="group">
                                        @foreach(['All','ICE','EV','Hybrid'] as $type)
                                            <button wire:click="$set('filters.car_type','{{ $type }}')" class="btn btn-xs w-100 {{ $filters['car_type'] === $type ? 'btn-primary' : 'btn-outline-primary' }}">{{ $type }}</button>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Grade Filter -->
                                <div class="mt-3">
                                    <h4 class="filter-label">Grade</h4>
                                    <div class="btn-group w-100" role="group">
                                        @foreach(['All','1st','2nd','3rd','4th'] as $grade)
                                            <button wire:click="$set('filters.grade','{{ $grade }}')" class="btn btn-xs w-100 {{ $filters['grade'] === $grade ? 'btn-primary' : 'btn-outline-primary' }}">{{ $grade }}</button>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Component Filter -->
                                <div class="mt-3">
                                    <h4 class="filter-label">Component Type</h4>
                                    <div class="btn-group w-100" role="group">
                                        @foreach($components as $comp)
                                            <button wire:click="$set('filters.component','{{ $comp }}')" class="btn btn-xs w-100 {{ $filters['component'] === $comp ? 'btn-primary' : 'btn-outline-primary' }}">{{ $comp }}</button>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Reset Filters -->
                                <div class="text-center mt-3">
                                    <button class="btn btn-outline-secondary btn-xs w-100" wire:click="clearFilters">Reset Filters</button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="col-xl-8 col-lg-7 content-left-offset">

                        <!-- Sorting -->
                        <div class="notify-box margin-top-15 d-flex justify-content-between align-items-center">
                            <h3 class="page-title mb-0">All Proformas</h3>
                            <div class="sort-by d-flex align-items-center">
                                <span class="me-2">Sort by:</span>
                                <select class="form-select form-select-sm" wire:model="sortBy">
                                    <option value="desc">Newest</option>
                                    <option value="asc">Oldest</option>
                                </select>
                            </div>
                        </div>

                        <!-- Proforma Listings -->
                        <div class="listings-container compact-list-layout margin-top-35">
                            @forelse($proformas as $proforma)
                                <a href="/marketer/proforma-details?proforma={{ $proforma->id }}" class="job-listing with-apply-button">
                                    <div class="job-listing-description">
                                        <h3 class="job-listing-title">
                                            @if(optional($proforma->poster)->role === 'garage')
                                                Garage
                                            @elseif(optional($proforma->poster)->role === 'insurance')
                                                {{ optional($proforma->poster)->name ?? 'N/A' }}
                                            @else
                                                {{ $proforma->file_number ?? 'N/A' }}
                                            @endif
                                        </h3>

                                        <div class="job-listing-footer">
                                            <ul>
                                                <li>
                                                    <i class="icon-material-outline-directions-car"></i>
                                                    {{ $proforma->year ?? 'N/A' }},
                                                    {{ optional($proforma->brand)->name ?? 'N/A' }},
                                                    {{ $proforma->model ?? 'N/A' }}
                                                    [{{ $proforma->license_plate_number ?? 'N/A' }}]
                                                </li>
                                                <li>
                                                    <i class="icon-material-outline-business"></i>
                                                    {{ ucfirst(optional($proforma->poster)->role ?? 'N/A') }}
                                                </li>
                                                <li>
                                                    <i class="icon-material-outline-settings"></i>
                                                    {{ $proforma->remaining_shops ?? 0 }} Remaining
                                                </li>
                                                <li>
                                                    <i class="icon-material-outline-access-time"></i>
                                                    {{ $proforma->created_at->diffForHumans() ?? 'N/A' }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <span class="list-apply-button radius-30 ripple-effect">View</span>
                                </a>
                            @empty
                                <div class="alert alert-info">No proformas found.</div>
                            @endforelse
                        </div>

                        <!-- Pagination -->
                        <div class="pagination-container mt-3">
                            {{ $proformas->onEachSide(1)->links() }}
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
