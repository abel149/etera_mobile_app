{{-- @extends('layouts.admin')
@section('content')
<!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">              
                <div class="card">
                  <div class="card-body p-4">
                      <h5 class="card-title">Edit Garage</h5>
                      <hr/>
                       <form class="row g-3" action="{{route('edit-garage')}}" method="POST">
                        @csrf
                        @method('POST')
                                    <div class="col-md-6">
                                        <label for="input1" class="form-label">Name</label>
                                        <input name="name" type="text" class="form-control" id="input1" placeholder="Your Company">
                                    </div>
                                     @error('name')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-6">
                                        <label for="input2" class="form-label">Phone Number</label>
                                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09...">
                                    </div>
                                     @error('phone_number')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-6">
                                        <label for="input3" class="form-label">Tin #</label>
                                        <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Your Company Tin #">
                                    </div>
                                     @error('tin_number')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-6">
                                        <label for="input4" class="form-label">Location / Address</label>
                                        <input name="location" type="text" class="form-control" id="input4" placeholder="">
                                    </div>
                                     @error('location')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-6">
                                        <label for="input6" class="form-label">Business License Proc. Number</label>
                                        <input name="business_license_number" type="text" class="form-control" id="input6" placeholder="Proclamation Number">
                                    </div>
                                     @error('business_license_number')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-6">
                                        <label for="input6" class="form-label">Business License Expiry Date</label>
                                        <input name="license_expire_date" type="date" class="form-control" id="input6" placeholder="Select Date">
                                    </div>
                                     @error('license_expire_date')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-4">
                                        <label for="input7" class="form-label">Email</label>
                                        <input name="email" type="email" class="form-control" id="input7" placeholder="Your Email">
                                    </div>
                                    @error('email')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-4">
                                        <label for="input8" class="form-label">Password</label>
                                        <input name="password" type="password" class="form-control" id="input8" placeholder="********">
                                    </div>
                                    @error('password')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-4">
                                        <label for="input9" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="input9" placeholder="Confirm Password">
                                    </div>
                                   <!--  <div class="col-md-12">
                                        <label for="inputProductDescription" class="form-label">Business License Image</label>
                                <input id="image-uploadify" type="file" accept="image/*,.pdf" multiple>
                                    </div> -->
                                    <hr/>
                                    <div class="my-0">
                                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Garage Updated Successfully')"> Update
                                        </button>
                                        &nbsp
                                        <a href="/admin/garages" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                                        </a>
                                    </div>
                                                
                                </form>
                  </div>
              </div>


            </div>
        </div>
        <!--end page wrapper -->
@endsection --}}
{{-- @extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Edit Garage</h5>
                <hr/>
                <form class="row g-3" action="{{ route('update-garage', $garage->id) }}" method="POST">
                    @csrf
                    @method('PUT') <!-- Method for updating -->
                    
                    <div class="col-md-6">
                        <label for="input1" class="form-label">Name</label>
                        <input name="name" type="text" class="form-control" id="input1" placeholder="Your Company" value="{{ old('name', $garage->name) }}">
                    </div>
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input2" class="form-label">Phone Number</label>
                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09..." value="{{ old('phone_number', $garage->phone_number) }}">
                    </div>
                    @error('phone_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input3" class="form-label">Tin #</label>
                        <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Your Company Tin #" value="{{ old('tin_number', $garage->tin_number) }}">
                    </div>
                    @error('tin_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input4" class="form-label">Location / Address</label>
                        <input name="location" type="text" class="form-control" id="input4" placeholder="Location" value="{{ old('location', $garage->location) }}">
                    </div>
                    @error('location')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input6" class="form-label">Business License Proc. Number</label>
                        <input name="business_license_number" type="text" class="form-control" id="input6" placeholder="Proclamation Number" value="{{ old('business_license_number', $garage->business_license_number) }}">
                    </div>
                    @error('business_license_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input6" class="form-label">Business License Expiry Date</label>
                        <input name="license_expire_date" type="date" class="form-control" id="input6" value="{{ old('license_expire_date', $garage->license_expire_date) }}">
                    </div>
                    @error('license_expire_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-4">
                        <label for="input7" class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" id="input7" placeholder="Your Email" value="{{ old('email', $garage->email) }}">
                    </div>
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-4">
                        <label for="input8" class="form-label">Password</label>
                        <input name="password" type="password" class="form-control" id="input8" placeholder="********">
                    </div>
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-4">
                        <label for="input10" class="form-label">Confirm Password</label>
                        <input name="password_confirmation" type="password" class="form-control" id="input10" placeholder="Confirm Password">
                    </div>



                    <hr/>
                    <div class="my-0">
                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Garage Updated Successfully')"> Update
                        </button>
                        &nbsp;
                        <a href="/admin/garages" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->
@endsection --}}



@extends('layouts.admin')

@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Edit Garage</h5>
                <hr/>
                <form class="row g-3" action="{{ route('update-garage', $garage->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') <!-- Method for updating -->
                    
                    <div class="col-md-6">
                        <label for="input1" class="form-label">Name</label>
                        <input name="name" type="text" class="form-control" id="input1" placeholder="Your Company" value="{{ old('name', $garage->name) }}">
                    </div>
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input2" class="form-label">Phone Number</label>
                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09..." value="{{ old('phone_number', $garage->phone_number) }}">
                    </div>
                    @error('phone_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input3" class="form-label">Tin #</label>
                        <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Your Company Tin #" value="{{ old('tin_number', $garage->tin_number) }}">
                    </div>
                    @error('tin_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input4" class="form-label">Location / Address</label>
                        <input name="location" type="text" class="form-control" id="input4" placeholder="Location" value="{{ old('location', $garage->location) }}">
                    </div>
                    @error('location')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    {{-- <div class="col-md-6">
                        <label for="input6" class="form-label">Business License Proc. Number</label>
                        <input name="business_license_number" type="text" class="form-control" id="input6" placeholder="Proclamation Number" value="{{ old('business_license_number', $garage->business_license_number) }}">
                    </div>
                    @error('business_license_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input6" class="form-label">Business License Expiry Date</label>
                        <input name="license_expire_date" type="date" class="form-control" id="input6" value="{{ old('license_expire_date', $garage->license_expire_date) }}">
                    </div>
                    @error('license_expire_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror --}}

                    <div class="col-md-4">
                        <label for="input7" class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" id="input7" placeholder="Your Email" autocomplete="off" value="{{ old('email', filter_var($garage->email, FILTER_VALIDATE_EMAIL) ? $garage->email : '') }}">
                    </div>
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <hr/>
                    
                    <!-- File Inputs for Business License and Stamp Images (FilePond) -->
                    <div class="col-md-6">
                        <label class="form-label">Business License Image</label>
                        @if($garage->license_image)
                            @php $licenseUrl = asset('storage/' . (str_starts_with($garage->license_image, 'public/') ? substr($garage->license_image, 7) : $garage->license_image)); @endphp
                            <div class="mb-2" id="licensePreview">
                                <a href="{{ $licenseUrl }}" target="_blank">
                                    <img src="{{ $licenseUrl }}" alt="Current License" class="img-thumbnail" style="max-height: 120px;">
                                </a>
                                <div class="mt-1 d-flex gap-2">
                                    <a href="{{ $licenseUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-show"></i> View
                                    </a>
                                    <a href="{{ $licenseUrl }}" download class="btn btn-sm btn-outline-secondary">
                                        <i class="bx bx-download"></i> Download
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('license')">
                                        <i class="bx bx-trash"></i> Remove
                                    </button>
                                </div>
                                <small class="d-block text-muted mt-1">Current image (upload new to replace)</small>
                            </div>
                        @endif
                        <input type="file" class="filepond-license" accept="image/png, image/jpeg, image/jpg">
                        <input type="hidden" id="license_image_data" name="license_image_data" value="">
                        <input type="hidden" id="remove_license_image" name="remove_license_image" value="">
                        @error('license_image_data')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Stamp Image</label>
                        @if($garage->stamp_image)
                            @php $stampUrl = asset('storage/' . (str_starts_with($garage->stamp_image, 'public/') ? substr($garage->stamp_image, 7) : $garage->stamp_image)); @endphp
                            <div class="mb-2" id="stampPreview">
                                <a href="{{ $stampUrl }}" target="_blank">
                                    <img src="{{ $stampUrl }}" alt="Current Stamp" class="img-thumbnail" style="max-height: 120px;">
                                </a>
                                <div class="mt-1 d-flex gap-2">
                                    <a href="{{ $stampUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-show"></i> View
                                    </a>
                                    <a href="{{ $stampUrl }}" download class="btn btn-sm btn-outline-secondary">
                                        <i class="bx bx-download"></i> Download
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('stamp')">
                                        <i class="bx bx-trash"></i> Remove
                                    </button>
                                </div>
                                <small class="d-block text-muted mt-1">Current image (upload new to replace)</small>
                            </div>
                        @endif
                        <input type="file" class="filepond-stamp" accept="image/png, image/jpeg, image/jpg">
                        <input type="hidden" id="stamp_image_data" name="stamp_image_data" value="">
                        <input type="hidden" id="remove_stamp_image" name="remove_stamp_image" value="">
                        @error('stamp_image_data')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Dual Service Checkbox -->
                    <input type="hidden" name="shop_garage_form" value="1">
                    <div class="col-md-6">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="shop_garage" id="shop_garage" value="1" {{ old('shop_garage', $garage->shop_garage) ? 'checked' : '' }}>
                            <label class="form-check-label" for="shop_garage">
                                Dual Service (Shop + Garage)
                            </label>
                        </div>
                    </div>

                    <div class="my-0">
                        <button type="submit" class="btn btn-primary radius-30 px-4"> Update
                        </button>
                        &nbsp;
                        <a href="/admin/garages" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->

<style>
    .filepond--root { margin-bottom: 0; }
    .filepond--drop-label { min-height: 100px; border-radius: 8px; border: 2px dashed rgba(40,167,69,0.35); background: linear-gradient(135deg, #f9fafb 0%, #f1f8e9 100%); }
    .filepond--drop-label:hover { border-color: #28a745; background: linear-gradient(135deg, #fff 0%, #e8f5e9 100%); }
    .filepond--drop-label label { padding: 1em; cursor: pointer; font-size: 0.85rem; color: #6b7280; }
    .filepond--label-action { text-decoration: none !important; color: #28a745; font-weight: 600; background: rgba(40,167,69,0.08); padding: 4px 12px; border-radius: 20px; }
    .filepond--panel-root { background: transparent; border-radius: 8px; }
    .filepond--item-panel { background: linear-gradient(135deg, #28a745, #20c997) !important; border-radius: 8px !important; }
    .filepond--image-preview-wrapper { border-radius: 6px !important; overflow: hidden; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateType, FilePondPluginFileValidateSize);

    const serverConfig = {
        process: {
            url: '{{ route("upload.part.image") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            onload: (response) => { const data = JSON.parse(response); if (data.success && data.files && data.files.length > 0) return data.files[0].temp_path; return 'Upload failed.'; },
            onerror: (response) => { try { return JSON.parse(response).message || 'Upload error.'; } catch(e) { return 'Upload error.'; } },
        },
        revert: {
            url: '{{ route("upload.part.image.revert") }}',
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        },
    };

    const pondOptions = {
        acceptedFileTypes: ['image/png', 'image/jpeg', 'image/jpg'],
        maxFileSize: '10MB',
        maxFiles: 1,
        server: serverConfig,
        allowRevert: true,
        imagePreviewHeight: 140,
        stylePanelLayout: 'compact',
        stylePanelAspectRatio: '3:2',
        labelIdle: '📷 Drag & Drop or <span class="filepond--label-action">Browse</span>',
        name: 'image',
    };

    const licensePond = FilePond.create(document.querySelector('.filepond-license'), pondOptions);
    licensePond.on('processfile', (error, file) => { if (!error) { document.getElementById('license_image_data').value = file.serverId; document.getElementById('remove_license_image').value = ''; } });
    licensePond.on('removefile', () => { document.getElementById('license_image_data').value = ''; });

    const stampPond = FilePond.create(document.querySelector('.filepond-stamp'), pondOptions);
    stampPond.on('processfile', (error, file) => { if (!error) { document.getElementById('stamp_image_data').value = file.serverId; document.getElementById('remove_stamp_image').value = ''; } });
    stampPond.on('removefile', () => { document.getElementById('stamp_image_data').value = ''; });
});

function removeImage(type) {
    if (!confirm('Remove this image?')) return;
    const preview = document.getElementById(type + 'Preview');
    if (preview) preview.style.display = 'none';
    document.getElementById('remove_' + type + '_image').value = '1';
}
</script>
@endsection
