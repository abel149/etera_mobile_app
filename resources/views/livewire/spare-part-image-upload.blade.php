<div class="spare-part-image-upload-component" id="{{ $componentId }}">
    <div class="mb-2">
        <label for="{{ $componentId }}-input" class="form-label small mb-1">
            {{ $uploadLabel }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
        
        <div class="upload-area border border-gray-300 rounded p-2 text-center hover:border-primary transition-colors" 
             style="min-height: {{ $compactMode ? '60px' : '80px' }};">
            <input 
                wire:model="images" 
                type="file" 
                id="{{ $componentId }}-input"
                name="{{ $name }}"
                class="d-none"
                multiple
                accept="image/jpeg,image/png,image/jpg,image/webp"
            >
            
            <div class="upload-content">
                <div wire:loading.remove wire:target="images">
                    <div class="upload-icon-container mb-2">
                        <div class="upload-icon {{ $compactMode ? 'compact' : '' }}">
                            <i class="bx bx-cloud-upload"></i>
                            <div class="upload-plus">+</div>
                        </div>
                    </div>
                    <p class="mb-1 text-muted small">Click to upload</p>
                    <p class="small text-muted mb-0">
                        Max: {{ $maxFiles }} images ({{ $maxFileSize/1024 }}MB each)
                    </p>
                </div>
                <div wire:loading wire:target="images" class="text-center">
                    <i class="bx bx-loader-alt bx-spin {{ $compactMode ? 'fs-4' : 'fs-3' }} text-primary mb-1"></i>
                    <p class="mb-1 text-primary small">Loading Image. Please Wait.</p>
                    <p class="small text-muted mb-0">Processing your upload...</p>
                </div>
            </div>
        </div>
        
        @error('images')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    @if($showPreview && count($temporaryImages) > 0)
        <div class="image-preview-section mb-2">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="small text-muted">{{ count($temporaryImages) }}/{{ $maxFiles }} images</span>
                @if(count($temporaryImages) > 1)
                    <button 
                        type="button" 
                        wire:click="clearAll"
                        class="btn btn-outline-danger btn-sm"
                        style="font-size: 0.7rem; padding: 0.2rem 0.4rem;"
                        title="Clear all images"
                    >
                        <i class="bx bx-trash"></i>
                    </button>
                @endif
            </div>
            
            <div class="d-flex flex-wrap gap-1">
                @foreach($temporaryImages as $index => $image)
                    <div class="image-preview-item position-relative">
                        <img 
                            src="{{ Storage::disk('local')->url($image['path']) }}" 
                            alt="{{ $image['name'] }}"
                            class="img-thumbnail"
                            style="width: {{ $previewSize }}; height: {{ $previewSize }}; object-fit: cover;"
                        >
                        <button 
                            type="button" 
                            wire:click="removeImage({{ $index }})"
                            class="btn btn-sm btn-danger position-absolute top-0 end-0"
                            style="transform: translate(50%, -50%); font-size: 0.6rem; padding: 0.1rem 0.3rem;"
                            title="Remove image"
                        >
                            <i class="bx bx-x"></i>
                        </button>
                        <div class="image-info small text-muted mt-1 text-center">
                            <div class="text-truncate" title="{{ $image['name'] }}" style="max-width: {{ $previewSize }};">
                                {{ Str::limit($image['name'], 15) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <style>
        .upload-area {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .upload-area:hover {
            border-color: #ad3ef5 !important;
            background-color: #e6e6ff;
        }
        
        .upload-area {
            background-color: #f3f0ff;
        }
        
        .upload-icon-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .upload-icon {
            position: relative;
            width: 60px;
            height: 60px;
            border: 2px dashed #ad3ef5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .upload-icon.compact {
            width: 40px;
            height: 40px;
        }
        
        .upload-icon i {
            font-size: 24px;
            color: #ad3ef5;
            transition: all 0.3s ease;
        }
        
        .upload-icon.compact i {
            font-size: 18px;
        }
        
        .upload-plus {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 20px;
            height: 20px;
            background: #ad3ef5;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(173, 62, 245, 0.3);
            transition: all 0.3s ease;
        }
        
        .upload-icon.compact .upload-plus {
            width: 16px;
            height: 16px;
            font-size: 12px;
            top: -3px;
            right: -3px;
        }
        
        .upload-area:hover .upload-icon {
            border-color: #8b2fc9;
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
            transform: scale(1.05);
        }
        
        .upload-area:hover .upload-icon i {
            color: #8b2fc9;
            transform: scale(1.1);
        }
        
        .upload-area:hover .upload-plus {
            background: #8b2fc9;
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(139, 47, 201, 0.4);
        }
        
        .image-preview-item {
            transition: transform 0.2s ease;
        }
        
        .image-preview-item:hover {
            transform: scale(1.05);
        }
        
        .image-info {
            max-width: {{ $previewSize }};
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.querySelector('#{{ $componentId }} .upload-area');
            const fileInput = document.querySelector('#{{ $componentId }}-input');
            
            if (uploadArea && fileInput) {
                // Click to upload
                uploadArea.addEventListener('click', function() {
                    fileInput.click();
                });
                
                // Drag and drop functionality
                uploadArea.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    uploadArea.classList.add('border-primary');
                    uploadArea.style.backgroundColor = '#f0f8ff';
                });
                
                uploadArea.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    uploadArea.classList.remove('border-primary');
                    uploadArea.style.backgroundColor = '';
                });
                
                uploadArea.addEventListener('drop', function(e) {
                    e.preventDefault();
                    uploadArea.classList.remove('border-primary');
                    uploadArea.style.backgroundColor = '';
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        // Create a new FileList-like object
                        const dt = new DataTransfer();
                        for (let i = 0; i < files.length; i++) {
                            if (files[i].type.startsWith('image/')) {
                                dt.items.add(files[i]);
                            }
                        }
                        fileInput.files = dt.files;
                        
                        // Trigger change event
                        const event = new Event('change', { bubbles: true });
                        fileInput.dispatchEvent(event);
                    }
                });
            }
        });
    </script>
</div>
