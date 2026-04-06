@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Livewire File Upload Components Demo</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Image Upload Component Demo -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Image Upload Component</h5>
                </div>
                <div class="card-body">
                    @livewire('image-upload', [
                        'name' => 'demo_images',
                        'uploadLabel' => 'Upload Product Images',
                        'maxFiles' => 3,
                        'required' => true,
                        'showPreview' => true,
                        'previewSize' => '120px'
                    ])
                </div>
            </div>
        </div>

        <!-- File Upload Component Demo -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">File Upload Component (All Types)</h5>
                </div>
                <div class="card-body">
                    @livewire('file-upload', [
                        'name' => 'demo_files',
                        'uploadLabel' => 'Upload Any Files',
                        'fileType' => 'all',
                        'maxFiles' => 5,
                        'required' => false,
                        'showPreview' => true,
                        'previewSize' => '100px'
                    ])
                </div>
            </div>
        </div>

        <!-- Video Upload Component Demo -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Video Upload Component</h5>
                </div>
                <div class="card-body">
                    @livewire('file-upload', [
                        'name' => 'demo_videos',
                        'uploadLabel' => 'Upload Video Files',
                        'fileType' => 'video',
                        'maxFiles' => 2,
                        'required' => false,
                        'showPreview' => true,
                        'previewSize' => '120px'
                    ])
                </div>
            </div>
        </div>

        <!-- Audio Upload Component Demo -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Audio Upload Component</h5>
                </div>
                <div class="card-body">
                    @livewire('file-upload', [
                        'name' => 'demo_audios',
                        'uploadLabel' => 'Upload Audio Files',
                        'fileType' => 'audio',
                        'maxFiles' => 3,
                        'required' => false,
                        'showPreview' => true,
                        'previewSize' => '100px'
                    ])
                </div>
            </div>
        </div>

        <!-- Document Upload Component Demo -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Document Upload Component</h5>
                </div>
                <div class="card-body">
                    @livewire('file-upload', [
                        'name' => 'demo_documents',
                        'uploadLabel' => 'Upload Documents',
                        'fileType' => 'document',
                        'maxFiles' => 4,
                        'required' => false,
                        'showPreview' => true,
                        'previewSize' => '100px'
                    ])
                </div>
            </div>
        </div>

        <!-- Spare Part Image Upload Component Demo -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Spare Part Image Upload (Compact)</h5>
                </div>
                <div class="card-body">
                    @livewire('spare-part-image-upload', [
                        'name' => 'demo_part_images',
                        'partIndex' => 1,
                        'maxFiles' => 4,
                        'compactMode' => true
                    ])
                </div>
            </div>
        </div>
    </div>

    <!-- Spare Parts Form Demo -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Complete Spare Parts Form with Image Uploads</h5>
                </div>
                <div class="card-body">
                                         @livewire('spare-parts-form')
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Instructions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Usage Instructions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Usage:</h6>
                            <pre><code>@livewire('image-upload', [
    'name' => 'images',
    'maxFiles' => 5,
    'required' => true
])</code></pre>
                        </div>
                        <div class="col-md-6">
                            <h6>Advanced Usage:</h6>
                            <pre><code>@livewire('file-upload', [
    'name' => 'files',
    'fileType' => 'video',
    'maxFiles' => 3,
    'showPreview' => true
])</code></pre>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Available Components:</h6>
                        <ul>
                            <li><strong>image-upload:</strong> Specialized for images only</li>
                            <li><strong>file-upload:</strong> Handles multiple file types</li>
                            <li><strong>spare-part-image-upload:</strong> Compact component for spare parts</li>
                            <li><strong>spare-parts-form:</strong> Complete form with dynamic rows</li>
                        </ul>
                    </div>

                    <div class="mt-3">
                        <h6>Features:</h6>
                        <ul>
                            <li>Drag & drop support</li>
                            <li>Image previews</li>
                            <li>File validation</li>
                            <li>Multiple file support</li>
                            <li>Responsive design</li>
                            <li>Error handling</li>
                            <li>Temporary file storage</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    pre {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        padding: 15px;
        font-size: 0.9rem;
    }
    
    code {
        color: #e83e8c;
        background-color: #f8f9fa;
        padding: 2px 4px;
        border-radius: 3px;
    }
</style>
@endsection
