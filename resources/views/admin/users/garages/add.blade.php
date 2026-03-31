@extends('layouts.admin')
@section('content')
<!-- start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Add Garage</h5>
                <hr/>
                <form class="row g-3" action="{{ route('add-garage') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    <!-- Name Field -->
                    <div class="col-md-6">
                        <label for="input1" class="form-label">Name</label>
                        <input name="name" type="text" class="form-control" id="input1" placeholder="Your Company" required>
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Phone Number Field -->
                    <div class="col-md-6">
                        <label for="input2" class="form-label">Phone Number</label>
                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09..." required>
                        @error('phone_number')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Tin Number Field -->
                    <div class="col-md-6">
                        <label for="input3" class="form-label">Tin #</label>
                        <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Your Company Tin #" required>
                        @error('tin_number')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Location Field -->
                    <div class="col-md-6">
                        <label for="input4" class="form-label">Location / Address</label>
                        <input name="location" type="text" class="form-control" id="input4" required>
                        @error('location')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Business License Image -->
                    <div class="col-md-6">
                        <label for="license_image_fp" class="form-label">Business License Image</label>
                        <input type="file" name="license_image" id="license_image_fp" class="filepond" accept="image/*" required>
                        @error('license_image')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Stamp Image -->
                    <div class="col-md-6">
                        <label for="stamp_image_fp" class="form-label">Stamp Image</label>
                        <input type="file" name="stamp_image" id="stamp_image_fp" class="filepond" accept="image/*" required>
                        @error('stamp_image')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div class="col-md-4">
                        <label for="input7" class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" id="input7" placeholder="Your Email">
                        @error('email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <hr/>
                    <div class="my-0">
                        <button type="submit" class="btn btn-primary radius-30 px-4">Add</button>
                        &nbsp;
                        <a href="/admin/garages" class="btn btn-outline-secondary radius-30 px-3">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end page wrapper -->

<link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
<script src="https://unpkg.com/filepond/dist/filepond.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateType);

    document.querySelectorAll('.filepond').forEach(el => {
        FilePond.create(el, {
            allowMultiple: false,
            acceptedFileTypes: ['image/*'],
            labelIdle: 'Drag & drop an image or <span class="filepond--label-action">Browse</span>',
            credits: false,
            storeAsFile: true,
            stylePanelLayout: 'compact',
            imagePreviewHeight: 150,
        });
    });
});
</script>

@endsection
