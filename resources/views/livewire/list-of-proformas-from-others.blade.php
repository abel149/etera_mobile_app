<div style="padding-bottom: 60px;"> <!-- Add padding to the bottom here -->

    <!-- Page Content
    ================================================== -->
    <div class="container">
        <div class="row">
            <div class="col-xl-3 col-lg-4">
                <div class="sidebar-container">

                    <!-- Location -->
                    <div class="margin-bottom-20">
                        <h4 class="margin-bottom-10">File Number</h4>
                        <div class="input-with-icon">
                            <div>
                                <input wire:model.live="fileNumber" type="text" placeholder="">
                            </div>
                            <i class="icon-material-outline-search"></i>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="margin-bottom-20">
                        <h4 class="margin-bottom-10">Chassis Number</h4>
                        <div class="input-with-icon">
                            <div>
                                <input wire:model.live="chassisNumber" type="text" placeholder="">
                            </div>
                            <i class="icon-material-outline-search"></i>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="margin-bottom-20">
                        <h4 class="margin-bottom-10">License Plate Number</h4>
                        <div class="input-with-icon">
                            <div id="autocomplete-container">
                                <input wire:model.live="licenseNumber" id="autocomplete-input" type="text" placeholder="">
                            </div>
                            <i class="icon-material-outline-search"></i>
                        </div>
                    </div>

                    <div wire:ignore class="margin-bottom-20">
                        <h4 class="margin-bottom-10">Grades</h4>
                        <select class="selectpicker" wire:model.live="selectedGrades" multiple data-selected-text-format="count" data-size="7" title="All Grades">
                            @foreach($grades as $grade)
                                <option value="{{ $grade->grade }}">{{ $grade->grade }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Components Filter -->
                    <div wire:ignore class="margin-bottom-20">
                        <h4 class="margin-bottom-10">Components</h4>
                        <select class="selectpicker" wire:model.live="selectedComponents" multiple data-selected-text-format="count" data-size="7" title="All Components">
                            @foreach($components as $component)
                                <option value="{{ $component->condition }}" {{ $component->condition == 'Body Parts' ? 'selected' : '' }}>{{ $component->condition }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- All Parts Filter -->
                    <div wire:ignore class="margin-bottom-20">
                        <h4 class="margin-bottom-10">All Available Parts</h4>
                        <select class="selectpicker" multiple data-selected-text-format="count" data-size="7" title="All Parts" data-live-search="true">
                            @foreach($allParts as $part)
                                <option value="{{ $part->name }}">{{ $part->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

            <div class="col-xl-9 col-lg-8 content-left-offset">

                <div class="notify-box margin-top-15">
                    <div class="switch-container">
                        <h3 class="page-title">Proforma lList</h3>
                    </div>
                    <div class="sort-by">
                        <span>Sort by:</span>
                        <select class="selectpicker hide-tick">
                            <option>Relevance</option>
                            <option>Newest</option>
                            <option>Oldest</option>
                            <option>Random</option>
                        </select>
                    </div>
                </div>

                <div class="listings-container compact-list-layout margin-top-35">

                    <!-- Proforma Listing -->
                    @foreach($proformas as $proforma)
                        @if($proforma->userAlreadyApplied(auth()->id())) 
                            @continue
                        @endif

                        @if(!auth()->user()?->isInMyInbox($proforma->id) && $proforma->isApplicableBy(auth()->user()))
                            <a href="/proforma-details?proforma={{$proforma->id}}" class="job-listing with-apply-button">

                                <!-- Job Listing Details -->
                                <div class="job-listing-details">

                                    <!-- Logo -->
                                    <div class="job-listing-company-logo">
                                        <img src="{{asset('asset/images/company-logo-01.png')}}" alt="">
                                    </div>

                                    <!-- Details -->
                                    <div class="job-listing-description">
                                        <h3 class="job-listing-title">{{$proforma->poster?->role == 'business_owner' ? 'From Business Owner' : 'From Garage'}}</h3>

                                        <!-- Job Listing Footer -->
                                        <div class="job-listing-footer">
                                            <ul>
                                                @if($proforma->parts->isNotEmpty())
                                                    @php
                                                        $firstPart = $proforma->parts->first();
                                                    @endphp
                                                    <li>
                                                        <i class="icon-material-outline-build"></i>
                                                        {{ $firstPart->pivot->grade }} | {{ $firstPart->pivot->condition }}
                                                    </li>
                                                @endif
                                                <li><i class="icon-material-outline-directions-car"></i> {{$proforma->brand->name}}, {{$proforma->model}} </li>
                                                <li><i class="icon-material-outline-settings"></i> {{$proforma->remaining_shops}} Remaining </li>
                                                <li><i class="icon-material-outline-access-time"></i>{{$proforma->created_at->diffForHumans()}}</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Bookmark -->
                                    <span class="list-apply-button radius ripple-effect">Apply Now</span>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-12">
                        <!-- Pagination -->
                        <div class="pagination-container">
                            {{$proformas->links()}} <!-- Laravel pagination links -->
                        </div>
                    </div>
                </div>
                <!-- Pagination / End -->

            </div>
        </div>
    </div>

</div>
