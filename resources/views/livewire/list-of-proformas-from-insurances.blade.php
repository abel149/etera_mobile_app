<div>
    <style>
        .sidebar-container {
            display: flex;
            flex-direction: column;
        }

        .sidebar {
            background: rgba(255, 255, 255, 0.04);
            padding: 20px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar h4 {
            color: var(--etera-text-soft, rgba(255,255,255,0.7));
        }

        .input-with-icon input {
            background: rgba(255, 255, 255, 0.06) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #fff !important;
            border-radius: 10px;
        }

        .input-with-icon input:focus {
            border-color: var(--etera-teal) !important;
        }

        .notification.notice {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 20px;
            color: var(--etera-text-muted, rgba(255,255,255,0.5));
        }

        .margin-bottom-5 { margin-bottom: 5px; }
        .margin-bottom-10 { margin-bottom: 10px; }
        .margin-bottom-20 { margin-bottom: 20px; }
    </style>

    <!-- Page Content -->
    <main class="container">
        <div class="row">
            <div class="col-xl-3 col-lg-4">
                <div class="sidebar">
                    <div class="sidebar-container">
                        <!-- License Plate Number -->
                        <div class="margin-bottom-20">
                            <h4 class="margin-bottom-10">License Plate Number</h4>
                            <div class="input-with-icon">
                                <input wire:model.live="licenseNumber" id="autocomplete-input" type="text" placeholder="">
                                <i class="icon-material-outline-search"></i>
                            </div>
                        </div>

                        

                        <!-- Components Filter -->
                        <div class="margin-bottom-20">
                            <h4 class="margin-bottom-10">Components</h4>
                            <select class="form-select" wire:model.live="selectedComponents" multiple size="4">
                                @foreach($components as $component)
                                    <option value="{{ $component->condition }}">{{ $component->condition }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple. Leave empty to show all.</small>
                        </div>

                        <!-- Grades Filter -->
                        <div class="margin-bottom-20">
                            <h4 class="margin-bottom-10">Grades</h4>
                            <select class="form-select" wire:model.live="selectedGrades" multiple size="4">
                                @foreach($grades as $grade)
                                    @if($grade->grade)
                                        <option value="{{ $grade->grade }}">{{ $grade->grade }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple. Leave empty to show all.</small>
                        </div>

                        <!-- Brands Filter -->
                        <div class="margin-bottom-20">
                            <h4 class="margin-bottom-10">Car Brands</h4>
                            <select class="form-select" wire:model.live="selectedBrands" multiple size="4">
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple. Leave empty to show all.</small>
                        </div>

                        <!-- Insurances Filter -->
                        <div class="margin-bottom-20">
                            <h4 class="margin-bottom-10">Insurance Companies</h4>
                            <select class="form-select" wire:model.live="selectedInsurances" multiple size="4">
                                @foreach($insurances as $insurance)
                                    <option value="{{ $insurance->id }}">{{ $insurance->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple. Leave empty to show all.</small>
                        </div>

                        <!-- Chassis Number -->
                        <div class="margin-bottom-20">
                            <h4 class="margin-bottom-10">Chassis Number</h4>
                            <div class="input-with-icon">
                                <input wire:model.live.debounce.500ms="chasisNumber" type="text" placeholder="Search by chassis number">
                                <i class="icon-material-outline-search"></i>
                            </div>
                        </div>

                        <!-- File Number -->
                        <div class="margin-bottom-20">
                            <h4 class="margin-bottom-10">File Number</h4>
                            <div class="input-with-icon">
                                <input wire:model.live.debounce.500ms="fileNumber" type="text" placeholder="Search by file number">
                                <i class="icon-material-outline-search"></i>
                            </div>
                        </div>


                        
                    </div>
                </div>
            </div>

            <div class="col-xl-9 col-lg-8 content-left-offset">
                <!-- Header -->
                <div class="notify-box margin-top-15">
                    <div class="switch-container">
                        <h3 class="page-title margin-bottom-5">Proforma List</h3>
                    </div>
                    <div class="sort-by">
                        <span>Sort by:</span>
                        <select class="form-select" wire:model.live="sortBy" style="width: auto; display: inline-block;">
                            <option value="desc">Newest</option>
                            <option value="asc">Oldest</option>
                        </select>
                    </div>
                </div>

                <div class="listings-container compact-list-layout margin-top-35">
                    @php
                        $hasResults = false;
                    @endphp
                    @foreach($proformas as $proforma)
                        @if($proforma->userAlreadyApplied(auth()->id())) 
                            @continue
                        @endif

                        @if(!auth()->user()->isInMyInbox($proforma->id) && $proforma->isApplicableBy(auth()->user()))
                            @php $hasResults = true; @endphp
                            <a href="/proforma-details?proforma={{ $proforma->id }}" class="job-listing with-apply-button">
                                <div class="job-listing-details">
                                    <!-- Logo -->
                                    <div class="job-listing-company-logo">
                                        <img src="{{ asset('asset/images/company-logo-01.png') }}" alt="">
                                    </div>

                                    <!-- Details -->
                                    <div class="job-listing-description">
                                        <h3 class="job-listing-title">{{ $proforma->poster->name ?? 'N/A' }}</h3>
                                        <div class="job-listing-footer">
                                            <ul>
                                                <li><i class="icon-material-outline-directions-car"></i>{{ $proforma->year }}, {{ $proforma->brand->name ?? 'N/A' }}, {{ $proforma->model ?? 'N/A' }} [{{ $proforma->license_plate_number ?? 'N/A' }}]</li>
                                                @if(auth()->user()->role == 'garage')
                                                @elseif(auth()->user()->role == 'shop')
                                                    <li><i class="icon-material-outline-settings"></i> {{ $proforma->remaining_shops ?? 0 }} Remaining</li>
                                                @endif
                                                <li><i class="icon-material-outline-access-time"></i> {{ $proforma->created_at->diffForHumans() }}</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Apply Button -->
                                    @if(!$proforma->userAlreadyApplied(auth()->user()->id) 
                                    && ((auth()->user()->role == 'shop' && $proforma->applicationsFromShops()->count() != 3) 
                                    || (auth()->user()->role == 'garage' && $proforma->applicationsFromGarages()->count() != 3)))
                                        <span class="list-apply-button radius-30 ripple-effect">Apply Now</span>
                                    @endif
                                </div>
                            </a>
                        @endif
                    @endforeach
                    
                    @if(!$hasResults && $proformas->count() > 0)
                        <div class="notification notice">
                            <p>All proformas matching your filters have already been processed or are not applicable to your account.</p>
                        </div>
                    @elseif(!$hasResults)
                        <div class="notification notice">
                            <p>No proformas found matching your current filters. Try adjusting your search criteria.</p>
                        </div>
                    @endif
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div class="row">
                        <div class="pagination-container">
                            {{ $proformas->onEachSide(1)->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
