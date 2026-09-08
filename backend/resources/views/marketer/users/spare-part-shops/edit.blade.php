{{-- @extends('layouts.admin')
@section('content')
<!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">              
                <div class="card">
                  
                  <div class="card-body p-4">
                    
                      <h5 class="card-title">Edit Spare Parts Shop</h5>
                      <hr/>
                       <form class="row g-3" action="{{route('edit-shop')}}" method="POST">
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
                                    <div class="col-md-12">
                                        <label for="multiple-select-clear-field" class="form-label">Car Brands To Serve</label>
                                        <select required name="brands[]" class="form-select" id="multiple-select-clear-field" data-placeholder="Add Brands..." multiple>
                                            @foreach($brands as $brand)
                                            <option value="{{$brand->id}}">{{$brand->name}}</option>
                                       @endforeach
                                        </select>
                                    </div>
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
                                     @error('pasword')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-4">
                                        <label for="input9" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="input9" placeholder="Confirm Password">
                                    </div>
                                    <hr/>
                                    <div class="my-0">
                                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Spare Part Shop Updated Successfully')"> Update
                                        </button>
                                        &nbsp
                                        <a href="/admin/spare-part-shops" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                                        </a>
                                    </div>
                                </form>
                  </div>
              </div>


            </div>
        </div>
        <!--end page wrapper -->
@endsection --}}
{{-- @extends('layouts.marketer')

@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">              
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Edit Spare Parts Shop</h5>
                <hr/>
                <form class="row g-3" action="{{ route('update-shop.marketer', $shop->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="col-md-6">
                        <label for="input1" class="form-label">Name</label>
                        <input name="name" type="text" class="form-control" id="input1" placeholder="Your Company" value="{{ old('name', $shop->name) }}">
                    </div>
                    @error('name')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input2" class="form-label">Phone Number</label>
                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09..." value="{{ old('phone_number', $shop->phone_number) }}">
                    </div>
                    @error('phone_number')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input3" class="form-label">Tin #</label>
                        <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Your Company Tin #" value="{{ old('tin_number', $shop->tin_number) }}">
                    </div>
                    @error('tin_number')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input4" class="form-label">Location / Address</label>
                        <input name="location" type="text" class="form-control" id="input4" placeholder="" value="{{ old('location', $shop->location) }}">
                    </div>
                    @error('location')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                   
            
                    <div>
                        <label for="multiple-select-clear-field" class="form-label">Car Brands To Serve</label>
                        <select required name="brands[]" class="form-select" id="multiple-select-clear-field" data-placeholder="Add Brands..." multiple>
                            @foreach ($allBrands as $brand)
                                <option value="{{ $brand->id }}" 
                                        @if(in_array($brand->id, $brands)) selected @endif>
                                    {{ $brand->name }}
                                </option>
                            @endforeach



                            
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="input6" class="form-label">Business License Proc. Number</label>
                        <input name="business_license_number" type="text" class="form-control" id="input6" placeholder="Proclamation Number" value="{{ old('business_license_number', $shop->business_license_number) }}">
                    </div>
                    @error('business_license_number')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input7" class="form-label">Business License Expiry Date</label>
                        <input name="license_expire_date" type="date" class="form-control" id="input7" placeholder="Select Date" value="{{ old('license_expire_date', $shop->license_expire_date) }}">
                    </div>
                    @error('license_expire_date')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-4">
                        <label for="input8" class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" id="input8" placeholder="Your Email" value="{{ old('email', $shop->email) }}">
                    </div>
                    @error('email')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-4">
                        <label for="input9" class="form-label">Password</label>
                        <input name="password" type="password" class="form-control" id="input9" placeholder="********">
                    </div>
                    @error('password')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-4">
                        <label for="input10" class="form-label">Confirm Password</label>
                        <input name="password_confirmation" type="password" class="form-control" id="input10" placeholder="Confirm Password">
                    </div>

                    <!-- Dealer Checkbox -->
                    <div class="col-md-6">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="dealers" id="dealers" value="1" {{ $shop->dealers ?? 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="dealers">
                                Is Dealer
                            </label>
                        </div>
                    </div>

                    <!-- Dual Service Checkbox -->
                    <div class="col-md-6">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="shop_garage" id="shop_garage" value="1" {{ $shop->shop_garage ?? 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="shop_garage">
                                Dual Service (Shop + Garage)
                            </label>
                        </div>
                    </div>

                    <hr/>

                    <div class="my-0">
                        <button type="submit" class="btn btn-primary radius-30 px-4">Update</button>
                        &nbsp;
                        <a href="{{ url('/marketer/spare-part-shops') }}" type="button" class="btn btn-outline-secondary radius-30 px-3">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->
@endsection --}}


@extends('layouts.marketer')

@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">              
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Edit Spare Parts Shop</h5>
                <hr/>
                <form class="row g-3" action="{{ route('update-shop.marketer', $shop->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <!-- Shop Details -->
                    <div class="col-md-6">
                        <label for="input1" class="form-label">Name</label>
                        <input name="name" type="text" class="form-control" id="input1" placeholder="Your Company" value="{{ old('name', $shop->name) }}">
                    </div>
                    @error('name')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Phone Number -->
                    <div class="col-md-6">
                        <label for="input2" class="form-label">Phone Number</label>
                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09..." value="{{ old('phone_number', $shop->phone_number) }}">
                    </div>
                    @error('phone_number')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Tin Number -->
                    <div class="col-md-6">
                        <label for="input3" class="form-label">Tin #</label>
                        <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Your Company Tin #" value="{{ old('tin_number', $shop->tin_number) }}">
                    </div>
                    @error('tin_number')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Location -->
                    <div class="col-md-6">
                        <label for="input4" class="form-label">Location / Address</label>
                        <input name="location" type="text" class="form-control" id="input4" value="{{ old('location', $shop->location) }}">
                    </div>
                    @error('location')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Car Brands -->
                    <div class="col-md-6">
                        <label for="multiple-select-clear-field" class="form-label">Car Brands To Serve</label>
                        <select required name="brands[]" class="form-select" id="multiple-select-clear-field" data-placeholder="Add Brands..." multiple>
                            @foreach ($allBrands as $brand)
                                <option value="{{ $brand->id }}" 
                                        @if(in_array($brand->id, $brands)) selected @endif>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- <!-- Business License Number -->
                    <div class="col-md-6">
                        <label for="input6" class="form-label">Business License Proc. Number</label>
                        <input name="business_license_number" type="text" class="form-control" id="input6" value="{{ old('business_license_number', $shop->business_license_number) }}">
                    </div>
                    @error('business_license_number')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Business License Expiry Date -->
                    <div class="col-md-6">
                        <label for="input7" class="form-label">Business License Expiry Date</label>
                        <input name="license_expire_date" type="date" class="form-control" id="input7" value="{{ old('license_expire_date', $shop->license_expire_date) }}">
                    </div>
                    @error('license_expire_date')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror --}}






                    <!-- Email -->
                    <div class="col-md-4">
                        <label for="input8" class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" id="input8" value="{{ old('email', $shop->email) }}">
                    </div>
                    @error('email')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Password -->
                    <div class="col-md-4">
                        <label for="input9" class="form-label">Password</label>
                        <input name="password" type="password" class="form-control" id="input9" placeholder="********">
                    </div>
                    @error('password')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Confirm Password -->
                    <div class="col-md-4">
                        <label for="input10" class="form-label">Confirm Password</label>
                        <input name="password_confirmation" type="password" class="form-control" id="input10" placeholder="Confirm Password">
                    </div>

                    <!-- Business License Image Upload (FilePond) -->
                    <div class="col-md-6">
                        <label class="form-label">Business License Image</label>
                        @if($shop->license_image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $shop->license_image) }}" alt="Current License" class="img-thumbnail" style="max-height: 120px;">
                                <small class="d-block text-muted mt-1">Current image (upload new to replace)</small>
                            </div>
                        @endif
                        <input type="file" class="filepond-license" accept="image/png, image/jpeg, image/jpg">
                        <input type="hidden" id="license_image_data" name="license_image_data" value="">
                        @error('license_image_data')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Stamp Image Upload (FilePond) -->
                    <div class="col-md-6">
                        <label class="form-label">Stamp Image</label>
                        @if($shop->stamp_image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $shop->stamp_image) }}" alt="Current Stamp" class="img-thumbnail" style="max-height: 120px;">
                                <small class="d-block text-muted mt-1">Current image (upload new to replace)</small>
                            </div>
                        @endif
                        <input type="file" class="filepond-stamp" accept="image/png, image/jpeg, image/jpg">
                        <input type="hidden" id="stamp_image_data" name="stamp_image_data" value="">
                        @error('stamp_image_data')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <hr/>
                    <div class="my-0">
                        <button type="submit" class="btn btn-primary radius-30 px-4">Update</button>
                        &nbsp;
                        <a href="{{ url('/marketer/spare-part-shops') }}" type="button" class="btn btn-outline-secondary radius-30 px-3">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->

{{-- FilePond CSS --}}
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">

{{-- FilePond JS --}}
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
<script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

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
    licensePond.on('processfile', (error, file) => { if (!error) document.getElementById('license_image_data').value = file.serverId; });
    licensePond.on('removefile', () => { document.getElementById('license_image_data').value = ''; });

    const stampPond = FilePond.create(document.querySelector('.filepond-stamp'), pondOptions);
    stampPond.on('processfile', (error, file) => { if (!error) document.getElementById('stamp_image_data').value = file.serverId; });
    stampPond.on('removefile', () => { document.getElementById('stamp_image_data').value = ''; });
});
</script>
@endsection
