@props(['proforma' => null, 'application' => null, 'type' => 'all'])

@php
    $mediaItems = collect();
    
    if ($proforma) {
        if ($type === 'all' || $type === 'images') {
            $mediaItems = $mediaItems->merge($proforma->images);
        }
        if ($type === 'all' || $type === 'videos') {
            $mediaItems = $mediaItems->merge($proforma->videos);
        }
        if ($type === 'all' || $type === 'audio') {
            $mediaItems = $mediaItems->merge($proforma->audios);
        }
    }
    
    if ($application) {
        // Get media from application using Spatie Media Library
        $applicationMedia = $application->getMedia();
        $mediaItems = $mediaItems->merge($applicationMedia);
    }
@endphp

@if($mediaItems->count() > 0)
<div class="media-viewer-container">
    <h6 class="mb-3">
        <i class="fas fa-images"></i> 
        Media Files 
        <span class="badge bg-primary">{{ $mediaItems->count() }}</span>
    </h6>
    
    <div class="row g-3">
        @foreach($mediaItems as $media)
            @php
                $mediaType = $media->mime_type ?? 'unknown';
                $isImage = str_starts_with($mediaType, 'image/');
                $isVideo = str_starts_with($mediaType, 'video/');
                $isAudio = str_starts_with($mediaType, 'audio/');
                $mediaUrl = $media->getUrl() ?? asset($media->path ?? '');
            @endphp
            
            <div class="col-md-4 col-lg-3">
                <div class="media-item card h-100">
                    <div class="media-preview">
                        @if($isImage)
                            <img src="{{ $mediaUrl }}" 
                                 alt="Media file" 
                                 class="card-img-top" 
                                 style="height: 150px; object-fit: cover; cursor: pointer;"
                                 onclick="openMediaModal('{{ $mediaUrl }}', 'image')">
                        @elseif($isVideo)
                            <video class="card-img-top" 
                                   style="height: 150px; object-fit: cover; cursor: pointer;"
                                   onclick="openMediaModal('{{ $mediaUrl }}', 'video')"
                                   preload="metadata">
                                <source src="{{ $mediaUrl }}" type="{{ $mediaType }}">
                                Your browser does not support the video tag.
                            </video>
                            <div class="video-overlay">
                                <i class="fas fa-play-circle fa-3x text-white"></i>
                            </div>
                        @elseif($isAudio)
                            <div class="audio-preview d-flex align-items-center justify-content-center" 
                                 style="height: 150px; background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); cursor: pointer;"
                                 onclick="openMediaModal('{{ $mediaUrl }}', 'audio')">
                                <i class="fas fa-music fa-3x text-white"></i>
                            </div>
                        @else
                            <div class="file-preview d-flex align-items-center justify-content-center" 
                                 style="height: 150px; background: #f8f9fa; cursor: pointer;"
                                 onclick="openMediaModal('{{ $mediaUrl }}', 'file')">
                                <i class="fas fa-file fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="card-body p-2">
                        <small class="text-muted d-block">
                            @if($isImage)
                                <i class="fas fa-image"></i> Image
                            @elseif($isVideo)
                                <i class="fas fa-video"></i> Video
                            @elseif($isAudio)
                                <i class="fas fa-music"></i> Audio
                            @else
                                <i class="fas fa-file"></i> File
                            @endif
                        </small>
                        <small class="text-muted">
                            {{ $media->file_name ?? basename($media->path ?? '') }}
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Media Modal -->
<div class="modal fade" id="mediaModal" tabindex="-1" aria-labelledby="mediaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaModalLabel">Media Viewer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="mediaContent">
                    <!-- Media content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="downloadMedia" href="#" class="btn btn-primary" download>
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.media-viewer-container {
    margin: 20px 0;
}

.media-item {
    transition: transform 0.2s ease-in-out;
    border: 1px solid #dee2e6;
}

.media-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.video-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.8;
    transition: opacity 0.2s ease-in-out;
}

.media-preview:hover .video-overlay {
    opacity: 1;
}

.audio-preview {
    border-radius: 8px;
}

.file-preview {
    border-radius: 8px;
}

#mediaContent img,
#mediaContent video {
    max-width: 100%;
    max-height: 70vh;
    border-radius: 8px;
}

#mediaContent audio {
    width: 100%;
    max-width: 500px;
}
</style>
@endpush

@push('scripts')
<script>
function openMediaModal(url, type) {
    const modal = new bootstrap.Modal(document.getElementById('mediaModal'));
    const content = document.getElementById('mediaContent');
    const downloadBtn = document.getElementById('downloadMedia');
    
    // Set download link
    downloadBtn.href = url;
    downloadBtn.download = url.split('/').pop();
    
    // Clear previous content
    content.innerHTML = '';
    
    // Add appropriate media element
    if (type === 'image') {
        content.innerHTML = `<img src="${url}" class="img-fluid" alt="Media file">`;
    } else if (type === 'video') {
        content.innerHTML = `
            <video controls class="w-100">
                <source src="${url}" type="video/mp4">
                <source src="${url}" type="video/webm">
                Your browser does not support the video tag.
            </video>
        `;
    } else if (type === 'audio') {
        content.innerHTML = `
            <audio controls class="w-100">
                <source src="${url}" type="audio/mpeg">
                <source src="${url}" type="audio/webm">
                <source src="${url}" type="audio/wav">
                Your browser does not support the audio tag.
            </audio>
        `;
    } else {
        content.innerHTML = `
            <div class="text-center">
                <i class="fas fa-file fa-5x text-muted mb-3"></i>
                <p>File preview not available</p>
                <a href="${url}" class="btn btn-primary" download>
                    <i class="fas fa-download"></i> Download File
                </a>
            </div>
        `;
    }
    
    modal.show();
}

// Handle voice notes from base64 data
function playVoiceNote(base64Data) {
    const modal = new bootstrap.Modal(document.getElementById('mediaModal'));
    const content = document.getElementById('mediaContent');
    const downloadBtn = document.getElementById('downloadMedia');
    
    // Create blob from base64
    const byteCharacters = atob(base64Data.split(',')[1]);
    const byteNumbers = new Array(byteCharacters.length);
    for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
    }
    const byteArray = new Uint8Array(byteNumbers);
    const blob = new Blob([byteArray], { type: 'audio/webm' });
    const audioUrl = URL.createObjectURL(blob);
    
    // Set download link
    downloadBtn.href = audioUrl;
    downloadBtn.download = 'voice-note.webm';
    
    // Add audio element
    content.innerHTML = `
        <audio controls class="w-100">
            <source src="${audioUrl}" type="audio/webm">
            Your browser does not support the audio tag.
        </audio>
    `;
    
    modal.show();
}
</script>
@endpush

@else
<div class="media-viewer-container">
    <div class="text-center text-muted py-4">
        <i class="fas fa-images fa-3x mb-3"></i>
        <p>No media files available</p>
    </div>
</div>
@endif
