@extends('layouts.admin')
@section('content')
<!-- start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Add Insurance</h5>
                <hr/>
                @if ($errors->has('error'))
                    <div class="alert alert-danger">
                        {{ $errors->first('error') }}
                    </div>
                @endif
                <form class="row g-3" action="{{ route('add-insurance') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    <!-- Name Field -->
                    <div class="col-md-6">
                        <label for="input1" class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" id="input1" placeholder="Insurance Name..." required>
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

                    <!-- Email Field -->
                    <div class="col-md-6">
                        <label for="input7" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" id="input7" placeholder="Email...">
                        @error('email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Stamp Image -->
                    <div class="col-md-6">
                        <label for="stamp_image_fp" class="form-label">Stamp Image</label>
                        <input type="file" name="stamp_image" id="stamp_image_fp" class="filepond-upload" accept="image/*" required>
                        <div id="stamp_image_fp_progress" style="display:none;" class="mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="upload-progress-text text-muted">Loading...</small>
                                <small class="upload-progress-pct text-muted fw-bold">0%</small>
                            </div>
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated upload-progress-bar" role="progressbar" style="width:0%"></div>
                            </div>
                        </div>
                        @error('stamp_image')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Custom Pricing (optional) -->
                    <div class="col-12">
                        <hr/>
                        <h6 class="text-muted mb-3">Custom Proforma Pricing <small class="fw-normal">(leave blank to use global default)</small></h6>
                    </div>
                    <div class="col-md-6">
                        <label for="insured_cost" class="form-label">Insured Cost (ETB)</label>
                        <input type="number" step="0.01" min="0" name="insured_cost" id="insured_cost" class="form-control" placeholder="e.g. 1150.00" value="{{ old('insured_cost') }}">
                        @error('insured_cost')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="insurance_proforma" class="form-label">Non-Insured Cost (ETB)</label>
                        <input type="number" step="0.01" min="0" name="insurance_proforma" id="insurance_proforma" class="form-control" placeholder="e.g. 2300.00" value="{{ old('insurance_proforma') }}">
                        @error('insurance_proforma')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <hr/>
                    <div class="my-0">
                        <button type="submit" class="btn btn-primary radius-30 px-4">Add</button>
                        &nbsp;
                        <a href="/admin/insurances" class="btn btn-outline-secondary radius-30 px-3">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end page wrapper -->

<script>
$(document).ready(function () {
    FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateType);
    document.querySelectorAll('.filepond-upload').forEach(el => {
        const pond = FilePond.create(el, {
            allowMultiple: false,
            acceptedFileTypes: ['image/*'],
            labelIdle: 'Drag & drop an image or <span class="filepond--label-action">Browse</span>',
            labelFileProcessing: 'Loading...',
            labelFileProcessingComplete: '✓ Ready',
            credits: false,
            storeAsFile: true,
            stylePanelLayout: 'compact',
            imagePreviewHeight: 150,
        });

        const progressWrap = document.getElementById(el.id + '_progress');
        const bar  = progressWrap && progressWrap.querySelector('.upload-progress-bar');
        const text = progressWrap && progressWrap.querySelector('.upload-progress-text');
        const pct  = progressWrap && progressWrap.querySelector('.upload-progress-pct');

        pond.on('addfilestart', () => {
            if (!progressWrap) return;
            progressWrap.style.display = 'block';
            bar.className = 'progress-bar progress-bar-striped progress-bar-animated upload-progress-bar';
            bar.style.width = '0%';
            text.textContent = 'Loading...';
            pct.textContent = '0%';
            let val = 0;
            el._uploadInterval = setInterval(() => {
                val = Math.min(val + 8, 85);
                bar.style.width = val + '%';
                pct.textContent = val + '%';
                if (val >= 85) clearInterval(el._uploadInterval);
            }, 40);
        });

        pond.on('addfile', (error) => {
            if (!progressWrap) return;
            clearInterval(el._uploadInterval);
            if (error) {
                bar.className = 'progress-bar upload-progress-bar bg-danger';
                bar.style.width = '100%';
                text.textContent = 'Failed to load';
                pct.textContent = '';
            } else {
                bar.className = 'progress-bar upload-progress-bar bg-success';
                bar.style.width = '100%';
                text.textContent = '✓ Image ready';
                pct.textContent = '100%';
            }
        });

        pond.on('removefile', () => {
            if (!progressWrap) return;
            clearInterval(el._uploadInterval);
            progressWrap.style.display = 'none';
            bar.style.width = '0%';
            bar.className = 'progress-bar progress-bar-striped progress-bar-animated upload-progress-bar';
            text.textContent = 'Loading...';
            pct.textContent = '0%';
        });
    });
});
</script>

@endsection
