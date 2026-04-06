<div class="upload-demo-page">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="bx bx-upload me-2"></i>
                            Enhanced Upload Components Demo
                        </h4>
                        <p class="text-muted mb-0">Showcasing multi-file uploads, progress tracking, and role-based media viewing</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-2">
            <!-- Enhanced Image Upload -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bx bx-image me-2"></i>
                            Enhanced Image Upload
                        </h6>
                        <small class="text-muted">Multi-file with progress tracking</small>
                    </div>
                    <div class="card-body">
                        @livewire('image-upload', [
                            'name' => 'demo_images',
                            'multiple' => true,
                            'maxFiles' => 10,
                            'showPreview' => true
                        ])
                    </div>
                </div>
            </div>

            <!-- Voice Note Upload -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bx bx-microphone me-2"></i>
                            Voice Note Upload
                        </h6>
                        <small class="text-muted">Record or upload audio files</small>
                    </div>
                    <div class="card-body">
                        @livewire('voice-note-upload', [
                            'name' => 'demo_voice_note',
                            'required' => false
                        ])
                    </div>
                </div>
            </div>

            <!-- Role-Based Media Viewer -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bx bx-images me-2"></i>
                            Role-Based Media Viewer
                        </h6>
                        <small class="text-muted">Different media access based on user roles</small>
                    </div>
                    <div class="card-body">
                        @livewire('role-based-media-viewer', ['proformaId' => 1])
                    </div>
                </div>
            </div>

            <!-- Component Features -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bx bx-list-check me-2"></i>
                            Component Features Overview
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="feature-card text-center p-3">
                                    <i class="bx bx-upload fs-1 text-primary mb-3"></i>
                                    <h6>Multi-File Upload</h6>
                                    <small class="text-muted">
                                        Upload multiple files simultaneously with individual progress tracking
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="feature-card text-center p-3">
                                    <i class="bx bx-trending-up fs-1 text-success mb-3"></i>
                                    <h6>Progress Tracking</h6>
                                    <small class="text-muted">
                                        Real-time progress bars from 0-100% with success notifications
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="feature-card text-center p-3">
                                    <i class="bx bx-shield fs-1 text-warning mb-3"></i>
                                    <h6>Role-Based Access</h6>
                                    <small class="text-muted">
                                        Different media visibility based on user roles and permissions
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="feature-card text-center p-3">
                                    <i class="bx bx-music fs-1 text-info mb-3"></i>
                                    <h6>Voice Recording</h6>
                                    <small class="text-muted">
                                        Built-in voice recording with timer and audio playback
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="feature-card text-center p-3">
                                    <i class="bx bx-image fs-1 text-danger mb-3"></i>
                                    <h6>Image Management</h6>
                                    <small class="text-muted">
                                        Preview, remove, and manage uploaded images with statistics
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="feature-card text-center p-3">
                                    <i class="bx bx-file fs-1 text-secondary mb-3"></i>
                                    <h6>File Validation</h6>
                                    <small class="text-muted">
                                        Comprehensive file type and size validation with error handling
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage Examples -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bx bx-code me-2"></i>
                            Usage Examples
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Image Upload Component</h6>
                                <pre class="bg-light p-3 rounded"><code>@livewire('image-upload', [
    'name' => 'part_images',
    'multiple' => true,
    'maxFiles' => 10,
    'showPreview' => true
])</code></pre>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Voice Note Component</h6>
                                <pre class="bg-light p-3 rounded"><code>@livewire('voice-note-upload', [
    'name' => 'voice_note',
    'required' => false
])</code></pre>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Media Viewer Component</h6>
                                <pre class="bg-light p-3 rounded"><code>@livewire('role-based-media-viewer', [
    'proformaId' => $proforma->id
])</code></pre>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Spare Part Image Upload</h6>
                                <pre class="bg-light p-3 rounded"><code>@livewire('spare-part-image-upload', [
    'name' => 'parts[0][photo_data]',
    'partIndex' => 0,
    'maxFiles' => 5,
    'compactMode' => true
])</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .feature-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        pre {
            font-size: 0.875rem;
            margin-bottom: 0;
        }
        
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
    </style>
</div>
