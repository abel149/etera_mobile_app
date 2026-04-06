@extends('layouts.sparepart')
@section('post')
class="current"
@endsection
@section('content')
<div class="margin-top-15 margin-bottom-45n"></div>
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@elseif ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                <script>console.error('❌ Validation Error: {{ $error }}');</script>
            @endforeach
        </ul>
    </div>
@else
    <script>
        console.log('ℹ️ No success or error messages in session.');
    </script>
@endif

<div id="garageSubmitOverlay" style="display:none;position:fixed;inset:0;background:rgba(255,255,255,.8);z-index:9999;align-items:center;justify-content:center;">
    <div class="text-center">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="mt-2 fw-bold">Please wait...</div>
    </div>
</div>

<!-- FilePond CSS FIRST -->
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
<link href="{{asset('assets/plugins/bs-stepper/css/bs-stepper.css')}}" rel="stylesheet" />

<div class="container">
    <div>
        <h1 class="mb-4 mb-2 text-center justify-content-center">Request Proforma Invoices</h1>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<form id="garageProformaForm" action="{{route('garage.create-file')}}" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
@csrf
@method('POST')
    <div id="stepper1" class="bs-stepper">
        <div class="dashboard-box padding-top-10 padding-bottom-10 padding-left-20 padding-right-20 margin-bottom-45">
            <div class="card-header">
                <div class="d-lg-flex flex-lg-row align-items-lg-center justify-content-lg-between" role="tablist">
                    <div class="step" data-target="#test-l-1">
                      <div class="step-trigger" role="tab" id="stepper1trigger1" aria-controls="test-l-1">
                        <div class="bs-stepper-circle">1</div>
                        <div class="">
                            <h5 class="mb-0 steper-title">Basic Info</h5>
                            <p class="mb-0 steper-sub-title">Enter Details</p>
                        </div>
                      </div>
                    </div>
                    <div class="bs-stepper-line"></div>
                    <div class="step" data-target="#test-l-2">
                        <div class="step-trigger" role="tab" id="stepper1trigger2" aria-controls="test-l-2">
                          <div class="bs-stepper-circle">2</div>
                          <div class="">
                              <h5 class="mb-0 steper-title">Customer Details</h5>
                              <p class="mb-0 steper-sub-title">Enter Customer Details</p>
                          </div>
                        </div>
                      </div>
                    <div class="bs-stepper-line"></div>
                    <div class="step" data-target="#test-l-3">
                        <div class="step-trigger" role="tab" id="stepper1trigger3" aria-controls="test-l-3">
                          <div class="bs-stepper-circle">3</div>
                          <div class="">
                              <h5 class="mb-0 steper-title">Spare part Details</h5>
                              <p class="mb-0 steper-sub-title">Enter Spare Part Details</p>
                          </div>
                        </div>
                      </div>
                      <div class="bs-stepper-line"></div>
                      <div class="step" data-target="#test-l-4">
                        <div class="step-trigger" role="tab" id="stepper1trigger4" aria-controls="test-l-4">
                          <div class="bs-stepper-circle">4</div>
                          <div class="">
                              <h5 class="mb-0 steper-title">Additional Information</h5>
                              <p class="mb-0 steper-sub-title">Enter Any Important Media Files</p>
                          </div>
                        </div>
                      </div>
                  </div>
            </div>
            <div class="card-body">
                <div class="bs-stepper-content">
                    <!-- Removed nested form tag - stepper content is already inside main form -->
                        <div id="test-l-1" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger1">
                           
                            <h5 class="mb-1">Step 1</h5>
                            <h3 class="mb-4">Enter your proforma information </h3>
                            <br>
                            <div class="row g-3">
                               <div class="col-12 col-lg-6">
    <label for="FisrtName">Number of Proforma Invoices</label>
     @php
                               $cost = \App\Models\Cost::latest()->first();
                            @endphp
    <select name="number_of_proformas"
            id="garageProformaType"
            aria-label="Default select example">

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
                                    <div class="col-12 col-lg-6" id="garageEteraCheretaDropdown" style="display: none;">
                                        <label for="boEteraCheretaHours" class="form-label">Timer Duration</label>
                                    <select class="form-select" name="etera_chereta_hours" id="garageEteraCheretaHours" aria-label="Default select example">
                                        <option value="4">4hr</option>
                                        <option value="8">8hr</option>
                                        <option value="12">12hr</option>
                                        <option value="24">24hr</option>
                                        <option value="48">48hr</option>
                                        <option value="72">72hr</option>
                                    </select>
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const proformaTypeSelect = document.getElementById('garageProformaType');
                                        const eteraCheretaDropdown = document.getElementById('garageEteraCheretaDropdown');
                                        function toggleEteraCheretaDropdown() {
                                            if (proformaTypeSelect.value === '-1') {
                                                eteraCheretaDropdown.style.display = 'block';
                                            } else {
                                                eteraCheretaDropdown.style.display = 'none';
                                            }
                                        }
                                        if (proformaTypeSelect) {
                                            proformaTypeSelect.addEventListener('change', toggleEteraCheretaDropdown);
                                            toggleEteraCheretaDropdown();
                                        }
                                    });
                                </script>
                                </div>
                                  <div class="col-12 col-lg-6">
    <label for="car_type" class="form-label">Car Type</label>
    <select name="car_type" id="car_type" required aria-label="Default select example">
        <option value="ICE" {{ old('car_type', 'ICE') == 'ICE' ? 'selected' : '' }}>ICE(Gas)</option>
        <option value="EV" {{ old('car_type') == 'EV' ? 'selected' : '' }}>EV</option>
        <option value="Hybrid" {{ old('car_type') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
    </select>

    @error('car_type')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
                                <div class="col-12 col-lg-6">
                                @php
                                    use App\Models\Brand;
                                    use App\Models\CarPart;
                                    $userIsTest = auth()->user()?->is_test ?? false;
                                    $brands = Brand::where('is_test', $userIsTest)
                                    ->orderBy('name', 'asc')
                                    ->get();
                                    $parts = CarPart::orderBy('name', 'asc')->get();
                                @endphp
                                <label for="InputCountry">Brand</label>
                                    <select name="brand_id" required id="InputCountry" aria-label="Default select example">
                                        @foreach ($brands as $brand)
                                            <option {{ old('brand_id') == $brand->id ? 'selected' : '' }} value="{{ $brand->id }}">
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>

                               <div class="col-12 col-lg-6">
                                    <label for="PhoneNumber" class="form-label">Model</label>
                                    <input type="text" name="model" required class="form-control" id="PhoneNumber" placeholder="Example: Yaris" value="{{old('model')}}">
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label for="InputCountry" >Year</label>
                                    <select  required name="year" id="InputCountry" aria-label="Default select example">
                                       <option value="#N/A" {{ old('year') == '#N/A' ? 'selected' : '' }}>#N/A</option>

                                    @for($i = 1990; $i <= date('Y'); $i++)
                                        <option value="{{$i}}" {{ old('year') == $i ? 'selected' : '' }}">{{$i}}</option>
                                        @endfor
                                      </select>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <button type="button" class="button radius-30 ripple-effect margin-top-15" onclick="validateStep1()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
                                </div>
                            </div>
                        </div>

                        <div id="test-l-2" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger2">
                            <h5 class="mb-1">Step 2</h5>
                            <h3 class="mb-4">Enter the required Information</h3>
                            <br>
                            <div class="row g-3">
                                <div class="col-12 col-lg-6">
                                    <label for="InputEmail2" class="form-label">Phone Number</label>
                                    <input type="text" name="customer_phone_number" class="form-control" id="InputEmail2" required placeholder="Example: +251900000000 or 0900000000" value="{{old('customer_phone_number')}}">
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label for="InputPassword" class="form-label">License Plate Number (Code, City & Number)/please note this will be used as your file number/</label>
                                    <input type="text"  name="license_plate_number"class="form-control" id="InputPassword" required value="{{old('license_plate_number')}}" placeholder="Example: 2AA-12345">
                                </div>
                                <div class="col-12 col-lg-6">
    <label class="form-label">
        Chassis Number
        <small class="text-muted">(Optional)</small>
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
                                        <button type="button" class="button radius-30 gray ripple-effect margin-top-15" onclick="stepper1.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Back</button> &nbsp; &nbsp; &nbsp;
                                        <button type="button" class="button radius-30 ripple-effect margin-top-15" onclick="validateStep2()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="test-l-3" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger3">
                            <div class="repeater-form">
                                <!-- Fixed: Removed duplicate id="repeater-items" -->
                                <div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="mb-1">Spare Parts</h5>
                                            <h3 class="mb-4">Add the required spare parts</h3>
                                            <br>
                                        </div>
                                        <button type="button" id="add-repeater" class="button radius-30">Add another part</button>
                                    </div>
                                    <div id="repeater-items">
                                        <div class="repeater-item">
                                            <div class="item-content row g-3">
                                                <div class="col-12">
                                                    <span class="mb-0 font-16 mt-0"><b>Spare Part #1</b></span>
                                                </div>
                                                <div class="col-12 col-lg-4">
                                                    <label for="inputName1" class="form-label">Condition</label>
                                                    <select class="form-select" name="parts[condition][]" aria-label="Default select example" required>
                                                        <option value="">Select Condition</option>
                                                        <option value="New" selected>New</option>
                                                        <option value="Used" disabled>Used</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 col-lg-4">
                                                    <label for="inputEmail1" class="form-label">Part name and (Part Number)</label>
                                                    <input name="parts[number][]" value="{{old('parts[number][]')}}" type="text" required class="form-control" id="inputEmail1" placeholder="Axel: 0001" data-skip-name="true" data-name="email">
                                                </div>
                                                <div class="col-12 col-lg-4">
                                                    <label for="inputName1" class="form-label">Parts Grade</label>
                                                    <select class="form-select" name="parts[grade][]" aria-label="Default select example">
                                                        <option >1st grade (Original OEM)</option>
                                                        <option >2nd grade (After market)</option>
                                                        <option >3rd grade</option>
                                                        <option >4th grade (Local)</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 col-lg-3">
                                                    <label for="inputName1" class="form-label">Country Part is Manufactured</label>
                                                    <input name="parts[country][]" value="{{old('parts[country][]')}}" type="text" class="form-control" id="inputName1" placeholder="" data-name="name" required>
                                                </div>
                                                <div class="col-12 col-lg-2">
                                                    <label for="inputName1" class="form-label">Qty</label>
                                                    <input name="parts[quantity][]" value="{{old('parts[quantity][]')}}" type="number" class="form-control" id="inputName1" placeholder="" data-name="name">
                                                </div>
                                                <div class="col-12 col-lg-2">
                                                    <label for="component" class="form-label">Component</label>
                                                    <select name="parts[component][]" id="component" class="form-select" required>
                                                        <option value="">Select Component</option>
                                                        <option value="Body Parts">Body Parts</option>
                                                        <option value="Mechanical Parts">Mechanical Parts</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 col-lg-4">
                                                    <label for="part_photo_1" class="form-label">Upload Photo (Optional Max=3)</label>
                                                    <input type="file" name="parts[photo][0][]" id="part_photo_1" class="filepond-initialize" accept="image/*" multiple data-part-index="0">
                                                </div>
                                                <div class="repeater-remove-btn remove-repeater col-12 col-lg-1">
                                                    <label for="inputEmail1" class="form-label"> &nbsp</label>
                                                    <button type="button" class="remove-repeater btn btn-danger button red radius-30"><i class="icon-feather-trash-2"></i></button>
                                                </div>
                                                <div class="col-12">
                                                    <hr/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div >
                                <div class="d-flex align-items-center gap-3">
                                    <button type="button" class="button radius-30 gray ripple-effect margin-top-15" onclick="stepper1.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Back</button> &nbsp; &nbsp; &nbsp;
                                    <button type="button" class="button radius-30 ripple-effect margin-top-15" onclick="validateStep3()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
                                </div>
                            </div>
                        </div>
                        <div id="test-l-4" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger4">
                            <h5 class="mb-1">Step 4</h5>
                            <p class="mb-4">Submit The Form </p>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Voice Note (Optional)</h5>
                                            <p class="card-text">Record a voice note to provide additional information about your request.</p>
                                            <div class="d-flex voice-recorder-container">
                                                <div class="d-flex align-items-center flex-wrap gap-3 mb-3">
                                                    <button type="button" id="startRecording" class="btn btn-primary rounded-pill px-4">
                                                        <i class="bx bx-microphone me-2"></i>Start Recording
                                                    </button>
                                                    <button type="button" id="stopRecording" class="btn btn-secondary rounded-pill px-4" disabled>
                                                        <i class="bx bx-stop-circle me-2"></i>Stop Recording
                                                    </button>
                                                </div>
                                                <div id="recordingStatus" class="mb-3 voice-animate">
                                                    <div class="d-flex align-items-center">
                                                        <div class="recording-indicator"></div>
                                                        <span class="ms-2 recording-active">Recording in progress...<br>Once finished recording, please press the stop button before submitting!</span>
                                                    </div>
                                                </div>
                                                <div id="audioPreview" class="mb-3 voice-animate">
                                                    <audio id="recordedAudio" controls class="mb-2"></audio>
                                                    <button type="button" id="deleteRecording" class="btn btn-danger rounded-pill px-4 ms-3">
                                                        <i class="bx bx-trash me-2"></i>
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
                                    <button type="button" class="button radius-30 gray ripple-effect margin-top-15" onclick="stepper1.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Back</button> &nbsp; &nbsp; &nbsp;
                                    <button id="garageSubmitButton" type="submit" class="button radius-30 ripple-effect margin-top-15" onclick="document.getElementById('garageSubmitOverlay').style.display='flex'">
                                        <span class="submit-text">Submit</span>
                                        <span class="loading-text" style="display: none;">
                                            <i class="bx bx-loader-alt bx-spin me-2"></i>Creating Proforma...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <!-- Removed closing form tag since we removed opening tag -->
                </div>
            </div>
        </div>
    </div>
</form>
</div>

<!-- FilePond JS -->
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
<script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

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
    const filePondInstances = new Map(); // ensure we have a map

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
                    <input name="parts[number][]" type="text" required class="form-control" placeholder="Axel: 0001">
                </div>
                <div class="col-12 col-lg-4">
                    <label class="form-label">Grade</label>
                    <select class="form-select" name="parts[grade][]">
                        <option>1st grade (Original OEM)</option>
                        <option>2nd grade (After market)</option>
                        <option>3rd grade</option>
                        
                        <option>4th grade (Local)</option>
                    </select>
                </div>
                <div class="col-12 col-lg-3">
                    <label class="form-label">Country Part is Manufactured</label>
                    <input name="parts[country][]" type="text" class="form-control" required>
                </div>
                <div class="col-12 col-lg-2">
                    <label class="form-label">Qty</label>
                    <input name="parts[quantity][]" type="number" class="form-control">
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
                           id="part_photo_${sparePartCounter}">
                </div>
                <div class="col-12 col-lg-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="remove-repeater btn btn-danger radius-30">
                        <i class="icon-feather-trash-2"></i>
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
            const allItems = document.querySelectorAll(".repeater-item");
            if (allItems.length <= 1) {
                alert('You must have at least one spare part.');
                return;
            }
            const fileInput = repeaterItem.querySelector('input.filepond-initialize');
            if (fileInput && filePondInstances.has(fileInput.id)) {
                const pond = filePondInstances.get(fileInput.id);
                pond.destroy();
                filePondInstances.delete(fileInput.id);
            }
            repeaterItem.remove();
            updateSparePartNumbers();
            toggleDeleteButtons();
        });

        updateSparePartNumbers();
    });

    // Initialize remove buttons for existing items
    document.querySelectorAll(".remove-repeater").forEach(btn => {
        btn.addEventListener("click", function() {
            const allItems = document.querySelectorAll(".repeater-item");
            if (allItems.length <= 1) {
                alert('You must have at least one spare part.');
                return;
            }
            const repeaterItem = this.closest(".repeater-item");
            const fileInput = repeaterItem.querySelector('input.filepond-initialize');
            if (fileInput && filePondInstances.has(fileInput.id)) {
                const pond = filePondInstances.get(fileInput.id);
                pond.destroy();
                filePondInstances.delete(fileInput.id);
            }
            repeaterItem.remove();
            updateSparePartNumbers();
            toggleDeleteButtons();
        });
    });

    function updateSparePartNumbers() {
        document.querySelectorAll(".repeater-item").forEach((item, index) => {
            const span = item.querySelector("span.mb-0");
            if (span) {
                span.innerHTML = `<b>Spare Part #${index + 1}</b>`;
            }
        });
        toggleDeleteButtons();
    }

    function toggleDeleteButtons() {
        const allItems = document.querySelectorAll(".repeater-item");
        allItems.forEach((item) => {
            const deleteBtn = item.querySelector(".remove-repeater");
            if (deleteBtn) {
                if (allItems.length <= 1) {
                    deleteBtn.style.display = 'none';
                } else {
                    deleteBtn.style.display = '';
                }
            }
        });
    }

    // Initial toggle — hide delete button if only 1 part
    toggleDeleteButtons();
}

function initializeStepper() {
    // Fixed: Removed nested DOMContentLoaded since this is already called from DOMContentLoaded
    const stepperElement = document.querySelector('#stepper1');
    if (stepperElement) {
        window.stepper1 = new Stepper(stepperElement, {
            linear: true,
            animation: true
        });
        console.log('✅ Stepper initialized successfully');
    } else {
        console.error('❌ Stepper element not found');
    }

    // Etera-Chereta dropdown toggle
    const proformaTypeSelect = document.getElementById('garageProformaType');
    const eteraCheretaDropdown = document.getElementById('garageEteraCheretaDropdown');
    
    function toggleEteraCheretaDropdown() {
        if (proformaTypeSelect && eteraCheretaDropdown) {
            if (proformaTypeSelect.value === '-1') {
                eteraCheretaDropdown.style.display = 'block';
            } else {
                eteraCheretaDropdown.style.display = 'none';
            }
        }
    }
    
    if (proformaTypeSelect) {
        proformaTypeSelect.addEventListener('change', toggleEteraCheretaDropdown);
        toggleEteraCheretaDropdown();
    }
}

function initializeVoiceRecording() {
    console.log('🎤 Initializing voice recording...');
    
    let mediaRecorder;
    let audioChunks = [];
    let isRecording = false;
    const startButton = document.getElementById('startRecording');
    const stopButton = document.getElementById('stopRecording');
    const recordingStatus = document.getElementById('recordingStatus');
    const audioPreview = document.getElementById('audioPreview');
    const recordedAudio = document.getElementById('recordedAudio');
    const deleteButton = document.getElementById('deleteRecording');
    const voiceNoteInput = document.getElementById('voiceNoteInput');

    // Check if all elements exist
    if (!startButton || !stopButton) {
        console.error('❌ Voice recording buttons not found!');
        return;
    }

    // Check browser compatibility
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        console.error('❌ MediaDevices API not supported in this browser');
        alert('Voice recording is not supported in your browser. Please use a modern browser like Chrome, Firefox, or Edge.');
        startButton.disabled = true;
        startButton.title = 'Voice recording not supported in this browser';
        return;
    }

    console.log('✅ Voice recording elements found:', { startButton, stopButton });

    function startRecording() {
        console.log('▶️ Start recording clicked');
        
        if (isRecording) {
            console.warn('⚠️ Already recording');
            return;
        }
        
        navigator.mediaDevices.getUserMedia({ 
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true
            } 
        })
            .then(stream => {
                console.log('🎙️ Microphone access granted');
                
                // Try to use webm with opus codec, fallback to whatever is available
                const mimeType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus') 
                    ? 'audio/webm;codecs=opus' 
                    : 'audio/webm';
                    
                mediaRecorder = new MediaRecorder(stream, { mimeType });
                audioChunks = [];
                isRecording = true;
                
                console.log('📼 MediaRecorder created with mimeType:', mimeType);
                
                mediaRecorder.addEventListener('dataavailable', event => {
                    if (event.data.size > 0) {
                        audioChunks.push(event.data);
                        console.log('📊 Data chunk received:', event.data.size, 'bytes');
                    }
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
                
                mediaRecorder.addEventListener('error', (event) => {
                    console.error('❌ MediaRecorder error:', event.error);
                    alert('Error during recording: ' + event.error.message);
                    isRecording = false;
                    startButton.disabled = false;
                    stopButton.disabled = true;
                });
                
                mediaRecorder.start(1000); // Collect data every second
                console.log('🔴 Recording started');
                
                startButton.disabled = true;
                stopButton.disabled = false;
                if (recordingStatus) {
                    recordingStatus.classList.add('show-graceful');
                }
                if (audioPreview) {
                    audioPreview.classList.remove('show-graceful');
                }
            })
            .catch(err => {
                console.error('❌ Error accessing microphone:', err);
                let errorMsg = 'Error accessing microphone. ';
                
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    errorMsg += 'Please allow microphone permissions and try again.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    errorMsg += 'No microphone found. Please connect a microphone and try again.';
                } else {
                    errorMsg += err.message;
                }
                
                alert(errorMsg);
            });
    }

    function stopRecording() {
        console.log('⏹️ Stop recording clicked');
        
        if (!isRecording || !mediaRecorder) {
            console.warn('⚠️ Not currently recording');
            return;
        }
        
        if (mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            mediaRecorder.stream.getTracks().forEach(track => {
                track.stop();
                console.log('🛑 Stopped track:', track.kind);
            });
        }
        
        isRecording = false;
        startButton.disabled = false;
        stopButton.disabled = true;
        if (recordingStatus) {
            recordingStatus.classList.remove('show-graceful');
        }
        if (audioPreview) {
            audioPreview.classList.add('show-graceful');
        }
    }

    // Attach event listeners
    startButton.addEventListener('click', startRecording);
    stopButton.addEventListener('click', stopRecording);
    console.log('✅ Voice recording event listeners attached');

    if (deleteButton) {
        deleteButton.addEventListener('click', () => {
            console.log('🗑️ Deleting recording');
            audioChunks = [];
            if (recordedAudio) recordedAudio.src = '';
            if (voiceNoteInput) voiceNoteInput.value = '';
            if (audioPreview) {
                audioPreview.classList.remove('show-graceful');
            }
        });
    }

    // Form submission handling
    const form = document.getElementById('garageProformaForm');
    const submitButton = document.getElementById('garageSubmitButton');
    
    if (form && submitButton) {
        form.addEventListener('submit', function(e) {
            // Check if still recording
            if (isRecording) {
                e.preventDefault();
                alert('Please stop the voice recording before submitting the form.');
                return false;
            }
            
            const submitText = submitButton.querySelector('.submit-text');
            const loadingText = submitButton.querySelector('.loading-text');
            
            if (submitText && loadingText) {
                submitButton.disabled = true;
                submitText.style.display = 'none';
                loadingText.style.display = 'inline';
            }
            
            const overlay = document.getElementById('garageSubmitOverlay');
            if (overlay) overlay.style.display = 'flex';
        });
    }
}

// Validation functions
function validateForm() {
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

function validateStep1() {
    const brandId = document.querySelector('select[name="brand_id"]');
    const model = document.querySelector('input[name="model"]');
    const year = document.querySelector('select[name="year"]');
    const proformaType = document.querySelector('select[name="number_of_proformas"]');
    
    if (!brandId || !brandId.value) {
        alert('Please select a brand.');
        if (brandId) brandId.focus();
        return false;
    }
    
    if (!model || !model.value.trim()) {
        alert('Please enter the model.');
        if (model) model.focus();
        return false;
    }
    
    if (!year || !year.value) {
        alert('Please select the year.');
        if (year) year.focus();
        return false;
    }
    
    if (!proformaType || !proformaType.value) {
        alert('Please select the number of proforma invoices.');
        if (proformaType) proformaType.focus();
        return false;
    }
    
    stepper1.next();
}

function validateStep2() {
    const phoneNumber = document.querySelector('input[name="customer_phone_number"]');
    const licensePlate = document.querySelector('input[name="license_plate_number"]');
    const chassisNumber = document.querySelector('input[name="chassis_number"]');
    
    if (!phoneNumber || !phoneNumber.value.trim()) {
        alert('Please enter the phone number.');
        if (phoneNumber) phoneNumber.focus();
        return false;
    }
    
    if (!licensePlate || !licensePlate.value.trim()) {
        alert('Please enter the license plate number.');
        if (licensePlate) licensePlate.focus();
        return false;
    }
    
    
    stepper1.next();
}

function validateStep3() {
    const sparePartInputs = document.querySelectorAll('select[name="parts[condition][]"]');
    if (sparePartInputs.length === 0) {
        alert('Please add at least one spare part.');
        return false;
    }
    
    for (let i = 0; i < sparePartInputs.length; i++) {
        const conditionSelect = sparePartInputs[i];
        const partRow = conditionSelect.closest('.repeater-item');
        const partNumber = partRow ? partRow.querySelector('input[name="parts[number][]"]') : null;
        const component = partRow ? partRow.querySelector('select[name="parts[component][]"]') : null;
        
        if (!conditionSelect.value || conditionSelect.value !== 'New') {
            alert('Please select "New" condition for all spare parts.');
            conditionSelect.focus();
            return false;
        }
        
        if (!partNumber || !partNumber.value.trim()) {
            alert('Please enter part number for all spare parts.');
            if (partNumber) partNumber.focus();
            return false;
        }
        
        if (!component || !component.value) {
            alert('Please select component for all spare parts.');
            if (component) component.focus();
            return false;
        }
    }
    
    stepper1.next();
}
</script>

<!-- Rest of your existing scripts -->
<script src="{{asset('assets/plugins/bs-stepper/js/bs-stepper.min.js')}}"></script>
<script src="{{asset('assets/plugins/bs-stepper/js/main.js')}}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

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
});
</script>
<style>
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
<style>
/* Voice recording visibility toggle */
.voice-animate {
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.voice-animate.show-graceful {
    display: flex;
    align-items: center;
    opacity: 1;
}

/* Fixed: Removed duplicate .recording-indicator and @keyframes pulse */
.recording-indicator {
    width: 12px;
    height: 12px;
    background-color: #dc3545;
    border-radius: 50%;
    animation: pulse 1.5s ease-in-out infinite;
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
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    padding: 20px;
    border-radius: 12px;
    flex-direction: column;
}

.recording-active {
    color: #fca5a5;
    font-weight: 600;
    font-size: 0.9rem;
}

/* Audio player dark theme */
#audioPreview audio {
    width: 100%;
    max-width: 400px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.06);
    filter: invert(1) hue-rotate(180deg);
}

/* Delete recording button */
#deleteRecording {
    background: rgba(239, 68, 68, 0.15) !important;
    border: 1px solid rgba(239, 68, 68, 0.3) !important;
    color: #fca5a5 !important;
    transition: all 0.25s ease;
}

#deleteRecording:hover {
    background: rgba(239, 68, 68, 0.3) !important;
    color: #fff !important;
}

/* FilePond Custom Styles */
.filepond--root {
    font-family: inherit;
    margin-bottom: 0;
}
.filepond--panel-root {
    background-color: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
}
.filepond--drop-label {
    color: #6c757d;
    border-radius: 8px;
}
.filepond--drip-blob {
    background-color: var(--etera-teal);
}
.filepond--item-panel {
    background-color: var(--etera-teal);
}
.filepond--progress-indicator {
    color: var(--etera-teal);
}
</style>
@endsection
