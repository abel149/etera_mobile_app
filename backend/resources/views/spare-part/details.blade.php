@extends('layouts.sparepart')
@section('insurance')
    class="current"
@endsection
@section('content')

    <style type="text/css">
        .player audio {
            width: 100%;
            border-radius: 6px;
            margin: 0;
            padding: 0;
            border: none;
        }

        /* Table Styles */
        .table-container {
            margin-top: 20px;
            overflow-y: hidden;
            overflow-x: auto;
            /* Allow horizontal scroll for the table on small screens */
            white-space: nowrap;
        }

        .basic-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid rgba(255, 255, 255, 0.08);
            font-family: Arial, sans-serif;
        }

        .basic-table th,
        .basic-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            white-space: normal;
        }

        .basic-table th {
            background: rgba(13, 148, 136, 0.12) !important;
            color: var(--etera-teal-light) !important;
            font-weight: bold;
            text-transform: uppercase;
        }

        .basic-table tr:hover {
            background: rgba(13, 148, 136, 0.04) !important;
        }

        .basic-table tfoot tr {
            background: rgba(13, 148, 136, 0.06) !important;
            font-weight: bold;
        }

        .basic-table input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            box-sizing: border-box;
            min-width: 80px;
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        .basic-table input[type="number"]:disabled {
            background: rgba(255, 255, 255, 0.03);
            color: rgba(255, 255, 255, 0.5);
        }

        /* Button Styles */
        .apply-now-button {
            background-color: var(--etera-teal);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .apply-now-button:hover {
            background-color: #0056b3;
        }

        /* --- Responsive layout for summary + table --- */
        /* Default: stacked layout for mobile */
        .row.responsive-layout {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Desktop: side-by-side layout (Applies from 700px up, then refined at 992px by standard cols) */
        @media (min-width: 992px) {
            .row.responsive-layout {
                flex-direction: row;
                align-items: flex-start;
                gap: 30px;
            }

            /* Sidebar (Proforma Summary) */
            .row.responsive-layout .col-lg-4 {
                flex: 0 0 30%;
                max-width: 30%;
            }

            /* Table container */
            .row.responsive-layout .col-lg-8 {
                flex: 1;
                max-width: 70%;
            }
        }

        /* * RESPONSIVE LAYOUT LOGIC REFINEMENT:
        * On desktop (>=992px), the sidebar (col-xl-4) is on the left.
        * On mobile (<992px), the content stacks.
        */
        @media (max-width: 991px) {
            /* Target lg breakpoint and down */
            .row.responsive-layout {
                /* Puts the main content (col-lg-8) at the top, and the sidebar (col-lg-4) at the bottom */
                flex-direction: column;
            }

            .col-lg-8,
            .col-lg-4 {
                /* Use full width for stacked elements */
                max-width: 100% !important;
                margin-top: 20px;
                /* Add some space between stacked elements */
            }
        }

        /* Utility to right-align text in table cells */
        .text-align-right {
            text-align: right !important;
        }

        /* --- MODAL FIXES FOR MOBILE (Part Image Gallery) --- */

        /* Ensure this modal always sits above its backdrop (scope via a custom backdrop class). */
        .modal-backdrop.part-image-gallery-backdrop {
            z-index: 1040 !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
        }

        /* Main modal container - ensure it sits above backdrop */
        #partImageGalleryModal {
            z-index: 1050 !important;
            background-color: transparent !important;
            padding: 0 !important; /* Fix for potential padding issues */
        }

        /* Modal dialog positioning and sizing */
        #partImageGalleryModal .modal-dialog {
            z-index: 1051 !important;
            margin: 0;
            max-width: 100vw;
            height: 100vh;
            display: flex;
            align-items: center;
        }

        /* Modal content styling */
        #partImageGalleryModal .modal-content {
            background: transparent;
            border: none;
            box-shadow: none;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Modal body - make it full height and centered */
        #partImageGalleryModal .modal-body {
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            background: transparent;
        }

        /* Image gallery wrapper */
        .image-gallery-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
        }

        /* Image styling */
        #currentPartImage {
            max-height: 85vh; /* Increase max height */
            max-width: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
            background: transparent !important;
            transition: opacity 0.3s ease-in-out; /* Add transition for loading effect */
        }
        
        /* Make carousel buttons black with white outline */
.custom-carousel-btn {
    background-color: #000 !important;     /* black */
    border: 2px solid #fff !important;     /* white outline */
    width: 50px;
    height: 50px;
    border-radius: 50%;                    /* circular button */
    display: flex;
    justify-content: center;
    top: 100px;
    align-items: center;
    opacity: 1 !important;                 /* full visibility */
}

/* Make the arrow (icon) white */
.custom-carousel-btn .carousel-control-prev-icon,
.custom-carousel-btn .carousel-control-next-icon {
    filter: invert(1);  /* makes arrow icon white */
    font-size: 30px;
    font-weight: bolder;
}


        /* Navigation buttons - make them larger and more touch-friendly */
        .modal-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1052 !important;
            background: rgba(0, 0, 0, 0.8) !important;
            border: 2px solid white !important;
            color: white !important;
            width: 50px !important;
            height: 50px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            font-size: 24px !important;
            opacity: 0.9 !important;
            transition: all 0.2s ease;
            cursor: pointer;
            pointer-events: auto;
            touch-action: manipulation;
        }

        .modal-nav-btn:hover,
        .modal-nav-btn:active,
        .modal-nav-btn:focus {
            opacity: 1 !important;
            background: rgba(0, 0, 0, 0.9) !important;
            transform: translateY(-50%) scale(1.05);
        }

        /* Button positioning */
        #prevPartImageBtn {
            left: 10px !important;
        }

        #nextPartImageBtn {
            right: 10px !important;
        }

        /* Modal header and footer - positioned absolutely on top of the backdrop */
        #partImageGalleryModal .modal-header {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            border-bottom: 1px solid #444;
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1053;
            flex-shrink: 0; /* Prevents shrinking */
        }

        #partImageGalleryModal .modal-footer {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            border-top: 1px solid #444;
            position: sticky;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1053;
            flex-shrink: 0; /* Prevents shrinking */
        }

        /* Close button styling */
        #partImageGalleryModal .btn-close {
            filter: invert(1) !important;
            opacity: 0.8 !important;
        }

        /* Mobile-specific styles for modal */
        @media (max-width: 768px) {
            .modal-nav-btn {
                width: 45px !important;
                height: 45px !important;
                font-size: 20px !important;
                min-width: 45px !important;
                min-height: 45px !important;
            }

            #prevPartImageBtn {
                left: 8px !important;
            }

            #nextPartImageBtn {
                right: 8px !important;
            }

            #currentPartImage {
                max-height: 70vh;
            }

            /* Ensure buttons are always visible on mobile */
            .modal-nav-btn:active {
                transform: translateY(-50%) scale(0.95) !important;
                background: rgba(0, 0, 0, 1) !important;
            }
        }

        /* Force modal controls to be clickable and above any overlays */
        #partImageGalleryModal { pointer-events: auto !important; }
        #partImageGalleryModal .modal-content { pointer-events: auto !important; }
        #partImageGalleryModal .modal-header,
        #partImageGalleryModal .modal-footer { position: sticky; z-index: 1065; pointer-events: auto !important; }
        #partImageGalleryModal .carousel,
        #partImageGalleryModal .carousel-inner { position: relative; z-index: 1061; }
        #partImageGalleryModal .carousel-control-prev,
        #partImageGalleryModal .carousel-control-next { z-index: 1066; pointer-events: auto !important; }
        #partImageGalleryModal .btn,
        #partImageGalleryModal .btn-close { pointer-events: auto !important; }
        @keyframes pdfUploadPulse {
            0%, 100% { opacity: 1; background-position: 0% 50%; }
            50%       { opacity: 0.55; background-position: 100% 50%; }
        }
    </style>

    <div class="single-page-header" data-background-image="{{ asset('asset/images/banner-auto-insurance.jpg') }}">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="single-page-header-inner">
                        <div class="left-side">
                            <div class="header-image"><a href="single-company-profile.html"><img
                                        src="{{ asset('asset/images/company-logo-03a.png') }}" alt=""></a></div>
                            <div class="header-details">
                                {{-- <h3>{{$proforma->insurance->name}}</h3> --}}
                                <h5>File #: {{ $proforma->file_number ?? 'N/A' }}</h5>
                                <ul>
                                    <li><i class="icon-feather-credit-card"></i> Plate Number:
                                        {{ $proforma->license_plate_number ?? 'N/A' }}</li>
                                    <li><i class="icon-feather-settings"></i> Chassis Number: {{ $proforma->chassis_number ?? 'N/A' }}</li>
                                    <li><i class="icon-material-outline-directions-car"></i> Year:
                                        {{ $proforma->year ?? 'N/A' }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        // Centralized role resolution: a user "acts as shop" (sees the parts pricing
        // table, submits via the shop route/flow) when they are a shop, OR they are a
        // dual-service garage (shop_garage=1) viewing a dual-service (insurance_shop_garage)
        // proforma. Any other garage — including a dual-service garage on a non-dual
        // proforma — is treated as a plain garage (single amount field).
        $isDualServiceProforma = $proforma->isShopGarageInsurance();
        $actsAsShop = auth()->check() && (
            auth()->user()->role === 'shop'
            || ($isDualServiceProforma && auth()->user()->shop_garage == 1)
        );
    @endphp

    @if(isset($proforma->images) && count($proforma->images) > 0)
        @php
            $proformaImagePaths = $proforma->images
                ->pluck('path')
                ->map(function ($path) {
                    return asset($path ?? '');
                })
                ->values()
                ->all();
            $proformaImageCount = $proforma->images->count();
        @endphp
        <div style="text-align: center; margin-bottom: 20px;">
            <button type="button" class="btn btn-outline-primary"
                onclick='openPartImageModal(@json($proformaImagePaths))'>
                View Image{{ $proformaImageCount > 1 ? 's' : '' }} ({{ $proformaImageCount }})
            </button>
        </div>
    @endif

    <div class="container">
        <div class="row">
            <div class="col-xl-4 col-lg-4">
                <div class="sidebar-container">
                    <div class="sidebar-widget">
                        <div class="job-overview">
                            <div class="job-overview-headline">Proforma Summary</div>
                            <div class="job-overview-inner">
                                <ul>
                                    <li>
                                        <i class="icon-material-outline-directions-car"></i>
                                        <span>Operator Phone Number</span>
                                        <h5>{{ $proforma->agent_phone_number ?? 'N/A' }}</h5>
                                    </li>
                                    <li>
                                        <i class="icon-material-outline-directions-car"></i>
                                        <span>{{ $proforma->brand->name ?? 'N/A' }}</span>
                                        <h5>{{ $proforma->model ?? 'N/A' }}</h5>
                                    </li>
                                    @if ($proforma->damage_severity && !($actsAsShop && $proforma->isShopOnlyInsurance()))
                                        <li>
                                            <i class="icon-material-outline-report-problem"></i>
                                            <span>Damage Severity</span>
                                            <h5>
                                                @php
                                                    $severityLabels = [
                                                        'minor' => 'Minor (Remote Fill) - ቀላል',
                                                        'major' => 'Major (Garage Required) - ከባድ',
                                                        'severe' => 'Severe (Site Visit) - ከፍተኛ',
                                                    ];
                                                    $severityColors = [
                                                        'minor' => '#4dd0c4',
                                                        'major' => '#fb923c',
                                                        'severe' => '#ef4444',
                                                    ];
                                                    $sev = $proforma->damage_severity;
                                                @endphp
                                                <span style="color: {{ $severityColors[$sev] ?? '#fff' }}; font-weight: 700;">
                                                    {{ $severityLabels[$sev] ?? ucfirst($sev) }}
                                                </span>
                                            </h5>
                                        </li>
                                    @endif
                                    @if ($actsAsShop)
                                        @if ($proforma->isFromOthers())
                                            <li>
                                                <i class="icon-material-outline-settings"></i>
                                                <span>Spare Part Shop</span>
                                                <h5>
                                                    @if ($proforma->isEteraCheretaMode())
                                                        ∞ Sparepart Shops Remaining
                                                    @else
                                                        {{ ($proforma->required_number_of_shops ?? 0) - ($proforma->applicationsFromShops()->count() ?? 0) }}
                                                        Sparepart Shops Remaining
                                                    @endif
                                                </h5>
                                            </li>
                                        @elseif($proforma->isFromInsurance())
                                            <li>
                                                <i class="icon-material-outline-settings"></i>
                                                <span>Spare Part Shop</span>
                                                <h5>{{ ($proforma->required_number_of_shops ?? 0) - ($proforma->applicationsFromShops()->count() ?? 0) }}
                                                    Sparepart Shops Remaining</h5>
                                            </li>
                                        @endif
                                    @elseif(auth()->check() && auth()->user()->role == 'garage')
                                        <li>
                                            <i class="icon-line-awesome-wrench"></i>
                                            <span>Garage</span>
                                            <h5>{{ ($proforma->required_number_of_garages ?? 0) - ($proforma->applicationsFromGarages()->count() ?? 0) }}
                                                Garages Remaining</h5>
                                        </li>
                                    @endif
                                    <li>
                                        <i class="icon-material-outline-access-time"></i>
                                        <span>Date Posted</span>
                                        <h5>{{ $proforma->created_at->diffForHumans() ?? 'N/A' }}</h5>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    @if (isset($proforma->audios) && count($proforma->audios))
                        <div>
                            <div class="job-overview margin-bottom-10">
                                <div class="job-overview-headline">Audio</div>
                            </div>
                            @foreach ($proforma->audios as $audio)
                                <div class="player">
                                    <audio controls>
                                        <source src="{{ $audio->url ?? '' }}" type="audio/mp3">
                                    </audio>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($proforma->voice_note_path ?? false)
                        <div>
                            <div class="job-overview margin-bottom-10">
                                <div class="job-overview-headline">Voice Note</div>
                            </div>
                            <div class="player">
                                <audio controls>
                                    <source src="{{ url('storage/' . $proforma->voice_note_path) }}" type="audio/webm">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                        </div>
                    @endif
                    <br>

                    @if (isset($proforma->videos) && count($proforma->videos))
                        <div class="job-overview margin-bottom-10">
                            <div class="job-overview-headline">Video</div>
                        </div>
                        <div>
                            @foreach ($proforma->videos as $video)
                                <video controls style="width: 100%; height: auto; max-height: 300px;">
                                    <source src="{{ $video->url ?? '' }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-xl-8 col-lg-8">

                @if(auth()->check() && auth()->user()->role === 'shop' && isset($lockedDataByPartId) && $lockedDataByPartId->isNotEmpty())
                <div style="background: rgba(251,146,60,0.08); border: 1px solid rgba(251,146,60,0.35); border-radius: 10px; padding: 14px 18px; margin-bottom: 18px; display: flex; align-items: flex-start; gap: 12px;">
                    <span style="font-size: 1.4rem; line-height:1;">&#x1F512;</span>
                    <div>
                        <strong style="color: #fb923c; font-size: 0.95rem;">Partial Proforma — Group {{ $assignedGroup ?? '' }}</strong>
                        <p style="margin: 4px 0 0; font-size: 0.85rem; color: rgba(255,255,255,0.65); line-height: 1.5;">
                            Some parts below have already been priced by another shop. Locked parts are shown in orange — only enter prices for the <strong style="color:#fff;">unlocked</strong> parts you can supply.
                        </p>
                    </div>
                </div>
                @endif

                @if(in_array(optional($proforma->poster)->role, ['insurance', 'insurance_agent']))
                    @include('components.insurance-letterhead', ['poster' => $proforma->poster, 'section' => 'header'])
                    @php
                        $insuranceForStamp = $proforma->poster->role === 'insurance_agent' ? $proforma->poster->parentInsurance : $proforma->poster;
                        $stampPath = $insuranceForStamp?->stamp_image ? preg_replace('#^public/#', '', $insuranceForStamp->stamp_image) : null;
                    @endphp
                @endif

                <form
                    action="{{ auth()->check() && auth()->user()->role === 'garage' && !$actsAsShop ? route('garage.proforma.apply', $proforma->id) : route('proforma.apply', $proforma->id) }}"
                    method="POST" id="proforma-quote-form" novalidate>
                    @csrf
                    <input type="hidden" name="application_mode" value="{{ $applicationMode ?? '' }}">
                    <input type="hidden" name="assigned_group" value="{{ $assignedGroup ?? '' }}">
                    <div id="price-table-section" style="position:relative;">
                    <div class="table-container" style="position:relative;">
                        @if($stampPath)
                            <div style="position:absolute;bottom:10px;left:10px;width:200px;height:200px;opacity:0.7;transform:rotate(10deg);pointer-events:none;z-index:5;">
                                <img src="{{ asset('storage/' . $stampPath) }}" alt="Insurance Stamp" style="width:200px;height:200px;border-radius:50%;object-fit:cover;" />
                            </div>
                        @endif
                        <table class="basic-table">
                            <thead>
    <tr>
        <th colspan="{{ $actsAsShop ? 10 : 8 }}"
            style="font-weight: bold; text-align: center;">
            Spare Parts that need to be changed
        </th>
    </tr>
                                <tr>
                                    <th style="width: 1%;">#</th>
                                    <th>Image</th>
                                    <th>Components</th>
                                    <th>Part Name and Number</th>
                                    <th>Grade</th>
                                    <th>Condition</th>
                                    <th>Country</th>
                                    <th>Qty</th>
                                    @if ($actsAsShop)
                                        <th style="min-width: 100px;">Unit Price (ETB)</th>
                                        <th style="min-width: 100px;">Total (ETB)</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($proforma->parts) && count($proforma->parts) > 0)
                                    @php
                                        $sortedParts = $proforma->parts->sortBy('id')->values();
                                        $hasRepairRenew = $sortedParts->whereNotNull('repair_renew')->count() > 0;
                                        $showSplit   = !$actsAsShop && $hasRepairRenew;
                                        $showDualSplit = $actsAsShop && $isDualServiceProforma && $hasRepairRenew;
                                    @endphp
                                    @if ($showSplit)
                                        {{-- ── Garage split view: group parts by repair_renew ── --}}
                                        @php
                                            $renewParts  = $sortedParts->where('repair_renew', 'renew')->values();
                                            $repairParts = $sortedParts->where('repair_renew', 'repair')->values();
                                            $otherParts  = $sortedParts->filter(fn($p) => !in_array($p->repair_renew, ['renew', 'repair']))->values();
                                            $gIdx        = 0;
                                        @endphp
                                        {{-- Section: Renew --}}
                                        @if ($renewParts->count())
                                        <tr>
                                            <td colspan="8" style="background:rgba(77,208,196,0.12);color:#0a8f7d;font-weight:700;padding:10px 16px;border-bottom:2px solid rgba(77,208,196,0.35);">
                                                <i class="bx bx-refresh me-1"></i> Parts to Renew &nbsp;<span style="font-weight:400;font-size:0.88em;">({{ $renewParts->count() }})</span>
                                            </td>
                                        </tr>
                                        @foreach ($renewParts as $part)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td>
                                                    @php
                                                        $partImagePaths = [];
                                                        $imageCount = 0;
                                                        if(isset($part->images) && $part->images->count() > 0) {
                                                            $partImagePaths = $part->images->pluck('image_path')->map(fn($path) => asset('storage/' . ($path ?? '')))->values()->all();
                                                            $imageCount = $part->images->count();
                                                        }
                                                    @endphp
                                                    @if ($imageCount > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick='openPartImageModal(@json($partImagePaths))'>View Image{{ $imageCount > 1 ? 's' : '' }} ({{ $imageCount }})</button>
                                                    @else
                                                        <span class="text-muted">#N/A</span>
                                                    @endif
                                                </td>
                                                <td>{{ $part->component ?? 'N/A' }}</td>
                                                <td>{{ $part->number ?? 'N/A' }}</td>
                                                <td>{{ $part->grade ?? 'N/A' }}</td>
                                                <td>{{ $part->condition ?? 'N/A' }}</td>
                                                <td>{{ $part->country ?? 'N/A' }}</td>
                                                <td>{{ $part->quantity ?? 0 }}
                                                    <input type="hidden" class="row-qty" value="{{ $part->quantity ?? 0 }}">
                                                    <input type="hidden" name="part_id[{{ $gIdx }}]" value="{{ $part->id ?? '' }}">
                                                </td>
                                            </tr>
                                            @php $gIdx++ @endphp
                                        @endforeach
                                        @endif
                                        {{-- Section: Repair --}}
                                        @if ($repairParts->count())
                                        <tr>
                                            <td colspan="8" style="background:rgba(251,146,60,0.12);color:#b85a0d;font-weight:700;padding:10px 16px;border-bottom:2px solid rgba(251,146,60,0.35);">
                                                <i class="bx bx-wrench me-1"></i> Parts to Repair &nbsp;<span style="font-weight:400;font-size:0.88em;">({{ $repairParts->count() }})</span>
                                            </td>
                                        </tr>
                                        @foreach ($repairParts as $part)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td>
                                                    @php
                                                        $partImagePaths = [];
                                                        $imageCount = 0;
                                                        if(isset($part->images) && $part->images->count() > 0) {
                                                            $partImagePaths = $part->images->pluck('image_path')->map(fn($path) => asset('storage/' . ($path ?? '')))->values()->all();
                                                            $imageCount = $part->images->count();
                                                        }
                                                    @endphp
                                                    @if ($imageCount > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick='openPartImageModal(@json($partImagePaths))'>View Image{{ $imageCount > 1 ? 's' : '' }} ({{ $imageCount }})</button>
                                                    @else
                                                        <span class="text-muted">#N/A</span>
                                                    @endif
                                                </td>
                                                <td>{{ $part->component ?? 'N/A' }}</td>
                                                <td>{{ $part->number ?? 'N/A' }}</td>
                                                <td>{{ $part->grade ?? 'N/A' }}</td>
                                                <td>{{ $part->condition ?? 'N/A' }}</td>
                                                <td>{{ $part->country ?? 'N/A' }}</td>
                                                <td>{{ $part->quantity ?? 0 }}
                                                    <input type="hidden" class="row-qty" value="{{ $part->quantity ?? 0 }}">
                                                    <input type="hidden" name="part_id[{{ $gIdx }}]" value="{{ $part->id ?? '' }}">
                                                </td>
                                            </tr>
                                            @php $gIdx++ @endphp
                                        @endforeach
                                        @endif
                                        {{-- Section: Unspecified (no repair_renew) --}}
                                        @if ($otherParts->count())
                                        <tr>
                                            <td colspan="8" style="background:rgba(108,117,125,0.08);color:#5c6371;font-weight:700;padding:10px 16px;border-bottom:2px solid rgba(108,117,125,0.2);">
                                                Other Parts &nbsp;<span style="font-weight:400;font-size:0.88em;">({{ $otherParts->count() }})</span>
                                            </td>
                                        </tr>
                                        @foreach ($otherParts as $part)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td>
                                                    @php
                                                        $partImagePaths = [];
                                                        $imageCount = 0;
                                                        if(isset($part->images) && $part->images->count() > 0) {
                                                            $partImagePaths = $part->images->pluck('image_path')->map(fn($path) => asset('storage/' . ($path ?? '')))->values()->all();
                                                            $imageCount = $part->images->count();
                                                        }
                                                    @endphp
                                                    @if ($imageCount > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick='openPartImageModal(@json($partImagePaths))'>View Image{{ $imageCount > 1 ? 's' : '' }} ({{ $imageCount }})</button>
                                                    @else
                                                        <span class="text-muted">#N/A</span>
                                                    @endif
                                                </td>
                                                <td>{{ $part->component ?? 'N/A' }}</td>
                                                <td>{{ $part->number ?? 'N/A' }}</td>
                                                <td>{{ $part->grade ?? 'N/A' }}</td>
                                                <td>{{ $part->condition ?? 'N/A' }}</td>
                                                <td>{{ $part->country ?? 'N/A' }}</td>
                                                <td>{{ $part->quantity ?? 0 }}
                                                    <input type="hidden" class="row-qty" value="{{ $part->quantity ?? 0 }}">
                                                    <input type="hidden" name="part_id[{{ $gIdx }}]" value="{{ $part->id ?? '' }}">
                                                </td>
                                            </tr>
                                            @php $gIdx++ @endphp
                                        @endforeach
                                        @endif
                                    @elseif ($showDualSplit)
                                        @php
                                            $partsWithIndex = $sortedParts->map(function ($p, $i) {
                                                return ['part' => $p, 'idx' => $i];
                                            });
                                            $renewParts  = $partsWithIndex->filter(fn($x) => ($x['part']->repair_renew ?? null) === 'renew')->values();
                                            $repairParts = $partsWithIndex->filter(fn($x) => ($x['part']->repair_renew ?? null) === 'repair')->values();
                                            $otherParts  = $partsWithIndex->filter(fn($x) => !in_array(($x['part']->repair_renew ?? null), ['renew', 'repair']))->values();
                                            $displayNo   = 0;
                                        @endphp
                                        @if ($renewParts->count())
                                        <tr>
                                            <td colspan="10" style="background:rgba(77,208,196,0.12);color:#0a8f7d;font-weight:700;padding:10px 16px;border-bottom:2px solid rgba(77,208,196,0.35);">
                                                <i class="bx bx-refresh me-1"></i> Parts to Renew &nbsp;<span style="font-weight:400;font-size:0.88em;">({{ $renewParts->count() }})</span>
                                            </td>
                                        </tr>
                                        @foreach ($renewParts as $item)
                                            @php
                                                $part = $item['part'];
                                                $idx = $item['idx'];
                                                $displayNo++;

                                                $partImagePaths = [];
                                                $imageCount = 0;
                                                if(isset($part->images) && $part->images->count() > 0) {
                                                    $partImagePaths = $part->images
                                                        ->pluck('image_path')
                                                        ->map(function ($path) {
                                                            return asset('storage/' . ($path ?? ''));
                                                        })
                                                        ->values()
                                                        ->all();
                                                    $imageCount = $part->images->count();
                                                }

                                                $lockedData = isset($lockedDataByPartId) ? ($lockedDataByPartId[$part->id] ?? null) : null;
                                                $isLocked   = $lockedData !== null;
                                                $lockedPrice = $isLocked ? (float) ($lockedData['unit_price'] ?? 0) : 0;
                                                $lockedTotal = $isLocked ? $lockedPrice * ($part->quantity ?? 1) : 0;
                                            @endphp
                                            <tr>
                                                <td data-label="Index">{{ $displayNo }}</td>
                                                <td>
                                                    @if ($imageCount > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick='openPartImageModal(@json($partImagePaths))'>
                                                            View Image{{ $imageCount > 1 ? 's' : '' }} ({{ $imageCount }})
                                                        </button>
                                                    @else
                                                        <span class="text-muted">#N/A</span>
                                                    @endif
                                                </td>
                                                <td data-label="Component">{{ $part->component ?? 'N/A' }}</td>
                                                <td data-label="Part #">{{ $part->number ?? 'N/A' }}</td>
                                                <td data-label="Grade">{{ $part->grade ?? 'N/A' }}</td>
                                                <td data-label="Condition">{{ $part->condition ?? 'N/A' }}</td>
                                                <td data-label="Country">{{ $part->country ?? 'N/A' }}</td>
                                                <td data-label="Qty">{{ $part->quantity ?? 0 }}</td>
                                                <input type="hidden" class="row-qty" value="{{ $part->quantity ?? 0 }}">
                                                <input type="hidden" name="part_id[{{ $idx }}]" value="{{ $part->id ?? '' }}">

                                                @if($isLocked)
                                                    <td style="background: rgba(251,146,60,0.08); border-color: rgba(251,146,60,0.25);">
                                                        <div style="display:flex; align-items:center; gap:5px;">
                                                            <span style="color:#fb923c; font-size:0.85rem;">&#x1F512;</span>
                                                            <input type="number"
                                                                name="total[{{ $idx }}]"
                                                                class="with-border unit-price-input"
                                                                value="{{ $lockedPrice }}"
                                                                step="any" min="1"
                                                                readonly disabled
                                                                style="background: rgba(251,146,60,0.06); color: rgba(255,255,255,0.45); cursor: not-allowed; border-color: rgba(251,146,60,0.2);">
                                                            <input type="hidden" name="total[{{ $idx }}]" value="{{ $lockedPrice }}">
                                                        </div>
                                                    </td>
                                                    <td style="background: rgba(251,146,60,0.08); border-color: rgba(251,146,60,0.25);">
                                                        <input type="number" class="with-border part-total"
                                                            value="{{ $lockedTotal }}" readonly disabled
                                                            style="background: rgba(251,146,60,0.06); color: rgba(255,255,255,0.45); cursor: not-allowed; border-color: rgba(251,146,60,0.2);">
                                                    </td>
                                                @else
                                                    <td>
                                                        <input type="number"
                                                            name="total[{{ $idx }}]"
                                                            class="with-border unit-price-input" placeholder="unit price" value=""
                                                            step="any" min="1"
                                                            oninvalid="this.setCustomValidity('Price must be at least 1 ETB, or leave blank if unavailable')"
                                                            oninput="this.setCustomValidity('')">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="with-border part-total" placeholder="Total"
                                                            value="0" readonly disabled>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        @endif

                                        @if ($repairParts->count())
                                        <tr>
                                            <td colspan="10" style="background:rgba(251,146,60,0.12);color:#b85a0d;font-weight:700;padding:10px 16px;border-bottom:2px solid rgba(251,146,60,0.35);">
                                                <i class="bx bx-wrench me-1"></i> Parts to Repair &nbsp;<span style="font-weight:400;font-size:0.88em;">({{ $repairParts->count() }})</span>
                                            </td>
                                        </tr>
                                        @foreach ($repairParts as $item)
                                            @php
                                                $part = $item['part'];
                                                $idx = $item['idx'];
                                                $displayNo++;

                                                $partImagePaths = [];
                                                $imageCount = 0;
                                                if(isset($part->images) && $part->images->count() > 0) {
                                                    $partImagePaths = $part->images
                                                        ->pluck('image_path')
                                                        ->map(function ($path) {
                                                            return asset('storage/' . ($path ?? ''));
                                                        })
                                                        ->values()
                                                        ->all();
                                                    $imageCount = $part->images->count();
                                                }

                                                $lockedData = isset($lockedDataByPartId) ? ($lockedDataByPartId[$part->id] ?? null) : null;
                                                $isLocked   = $lockedData !== null;
                                                $lockedPrice = $isLocked ? (float) ($lockedData['unit_price'] ?? 0) : 0;
                                                $lockedTotal = $isLocked ? $lockedPrice * ($part->quantity ?? 1) : 0;
                                            @endphp
                                            <tr>
                                                <td data-label="Index">{{ $displayNo }}</td>
                                                <td>
                                                    @if ($imageCount > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick='openPartImageModal(@json($partImagePaths))'>
                                                            View Image{{ $imageCount > 1 ? 's' : '' }} ({{ $imageCount }})
                                                        </button>
                                                    @else
                                                        <span class="text-muted">#N/A</span>
                                                    @endif
                                                </td>
                                                <td data-label="Component">{{ $part->component ?? 'N/A' }}</td>
                                                <td data-label="Part #">{{ $part->number ?? 'N/A' }}</td>
                                                <td data-label="Grade">{{ $part->grade ?? 'N/A' }}</td>
                                                <td data-label="Condition">{{ $part->condition ?? 'N/A' }}</td>
                                                <td data-label="Country">{{ $part->country ?? 'N/A' }}</td>
                                                <td data-label="Qty">{{ $part->quantity ?? 0 }}</td>
                                                <input type="hidden" class="row-qty" value="{{ $part->quantity ?? 0 }}">
                                                <input type="hidden" name="part_id[{{ $idx }}]" value="{{ $part->id ?? '' }}">

                                                @if($isLocked)
                                                    <td style="background: rgba(251,146,60,0.08); border-color: rgba(251,146,60,0.25);">
                                                        <div style="display:flex; align-items:center; gap:5px;">
                                                            <span style="color:#fb923c; font-size:0.85rem;">&#x1F512;</span>
                                                            <input type="number"
                                                                name="total[{{ $idx }}]"
                                                                class="with-border unit-price-input"
                                                                value="{{ $lockedPrice }}"
                                                                step="any" min="1"
                                                                readonly disabled
                                                                style="background: rgba(251,146,60,0.06); color: rgba(255,255,255,0.45); cursor: not-allowed; border-color: rgba(251,146,60,0.2);">
                                                            <input type="hidden" name="total[{{ $idx }}]" value="{{ $lockedPrice }}">
                                                        </div>
                                                    </td>
                                                    <td style="background: rgba(251,146,60,0.08); border-color: rgba(251,146,60,0.25);">
                                                        <input type="number" class="with-border part-total"
                                                            value="{{ $lockedTotal }}" readonly disabled
                                                            style="background: rgba(251,146,60,0.06); color: rgba(255,255,255,0.45); cursor: not-allowed; border-color: rgba(251,146,60,0.2);">
                                                    </td>
                                                @else
                                                    <td>
                                                        <input type="number"
                                                            name="total[{{ $idx }}]"
                                                            class="with-border unit-price-input" placeholder="unit price" value=""
                                                            step="any" min="1"
                                                            oninvalid="this.setCustomValidity('Price must be at least 1 ETB, or leave blank if unavailable')"
                                                            oninput="this.setCustomValidity('')">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="with-border part-total" placeholder="Total"
                                                            value="0" readonly disabled>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        @endif

                                        @if ($otherParts->count())
                                        <tr>
                                            <td colspan="10" style="background:rgba(108,117,125,0.08);color:#5c6371;font-weight:700;padding:10px 16px;border-bottom:2px solid rgba(108,117,125,0.2);">
                                                Other Parts &nbsp;<span style="font-weight:400;font-size:0.88em;">({{ $otherParts->count() }})</span>
                                            </td>
                                        </tr>
                                        @foreach ($otherParts as $item)
                                            @php
                                                $part = $item['part'];
                                                $idx = $item['idx'];
                                                $displayNo++;

                                                $partImagePaths = [];
                                                $imageCount = 0;
                                                if(isset($part->images) && $part->images->count() > 0) {
                                                    $partImagePaths = $part->images
                                                        ->pluck('image_path')
                                                        ->map(function ($path) {
                                                            return asset('storage/' . ($path ?? ''));
                                                        })
                                                        ->values()
                                                        ->all();
                                                    $imageCount = $part->images->count();
                                                }

                                                $lockedData = isset($lockedDataByPartId) ? ($lockedDataByPartId[$part->id] ?? null) : null;
                                                $isLocked   = $lockedData !== null;
                                                $lockedPrice = $isLocked ? (float) ($lockedData['unit_price'] ?? 0) : 0;
                                                $lockedTotal = $isLocked ? $lockedPrice * ($part->quantity ?? 1) : 0;
                                            @endphp
                                            <tr>
                                                <td data-label="Index">{{ $displayNo }}</td>
                                                <td>
                                                    @if ($imageCount > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick='openPartImageModal(@json($partImagePaths))'>
                                                            View Image{{ $imageCount > 1 ? 's' : '' }} ({{ $imageCount }})
                                                        </button>
                                                    @else
                                                        <span class="text-muted">#N/A</span>
                                                    @endif
                                                </td>
                                                <td data-label="Component">{{ $part->component ?? 'N/A' }}</td>
                                                <td data-label="Part #">{{ $part->number ?? 'N/A' }}</td>
                                                <td data-label="Grade">{{ $part->grade ?? 'N/A' }}</td>
                                                <td data-label="Condition">{{ $part->condition ?? 'N/A' }}</td>
                                                <td data-label="Country">{{ $part->country ?? 'N/A' }}</td>
                                                <td data-label="Qty">{{ $part->quantity ?? 0 }}</td>
                                                <input type="hidden" class="row-qty" value="{{ $part->quantity ?? 0 }}">
                                                <input type="hidden" name="part_id[{{ $idx }}]" value="{{ $part->id ?? '' }}">

                                                @if($isLocked)
                                                    <td style="background: rgba(251,146,60,0.08); border-color: rgba(251,146,60,0.25);">
                                                        <div style="display:flex; align-items:center; gap:5px;">
                                                            <span style="color:#fb923c; font-size:0.85rem;">&#x1F512;</span>
                                                            <input type="number"
                                                                name="total[{{ $idx }}]"
                                                                class="with-border unit-price-input"
                                                                value="{{ $lockedPrice }}"
                                                                step="any" min="1"
                                                                readonly disabled
                                                                style="background: rgba(251,146,60,0.06); color: rgba(255,255,255,0.45); cursor: not-allowed; border-color: rgba(251,146,60,0.2);">
                                                            <input type="hidden" name="total[{{ $idx }}]" value="{{ $lockedPrice }}">
                                                        </div>
                                                    </td>
                                                    <td style="background: rgba(251,146,60,0.08); border-color: rgba(251,146,60,0.25);">
                                                        <input type="number" class="with-border part-total"
                                                            value="{{ $lockedTotal }}" readonly disabled
                                                            style="background: rgba(251,146,60,0.06); color: rgba(255,255,255,0.45); cursor: not-allowed; border-color: rgba(251,146,60,0.2);">
                                                    </td>
                                                @else
                                                    <td>
                                                        <input type="number"
                                                            name="total[{{ $idx }}]"
                                                            class="with-border unit-price-input" placeholder="unit price" value=""
                                                            step="any" min="1"
                                                            oninvalid="this.setCustomValidity('Price must be at least 1 ETB, or leave blank if unavailable')"
                                                            oninput="this.setCustomValidity('')">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="with-border part-total" placeholder="Total"
                                                            value="0" readonly disabled>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        @endif
                                    @else
                                        {{-- ── Original view (shops, or no repair_renew data) ── --}}
                                        @foreach ($sortedParts as $part)
                                            <tr>
                                                <td data-label="Index">{{ $loop->index + 1 }}</td>
                                                {{-- START: PART IMAGES BUTTON (Displays the modal) --}}
                                                <td>
                                                    @php
                                                        // Safely handle part images with null checks
                                                        $partImagePaths = [];
                                                        $imageCount = 0;
                                                        
                                                        if(isset($part->images) && $part->images->count() > 0) {
                                                            $partImagePaths = $part->images
                                                                ->pluck('image_path')
                                                                ->map(function ($path) {
                                                                    return asset('storage/' . ($path ?? ''));
                                                                })
                                                                ->values()
                                                                ->all();
                                                            $imageCount = $part->images->count();
                                                        }
                                                    @endphp

                                                    @if ($imageCount > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick='openPartImageModal(@json($partImagePaths))'>
                                                            View Image{{ $imageCount > 1 ? 's' : '' }} ({{ $imageCount }})
                                                        </button>
                                                    @else
                                                        <span class="text-muted">#N/A</span>
                                                    @endif
                                                </td>
                                                {{-- END: PART IMAGES BUTTON --}}

                                                <td data-label="Component">{{ $part->component ?? 'N/A' }}</td>
                                                <td data-label="Part #">{{ $part->number ?? 'N/A' }}</td>
                                                <td data-label="Grade">{{ $part->grade ?? 'N/A' }}</td>
                                                <td data-label="Condition">{{ $part->condition ?? 'N/A' }}</td>
                                                <td data-label="Country">{{ $part->country ?? 'N/A' }}</td>
                                                <td data-label="Qty">{{ $part->quantity ?? 0 }}</td>
                                                <input type="hidden" class="row-qty" value="{{ $part->quantity ?? 0 }}">
                                                {{-- Hidden input for part ID to link quote amount to part --}}
                                                <input type="hidden" name="part_id[{{ $loop->index }}]" value="{{ $part->id ?? '' }}">

                                                @if ($actsAsShop)
                                                    @php
                                                        $lockedData = isset($lockedDataByPartId) ? ($lockedDataByPartId[$part->id] ?? null) : null;
                                                        $isLocked   = $lockedData !== null;
                                                        $lockedPrice = $isLocked ? (float) ($lockedData['unit_price'] ?? 0) : 0;
                                                        $lockedTotal = $isLocked ? $lockedPrice * ($part->quantity ?? 1) : 0;
                                                    @endphp
                                                    @if($isLocked)
                                                        <td style="background: rgba(251,146,60,0.08); border-color: rgba(251,146,60,0.25);">
                                                            <div style="display:flex; align-items:center; gap:5px;">
                                                                <span style="color:#fb923c; font-size:0.85rem;">&#x1F512;</span>
                                                                <input type="number"
                                                                    name="total[{{ $loop->index }}]"
                                                                    class="with-border unit-price-input"
                                                                    value="{{ $lockedPrice }}"
                                                                    step="any" min="1"
                                                                    readonly disabled
                                                                    style="background: rgba(251,146,60,0.06); color: rgba(255,255,255,0.45); cursor: not-allowed; border-color: rgba(251,146,60,0.2);">
                                                                <input type="hidden" name="total[{{ $loop->index }}]" value="{{ $lockedPrice }}">
                                                            </div>
                                                        </td>
                                                        <td style="background: rgba(251,146,60,0.08); border-color: rgba(251,146,60,0.25);">
                                                            <input type="number" class="with-border part-total"
                                                                value="{{ $lockedTotal }}" readonly disabled
                                                                style="background: rgba(251,146,60,0.06); color: rgba(255,255,255,0.45); cursor: not-allowed; border-color: rgba(251,146,60,0.2);">
                                                        </td>
                                                    @else
                                                        <td>
                                                            <input type="number"
                                                                name="total[{{ $loop->index }}]"
                                                                class="with-border unit-price-input" placeholder="unit price" value=""
                                                                step="any" min="1"
                                                                oninvalid="this.setCustomValidity('Price must be at least 1 ETB, or leave blank if unavailable')"
                                                                oninput="this.setCustomValidity('')">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="with-border part-total" placeholder="Total"
                                                                value="0" readonly disabled>
                                                        </td>
                                                    @endif
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif
                                @else
                                    <tr>
                                        <td colspan="{{ $actsAsShop ? 10 : 8 }}" class="text-center">
                                            No parts found for this proforma.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="{{ $actsAsShop ? 8 : 6 }}"></td>
                                    <td class="text-align-right" colspan="1">
                                        @if ($actsAsShop)
                                            <p style="margin: 0; padding: 0;">TOTAL PARTS PRICE (Net)</p>
                                        @else
                                            <p style="margin: 0; padding: 0;">GARAGE REPAIR SERVICE ESTIMATE (Net)</p>
                                        @endif
                                        <small style="color: #f5365c;">(Prices entered here are <strong>excluding VAT</strong> – 15% VAT will be added automatically)</small>
                                    </td>
                                    <td colspan="2">
                                        <input type="number" name="amount" class="with-border" required
                                            id="total-amount" 
                                            {{ $actsAsShop ? 'disabled' : '' }} min="1"
                                            step="any">
                                        @error('amount')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="{{ $actsAsShop ? 8 : 6 }}"></td>
                                    <td class="text-align-right" colspan="1">
                                        VAT (15%)
                                    </td>
                                    <td colspan="2">
                                        <input type="number" id="vat-amount" class="with-border" value="0" disabled>
                                    </td>
                                    <input type="hidden" name="final-amount" id="final-amount-hidden"
                                        class="with-border">
                                </tr>
                                <tr>
                                    <td colspan="{{ $actsAsShop ? 8 : 6 }}"></td>
                                    <td class="text-align-right" colspan="1">
                                        Quote Expiry Date
                                    </td>
                                    <td colspan="2">
                                        <input type="date" id="expiry_date" name="expiry_date" class="with-border"
                                            placeholder="Select expiry date">
                                        @error('expiry_date')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="{{ $actsAsShop ? 8 : 6 }}"></td>
                                    <td class="text-align-right" colspan="1">
                                        Grand Total (VAT Included)
                                    </td>
                                    <td colspan="2">
                                        <input type="number" id="grand-total" class="with-border" value="0"
                                            disabled>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    </div>{{-- /price-table-section --}}

                    @if (auth()->check() && !$proforma->userAlreadyApplied(auth()->user()->id) && auth()->user()->dealers)
                    <div class="margin-top-15" style="background: rgba(13,148,136,0.05); border: 1px solid rgba(13,148,136,0.15); border-radius: 8px; padding: 14px 16px;">
                        <label for="application_notes" style="font-weight: 600; font-size: 0.88rem; color: var(--etera-teal-light, #4dd0c4); display:block; margin-bottom: 6px;">
                            <i class="bx bx-message-detail" style="margin-right:4px;"></i>Additional Notes <span style="font-weight:400; color:#aaa;">(optional)</span>
                        </label>
                        <textarea name="notes" id="application_notes" rows="3"
                            class="with-border"
                            style="width:100%; resize:vertical; min-height:72px; max-height:180px; font-size:0.9rem; border-radius:6px; padding:8px 10px;"
                            placeholder="Availability, delivery time, warranty, or any other details the poster should know…"></textarea>
                    </div>
                    @endif

                    @if (auth()->check() && !$proforma->userAlreadyApplied(auth()->user()->id))
                    @if (auth()->user()->dealers || auth()->user()->shop_garage == 1)
                    {{-- Submission Mode Toggle (only for dealers and dual service providers who can choose between prices and PDF) --}}
                    <div class="margin-top-15" id="submissionModeToggle" style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button type="button" id="modePriceBtn" onclick="setSubmissionMode('price')"
                            style="flex:1; min-width:140px; padding:9px 14px; border-radius:8px; font-size:0.85rem; font-weight:600; cursor:pointer; border:2px solid rgba(13,148,136,0.5); background:rgba(13,148,136,0.18); color:var(--etera-teal-light,#4dd0c4); transition:all .2s;">
                            <i class="bx bx-list-ul"></i> Enter Prices
                        </button>
                        <button type="button" id="modePdfBtn" onclick="setSubmissionMode('pdf')"
                            style="flex:1; min-width:140px; padding:9px 14px; border-radius:8px; font-size:0.85rem; font-weight:600; cursor:pointer; border:2px solid rgba(255,255,255,0.12); background:transparent; color:#aaa; transition:all .2s;">
                            <i class="bx bxs-file-pdf"></i> Upload PDF / Image
                        </button>
                    </div>
                    @endif
                    <input type="hidden" name="submission_mode" id="hiddenSubmissionMode" value="price">

                    {{-- PDF Upload Section (hidden until mode=pdf) --}}
                    <div class="margin-top-15" style="background: rgba(13,148,136,0.05); border: 1px dashed rgba(13,148,136,0.3); border-radius: 8px; padding: 14px 16px; display:none;" id="pdfUploadSection">
                        <label style="font-weight: 600; font-size: 0.88rem; color: var(--etera-teal-light, #4dd0c4); display:block; margin-bottom: 6px;">
                            <i class="bx bx-file" style="margin-right:4px;"></i>PDF or Image Quotation <span style="font-weight:400; color:#aaa;">(max 10MB — PDF, JPG, PNG, WEBP)</span>
                        </label>
                        <input type="file" id="pdfFileInput" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.bmp" style="display:none;">
                        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <button type="button" onclick="document.getElementById('pdfFileInput').click()"
                                style="background:rgba(13,148,136,0.12); color:var(--etera-teal-light,#4dd0c4); border:1px solid rgba(13,148,136,0.3); border-radius:6px; padding:7px 14px; font-size:0.85rem; cursor:pointer;">
                                <i class="bx bx-upload"></i> Choose File
                            </button>
                            <span id="pdfFileLabel" style="font-size:0.85rem; color:#aaa;">No file chosen</span>
                            <button type="button" id="pdfClearBtn" onclick="clearPdfUpload()" style="display:none; background:transparent; color:#ef4444; border:none; cursor:pointer; font-size:0.82rem;">
                                <i class="bx bx-x"></i> Remove
                            </button>
                        </div>
                        <div id="pdfSizeError" style="color:#ef4444; font-size:0.8rem; margin-top:4px; display:none;">File too large. Maximum size is 10MB.</div>
                        <div id="pdfProcessing" style="margin-top:6px; display:none;">
                            <div style="font-size:0.8rem; color:var(--etera-teal-light,#4dd0c4); margin-bottom:4px;"><i class="bx bx-loader-alt bx-spin"></i> <span id="pdfProcessingMsg">Processing PDF…</span></div>
                            <div style="width:100%;background:rgba(255,255,255,0.1);border-radius:4px;overflow:hidden;height:5px;"><div id="pdfProgressBar" style="height:100%;width:0%;background:var(--etera-teal,#0d9488);border-radius:4px;transition:width 0.25s ease;"></div></div>
                        </div>
                    </div>
                    {{-- Hidden fields for PDF data (populated by JS) --}}
                    <input type="hidden" name="encrypted_pdf" id="hiddenEncryptedPdf">
                    <input type="hidden" name="encrypted_aes_key" id="hiddenEncryptedAesKey">
@endif
@if (auth()->check() && !$proforma->userAlreadyApplied(auth()->user()->id) && auth()->user()->role == 'garage' && !$actsAsShop)
    {{-- Only show price entry for plain garages (not acting as shop) --}}
    <input type="hidden" name="submission_mode" id="hiddenSubmissionMode" value="price">
@endif
                    <input type="hidden" name="aes_iv" id="hiddenAesIv">
                    <input type="hidden" name="pdf_data" id="hiddenPdfData">
                    <input type="hidden" name="pdf_filename" id="hiddenPdfFilename">

                    {{-- Garage application section (embedded in shop form for shop_garage users) --}}
                    @if (auth()->check() && auth()->user()->shop_garage == 1 && $isDualServiceProforma)
                    <div class="margin-top-30" style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 12px; padding: 20px;">
                        <h6 style="color: #60a5fa; font-weight: 600; margin-bottom: 15px;">
                            <i class="bx bx-buildings" style="margin-right: 6px;"></i>Garage Service Application
                        </h6>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 15px;">
                            Apply as a garage for repair service estimate (single total amount)
                        </p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <label style="font-weight: 600; font-size: 0.85rem; color: #60a5fa; display: block; margin-bottom: 6px;">
                                    Repair Service Estimate (Net, ETB)
                                </label>
                                <input type="number" name="garage_amount" class="with-border" min="1" step="any"
                                    style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.3); background: rgba(59, 130, 246, 0.05);">
                                @error('garage_amount')
                                    <span class="text-danger" style="font-size: 0.8rem;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label style="font-weight: 600; font-size: 0.85rem; color: #60a5fa; display: block; margin-bottom: 6px;">
                                    VAT (15%)
                                </label>
                                <input type="text" class="with-border" value="15% (auto-applied)" disabled
                                    style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.3); background: rgba(59, 130, 246, 0.05); color:#0f172a;">
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: 600; font-size: 0.85rem; color: #60a5fa; display: block; margin-bottom: 6px;">
                                Quote Expiry Date
                            </label>
                            <input type="date" name="garage_expiry_date" class="with-border" placeholder="Select expiry date"
                                style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.3); background: rgba(59, 130, 246, 0.05);">
                            @error('garage_expiry_date')
                                <span class="text-danger" style="font-size: 0.8rem;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: 600; font-size: 0.85rem; color: #60a5fa; display: block; margin-bottom: 6px;">
                                <i class="bx bx-message-detail" style="margin-right: 4px;"></i>Additional Notes <span style="font-weight: 400; color: #94a3b8;">(optional)</span>
                            </label>
                            <textarea name="garage_notes" rows="3"
                                class="with-border"
                                style="width: 100%; resize: vertical; min-height: 72px; max-height: 180px; font-size: 0.9rem; border-radius: 6px; padding: 8px 10px; border: 1px solid rgba(59, 130, 246, 0.3); background: rgba(59, 130, 246, 0.05);"
                                placeholder="Service details, timeline, warranty, or any other information…"></textarea>
                        </div>
                    </div>
                    @endif

                    @if (auth()->check() && !$proforma->userAlreadyApplied(auth()->user()->id))
                        <button type="submit" class="apply-now-button radius-30 margin-top-15" id="submitBtn">
                            <span class="btn-text">Apply Now <i class="icon-material-outline-arrow-right-alt"></i></span>
                            <span class="btn-loading" style="display: none;">
                                <i class="bx bx-loader-alt bx-spin"></i> Submitting...
                            </span>
                        </button>
                    @endif
                </form>

                @if(in_array(optional($proforma->poster)->role, ['insurance', 'insurance_agent']))
                    @include('components.insurance-letterhead', ['poster' => $proforma->poster, 'section' => 'footer'])
                @endif
            </div>
        </div>
    </div>

    {{-- START: PART IMAGE GALLERY MODAL (Bootstrap Carousel for reliability) --}}
    <div class="modal fade" id="partImageGalleryModal" tabindex="-1" aria-labelledby="partImageGalleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="partImageGalleryModalLabel">Part Images (<span id="partImageIndex"></span>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="console.log('clicked button Close (Header)'); closePartImageModal();"></button>
                </div>
                <div class="modal-body">
                    <div id="partImageCarousel" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner" id="partImageCarouselInner"></div>
                        <button class="carousel-control-prev custom-carousel-btn " type="button" data-bs-target="#partImageCarousel" data-bs-slide="prev" onclick="console.log('clicked button Previous')">
                            <</span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next custom-carousel-btn " type="button" data-bs-target="#partImageCarousel" data-bs-slide="next" onclick="console.log('clicked button Next')">
                            ></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <small class="text-muted" id="partImageCountText"></small>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="console.log('clicked button Close (Footer)'); closePartImageModal();">Close</button>
                </div>
            </div>
        </div>
    </div>
    {{-- END: PART IMAGE GALLERY MODAL --}}

    {{-- General Image Modal (Assuming this is for the main proforma images - kept for compatibility) --}}
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

    <script>{!! file_get_contents(base_path('resources/js/e2e-encryption.js')) !!}</script>
    <script>
        // START: JAVASCRIPT LOGIC

        // --- Calculation Logic ---
        /**
         * Calculates the total NET amount, applies a fixed 15% VAT, and updates the final fields.
         * Used for both 'shop' (multi-line calculation) and 'garage' (single field calculation).
         */
        const VAT_RATE = 15; // fixed VAT percentage
        let isCalculating = false; // Prevent infinite loops

        function calculateAmounts() {
            if (isCalculating) return; // Prevent recursive calls
            isCalculating = true;

            try {
                const isShopRole = {{ $actsAsShop ? 'true' : 'false' }};
                let netTotal = 0;

                if (isShopRole) {
                    // SHOP role: Calculate NET sum from all part total inputs
                    const totalInputs = document.querySelectorAll('.part-total');
                    totalInputs.forEach(input => {
                        const value = parseFloat(input.value) || 0;
                        netTotal += value;
                    });
                } else {
                    // GARAGE role: Use the value from the manually entered total-amount input (NET)
                    const totalAmountInput = document.getElementById('total-amount');
                    netTotal = totalAmountInput ? parseFloat(totalAmountInput.value) || 0 : 0;
                }

                // Update the main NET total amount field for consistency (only active for shop role)
                const totalAmountInput = document.getElementById('total-amount');
                if (isShopRole && totalAmountInput) {
                    totalAmountInput.value = netTotal ? netTotal.toFixed(2) : '';
                }

                const safeNet = Math.max(0, netTotal);
                const vatAmount = safeNet * (VAT_RATE / 100);
                const grossTotal = safeNet + vatAmount;

                // Update VAT and grand total fields
                const vatInput = document.getElementById('vat-amount');
                const grandTotalInput = document.getElementById('grand-total');
                const finalAmountHidden = document.getElementById('final-amount-hidden');

                if (vatInput) {
                    vatInput.value = vatAmount > 0 ? vatAmount.toFixed(2) : '';
                }
                if (grandTotalInput) {
                    grandTotalInput.value = grossTotal > 0 ? grossTotal.toFixed(2) : '';
                }
                if (finalAmountHidden) {
                    finalAmountHidden.value = grossTotal > 0 ? grossTotal.toFixed(2) : '';
                }
            } catch (error) {
                console.error('Error in calculateAmounts:', error);
            } finally {
                isCalculating = false;
            }
        }

        /**
         * Calculates the total for each row (Unit Price * Qty) and triggers the main calculation.
         * Only runs for the 'shop' role, otherwise acts as a direct trigger.
         */
        function updatePartAmounts() {
            if (isCalculating) return; // Prevent recursive calls
            
            try {
                const isShopRole = {{ $actsAsShop ? 'true' : 'false' }};

                if (isShopRole) {
                    const rows = document.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        const unitPriceInput = row.querySelector('.unit-price-input');
                        const qtyElement = row.querySelector('.row-qty');
                        const partTotalInput = row.querySelector('.part-total');

                        if (unitPriceInput && qtyElement && partTotalInput) {
                            const unitPrice = parseFloat(unitPriceInput.value) || 0;
                            const quantity = parseFloat(qtyElement.value) || 0;
                            const rowTotal = unitPrice * quantity;

                            partTotalInput.value = rowTotal.toFixed(2);
                        }
                    });
                }

                // Always call the main calculation function to update the footer totals
                calculateAmounts();
            } catch (error) {
                console.error('Error in updatePartAmounts:', error);
            }
        }

        /**
         * Handles form submission to show loading state.
         */
        function submitQuote(event) {
            const form = document.getElementById('proforma-quote-form');
            const submitBtn = document.getElementById('submitBtn');

            if (!form || !submitBtn) return;

            // Form validation is typically handled by the browser/Laravel, but we prevent double-submission
            if (!form.checkValidity()) {
                // If invalid, let browser show errors
                return;
            }

            // Shop role: require at least one part price to be filled
            const isShopRole = {{ $actsAsShop ? 'true' : 'false' }};
            if (isShopRole) {
                const priceInputs = form.querySelectorAll('.unit-price-input');
                const hasAtLeastOne = Array.from(priceInputs).some(function(inp) {
                    return inp.value.trim() !== '' && parseFloat(inp.value) > 0;
                });
                if (!hasAtLeastOne) {
                    event.preventDefault();
                    alert('Please enter a price for at least one part.\nLeave fields blank only for parts you do not carry.');
                    return;
                }
                // Check no filled price is less than 1
                for (let i = 0; i < priceInputs.length; i++) {
                    const val = priceInputs[i].value.trim();
                    if (val !== '' && parseFloat(val) < 1) {
                        event.preventDefault();
                        priceInputs[i].setCustomValidity('Price must be at least 1 ETB, or leave blank if unavailable');
                        priceInputs[i].reportValidity();
                        return;
                    }
                }
            }

            // Show loading state
            submitBtn.disabled = true;
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            
            if (btnText) btnText.style.display = 'none';
            if (btnLoading) btnLoading.style.display = 'inline-flex';
        }

        // --- Part Image Gallery using Bootstrap Carousel ---

        let currentPartImages = [];
        let partImageModalInstance = null;
        let partImageCarouselInstance = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize modal instance
            const modalEl = document.getElementById('partImageGalleryModal');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                partImageModalInstance = new bootstrap.Modal(modalEl);
                modalEl.addEventListener('hidden.bs.modal', cleanupPartImageModal);
            }

            // Setup calculation listeners based on role
            const isShopRole = {{ $actsAsShop ? 'true' : 'false' }};

            if (isShopRole) {
                // Shop role: Listen to all unit price inputs
                const unitPriceInputs = document.querySelectorAll('input.unit-price-input');
                unitPriceInputs.forEach(input => {
                    input.addEventListener('input', function() {
                        updatePartAmounts();
                    });
                });
            } else {
                // Garage role: Listen to the main total amount input
                const totalAmountInput = document.querySelector('#total-amount');
                if (totalAmountInput) {
                    totalAmountInput.addEventListener('input', function() {
                        calculateAmounts();
                    });
                }
            }

            // Initial calculation run if there are parts (only once on load)
            setTimeout(function() {
                if (document.querySelectorAll('tbody tr').length > 0) {
                    if (isShopRole) {
                        updatePartAmounts();
                    } else {
                        calculateAmounts();
                    }
                }
            }, 100);

            // Setup form submission listener (E2E encryption + loading state)
            const form = document.getElementById('proforma-quote-form');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const isShopRole = {{ $actsAsShop ? 'true' : 'false' }};
                    const isDualService = {{ $isDualServiceProforma ? 'true' : 'false' }};

                    // Check if PDF data was already populated by the capture-phase PDF handler
                    const hasPdfData = !!(document.getElementById('hiddenEncryptedPdf')?.value ||
                                         document.getElementById('hiddenPdfData')?.value);

                    // ── Frontend validation ──────────────────────────────────
                    if (isShopRole) {
                        if (!hasPdfData) {
                            const priceInputs = form.querySelectorAll('.unit-price-input');
                            const hasAtLeastOne = Array.from(priceInputs).some(
                                inp => !inp.disabled && inp.value.trim() !== '' && parseFloat(inp.value) > 0
                            );
                            if (!hasAtLeastOne) {
                                alert('Please enter a price for at least one part.\nLeave fields blank only for parts you do not carry.');
                                return;
                            }
                            for (const inp of priceInputs) {
                                if (inp.disabled) continue; // skip locked parts (already priced, stored as 0)
                                const val = inp.value.trim();
                                if (val !== '' && parseFloat(val) < 1) {
                                    inp.setCustomValidity('Price must be at least 1 ETB, or leave blank if unavailable');
                                    inp.reportValidity();
                                    return;
                                }
                                inp.setCustomValidity('');
                            }
                        }
                        if (isDualService) {
                            const garageInput = form.querySelector('input[name="garage_amount"]');
                            const garageAmount = parseFloat(garageInput?.value || 0);
                            if (garageAmount < 1) {
                                alert('Please enter a valid garage service estimate (minimum 1 ETB).');
                                if (garageInput) garageInput.focus();
                                return;
                            }
                        }
                    } else {
                        // Garage: validate the repair estimate amount
                        // shop_garage providers may substitute a PDF/image in place of a price
                        const isShopGarageProvider = {{ auth()->user()?->shop_garage == 1 ? 'true' : 'false' }};
                        if (!(hasPdfData && isShopGarageProvider)) {
                            const amtInput = form.querySelector('#total-amount');
                            const amt = amtInput ? parseFloat(amtInput.value) || 0 : 0;
                            if (amt < 1) {
                                alert('Please enter a valid repair estimate price (minimum 1 ETB).');
                                if (amtInput) { amtInput.focus(); amtInput.select(); }
                                return;
                            }
                        }
                    }

                    // ── Show loading state ───────────────────────────────────
                    const submitBtn = document.getElementById('submitBtn');
                    const btnText   = submitBtn ? submitBtn.querySelector('.btn-text')    : null;
                    const btnLoad   = submitBtn ? submitBtn.querySelector('.btn-loading') : null;
                    if (submitBtn) submitBtn.disabled = true;
                    if (btnText)   btnText.style.display = 'none';
                    if (btnLoad) { btnLoad.style.display = 'inline-flex'; btnLoad.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Submitting…'; }

                    // Prices are sent as NET — the backend adds 15% VAT to compute the stored amount.

                    // ── E2E Encryption (insurance proformas only) ────────────
                    const isInsuranceProforma = {{ in_array($proforma->poster?->role, ['insurance', 'insurance_agent']) ? 'true' : 'false' }};

                    // Check if any prices are actually entered
                    const hasActualPrices = isShopRole
                        ? Array.from(form.querySelectorAll('.unit-price-input')).some(inp => !inp.disabled && inp.value.trim() && parseFloat(inp.value) > 0)
                            || (isDualService && parseFloat(form.querySelector('input[name="garage_amount"]')?.value || 0) > 0)
                        : (parseFloat(form.querySelector('#total-amount')?.value || 0) > 0);

                    if (isInsuranceProforma && hasActualPrices) {
                        // Prices entered for insurance proforma: encrypt them
                        let keyData;
                        try {
                            if (btnLoad) btnLoad.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Checking encryption…';
                            const resp = await fetch('{{ route("insurance.public-key", $proforma->id) }}');
                            keyData = await resp.json();
                        } catch (err) {
                            if (submitBtn) submitBtn.disabled = false;
                            if (btnText)   btnText.style.display = '';
                            if (btnLoad)   btnLoad.style.display = 'none';
                            alert('Could not verify encryption status. Please check your connection and try again.');
                            return;
                        }

                        if (!keyData.has_encryption || !keyData.public_key) {
                            // Poster has not set up encryption — submit NET prices as plain text.
                            if (btnLoad) btnLoad.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Submitting…';
                            await new Promise(r => setTimeout(r, 0));
                            form.submit();
                            return;
                        }

                        if (btnLoad) btnLoad.innerHTML = '<i class="bx bx-lock-alt bx-spin"></i> Encrypting prices…';

                        let flagInput = form.querySelector('input[name="prices_encrypted"]');
                        if (!flagInput) {
                            flagInput = document.createElement('input');
                            flagInput.type = 'hidden';
                            flagInput.name = 'prices_encrypted';
                            form.appendChild(flagInput);
                        }
                        flagInput.value = '1';

                        if (isShopRole) {
                            const priceInputs = form.querySelectorAll('.unit-price-input');
                            for (let i = 0; i < priceInputs.length; i++) {
                                const raw = priceInputs[i].value.trim();
                                if (raw) {
                                    const net = parseFloat(raw) || 0;
                                    const cipher = await E2EEncryption.encryptValue(net.toFixed(2), keyData.public_key);
                                    const hidden = document.createElement('input');
                                    hidden.type  = 'hidden';
                                    hidden.name  = `encrypted_total[${i}]`;
                                    hidden.value = cipher;
                                    form.appendChild(hidden);
                                }
                                priceInputs[i].name = ''; // strip plaintext from POST
                            }
                            const amtInput = form.querySelector('#total-amount');
                            if (amtInput) amtInput.name = '';

                            if (isDualService) {
                                const garageInput = form.querySelector('input[name="garage_amount"]');
                                const garageRaw = garageInput ? garageInput.value.trim() : '';
                                if (garageRaw) {
                                    const net = parseFloat(garageRaw) || 0;
                                    const garageCipher = await E2EEncryption.encryptValue(net.toFixed(2), keyData.public_key);
                                    const garageHidden = document.createElement('input');
                                    garageHidden.type = 'hidden';
                                    garageHidden.name = 'encrypted_amount';
                                    garageHidden.value = garageCipher;
                                    form.appendChild(garageHidden);
                                }
                                if (garageInput) garageInput.name = '';
                            }
                        } else {
                            // Garage
                            const amtInput = document.getElementById('total-amount');
                            const raw = amtInput ? amtInput.value.trim() : '';
                            if (raw) {
                                const net = parseFloat(raw) || 0;
                                const cipher = await E2EEncryption.encryptValue(net.toFixed(2), keyData.public_key);
                                const hidden = document.createElement('input');
                                hidden.type  = 'hidden';
                                hidden.name = 'encrypted_amount';
                                hidden.value = cipher;
                                form.appendChild(hidden);
                            }
                            if (amtInput) amtInput.name = '';
                        }
                    }
                    // Non-insurance proformas (or PDF-only to insurance): submit NET prices as plain text.
                    // The backend adds 15% VAT to compute the stored amount.
                    if (!isInsuranceProforma || !hasActualPrices) {
                        // No conversion needed — NET prices are sent as-is.
                    }

                    if (btnLoad) btnLoad.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Submitting…';
                    await new Promise(r => setTimeout(r, 0));
                    form.submit();
                });
            }
        });

        // ── Submission Mode Toggle ──────────────────────────────────────────────
        function setSubmissionMode(mode) {
            const priceBtn    = document.getElementById('modePriceBtn');
            const pdfBtn      = document.getElementById('modePdfBtn');
            const pdfSection  = document.getElementById('pdfUploadSection');
            // Price table wrapper (the div containing the parts table + totals)
            const priceTable  = document.getElementById('price-table-section');
            const modeInput   = document.getElementById('hiddenSubmissionMode');

            const activeStyle   = { border:'2px solid rgba(13,148,136,0.5)', background:'rgba(13,148,136,0.18)', color:'var(--etera-teal-light,#4dd0c4)' };
            const inactiveStyle = { border:'2px solid rgba(255,255,255,0.12)', background:'transparent', color:'#aaa' };

            if (mode === 'pdf') {
                if (pdfBtn) Object.assign(pdfBtn.style,   activeStyle);
                Object.assign(priceBtn.style, inactiveStyle);
                if (pdfSection)  pdfSection.style.display  = 'block';
                if (priceTable)  priceTable.style.display  = 'none';
                if (modeInput)   modeInput.value = 'pdf';
                // Clear any entered prices so they don't interfere
                document.querySelectorAll('.unit-price-input').forEach(inp => { inp.value = ''; });
                const totalAmtEl = document.getElementById('total-amount');
                if (totalAmtEl) totalAmtEl.value = '';
                calculateAmounts();
            } else {
                Object.assign(priceBtn.style, activeStyle);
                if (pdfBtn) Object.assign(pdfBtn.style,   inactiveStyle);
                if (pdfSection) pdfSection.style.display  = 'none';
                if (priceTable) priceTable.style.display  = '';
                if (modeInput)  modeInput.value = 'price';
                // Clear any attached PDF
                clearPdfUpload();
            }
        }

        // ── PDF Upload Handling ─────────────────────────────────────────────────
        const pdfFileInput = document.getElementById('pdfFileInput');
        if (pdfFileInput) {
            pdfFileInput.addEventListener('change', function () {
                const file = this.files[0];
                const sizeErr = document.getElementById('pdfSizeError');
                const label   = document.getElementById('pdfFileLabel');
                const clearBtn = document.getElementById('pdfClearBtn');
                if (!file) return;
                if (file.size > 10 * 1024 * 1024) {
                    sizeErr.style.display = 'block';
                    this.value = '';
                    label.textContent = 'No file chosen';
                    clearBtn.style.display = 'none';
                    return;
                }
                sizeErr.style.display = 'none';
                label.textContent = file.name;
                clearBtn.style.display = 'inline';
            });
        }

        function clearPdfUpload() {
            const fi = document.getElementById('pdfFileInput');
            if (fi) fi.value = '';
            const label = document.getElementById('pdfFileLabel');
            if (label) label.textContent = 'No file chosen';
            const clearBtn = document.getElementById('pdfClearBtn');
            if (clearBtn) clearBtn.style.display = 'none';
            ['hiddenEncryptedPdf','hiddenEncryptedAesKey','hiddenAesIv','hiddenPdfData','hiddenPdfFilename'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
        }

        // Safe base64 encoder — processes in 8 KB chunks to avoid call-stack overflow on large ArrayBuffers
        function arrayBufferToBase64(buffer) {
            const bytes = new Uint8Array(buffer);
            let binary = '';
            const chunk = 8192;
            for (let i = 0; i < bytes.length; i += chunk) {
                binary += String.fromCharCode(...bytes.subarray(i, Math.min(i + chunk, bytes.length)));
            }
            return btoa(binary);
        }

        // Hybrid RSA/AES encryption for PDF bytes
        async function encryptPdfBytes(pdfBytes, publicKeyBase64) {
            const b64 = buf => arrayBufferToBase64(buf);
            const unb64 = str => Uint8Array.from(atob(str), c => c.charCodeAt(0));

            const rsaKey = await crypto.subtle.importKey(
                'spki', unb64(publicKeyBase64),
                { name: 'RSA-OAEP', hash: 'SHA-256' }, false, ['encrypt']
            );
            const aesKey = await crypto.subtle.generateKey({ name: 'AES-GCM', length: 256 }, true, ['encrypt']);
            const iv = crypto.getRandomValues(new Uint8Array(12));
            const encryptedPdf = await crypto.subtle.encrypt({ name: 'AES-GCM', iv }, aesKey, pdfBytes);
            const rawAesKey = await crypto.subtle.exportKey('raw', aesKey);
            const encryptedAesKey = await crypto.subtle.encrypt({ name: 'RSA-OAEP' }, rsaKey, rawAesKey);
            return {
                encrypted_pdf:     b64(encryptedPdf),
                encrypted_aes_key: b64(encryptedAesKey),
                aes_iv:            b64(iv),
            };
        }

        // Attach PDF processing to submit (runs before existing encryption logic)
        const proformaQuoteForm = document.getElementById('proforma-quote-form');
        if (proformaQuoteForm) {
            proformaQuoteForm.addEventListener('submit', async function pdfPreSubmit(e) {
                const fileInput = document.getElementById('pdfFileInput');
                if (!fileInput || !fileInput.files[0]) return; // no PDF, continue normally

                e.preventDefault();
                e.stopImmediatePropagation(); // prevent bubble handler from firing while PDF is being processed async
                proformaQuoteForm.removeEventListener('submit', pdfPreSubmit, true);

                const file = fileInput.files[0];
                const proc = document.getElementById('pdfProcessing');
                if (proc) proc.style.display = 'block';

                try {
                    const isInsurance = {{ in_array($proforma->poster?->role, ['insurance', 'insurance_agent']) ? 'true' : 'false' }};
                    const setProgress = p => { const pb = document.getElementById('pdfProgressBar'); if (pb) pb.style.width = p + '%'; };
                    const setMsg = m => { const el = document.getElementById('pdfProcessingMsg'); if (el) el.textContent = m; };

                    setProgress(10);

                    if (isInsurance) {
                        setMsg('Fetching encryption info…');
                        const resp = await fetch('{{ route("insurance.public-key", $proforma->id) }}');
                        const keyData = await resp.json();
                        setProgress(30);
                        if (!keyData.has_encryption || !keyData.public_key) {
                            setMsg('Reading PDF…');
                            const b64 = await new Promise((res, rej) => { const r = new FileReader(); r.onload = e => res(e.target.result.split(',')[1]); r.onerror = rej; r.readAsDataURL(file); });
                            setProgress(90);
                            document.getElementById('hiddenPdfData').value = b64;
                        } else {
                            setMsg('Reading PDF…');
                            const arrayBuffer = await file.arrayBuffer();
                            setProgress(50);
                            setMsg('Encrypting PDF…');
                            const result = await encryptPdfBytes(arrayBuffer, keyData.public_key);
                            setProgress(90);
                            document.getElementById('hiddenEncryptedPdf').value    = result.encrypted_pdf;
                            document.getElementById('hiddenEncryptedAesKey').value = result.encrypted_aes_key;
                            document.getElementById('hiddenAesIv').value           = result.aes_iv;
                            // Do NOT set prices_encrypted=1 here — the bubble handler manages price encryption separately
                        }
                    } else {
                        setMsg('Reading file…');
                        const b64 = await new Promise((res, rej) => { const r = new FileReader(); r.onload = e => res(e.target.result.split(',')[1]); r.onerror = rej; r.readAsDataURL(file); });
                        setProgress(90);
                        document.getElementById('hiddenPdfData').value = b64;
                    }

                    setProgress(100);
                    document.getElementById('hiddenPdfFilename').value = file.name;
                } catch (err) {
                    console.error('PDF processing failed:', err);
                    if (proc) {
                        proc.style.display = 'none';
                    }
                    const sizeErr = document.getElementById('pdfSizeError');
                    if (sizeErr) {
                        sizeErr.textContent = 'Failed to process PDF: ' + (err.message || 'Unknown error');
                        sizeErr.style.display = 'block';
                    }
                    // Re-attach handler so user can try again
                    proformaQuoteForm.addEventListener('submit', pdfPreSubmit, true);
                    return; // do not submit
                }

                // Keep proc visible — switch to animated "uploading" state for the HTTP round-trip
                if (proc) {
                    const el = document.getElementById('pdfProcessingMsg');
                    if (el) el.textContent = 'Uploading to server…';
                    const pb = document.getElementById('pdfProgressBar');
                    if (pb) {
                        pb.style.transition = 'none';
                        pb.style.width = '100%';
                        pb.style.backgroundImage = 'linear-gradient(90deg,var(--etera-teal,#0d9488),#4dd0c4,var(--etera-teal,#0d9488))';
                        pb.style.backgroundSize = '200% 100%';
                        pb.style.animation = 'pdfUploadPulse 1.4s ease-in-out infinite';
                    }
                }

                // Re-trigger submit (bubble-phase handler will now run for price encryption)
                // NOTE: prices_encrypted is NOT set here — PDF uses its own encryption path
                proformaQuoteForm.requestSubmit
                    ? proformaQuoteForm.requestSubmit()
                    : proformaQuoteForm.submit();
            }, true); // capture phase so this runs first
        }
        // ── End PDF Upload Handling ─────────────────────────────────────────────

        /**
         * Opens the part image gallery modal.
         */
        function openPartImageModal(imageUrls) {
            if (!partImageModalInstance || !imageUrls || imageUrls.length === 0) return;

            currentPartImages = imageUrls;

            const carouselEl = document.getElementById('partImageCarousel');
            const innerEl = document.getElementById('partImageCarouselInner');
            
            if (!carouselEl || !innerEl) return;
            
            innerEl.innerHTML = '';

            currentPartImages.forEach(function(url, idx) {
                const item = document.createElement('div');
                item.className = 'carousel-item' + (idx === 0 ? ' active' : '');
                const img = document.createElement('img');
                img.src = url;
                img.alt = 'Part Image ' + (idx + 1);
                img.className = 'd-block w-100';
                img.style.maxHeight = '80vh';
                img.style.objectFit = 'contain';
                img.style.background = '#343a40';
                item.appendChild(img);
                innerEl.appendChild(item);
            });

            if (typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
                if (partImageCarouselInstance) {
                    partImageCarouselInstance.dispose();
                }
                partImageCarouselInstance = new bootstrap.Carousel(carouselEl, { 
                    interval: false, 
                    wrap: true, 
                    keyboard: true 
                });
                
                carouselEl.addEventListener('slid.bs.carousel', updateCarouselCaption);
                updateCarouselCaption();
            }

            partImageModalInstance.show();

            // Tag this modal's backdrop so our z-index rules are scoped and don't affect other modals.
            setTimeout(function() {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                const lastBackdrop = backdrops.length ? backdrops[backdrops.length - 1] : null;
                if (lastBackdrop) {
                    lastBackdrop.classList.add('part-image-gallery-backdrop');
                }
            }, 0);
        }

        /**
         * Updates the carousel caption with current image info.
         */
        function updateCarouselCaption() {
            const total = currentPartImages.length;
            const carouselEl = document.getElementById('partImageCarousel');
            
            if (!carouselEl) return;
            
            const items = Array.from(carouselEl.querySelectorAll('.carousel-item'));
            const activeIndex = items.findIndex(function(el) { 
                return el.classList.contains('active'); 
            });
            
            const displayIndex = (activeIndex >= 0 ? activeIndex : 0) + 1;

            const title = document.getElementById('partImageGalleryModalLabel');
            const counterText = document.getElementById('partImageCountText');

            if (title && counterText) {
                if (total <= 1) {
                    title.textContent = 'Part Image';
                    counterText.textContent = 'Single Image';
                } else {
                    const indexText = `${displayIndex} of ${total}`;
                    title.innerHTML = `Part Images (<span id="partImageIndex">${indexText}</span>)`;
                    counterText.textContent = `Showing image ${indexText}`;
                }
            }
        }

        /**
         * Cleans up state when the modal is closed.
         */
        function cleanupPartImageModal() {
            currentPartImages = [];
            const backdrops = document.querySelectorAll('.modal-backdrop.part-image-gallery-backdrop');
            backdrops.forEach(function(el) {
                el.classList.remove('part-image-gallery-backdrop');
            });
            if (partImageCarouselInstance) {
                partImageCarouselInstance.dispose();
                partImageCarouselInstance = null;
            }
        }

        /**
         * Explicitly closes the modal using the bootstrap instance.
         */
        function closePartImageModal() {
            const modalEl = document.getElementById('partImageGalleryModal');
            if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;

            // Use the stored instance if available; otherwise safely get/create an instance.
            const instance = partImageModalInstance || bootstrap.Modal.getOrCreateInstance(modalEl);
            instance.hide();

            // Hard fallback: if something (CSS/z-index/backdrop) prevents bootstrap from closing,
            // force-remove modal/backdrop state for this specific modal only.
            setTimeout(function() {
                if (modalEl.classList.contains('show')) {
                    try {
                        modalEl.classList.remove('show');
                        modalEl.style.display = 'none';
                        modalEl.setAttribute('aria-hidden', 'true');
                        modalEl.removeAttribute('aria-modal');
                        modalEl.removeAttribute('role');
                    } catch (e) {}

                    document.querySelectorAll('.modal-backdrop.part-image-gallery-backdrop').forEach(function(bd) {
                        bd.parentNode && bd.parentNode.removeChild(bd);
                    });

                    // Restore scroll state
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('overflow');
                    document.body.style.removeProperty('padding-right');
                }
            }, 150);
        }

        // END: JAVASCRIPT LOGIC
    </script>
@endsection
