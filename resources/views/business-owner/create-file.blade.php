@extends('layouts.business-owner')

@section('content')

<style type="text/css">
/* Custom Upload Button — White/Green Theme
------------------------------------- */
.uploadButton {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
  margin-bottom: 10px;
  width: 100%;
  font-style: normal;
  font-size: 14px;
}

.uploadButton .uploadButton-input {
  opacity: 0;
  position: absolute;
  overflow: hidden;
  z-index: -1;
  pointer-events: none;
}

.uploadButton .uploadButton-button {
  display: flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  height: 44px;
  padding: 0px 18px;
  cursor: pointer;
  color: #2e7d32;
  background-color: transparent;
  border: 1px solid #c8e6c9;
  flex-direction: row;
  transition: 0.3s;
  margin: 0;
  outline: none;
  box-shadow: 0 3px 10px rgba(40, 167, 69, 0.08);
}

.uploadButton .uploadButton-button:hover {
  background: rgba(40, 167, 69, 0.1);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.15);
  color: #1b5e20;
}

.uploadButton .uploadButton-file-name {
  flex-grow: 1;
  display: flex;
  align-items: center;
  flex: 1;
  box-sizing: border-box;
  padding: 0 10px;
  padding-left: 18px;
  min-height: 42px;
  top: 1px;
  position: relative;
  color: #555;
  background-color: transparent;
  overflow: hidden;
  line-height: 22px;
}

.preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.recording-indicator {
    width: 12px;
    height: 12px;
    background-color: #dc3545;
    border-radius: 50%;
    animation: pulse 1.5s ease-in-out infinite;
    flex-shrink: 0;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.3); opacity: 0.4; }
    100% { transform: scale(1); opacity: 1; }
}

/* Voice recorder — white/green */
.voice-recorder-container {
    background: #fff;
    border: 1px solid #c8e6c9;
    border-radius: 14px;
    padding: 20px;
    margin-top: 10px;
}

#recordedAudio {
    width: 100%;
    max-width: 400px;
    border-radius: 10px;
    outline: none;
}

#audioPreview {
    background: #f9fdf7;
    border: 1px solid #c8e6c9;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

#recordingStatus {
    background: rgba(220, 53, 69, 0.05);
    border: 1px solid rgba(220, 53, 69, 0.15);
    border-radius: 10px;
    padding: 12px 16px;
}

/* Recording button states */
#startRecording {
    background: linear-gradient(135deg, #28a745, #20c997) !important;
    border: none !important;
    color: #fff !important;
    font-weight: 600;
    padding: 10px 24px !important;
    font-size: 0.9rem;
}
#startRecording:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(40, 167, 69, 0.2);
}
#stopRecording {
    background: rgba(220, 53, 69, 0.1) !important;
    border: 1px solid rgba(220, 53, 69, 0.3) !important;
    color: #dc3545 !important;
    font-weight: 600;
    padding: 10px 24px !important;
}
#stopRecording:not(:disabled):hover {
    background: rgba(220, 53, 69, 0.18) !important;
}
#stopRecording:disabled,
#startRecording:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
#deleteRecording {
    background: rgba(220, 53, 69, 0.08) !important;
    border: 1px solid rgba(220, 53, 69, 0.25) !important;
    color: #dc3545 !important;
    font-weight: 600;
    padding: 8px 20px !important;
}
#deleteRecording:hover {
    background: rgba(220, 53, 69, 0.15) !important;
}

.recording-active {
    color: #dc3545 !important;
    font-size: 0.88rem;
    font-weight: 600;
}

/* FilePond — white/green theme */
.filepond--root {
    margin-bottom: 0;
    font-family: inherit;
}

.filepond--panel-root {
    background: #f9fdf7 !important;
    border: 1px solid #c8e6c9 !important;
    border-radius: 10px !important;
}

.filepond--drop-label {
    color: #555 !important;
    border-radius: 10px;
    font-size: 0.875rem;
}

.filepond--drop-label label {
    color: #555 !important;
}

.filepond--label-action {
    color: #2e7d32 !important;
    text-decoration: underline !important;
    text-decoration-color: #2e7d32 !important;
}

.filepond--item-panel {
    background: rgba(40, 167, 69, 0.12) !important;
}

/* BS Stepper — white/green overrides */
.bs-stepper {
    background: transparent !important;
}

.bs-stepper .step-trigger {
    color: #555 !important;
}

.bs-stepper .step-trigger:hover {
    color: #1a1a1a !important;
}

.bs-stepper .bs-stepper-circle {
    background: #f1f8e9 !important;
    color: #555 !important;
    border: 2px solid #c8e6c9 !important;
}

.bs-stepper .step.active .bs-stepper-circle,
.bs-stepper .active .bs-stepper-circle {
    background: #28a745 !important;
    border-color: #28a745 !important;
    color: #fff !important;
}

.bs-stepper-line {
    background-color: #c8e6c9 !important;
}

.bs-stepper-content {
    padding: 24px 20px !important;
}

.steper-title {
    color: #1a1a1a !important;
}

.steper-sub-title {
    color: #555 !important;
}

/* Dashboard box — white/green */
.dashboard-box {
    background: #fff !important;
    border: 1px solid #c8e6c9 !important;
    border-radius: 14px !important;
    overflow: hidden;
    margin-bottom: 20px;
}

/* Form select option text */
.form-select option {
    background: #fff;
    color: #1a1a1a;
}

/* Card overrides */
.card {
    background: #fff !important;
    border: 1px solid #c8e6c9 !important;
    border-radius: 14px !important;
    color: #1a1a1a !important;
    box-shadow: 0 2px 12px rgba(40, 167, 69, 0.08) !important;
}

.card-header {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.06), rgba(32, 201, 151, 0.06)) !important;
    border-bottom: 1px solid #c8e6c9 !important;
    color: #1a1a1a !important;
}

.card-body { color: #1a1a1a !important; }

/* Forms */
.form-control, .form-select {
    background: #fff !important;
    border: 1px solid #c8e6c9 !important;
    color: #1a1a1a !important;
    border-radius: 10px;
}

.form-control:focus, .form-select:focus {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.15) !important;
    background: #fff !important;
    color: #1a1a1a !important;
}

.form-control::placeholder { color: #999 !important; }

/* Labels */
label, .form-label {
    color: #1a1a1a !important;
    font-weight: 600;
    font-size: 0.85rem;
}

/* Buttons */
.btn-primary {
    background: linear-gradient(135deg, #28a745, #20c997) !important;
    border: none !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    box-shadow: 0 4px 16px rgba(40, 167, 69, 0.2) !important;
    transition: all 0.3s ease !important;
    color: #fff !important;
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(40, 167, 69, 0.3) !important;
}

.btn-outline-secondary {
    color: #555 !important;
    border-color: #c8e6c9 !important;
}
.btn-outline-secondary:hover {
    background: #f1f8e9 !important;
    color: #1a1a1a !important;
}

/* Text overrides */
h1, h2, h3, h4, h5, h6 { color: #1a1a1a !important; }
p { color: #333 !important; }

/* Overlay */
#boSubmitOverlay {
    background: rgba(255, 255, 255, 0.85) !important;
}
#boSubmitOverlay .fw-bold {
    color: #1a1a1a !important;
}

/* Remove repeater button */
.remove-repeater.btn-danger {
    background: rgba(220, 53, 69, 0.1) !important;
    color: #dc3545 !important;
    border: 1px solid rgba(220, 53, 69, 0.3) !important;
}
.remove-repeater.btn-danger:hover {
    background: rgba(220, 53, 69, 0.18) !important;
}

/* Repeater add button */
.repeater-add-btn {
    background: linear-gradient(135deg, #28a745, #20c997) !important;
    border: none !important;
    color: #fff !important;
}

/* VIN/Chassis counter */
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
    font-weight: 600;
    pointer-events: none;
}
#vin_input {
    padding-right: 70px;
    letter-spacing: 2px;
}
</style>

<!-- Add FilePond CSS -->
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">

<div id="boSubmitOverlay" style="display:none;position:fixed;inset:0;background:rgba(10,22,40,.85);z-index:9999;align-items:center;justify-content:center;">
    <div class="text-center">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="mt-2 fw-bold">Please wait...</div>
    </div>
</div>

<h3 class="">Request Proforma</h3>
@if (session('success'))
    <div class="alert alert-success mt-2">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div id="stepper3" class="bs-stepper gap-4 vertical">
                    <div class="border-right pr-2" role="tablist">
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
                                    <h5 class="mb-0 steper-title">Additional Information</h5>
                                    <p class="mb-0 steper-sub-title">4th Step</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bs-stepper-content">
                        <form id="createFileForm" action="{{ route('business-owner.create-file') }}" method="POST" enctype="multipart/form-data" onsubmit="return validateBOForm()">
                            @csrf
                            <div id="test-vl-1" role="tabpane3" class="bs-stepper-pane content fade" aria-labelledby="stepper3trigger1">
                                <h5 class="mb-1">Basic Information</h5>
                                <p class="mb-4">Enter the basic proforma request</p>
                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
    <label for="FisrtName">Number of Proforma Invoices</label>
     @php
                               $cost = \App\Models\Cost::latest()->first();
                            @endphp
    <select name="number_of_proformas"
            id="boProformaType"
            class="form-select">

        <option value="1" {{ old('number_of_proformas') == 1 ? 'selected' : '' }}>
            1: ({{ number_format($cost->{'1_proforma_cost'}, 2) }} Birr)
        </option>

        <option value="2" {{ old('number_of_proformas') == 2 ? 'selected' : '' }}>
            2: ({{ number_format($cost->{'2_proforma_cost'}, 2) }} Birr)
        </option>

        <option value="3" {{ old('number_of_proformas') == 3 ? 'selected' : '' }}>
            3: ({{ number_format($cost->{'3_proforma_cost'}, 2) }} Birr)
        </option>

        <!-- <option value="4" {{ old('number_of_proformas') == 4 ? 'selected' : '' }}>
            4: ({{ number_format($cost->{'4_proforma_cost'}, 2) }} Birr)
        </option> -->

        <option value="-1" {{ old('number_of_proformas') == -1 ? 'selected' : '' }}>
            5: Unlimited by timer ({{ number_format($cost->etera_chereta_cost, 2) }} Birr)
        </option>
    </select>
</div>
                                    <div class="col-12 col-lg-6" id="boEteraCheretaDropdown" style="display: none;">
                                        <label for="boEteraCheretaHours" class="form-label">Timer Duration</label>
                                        <select class="form-select" name="etera_chereta_hours" id="boEteraCheretaHours" aria-label="Default select example">
                                            <option value="4" {{ old('etera_chereta_hours') == 4 ? 'selected' : '' }}>4hr</option>
                                            <option value="8" {{ old('etera_chereta_hours') == 8 ? 'selected' : '' }}>8hr</option>
                                            <option value="12" {{ old('etera_chereta_hours') == 12 ? 'selected' : '' }}>12hr</option>
                                            <option value="24" {{ old('etera_chereta_hours') == 24 ? 'selected' : '' }}>24hr</option>
                                            <option value="48" {{ old('etera_chereta_hours') == 48 ? 'selected' : '' }}>48hr</option>
                                        <option value="72" {{ old('etera_chereta_hours') == 72 ? 'selected' : '' }}>72hr</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-6">
    <label for="car_type" class="form-label">Car Type</label>
    <select name="car_type" id="car_type" class="form-select" required>
        <option value="ICE" {{ old('car_type', 'ICE') == 'ICE' ? 'selected' : '' }}>ICE(Gas)</option>
        <option value="EV" {{ old('car_type') == 'EV' ? 'selected' : '' }}>EV</option>
        <option value="Hybrid" {{ old('car_type') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
    </select>

    @error('car_type')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
    
                                    <div class="col-12 col-lg-6">
                                        <label for="InputBrand" class="form-label">Brand</label>
                                        <select class="form-select" name="brand_id" required id="InputBrand" aria-label="Default select example">
                                            @foreach($brands as $brand)
                                                <option value="{{$brand->id}}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{$brand->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="InputModel" class="form-label">Model</label>
                                        <input type="text" name="model" value="{{old('model')}}" required class="form-control" id="InputModel" placeholder="Example: Yaris">
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="InputYear" class="form-label">Year</label>
                                        <select class="form-select" name="year" required id="InputYear" aria-label="Default select example">
                                           <option value="#N/A" {{ old('year') == '#N/A' ? 'selected' : '' }}>#N/A</option>
                                            @for($i = 1990; $i <= date('Y'); $i++)
                                                <option value="{{$i}}" {{ old('year') == $i ? 'selected' : '' }}>{{$i}}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <button type="button" class="btn btn-primary px-4 rounded-pill" onclick="validateBOStep1()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
                                    </div>
                                </div>
                            </div>

                            <div id="test-vl-2" role="tabpane3" class="bs-stepper-pane content fade" aria-labelledby="stepper3trigger2">
                                <h5 class="mb-1">Car Information</h5>
                                <p class="mb-4">Enter the car details</p>
                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <label for="customer_phone_number" class="form-label">Phone Number</label>
                                        <input type="text" name="customer_phone_number" value="{{ old('customer_phone_number') ?? auth()->user()->phone_number }}" required class="form-control" id="customer_phone_number" placeholder="Example: +251900000000 or 0900000000">
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="InputPassword" class="form-label">License Plate Number (Code, City & Number)/please note this will be used as your file number/</label>
                                        <input type="text" name="license_plate_number" value="{{old('license_plate_number')}}" required class="form-control" id="license_plate_number" placeholder="Example: 2AA-12345">
                                    </div>
                                   <div class="col-12 col-lg-6">
    <label class="form-label">
        Chassis Number
    </label>

    <div class="vin-single-wrapper position-relative">
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
                                            <button type="button" class="btn btn-primary rounded-pill px-4" onclick="validateBOStep2()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="test-vl-3" role="tabpane3" class="bs-stepper-pane content fade" aria-labelledby="stepper3trigger3">
                                <div class="repeater-form">
                                    <div id="repeater-container">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h5 class="mb-1">Spare Parts</h5>
                                                <p class="mb-4">Add the required spare parts</p>
                                            </div>
                                            <button type="button" id="add-repeater" class="btn btn-primary repeater-add-btn px-4">Add another part</button>
                                        </div>
                                        <div id="repeater-items">
                                            <div class="repeater-item">
                                                <div class="item-content row g-3">
                                                    <span class="mb-0 font-16 mt-0"><b>Spare Part #1</b></span>
                                                    <div class="col-12 col-lg-4">
                                                        <label for="condition_0" class="form-label">Condition</label>
                                                        <select class="form-select" name="parts[condition][]" id="condition_0" aria-label="Default select example" required>
                                                            <option value="">Select Condition</option>
                                                            <option value="New" selected>New</option>
                                                            <option value="Used" disabled>Used</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 col-lg-4">
                                                        <label for="number_0" class="form-label">Part Name and (Part Number)</label>
                                                        <input name="parts[number][]" type="text" class="form-control" id="number_0" placeholder="e.g: Boost Sensor (008-900734)" required>
                                                    </div>
                                                    <div class="col-12 col-lg-4">
                                                        <label for="grade_0" class="form-label">Parts Grade</label>
                                                        <select class="form-select" name="parts[grade][]" id="grade_0" aria-label="Default select example" required>
                                                            <option value="1st grade (Original OEM)">1st grade (Original OEM)</option>
                                                            <option value="2nd grade (After market)">2nd grade (After market)</option>
                                                            <option value="3rd grade">3rd grade</option>
                                                            <option value="4th grade (Local)">4th grade (Local)</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 col-lg-3">
                                                        <label for="country_0" class="form-label">Country Part is Manufactured</label>
                                                        <input name="parts[country][]" type="text" class="form-control" id="country_0" required>
                                                    </div>
                                                    <div class="col-12 col-lg-2">
                                                        <label for="quantity_0" class="form-label">Qty</label>
                                                        <input name="parts[quantity][]" type="number" class="form-control" id="quantity_0" min="1" value="1">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label for="component_0" class="form-label">Component</label>
                                                        <select name="parts[component][]" class="form-select" id="component_0" required>
                                                            <option value="">Select Component</option>
                                                            <option value="Body Parts">Body Parts</option>
                                                            <option value="Mechanical Parts">Mechanical Parts</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 col-lg-4">
                                                        <label class="form-label">Images (Optional Max=3)</label>
                                                        <input type="file" name="parts[photo][0][]" id="parts_photo_0" class="filepond-initialize" multiple accept="image/*" data-part-index="0">
                                                    </div>
                                                    <div class="repeater-remove-btn remove-repeater col-12 col-lg-1">
                                                        <label for="inputEmail1" class="form-label"> &nbsp</label>
                                                        <button type="button" class="remove-repeater btn btn-danger"><i class="bx bx-trash me-0"></i></button>
                                                    </div>
                                                    <hr/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 pt-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="stepper3.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Previous</button>
                                            <button type="button" class="btn btn-primary rounded-pill px-4" onclick="validateBOStep3()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="test-vl-4" role="tabpane3" class="bs-stepper-pane content fade" aria-labelledby="stepper3trigger4">
                                <h5 class="mb-1">Additional Information</h5>
                                <p class="mb-4">Submit the Form </p>
                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Voice Note (Optional)</h5>
                                                <p class="card-text">Record a voice note to provide additional information about your request.</p>
                                                <div class="voice-recorder-container">
                                                    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
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
                                                    <div id="audioPreview" class="mb-3 align-items-center gap-3 flex-wrap" style="display: none;">
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
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="stepper3.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Previous</button>
                                        <button id="boSubmitButton" type="submit" class="btn btn-primary rounded-pill px-4" onclick="showSubmitOverlay()">Submit</button>
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

<!-- Add FilePond JS -->
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
<script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

<script src="{{asset('assets/plugins/bs-stepper/js/bs-stepper.min.js')}}"></script>
<script>
    // Global variable to track FilePond instances
    const filePondInstances = new Map();

    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 FilePond initialization started');

        // Register plugins
        FilePond.registerPlugin(
            FilePondPluginFileValidateType,
            FilePondPluginImageExifOrientation,
            FilePondPluginImagePreview
        );

        initializeFilePond();
        initializeRepeater();
        initializeStepper();
        initializeVoiceRecording();
    });

    function initializeFilePond() {
        const csrfToken = document.querySelector('input[name="_token"]').value;

        // FilePond configuration
        const pondOptions = {
            allowMultiple: true,
            maxFiles: 3,
            maxParallelUploads: 3,
            credits: false,
            imagePreviewHeight: 120,
            imageResizeTargetWidth: 800,
            imageResizeTargetHeight: 600,
            imageResizeMode: 'contain',
            imageResizeUpscale: false,
            acceptedFileTypes: ['image/*'],
            allowImagePreview: true,
            allowImageExifOrientation: true,
            allowImageTransform: true,
            server: {
                process: {
                    url: '/upload-part-image',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    withCredentials: false,
                    onload: (response) => {
                        try {
                            const data = JSON.parse(response);
                            if (data.success && data.files && data.files.length > 0) {
                                // ✅ Return the actual temp_path string (e.g., "uploads/temp/abc.jpg")
                                return data.files[0].temp_path;
                            }
                            console.error('Unexpected upload response:', data);
                            return null;
                        } catch (e) {
                            console.error('Error parsing upload response:', e);
                            return null;
                        }
                    },
                    onerror: (response) => {
                        console.error('Upload error:', response);
                        return response;
                    }
                },
                revert: {
                    url: '/delete-part-image',
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    onload: (response) => {
                        console.log('File deleted');
                        return response;
                    },
                    onerror: (response) => {
                        console.error('Delete error:', response);
                        return response;
                    }
                }
            }
        };

        // Initialize all inputs that need FilePond
        document.querySelectorAll('input.filepond-initialize').forEach((input, index) => {
            if (!filePondInstances.has(input.id)) {
                const pond = FilePond.create(input, pondOptions);
                filePondInstances.set(input.id, pond);

                // 🧩 When a file is added
                pond.on('addfile', (error, file) => {
                    if (error) {
                        console.error('Error adding file:', error);
                        return;
                    }
                    console.log('📦 File added:', file.filename);
                });

                // 🧩 When upload completes successfully
                pond.on('processfile', (error, file) => {
                    if (error) {
                        console.error('Error processing file:', error);
                        return;
                    }

                    console.log('✅ File processed successfully:', file.filename);

                    // Get uploaded file path (from the backend)
                    const uploadedPath = file.serverId; // because onload() already returned path
                    console.log('🗂️ Uploaded path received:', uploadedPath);

                    // Add a hidden input to the same form
                    const form = input.closest('form');
                    if (form) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = `${input.name}_paths[]`; // e.g. parts[photo]_paths[]
                        hiddenInput.value = uploadedPath;
                        hiddenInput.dataset.filePath = uploadedPath; // for later removal
                        form.appendChild(hiddenInput);

                        console.log('💾 Hidden input added:', hiddenInput);
                    }
                });

                // 🧩 Remove hidden input if file is deleted
                pond.on('removefile', (error, file) => {
                    const uploadedPath = file.serverId;
                    console.log('🗑️ File removed:', file.filename, uploadedPath);

                    if (uploadedPath) {
                        const form = input.closest('form');
                        if (form) {
                            const hiddenInputs = form.querySelectorAll(
                                `input[name="${input.name}_paths[]"]`
                            );
                            hiddenInputs.forEach((h) => {
                                if (h.value === uploadedPath) {
                                    console.log('🚮 Removing hidden input for:', uploadedPath);
                                    h.remove();
                                }
                            });
                        }
                    }
                });

                pond.on('processfileprogress', (file, progress) => {
                    console.log(`⏳ Upload progress (${file.filename}): ${(progress * 100).toFixed(1)}%`);
                });
            }
        });
    }

    function initializeRepeater() {
        let sparePartCounter = 1;
        const repeaterContainer = document.querySelector("#repeater-items");
        const addButton = document.getElementById("add-repeater");

        addButton.addEventListener("click", function () {
            sparePartCounter++;

            const repeaterItem = document.createElement("div");
            repeaterItem.classList.add("repeater-item");
            repeaterItem.innerHTML = `
                <div class="item-content row g-3 pt-2">
                    <div class="col-12">
                        <span class="mb-0 font-16 mt-0"><b>Spare Part #${sparePartCounter}</b></span>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Condition</label>
                        <select class="form-select" name="parts[condition][]" required>
                            <option value="">Select Condition</option>
                            <option value="New" selected>New</option>
                            <option value="Used" disabled>Used</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Part name and (Part Number)</label>
                        <input name="parts[number][]" type="text" required class="form-control" placeholder="e.g: Boost Sensor (008-900734)">
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Grade</label>
                        <select class="form-select" name="parts[grade][]" required>
                            <option value="1st grade (Original OEM)">1st grade (Original OEM)</option>
                            <option value="2nd grade (After market)">2nd grade (After market)</option>
                            <option value="3rd grade">3rd grade</option>
                            <option value="4th grade (Local)">4th grade (Local)</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-3">
                        <label class="form-label">Country Part is Manufactured</label>
                        <input name="parts[country][]" type="text" class="form-control" required>
                    </div>
                    <div class="col-12 col-lg-2">
                        <label class="form-label">Qty</label>
                        <input name="parts[quantity][]" type="number" class="form-control" min="1" value="1">
                    </div>
                    <div class="col-12 col-lg-2">
                        <label class="form-label">Component</label>
                        <select name="parts[component][]" class="form-select" required>
                            <option value="">Select Component</option>
                            <option value="Body Parts">Body Parts</option>
                            <option value="Mechanical Parts">Mechanical Parts</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Upload Photo (Optional Max=3)</label>
                        <input type="file" 
                               name="parts[photo][${sparePartCounter - 1}][]" 
                               class="filepond-initialize" 
                               accept="image/*" 
                               multiple
                               data-part-index="${sparePartCounter - 1}"
                               id="parts_photo_${sparePartCounter}">
                    </div>
                    <div class="col-12 col-lg-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="remove-repeater btn btn-danger">
                            <i class="bx bx-trash me-0"></i>
                        </button>
                    </div>
                    <div class="col-12"><hr/></div>
                </div>
            `;

            repeaterContainer.appendChild(repeaterItem);

            // Initialize FilePond for the new input
            setTimeout(() => {
                initializeFilePond();
            }, 100);

            // Remove button functionality
            repeaterItem.querySelector(".remove-repeater").addEventListener("click", function() {
                const fileInput = repeaterItem.querySelector('input.filepond-initialize');
                if (fileInput && filePondInstances.has(fileInput.id)) {
                    const pond = filePondInstances.get(fileInput.id);
                    pond.destroy();
                    filePondInstances.delete(fileInput.id);
                }
                repeaterItem.remove();
                updateSparePartNumbers();
            });

            updateSparePartNumbers();
        });

        // Initialize remove buttons for existing items
        document.querySelectorAll(".remove-repeater").forEach(btn => {
            btn.addEventListener("click", function() {
                const repeaterItem = this.closest(".repeater-item");
                const fileInput = repeaterItem.querySelector('input.filepond-initialize');
                if (fileInput && filePondInstances.has(fileInput.id)) {
                    const pond = filePondInstances.get(fileInput.id);
                    pond.destroy();
                    filePondInstances.delete(fileInput.id);
                }
                repeaterItem.remove();
                updateSparePartNumbers();
            });
        });

        function updateSparePartNumbers() {
            document.querySelectorAll(".repeater-item").forEach((item, index) => {
                const span = item.querySelector("span.mb-0");
                if (span) {
                    span.innerHTML = `<b>Spare Part #${index + 1}</b>`;
                }
            });
        }
    }

    function initializeStepper() {
        // Initialize stepper
        var stepper3 = new Stepper(document.querySelector('#stepper3'), {
            linear: true,
            animation: true
        });

        window.stepper3 = stepper3;

        // Toggle Etera-Chereta dropdown based on proforma type selection
        const boProformaType = document.getElementById('boProformaType');
        const boEteraCheretaDropdown = document.getElementById('boEteraCheretaDropdown');
        function toggleEteraCheretaDropdown() {
            if (boProformaType.value === '-1') {
                boEteraCheretaDropdown.style.display = 'block';
            } else {
                boEteraCheretaDropdown.style.display = 'none';
            }
        }
        if (boProformaType) {
            boProformaType.addEventListener('change', toggleEteraCheretaDropdown);
            toggleEteraCheretaDropdown();
        }

        // VIN/Chassis number counter
        const vinInput = document.getElementById('vin_input');
        const vinCounter = document.getElementById('vin_counter');

        function updateVinCounter() {
            if (!vinInput || !vinCounter) return;
            let val = vinInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            vinInput.value = val;
            const len = val.length;
            vinCounter.textContent = len + '/17';
            if (len === 17) {
                vinCounter.style.color = '#28a745';
            } else {
                vinCounter.style.color = '#dc3545';
            }
        }

        if (vinInput) {
            vinInput.addEventListener('input', updateVinCounter);
            updateVinCounter();
        }
    }

    function initializeVoiceRecording() {
        const startRecordingBtn = document.getElementById('startRecording');
        const stopRecordingBtn = document.getElementById('stopRecording');
        const recordedAudio = document.getElementById('recordedAudio');
        const voiceNoteInput = document.getElementById('voiceNoteInput');
        const audioPreview = document.getElementById('audioPreview');
        const recordingStatus = document.getElementById('recordingStatus');
        const deleteRecordingBtn = document.getElementById('deleteRecording');
        let mediaRecorder;
        let audioChunks = [];
        let isRecording = false;

        function startRecording() {
            if (isRecording) return;
            
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(stream => {
                    mediaRecorder = new MediaRecorder(stream);
                    audioChunks = [];
                    isRecording = true;
                    
                    mediaRecorder.addEventListener('dataavailable', event => {
                        audioChunks.push(event.data);
                    });
                    
                    mediaRecorder.addEventListener('stop', () => {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        const audioUrl = URL.createObjectURL(audioBlob);
                        recordedAudio.src = audioUrl;
                        
                        const reader = new FileReader();
                        reader.readAsDataURL(audioBlob);
                        reader.onloadend = function() {
                            voiceNoteInput.value = reader.result;
                        }
                    });
                    
                    mediaRecorder.start();
                    startRecordingBtn.disabled = true;
                    stopRecordingBtn.disabled = false;
                    recordingStatus.style.display = 'block';
                    audioPreview.style.display = 'none';
                })
                .catch(err => {
                    console.error('Error accessing microphone:', err);
                    alert('Error accessing microphone. Please ensure you have granted microphone permissions.');
                });
        }

        function stopRecording() {
            if (!isRecording) return;
            mediaRecorder.stop();
            mediaRecorder.stream.getTracks().forEach(track => track.stop());
            isRecording = false;
            startRecordingBtn.disabled = false;
            stopRecordingBtn.disabled = true;
            recordingStatus.style.display = 'none';
            audioPreview.style.display = 'flex';
        }

        if (startRecordingBtn && stopRecordingBtn) {
            startRecordingBtn.addEventListener('click', startRecording);
            stopRecordingBtn.addEventListener('click', stopRecording);
        }

        if (deleteRecordingBtn) {
            deleteRecordingBtn.addEventListener('click', () => {
                audioChunks = [];
                recordedAudio.src = '';
                voiceNoteInput.value = '';
                audioPreview.style.display = 'none';
            });
        }
    }

    // Validation functions
    function validateBOForm() {
        const sparePartInputs = document.querySelectorAll('select[name="parts[condition][]"]');
        if (sparePartInputs.length === 0) {
            alert('Please add at least one spare part.');
            return false;
        }
        
        const requiredFields = document.querySelectorAll('[required]');
        for (let field of requiredFields) {
            if (!field.value.trim()) {
                alert('Please fill in all required fields.');
                field.focus();
                return false;
            }
        }
        
        return true;
    }

    function validateBOStep1() {
        const proformaType = document.getElementById('boProformaType');
        const brand = document.getElementById('InputBrand');
        const model = document.getElementById('InputModel');
        const year = document.getElementById('InputYear');
        
        if (!proformaType.value) {
            alert('Please select number of proformas.');
            proformaType.focus();
            return false;
        }
        if (!brand.value) {
            alert('Please select a brand.');
            brand.focus();
            return false;
        }
        if (!model.value.trim()) {
            alert('Please enter the model.');
            model.focus();
            return false;
        }
        if (!year.value) {
            alert('Please select the year.');
            year.focus();
            return false;
        }
        
        stepper3.next();
        return true;
    }

    function validateBOStep2() {
        const phoneNumber = document.getElementById('customer_phone_number');
        const licensePlate = document.getElementById('license_plate_number');
        const chassisNumber = document.getElementById('vin_input');
        
        if (!phoneNumber.value.trim()) {
            alert('Please enter the phone number.');
            phoneNumber.focus();
            return false;
        }
        if (!licensePlate.value.trim()) {
            alert('Please enter the license plate number.');
            licensePlate.focus();
            return false;
        }
        if (!chassisNumber.value.trim()) {
            alert('Please enter the chassis number.');
            chassisNumber.focus();
            return false;
        }
        if (!/^[A-Za-z0-9]{17}$/.test(chassisNumber.value.trim())) {
            alert('Chassis number must be exactly 17 characters (letters and digits only).');
            chassisNumber.focus();
            return false;
        }
        
        stepper3.next();
        return true;
    }

    function validateBOStep3() {
        const sparePartInputs = document.querySelectorAll('select[name="parts[condition][]"]');
        if (sparePartInputs.length === 0) {
            alert('Please add at least one spare part.');
            return false;
        }
        
        for (let i = 0; i < sparePartInputs.length; i++) {
            const condition = sparePartInputs[i];
            const partNumber = document.querySelectorAll('input[name="parts[number][]"]')[i];
            const grade = document.querySelectorAll('select[name="parts[grade][]"]')[i];
            const component = document.querySelectorAll('select[name="parts[component][]"]')[i];
            const country = document.querySelectorAll('input[name="parts[country][]"]')[i];
            
            
            if (!condition.value) {
                alert(`Please select condition for spare part #${i + 1}.`);
                condition.focus();
                return false;
            }
            if (!partNumber.value.trim()) {
                alert(`Please enter part name and number for spare part #${i + 1}.`);
                partNumber.focus();
                return false;
            }
            if (!grade.value) {
                alert(`Please select grade for spare part #${i + 1}.`);
                grade.focus();
                return false;
            }
            if (!component.value) {
                alert(`Please select component for spare part #${i + 1}.`);
                component.focus();
                return false;
            }
            if (!country.value) {
                alert(`Please enter country for spare part #${i + 1}.`);
                country.focus();
                return false;
            }
        }
        
        stepper3.next();
        return true;
    }

    function showSubmitOverlay() {
        document.getElementById('boSubmitOverlay').style.display = 'flex';
    }

    // Handle form submission
    document.getElementById('createFileForm').addEventListener('submit', function(e) {
        // The overlay is already shown by the onclick handler
        // Additional form validation can be added here if needed
        return true;
    });
</script>

@endsection
