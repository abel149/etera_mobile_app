<div class="image-upload-component" id="{{ $componentId }}">
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bx bx-image me-2"></i>
                {{ $uploadLabel }}
                @if($required)
                    <span class="text-danger">*</span>
                @endif
            </h6>
        </div>
        <div class="card-body">
            <!-- File Input -->
            <div class="mb-3">
                <input type="file" 
                       wire:model="images" 
                       class="form-control @error('images') is-invalid @enderror"
                       name="{{ $name }}"
                       accept="image/*"
                       multiple="{{ $multiple ? 'multiple' : '' }}"
                       id="image-upload-{{ $componentId }}">
                
                @error('images')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                <small class="form-text text-muted">
                    Maximum {{ $maxFiles }} files, {{ $maxFileSize/1024 }}MB each. 
                    Supported: JPEG, PNG, JPG, WebP, GIF
                </small>
            </div>

            <!-- Progress Bar -->
            @if($showProgressBar && $isUploading)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-primary fw-bold">{{ $uploadMessage }}</span>
                        <span class="badge bg-primary">{{ $this->getTotalProgress() }}%</span>
                    </div>
                    
                    <!-- Overall Progress -->
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: {{ $this->getTotalProgress() }}%"
                             aria-valuenow="{{ $this->getTotalProgress() }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>

                    <!-- Individual File Progress -->
                    @foreach($uploadProgress as $index => $progress)
                        @if(isset($images[$index]))
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $images[$index]->getClientOriginalName() }}</small>
                                    <small class="text-muted">{{ $progress }}%</small>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-success" 
                                         role="progressbar" 
                                         style="width: {{ $progress }}%"
                                         aria-valuenow="{{ $progress }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            <!-- Success Message -->
            @if($uploadComplete)
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ $uploadMessage }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Image Previews -->
            @if(count($temporaryImages) > 0)
                <div class="row g-3">
                    @foreach($temporaryImages as $index => $image)
                        <div class="col-md-3 col-sm-4 col-6">
                            <div class="image-preview-card position-relative">
                                <img src="{{ Storage::disk('local')->url($image['path']) }}" 
                                     class="img-fluid rounded" 
                                     style="width: 100%; height: 150px; object-fit: cover;"
                                     alt="{{ $image['name'] }}">
                                
                                <div class="image-overlay">
                                    <div class="d-flex justify-content-between align-items-start p-2">
                                        <small class="text-white bg-dark bg-opacity-75 px-2 py-1 rounded">
                                            {{ number_format($image['size'] / 1024, 1) }} KB
                                        </small>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                wire:click="removeImage({{ $index }})"
                                                title="Remove Image">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mt-2">
                                    <small class="text-muted d-block text-truncate" title="{{ $image['name'] }}">
                                        {{ $image['name'] }}
                                    </small>
                                    <small class="text-muted">
                                        {{ $image['uploaded_at'] ? $image['uploaded_at']->diffForHumans() : 'Just now' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Clear All Button -->
                <div class="mt-3">
                    <button type="button" 
                            class="btn btn-outline-danger btn-sm" 
                            wire:click="clearAll">
                        <i class="bx bx-trash me-1"></i>
                        Clear All Images
                    </button>
                </div>
            @endif

            <!-- Upload Stats -->
            @if(count($temporaryImages) > 0)
                <div class="mt-3 p-3 bg-light rounded">
                    <div class="row text-center">
                        <div class="col">
                            <h6 class="mb-0">{{ count($temporaryImages) }}</h6>
                            <small class="text-muted">Images</small>
                        </div>
                        <div class="col">
                            <h6 class="mb-0">{{ number_format(array_sum(array_column($temporaryImages, 'size')) / 1024 / 1024, 2) }} MB</h6>
                            <small class="text-muted">Total Size</small>
                        </div>
                        <div class="col">
                            <h6 class="mb-0">{{ $maxFiles - count($temporaryImages) }}</h6>
                            <small class="text-muted">Remaining</small>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .image-preview-card {
            transition: transform 0.2s;
        }
        
        .image-preview-card:hover {
            transform: translateY(-2px);
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), transparent);
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .image-preview-card:hover .image-overlay {
            opacity: 1;
        }
        
        .progress {
            border-radius: 10px;
        }
        
        .progress-bar {
            border-radius: 10px;
        }
    </style>

    <script>
        // Auto-hide success message after 5 seconds
        document.addEventListener('livewire:init', () => {
            Livewire.on('hide-success-message', () => {
                setTimeout(() => {
                    const alerts = document.querySelectorAll('.alert-success');
                    alerts.forEach(alert => {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    });
                }, 5000);
            });
        });
    </script>
</div>
