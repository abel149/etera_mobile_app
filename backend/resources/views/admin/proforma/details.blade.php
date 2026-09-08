@extends('layouts.admin')

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
                                @if(auth()->user()->isSuperAdmin())
                                    <button type="button" class="btn btn-danger" onclick="deleteProforma({{ $proforma->id }})">
                                        <i class="bx bx-trash me-1"></i>Delete
                                    </button>
                                @endif
                                @if(auth()->user()->isSuperAdmin() && $proforma->status === 'pending')
                                    <button type="button" class="btn btn-success" onclick="approveProforma({{ $proforma->id }})">
                                        <i class="bx bx-check me-1"></i>Approve
                                    </button>
                                @endif
                                <a href="{{ url('/admin/others-proforma') }}" class="btn btn-secondary">
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
                                            @if($proforma?->selected())
    File assigned to {{ $proforma?->selectedBy?->employee?->name }}

                                            @else
                                            @if($proforma->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($proforma->status == 'published')
                                                <span class="badge bg-info">Published</span>
                                            @elseif($proforma->status == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($proforma->status == 'closed')
                                                <span class="badge bg-danger">Closed</span>
                                            @endif
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Created By:</strong></td>
                                        <td>{{ $proforma->poster?->name ?? 'N/A' }} ({{ ucfirst($proforma->poster?->role ?? 'unknown') }})</td>
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
                                        <td>{{ $proforma->license_plate_number ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Chassis Number:</strong></td>
                                        <td>{{ $proforma->chassis_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Car:</strong></td>
                                        <td>{{ $proforma->car_type }} {{ $proforma->brand?->name ?? 'N/A' }} {{ $proforma->model }} ({{ $proforma->year }})</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Proforma Requested:</strong></td>
                                        @if($proforma->required_number_of_shops>0)
                                        <td>{{ $proforma->required_number_of_shops }}</td>
                                        @else
                                        <td>Unlimted with timer.</td>
                                        @endif
                                    </tr>
                                    @if($proforma->call_customer)
                                    <tr>
                                        <td><strong>Call Customer:</strong></td>
                                        <td><span class="badge bg-warning text-dark"><i class="bx bx-phone-call me-1"></i>Call Customer</span></td>
                                    </tr>
                                    @endif
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
                        @if($proforma->status== 'pending')
                        <!-- Reject Button -->
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
                        
                        @php
                            $noFloatNeeded = !$proforma->isEteraCheretaMode()
                                && $proforma->floatShopQuota()   <= 0
                                && $proforma->floatGarageQuota() <= 0
                                && ((int)($proforma->required_number_of_shops ?? 0) > 0
                                    || (int)($proforma->required_number_of_garages ?? 0) > 0);
                        @endphp
                        @if(($proforma->status == 'pending' || $proforma->status == 'opened') && !$proforma?->selected() && !$noFloatNeeded)
                                                        <a href="/float?proforma_id={{ $proforma->id }}" class="btn btn-primary">Float</a>
                                                    @endif
                                                    @if(($proforma->status == 'completed' || $proforma->status == 'closed') && !$proforma->verified && !$proforma->selected() && $proforma->processed_by == auth()->id())
                                                        <a href="/admin/verify/{{ $proforma->id }}" class="btn btn-primary">Send To Owner</a>
                                                    @endif
                                                
                                           
                                                @if(auth()->user()->role == 'admin' && $proforma->status !== 'closed' && $proforma->status !== 'completed' && $proforma->processed_by == auth()->id())
                                                    @if($proforma->applications->isEmpty())
                                                        @else
                                                        <form action="{{ route('proforma.close', $proforma->id) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-primary btn-sm"
                                                                @if($proforma->status === 'pending' || $proforma->status === 'opened' || $proforma->processed_by === auth()->id()) hidden @endif>
                                                                Close
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            
                        <!-- Spare Parts -->
                        @if($proforma->parts->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">
                                    Required Spare Parts
                                    @php $progress = $proforma->partsPricingProgress(); @endphp
                                    @if($progress['total'] > 0)
                                    <span class="badge {{ $progress['filled'] >= $progress['total'] ? 'bg-success' : 'bg-warning' }} ms-2" style="font-size: 0.85rem;">
                                        Parts Priced: {{ $progress['filled'] }}/{{ $progress['total'] }}
                                    </span>
                                    @endif
                                </h5>
                                <div class="table-responsive">
                                     <table class="table table-bordered mb-0">
          <thead>
            <tr>
              <th>No</th>
              <th>Part Name and Number</th>
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
                {{-- START: UPDATED SNIPPET FOR PART IMAGES --}}
                @php
                    // Map the images collection to an array of public URLs using the 'image_path' column
                    $partImagePaths = $part->images->pluck('image_path')->map(function ($path) {
                        return asset('storage/' . $path);
                    })->toJson(); // Convert to JSON string for passing to JavaScript
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
                {{-- END: UPDATED SNIPPET FOR PART IMAGES --}}
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
                        @if($applications->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Applications ({{ $applications->count() }})</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Applicant</th>
                                                <th>Role</th>
                                                <th>Applied At</th>
                                                <th>Voice Note</th>
                                                <th>Status</th>
                                                @if(auth()->user()->isSuperAdmin())
                                                <th>Actions</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($applications as $index => $application)
                                            <tr class="application-row" data-index="{{ $index }}" @if($proforma->isEteraCheretaMode() && $index >= 5) style="display:none;" @endif>
                                                <td>{{ $application->applicationBy?->name ?? 'N/A' }}</td>
                                                <td>{{ ucfirst($application->applicationBy?->role ?? 'unknown') }}</td>
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
                                                                        onclick="playVoiceNote('{{ $voiceNote->getUrl() }}', '{{ $application->applicationBy?->name ?? 'N/A' }}')">
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
                                                @if(auth()->user()->isSuperAdmin())
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-success" onclick="acceptApplication({{ $application->id }})">
                                                            <i class="bx bx-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="rejectApplication({{ $application->id }})">
                                                            <i class="bx bx-x"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($proforma->isEteraCheretaMode() && $applications->count() > 5)
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-outline-primary" id="viewMoreBtn" onclick="showMoreApplications()">
                                        <i class="bx bx-chevron-down me-1"></i>View More
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Admin Inbox Management — Floating Button + Modal --}}
    @php
        $requiredShops      = (int) ($proforma->required_number_of_shops ?? 0);
        $isEteraChereta     = $requiredShops === 0 && $requiredGarages === 0;
        // Use controller-computed effective quotas (fallback to insurance inbox count when column is null)
        $shopQuota          = $effectiveShopQuota;
        $garageQuota        = $effectiveGarageQuota;
        $hasInsuranceSlots  = ($shopQuota > 0 || $garageQuota > 0);
        $hasAdminSlots      = ($adminShopSlotCap > 0 || $adminGarageSlotCap > 0);
        $proformaActive     = !in_array($proforma->status, ['closed', 'completed']);
    @endphp

    @if(!$isEteraChereta && ($hasAdminSlots || $hasInsuranceSlots) && $proformaActive)
    {{-- Floating Action Button — always visible when admin has slots to manage --}}
    <button type="button" class="btn btn-primary rounded-circle shadow-lg" id="inboxFab"
        data-bs-toggle="modal" data-bs-target="#inboxModal"
        style="position:fixed;bottom:2rem;right:2rem;width:56px;height:56px;font-size:1.4rem;z-index:1050;display:flex;align-items:center;justify-content:center;"
        title="Manage Admin Inboxes">
        <i class="bx bx-send"></i>
    </button>

    {{-- Admin Inbox Modal --}}
    <div class="modal fade" id="inboxModal" tabindex="-1" aria-labelledby="inboxModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inboxModalLabel">
                        <i class="bx bx-send me-2"></i>Manage Admin Inboxes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('proforma.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="proforma" value="{{ $proforma->id }}">
                    <div class="modal-body">

                        @php
                            // For dropdown: exclude already-inboxed AND applied users from options,
                            // but include the user currently in each slot so they show as selected
                            $shopDropdownExclude   = array_merge($alreadyInboxedShopIds,   $activeApplicationShopIds);
                            $garageDropdownExclude = array_merge($alreadyInboxedGarageIds, $activeApplicationGarageIds);

                            $inboxShopOptions = $shops->map(fn($s) => [
                                'id'    => (int)$s->id,
                                'label' => ($s->store_id ?? '') . ' - ' . $s->name,
                            ])->values()->toArray();

                            $inboxGarageOptions = $garages->map(fn($g) => [
                                'id'    => (int)$g->id,
                                'label' => $g->name,
                            ])->values()->toArray();
                        @endphp

                        {{-- JS data for comboboxes --}}
                        <script>
                        const inboxShopData   = @json($inboxShopOptions);
                        const inboxGarageData = @json($inboxGarageOptions);
                        const inboxShopExclude   = @json(array_map('intval', $shopDropdownExclude));
                        const inboxGarageExclude = @json(array_map('intval', $garageDropdownExclude));
                        </script>

                        {{-- Locked notice: garage-only proforma — no shop inboxing --}}
                        @if($proforma->isGarageOnlyInsurance())
                        <div class="alert alert-secondary d-flex align-items-center gap-2 mb-3">
                            <i class="bx bx-lock fs-5"></i>
                            <span><strong>Shop slots are locked.</strong> This is a <em>Garage Only</em> proforma — only garage inboxing is permitted.</span>
                        </div>
                        @endif

                        {{-- ── Shop Slots ────────────────────────────────────── --}}
                        @if($adminShopSlotCap > 0 || $shopQuota > 0)
                        <h6 class="fw-bold mb-2 text-primary"><i class="bx bx-store me-1"></i>Shop Slots</h6>

                        {{-- Insurance-reserved locked slots --}}
                        @if($shopQuota > 0)
                            @if(count($insuranceShopInboxes) > 0)
                                @foreach($insuranceShopInboxes as $insInbox)
                                @php
                                    $insApplied = in_array((int)$insInbox->user_id, $activeApplicationShopIds);
                                    $insLabel   = trim(($insInbox->user->store_id ?? '') . ' - ' . ($insInbox->user->name ?? 'Unknown'));
                                @endphp
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bx bx-lock-alt text-warning me-1"></i>Insurance Slot
                                        @if($insApplied)
                                            <span class="badge bg-success ms-1">Applied ✓</span>
                                        @else
                                            <span class="badge bg-warning text-dark ms-1">Awaiting partner</span>
                                        @endif
                                    </label>
                                    <input type="text" class="form-control bg-light text-muted" value="{{ $insLabel }}" disabled>
                                </div>
                                @endforeach
                            @else
                                {{-- Chereta fired — inboxes cleared, quota slots are filled --}}
                                @for($q = 0; $q < $shopQuota; $q++)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bx bx-lock-alt text-warning me-1"></i>Insurance Slot
                                        <span class="badge bg-success ms-1">Filled by insurance ✓</span>
                                    </label>
                                    <input type="text" class="form-control bg-light text-muted" value="Insurance partner applied" disabled>
                                </div>
                                @endfor
                            @endif
                        @endif

                        @if($adminShopSlotCap > 0 && $shopQuota > 0)<hr class="my-2">@endif

                        @if(count($activeApplicationShopIds) > 0 && $adminShopSlotCap > 0)
                        <div class="alert alert-warning py-2 mb-3">
                            <small><strong>Applied (slot locked):</strong></small>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach($shops->whereIn('id', $activeApplicationShopIds) as $s)
                                    <span class="badge bg-warning text-dark">{{ $s->store_id }} - {{ $s->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @for($i = 0; $i < $adminShopSlotCap; $i++)
                        @php
                            $existingShopInbox = $adminShopInboxes[$i] ?? null;
                            $existingShopLabel = $existingShopInbox
                                ? (($existingShopInbox->user->store_id ?? '') . ' - ' . ($existingShopInbox->user->name ?? ''))
                                : '';
                            $existingShopId = $existingShopInbox ? (int)$existingShopInbox->user_id : '';
                            $isShopLocked   = $existingShopInbox && in_array((int)$existingShopInbox->user_id, $activeApplicationShopIds);
                        @endphp
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Shop Slot {{ $i + 1 }}
                                @if($existingShopInbox && !$isShopLocked)
                                    <span class="badge bg-info text-dark ms-1">Inboxed — change or clear below</span>
                                @elseif($isShopLocked)
                                    <span class="badge bg-success ms-1">Applied — locked</span>
                                @endif
                            </label>
                            @if($isShopLocked)
                                {{-- Applied slot — readonly, submit current value so diff logic keeps it --}}
                                <input type="text" class="form-control bg-light" value="{{ $existingShopLabel }}" disabled>
                                <input type="hidden" name="spare_part_partners[]" value="{{ $existingShopId }}">
                            @else
                                <div class="inbox-combobox-wrapper position-relative" data-type="shop" data-slot="{{ $i }}">
                                    <input type="text"
                                           class="form-control inbox-cb-search"
                                           data-type="shop" data-slot="{{ $i }}"
                                           placeholder="Type to search or leave empty to clear…"
                                           value="{{ $existingShopLabel }}"
                                           autocomplete="off">
                                    <input type="hidden"
                                           name="spare_part_partners[]"
                                           class="inbox-cb-value"
                                           data-type="shop" data-slot="{{ $i }}"
                                           value="{{ $existingShopId }}">
                                    <div class="inbox-cb-dropdown list-group d-none"
                                         data-type="shop" data-slot="{{ $i }}"
                                         style="position:absolute;top:100%;left:0;right:0;max-height:200px;overflow-y:auto;z-index:2000;"></div>
                                </div>
                                @if($existingShopInbox)
                                <small class="text-muted">Select a different person to reassign, or clear the text and save to free this slot.</small>
                                @endif
                            @endif
                        </div>
                        @endfor
                        @endif

                        {{-- Locked notice: shop-only proforma — no garage inboxing --}}
                        @if($proforma->isShopOnlyInsurance())
                        <div class="alert alert-secondary d-flex align-items-center gap-2 mb-3 mt-3">
                            <i class="bx bx-lock fs-5"></i>
                            <span><strong>Garage slots are locked.</strong> This is a <em>Shop Only</em> proforma — only shop inboxing is permitted.</span>
                        </div>
                        @endif

                        {{-- ── Garage Slots ──────────────────────────────────── --}}
                        @if($adminGarageSlotCap > 0 || $garageQuota > 0)
                        <hr class="my-3">
                        <h6 class="fw-bold mb-2 text-success"><i class="bx bx-wrench me-1"></i>Garage Slots</h6>

                        {{-- Insurance-reserved locked slots --}}
                        @if($garageQuota > 0)
                            @if(count($insuranceGarageInboxes) > 0)
                                @foreach($insuranceGarageInboxes as $insInbox)
                                @php
                                    $insApplied = in_array((int)$insInbox->user_id, $activeApplicationGarageIds);
                                    $insLabel   = $insInbox->user->name ?? 'Unknown';
                                @endphp
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bx bx-lock-alt text-warning me-1"></i>Insurance Slot
                                        @if($insApplied)
                                            <span class="badge bg-success ms-1">Applied ✓</span>
                                        @else
                                            <span class="badge bg-warning text-dark ms-1">Awaiting partner</span>
                                        @endif
                                    </label>
                                    <input type="text" class="form-control bg-light text-muted" value="{{ $insLabel }}" disabled>
                                </div>
                                @endforeach
                            @else
                                {{-- Chereta fired — inboxes cleared, quota slots are filled --}}
                                @for($q = 0; $q < $garageQuota; $q++)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bx bx-lock-alt text-warning me-1"></i>Insurance Slot
                                        <span class="badge bg-success ms-1">Filled by insurance ✓</span>
                                    </label>
                                    <input type="text" class="form-control bg-light text-muted" value="Insurance partner applied" disabled>
                                </div>
                                @endfor
                            @endif
                        @endif

                        @if($adminGarageSlotCap > 0 && $garageQuota > 0)<hr class="my-2">@endif

                        @if(count($activeApplicationGarageIds) > 0 && $adminGarageSlotCap > 0)
                        <div class="alert alert-warning py-2 mb-3">
                            <small><strong>Applied (slot locked):</strong></small>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach($garages->whereIn('id', $activeApplicationGarageIds) as $g)
                                    <span class="badge bg-warning text-dark">{{ $g->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @for($i = 0; $i < $adminGarageSlotCap; $i++)
                        @php
                            $existingGarageInbox = $adminGarageInboxes[$i] ?? null;
                            $existingGarageLabel = $existingGarageInbox ? ($existingGarageInbox->user->name ?? '') : '';
                            $existingGarageId    = $existingGarageInbox ? (int)$existingGarageInbox->user_id : '';
                            $isGarageLocked      = $existingGarageInbox && in_array((int)$existingGarageInbox->user_id, $activeApplicationGarageIds);
                        @endphp
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Garage Slot {{ $i + 1 }}
                                @if($existingGarageInbox && !$isGarageLocked)
                                    <span class="badge bg-info text-dark ms-1">Inboxed — change or clear below</span>
                                @elseif($isGarageLocked)
                                    <span class="badge bg-success ms-1">Applied — locked</span>
                                @endif
                            </label>
                            @if($isGarageLocked)
                                <input type="text" class="form-control bg-light" value="{{ $existingGarageLabel }}" disabled>
                                <input type="hidden" name="garage_partners[]" value="{{ $existingGarageId }}">
                            @else
                                <div class="inbox-combobox-wrapper position-relative" data-type="garage" data-slot="{{ $i }}">
                                    <input type="text"
                                           class="form-control inbox-cb-search"
                                           data-type="garage" data-slot="{{ $i }}"
                                           placeholder="Type to search or leave empty to clear…"
                                           value="{{ $existingGarageLabel }}"
                                           autocomplete="off">
                                    <input type="hidden"
                                           name="garage_partners[]"
                                           class="inbox-cb-value"
                                           data-type="garage" data-slot="{{ $i }}"
                                           value="{{ $existingGarageId }}">
                                    <div class="inbox-cb-dropdown list-group d-none"
                                         data-type="garage" data-slot="{{ $i }}"
                                         style="position:absolute;top:100%;left:0;right:0;max-height:200px;overflow-y:auto;z-index:2000;"></div>
                                </div>
                                @if($existingGarageInbox)
                                <small class="text-muted">Select a different person to reassign, or clear the text and save to free this slot.</small>
                                @endif
                            @endif
                        </div>
                        @endfor
                        @endif

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Save Inboxes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Combobox JS (shared for both shop and garage) --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function getDataset(type) {
            return type === 'shop' ? inboxShopData : inboxGarageData;
        }
        function getExcludes(type) {
            return type === 'shop' ? inboxShopExclude : inboxGarageExclude;
        }

        function getOtherSelectedIds(type, slot) {
            const ids = [];
            document.querySelectorAll('.inbox-cb-value[data-type="' + type + '"]').forEach(h => {
                if (parseInt(h.dataset.slot) !== parseInt(slot) && h.value) ids.push(parseInt(h.value));
            });
            return ids;
        }

        function renderCbDropdown(type, slot, term) {
            const dropdown  = document.querySelector('.inbox-cb-dropdown[data-type="' + type + '"][data-slot="' + slot + '"]');
            const curHidden = document.querySelector('.inbox-cb-value[data-type="' + type + '"][data-slot="' + slot + '"]');
            if (!dropdown) return;

            const baseExcludes = getExcludes(type);
            const otherIds     = getOtherSelectedIds(type, slot);
            const curVal       = curHidden ? parseInt(curHidden.value) : 0;
            const lowerTerm    = term.toLowerCase();

            const filtered = getDataset(type).filter(item => {
                const id = item.id;
                // Always include the currently selected value for this slot
                if (id === curVal && curVal) return true;
                if (baseExcludes.includes(id)) return false;
                if (otherIds.includes(id)) return false;
                if (term && !item.label.toLowerCase().includes(lowerTerm)) return false;
                return true;
            });

            dropdown.innerHTML = '';
            if (!filtered.length) {
                dropdown.innerHTML = '<div class="list-group-item text-muted small py-2">No results found</div>';
            } else {
                filtered.forEach(item => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action small py-2';
                    if (term) {
                        const idx = item.label.toLowerCase().indexOf(lowerTerm);
                        btn.innerHTML = item.label.slice(0, idx)
                            + '<strong>' + item.label.slice(idx, idx + term.length) + '</strong>'
                            + item.label.slice(idx + term.length);
                    } else {
                        btn.textContent = item.label;
                    }
                    btn.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        selectCbItem(type, slot, String(item.id), item.label);
                    });
                    dropdown.appendChild(btn);
                });
            }
            dropdown.classList.remove('d-none');
        }

        function selectCbItem(type, slot, id, label) {
            const search   = document.querySelector('.inbox-cb-search[data-type="' + type + '"][data-slot="' + slot + '"]');
            const hidden   = document.querySelector('.inbox-cb-value[data-type="' + type + '"][data-slot="' + slot + '"]');
            const dropdown = document.querySelector('.inbox-cb-dropdown[data-type="' + type + '"][data-slot="' + slot + '"]');
            if (search)   search.value  = label;
            if (hidden)   hidden.value  = id;
            if (dropdown) dropdown.classList.add('d-none');

            // Clear the same person from other slots of same type
            document.querySelectorAll('.inbox-cb-value[data-type="' + type + '"]').forEach(h => {
                if (parseInt(h.dataset.slot) !== parseInt(slot) && h.value === id) {
                    h.value = '';
                    const sib = document.querySelector('.inbox-cb-search[data-type="' + type + '"][data-slot="' + h.dataset.slot + '"]');
                    if (sib) sib.value = '';
                }
            });
        }

        document.querySelectorAll('.inbox-cb-search').forEach(inp => {
            const type = inp.dataset.type;
            const slot = inp.dataset.slot;

            inp.addEventListener('input', function () {
                const hid = document.querySelector('.inbox-cb-value[data-type="' + type + '"][data-slot="' + slot + '"]');
                if (hid) hid.value = '';
                renderCbDropdown(type, slot, this.value.trim());
            });
            inp.addEventListener('focus', function () {
                renderCbDropdown(type, slot, this.value.trim());
            });
            inp.addEventListener('blur', function () {
                setTimeout(function () {
                    const dd = document.querySelector('.inbox-cb-dropdown[data-type="' + type + '"][data-slot="' + slot + '"]');
                    if (dd) dd.classList.add('d-none');
                }, 160);
            });
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.inbox-combobox-wrapper')) {
                document.querySelectorAll('.inbox-cb-dropdown').forEach(d => d.classList.add('d-none'));
            }
        });
    });
    </script>
    @endif
</div>

<!-- Image Modal (Used for general Proforma Images) - KEPT AS IS -->
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

<!-- Voice Note Modal - KEPT AS IS -->
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

{{-- START: NEW PART IMAGE GALLERY MODAL --}}
<div class="modal fade" id="partImageGalleryModal" tabindex="-1" aria-labelledby="partImageGalleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="partImageGalleryModalLabel">Part Images (<span id="partImageIndex"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center justify-content-center position-relative">
                    <!-- Previous Button -->
                    <button type="button" id="prevPartImageBtn" class="btn btn-dark position-absolute start-0 ms-3 z-index-100 opacity-75" style="font-size: 2rem; padding: 0.5rem 1rem;" onclick="navigatePartImage(-1)" aria-label="Previous">
                        &#10094;
                    </button>

                    <!-- Image -->
                    <img id="currentPartImage" src="" alt="Part Image Gallery" class="img-fluid rounded shadow-lg" style="max-height: 80vh; max-width: 100%; object-fit: contain; background: #343a40;">

                    <!-- Next Button -->
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
{{-- END: NEW PART IMAGE GALLERY MODAL --}}

<script>
// View More functionality for Etera Chereta applications
let visibleApplications = 5;
function showMoreApplications() {
    const rows = document.querySelectorAll('.application-row');
    const nextLimit = visibleApplications + 5;
    rows.forEach((row, i) => {
        if (i < nextLimit) row.style.display = '';
    });
    visibleApplications = nextLimit;
    if (visibleApplications >= rows.length) {
        const btn = document.getElementById('viewMoreBtn');
        if (btn) btn.style.display = 'none';
    }
}

// Voice note functionality
document.addEventListener('DOMContentLoaded', function() {
    // Existing voice note duration logic
    const audioPlayer = document.querySelector('.voice-note-player');
    if (audioPlayer) {
        audioPlayer.addEventListener('loadedmetadata', function() {
            const duration = Math.round(audioPlayer.duration);
            const minutes = Math.floor(duration / 60);
            const seconds = duration % 60;
            document.getElementById('audioDuration').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        });
        
        // Auto-play voice note for admin attention
        audioPlayer.addEventListener('canplay', function() {
            // NOTE: Replaced standard alert() with a custom confirmation for safety
            if (!sessionStorage.getItem('voiceNotePlayed_' + {{ $proforma->id }})) {
                // Since confirm() is blocked in some contexts, a simple flag is safer here.
                // Assuming `confirm` is available in this specific environment, otherwise this needs a custom modal UI.
                if (window.confirm('This proforma has a voice note. Would you like to play it now?')) {
                    audioPlayer.play();
                }
                sessionStorage.setItem('voiceNotePlayed_' + {{ $proforma->id }}, 'true');
            }
        });
    }

    // Initialize Part Image Gallery Modal Instance
    const partImageModalElement = document.getElementById('partImageGalleryModal');
    if (partImageModalElement) {
        partImageModalInstance = new bootstrap.Modal(partImageModalElement);
    }
});

// Existing Image modal functionality (for general proforma images)
function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}


// START: NEW PART IMAGE GALLERY LOGIC

let currentPartImages = [];
let currentPartImageIndex = 0;
let partImageModalInstance = null; // Stored in DOMContentLoaded

/**
 * Opens the part image gallery modal.
 * @param {string[]} imageUrls - Array of public URLs for the part images.
 */
function openPartImageModal(imageUrls) {
    if (!partImageModalInstance || imageUrls.length === 0) return;

    currentPartImages = imageUrls;
    currentPartImageIndex = 0;
    
    // Attach key listener for navigation
    document.addEventListener('keydown', handlePartImageKeyPress);

    // Initial load and button visibility
    updatePartImageModalContent();
    
    partImageModalInstance.show();
    
    // Ensure key listener is removed when modal is closed
    document.getElementById('partImageGalleryModal').addEventListener('hidden.bs.modal', cleanupPartImageModal);
}

/**
 * Updates the displayed image, counter, and navigation button visibility.
 */
function updatePartImageModalContent() {
    const total = currentPartImages.length;
    const isGallery = total > 1;
    const prevBtn = document.getElementById('prevPartImageBtn');
    const nextBtn = document.getElementById('nextPartImageBtn');
    const title = document.getElementById('partImageGalleryModalLabel');
    const counterText = document.getElementById('partImageCountText');
    const currentImage = document.getElementById('currentPartImage');

    if (total === 0) return;

    // Update image source
    currentImage.src = currentPartImages[currentPartImageIndex];

    if (isGallery) {
        // Show navigation controls
        prevBtn.style.display = 'block';
        nextBtn.style.display = 'block';
        
        // Update index counter
        const indexText = `${currentPartImageIndex + 1} of ${total}`;
        title.innerHTML = `Part Images (<span id="partImageIndex">${indexText}</span>)`;
        counterText.textContent = `Showing image ${indexText}`;

    } else {
        // Hide navigation controls for single image
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
        title.innerHTML = `Part Image`;
        counterText.textContent = `Single Image`;
    }
}

/**
 * Navigates to the next or previous image in the part gallery.
 * @param {number} direction - -1 for previous, 1 for next.
 */
function navigatePartImage(direction) {
    if (currentPartImages.length < 2) return;

    currentPartImageIndex += direction;

    if (currentPartImageIndex < 0) {
        currentPartImageIndex = currentPartImages.length - 1; // Wrap around to the last image
    } else if (currentPartImageIndex >= currentPartImages.length) {
        currentPartImageIndex = 0; // Wrap around to the first image
    }

    updatePartImageModalContent();
}

/**
 * Handles keyboard events (ArrowRight, ArrowLeft) for navigation.
 */
function handlePartImageKeyPress(event) {
    // Check if the part image gallery modal is currently open (Bootstrap class 'show')
    if (document.getElementById('partImageGalleryModal').classList.contains('show')) {
        if (event.key === 'ArrowRight') {
            navigatePartImage(1);
        } else if (event.key === 'ArrowLeft') {
            navigatePartImage(-1);
        }
    }
}

/**
 * Cleans up state when the modal is closed.
 */
function cleanupPartImageModal() {
    document.removeEventListener('keydown', handlePartImageKeyPress);
    currentPartImages = [];
    currentPartImageIndex = 0;
}

// END: NEW PART IMAGE GALLERY LOGIC


// Existing Voice note functionality
let currentVoiceNoteUrl = '';

function playVoiceNote(url, applicantName) {
    currentVoiceNoteUrl = url;
    document.getElementById('voiceNoteApplicant').textContent = `Voice Note from: ${applicantName}`;
    document.getElementById('voiceNotePlayer').src = url;
    
    // Show duration when loaded
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
        // Use a generic name if mime type is unknown or not provided
        link.download = `voice_note_${Date.now()}.mp4`; 
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Admin actions (Kept as is, ensuring custom confirmation instead of alert/confirm where possible)
function deleteProforma(proformaId) {
    // NOTE: Using window.confirm() as a placeholder for a custom modal UI, as per instructions.
    if (window.confirm('Are you sure you want to delete this proforma? This action cannot be undone.')) {
        fetch(`/admin/proformas/${proformaId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // NOTE: Using window.alert() as a placeholder for a custom notification UI.
                window.alert('Proforma deleted successfully');
                				window.location.href = '{{ url('/admin') }}';
            } else {
                window.alert('Error deleting proforma: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.alert('Error deleting proforma');
        });
    }
}

function approveProforma(proformaId) {
    if (window.confirm('Are you sure you want to approve this proforma?')) {
        fetch(`/admin/proformas/${proformaId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.alert('Proforma approved successfully');
                location.reload();
            } else {
                window.alert('Error approving proforma: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.alert('Error approving proforma');
        });
    }
}

function acceptApplication(applicationId) {
    if (window.confirm('Are you sure you want to accept this application?')) {
        fetch(`/admin/applications/${applicationId}/accept`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.alert('Application accepted successfully');
                location.reload();
            } else {
                window.alert('Error accepting application: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.alert('Error accepting application');
        });
    }
}

function rejectApplication(applicationId) {
    if (window.confirm('Are you sure you want to reject this application?')) {
        fetch(`/admin/applications/${applicationId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.alert('Application rejected successfully');
                location.reload();
            } else {
                window.alert('Error rejecting application: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.alert('Error rejecting application');
        });
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

.card-stamp {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    width: 80px;
    height: 80px;
    opacity: 0.3;
    pointer-events: none;
    z-index: 5;
}

.card-stamp img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
</style>

<!-- Include Proforma Media Component -->
@include('components.proforma-media', ['proforma' => $proforma])

@endsection
