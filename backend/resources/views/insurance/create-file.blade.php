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
                        <form action="{{ route('insurance.create-file') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Step 1: Basic Information -->
                            <div id="test-vl-1" role="tabpane3" class="bs-stepper-pane content fade" aria-labelledby="stepper3trigger1">
                                <h5 class="mb-1">Basic Information</h5>
                                <p class="mb-4">Enter the basic proforma request</p>

                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <label for="FisrtName" class="form-label">File Number</label>
                                        <input type="text" name="file_number" value="{{old('file_number')}}" class="form-control required-field" id="FisrtName" placeholder="">
                                        @error('file_number')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    
                                    <!-- Add Is Insured Checkbox here -->
                                    <div class="col-12 col-lg-6">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" name="insured" id="insured" value="1" {{ old('insured') ? 'checked' : '' }}>
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
                                            <option value="ICE" {{ old('car_type', 'ICE') == 'ICE' ? 'selected' : '' }}>ICE</option>
                                            <option value="EV" {{ old('car_type') == 'EV' ? 'selected' : '' }}>EV</option>
                                            <option value="Hybrid" {{ old('car_type') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                            <option value="Others" {{ old('car_type') == 'Others' ? 'selected' : '' }}>Others</option>
                                        </select>

                                        @error('car_type')
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
                                        <input type="text" name="model" required value="{{old('model')}}" class="form-control required-field" id="PhoneNumber" placeholder="example: yarris">
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
                                    <!-- Proforma Type Selector -->
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Proforma Type</label>
                                        <div class="d-flex flex-wrap gap-3" id="proformaTypeOptions">
                                            <div class="form-check proforma-type-card active" data-type="insurance_standard">
                                                <input class="form-check-input" type="radio" name="proforma_type" id="typeStandard" value="insurance_standard" checked>
                                                <label class="form-check-label" for="typeStandard">
                                                    <i class="bx bx-buildings me-1"></i> Standard <small class="text-muted d-block">Shops + Garages</small>
                                                </label>
                                            </div>
                                            <div class="form-check proforma-type-card" data-type="insurance_shop_only">
                                                <input class="form-check-input" type="radio" name="proforma_type" id="typeShopOnly" value="insurance_shop_only">
                                                <label class="form-check-label" for="typeShopOnly">
                                                    <i class="bx bx-store me-1"></i> Shop Only <small class="text-muted d-block">Spare Part Shops</small>
                                                </label>
                                            </div>
                                            <div class="form-check proforma-type-card" data-type="insurance_garage_only">
                                                <input class="form-check-input" type="radio" name="proforma_type" id="typeGarageOnly" value="insurance_garage_only">
                                                <label class="form-check-label" for="typeGarageOnly">
                                                    <i class="bx bx-wrench me-1"></i> Garage Only <small class="text-muted d-block">Repair Garages</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Number of Shops (Standard / Shop Only) -->
                                    <div class="col-12 col-lg-6" id="numberOfShopsWrapper">
                                        <label for="number_of_proformas" class="form-label">Number of Required Shops</label>
                                        <select name="number_of_proformas" id="number_of_proformas" class="form-select">
                                            <option value="1">1 Shop</option>
                                            <option value="2">2 Shops</option>
                                            <option value="3" selected>3 Shops</option>
                                            <option value="4">4 Shops</option>
                                            <option value="5">5 Shops</option>
                                        </select>
                                    </div>

                                    <!-- Number of Garages (Garage Only) -->
                                    <div class="col-12 col-lg-6" id="numberOfGaragesWrapper" style="display:none;">
                                        <label for="number_of_garages" class="form-label">Number of Required Garages</label>
                                        <select name="number_of_garages" id="number_of_garages" class="form-select">
                                            <option value="1">1 Garage</option>
                                            <option value="2">2 Garages</option>
                                            <option value="3" selected>3 Garages</option>
                                            <option value="4">4 Garages</option>
                                            <option value="5">5 Garages</option>
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
                                        <input type="text" name="customer_name" value="{{old('customer_name')}}" class="form-control required-field" id="InputUsername" placeholder="Customer Name">
                                        @error('customer_name')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="InputEmail2" class="form-label">Phone Number</label>
                                        <input type="text" name="customer_phone_number" value="{{old('customer_phone_number')}}" class="form-control required-field" id="InputEmail2" placeholder="">
                                        @error('customer_phone_number')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>                                
                                    <div class="col-12 col-lg-6">
                                        <label for="InputEmail3" class="form-label">Email (optional)</label>
                                        <input type="text" name="customer_email" value="{{old('customer_email')}}" class="form-control" id="InputEmail3" placeholder="">
                                        @error('customer_email')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="InputPassword" class="form-label">License Plate Number (Code, City & Number)</label>
                                        <input type="text" name="license_plate_number" value="{{old('license_plate_number')}}" class="form-control required-field" id="InputPassword" value="" placeholder="Example: 3OR-B22662">
                                        @error('license_plate_number')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
<label class="form-label">
    Chassis Number
    <small class="text-muted">(Optional)</small>
</label>

<div class="vin-single-wrapper">
    <input type="text"
           class="form-control text-uppercase"
           id="vin_input"
           name="chassis_number"
           maxlength="17"
           placeholder="Enter Chassis Number"
           value="{{ old('chassis_number') }}">

    <span class="vin-counter" id="vin_counter"></span>
</div>

@error('chassis_number')
    <span class="text-danger">{{ $message }}</span>
@enderror

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
                                <div class="repeater-form">
                                    <div id="repeater">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h5 class="mb-1">Spare Parts</h5>
                                                <p class="mb-4">Add the required spare parts</p>
                                            </div>
           <button type="button" id="add-repeater" class="btn btn-primary repeater-add-btn px-4">Add another part</button>
                                       </div>

                                        <div class="repeater-item">
                                            <div class="item-content row g-3">
                                                <span class="mb-0 font-16 mt-0"><b>Spare Part #1</b></span>
                                                
                                                <div class="col-12 col-lg-4">
                                                    <label for="inputEmail1" class="form-label">Part Name And Part Number</label>
                                                    <input type="text" name="parts[0][number]" class="form-control required-field" id="inputEmail1" required />
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
                                                    <input name="parts[0][country]" type="text" class="form-control" id="inputName1" placeholder="" data-name="name" required>
                                                    @error('parts.0.country')
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-12 col-lg-2">
                                                    <label for="inputName1" class="form-label">Qty</label>
                                                    <input name="parts[0][quantity]" type="number" class="form-control required-field" id="inputName1" placeholder="" data-name="name" required min="1">
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
                                                    <select name="parts[0][component]" id="component" class="form-select required-field" required>
                                                        <option value="">Select Component</option>
                                                        <option value="Body Parts">Body Parts</option>
                                                        <option value="Mechanical Parts">Mechanical Parts</option>
                                                    </select>
                                                    @error('parts.0.component')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="repeater-remove-btn remove-repeater col-12 col-lg-1">
                                                    <label for="inputEmail1" class="form-label"> &nbsp</label>
                                                    <label for="inputName1" class="form-label">&nbsp</label>
                                                    <button type="button" class="remove-repeater btn btn-danger"><i class="bx bx-trash me-0"></i></button>
                                                </div>
                                                <hr/>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <br>
                                <div class="row g-3">
                                    <div class="col-12 col-lg-6" id="shopPartnersWrapper">
                                        <label for="multiple-select-sparepart" class="form-label">Spare Part Shop Partners (Optional)</label>
                                        <select class="form-select" name="spare_part_partners[]" id="multiple-select-sparepart" data-placeholder="Choose anything" multiple>
                                            @foreach($spare_part_partners as $partner)
                                                <option value="{{$partner->id}}" {{ in_array($partner->id, old('spare_part_partners', [])) ? 'selected' : '' }}>{{$partner->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-6" id="garagePartnersWrapper">
                                        <label for="multiple-select-garage" class="form-label">Garage Partners (Optional)</label>
                                        <select class="form-select" name="garage_partners[]" id="multiple-select-garage" data-placeholder="Choose anything" multiple>
                                            @foreach($garage_partners as $partner)
                                                <option value="{{$partner->id}}" {{ in_array($partner->id, old('garage_partners', [])) ? 'selected' : '' }}>{{$partner->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

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
                                            <button type="submit" class="btn btn-success rounded-pill px-4">Submit</button>
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
position: relative;}
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
document.addEventListener('DOMContentLoaded', () => {

    // ── Proforma Type Selector ──────────────────────────────────────────────
    const typeCards = document.querySelectorAll('.proforma-type-card');
    const numberOfShopsWrapper  = document.getElementById('numberOfShopsWrapper');
    const numberOfGaragesWrapper = document.getElementById('numberOfGaragesWrapper');
    const shopPartnersWrapper   = document.getElementById('shopPartnersWrapper');
    const garagePartnersWrapper = document.getElementById('garagePartnersWrapper');

    function applyProformaType(type) {
        if (type === 'insurance_garage_only') {
            numberOfShopsWrapper.style.display  = 'none';
            numberOfGaragesWrapper.style.display = '';
            shopPartnersWrapper.style.display   = 'none';
            garagePartnersWrapper.style.display = '';
        } else if (type === 'insurance_shop_only') {
            numberOfShopsWrapper.style.display  = '';
            numberOfGaragesWrapper.style.display = 'none';
            shopPartnersWrapper.style.display   = '';
            garagePartnersWrapper.style.display = 'none';
        } else {
            numberOfShopsWrapper.style.display  = '';
            numberOfGaragesWrapper.style.display = 'none';
            shopPartnersWrapper.style.display   = '';
            garagePartnersWrapper.style.display = '';
        }
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

    // Initialise based on any pre-checked radio (e.g. after validation error)
    const checkedRadio = document.querySelector('input[name="proforma_type"]:checked');
    if (checkedRadio) applyProformaType(checkedRadio.value);
    // ── End Proforma Type Selector ──────────────────────────────────────────

const stepper3 = new Stepper(document.getElementById('stepper3'), {
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
            // STOP here, do NOT go to next step
            e.stopImmediatePropagation(); // stop other listeners
            return false; // prevents stepper.next() call
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
            
            // Clear inputs in the clone
            const inputs = clone.querySelectorAll('input');
            inputs.forEach(input => input.value = '');
            
            const selects = clone.querySelectorAll('select');
            selects.forEach(select => select.selectedIndex = 0);

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

            repeaterContainer.appendChild(clone);
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
