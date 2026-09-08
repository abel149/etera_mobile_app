@extends('layouts.insurance')

@section('content')

<h3 class="">Request Proforma</h3>
@if(session('success'))
    <div class="alert alert-success border-0 bg-success alert-dismissible fade show py-2">
        <div class="d-flex align-items-center">
            <div class="font-35 text-white"><i class='bx bxs-check-circle'></i>
            </div>
            <div class="ms-3">
                <h6 class="mb-0 text-white">Success</h6>
                <div class="text-white">{{ session('success') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2">
        <div class="d-flex align-items-center">
            <div class="font-35 text-white"><i class='bx bxs-message-square-x'></i>
            </div>
            <div class="ms-3">
                <h6 class="mb-0 text-white">Error</h6>
                <div class="text-white">{{ session('error') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@foreach ($errors->all() as $error) <p class="text-danger">{{ $error }}</p> @endforeach

<div class="row">
    <div class="col-12">
        <!--start stepper three--> 
        <div class="card">
            <div class="card-body">
                <div id="stepper3" class="bs-stepper gap-4 vertical">
                    <div class="border-right pr-2" role="tablist">
                        <!-- Stepper steps remain the same -->
                        <div class="step" data-target="#test-vl-1">
                            <div class="step-trigger" role="tab" id="stepper3trigger1" aria-controls="test-vl-1">
                                <div class="bs-stepper-circle">1</div>
                                <div class="">
                                    <h5 class="mb-0 steper-title">Basic Information</h5>
                                    <p class="mb-0 steper-sub-title">1st Step</p>
                                </div>
                            </div>
                        </div>
                        <div class="step" data-target="#test-vl-2">
                            <div class="step-trigger" role="tab" id="stepper3trigger2" aria-controls="test-vl-2">
                                <div class="bs-stepper-circle">2</div>
                                <div class="">
                                    <h5 class="mb-0 steper-title">Car Specification</h5>
                                    <p class="mb-0 steper-sub-title">2nd Step</p>
                                </div>
                            </div>
                        </div>
                        <div class="step" data-target="#test-vl-3">
                            <div class="step-trigger" role="tab" id="stepper3trigger3" aria-controls="test-vl-3">
                                <div class="bs-stepper-circle">3</div>
                                <div class="">
                                    <h5 class="mb-0 steper-title">Required Spare Parts</h5>
                                    <p class="mb-0 steper-sub-title">3rd Step</p>
                                </div>
                            </div>
                        </div>
                        <div class="step" data-target="#test-vl-4">
                            <div class="step-trigger" role="tab" id="stepper3trigger4" aria-controls="test-vl-4">
                                <div class="bs-stepper-circle">4</div>
                                <div class="">
                                    <h5 class="mb-0 steper-title">Information for Garage</h5>
                                    <p class="mb-0 steper-sub-title">4th Step</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bs-stepper-content">
                        <form id="insuranceProformaForm" action="{{ route('insurance.create-file') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Step 1: Basic Information -->
                            <div id="test-vl-1" role="tabpane3" class="bs-stepper-pane content fade" aria-labelledby="stepper3trigger1">
                                <h5 class="mb-1">Basic Information</h5>
                                <p class="mb-4">Enter the basic proforma request</p>

                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <label for="FisrtName" class="form-label">Claim Number</label>
                                        <input type="text" name="file_number" value="{{old('file_number')}}" class="form-control required-field" id="FisrtName" 
                                        placeholder="" 
                                        required
                                        oninvalid="this.setCustomValidity('Please enter the file number')"
                                        oninput="this.setCustomValidity('')">
                                        @error('file_number')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    
                                    <!-- Add Is Insured Checkbox here -->
                                    <div class="col-12 col-lg-6">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" name="insured" id="insured" value="1" {{ old('insured', '1') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="insured">
                                                Is Insured
                                            </label>
                                        </div>
                                        @error('insured')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12 col-lg-6">
                                        <label for="car_type" class="form-label">Car Type</label>
                                        <select name="car_type" id="car_type" class="form-select" required>
                                            <option value="Sedan/S.U.V(GAS)" {{ old('car_type', 'Sedan/S.U.V(GAS)') == 'Sedan/S.U.V(GAS)' ? 'selected' : '' }}>Sedan/S.U.V(GAS)</option>
                                            <option value="Sedan/S.U.V(EV)" {{ old('car_type') == 'Sedan/S.U.V(EV)' ? 'selected' : '' }}>Sedan/S.U.V(EV)</option>
                                            <option value="Mini Van(GAS)" {{ old('car_type') == 'Mini Van(GAS)' ? 'selected' : '' }}>Mini Van(GAS)</option>
                                            <option value="Mini Van(EV)" {{ old('car_type') == 'Mini Van(EV)' ? 'selected' : '' }}>Mini Van(EV)</option>
                                            <option value="Isuzu/Bus(GAS)" {{ old('car_type') == 'Isuzu/Bus(GAS)' ? 'selected' : '' }}>Isuzu/Bus(GAS)</option>
                                            <option value="Isuzu/Bus(EV)" {{ old('car_type') == 'Isuzu/Bus(EV)' ? 'selected' : '' }}>Isuzu/Bus(EV)</option>
                                            <option value="Heavy" {{ old('car_type') == 'Heavy' ? 'selected' : '' }}>Heavy Duty</option>
                                        </select>

                                        @error('car_type')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-lg-6">
                                        <label class="form-label">Damage Severity (Optional)</label>
                                        <div class="d-flex gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="damage_severity" id="damage_minor" value="minor" {{ old('damage_severity') == 'minor' ? 'checked' : '' }} style="accent-color: #0d6efd; width: 1.2em; height: 1.2em;">
                                                <label class="form-check-label" for="damage_minor">
                                                    Minor (Remote Fill) - ቀላል
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="damage_severity" id="damage_major" value="major" {{ old('damage_severity') == 'major' ? 'checked' : '' }} style="accent-color: #0d6efd; width: 1.2em; height: 1.2em;">
                                                <label class="form-check-label" for="damage_major">
                                                    Major (Garage Required) - ከባድ
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="damage_severity" id="damage_severe" value="severe" {{ old('damage_severity') == 'severe' ? 'checked' : '' }} style="accent-color: #0d6efd; width: 1.2em; height: 1.2em;">
                                                <label class="form-check-label" for="damage_severe">
                                                    Severe (Site Visit) - ከፍተኛ
                                                </label>
                                            </div>
                                        </div>
                                        @error('damage_severity')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                @php
                                    use App\Models\Brand;
                                    use App\Models\CarPart;
                                    $userIsTest = auth()->user()?->is_test ?? false;
                                    $brands = Brand::where('is_test', $userIsTest)
                                    ->orderBy('name', 'asc')
                                    ->get();
                                    $parts = CarPart::orderBy('name', 'asc')->get();
                                @endphp
                             

                                    
                                    <div class="col-12 col-lg-6">
                                        <label for="InputCountry" class="form-label">Brand</label>
                                        <select class="form-select" name="brand_id" value="old('brand_id')" required id="InputCountry" aria-label="Default select example">
                                            @foreach($brands as $brand)    
                                                <option value="{{$brand->id}}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{$brand->name}}</option>
                                            @endforeach
                                        </select>
                                        @error('brand_id')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="PhoneNumber" class="form-label">Model</label>
                                        <input type="text" name="model"  value="{{old('model')}}" class="form-control required-field" id="PhoneNumber" placeholder="example: yarris"
                                         required
                                        oninvalid="this.setCustomValidity('Please enter the model')"
                                        oninput="this.setCustomValidity('')"
                                        >
                                        @error('model')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="InputCountry" class="form-label">Year</label>
                                        <select class="form-select" name="year" value="{{old('year')}}" id="InputCountry" aria-label="Default select example">
                                            <option value="#N/A" {{ old('year') == '#N/A' ? 'selected' : '' }}>#N/A</option>
                                            @for($i = 1990; $i <= date('Y'); $i++)
                                                <option value="{{$i}}" {{ old('year') == $i ? 'selected' : '' }}>{{$i}}</option>
                                            @endfor
                                        </select>
                                        @error('year')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    {{-- ── Proforma Type Selector ─────────────────────────────── --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Proforma Type</label>
                                        <div class="d-flex flex-wrap gap-3" id="proformaTypeOptions">
                                            {{-- Standard type hidden for now — existing insurance_standard proformas still work via the route fallback --}}
                                            <div class="proforma-type-card d-none" data-type="insurance_standard">
                                                <input class="form-check-input" type="radio" name="proforma_type" id="typeStandard" value="insurance_standard" {{ old('proforma_type') == 'insurance_standard' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="typeStandard">
                                                    <i class="bx bx-buildings me-1"></i> Standard
                                                    <small class="text-muted d-block">Shops + Garages</small>
                                                </label>
                                            </div>
                                            <div class="proforma-type-card active" data-type="insurance_shop_only">
                                                <input class="form-check-input" type="radio" name="proforma_type" id="typeShopOnly" value="insurance_shop_only" {{ old('proforma_type', 'insurance_shop_only') == 'insurance_shop_only' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="typeShopOnly">
                                                    <i class="bx bx-store me-1"></i> Shop Only
                                                    <small class="text-muted d-block">Spare Part Shops</small>
                                                </label>
                                            </div>
                                            <div class="proforma-type-card" data-type="insurance_garage_only">
                                                <input class="form-check-input" type="radio" name="proforma_type" id="typeGarageOnly" value="insurance_garage_only" {{ old('proforma_type') == 'insurance_garage_only' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="typeGarageOnly">
                                                    <i class="bx bx-wrench me-1"></i> Garage Only
                                                    <small class="text-muted d-block">Repair Garages</small>
                                                </label>
                                            </div>
                                            <div class="proforma-type-card" data-type="insurance_shop_garage">
                                                <input class="form-check-input" type="radio" name="proforma_type" id="typeShopGarage" value="insurance_shop_garage" {{ old('proforma_type') == 'insurance_shop_garage' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="typeShopGarage">
                                                    <i class="bx bx-building-house me-1"></i> Shop + Garage
                                                    <small class="text-muted d-block">Dual Service Providers</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Number of Shops (Standard / Shop Only) --}}
                                    <div class="col-12 col-lg-6" id="numberOfShopsWrapper">
                                        <label for="number_of_proformas" class="form-label">Number of Required Shops</label>
                                        <select name="number_of_proformas" id="number_of_proformas" class="form-select">
                                            <option value="1" {{ old('number_of_proformas') == '1' ? 'selected' : '' }}>1 Shop</option>
                                            <option value="2" {{ old('number_of_proformas') == '2' ? 'selected' : '' }}>2 Shops</option>
                                            <option value="3" {{ old('number_of_proformas', '3') == '3' ? 'selected' : '' }}>3 Shops</option>
                                            <option value="4" {{ old('number_of_proformas') == '4' ? 'selected' : '' }}>4 Shops</option>
                                            <option value="5" {{ old('number_of_proformas') == '5' ? 'selected' : '' }}>5 Shops</option>
                                        </select>
                                    </div>

                                    {{-- Number of Garages (Garage Only) --}}
                                    <div class="col-12 col-lg-6" id="numberOfGaragesWrapper" style="display:none;">
                                        <label for="number_of_garages" class="form-label">Number of Required Garages</label>
                                        <select name="number_of_garages" id="number_of_garages" class="form-select">
                                            <option value="1" {{ old('number_of_garages') == '1' ? 'selected' : '' }}>1 Garage</option>
                                            <option value="2" {{ old('number_of_garages') == '2' ? 'selected' : '' }}>2 Garages</option>
                                            <option value="3" {{ old('number_of_garages', '3') == '3' ? 'selected' : '' }}>3 Garages</option>
                                            <option value="4" {{ old('number_of_garages') == '4' ? 'selected' : '' }}>4 Garages</option>
                                            <option value="5" {{ old('number_of_garages') == '5' ? 'selected' : '' }}>5 Garages</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-lg-6">
                                        <button type="button" class="btn btn-primary btn-next px-4 rounded-pill">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Car Information -->
                            <div id="test-vl-2" role="tabpane3" class="bs-stepper-pane content fade" aria-labelledby="stepper3trigger2">
                                <h5 class="mb-1">Car Information</h5>
                                <p class="mb-4">Enter the car details</p>

                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <label for="InputUsername" class="form-label">Owner Name</label>
                                        <input type="text" name="customer_name" value="{{old('customer_name')}}" class="form-control required-field" id="InputUsername" placeholder="Customer Name"
                                        required
                                        oninvalid="this.setCustomValidity('Please enter the Owner Name')"
                                        oninput="this.setCustomValidity('')"
                                        >
                                        @error('customer_name')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="InputEmail2" class="form-label">Customer Phone Number</label>
                                        <input type="text" name="customer_phone_number" value="{{old('customer_phone_number')}}" class="form-control required-field" id="InputEmail2" placeholder=""
                                        required
                                        oninvalid="this.setCustomValidity('Please enter the Phone number')"
                                        oninput="this.setCustomValidity('')"
                                        >
                                        @error('customer_phone_number')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div> <div class="col-12 col-lg-6">
                                        <label for="InputEmail2" class="form-label">operator Phone Number</label>
                                        <input type="text" name="Agent_phone_number" value="{{old('Agent_phone_number')}}" class="form-control required-field" id="InputEmail2" placeholder=""
                                        required
                                        oninvalid="this.setCustomValidity('Please enter the Phone number')"
                                        oninput="this.setCustomValidity('')"
                                        >
                                        @error('Agent_phone_number')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>                                
                          
                                    
                                    <div class="col-12 col-lg-6">
<label class="form-label">
    Chassis Number
    
</label>

<div class="vin-single-wrapper">
    <input type="text"
       class="form-control text-uppercase required-field"
       id="vin_input"
       name="chassis_number"
       maxlength="17"
       placeholder="Enter Chassis Number"
       value="{{ old('chassis_number') }}"
       required
       oninvalid="this.setCustomValidity('Please enter a valid 17-character chassis number')"
       oninput="this.setCustomValidity('')">

    <span class="vin-counter" id="vin_counter"></span>
</div>

@error('chassis_number')
    <span class="text-danger">{{ $message }}</span>
@enderror

</div>

                                    <div class="col-12 col-lg-6">
                                        <label for="license_plate_input" class="form-label">License Plate Number</label>
                                        <input type="text"
                                               name="license_plate_number"
                                               id="license_plate_input"
                                               class="form-control required-field text-uppercase"
                                               placeholder="Example: 2AA-12345"
                                               value="{{ old('license_plate_number') }}"
                                               required
                                                oninvalid="this.setCustomValidity('Please enter the License Plate Number')"
                                                oninput="this.setCustomValidity('')"
                                               >
                                        @error('license_plate_number')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-lg-6">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" name="call_customer" id="call_customer" value="1" {{ old('call_customer') ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="call_customer">
                                                <i class="bx bx-phone-call me-1"></i> Call Customer
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="stepper3.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Previous</button>
                                            <button type="button" class="btn btn-primary btn-next rounded-pill px-4">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Spare Parts -->
                            <div id="test-vl-3" role="tabpane3" class="bs-stepper-pane content fade" aria-labelledby="stepper3trigger3">

                                {{-- ── Excel Import Panel ─────────────────────────────────── --}}
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div>
                                        <h5 class="mb-0">Spare Parts</h5>
                                        <p class="text-muted small mb-0">Enter parts manually, or <button type="button" id="toggleExcelImport" class="btn btn-link p-0 small text-success fw-semibold" style="vertical-align:baseline;"><i class="bx bx-table me-1"></i>import from Excel</button></p>
                                    </div>
                                </div>

                                <div id="excelImportBox" style="display:none;" class="border rounded-3 p-3 mb-3 mt-2" style="border-color:rgba(20,184,166,0.35)!important; background:rgba(20,184,166,0.04);">
                                    <p class="small text-muted mb-2">
                                        Download the template, fill in your parts, then upload the file to auto-populate the list below.
                                        <strong class="text-dark">Uploaded rows will replace any parts already entered.</strong>
                                    </p>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button type="button" id="downloadExcelTemplate" class="btn btn-outline-secondary btn-sm rounded-pill">
                                            <i class="bx bx-download me-1"></i>Download Template
                                        </button>
                                        <label class="btn btn-success btn-sm rounded-pill mb-0" style="cursor:pointer;">
                                            <i class="bx bx-upload me-1"></i>Upload &amp; Import
                                            <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" style="display:none;">
                                        </label>
                                    </div>
                                    <div id="excelImportStatus" class="mt-2 small"></div>
                                </div>
                                {{-- ── End Excel Import Panel ──────────────────────────────── --}}

                                <div class="repeater-form">
                                    <div id="repeater">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h5 class="mb-1" style="display:none;"></h5>
                                            </div>
                                       </div>

                                        <div class="repeater-item mb-3">
                                            <div class="part-card">
                                            <div class="part-card-header d-flex align-items-center justify-content-between">
                                                <span class="part-label"><b>Spare Part #1</b></span>
                                                <button type="button" class="remove-repeater btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                            </div>
                                            <div class="item-content row g-3 pt-2">
                                                
                                                <div class="col-12 col-lg-4">
                                                    <label for="inputEmail1" class="form-label">Part Name And Part Number</label>
                                                    <input type="text" name="parts[0][number]" class="form-control required-field" id="inputEmail1" required oninvalid="this.setCustomValidity('Please enter the part name and number')" oninput="this.setCustomValidity('')">
                                                    @error('parts.0.number')
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-12 col-lg-3">
                                                    <label for="inputName1" class="form-label">Grade</label>
                                                    <select class="form-select" name="parts[0][grade]" id="InputCountry" aria-label="Default select example" required>
                                                        <option value="1st Grade(Original OEM)">1st Grade(Original OEM)</option>
                                                        <option value="2nd Grade(After market)">2nd Grade(After market)</option>
                                                        <option value="3rd Grade">3rd Grade</option>
                                                        <option value="4th grade (Local)">4th grade (Local)</option>
                                                    </select>
                                                    @error('parts.0.grade')
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-12 col-lg-3">
                                                    <label for="inputName1" class="form-label">Country Part is Manufactured</label>
                                                    <input name="parts[0][country]" type="text" class="form-control required-field" id="inputName1" placeholder="" data-name="name" required oninvalid="this.setCustomValidity('Please enter the country')" oninput="this.setCustomValidity('')">
                                                    @error('parts.0.country')
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-12 col-lg-2">
                                                    <label for="inputName1" class="form-label">Qty</label>
                                                    <input name="parts[0][quantity]" type="number" class="form-control required-field" id="inputName1" placeholder="e.g. 1" data-name="name" required min="1" value="{{ old('parts.0.quantity', 1) }}" oninvalid="this.setCustomValidity('Quantity must be at least 1')" oninput="this.setCustomValidity('')">
                                                    @error('parts.0.quantity')
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <!-- Condition -->
                                                <div class="col-12 col-lg-2">
                                                    <label for="condition" class="form-label">Condition</label>
                                                    <select name="parts[0][condition]" id="condition" class="form-select" required>
                                                        <option value="New" selected>New</option>
                                                        <option value="Used" disabled>Used</option>
                                                    </select>
                                                    @error('parts.0.condition')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <!-- Component -->
                                                <div class="col-12 col-lg-2">
                                                    <label for="component" class="form-label">Component</label>
                                                    <select name="parts[0][component]" id="component" class="form-select required-field" required oninvalid="this.setCustomValidity('Please select a component')" onchange="this.setCustomValidity('')">
                                                        <option value="">Select Component</option>
                                                        <option value="Body Parts">Body Parts</option>
                                                        <option value="Mechanical Parts">Mechanical Parts</option>
                                                    </select>
                                                    @error('parts.0.component')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <!-- Repair / Renew -->
                                                <div class="col-12 col-lg-2">
                                                    <label class="form-label">Service</label>
                                                    <select name="parts[0][repair_renew]" class="form-select">
                                                        <option value="">— Select —</option>
                                                        <option value="renew" {{ old('parts.0.repair_renew') == 'renew' ? 'selected' : '' }}>Renew</option>
                                                        <option value="repair" {{ old('parts.0.repair_renew') == 'repair' ? 'selected' : '' }}>Repair</option>
                                                    </select>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        <button type="button" id="add-repeater" class="btn btn-primary repeater-add-btn px-4">Add another part</button>
                                    </div>
                                </div>

                                <br>
                                <p class="mb-2">Send to inbox <span class="text-secondary small">(optional — each row is a separate group; when one applies the others in that row are removed)</span></p>

                                {{-- ── SHOPS ─────────────────────────────────────────────── --}}
                                <div id="shopGroupsWrapper">
                                <h6 class="mt-3 mb-2 text-muted fw-semibold">Shops</h6>

                                {{-- Group 1: All shops (same as other slots) --}}
                                <div class="mb-3 shop-inbox-group" data-group="1">
                                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                        <label for="shopPartners" class="form-label mb-0">Shops — Slot 1 <span class="text-secondary small">(all shops)</span></label>
                                        <select class="form-select radius-30 brand-filter-select" data-target="shopPartners" style="display:none; width:auto; min-width:180px;">
                                            <option value="">All Brands</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <select class="form-select shop-select" name="spare_part_partners[]" id="shopPartners" multiple size="4">
                                        @foreach($all_shops as $shop)
                                            @php $brandIds = $shop->brands->pluck('id')->implode(','); @endphp
                                            <option value="{{ $shop->id }}" data-role="shop" data-shop-garage="{{ $shop->shop_garage ?? 0 }}" data-brand-ids="{{ $brandIds }}" {{ in_array($shop->id, old('spare_part_partners', [])) ? 'selected' : '' }}>{{ $shop->store_id }} — {{ $shop->name }}</option>
                                        @endforeach
                                        @foreach($all_garages as $garage)
                                            @if($garage->shop_garage == 1)
                                                <option value="{{ $garage->id }}" data-role="garage" data-shop-garage="{{ $garage->shop_garage ?? 0 }}" data-brand-ids="" {{ in_array($garage->id, old('spare_part_partners', [])) ? 'selected' : '' }}>{{ $garage->store_id }} — {{ $garage->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Group 2 --}}
                                <div class="mb-3 shop-inbox-group" data-group="2">
                                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                        <label for="shopExtra1" class="form-label mb-0">Additional Shops — Slot 2 <span class="text-secondary small">(all shops)</span></label>
                                        <select class="form-select radius-30 brand-filter-select" data-target="shopExtra1" style="display:none; width:auto; min-width:180px;">
                                            <option value="">All Brands</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <select class="form-select shop-select" name="insurance_shop_extra1[]" id="shopExtra1" multiple size="4">
                                        @foreach($all_shops as $shop)
                                            @php $brandIds = $shop->brands->pluck('id')->implode(','); @endphp
                                            <option value="{{ $shop->id }}" data-role="shop" data-shop-garage="{{ $shop->shop_garage ?? 0 }}" data-brand-ids="{{ $brandIds }}" {{ in_array($shop->id, old('insurance_shop_extra1', [])) ? 'selected' : '' }}>{{ $shop->store_id }} — {{ $shop->name }}</option>
                                        @endforeach
                                        @foreach($all_garages as $garage)
                                            @if($garage->shop_garage == 1)
                                                <option value="{{ $garage->id }}" data-role="garage" data-shop-garage="{{ $garage->shop_garage ?? 0 }}" data-brand-ids="" {{ in_array($garage->id, old('insurance_shop_extra1', [])) ? 'selected' : '' }}>{{ $garage->store_id }} — {{ $garage->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Group 3 --}}
                                <div class="mb-3 shop-inbox-group" data-group="3">
                                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                        <label for="shopExtra2" class="form-label mb-0">Additional Shops — Slot 3 <span class="text-secondary small">(all shops)</span></label>
                                        <select class="form-select radius-30 brand-filter-select" data-target="shopExtra2" style="display:none; width:auto; min-width:180px;">
                                            <option value="">All Brands</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <select class="form-select shop-select" name="insurance_shop_extra2[]" id="shopExtra2" multiple size="4">
                                        @foreach($all_shops as $shop)
                                            @php $brandIds = $shop->brands->pluck('id')->implode(','); @endphp
                                            <option value="{{ $shop->id }}" data-role="shop" data-shop-garage="{{ $shop->shop_garage ?? 0 }}" data-brand-ids="{{ $brandIds }}" {{ in_array($shop->id, old('insurance_shop_extra2', [])) ? 'selected' : '' }}>{{ $shop->store_id }} — {{ $shop->name }}</option>
                                        @endforeach
                                        @foreach($all_garages as $garage)
                                            @if($garage->shop_garage == 1)
                                                <option value="{{ $garage->id }}" data-role="garage" data-shop-garage="{{ $garage->shop_garage ?? 0 }}" data-brand-ids="" {{ in_array($garage->id, old('insurance_shop_extra2', [])) ? 'selected' : '' }}>{{ $garage->store_id }} — {{ $garage->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Group 4 (shown when required ≥ 4) --}}
                                <div class="mb-3 shop-inbox-group" data-group="4" style="display:none">
                                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                        <label for="shopExtra3" class="form-label mb-0">Additional Shops — Slot 4 <span class="text-secondary small">(all shops)</span></label>
                                        <select class="form-select radius-30 brand-filter-select" data-target="shopExtra3" style="display:none; width:auto; min-width:180px;">
                                            <option value="">All Brands</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <select class="form-select shop-select" name="insurance_shop_extra3[]" id="shopExtra3" multiple size="4">
                                        @foreach($all_shops as $shop)
                                            @php $brandIds = $shop->brands->pluck('id')->implode(','); @endphp
                                            <option value="{{ $shop->id }}" data-role="shop" data-shop-garage="{{ $shop->shop_garage ?? 0 }}" data-brand-ids="{{ $brandIds }}" {{ in_array($shop->id, old('insurance_shop_extra3', [])) ? 'selected' : '' }}>{{ $shop->store_id }} — {{ $shop->name }}</option>
                                        @endforeach
                                        @foreach($all_garages as $garage)
                                            @if($garage->shop_garage == 1)
                                                <option value="{{ $garage->id }}" data-role="garage" data-shop-garage="{{ $garage->shop_garage ?? 0 }}" data-brand-ids="" {{ in_array($garage->id, old('insurance_shop_extra3', [])) ? 'selected' : '' }}>{{ $garage->store_id }} — {{ $garage->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Group 5 (shown when required = 5) --}}
                                <div class="mb-3 shop-inbox-group" data-group="5" style="display:none">
                                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                        <label for="shopExtra4" class="form-label mb-0">Additional Shops — Slot 5 <span class="text-secondary small">(all shops)</span></label>
                                        <select class="form-select radius-30 brand-filter-select" data-target="shopExtra4" style="display:none; width:auto; min-width:180px;">
                                            <option value="">All Brands</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <select class="form-select shop-select" name="insurance_shop_extra4[]" id="shopExtra4" multiple size="4">
                                        @foreach($all_shops as $shop)
                                            @php $brandIds = $shop->brands->pluck('id')->implode(','); @endphp
                                            <option value="{{ $shop->id }}" data-role="shop" data-shop-garage="{{ $shop->shop_garage ?? 0 }}" data-brand-ids="{{ $brandIds }}" {{ in_array($shop->id, old('insurance_shop_extra4', [])) ? 'selected' : '' }}>{{ $shop->store_id }} — {{ $shop->name }}</option>
                                        @endforeach
                                        @foreach($all_garages as $garage)
                                            @if($garage->shop_garage == 1)
                                                <option value="{{ $garage->id }}" data-role="garage" data-shop-garage="{{ $garage->shop_garage ?? 0 }}" data-brand-ids="" {{ in_array($garage->id, old('insurance_shop_extra4', [])) ? 'selected' : '' }}>{{ $garage->store_id }} — {{ $garage->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                </div>{{-- end #shopGroupsWrapper --}}

                                {{-- ── GARAGES ──────────────────────────────────────────── --}}
                                <div id="garageGroupsWrapper">
                                <h6 class="mt-3 mb-2 text-muted fw-semibold">Garages</h6>

                                {{-- Group 1: All garages (same as other slots) --}}
                                <div class="mb-3 garage-inbox-group" data-group="1">
                                    <label for="garagePartners" class="form-label">Garages — Slot 1 <span class="text-secondary small">(all garages)</span></label>
                                    <select class="form-select" name="garage_partners[]" id="garagePartners" multiple size="4">
                                        @foreach($all_garages as $garage)
                                            <option value="{{ $garage->id }}" {{ in_array($garage->id, old('garage_partners', [])) ? 'selected' : '' }}>{{ $garage->store_id }} — {{ $garage->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Group 2 --}}
                                <div class="mb-3 garage-inbox-group" data-group="2">
                                    <label for="garageExtra1" class="form-label">Additional Garages — Slot 2 <span class="text-secondary small">(all garages)</span></label>
                                    <select class="form-select" name="insurance_garage_extra1[]" id="garageExtra1" multiple size="4">
                                        @foreach($all_garages as $garage)
                                            <option value="{{ $garage->id }}" {{ in_array($garage->id, old('insurance_garage_extra1', [])) ? 'selected' : '' }}>{{ $garage->store_id }} — {{ $garage->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Group 3 --}}
                                <div class="mb-3 garage-inbox-group" data-group="3">
                                    <label for="garageExtra2" class="form-label">Additional Garages — Slot 3 <span class="text-secondary small">(all garages)</span></label>
                                    <select class="form-select" name="insurance_garage_extra2[]" id="garageExtra2" multiple size="4">
                                        @foreach($all_garages as $garage)
                                            <option value="{{ $garage->id }}" {{ in_array($garage->id, old('insurance_garage_extra2', [])) ? 'selected' : '' }}>{{ $garage->store_id }} — {{ $garage->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Group 4 (shown when required ≥ 4) --}}
                                <div class="mb-3 garage-inbox-group" data-group="4" style="display:none">
                                    <label for="garageExtra3" class="form-label">Additional Garages — Slot 4 <span class="text-secondary small">(all garages)</span></label>
                                    <select class="form-select" name="insurance_garage_extra3[]" id="garageExtra3" multiple size="4">
                                        @foreach($all_garages as $garage)
                                            <option value="{{ $garage->id }}" {{ in_array($garage->id, old('insurance_garage_extra3', [])) ? 'selected' : '' }}>{{ $garage->store_id }} — {{ $garage->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Group 5 (shown when required = 5) --}}
                                <div class="mb-3 garage-inbox-group" data-group="5" style="display:none">
                                    <label for="garageExtra4" class="form-label">Additional Garages — Slot 5 <span class="text-secondary small">(all garages)</span></label>
                                    <select class="form-select" name="insurance_garage_extra4[]" id="garageExtra4" multiple size="4">
                                        @foreach($all_garages as $garage)
                                            <option value="{{ $garage->id }}" {{ in_array($garage->id, old('insurance_garage_extra4', [])) ? 'selected' : '' }}>{{ $garage->store_id }} — {{ $garage->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                </div>{{-- end #garageGroupsWrapper --}}

                                <div class="col-12 pt-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="stepper3.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Previous</button>
                                        <button type="button" class="btn btn-primary btn-next rounded-pill px-4">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4: Information for Garage -->
                            <div id="test-vl-4" role="tabpane3" class="bs-stepper-pane content fade" aria-labelledby="stepper3trigger4">
                                <h5 class="mb-1">Information for Garage (Optional)</h5>
                                <p class="mb-4">Upload car information media files</p>

                                <div class="row g-3">
                                    <div class="col-12 col-lg-4">
                                        <label for="inputProductDescription" class="form-label">Images</label>
                                        <input type="file" id="image" name="image[]" accept="image/*,.jpg,.png,.jpeg" multiple>

                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label for="inputProductDescription" class="form-label">Video</label>
                                        <input type="file" id="video" name="video" accept="video/*,.mp4">
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label for="inputProductDescription" class="form-label">Audio</label>
                                        <input type="file" id="audio" name="audio" accept="audio/*,.mp3">
                                    </div>

                                    <!-- Voice Note Section -->
                                    <div class="col-12 mt-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Voice Note (Optional)</h5>
                                                <p class="card-text">Record a voice note to provide additional information about your request.</p>
                                                
                                                <div class="voice-recorder-container">
                                                    <div class="d-flex align-items-center gap-3 mb-3">
                                                        <button type="button" id="startRecording" class="btn btn-primary rounded-pill px-4">
                                                            <i class="bx bx-microphone me-2"></i>Start Recording
                                                        </button>
                                                        <button type="button" id="stopRecording" class="btn btn-secondary rounded-pill px-4" disabled>
                                                            <i class="bx bx-stop-circle me-2"></i>Stop Recording
                                                        </button>
                                                    </div>
                                                    
                                                    <div id="recordingStatus" class="mb-3" style="display: none;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="recording-indicator"></div>
                                                            <span class="ms-2 recording-active">Recording in progress...<br>Once finished recording, please press the stop button before submitting!</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div id="audioPreview" class="mb-3" style="display: none;">
                                                        <audio id="recordedAudio" controls class="mb-2"></audio>
                                                        <button type="button" id="deleteRecording" class="btn btn-danger rounded-pill px-4 ms-3">
                                                            <i class="bx bx-trash me-2"></i>Delete Recording
                                                        </button>
                                                    </div>
                                                    
                                                    <input type="hidden" name="voice_note" id="voiceNoteInput">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="stepper3.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Previous</button>
                                            <button type="submit" id="proformaSubmitBtn" class="btn btn-success rounded-pill px-4">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Proforma Type Cards */
.proforma-type-card {
    display: flex;
    flex-direction: column;
    border: 2px solid #dee2e6;
    border-radius: 12px;
    padding: 14px 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 140px;
    background: #fff;
}
.proforma-type-card:hover {
    border-color: #0d6efd;
    background: #f0f6ff;
}
.proforma-type-card.active {
    border-color: #0d6efd;
    background: #e8f1ff;
}
.proforma-type-card .form-check-input {
    display: none;
}
.proforma-type-card .form-check-label {
    cursor: pointer;
    font-weight: 600;
    font-size: 0.95rem;
}

    .recording-indicator {
        width: 10px;
        height: 10px;
        background-color: #dc3545;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.2);
            opacity: 0.5;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .voice-recorder-container {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }

    #recordedAudio {
        width: 100%;
        max-width: 400px;
        margin-bottom: 10px;
    }

    .recording-active {
        color: #dc3545;
        font-weight: bold;
    }

    #startRecording:disabled,
    #stopRecording:disabled {
        background-color: #6c757d;
        cursor: not-allowed;
    }

.vin-wrapper {
    display: grid;
    grid-template-columns: repeat(17, 1fr);
    gap: 6px;
}

.vin-box {
    aspect-ratio: 1 / 1;
    font-size: 1rem;
    padding: 0;
}
.vin-single-wrapper {
    position: relative;
    max-width: 420px;
}

.vin-counter {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    color: #6c757d;
    pointer-events: none;
}
.repeater-add-btn{
    z-index: 10;
    position: relative;
    margin-top: 8px;
}
.part-card {
    border: 1px solid #dee2e6;
    border-radius: 10px;
    overflow: hidden;
}
.part-card-header {
    background-color: #f8f9fa;
    padding: 10px 16px;
    border-bottom: 1px solid #dee2e6;
}
.part-label {
    font-size: 15px;
    color: #344767;
}
.part-card .item-content {
    padding: 16px;
}
#vin_input {
    padding-right: 70px;
    letter-spacing: 2px;
}

/* Tablet */
@media (max-width: 992px) {
    .vin-wrapper {
        grid-template-columns: repeat(9, 1fr);
    }
}

/* Mobile */
@media (max-width: 576px) {
    .vin-wrapper {
        grid-template-columns: repeat(6, 1fr);
    }
}


</style>

<script>
var stepper3;
document.addEventListener('DOMContentLoaded', () => {

    // ── Proforma Type Selector ──────────────────────────────────────────────
    const typeCards = document.querySelectorAll('.proforma-type-card');
    const numberOfShopsWrapper   = document.getElementById('numberOfShopsWrapper');
    const numberOfGaragesWrapper = document.getElementById('numberOfGaragesWrapper');
    const shopGroupsWrapper      = document.getElementById('shopGroupsWrapper');
    const garageGroupsWrapper    = document.getElementById('garageGroupsWrapper');

    // ── Store all shop-select options for detach/reattach filtering ──────────
    const shopSelects = document.querySelectorAll('.shop-select');
    const allShopOptions = {};
    shopSelects.forEach(sel => {
        allShopOptions[sel.id] = Array.from(sel.querySelectorAll('option')).map(opt => opt.cloneNode(true));
    });

    function filterShopOptions(type) {
        const shopOnly = type === 'insurance_shop_only';
        const dualOnly = type === 'insurance_shop_garage';
        shopSelects.forEach(sel => {
            const opts = allShopOptions[sel.id] || [];
            const selectedIds = Array.from(sel.selectedOptions).map(o => o.value);
            // Get selected brand from the brand dropdown for this slot
            const brandSelect = document.querySelector('.brand-filter-select[data-target="' + sel.id + '"]');
            const selectedBrand = brandSelect ? brandSelect.value : '';
            // Destroy Select2 before modifying DOM (only if already initialized)
            if (window.jQuery && $('#' + sel.id).hasClass('select2-hidden-accessible')) {
                $('#' + sel.id).select2('destroy');
            }
            sel.innerHTML = '';
            opts.forEach(opt => {
                let show;
                if (dualOnly) {
                    show = opt.dataset.shopGarage === '1';
                } else if (shopOnly) {
                    show = opt.dataset.role === 'shop';
                    // If a brand is selected, also filter by brand
                    if (show && selectedBrand) {
                        const brandIds = (opt.dataset.brandIds || '').split(',').filter(Boolean);
                        show = brandIds.includes(selectedBrand);
                    }
                } else {
                    show = opt.dataset.role === 'shop';
                }
                if (show) {
                    const cloned = opt.cloneNode(true);
                    if (selectedIds.includes(cloned.value)) cloned.selected = true;
                    sel.appendChild(cloned);
                }
            });
            // Re-init Select2 (plain, no custom matcher needed)
            if (window.jQuery) {
                $('#' + sel.id).select2({
                    theme:       'bootstrap-5',
                    placeholder: 'Type to search and select...',
                    allowClear:  true,
                    width:       '100%'
                });
            }
        });
        // Show/hide brand dropdowns — only for insurance_shop_only
        document.querySelectorAll('.brand-filter-select').forEach(el => {
            el.style.display = shopOnly ? '' : 'none';
            if (!shopOnly) el.value = '';
        });
        // Re-run mutual exclusion so cross-slot dedup still applies
        if (typeof syncMutualExclusion === 'function') {
            syncMutualExclusion(['shopPartners', 'shopExtra1', 'shopExtra2', 'shopExtra3', 'shopExtra4']);
        }
    }

    // ── Group visibility: show exactly N inbox slots for each side ────────────
    function updateGroupVisibility() {
        const type = (document.querySelector('input[name="proforma_type"]:checked') || {}).value
                     || 'insurance_shop_only';
        const shopCount   = ['insurance_shop_only', 'insurance_shop_garage'].includes(type)
            ? (parseInt((document.getElementById('number_of_proformas') || {}).value) || 3)
            : (type === 'insurance_garage_only' ? 0 : 3);
        const garageCount = type === 'insurance_garage_only'
            ? (parseInt((document.getElementById('number_of_garages') || {}).value) || 3)
            : (type === 'insurance_shop_only' ? 0 : (type === 'insurance_shop_garage' ? 0 : 3));

        document.querySelectorAll('.shop-inbox-group').forEach(el => {
            el.style.display = (parseInt(el.dataset.group) <= shopCount) ? '' : 'none';
        });
        document.querySelectorAll('.garage-inbox-group').forEach(el => {
            el.style.display = (parseInt(el.dataset.group) <= garageCount) ? '' : 'none';
        });
    }

    function applyProformaType(type) {
        if (type === 'insurance_garage_only') {
            if (numberOfShopsWrapper)   numberOfShopsWrapper.style.display   = 'none';
            if (numberOfGaragesWrapper) numberOfGaragesWrapper.style.display = '';
            if (shopGroupsWrapper)      shopGroupsWrapper.style.display      = 'none';
            if (garageGroupsWrapper)    garageGroupsWrapper.style.display    = '';
        } else if (type === 'insurance_shop_only') {
            if (numberOfShopsWrapper)   numberOfShopsWrapper.style.display   = '';
            if (numberOfGaragesWrapper) numberOfGaragesWrapper.style.display = 'none';
            if (shopGroupsWrapper)      shopGroupsWrapper.style.display      = '';
            if (garageGroupsWrapper)    garageGroupsWrapper.style.display    = 'none';
        } else if (type === 'insurance_shop_garage') {
            if (numberOfShopsWrapper)   numberOfShopsWrapper.style.display   = '';
            if (numberOfGaragesWrapper) numberOfGaragesWrapper.style.display = 'none';
            if (shopGroupsWrapper)      shopGroupsWrapper.style.display      = '';
            if (garageGroupsWrapper)    garageGroupsWrapper.style.display    = 'none';
        } else {
            if (numberOfShopsWrapper)   numberOfShopsWrapper.style.display   = '';
            if (numberOfGaragesWrapper) numberOfGaragesWrapper.style.display = 'none';
            if (shopGroupsWrapper)      shopGroupsWrapper.style.display      = '';
            if (garageGroupsWrapper)    garageGroupsWrapper.style.display    = '';
        }

        filterShopOptions(type);
        updateGroupVisibility();
    }

    typeCards.forEach(card => {
        card.addEventListener('click', () => {
            typeCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
            applyProformaType(card.dataset.type);
        });
    });

    // Wire count selectors to update group visibility on change
    const shopCountSel   = document.getElementById('number_of_proformas');
    const garageCountSel = document.getElementById('number_of_garages');
    if (shopCountSel)   shopCountSel.addEventListener('change',   updateGroupVisibility);
    if (garageCountSel) garageCountSel.addEventListener('change', updateGroupVisibility);

    // Wire brand filter dropdowns to re-filter shop options
    document.querySelectorAll('.brand-filter-select').forEach(el => {
        el.addEventListener('change', () => {
            const currentType = (document.querySelector('input[name="proforma_type"]:checked') || {}).value || 'insurance_shop_only';
            filterShopOptions(currentType);
        });
    });

    // Initialise on page load (handles validation-error repopulation)
    const checkedRadio = document.querySelector('input[name="proforma_type"]:checked');
    const initialType = checkedRadio ? checkedRadio.value : 'insurance_shop_only';
    if (checkedRadio) {
        typeCards.forEach(c => c.classList.remove('active'));
        const activeCard = document.querySelector('.proforma-type-card[data-type="' + checkedRadio.value + '"]');
        if (activeCard) activeCard.classList.add('active');
    }
    applyProformaType(initialType);
    // ── End Proforma Type Selector ──────────────────────────────────────────────

stepper3 = new Stepper(document.getElementById('stepper3'), {
        linear: false, // We'll handle linearity manually
        animation: true
    });

   const vinInput = document.getElementById('vin_input');
    const counter = document.getElementById('vin_counter');

    function updateCounter() {
        let val = vinInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        vinInput.value = val;
        const len = val.length;
        counter.textContent = len + '/17';
        if (len === 17) {
            counter.style.color = '#28a745';
        } else {
            counter.style.color = '#dc3545';
        }
    }

    vinInput.addEventListener('input', updateCounter);
    updateCounter();
});


// Stepper Next Button Validation
document.querySelectorAll('.bs-stepper .btn-next').forEach(button => {
    button.addEventListener('click', e => {
        const currentPane = e.target.closest('.bs-stepper-pane');
        if (!currentPane) return;

        const requiredFields = currentPane.querySelectorAll('.required-field');
        let allFilled = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                allFilled = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!allFilled) {
            const firstInvalid = currentPane.querySelector('.required-field.is-invalid');
            if (firstInvalid) firstInvalid.reportValidity();
            e.stopImmediatePropagation();
            return false;
        }

        // Validate quantity fields are >= 1
        const quantityInputs = currentPane.querySelectorAll('input[name*="[quantity]"]');
        for (let i = 0; i < quantityInputs.length; i++) {
            const val = parseInt(quantityInputs[i].value);
            if (!quantityInputs[i].value || val < 1) {
                quantityInputs[i].classList.add('is-invalid');
                quantityInputs[i].setCustomValidity('Quantity must be at least 1');
                quantityInputs[i].reportValidity();
                return false;
            } else {
                quantityInputs[i].classList.remove('is-invalid');
                quantityInputs[i].setCustomValidity('');
            }
        }

        // Only move to next step if all fields are filled
        stepper3.next();
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Repeater Functionality
    const repeaterContainer = document.getElementById('repeater');
    const addButton = document.getElementById('add-repeater');

    if (repeaterContainer && addButton) {
        addButton.addEventListener('click', function() {

console.log(1);
            const template = repeaterContainer.querySelector('.repeater-item');
            const clone = template.cloneNode(true);
            
            // Clear inputs in the clone (but restore quantity default)
            const inputs = clone.querySelectorAll('input');
            inputs.forEach(input => {
                input.value = '';
                input.classList.remove('is-invalid');
            });

            // Restore quantity default to 1
            const qtyInput = clone.querySelector('input[name*="[quantity]"]');
            if (qtyInput) qtyInput.value = 1;

            const selects = clone.querySelectorAll('select');
            selects.forEach(select => {
                select.selectedIndex = 0;
                select.classList.remove('is-invalid');
            });

            // Update the part number label
            const itemCount = repeaterContainer.querySelectorAll('.repeater-item').length; // 0-indexed count for name, but display is +1
            clone.querySelector('span b').textContent = 'Spare Part #' + (itemCount + 1);

            // Update names with new index
            const inputsAndSelects = clone.querySelectorAll('input, select');
            inputsAndSelects.forEach(el => {
                if (el.name) {
                    el.name = el.name.replace(/parts\[\d+\]/, `parts[${itemCount}]`);
                }
            });

            // Add remove event listener to the new button
            const removeBtn = clone.querySelector('.remove-repeater');
            removeBtn.addEventListener('click', function() {
                if (repeaterContainer.querySelectorAll('.repeater-item').length > 1) {
                    clone.remove();
                    updatePartNumbers();
                }
            });

            repeaterContainer.insertBefore(clone, addButton);
        });

        // Initial remove buttons
        const removeButtons = repeaterContainer.querySelectorAll('.remove-repeater');
        removeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const item = this.closest('.repeater-item');
                if (repeaterContainer.querySelectorAll('.repeater-item').length > 1) {
                    item.remove();
                    updatePartNumbers();
                }
            });
        });

        function updatePartNumbers() {
            const items = repeaterContainer.querySelectorAll('.repeater-item');
            items.forEach((item, index) => {
                item.querySelector('span b').textContent = 'Spare Part #' + (index + 1);
                
                // Update names with new index
                const inputsAndSelects = item.querySelectorAll('input, select');
                inputsAndSelects.forEach(el => {
                    if (el.name) {
                        el.name = el.name.replace(/parts\[\d+\]/, `parts[${index}]`);
                    }
                });
            });
        }
    }

    // Voice Recording Script
    console.log('Voice recording script loaded');
    
    let mediaRecorder;
    let audioChunks = [];
    const startButton = document.getElementById('startRecording');
    const stopButton = document.getElementById('stopRecording');
    const recordingStatus = document.getElementById('recordingStatus');
    const audioPreview = document.getElementById('audioPreview');
    const recordedAudio = document.getElementById('recordedAudio');
    const deleteButton = document.getElementById('deleteRecording');
    const voiceNoteInput = document.getElementById('voiceNoteInput');

    if (!startButton || !stopButton) {
        console.error('Recording buttons not found!');
        return;
    }

    console.log('Recording buttons found:', { startButton, stopButton });

    startButton.addEventListener('click', async () => {
        console.log('Start recording clicked');
        try {
            // Request microphone access
            console.log('Requesting microphone access...');
            const stream = await navigator.mediaDevices.getUserMedia({ 
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                } 
            });
            console.log('Microphone access granted');

            // Create MediaRecorder instance
            mediaRecorder = new MediaRecorder(stream, {
                mimeType: 'audio/webm;codecs=opus'
            });
            console.log('MediaRecorder created:', mediaRecorder.state);

            audioChunks = [];

            mediaRecorder.addEventListener('dataavailable', event => {
                console.log('Data available:', event.data.size, 'bytes');
                audioChunks.push(event.data);
            });

            mediaRecorder.addEventListener('start', () => {
                console.log('Recording started');
                startButton.disabled = true;
                stopButton.disabled = false;
                recordingStatus.style.display = 'block';
                audioPreview.style.display = 'none';
            });

            mediaRecorder.addEventListener('stop', () => {
                console.log('Recording stopped');
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm;codecs=opus' });
                console.log('Audio blob created:', audioBlob.size, 'bytes');
                
                const audioUrl = URL.createObjectURL(audioBlob);
                recordedAudio.src = audioUrl;
                
                // Convert blob to base64 for form submission
                const reader = new FileReader();
                reader.readAsDataURL(audioBlob);
                reader.onloadend = function() {
                    console.log('Audio converted to base64');
                    voiceNoteInput.value = reader.result;
                }

                startButton.disabled = false;
                stopButton.disabled = true;
                recordingStatus.style.display = 'none';
                audioPreview.style.display = 'block';
            });

            mediaRecorder.addEventListener('error', (event) => {
                console.error('MediaRecorder error:', event.error);
                alert('Error during recording: ' + event.error.message);
            });

            // Start recording
            mediaRecorder.start(1000); // Collect data every second
            console.log('MediaRecorder started');

        } catch (err) {
            console.error('Error accessing microphone:', err);
            alert('Error accessing microphone: ' + err.message + '\nPlease ensure you have granted microphone permissions and try again.');
        }
    });

    stopButton.addEventListener('click', () => {
        console.log('Stop recording clicked');
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            mediaRecorder.stream.getTracks().forEach(track => {
                console.log('Stopping track:', track.kind);
                track.stop();
            });
        } else {
            console.warn('MediaRecorder not active');
        }
    });

    deleteButton.addEventListener('click', () => {
        console.log('Delete recording clicked');
        audioChunks = [];
        recordedAudio.src = '';
        voiceNoteInput.value = '';
        audioPreview.style.display = 'none';
        startButton.disabled = false;
    });

    // Check if browser supports required APIs
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        console.error('MediaDevices API not supported in this browser');
        alert('Voice recording is not supported in your browser. Please use a modern browser like Chrome, Firefox, or Edge.');
        startButton.disabled = true;
        startButton.title = 'Voice recording not supported in this browser';
    }

});

</script>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // Initialise all 10 inputs as searchable Select2 multi-selects
    ['shopPartners', 'shopExtra1', 'shopExtra2', 'shopExtra3', 'shopExtra4',
     'garagePartners', 'garageExtra1', 'garageExtra2', 'garageExtra3', 'garageExtra4'].forEach(function (id) {
        var $el = $('#' + id);
        if (!$el.hasClass('select2-hidden-accessible')) {
            $el.select2({
                theme:       'bootstrap-5',
                placeholder: 'Type to search and select...',
                allowClear:  true,
                width:       '100%'
            });
        }
    });

    // N-way mutual exclusion across all specified inputs
    function syncMutualExclusion(inputIds) {
        var $inputs = inputIds.map(function (id) { return $('#' + id); });
        var syncing = false;

        function updateAll(sourceId) {
            if (syncing) return;
            syncing = true;

            $inputs.forEach(function ($input) {
                var thisId  = $input.attr('id');
                var thisVals = $input.val() || [];

                // Values selected in every OTHER input in this group
                var othersSelected = [];
                $inputs.forEach(function ($other) {
                    if ($other.attr('id') !== thisId) {
                        othersSelected = othersSelected.concat($other.val() || []);
                    }
                });

                // Disable options that are taken by another input,
                // but keep this input's OWN selections enabled so their tags stay visible
                $input.find('option').each(function () {
                    var val = $(this).val();
                    if (val) {
                        var takenElsewhere = othersSelected.indexOf(val) !== -1;
                        var ownSelection   = thisVals.indexOf(val) !== -1;
                        $(this).prop('disabled', takenElsewhere && !ownSelection);
                    }
                });

                // For non-source inputs: remove any value that is now taken by another input
                if (thisId !== sourceId) {
                    var filteredVals = thisVals.filter(function (v) {
                        return othersSelected.indexOf(v) === -1;
                    });
                    if (filteredVals.length !== thisVals.length) {
                        $input.val(filteredVals).trigger('change');
                    }
                }

                // Refresh Select2 UI to reflect disabled state in dropdown
                $input.trigger('change.select2');
            });

            syncing = false;
        }

        $inputs.forEach(function ($input) {
            $input.on('change', function () {
                updateAll($input.attr('id'));
            });
        });

        // Initial state
        updateAll(null);
    }

    syncMutualExclusion(['shopPartners', 'shopExtra1', 'shopExtra2', 'shopExtra3', 'shopExtra4']);
    syncMutualExclusion(['garagePartners', 'garageExtra1', 'garageExtra2', 'garageExtra3', 'garageExtra4']);
});
</script>

{{-- ── Excel Import Logic ───────────────────────────────────────────── --}}
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
<script>
(function () {
    // ── Column mapping (header text → field key) ───────────────────────
    const COL_MAP = {
        'part name and number': 'number',
        'part name & number':   'number',
        'part name':            'number',
        'grade':                'grade',
        'country':              'country',
        'qty':                  'quantity',
        'quantity':             'quantity',
        'condition':            'condition',
        'component':            'component',
    };

    const GRADE_OPTIONS   = ['1st Grade(Original OEM)', '2nd Grade(After market)', '3rd Grade', '4th grade (Local)'];
    const COMPONENT_OPTIONS = ['Body Parts', 'Mechanical Parts'];

    function matchOption(options, raw) {
        if (!raw) return options[0];
        const r = String(raw).toLowerCase().trim();
        return options.find(o => o.toLowerCase().includes(r) || r.includes(o.toLowerCase())) || options[0];
    }

    // ── Toggle panel ───────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggleExcelImport');
        const box       = document.getElementById('excelImportBox');
        if (toggleBtn && box) {
            toggleBtn.addEventListener('click', function () {
                const open = box.style.display !== 'none';
                box.style.display = open ? 'none' : 'block';
                toggleBtn.innerHTML = open
                    ? '<i class="bx bx-table me-1"></i>Import from Excel'
                    : '<i class="bx bx-x me-1"></i>Close Import';
            });
        }

        // ── Download Template ──────────────────────────────────────────
        const dlBtn = document.getElementById('downloadExcelTemplate');
        if (dlBtn) {
            dlBtn.addEventListener('click', function () {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet([
                    ['Part Name and Number', 'Grade', 'Country', 'Qty', 'Condition', 'Component'],
                    ['Example: Brake Pad F001', '1st Grade(Original OEM)', 'Japan', '2', 'New', 'Mechanical Parts'],
                ]);
                ws['!cols'] = [{ wch: 30 }, { wch: 25 }, { wch: 15 }, { wch: 6 }, { wch: 12 }, { wch: 18 }];
                XLSX.utils.book_append_sheet(wb, ws, 'Parts');
                XLSX.writeFile(wb, 'spare-parts-template.xlsx');
            });
        }

        // ── File Upload & Parse ────────────────────────────────────────
        const fileInput = document.getElementById('excelFileInput');
        const status    = document.getElementById('excelImportStatus');
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                status.innerHTML = '<span class="text-muted">Reading file…</span>';

                const reader = new FileReader();
                reader.onload = function (e) {
                    try {
                        const wb    = XLSX.read(e.target.result, { type: 'array' });
                        const ws    = wb.Sheets[wb.SheetNames[0]];
                        const rows  = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '' });

                        if (rows.length < 2) {
                            status.innerHTML = '<span class="text-danger">No data rows found. Make sure your file has a header row and at least one data row.</span>';
                            return;
                        }

                        // Map header row → column indices
                        const headers = rows[0].map(h => String(h).toLowerCase().trim());
                        const colIdx  = {};
                        headers.forEach((h, i) => {
                            const key = COL_MAP[h];
                            if (key && !(key in colIdx)) colIdx[key] = i;
                        });

                        if (!('number' in colIdx)) {
                            status.innerHTML = '<span class="text-danger">Could not find a "Part Name and Number" column. Please use the template.</span>';
                            return;
                        }

                        const dataRows = rows.slice(1).filter(r => r.some(c => String(c).trim() !== ''));
                        if (dataRows.length === 0) {
                            status.innerHTML = '<span class="text-danger">No data rows found after the header.</span>';
                            return;
                        }

                        populateRepeater(dataRows, colIdx);
                        status.innerHTML = `<span class="text-success"><i class="bx bx-check-circle me-1"></i>${dataRows.length} part(s) imported successfully.</span>`;
                        fileInput.value = '';
                    } catch (err) {
                        status.innerHTML = '<span class="text-danger">Failed to parse file: ' + err.message + '</span>';
                    }
                };
                reader.readAsArrayBuffer(file);
            });
        }

        // ── Populate repeater from parsed rows ─────────────────────────
        function populateRepeater(dataRows, colIdx) {
            const container = document.getElementById('repeater');
            if (!container) return;

            // Remove all existing items except the first (template)
            const existing = container.querySelectorAll('.repeater-item');
            existing.forEach((el, i) => { if (i > 0) el.remove(); });

            const template = container.querySelector('.repeater-item');

            dataRows.forEach(function (row, idx) {
                let item;
                if (idx === 0) {
                    item = template;
                } else {
                    item = template.cloneNode(true);
                    // Update names and label
                    item.querySelectorAll('input, select').forEach(el => {
                        if (el.name) el.name = el.name.replace(/parts\[\d+\]/, `parts[${idx}]`);
                        el.classList.remove('is-invalid');
                    });
                    item.querySelector('.part-label b').textContent = 'Spare Part #' + (idx + 1);
                    container.insertBefore(item, document.getElementById('add-repeater'));
                }

                const g = k => colIdx[k] !== undefined ? String(row[colIdx[k]] ?? '').trim() : '';

                const numInput  = item.querySelector('input[name*="[number]"]');
                const qtyInput  = item.querySelector('input[name*="[quantity]"]');
                const ctrInput  = item.querySelector('input[name*="[country]"]');
                const gradeSel  = item.querySelector('select[name*="[grade]"]');
                const condSel   = item.querySelector('select[name*="[condition]"]');
                const compSel   = item.querySelector('select[name*="[component]"]');

                if (numInput)  numInput.value  = g('number');
                if (qtyInput)  qtyInput.value  = g('quantity') || '1';
                if (ctrInput)  ctrInput.value  = g('country');
                if (gradeSel)  gradeSel.value  = matchOption(GRADE_OPTIONS, g('grade'));
                if (condSel)   condSel.value   = 'New';
                if (compSel)   compSel.value   = matchOption(COMPONENT_OPTIONS, g('component'));
            });

            // Re-index part labels
            container.querySelectorAll('.repeater-item').forEach((item, i) => {
                const lbl = item.querySelector('.part-label b');
                if (lbl) lbl.textContent = 'Spare Part #' + (i + 1);
            });
        }
    });
}());
</script>
{{-- ── End Excel Import Logic ───────────────────────────────────────── --}}
@endpush

@push('scripts')
<script>
(function () {
    var form = document.getElementById('insuranceProformaForm');
    var btn  = document.getElementById('proformaSubmitBtn');
    if (!form || !btn) return;
    var submitted = false;
    form.addEventListener('submit', function () {
        if (submitted) { return false; }
        submitted = true;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting…';
    });
})();
</script>
@endpush
