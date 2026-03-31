<div class="spare-parts-form-component">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Required Spare Parts</h5>
            <button type="button" wire:click="addPart" class="btn btn-primary btn-sm" 
                    @if(count($parts) >= $maxParts) disabled @endif>
                <i class="bx bx-plus me-1"></i>Add Part
            </button>
        </div>
        <div class="card-body">
            @if(count($parts) > 0)
                @foreach($parts as $index => $part)
                    <div class="part-item border rounded p-3 mb-3" wire:key="part-{{ $index }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Spare Part #{{ $index + 1 }}</h6>
                            @if(count($parts) > 1)
                                <button type="button" wire:click="removePart({{ $index }})" 
                                        class="btn btn-danger btn-sm">
                                    <i class="bx bx-trash"></i>
                                </button>
                            @endif
                        </div>
                        
                        <div class="row g-3">
                            <!-- Part Number -->
                            <div class="col-12 col-lg-4">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="parts.{{ $index }}.number" 
                                       class="form-control" placeholder="Axel: 0002" required>
                                @error("parts.{$index}.number") 
                                    <span class="text-danger small">{{ $message }}</span> 
                                @enderror
                            </div>

                            <!-- Grade -->
                            <div class="col-12 col-lg-2">
                                <label class="form-label">Grade <span class="text-danger">*</span></label>
                                <select wire:model="parts.{{ $index }}.grade" class="form-select" required>
                                    <option value="">Select Grade</option>
                                    <option value="1st grade (Original OEM)">1st grade (Original OEM)</option>
                                    <option value="2nd grade (After market)">2nd grade (After market)</option>
                                    <option value="3rd grade">3rd grade</option>
                                </select>
                                @error("parts.{$index}.grade") 
                                    <span class="text-danger small">{{ $message }}</span> 
                                @enderror
                            </div>

                            <!-- Country -->
                            <div class="col-12 col-lg-2">
                                <label class="form-label">Country (Optional)</label>
                                <input type="text" wire:model="parts.{{ $index }}.country" 
                                       class="form-control" placeholder="">
                                @error("parts.{$index}.country") 
                                    <span class="text-danger small">{{ $message }}</span> 
                                @enderror
                            </div>

                            <!-- Quantity -->
                            <div class="col-12 col-lg-1">
                                <label class="form-label">Qty <span class="text-danger">*</span></label>
                                <input type="number" wire:model="parts.{{ $index }}.quantity" 
                                       class="form-control" min="1" required>
                                @error("parts.{$index}.quantity") 
                                    <span class="text-danger small">{{ $message }}</span> 
                                @enderror
                            </div>

                            <!-- Component -->
                            <div class="col-12 col-lg-2">
                                <label class="form-label">Component <span class="text-danger">*</span></label>
                                <select wire:model="parts.{{ $index }}.component" class="form-select" required>
                                    <option value="">Select Component</option>
                                    <option value="Body Parts">Body Parts</option>
                                    <option value="Mechanical Parts">Mechanical Parts</option>
                                </select>
                                @error("parts.{$index}.component") 
                                    <span class="text-danger small">{{ $message }}</span> 
                                @enderror
                            </div>

                            <!-- Images -->
                            <div class="col-12">
                                <label class="form-label">Images (Optional)</label>
                                <div class="image-upload-section">
                                    <input type="file" wire:model="parts.{{ $index }}.images" 
                                           class="form-control" multiple accept="image/*">
                                    
                                    @if(isset($part['temporaryImages']) && count($part['temporaryImages']) > 0)
                                        <div class="image-preview-section mt-2">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="small text-muted">
                                                    {{ count($part['temporaryImages']) }} images uploaded
                                                </span>
                                                <button type="button" wire:click="clearPartImages({{ $index }})" 
                                                        class="btn btn-outline-danger btn-sm">
                                                    <i class="bx bx-trash me-1"></i>Clear All
                                                </button>
                                            </div>
                                            
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($part['temporaryImages'] as $imageIndex => $image)
                                                    <div class="image-preview-item position-relative">
                                                        <img src="{{ Storage::disk('local')->url($image['path']) }}" 
                                                             alt="{{ $image['name'] }}"
                                                             class="img-thumbnail"
                                                             style="width: 80px; height: 80px; object-fit: cover;">
                                                        <button type="button" 
                                                                wire:click="removePartImage({{ $index }}, {{ $imageIndex }})"
                                                                class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                                                style="transform: translate(50%, -50%); font-size: 0.6rem; padding: 0.1rem 0.3rem;"
                                                                title="Remove image">
                                                            <i class="bx bx-x"></i>
                                                        </button>
                                                        <div class="image-info small text-muted mt-1 text-center">
                                                            <div class="text-truncate" title="{{ $image['name'] }}" style="max-width: 80px;">
                                                                {{ Str::limit($image['name'], 15) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center text-muted py-4">
                    <i class="bx bx-package fs-1 mb-3"></i>
                    <p>No spare parts added yet. Click "Add Part" to get started.</p>
                </div>
            @endif
        </div>
    </div>

    <style>
        .part-item {
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .part-item:hover {
            background-color: #e9ecef;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .image-preview-item {
            transition: transform 0.2s ease;
        }
        
        .image-preview-item:hover {
            transform: scale(1.05);
        }
        
        .image-upload-section {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: border-color 0.3s ease;
        }
        
        .image-upload-section:hover {
            border-color: #ad3ef5;
        }
    </style>
</div>
