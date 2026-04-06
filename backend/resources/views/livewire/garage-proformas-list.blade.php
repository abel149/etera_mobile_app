<div>
    <style>
    /* Layout */
    .sidebar-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .sidebar {
        background: rgba(255, 255, 255, 0.04);
        padding: 24px;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    /* Sidebar column (desktop only) */
    .sidebar-col {
        margin-left: -60px;
    }

    /* Typography */
    .filter-label {
        font-weight: 600;
        color: var(--etera-text-soft, rgba(255,255,255,0.7));
        margin-bottom: 10px;
        font-size: 15px;
    }

    /* Buttons */
    .btn-xs {
        padding: 8px 12px !important;
        font-size: 14px !important;
        line-height: 1.3;
    }

    .btn-group .btn {
        border-radius: 6px !important;
        margin-bottom: 6px;
    }

    /* Job listing cards */
    .job-listing {
        display: block;
        background: rgba(255, 255, 255, 0.04);
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 18px;
        transition: 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #fff;
    }

    .job-listing:hover {
        border-color: rgba(13, 148, 136, 0.3);
        background: rgba(13, 148, 136, 0.06);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }

    /* Apply button */
    .list-apply-button {
        background: rgba(13, 148, 136, 0.15);
        color: var(--etera-teal-light, #5eead4) !important;
        border: 1px solid rgba(13, 148, 136, 0.3);
        padding: 10px 20px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 30px;
        transition: all 0.25s ease;
    }

    .list-apply-button:hover {
        background: rgba(13, 148, 136, 0.25);
        color: #fff !important;
    }

    /* Inputs */
    .form-control-sm {
        font-size: 15px;
        padding: 10px 12px;
    }

    /* Mobile adjustments */
    @media (max-width: 991px) {
        .sidebar-col {
            margin-left: 0;
        }

        .sidebar {
            padding: 20px;
            border-radius: 12px;
        }

        .btn-xs {
            font-size: 15px !important;
            padding: 10px 14px !important;
        }

        .filter-label {
            font-size: 16px;
        }

        .job-listing {
            padding: 20px;
        }

        .list-apply-button {
            width: 100%;
            text-align: center;
            margin-top: 15px;
        }
    }

    @media (max-width: 576px) {
        .page-title {
            font-size: 20px;
        }

        .notify-box {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }
</style>


    <main class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-xl-3 col-lg-4 mb-4 sidebar-col">
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
                                <i class="icon-material-outline-search"></i>
                            </div>
                        </div>

                        <!-- Car Type Filter -->
                        <div>
                            <h4 class="filter-label">Car Type</h4>
                            <div class="btn-group w-100" role="group">
                                @foreach(['All', 'ICE', 'EV', 'Hybrid'] as $type)
                                    <button
                                        wire:click="$set('filters.car_type', '{{ $type }}')"
                                        class="btn btn-xs w-100 {{ $filters['car_type'] === $type ? 'btn-primary' : 'btn-outline-primary' }}">
                                        {{ $type }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        

                        <!-- Component Filter -->
                        <div>
                            <h4 class="filter-label">Component Type</h4>
                            <div class="btn-group w-100" role="group">
                                @foreach($components as $comp)
                                    <button
                                        wire:click="$set('filters.component', '{{ $comp }}')"
                                        class="btn btn-xs w-100 {{ $filters['component'] === $comp ? 'btn-primary' : 'btn-outline-primary' }}">
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

            <!-- Main Content -->
            <div class="col-xl-9 col-lg-8 content-left-offset">
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

                <!-- Proforma List -->
                <div class="listings-container compact-list-layout margin-top-35">
                    @foreach($proformas as $proforma)

                        @if($proforma->userAlreadyApplied(auth()->id()))
                            @continue
                        @endif

                        @if(!auth()->user()->isInMyInbox($proforma->id) && $proforma->isApplicableBy(auth()->user()))
                            <a href="/garage/proforma-details?proforma={{ $proforma->id }}"
                               class="job-listing with-apply-button">

                                <div class="job-listing-details">

                                    <div class="job-listing-company-logo">
                                        <img src="{{ asset('asset/images/company-logo-01.png') }}" alt="">
                                    </div>

                                    <div class="job-listing-description">
                                        <h3 class="job-listing-title">
                                            {{ $proforma->poster->name ?? 'N/A' }}
                                        </h3>

                                        <div class="job-listing-footer">
                                            <ul>
                                                <li>
                                                    <i class="icon-material-outline-directions-car"></i>
                                                    {{ $proforma->year }},
                                                    {{ $proforma->brand?->name }},
                                                    {{ $proforma->model }}
                                                    [{{ $proforma->license_plate_number }}]
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
                                                @elseif(auth()->user()->role == 'garage')
                                                    <li>
                                                        <i class="icon-material-outline-settings"></i>
                                                        {{ $proforma->remaining_garages }} Remaining
                                                    </li>
                                                @endif

                                                <li>
                                                    <i class="icon-material-outline-access-time"></i>
                                                    {{ $proforma->created_at->diffForHumans() }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <span class="list-apply-button radius-30 ripple-effect">
                                        Apply Now
                                    </span>

                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="pagination-container mt-3">
                    {{ $proformas->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </main>
</div>
