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
    VIN Number
    <small class="text-muted">(17 characters & digits)</small>
</label>

<div class="vin-single-wrapper">
    <input type="text"
           class="form-control text-uppercase"
           id="vin_input"
           name="chassis_number"
           maxlength="17"
           minlength="17"
           placeholder="Ente VIN Number"
           value="{{ old('chassis_number') }}"
           required>

    <span class="vin-counter" id="vin_counter">0 / 17</span>
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
                                                    <label for="inputName1" class="form-label">Country(Optional)</label>
                                                    <input name="parts[0][country]" type="text" class="form-control" id="inputName1" placeholder="" data-name="name">
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
                                    <div class="col-12 col-lg-6">
                                        <label for="multiple-select-sparepart" class="form-label">Spare Part Shop Partners (Optional)</label>
                                        <select class="form-select" name="spare_part_partners[]" id="multiple-select-sparepart" data-placeholder="Choose anything" multiple>
                                            @foreach($spare_part_partners as $partner)
                                                <option value="{{$partner->id}}" {{ in_array($partner->id, old('spare_part_partners', [])) ? 'selected' : '' }}>{{$partner->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-6">
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
/**
 * Global variable for the Stepper instance.
 * Declaring it outside the DOMContentLoaded listener ensures it's accessible
 * to the global click handlers used for event delegation.
 */
let stepper3;

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize the BS-Stepper
    const stepperElement = document.getElementById('stepper3');
    if (stepperElement) {
        stepper3 = new Stepper(stepperElement, {
            linear: false,
            animation: true
        });
    }

    // 2. VIN Number Logic: Formats to uppercase and tracks character count
    const vinInput = document.getElementById('vin_input');
    const counter = document.getElementById('vin_counter');

    if (vinInput && counter) {
        const updateCounter = () => {
            let val = vinInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            val = val.slice(0, 17); // Enforce 17 character limit
            vinInput.value = val;

            counter.textContent = `${val.length} / 17`;

            if (val.length === 17) {
                counter.classList.add('text-success');
                counter.classList.remove('text-danger');
            } else {
                counter.classList.remove('text-success');
                counter.classList.add('text-danger');
            }
        };

        vinInput.addEventListener('input', updateCounter);
        updateCounter(); // Initialize on page load
    }

    // 3. Voice Recording Logic
    let mediaRecorder;
    let audioChunks = [];
    const startButton = document.getElementById('startRecording');
    const stopButton = document.getElementById('stopRecording');
    const recordingStatus = document.getElementById('recordingStatus');
    const audioPreview = document.getElementById('audioPreview');
    const recordedAudio = document.getElementById('recordedAudio');
    const deleteButton = document.getElementById('deleteRecording');
    const voiceNoteInput = document.getElementById('voiceNoteInput');

    if (startButton && stopButton) {
        startButton.addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    audio: { echoCancellation: true, noiseSuppression: true } 
                });

                mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm;codecs=opus' });
                audioChunks = [];

                mediaRecorder.addEventListener('dataavailable', e => {
                    if (e.data.size > 0) audioChunks.push(e.data);
                });

                mediaRecorder.addEventListener('start', () => {
                    startButton.disabled = true;
                    stopButton.disabled = false;
                    recordingStatus.style.display = 'block';
                    audioPreview.style.display = 'none';
                });

                mediaRecorder.addEventListener('stop', () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm;codecs=opus' });
                    recordedAudio.src = URL.createObjectURL(audioBlob);
                    
                    const reader = new FileReader();
                    reader.readAsDataURL(audioBlob);
                    reader.onloadend = () => { 
                        voiceNoteInput.value = reader.result; 
                    };

                    startButton.disabled = false;
                    stopButton.disabled = true;
                    recordingStatus.style.display = 'none';
                    audioPreview.style.display = 'block';
                });

                mediaRecorder.start(1000);
            } catch (err) {
                console.error('Mic access error:', err);
                alert('Microphone access denied or not available.');
            }
        });

        stopButton.addEventListener('click', () => {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
                mediaRecorder.stream.getTracks().forEach(track => track.stop());
            }
        });

        deleteButton.addEventListener('click', () => {
            audioChunks = [];
            recordedAudio.src = '';
            voiceNoteInput.value = '';
            audioPreview.style.display = 'none';
        });
    }
});

/**
 * 4. Global Event Delegation
 * This section handles clicks for elements that might be added dynamically 
 * or elements that sometimes fail to trigger listeners on desktop due to
 * UI layering (like the Stepper library content panes).
 */
document.addEventListener('click', function (e) {
    
    // --- STEPPER NEXT BUTTONS ---
    if (e.target.classList.contains('btn-next')) {
        const currentPane = e.target.closest('.bs-stepper-pane');
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

        if (allFilled && stepper3) {
            stepper3.next();
        }
    }

    // --- REPEATER: ADD PART ---
    if (e.target.id === 'add-repeater') {
        e.preventDefault();
        const repeaterContainer = document.getElementById('repeater');
        const items = repeaterContainer.querySelectorAll('.repeater-item');
        
        // Clone the first item as a template
        const template = items[0];
        const clone = template.cloneNode(true);
        const newIndex = items.length;

        // Reset all inputs/selects in the clone and update array index (parts[0] -> parts[1])
        clone.querySelectorAll('input, select').forEach(el => {
            el.value = '';
            el.classList.remove('is-invalid');
            if (el.name) {
                el.name = el.name.replace(/parts\[\d+\]/, `parts[${newIndex}]`);
            }
        });

        // Set defaults for specific fields
        const conditionSelect = clone.querySelector('select[name*="[condition]"]');
        if (conditionSelect) conditionSelect.value = 'New';

        // Update the visual label
        clone.querySelector('span b').textContent = 'Spare Part #' + (newIndex + 1);
        
        repeaterContainer.appendChild(clone);
    }

    // --- REPEATER: REMOVE PART ---
    // Using closest() to handle cases where the user clicks the icon <i> inside the button
    const removeBtn = e.target.closest('.remove-repeater');
    if (removeBtn) {
        e.preventDefault();
        const repeaterContainer = document.getElementById('repeater');
        const items = repeaterContainer.querySelectorAll('.repeater-item');
        
        if (items.length > 1) {
            removeBtn.closest('.repeater-item').remove();
            
            // Re-index all remaining items to ensure the array stays sequential for Laravel validation
            document.querySelectorAll('.repeater-item').forEach((item, index) => {
                item.querySelector('span b').textContent = 'Spare Part #' + (index + 1);
                item.querySelectorAll('input, select').forEach(el => {
                    if (el.name) {
                        el.name = el.name.replace(/parts\[\d+\]/, `parts[${index}]`);
                    }
                });
            });
        }
    }
});
</script>
@endsection
