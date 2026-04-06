<div class="file-upload-component" id="{{ $componentId }}">
    <div class="mb-3">
        <label for="{{ $componentId }}-input" class="form-label">
            {{ $uploadLabel }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
        
        <div class="upload-area border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors">
            <input 
                wire:model="files" 
                type="file" 
                id="{{ $componentId }}-input"
                name="{{ $name }}"
                class="d-none"
                @if($multiple) multiple @endif
                accept="{{ implode(',', $acceptedTypes) }}"
            >
            
            <div class="upload-content">
                <i class="bx bx-upload fs-1 text-muted mb-2"></i>
                <p class="mb-2 text-muted">Click to upload or drag and drop</p>
                <p class="small text-muted mb-0">
                    @if($fileType === 'all')
                        All file types supported
                    @else
                        {{ ucfirst($fileType) }} files only
                    @endif
                </p>
                <p class="small text-muted mb-0">
                    Maximum {{ $maxFiles }} files (Max: {{ $maxFileSize/1024 }}MB each)
                </p>
            </div>
        </div>
        
        @error('files')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    @if($showPreview && count($temporaryFiles) > 0)
        <div class="file-preview-section mb-3">
            <h6 class="mb-2">Uploaded Files ({{ count($temporaryFiles) }}/{{ $maxFiles }})</h6>
            <div class="row g-2">
                @foreach($temporaryFiles as $index => $file)
                    <div class="col-auto">
                        <div class="file-preview-item position-relative">
                            @if(str_starts_with($file['type'], 'image/'))
                                <img 
                                    src="{{ Storage::disk('local')->url($file['path']) }}" 
                                    alt="{{ $file['name'] }}"
                                    class="img-thumbnail"
                                    style="width: {{ $previewSize }}; height: {{ $previewSize }}; object-fit: cover;"
                                >
                            @else
                                <div class="file-icon-placeholder d-flex align-items-center justify-content-center bg-light border rounded"
                                     style="width: {{ $previewSize }}; height: {{ $previewSize }};">
                                    <i class="{{ $this->getFileIcon($file['type']) }} fs-2 text-muted"></i>
                                </div>
                            @endif
                            
                            <button 
                                type="button" 
                                wire:click="removeFile({{ $index }})"
                                class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                style="transform: translate(50%, -50%);"
                                title="Remove file"
                            >
                                <i class="bx bx-x"></i>
                            </button>
                            
                            <div class="file-info small text-muted mt-1">
                                <div class="text-truncate" title="{{ $file['name'] }}">
                                    {{ Str::limit($file['name'], 20) }}
                                </div>
                                <div>{{ $this->formatFileSize($file['size']) }}</div>
                                <div class="text-uppercase small">{{ $file['extension'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if(count($temporaryFiles) > 1)
                <button 
                    type="button" 
                    wire:click="clearAll"
                    class="btn btn-outline-danger btn-sm mt-2"
                >
                    <i class="bx bx-trash me-1"></i>Clear All
                </button>
            @endif
        </div>
    @endif

    <style>
        .upload-area {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .upload-area:hover {
            border-color: #ad3ef5 !important;
            background-color: #f8f9fa;
        }
        
        .file-preview-item {
            transition: transform 0.2s ease;
        }
        
        .file-preview-item:hover {
            transform: scale(1.05);
        }
        
        .file-info {
            max-width: {{ $previewSize }};
        }
        
        .file-icon-placeholder {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
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
                            // Check if file type is accepted
                            if (@json($acceptedTypes).includes(files[i].type) || @json($acceptedTypes).length === 0) {
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
