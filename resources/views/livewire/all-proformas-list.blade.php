<div>
    <style>
        .sidebar-container {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .sidebar {
            background: rgba(255, 255, 255, 0.04);
            padding: 20px 22px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
.notify-box {
    flex-wrap: nowrap;
}

.notify-box .page-title {
    flex: 1 1 auto;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.notify-box .sort-by {
    flex-shrink: 0;
}


        .filter-label {
            font-weight: 600;
            color: var(--etera-text-soft, rgba(255,255,255,0.7));
            margin-bottom: 8px;
            font-size: 14px;
        }

        .btn-xs {
            padding: 4px 8px !important;
            font-size: 12px !important;
            line-height: 1.2;
        }

        .btn-group .btn {
            border-radius: 4px !important;
            margin-bottom: 4px;
        }

        .job-listing {
            display: block;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 15px;
            transition: 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none !important;
            color: #fff;
        }

        .job-listing:hover {
            border-color: rgba(13, 148, 136, 0.3);
            background: rgba(13, 148, 136, 0.06);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }

        .list-apply-button {
            background: rgba(13, 148, 136, 0.15);
            color: var(--etera-teal-light, #5eead4) !important;
            border: 1px solid rgba(13, 148, 136, 0.3);
            padding: 8px 15px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            border-radius: 50px;
            transition: all 0.25s ease;
        }

        .list-apply-button:hover {
            background: rgba(13, 148, 136, 0.25);
            color: #fff !important;
        }

        /* Responsive Sidebar Logic */
        @media (min-width: 1200px) {
            .sidebar-col {
                margin-left: -100px;
            }
        }

        .job-listing-details {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        @media (min-width: 768px) {
            .job-listing-details {
                flex-direction: row;
                align-items: center;
            }
        }

        .job-listing-company-logo img {
            width: 50px;
            height: auto;
            border-radius: 10px;
        }

        .job-listing-description {
            flex-grow: 1;
        }

        .job-listing-footer ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 15px;
            color: var(--etera-text-muted, rgba(255,255,255,0.5));
            font-size: 13px;
        }

        .job-listing-footer li {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .job-listing-footer li i {
            color: var(--etera-teal-light, #5eead4);
        }

        .alert-light {
            background: rgba(255, 255, 255, 0.04) !important;
            color: var(--etera-text-muted) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
    </style>

    <main class="container py-4">
        <div class="row g-4">
            <!-- Sidebar Filters - Moved to order-1 for mobile, order-lg-1 keeps it on left for desktop -->
            <div class="col-12 col-lg-4 col-xl-3 order-1 order-lg-1 sidebar-col">
                <div class="sidebar">
                    <div class="sidebar-container">

                        <!-- License Plate Search -->
                        <div>
                            <h4 class="filter-label">Search by License Plate</h4>
                            <div class="input-with-icon mb-2">
                                <input
                                    wire:model.live="filters.license"
                                    type="text"
                                    class="form-control form-control-sm"
                                    placeholder="Enter license plate">
                            </div>
                        </div>

                        <!-- Proforma Type Filter -->
                        <div>
                            <h4 class="filter-label">Proforma Type</h4>
                            <div class="btn-group w-100 flex-wrap" role="group">
                                @foreach(['default' => 'Default', 'insurance' => 'Insurance', 'others' => 'Others'] as $key => $label)
                                    <button
                                        wire:click="$set('filters.type', '{{ $key }}')"
                                        class="btn btn-xs flex-fill {{ ($filters['type'] ?? '') === $key ? 'btn-primary' : 'btn-outline-primary' }}">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Car Type Filter -->
                        <div>
                            <h4 class="filter-label">Car Type</h4>
                            <div class="btn-group w-100 flex-wrap" role="group">
                                @foreach(['All', 'ICE', 'EV', 'Hybrid'] as $type)
                                    <button
                                        wire:click="$set('filters.car_type', '{{ $type }}')"
                                        class="btn btn-xs flex-fill {{ ($filters['car_type'] ?? 'All') === $type ? 'btn-primary' : 'btn-outline-primary' }}">
                                        {{ $type }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Grade Filter -->
                        <div>
                            <h4 class="filter-label">Grade</h4>
                            <div class="btn-group w-100 flex-wrap" role="group">
                                @foreach(['All', '1st', '2nd', '3rd', '4th'] as $grade)
                                    <button
                                        wire:click="$set('filters.grade', '{{ $grade }}')"
                                        class="btn btn-xs flex-fill {{ ($filters['grade'] ?? 'All') === $grade ? 'btn-primary' : 'btn-outline-primary' }}">
                                        {{ $grade }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Component Filter -->
                        <div>
                            <h4 class="filter-label">Component Type</h4>
                            <div class="btn-group w-100 flex-wrap" role="group">
                                @foreach($components as $comp)
                                    <button
                                        wire:click="$set('filters.component', '{{ $comp }}')"
                                        class="btn btn-xs flex-fill {{ ($filters['component'] ?? '') === $comp ? 'btn-primary' : 'btn-outline-primary' }}">
                                        {{ $comp }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Reset -->
                        <div class="text-center mt-2">
                            <button
                                class="btn btn-outline-secondary btn-xs w-100"
                                wire:click="clearFilters">
                                Reset Filters
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Main Content - Changed to order-2 for mobile -->
            <div class="col-12 col-lg-8 col-xl-9 order-2 order-lg-2">

<div class="notify-box d-flex flex-column flex-sm-row align-items-start align-items-sm-center mb-4 gap-2">
    
    <!-- Title -->
    <h3 class="page-title mb-0 text-xl font-bold flex-grow-1">
        All Proformas
    </h3>

    <!-- Sort -->
    <div class="sort-by d-flex align-items-center flex-shrink-0">
        <span class="me-2 text-nowrap">Sort by:</span>
        <select class="form-select form-select-sm w-auto" wire:model.live="sortBy">
            <option value="desc">Newest</option>
            <option value="asc">Oldest</option>
        </select>
    </div>

</div>




                <!-- Proforma List -->
                <div class="listings-container">
                    @forelse($proformas as $proforma)

                        @if($proforma->userAlreadyApplied(auth()->id()))
                            @continue
                        @endif

                        @if(!auth()->user()->isInMyInbox($proforma->id) && $proforma->isApplicableBy(auth()->user()))
                            <a href="/spare-part-shops/proforma-details?proforma={{ $proforma->id }}"
                               class="job-listing">

                                <div class="job-listing-details">
                                    <div class="job-listing-company-logo">
                                        <img src="{{ asset('asset/images/company-logo-01.png') }}" alt="Company Logo">
                                    </div>

                                    <div class="job-listing-description">
                                        @if($proforma->poster->role == 'garage')
                                            <h3 class="job-listing-title h5 mb-2">Garage</h3>
                                        @elseif($proforma->poster->role == 'insurance')
                                            <h3 class="job-listing-title h5 mb-2">{{ $proforma->poster->name ?? 'N/A' }}</h3>
                                        @else
                                            <h3 class="job-listing-title h5 mb-2">{{ $proforma->file_number ?? 'N/A' }}</h3>
                                        @endif

                                        <div class="job-listing-footer">
                                            <ul>
                                                <li>
                                                    <i class="icon-material-outline-directions-car"></i>
                                                    {{ $proforma->year }}, {{ $proforma->brand?->name }}, {{ $proforma->model }} [{{ $proforma->license_plate_number }}]
                                                </li>
                                                <li>
                                                    <i class="icon-material-outline-business"></i>
                                                    {{ ucfirst($proforma->poster->role ?? 'N/A') }}
                                                </li>
                                                @if(auth()->user()->role == 'shop')
                                                    <li>
                                                        <i class="icon-material-outline-settings"></i>
                                                        {{ $proforma->remaining_shops }} Remaining
                                                    </li>
                                                @endif
                                                <li>
                                                    <i class="icon-material-outline-access-time"></i>
                                                    {{ $proforma->created_at->diffForHumans() }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <span class="list-apply-button radius-30">
                                        Apply Now
                                    </span>
                                </div>
                            </a>
                        @endif
                    @empty
                        <div class="alert alert-light text-center border">
                            No proformas found.
                        </div>
                    @endforelse
                </div>

                <div class="pagination-container mt-4">
                    {{ $proformas->onEachSide(1)->links() }}
                </div>

            </div>
        </div>
    </main>
</div>