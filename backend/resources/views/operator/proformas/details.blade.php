@extends('layouts.operator')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="mb-0">Proforma Details - #{{ $proforma->file_number }}</h4>
                            <div class="d-flex gap-2">
                                <a href="{{ route('operator.proformas.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Voice Note Section -->
                        @if($proforma->voice_note_path)
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="bx bx-volume-full me-2"></i>Voice Note
                                            <span class="badge bg-primary ms-2">Important: Please listen to this voice note</span>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center gap-3">
                                            <audio controls style="width: 100%; max-width: 500px;" class="voice-note-player">
                                                <source src="{{ asset('storage/' . $proforma->voice_note_path) }}" type="audio/webm">
                                                <source src="{{ asset('storage/' . $proforma->voice_note_path) }}" type="audio/mp3">
                                                Your browser does not support the audio element.
                                            </audio>
                                            <div class="voice-note-info">
                                                <small class="text-muted">
                                                    <i class="bx bx-time me-1"></i>
                                                    <span id="audioDuration">Loading...</span>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="bx bx-info-circle me-1"></i>
                                                This voice note contains additional information about the proforma request.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Proforma Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Basic Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>File Number:</strong></td>
                                        <td>{{ $proforma->file_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            @if($proforma->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($proforma->status == 'published')
                                                <span class="badge bg-info">Published</span>
                                            @elseif($proforma->status == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($proforma->status == 'closed')
                                                <span class="badge bg-danger">Closed</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Created By:</strong></td>
                                        <td>{{ $proforma->poster->name }} ({{ ucfirst($proforma->poster->role) }})</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Created At:</strong></td>
                                        <td>{{ $proforma->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                    @if($proforma->isEteraCheretaMode())
                                    <tr>
                                        <td><strong>Timer:</strong></td>
                                        <td>
                                            <span class="badge bg-info">Etera-Chereta Mode</span>
                                            @if($proforma->timer_expires_at)
                                                <br><small class="text-muted">Expires: {{ $proforma->timer_expires_at->format('d M Y, h:i A') }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3">Customer Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Customer Name:</strong></td>
                                        <td>{{ $proforma->customer_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td>{{ $proforma->customer_phone_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>License Plate:</strong></td>
                                        <td>{{ $proforma->license_plate_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Chassis Number:</strong></td>
                                        <td>{{ $proforma->chassis_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Car:</strong></td>
                                        <td>{{ $proforma->car_type }} {{ $proforma->brand->name }} {{ $proforma->model }} ({{ $proforma->year }})</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Proforma Requested:</strong></td>
                                        <td>{{ $proforma->required_number_of_shops +  $proforma->required_number_of_garages }} </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Damage and Repair Information -->
                        @if($proforma->damage_description || $proforma->repair_description)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Damage & Repair Information</h5>
                                <div class="row">
                                    @if($proforma->damage_description)
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Damage Description</h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-0">{{ $proforma->damage_description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @if($proforma->repair_description)
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Repair Description</h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-0">{{ $proforma->repair_description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Media: Images & Videos -->
                        @if($proforma->images->count() > 0 || $proforma->videos->count() > 0)
                        <div class="row mt-4">
                            @if($proforma->images->count() > 0)
                            <div class="col-md-8 mb-3">
                                <h5 class="mb-3">Images</h5>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($proforma->images as $image)
                                        <img src="{{ $image->url }}" alt="Proforma Image" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover; cursor:pointer;" onclick="openImageModal('{{ $image->url }}')">
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            @if($proforma->videos->count() > 0)
                            <div class="col-md-4 mb-3">
                                <h5 class="mb-3">Videos</h5>
                                @foreach($proforma->videos as $video)
                                    <video controls style="width: 100%; max-width: 360px; border-radius: 6px;" class="mb-2">
                                        <source src="{{ $video->url }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endif

                        <!-- Close Button for Operator -->
                        @if($proforma->status !== 'closed' && $proforma->status !== 'completed')
                            @if(!$proforma->applications->isEmpty())
                                <div class="mt-3">
                                    <form action="{{ route('proforma.close', $proforma->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-primary"
                                            @if($proforma->status === 'pending' || $proforma->status === 'opened') hidden @endif>
                                            Close Proforma
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endif
                        @if(($proforma->status == 'pending' || $proforma->status == 'opened') && $proforma?->selected())
                                                            <a href="/float?proforma_id={{ $proforma->id }}" class="btn btn-sm btn-primary">Float</a>
                                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $proforma->id }}">
    Reject
</button>

<!-- Modal -->
<div class="modal fade" id="rejectModal-{{ $proforma->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('proformas.reject', $proforma->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Rejection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>To confirm rejection, please type <strong>reject</strong> in the box below:</p>
                    <input type="text" name="confirmation" class="form-control" placeholder="Type 'reject' here" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

                                                        @endif

                        <!-- Spare Parts -->
                        @if($proforma->parts->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Required Spare Parts</h5>
                                <div class="table-responsive">
                                     <table class="table table-bordered mb-0">
          <thead>
            <tr>
              <th>No</th>
              <th>Part Name and Munber</th>
              <th>Grade</th>
              <th>Country</th>
              <th>Quantity</th>
              <th>Condition</th>
              <th>Photo</th>
            </tr>
          </thead>
          <tbody>
            @foreach($proforma->parts as $part)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $part->number ?? 'N/A' }}</td>
              <td>{{ $part->grade ?? 'N/A' }}</td>
              <td>{{ $part->country ?? 'N/A' }}</td>
              <td>{{ $part->quantity ?? 'N/A' }}</td>
              <td>{{ $part->condition ?? 'N/A' }}</td>
              <td>
                @php
                    $partImagePaths = $part->images->pluck('image_path')->map(function ($path) {
                        return asset('storage/' . $path);
                    })->toJson();
                    $imageCount = $part->images->count();
                @endphp

                @if($imageCount > 0)
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        onclick="openPartImageModal({{ $partImagePaths }})"
                    >
                        View Image{{ $imageCount > 1 ? 's' : '' }} ({{ $imageCount }})
                    </button>
                @else
                    <span class="text-muted">#N/A</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Applications -->
                        @if($proforma->applications->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Applications ({{ $proforma->applications->count() }})</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Applicant</th>
                                                <th>Role</th>
                                                <th>Applied At</th>
                                                <th>Voice Note</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($proforma->applications as $application)
                                            <tr>
                                                <td>{{ $application->applicationBy->name }}</td>
                                                <td>{{ ucfirst($application->applicationBy->role) }}</td>
                                                <td>{{ $application->created_at->format('d M Y, h:i A') }}</td>
                                                
                                                <td>
                                                    @if($application->getMedia('voice_notes')->count() > 0)
                                                        @foreach($application->getMedia('voice_notes') as $voiceNote)
                                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                                <audio controls style="width: 200px; height: 30px;">
                                                                    <source src="{{ $voiceNote->getUrl() }}" type="{{ $voiceNote->mime_type }}">
                                                                    Your browser does not support the audio element.
                                                                </audio>
                                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                        onclick="playVoiceNote('{{ $voiceNote->getUrl() }}', '{{ $application->applicationBy->name }}')">
                                                                    <i class="bx bx-play"></i>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">No voice note</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($application->status == 'pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                    @elseif($application->status == 'accepted')
                                                        <span class="badge bg-success">Accepted</span>
                                                    @elseif($application->status == 'rejected')
                                                        <span class="badge bg-danger">Rejected</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Proforma Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Proforma Image" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Voice Note Modal -->
<div class="modal fade" id="voiceNoteModal" tabindex="-1" aria-labelledby="voiceNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="voiceNoteModalLabel">Voice Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <h6 id="voiceNoteApplicant" class="mb-3"></h6>
                    <audio id="voiceNotePlayer" controls style="width: 100%; max-width: 500px;">
                        Your browser does not support the audio element.
                    </audio>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bx bx-time me-1"></i>
                            <span id="voiceNoteDuration">Loading...</span>
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadVoiceNote()">
                    <i class="bx bx-download"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Part Image Gallery Modal -->
<div class="modal fade" id="partImageGalleryModal" tabindex="-1" aria-labelledby="partImageGalleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="partImageGalleryModalLabel">Part Images (<span id="partImageIndex"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center justify-content-center position-relative">
                    <button type="button" id="prevPartImageBtn" class="btn btn-dark position-absolute start-0 ms-3 z-index-100 opacity-75" style="font-size: 2rem; padding: 0.5rem 1rem;" onclick="navigatePartImage(-1)" aria-label="Previous">
                        &#10094;
                    </button>
                    <img id="currentPartImage" src="" alt="Part Image Gallery" class="img-fluid rounded shadow-lg" style="max-height: 80vh; max-width: 100%; object-fit: contain; background: #343a40;">
                    <button type="button" id="nextPartImageBtn" class="btn btn-dark position-absolute end-0 me-3 z-index-100 opacity-75" style="font-size: 2rem; padding: 0.5rem 1rem;" onclick="navigatePartImage(1)" aria-label="Next">
                        &#10095;
                    </button>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                 <small class="text-muted" id="partImageCountText"></small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const audioPlayer = document.querySelector('.voice-note-player');
    if (audioPlayer) {
        audioPlayer.addEventListener('loadedmetadata', function() {
            const duration = Math.round(audioPlayer.duration);
            const minutes = Math.floor(duration / 60);
            const seconds = duration % 60;
            document.getElementById('audioDuration').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        });
    }

    const partImageModalElement = document.getElementById('partImageGalleryModal');
    if (partImageModalElement) {
        partImageModalInstance = new bootstrap.Modal(partImageModalElement);
    }
});

function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

let currentPartImages = [];
let currentPartImageIndex = 0;
let partImageModalInstance = null;

function openPartImageModal(imageUrls) {
    if (!partImageModalInstance || imageUrls.length === 0) return;
    currentPartImages = imageUrls;
    currentPartImageIndex = 0;
    document.addEventListener('keydown', handlePartImageKeyPress);
    updatePartImageModalContent();
    partImageModalInstance.show();
    document.getElementById('partImageGalleryModal').addEventListener('hidden.bs.modal', cleanupPartImageModal);
}

function updatePartImageModalContent() {
    const total = currentPartImages.length;
    const isGallery = total > 1;
    const prevBtn = document.getElementById('prevPartImageBtn');
    const nextBtn = document.getElementById('nextPartImageBtn');
    const title = document.getElementById('partImageGalleryModalLabel');
    const counterText = document.getElementById('partImageCountText');
    const currentImage = document.getElementById('currentPartImage');

    if (total === 0) return;
    currentImage.src = currentPartImages[currentPartImageIndex];

    if (isGallery) {
        prevBtn.style.display = 'block';
        nextBtn.style.display = 'block';
        const indexText = `${currentPartImageIndex + 1} of ${total}`;
        title.innerHTML = `Part Images (<span id="partImageIndex">${indexText}</span>)`;
        counterText.textContent = `Showing image ${indexText}`;
    } else {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
        title.innerHTML = `Part Image`;
        counterText.textContent = `Single Image`;
    }
}

function navigatePartImage(direction) {
    if (currentPartImages.length < 2) return;
    currentPartImageIndex += direction;
    if (currentPartImageIndex < 0) {
        currentPartImageIndex = currentPartImages.length - 1;
    } else if (currentPartImageIndex >= currentPartImages.length) {
        currentPartImageIndex = 0;
    }
    updatePartImageModalContent();
}

function handlePartImageKeyPress(event) {
    if (document.getElementById('partImageGalleryModal').classList.contains('show')) {
        if (event.key === 'ArrowRight') {
            navigatePartImage(1);
        } else if (event.key === 'ArrowLeft') {
            navigatePartImage(-1);
        }
    }
}

function cleanupPartImageModal() {
    document.removeEventListener('keydown', handlePartImageKeyPress);
    currentPartImages = [];
    currentPartImageIndex = 0;
}

let currentVoiceNoteUrl = '';

function playVoiceNote(url, applicantName) {
    currentVoiceNoteUrl = url;
    document.getElementById('voiceNoteApplicant').textContent = `Voice Note from: ${applicantName}`;
    document.getElementById('voiceNotePlayer').src = url;
    
    const player = document.getElementById('voiceNotePlayer');
    player.addEventListener('loadedmetadata', function() {
        const duration = Math.round(player.duration);
        const minutes = Math.floor(duration / 60);
        const seconds = duration % 60;
        document.getElementById('voiceNoteDuration').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    });
    
    new bootstrap.Modal(document.getElementById('voiceNoteModal')).show();
}

function downloadVoiceNote() {
    if (currentVoiceNoteUrl) {
        const link = document.createElement('a');
        link.href = currentVoiceNoteUrl;
        link.download = `voice_note_${Date.now()}.mp4`; 
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}
</script>

<style>
.voice-note-player {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.voice-note-info {
    display: flex;
    align-items: center;
}
</style>

@include('components.proforma-media', ['proforma' => $proforma])

@endsection
