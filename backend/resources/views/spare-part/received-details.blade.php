@extends('layouts.sparepart')

@section('received', 'class="current"')

@section('content')

<div class="container-fluid margin-top-40 margin-bottom-45" style="max-width: 1100px;">
    <div class="row">
        <div class="col-12">
            <style type="text/css">
                /* ---- Stamp ---- */
                .card-stamp {
                    position: absolute;
                    bottom: 2rem;
                    right: 1rem;
                    width: 7rem;
                    height: 7rem;
                    opacity: .25;
                    overflow: hidden;
                    pointer-events: none;
                    z-index: 5;
                }

                .profile-pic.stamp-image {
                    width: 100%;
                    height: 100%;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 2px solid rgba(255, 255, 255, 0.15);
                }

                /* ---- Job listing card ---- */
                .job-listing {
                    position: relative;
                    overflow: hidden;
                }

                /* ---- Tables — dark theme ---- */
                table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0;
                    margin-bottom: 5px;
                }

                table th {
                    background: rgba(13, 148, 136, 0.12) !important;
                    text-align: left;
                    color: var(--etera-teal-light) !important;
                    font-weight: 500;
                    white-space: nowrap;
                }

                table th:first-child { border-radius: 4px 0 0 4px; }
                table th:last-child { border-radius: 0 4px 4px 0; }

                table th, table td {
                    padding: 8px 10px;
                    font-size: 0.9rem;
                }

                table tr:nth-child(odd) {
                    background: rgba(255, 255, 255, 0.02) !important;
                }

                tfoot tr:nth-child(odd) {
                    color: var(--etera-teal-light) !important;
                    font-weight: 700;
                    background: rgba(13, 148, 136, 0.06) !important;
                }

                /* ---- Invoice card styles ---- */
                .invoice-card {
                    border-radius: 12px;
                    background: rgba(255, 255, 255, 0.04);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                }
                .invoice-header {
                    background: var(--etera-gradient);
                    color: white;
                    padding: 24px;
                    border-top-left-radius: 12px;
                    border-top-right-radius: 12px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .invoice-title { font-size: 2.25rem; font-weight: 700; margin: 0; }
                .invoice-details { padding: 24px; color: #fff; }
                .invoice-details p { margin-bottom: 8px; font-size: 1rem; }
                .invoice-details strong { font-weight: 600; color: var(--etera-teal-light); }
                .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
                .invoice-table th, .invoice-table td { padding: 12px; text-align: left; border-bottom: 1px solid rgba(255, 255, 255, 0.06); }
                .invoice-table thead th { background: rgba(13, 148, 136, 0.12) !important; font-weight: 600; color: var(--etera-teal-light) !important; }
                .invoice-summary { padding: 24px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; background: rgba(255, 255, 255, 0.04); }
                .invoice-summary-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 1rem; }
                .invoice-summary-row strong { font-weight: 600; }
                .grand-total { font-size: 1.5rem; font-weight: 700; color: var(--etera-teal); border-top: 2px solid var(--etera-teal); padding-top: 16px; margin-top: 16px; }
                .download-button { background: var(--etera-gradient); color: white; border-radius: 20px; padding: 10px 24px; font-size: 1rem; transition: background-color 0.3s; }
                .download-button:hover { background-color: rgba(13, 148, 136, 0.3); }
                .center-content { display: flex; justify-content: center; align-items: center; }
                .company-stamp { position: absolute; bottom: 160px; left: 4px; width: 100px; height:100px; opacity: 0.7; transform: rotate(10deg); pointer-events: none; }

                /* ---- Responsive table scrolling inside cards ---- */
                .job-listing-footer {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                    padding: 10px 20px 20px;
                }

                .job-listing-footer table {
                    min-width: 650px;
                }

                /* ---- Mobile breakpoints ---- */
                @media (max-width: 768px) {
                    .card-stamp {
                        width: 5rem;
                        height: 5rem;
                        bottom: 1rem;
                        right: 0.5rem;
                    }

                    .job-listing-details {
                        flex-direction: column;
                        text-align: center;
                    }

                    .padding-left-20, .padding-right-20 {
                        padding-left: 12px !important;
                        padding-right: 12px !important;
                    }

                    table th, table td {
                        padding: 6px 8px;
                        font-size: 0.82rem;
                    }

                    .invoice-header {
                        flex-direction: column;
                        gap: 8px;
                        text-align: center;
                    }

                    .invoice-title { font-size: 1.5rem; }
                }

                @media print { .d-print-none { display: none !important; } }
            </style>

            {{-- Voice Note Section --}}
            @if($proforma->voice_note_path)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="icon-feather-volume-2 me-2"></i>Voice Note</h6>
                            </div>
                            <div class="card-body">
                                <audio controls style="width: 100%;">
                                    <source src="{{ asset('storage/' . $proforma->voice_note_path) }}" type="audio/webm">
                                    <source src="{{ asset('storage/' . $proforma->voice_note_path) }}" type="audio/mp3">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="section-headline margin-bottom-5">
                <h3>Spare Part Shops Proformas List</h3>
            </div>

            <div class="row">
                @foreach($applications as $index => $application)
                    @if($application->applicationBy->role === 'shop')
                        @php $appPdfOnly = $application->pdf !== null && $application->prices->isEmpty(); @endphp
                        @if($appPdfOnly)
                        {{-- PDF-only card --}}
                        <div class="col-12 mb-4 application-card" data-index="{{ $index }}" @if(($proforma->required_number_of_shops ?? 0) == 0 && ($proforma->required_number_of_garages ?? 0) == 0 && $index >= 5) style="display:none;" @endif>
                            <div class="card" style="position:relative;overflow:hidden;">
                                <div class="card-stamp">
                                    @if($application->applicationBy->stamp_image)
                                        <img class="profile-pic stamp-image" src="{{ asset('storage/' . $application->applicationBy->stamp_image) }}" alt="Stamp" />
                                    @else
                                        <img class="profile-pic stamp-image" src="{{ asset('assets/images/stamp.png') }}" alt="No Stamp Here" />
                                    @endif
                                </div>
                                <div class="card-body pb-2">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <img src="{{ asset('assets/images/avatars/avatar-9.jpg') }}" alt="Shop" style="width:50px;height:50px;border-radius:50%;border:2px solid rgba(13,148,136,0.3);">
                                        <div>
                                            <h5 class="mb-0">{{ $application->applicationBy->name }}</h5>
                                            <small class="text-muted">{{ ucfirst($application->applicationBy->role) }}</small>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-sm-6 col-12"><span><b>Store ID:</b> <span class="text-muted">{{ $application->applicationBy->store_id }}</span></span></div>
                                        <div class="col-sm-6 col-12"><span><b>Tin #:</b> <span class="text-muted">{{ $application->applicationBy->tin_number }}</span></span></div>
                                        <div class="col-sm-6 col-12"><span><b>Phone:</b> <span class="text-muted">{{ $application->applicationBy->phone_number }}</span></span></div>
                                        <div class="col-sm-6 col-12"><span><b>Location:</b> <span class="text-muted">{{ $application->applicationBy->location }}</span></span></div>
                                    </div>
                                    <div style="padding:12px 16px;background:rgba(13,148,136,0.06);border:1px solid rgba(13,148,136,0.18);border-radius:10px;display:flex;align-items:center;gap:12px;">
                                        <i class="bx bxs-file-pdf" style="font-size:1.8rem;color:#ef4444;flex-shrink:0;"></i>
                                        <div style="flex:1;min-width:0;">
                                            <div style="font-weight:600;font-size:0.88rem;">PDF Quotation</div>
                                            <div style="font-size:0.78rem;color:#aaa;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $application->pdf->original_filename }}</div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="openPdfViewer(this)"
                                                data-encrypted="{{ $application->pdf->isEncrypted() ? '1' : '0' }}"
                                                data-stamp="{{ $application->applicationBy->stamp_image ? asset('storage/' . $application->applicationBy->stamp_image) : asset('assets/images/stamp.png') }}"
                                                data-serve-url="{{ route('application.pdf.serve', $application->id) }}"
                                                data-encrypted-url="{{ route('application.pdf.encrypted', $application->id) }}">
                                            <i class="bx bx-show"></i> View PDF
                                        </button>
                                    </div>
                                    @if($application->notes)
                                    <div style="margin-top:10px;background:#f0fdf9;border-left:3px solid #14b8a6;border-radius:0 6px 6px 0;padding:9px 14px;">
                                        <p style="font-size:0.78rem;font-weight:700;color:#0f766e;margin-bottom:4px;"><i class="bx bx-message-detail me-1"></i>Applicant Notes</p>
                                        <p style="font-size:0.9rem;margin:0;white-space:pre-wrap;color:#1f2937;">{{ $application->notes }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="col-12 mb-4 application-card" data-index="{{ $index }}" @if(($proforma->required_number_of_shops ?? 0) == 0 && ($proforma->required_number_of_garages ?? 0) == 0 && $index >= 5) style="display:none;" @endif>
                            <div class="card"
                                 style="position: relative; overflow: hidden;"
                                 data-shop-name="{{ $application->applicationBy->name }}"
                                 data-store-id="{{ $application->applicationBy->store_id }}"
                                 data-tin-number="{{ $application->applicationBy->tin_number }}"
                                 data-phone-number="{{ $application->applicationBy->phone_number }}"
                                 data-location="{{ $application->applicationBy->location }}"
                                 data-vat-rate="15"
                                 data-proforma-parts='@json($proforma->parts)'
                                 data-application-prices='@json($application->prices)'
                                 data-notes="{{ $application->notes ?? '' }}"
                                 data-expiry-date="{{ $application->expiry_date ? \Carbon\Carbon::parse($application->expiry_date)->format('F j, Y') : '' }}"
                                 data-stamp-image-url="{{ $application->applicationBy->stamp_image ? asset('storage/' . ($application->applicationBy->stamp_image)) : asset('assets/images/stamp.png') }}"
                            >
                                {{-- Stamp overlay --}}
                                <div class="card-stamp">
                                    @if($application->applicationBy->stamp_image)
                                        <img class="profile-pic stamp-image"
                                             src="{{ asset('storage/' . ($application->applicationBy->stamp_image)) }}"
                                             alt="Stamp" />
                                    @else
                                        <img class="profile-pic stamp-image"
                                             src="{{ asset('assets/images/stamp.png') }}"
                                             alt="No Stamp Here" />
                                    @endif
                                </div>

                                {{-- Shop info at the TOP --}}
                                <div class="card-body pb-2">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <img src="{{ asset('assets/images/avatars/avatar-9.jpg') }}"
                                             alt="Shop" style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid rgba(13,148,136,0.3);">
                                        <div>
                                            <h5 class="mb-0">{{ $application->applicationBy->name }}</h5>
                                            <small class="text-muted">{{ ucfirst($application->applicationBy->role) }}</small>
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-sm-6 col-12">
                                            <span><b>Store ID:</b> <span class="text-muted">{{ $application->applicationBy->store_id }}</span></span>
                                        </div>
                                        <div class="col-sm-6 col-12">
                                            <span><b>Tin #:</b> <span class="text-muted">{{ $application->applicationBy->tin_number }}</span></span>
                                        </div>
                                        <div class="col-sm-6 col-12">
                                            <span><b>Phone:</b> <span class="text-muted">{{ $application->applicationBy->phone_number }}</span></span>
                                        </div>
                                        <div class="col-sm-6 col-12">
                                            <span><b>Location:</b> <span class="text-muted">{{ $application->applicationBy->location }}</span></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Parts table at the BOTTOM --}}
                                <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; padding: 0 20px 20px;">
                                    <table style="min-width: 650px;">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Part Name & Number</th>
                                                <th>Condition</th>
                                                <th>Grade</th>
                                                <th>Country</th>
                                                <th>Qty</th>
                                                <th>Unit Price</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($proforma->parts as $index => $part)
                                                @php
                                                    $partPrice = $application->prices->values()->get($loop->index);
                                                @endphp
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $part->number }}</td>
                                                    <td>{{ $part->condition }}</td>
                                                    <td>{{ $part->grade }}</td>
                                                    <td>{{ $part->country }}</td>
                                                    <td>{{ $part->quantity }}</td>
                                                    @if($partPrice && $partPrice->unit_price > 0)
                                                        <td>{{ number_format($partPrice->unit_price, 2) }} ETB</td>
                                                        <td>{{ number_format($partPrice->unit_price * $part->quantity, 2) }} ETB</td>
                                                    @else
                                                        <td class="text-muted fst-italic">— Not available</td>
                                                        <td class="text-muted">—</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            @php
                                                $subtotalParts = 0;
                                                foreach ($proforma->parts as $index => $part) {
                                                    $price = $application->prices->values()->get($index);
                                                    if ($price) {
                                                        $subtotalParts += $price->unit_price * $part->quantity;
                                                    }
                                                }
                                                $usingParts = $subtotalParts > 0;
                                                $netTotal = $usingParts ? $subtotalParts : ((float) $application->amount / 1.15);
                                                $vatRate = 15;
                                                $vatAmount = $netTotal * ($vatRate / 100);
                                                $grandTotal = $netTotal + $vatAmount;
                                            @endphp
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="text-end"><strong>SUBTOTAL (Net)</strong></td>
                                                <td class="text-end"><strong>{{ number_format($netTotal, 2) }} ETB</strong></td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="text-end"><strong>VAT (15%)</strong></td>
                                                <td class="text-end"><strong>{{ number_format($vatAmount, 2) }} ETB</strong></td>
                                            </tr>
                                            <tr style="background-color: rgba(13,148,136,0.12); font-weight: bold; border-top: 2px solid var(--etera-teal);">
                                                <td colspan="6"></td>
                                                <td class="text-end"><strong>GRAND TOTAL (VAT Included)</strong></td>
                                                <td class="text-end" style="color: var(--etera-teal); font-size: 1.1em;"><strong>{{ number_format($grandTotal, 2) }} ETB</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    @if($application->notes)
                                    <div style="margin-top:12px; background:#f0fdf9; border-left:3px solid #14b8a6; border-radius:0 6px 6px 0; padding:9px 14px;">
                                        <p style="font-size:0.78rem; font-weight:700; color:#0f766e; margin-bottom:4px;">
                                            <i class="bx bx-message-detail me-1"></i>Applicant Notes
                                        </p>
                                        <p style="font-size:0.9rem; margin:0; white-space:pre-wrap; color:#1f2937;">{{ $application->notes }}</p>
                                    </div>
                                    @endif
                                    @if($application->expiry_date)
                                    <div style="margin-top:12px; background:#fef3c7; border-left:3px solid #f59e0b; border-radius:0 6px 6px 0; padding:9px 14px;">
                                        <p style="font-size:0.78rem; font-weight:700; color:#b45309; margin-bottom:4px;">
                                            <i class="bx bx-calendar me-1"></i>Quote Expiry Date
                                        </p>
                                        <p style="font-size:0.9rem; margin:0; color:#1f2937;">{{ \Carbon\Carbon::parse($application->expiry_date)->format('F j, Y') }}</p>
                                    </div>
                                    @endif
                                    <div class="text-end mt-3">
                                        <button class="button radius-30 rounded-pill px-4" onclick="openPrintPage(this)">Print</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif
                @endforeach
            </div>

            @if(($proforma->required_number_of_shops ?? 0) == 0 && ($proforma->required_number_of_garages ?? 0) == 0 && $applications->count() > 5)
            <div class="text-center mt-3 mb-4">
                <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="viewMoreBtn" onclick="showMoreApplications()">
                    <i class="bx bx-chevron-down me-1"></i>View More
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- Invoice Link --}}
    @if($proforma->proformaInvoice && $proforma->proformaInvoice->sku)
        <div class="text-center mt-4 mb-3">
            <a href="{{ url('/transaction/' . $proforma->proformaInvoice->sku) }}" class="btn btn-primary rounded-pill px-5 py-2" target="_blank">
                <i class="fas fa-file-invoice me-2"></i> View Invoice
            </a>
        </div>
    @endif
</div>

{{-- PDF Viewer Modal --}}
<div class="modal fade" id="pdfViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="height:90vh;">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bxs-file-pdf fs-5" style="color:#ef4444;"></i>
                    <h5 class="modal-title mb-0">PDF Quotation</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closePdfViewer()"></button>
            </div>
            <div class="modal-body p-0" style="position:relative;flex:1;overflow:hidden;">
                <div id="pdfViewerLoading" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;z-index:10;background:rgba(0,0,0,0.15);">
                    <div class="spinner-border" style="color:var(--etera-teal,#0d9488);" role="status"></div>
                </div>
                <div id="pdfIframeContainer" style="position:relative;width:100%;height:100%;">
                    <iframe id="pdfViewerIframe" src="" style="width:100%;height:100%;border:none;" title="PDF Quotation"></iframe>
                    <div id="pdfStampOverlay" style="position:absolute;bottom:40px;right:30px;width:150px;height:150px;opacity:0.75;pointer-events:none;z-index:5;transform:rotate(10deg);mix-blend-mode:multiply;">
                        <img id="pdfStampImg" src="" alt="Stamp" style="width:100%;height:100%;object-fit:contain;filter:contrast(1.6) brightness(1.2) saturate(1.3);">
                    </div>
                </div>
            </div>
            <div class="modal-footer d-print-none">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" onclick="closePdfViewer()">Close</button>
                <button type="button" class="btn btn-outline-primary" onclick="printPdfViewer()"><i class="bx bx-printer"></i> Print</button>
            </div>
        </div>
    </div>
</div>

<script>
// View More functionality for Etera Chereta applications
let visibleApplications = 5;
function showMoreApplications() {
    const cards = document.querySelectorAll('.application-card');
    const nextLimit = visibleApplications + 5;
    cards.forEach((card, i) => {
        if (i < nextLimit) card.style.display = '';
    });
    visibleApplications = nextLimit;
    if (visibleApplications >= cards.length) {
        const btn = document.getElementById('viewMoreBtn');
        if (btn) btn.style.display = 'none';
    }
}
</script>

<script>
	function openPrintPage(button) {
		let card = button.closest('.card, .invoice-card'); // Support both structures

		let isSparePartInvoice = card && !card.classList.contains('invoice-card');

		if (isSparePartInvoice) {
			// This part is for the Spare Part Shop Invoice
			let storeId = card.dataset.storeId || "N/A";
			let tinNumber = card.dataset.tinNumber || "N/A";
			let location = card.dataset.location || "N/A";
			let shopName = card.dataset.shopName || "N/A";
			let phoneNumber = card.dataset.phoneNumber || "N/A";
			let stampImage = card.dataset.stampImageUrl || "{{ asset('assets/images/stamp.png') }}";
            let vatRate = parseFloat(card.dataset.vatRate) || 15;
            let applicantNotes = card.dataset.notes || "";
            let expiryDate = card.dataset.expiryDate || "";


			let table = card.querySelector("table");
			let rows = table?.querySelectorAll("tbody tr") || [];
			let partsData = [];

			rows.forEach((row, index) => {
				let cells = row.querySelectorAll("td");
				if (cells.length >= 8) {
					partsData.push({
						no: index + 1,
						partNumber: cells[1].textContent.trim(),
						condition: cells[2].textContent.trim(),
						grade: cells[3].textContent.trim(),
						country: cells[4].textContent.trim(),
						quantity: cells[5].textContent.trim(),
						unitPrice: cells[6].textContent.trim(),
						total: cells[7].textContent.trim()
					});
				}
			});

			function parseETB(value) {
				return parseFloat(value.replace(/[^0-9.-]+/g, "")) || 0;
			}
            function formatETB(num) {
                return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " ETB";
            }

			let netTotal = partsData.reduce((sum, p) => sum + parseETB(p.total), 0);
			let vatAmount = netTotal * (vatRate / 100);
			let grandTotal = netTotal + vatAmount;

			let printWindow = window.open('', '_blank');
			printWindow.document.write(`
				<!DOCTYPE html>
				<html lang="en">
				<head>
					<meta charset="utf-8" />
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<title>etera - Invoice</title>
					<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700' type='text/css'>
					<link rel="stylesheet" href="{{ asset('assets/invoice/vendor/bootstrap/css/bootstrap.min.css') }}"/>
					<link rel="stylesheet" href="{{ asset('assets/invoice/vendor/font-awesome/css/all.min.css') }}"/>
					<link rel="stylesheet" href="{{ asset('assets/invoice/css/stylesheet.css') }}"/>
					<style>
						.table th, .table td { padding: 8px; }
						.text-end { text-align: right; }
						.stamp-image { width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 2px solid #ccc; }
						.invoice-container { position: relative; }
						.info-row-wrapper { position: relative; }
						.print-stamp-col { display: flex; align-items: center; justify-content: center; }
						.print-stamp-col .stamp-image { width: 110px; height: 110px; border-radius: 50%; object-fit: cover; border: 2px solid #ccc; opacity: 0.85; }
					</style>
				</head>
				<body>
					<div class="container-fluid invoice-container">
						<header>
							<div class="row align-items-center gy-3">
								<div class="col-sm-7 text-center text-sm-start">
								<h3 class="text-7 mb-0">Online Proforma</h3>
			
								<div class="col-sm-5 text-center text-sm-end">
									<h4 class="text-7 mb-0">Invoice</h4>
								</div>
							</div>
							<hr>
						</header>

						<main>
							<div class="row">
								<div class="col-sm-6"><strong>Date:</strong> ${new Date().toLocaleDateString()}</div>
								<div class="col-sm-6 text-sm-end"><strong>Invoice No:</strong> ${Math.floor(Math.random() * 100000)}</div>
							</div>
							<hr>

							<div class="info-row-wrapper">
								<div class="row gy-3 align-items-center">
									<div class="col-sm-5">
										<p class="mb-1"><strong>Store ID:</strong> ${storeId}</p>
										<p class="mb-1"><strong>Shop Name:</strong> ${shopName}</p>
										<p class="mb-1"><strong>Tin #:</strong> ${tinNumber}</p>
										<p class="mb-1"><strong>Location:</strong> ${location}</p>
										<p class="mb-1"><strong>Phone:</strong> ${phoneNumber}</p>
									</div>
									<div class="col-sm-2 print-stamp-col">
										<img class="stamp-image" src="${stampImage}" alt="Stamp" />
									</div>
									<div class="col-sm-5 text-sm-end">
										<strong>Author:</strong>
										<address>
											etera<br />
											portal.eteraet.com<br />
											Addis Ababa, Ethiopia
										</address>
									</div>
								</div>
							</div>

							<div class="table-responsive mt-4">
								<table class="table border">
									<thead>
										<tr>
											<th>No</th>
											<th>Part Name & Number</th>
											<th>Condition</th>
											<th>Grade</th>
											<th>Country</th>
											<th>Qty</th>
											<th>Unit Price</th>
											<th>Total</th>
										</tr>
									</thead>
									<tbody>
										${partsData.map(part => `
											<tr>
												<td>${part.no}</td>
												<td>${part.partNumber}</td>
												<td>${part.condition}</td>
												<td>${part.grade}</td>
												<td>${part.country}</td>
												<td>${part.quantity}</td>
												<td>${part.unitPrice}</td>
												<td>${part.total}</td>
											</tr>
										`).join('')}
									</tbody>
									<tfoot>
										<tr>
											<td colspan="7" class="text-end"><strong>SUBTOTAL (Net):</strong></td>
											<td class="text-end">${formatETB(netTotal)}</td>
										</tr>
										<tr>
											<td colspan="7" class="text-end"><strong>VAT (15%):</strong></td>
											<td class="text-end">${formatETB(vatAmount)}</td>
										</tr>
										<tr style="background-color: rgba(13,148,136,0.12); font-weight: bold; border-top: 2px solid var(--etera-teal);">
											<td colspan="7" class="text-end"><strong>GRAND TOTAL (VAT Included):</strong></td>
											<td class="text-end" style="color: var(--etera-teal); font-size: 1.1em;">${formatETB(grandTotal)}</td>
										</tr>
									</tfoot>
								</table>
							</div>

							<p class="text-danger mt-4"><strong>NOTE:</strong> All prices including VAT</p>
						${applicantNotes ? `<div style="margin-top:16px;background:#f0fdf9;border-left:4px solid #14b8a6;border-radius:0 6px 6px 0;padding:10px 16px;"><p style="font-size:0.8rem;font-weight:700;color:#0f766e;margin-bottom:4px;">&#128172; Applicant Notes</p><p style="font-size:0.9rem;margin:0;white-space:pre-wrap;color:#1f2937;">${applicantNotes}</p></div>` : ''}
						${expiryDate ? `<div style="margin-top:16px;background:#fef3c7;border-left:4px solid #f59e0b;border-radius:0 6px 6px 0;padding:10px 16px;"><p style="font-size:0.8rem;font-weight:700;color:#b45309;margin-bottom:4px;">&#128197; Quote Expiry Date</p><p style="font-size:0.9rem;margin:0;color:#1f2937;">${expiryDate}</p></div>` : ''}
						</main>

						<footer class="text-center mt-4">
							<p><strong>NOTE:</strong> Price is including 15% VAT.</p>
							<div class="btn-group btn-group-sm d-print-none">
								<a href="javascript:window.print()" class="btn btn-light border text-black-50 shadow-none">
									<i class="fa fa-print"></i> Print & Download
								</a>
							</div>
						</footer>
					</div>
				</body>
				</html>
			`);
			printWindow.document.close();
		} else {
			// This part is for the etera Receipt
			let customerName = "{{ $proforma->customer_name ?? 'N/A' }}";
			let customerPhone = "{{ $proforma->customer_phone_number ?? 'N/A' }}";
			let createdAt = "{{ $proforma->proformaInvoice?->created_at->format('M d, Y') ?? 'N/A' }}";
			
			let printWindow = window.open('', '_blank');
			printWindow.document.write(`
				<!DOCTYPE html>
				<html lang="en">
				<head>
					<meta charset="utf-8" />
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<title>etera - Receipt</title>
					<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700' type='text/css'>
					<link rel="stylesheet" href="{{ asset('assets/invoice/vendor/bootstrap/css/bootstrap.min.css') }}"/>
					<link rel="stylesheet" href="{{ asset('assets/invoice/vendor/font-awesome/css/all.min.css') }}"/>
					<link rel="stylesheet" href="{{ asset('assets/invoice/css/stylesheet.css') }}"/>
                    <style>
                        .company-stamp { position: relative; bottom: 0px; left: 4px; width: 100px; height:100px; opacity: 0.7; transform: rotate(10deg); pointer-events: none; }
                    </style>
				</head>
				<body>
					<div class="container-fluid invoice-container">
						<header>
							<div class="row align-items-center gy-3">
								<div class="col-sm-7 text-center text-sm-start">
									<img id="logo" src="{{ asset('assets/invoice/images/transparent.png') }}" height="70" width="200" alt="etera" />
								</div>
								<div class="col-sm-5 text-center text-sm-end">
									<h4 class="text-7 mb-0">etera - Receipt<br>(Official Hard copy is availabe at the office)</h4>
								</div>
							</div>
							<hr>
						</header>

						<main>
							<div class="row gy-3 align-items-start">
								<div class="col-sm-6">
									<p class="mb-1"><strong>etera:</strong></p>
									<p class="mb-1"><strong>Phone:</strong> 011-470-7566</p>
									<p class="mb-1"><strong>TIN:</strong> 0094205503</p>
									<p class="mb-1"><strong>Date:</strong> ${createdAt}</p>
								</div>
								<div class="col-sm-6 text-sm-end">
									<strong>Customer:</strong> ${customerName}<br>
									<strong>Phone:</strong> ${customerPhone}
								</div>
							</div>

							<div class="table-responsive mt-4">
								<table class="table border">
									<thead>
										<tr>
											<th>Item</th>
											<th class="text-end">Quantity</th>
											<th class="text-end">Price</th>
										</tr>
									</thead>
									<tbody>
										@if($proforma->proformaInvoice?->type === 'regular')
											<tr>
												<td>Proforma Service</td>
												<td class="text-end">{{ $proforma->proformaInvoice?->requested_count }}</td>
												<td class="text-end">{{ number_format($proforma->proformaInvoice?->unit_price, 2) }} ETB</td>
												
											</tr>
										@else
											<tr>
												<td>Et-era Chereta Service (Hourly)</td>
												<td class="text-end">{{ $proforma->proformaInvoice?->hours }} Hours</td>
												<td class="text-end">{{ number_format($proforma->proformaInvoice?->hourly_price, 2) }} ETB</td>
												<td class="text-end">{{ number_format($proforma->proformaInvoice?->hours * $proforma->proformaInvoice?->hourly_price, 2) }} ETB</td>
											</tr>
										@endif
									</tbody>
									<tfoot>
                                        @php
                                            $receiptSubtotal = $proforma->proformaInvoice?->type === 'regular' 
                                                ? ($proforma->proformaInvoice?->unit_price) 
                                                : ($proforma->proformaInvoice?->hours * $proforma->proformaInvoice?->hourly_price);
                                        @endphp
                                        <tr>
                                            <td colspan="2" class="text-end"><strong>Subtotal:</strong></td>
                                            <td class="text-end">{{ number_format($receiptSubtotal, 2) }} ETB</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-end"><strong>VAT ({{ number_format($proforma->proformaInvoice?->vat_rate, 2) }}%):</strong></td>
                                            <td class="text-end">{{ number_format($proforma->proformaInvoice?->vat_amount, 2) }} ETB</td>
                                        </tr>
										<tr style="background-color: rgba(13,148,136,0.12); font-weight: bold; border-top: 2px solid var(--etera-teal);">
											<td colspan="2" class="text-end"><strong>GRAND TOTAL:</strong></td>
											<td class="text-end" style="color: var(--etera-teal); font-size: 1.1em;">{{ number_format($proforma->proformaInvoice?->total_amount, 2) }} ETB</td>
										</tr>
									</tfoot>
								</table>
								 <img src="{{ asset('assets/invoice/images/stamp.png') }}" class="company-stamp" alt="Stamp">
							</div>
						</main>

						<footer class="text-center mt-4">
							<div class="btn-group btn-group-sm d-print-none">
								<a href="javascript:window.print()" class="btn btn-light border text-black-50 shadow-none">
									<i class="fa fa-print"></i> Print & Download
								</a>
							</div>
						</footer>
					</div>
				</body>
				</html>
			`);
			printWindow.document.close();
		}
	}

    // ── PDF Viewer ─────────────────────────────────────────────────────────────
    function closePdfViewer() {
        const iframe = document.getElementById('pdfViewerIframe');
        if (iframe) iframe.src = '';
    }

    function printPdfViewer() {
        const iframe = document.getElementById('pdfViewerIframe');
        if (!iframe || !iframe.src) return;
        try { iframe.contentWindow.focus(); iframe.contentWindow.print(); }
        catch(e) { window.open(iframe.src, '_blank'); }
    }

    function openPdfViewer(btn) {
        const stampSrc = btn.dataset.stamp || '';
        const serveUrl = btn.dataset.serveUrl || '';

        const modal    = new bootstrap.Modal(document.getElementById('pdfViewerModal'));
        const loading  = document.getElementById('pdfViewerLoading');
        const iframe   = document.getElementById('pdfViewerIframe');
        const stampImg = document.getElementById('pdfStampImg');

        if (stampImg) stampImg.src = stampSrc;
        iframe.src = '';
        loading.style.display = 'flex';
        modal.show();

        iframe.onload = function() { loading.style.display = 'none'; };
        iframe.src = serveUrl;
    }
</script>

@endsection
