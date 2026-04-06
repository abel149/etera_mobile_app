<div class="role-based-media-viewer">
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bx bx-images me-2"></i>
                Media Files
                <small class="text-muted">(Role: {{ ucfirst($userRole) }})</small>
            </h6>
        </div>
        <div class="card-body">
            <!-- Images Section -->
            @if($canViewMedia('images') && count($mediaData['images']) > 0)
                <div class="media-section mb-4">
                    <h6 class="mb-3">
                        <i class="bx bx-image text-primary me-2"></i>
                        Spare Part Images
                    </h6>
                    
                    <div class="row g-3">
                        @foreach($mediaData['images'] as $image)
                            <div class="col-md-4 col-sm-6">
                                <div class="media-card">
                                    <div class="media-preview">
                                        <img src="{{ Storage::disk('public')->url($image['path']) }}" 
                                             class="img-fluid rounded" 
                                             style="width: 100%; height: 200px; object-fit: cover;"
                                             alt="{{ $image['name'] }}">
                                        
                                        <div class="media-overlay">
                                            <div class="d-flex justify-content-between align-items-start p-2">
                                                <span class="badge bg-dark bg-opacity-75">
                                                    {{ $this->formatFileSize($image['size']) }}
                                                </span>
                                                <a href="{{ Storage::disk('public')->url($image['path']) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-light">
                                                    <i class="bx bx-external-link"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="media-info p-3">
                                        <h6 class="mb-1 text-truncate" title="{{ $image['name'] }}">
                                            {{ $image['name'] }}
                                        </h6>
                                        <small class="text-muted d-block">
                                            <i class="bx bx-user me-1"></i>
                                            {{ $image['uploaded_by'] }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="bx bx-time me-1"></i>
                                            {{ $image['uploaded_at']->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Voice Notes Section -->
            @if($canViewMedia('voice_notes') && count($mediaData['voice_notes']) > 0)
                <div class="media-section mb-4">
                    <h6 class="mb-3">
                        <i class="bx bx-music text-success me-2"></i>
                        Voice Notes
                    </h6>
                    
                    <div class="row g-3">
                        @foreach($mediaData['voice_notes'] as $voiceNote)
                            <div class="col-md-6">
                                <div class="media-card">
                                    <div class="media-preview bg-light p-3 text-center">
                                        <i class="bx bx-music fs-1 text-success mb-3"></i>
                                        <h6 class="mb-1">{{ $voiceNote['name'] }}</h6>
                                        <small class="text-muted">
                                            {{ $this->formatFileSize($voiceNote['size']) }} • 
                                            {{ $this->formatDuration($voiceNote['duration']) }}
                                        </small>
                                    </div>
                                    
                                    <div class="media-info p-3">
                                        <div class="mb-3">
                                            <audio controls class="w-100">
                                                <source src="{{ Storage::disk('public')->url($voiceNote['path']) }}" type="{{ $voiceNote['type'] }}">
                                                Your browser does not support the audio element.
                                            </audio>
                                        </div>
                                        
                                        <small class="text-muted d-block">
                                            <i class="bx bx-user me-1"></i>
                                            {{ $voiceNote['uploaded_by'] }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="bx bx-time me-1"></i>
                                            {{ $voiceNote['uploaded_at']->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Stamp Images Section -->
            @if($canViewMedia('stamp_images') && count($mediaData['stamp_images']) > 0)
                <div class="media-section mb-4">
                    <h6 class="mb-3">
                        <i class="bx bx-stamp text-warning me-2"></i>
                        Business Stamps & Licenses
                    </h6>
                    
                    <div class="row g-3">
                        @foreach($mediaData['stamp_images'] as $stamp)
                            <div class="col-md-4 col-sm-6">
                                <div class="media-card">
                                    <div class="media-preview">
                                        <img src="{{ Storage::disk('public')->url($stamp['path']) }}" 
                                             class="img-fluid rounded" 
                                             style="width: 100%; height: 200px; object-fit: cover;"
                                             alt="{{ $stamp['name'] }}">
                                        
                                        <div class="media-overlay">
                                            <div class="d-flex justify-content-between align-items-start p-2">
                                                <span class="badge bg-warning bg-opacity-75">
                                                    {{ $this->formatFileSize($stamp['size']) }}
                                                </span>
                                                <a href="{{ Storage::disk('public')->url($stamp['path']) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-light">
                                                    <i class="bx bx-external-link"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="media-info p-3">
                                        <h6 class="mb-1 text-truncate" title="{{ $stamp['name'] }}">
                                            {{ $stamp['name'] }}
                                        </h6>
                                        <small class="text-muted d-block">
                                            <i class="bx bx-building me-1"></i>
                                            {{ $stamp['uploaded_by'] }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="bx bx-time me-1"></i>
                                            {{ $stamp['uploaded_at']->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Documents Section -->
            @if($canViewMedia('documents') && count($mediaData['documents']) > 0)
                <div class="media-section mb-4">
                    <h6 class="mb-3">
                        <i class="bx bx-file text-info me-2"></i>
                        Documents
                    </h6>
                    
                    <div class="row g-3">
                        @foreach($mediaData['documents'] as $document)
                            <div class="col-md-6">
                                <div class="media-card">
                                    <div class="media-preview bg-light p-3 text-center">
                                        <i class="{{ $this->getFileIcon($document['type']) }} fs-1 mb-3"></i>
                                        <h6 class="mb-1">{{ $document['name'] }}</h6>
                                        <small class="text-muted">
                                            {{ $this->formatFileSize($document['size']) }}
                                        </small>
                                    </div>
                                    
                                    <div class="media-info p-3">
                                        <div class="mb-3">
                                            <a href="{{ Storage::disk('public')->url($document['path']) }}" 
                                               target="_blank" 
                                               class="btn btn-primary btn-sm w-100">
                                                <i class="bx bx-download me-1"></i>
                                                View Document
                                            </a>
                                        </div>
                                        
                                        <small class="text-muted d-block">
                                            <i class="bx bx-user me-1"></i>
                                            {{ $document['uploaded_by'] }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="bx bx-time me-1"></i>
                                            {{ $document['uploaded_at']->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- No Media Message -->
            @if(empty(array_filter($mediaData)))
                <div class="text-center py-5">
                    <i class="bx bx-folder-open fs-1 text-muted mb-3"></i>
                    <h6 class="text-muted">No media files available</h6>
                    <small class="text-muted">
                        Based on your role ({{ ucfirst($userRole) }}), you don't have access to view any media files for this proforma.
                    </small>
                </div>
            @endif

            <!-- Role Information -->
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="mb-2"><i class="bx bx-info-circle me-2"></i>Media Access by Role</h6>
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Admin/Super Admin</small>
                        <span class="badge bg-success">Full Access</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Insurance</small>
                        <span class="badge bg-warning">Stamps Only</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Shop/Garage</small>
                        <span class="badge bg-info">Images & Voice</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Others</small>
                        <span class="badge bg-secondary">Limited</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .media-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .media-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .media-preview {
            position: relative;
            overflow: hidden;
        }
        
        .media-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), transparent);
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .media-card:hover .media-overlay {
            opacity: 1;
        }
        
        .media-info {
            background: white;
        }
        
        .media-section {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 1rem;
        }
        
        .media-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
    </style>
</div>
