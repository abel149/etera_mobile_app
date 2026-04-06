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
                                        <span>{{ $proforma->brand->name ?? 'N/A' }}</span>
                                        <h5>{{ $proforma->model ?? 'N/A' }}</h5>
                                    </li>
                                    @if (auth()->check() && auth()->user()->role == 'shop')
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
                <form
                    action="{{ auth()->check() && auth()->user()->role === 'garage' ? route('garage.proforma.apply', $proforma->id) : route('proforma.apply', $proforma->id) }}"
                    method="POST" id="proforma-quote-form">
                    @csrf
                    <div class="table-container">
                        <table class="basic-table">
                            <thead>
    <tr>
        <th colspan="{{ auth()->check() && auth()->user()->role == 'shop' ? 10 : 8 }}"
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
                                    @if (auth()->check() && auth()->user()->role == 'shop')
                                        <th style="min-width: 100px;">Unit Price (ETB)</th>
                                        <th style="min-width: 100px;">Total (ETB)</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($proforma->parts) && count($proforma->parts) > 0)
                                    @foreach ($proforma->parts as $part)
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

                                            @if (auth()->check() && auth()->user()->role == 'shop')
                                                <td>
                                                    <input type="number" required
                                                        name="total[{{ $loop->index }}]"
                                                        class="with-border unit-price-input" placeholder="Unit Price" value=""
                                                        step="any" min="0">
                                                </td>
                                                <td>
                                                    <input type="number" class="with-border part-total" placeholder="Total"
                                                        value="0" readonly disabled>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="{{ auth()->check() && auth()->user()->role == 'shop' ? 10 : 8 }}" class="text-center">
                                            No parts found for this proforma.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>

                            <tfoot>
                                <tr>
                                    @php
                                        // Calculate colspan based on user role
                                        $colspan = auth()->check() && auth()->user()->role == 'shop' ? 8 : 6;
                                    @endphp
                                    <td colspan="{{ $colspan }}"></td>
                                    <td class="text-align-right" colspan="1">
                                        @if (auth()->check() && auth()->user()->role == 'shop')
                                            <p style="margin: 0; padding: 0;">TOTAL PARTS PRICE</p>
                                        @else
                                            <p style="margin: 0; padding: 0;">GARAGE REPAIR SERVICE ESTIMATE PRICE</p>
                                        @endif
                                        <small style="color: #f5365c;">(Price not including VAT)</small>
                                    </td>
                                    <td colspan="2">
                                        <input type="number" name="amount" class="with-border" required
                                            id="total-amount" value="0"
                                            {{ auth()->check() && auth()->user()->role == 'shop' ? 'disabled' : '' }} min="0"
                                            step="any">
                                        @error('amount')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </td>
                                </tr>
                                <tr>
                                    @php
                                        $discountColspan = auth()->check() && auth()->user()->role == 'shop' ? 8 : 6;
                                    @endphp
                                    <td colspan="{{ $discountColspan }}"></td>
                                    <td class="text-align-right" colspan="1">
                                        Discount (%)
                                    </td>
                                    <td colspan="2">
                                        <input type="number" id="discount" name="discount" class="with-border"
                                            placeholder="Enter discount" value="0" min="0" max="100">
                                    </td>
                                    <input type="hidden" name="final-amount" id="final-amount-hidden" value="0"
                                        class="with-border">
                                </tr>
                                <tr>
                                    @php
                                        $grandTotalColspan = auth()->check() && auth()->user()->role == 'shop' ? 8 : 6;
                                    @endphp
                                    <td colspan="{{ $grandTotalColspan }}"></td>
                                    <td class="text-align-right" colspan="1">
                                        Grand Total (Discounted)
                                    </td>
                                    <td colspan="2">
                                        <input type="number" id="grand-total" class="with-border" value="0"
                                            disabled>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @if (auth()->check() && !$proforma->userAlreadyApplied(auth()->user()->id))
                        <button type="submit" class="apply-now-button radius-30 margin-top-15" id="submitBtn">
                            <span class="btn-text">Apply Now <i class="icon-material-outline-arrow-right-alt"></i></span>
                            <span class="btn-loading" style="display: none;">
                                <i class="bx bx-loader-alt bx-spin"></i> Submitting...
                            </span>
                        </button>
                    @endif
                </form>
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

    <script>
        // START: JAVASCRIPT LOGIC

        // --- Calculation Logic ---
        /**
         * Calculates the total amount, applies discount, and updates the final fields.
         * Used for both 'shop' (multi-line calculation) and 'garage' (single field calculation).
         */
        let isCalculating = false; // Prevent infinite loops
        
        function calculateAmounts() {
            if (isCalculating) return; // Prevent recursive calls
            isCalculating = true;
            
            try {
                const isShopRole = {{ (auth()->check() && auth()->user()->role === 'shop') ? 'true' : 'false' }};
                let totalAmount = 0;

                if (isShopRole) {
                    // SHOP role: Calculate sum from all part total inputs
                    const totalInputs = document.querySelectorAll('.part-total');
                    totalInputs.forEach(input => {
                        const value = parseFloat(input.value) || 0;
                        totalAmount += value;
                    });
                } else {
                    // GARAGE role: Use the value from the manually entered total-amount input
                    const totalAmountInput = document.getElementById('total-amount');
                    totalAmount = totalAmountInput ? parseFloat(totalAmountInput.value) || 0 : 0;
                }

                // Update the main total amount field for consistency (only active for shop role)
                const totalAmountInput = document.getElementById('total-amount');
                if (isShopRole && totalAmountInput) {
                    // Format to 2 decimal places if it's a shop, but keep as number type
                    totalAmountInput.value = totalAmount.toFixed(2);
                }

                // Get discount value
                const discountInput = document.getElementById('discount');
                if (!discountInput) {
                    isCalculating = false;
                    return;
                }
                
                let discountPercentage = parseFloat(discountInput.value) || 0;

                // Enforce discount constraints (0-100)
                if (discountPercentage < 0) discountPercentage = 0;
                if (discountPercentage > 100) discountPercentage = 100;
                discountInput.value = discountPercentage;

                // Calculate discounted amount
                const discountFactor = 1 - (discountPercentage / 100);
                const grandTotal = totalAmount * discountFactor;

                // Update final fields
                const grandTotalInput = document.getElementById('grand-total');
                const finalAmountHidden = document.getElementById('final-amount-hidden');
                
                if (grandTotalInput) {
                    grandTotalInput.value = Math.max(0, grandTotal).toFixed(2);
                }
                if (finalAmountHidden) {
                    finalAmountHidden.value = Math.max(0, grandTotal).toFixed(2);
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
                const isShopRole = {{ (auth()->check() && auth()->user()->role === 'shop') ? 'true' : 'false' }};

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
            const isShopRole = {{ (auth()->check() && auth()->user()->role === 'shop') ? 'true' : 'false' }};

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

            // Common listener for discount input (use debounce to prevent excessive calls)
            const discountInput = document.querySelector('#discount');
            if (discountInput) {
                let discountTimeout;
                discountInput.addEventListener('input', function() {
                    clearTimeout(discountTimeout);
                    discountTimeout = setTimeout(function() {
                        calculateAmounts();
                    }, 300);
                });
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

            // Setup form submission listener for loading state
            const form = document.getElementById('proforma-quote-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    submitQuote();
                });
            }
        });

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
