@if($proforma->getMedia('voice_notes')->count() > 0 || $proforma->getMedia('images')->count() > 0)
<div class="card radius-10 mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bx bx-images me-2"></i>
            Proforma Media
        </h5>
    </div>
    <div class="card-body">
        <!-- Voice Notes Section -->
        @if($proforma->getMedia('voice_notes')->count() > 0)
        <div class="mb-4">
            <h6 class="mb-3">
                <i class="bx bx-microphone me-2"></i>
                Voice Notes
            </h6>
            <div class="row">
                @foreach($proforma->getMedia('voice_notes') as $voiceNote)
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card border">
                        <div class="card-body text-center">
                            <audio controls class="w-100">
                                <source src="{{ $voiceNote->getUrl() }}" type="{{ $voiceNote->mime_type }}">
                                Your browser does not support the audio element.
                            </audio>
                            <small class="text-muted d-block mt-2">
                                {{ $voiceNote->created_at->format('M d, Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Images Section -->
        @if($proforma->getMedia('images')->count() > 0)
        <div class="mb-4">
            <h6 class="mb-3">
                <i class="bx bx-image me-2"></i>
                Images
            </h6>
            <div class="row">
                @foreach($proforma->getMedia('images') as $image)
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card border">
                        <div class="card-body p-2">
                            <img src="{{ $image->getUrl() }}" 
                                 alt="Proforma Image" 
                                 class="img-fluid rounded cursor-pointer proforma-image"
                                 style="cursor: pointer; max-height: 200px; object-fit: cover;"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#imageModal{{ $image->id }}"
                                 onclick="openImageModal('{{ $image->getUrl() }}', '{{ $image->name }}')">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Part Photos Section -->
        @php
            $hasPartPhotos = $proforma->parts->filter(function($part){
                return !empty($part->photo) && is_array($part->photo) && count($part->photo) > 0;
            })->count() > 0;
        @endphp
        @if($hasPartPhotos)
        <div class="mb-4">
            <h6 class="mb-3">
                <i class="bx bx-cog me-2"></i>
                Part Photos
            </h6>
            <div class="row">
                @foreach($proforma->parts as $part)
                    @if($part->photo && is_array($part->photo) && count($part->photo) > 0)
                        @foreach($part->photo as $photoPath)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border">
                                    <div class="card-body p-2">
                                        <div class="position-relative">
                                            <img src="{{ asset('storage/' . str_replace('public/', '', $photoPath)) }}" 
                                                 alt="{{ $part->component ?? 'Part' }} Photo" 
                                                 class="img-fluid rounded cursor-pointer proforma-image"
                                                 style="cursor: pointer; max-height: 200px; object-fit: cover;"
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#partImageModal{{ $loop->parent->index }}_{{ $loop->index }}"
                                                 onclick="openImageModal('{{ asset('storage/' . str_replace('public/', '', $photoPath)) }}', '{{ $part->component ?? 'Part' }}')">
                                            <div class="position-absolute top-0 start-0 m-2">
                                                <span class="badge bg-primary">{{ $part->component ?? 'Part' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Image Modal for Zoom -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Zoomed Image" class="img-fluid" style="max-height: 80vh;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="downloadImage" href="" download class="btn btn-primary">
                    <i class="bx bx-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- CSS for hover effects -->
<style>
.proforma-image {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.proforma-image:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.cursor-pointer {
    cursor: pointer;
}

.modal-xl {
    max-width: 90%;
}

@media (max-width: 768px) {
    .modal-xl {
        max-width: 95%;
    }
}
</style>

<!-- JavaScript for image modal -->
<script>
function openImageModal(imageSrc, imageName) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModalTitle').textContent = imageName || 'Image';
    document.getElementById('downloadImage').href = imageSrc;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

// Add click event listeners to all proforma images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.proforma-image');
    images.forEach(function(img) {
        img.addEventListener('click', function() {
            const src = this.src;
            const alt = this.alt || 'Image';
            openImageModal(src, alt);
        });
    });
});
</script>
@endif
