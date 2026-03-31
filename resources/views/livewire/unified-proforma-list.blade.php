<div>
    <!-- Filters Section -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Filters</h5>
            <div class="row g-3">
                <!-- Proforma Type Filter -->
                <div class="col-md-3">
                    <label for="proformaType" class="form-label">Proforma Type</label>
                    <select wire:model.live="proformaType" class="form-select" id="proformaType">
                        <option value="both">Both (Default)</option>
                        <option value="insurance">Insurance</option>
                        <option value="others">Others</option>
                    </select>
                </div>
                
                <!-- License Plate Filter -->
                <div class="col-md-3">
                    <label for="licenseNumber" class="form-label">License Plate</label>
                    <input wire:model.live.debounce.300ms="licenseNumber" type="text" class="form-control" id="licenseNumber" placeholder="Enter license plate">
                </div>
                
                <!-- Components Filter -->
                <div class="col-md-3">
                    <label for="selectedComponents" class="form-label">Components</label>
                    <select wire:model.live="selectedComponents" class="form-select" id="selectedComponents" multiple>
                        @foreach($components as $component)
                            <option value="{{ $component->condition }}" {{ $component->condition == 'Body Parts' ? 'selected' : '' }}>{{ $component->condition }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- All Parts Filter -->
                <div class="col-md-3">
                    <label for="allParts" class="form-label">All Available Parts</label>
                    <select class="form-select" id="allParts" multiple>
                        @foreach($allParts as $part)
                            <option value="{{ $part->name }}">{{ $part->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Additional Filters -->
                <div class="col-md-3">
                    <label for="selectedGrades" class="form-label">Grades</label>
                    <select wire:model.live="selectedGrades" class="form-select" id="selectedGrades" multiple>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->grade }}">{{ $grade->grade }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="selectedBrands" class="form-label">Brands</label>
                    <select wire:model.live="selectedBrands" class="form-select" id="selectedBrands" multiple>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="selectedInsurances" class="form-label">Insurances</label>
                    <select wire:model.live="selectedInsurances" class="form-select" id="selectedInsurances" multiple>
                        @foreach($insurances as $insurance)
                            <option value="{{ $insurance->id }}">{{ $insurance->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="chasisNumber" class="form-label">Chassis Number</label>
                    <input wire:model.live.debounce.300ms="chasisNumber" type="text" class="form-control" id="chasisNumber" placeholder="Enter chassis number">
                </div>
                
                <div class="col-md-3">
                    <label for="fileNumber" class="form-label">File Number</label>
                    <input wire:model.live.debounce.300ms="fileNumber" type="text" class="form-control" id="fileNumber" placeholder="Enter file number">
                </div>
            </div>
        </div>
    </div>

    <!-- Proformas List -->
    <div class="row">
        @forelse($proformas as $proforma)
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="card-title mb-1">{{ $proforma->file_number }}</h6>
                                <small class="text-muted">{{ $proforma->poster->name ?? 'Unknown' }}</small>
                            </div>
                            <span class="badge 
                                @if($proforma->poster && $proforma->poster->role === 'insurance') bg-primary
                                @else bg-secondary
                                @endif">
                                @if($proforma->poster && $proforma->poster->role === 'insurance')
                                    Insurance
                                @else
                                    Others
                                @endif
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <p class="mb-1"><strong>Customer:</strong> {{ $proforma->customer_name }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $proforma->customer_phone_number }}</p>
                            <p class="mb-1"><strong>License:</strong> {{ $proforma->license_plate_number }}</p>
                            <p class="mb-1"><strong>Chassis:</strong> {{ $proforma->chassis_number }}</p>
                            <p class="mb-1"><strong>Brand:</strong> {{ $proforma->brand->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Model:</strong> {{ $proforma->model }}</p>
                            <p class="mb-1"><strong>Year:</strong> {{ $proforma->year }}</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="mb-2">Required Parts ({{ $proforma->parts->count() }})</h6>
                            <div class="row">
                                @foreach($proforma->parts->take(3) as $part)
                                    <div class="col-12 mb-1">
                                        <small class="text-muted">
                                            • {{ $part->name }} 
                                            <span class="badge bg-light text-dark">{{ $part->pivot->grade }}</span>
                                            <span class="badge bg-light text-dark">{{ $part->pivot->condition }}</span>
                                        </small>
                                    </div>
                                @endforeach
                                @if($proforma->parts->count() > 3)
                                    <div class="col-12">
                                        <small class="text-muted">+ {{ $proforma->parts->count() - 3 }} more parts</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ $proforma->created_at->format('M d, Y') }}</small>
                            <a href="{{ route('spare-part.proforma-details', ['proforma' => $proforma->id]) }}" class="btn btn-primary btn-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bx bx-search-alt-2 font-50 text-muted mb-3"></i>
                        <h5 class="text-muted">No proformas found</h5>
                        <p class="text-muted">Try adjusting your search criteria</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $proformas->links() }}
    </div>
</div>
