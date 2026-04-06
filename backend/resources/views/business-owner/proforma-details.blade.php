@extends('layouts.business-owner')
@section('content')
<style>
/* ─── Graceful Proforma Details ─── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

.proforma-details-page {
    font-family: 'Inter', sans-serif;
    padding: 1.5rem 0;
}

/* Section Title */
.section-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: -0.01em;
    position: relative;
    padding-bottom: 0.75rem;
    margin-bottom: 1.5rem;
}
.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #10b981, #059669);
    border-radius: 99px;
}

/* Voice Note Card */
.voice-note-card {
    background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
    border: 1px solid #a7f3d0;
    border-radius: 16px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    animation: fadeSlideUp 0.5s ease-out;
}
.voice-note-card .voice-label {
    font-weight: 600;
    color: #10b981;
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.voice-note-card audio {
    width: 100%;
    border-radius: 12px;
    outline: none;
}

/* Application Card */
.application-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.03);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    animation: fadeSlideUp 0.6s ease-out both;
    position: relative;
}
.application-card:nth-child(2) { animation-delay: 0.1s; }
.application-card:nth-child(3) { animation-delay: 0.2s; }
.application-card:nth-child(4) { animation-delay: 0.3s; }
.application-card:hover {
    box-shadow: 0 4px 6px rgba(0,0,0,0.05), 0 10px 30px rgba(0,0,0,0.08);
    transform: translateY(-2px);
    border-color: #6ee7b7;
}

/* Card Header */
.application-card .app-header {
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-bottom: 1px solid #f1f5f9;
    background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
}
.app-header .avatar-circle {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #10b981, #059669);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 1.1rem;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}
.app-header .shop-info h6 {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}
.app-header .shop-info small {
    color: #64748b;
    font-size: 0.8rem;
}

/* Stamp Overlay */
.application-card .stamp-overlay {
    position: absolute;
    top: 2rem;
    right: -1rem;
    width: 130px;
    height: 130px;
    opacity: 0.15;
    pointer-events: none;
    z-index: 1;
    transform: rotate(12deg);
}
.application-card .stamp-overlay img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 50%;
}

/* Info Tags */
.info-tags {
    padding: 1rem 1.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.info-tag {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    background: #f1f5f9;
    padding: 0.35rem 0.75rem;
    border-radius: 99px;
    font-size: 0.78rem;
    color: #475569;
    transition: all 0.2s ease;
}
.info-tag:hover {
    background: #e2e8f0;
    color: #334155;
}
.info-tag i {
    font-size: 0.85rem;
    color: #10b981;
}

/* Parts Table */
.parts-table-wrapper {
    padding: 0 1.5rem 1.25rem;
    overflow-x: auto;
}
.parts-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.82rem;
}
.parts-table thead th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.65rem 0.75rem;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}
.parts-table tbody td {
    padding: 0.6rem 0.75rem;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.parts-table tbody tr {
    transition: background 0.15s ease;
}
.parts-table tbody tr:hover {
    background: #f8fafc;
}
.parts-table tfoot td {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
    border-bottom: none;
}
.parts-table .total-row {
    font-weight: 700;
    color: #1e293b;
    background: linear-gradient(90deg, #ecfdf5, #f0fdf4);
}
.parts-table .total-row td {
    padding: 0.75rem;
    border-top: 2px solid #10b981;
}
.price-tag {
    font-variant-numeric: tabular-nums;
    font-weight: 500;
    color: #1e293b;
}
.vat-note {
    font-size: 0.72rem;
    color: #ef4444;
    font-weight: 500;
    padding: 0.5rem 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* Card Footer */
.app-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    background: #fafbff;
    display: flex;
    justify-content: flex-end;
}
.btn-select {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    padding: 0.6rem 1.75rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
}
.btn-select:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);
}
.btn-select:active {
    transform: translateY(0);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    animation: fadeSlideUp 0.5s ease-out;
}
.empty-state-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.25rem;
    background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.empty-state-icon i {
    font-size: 2rem;
    color: #93c5fd;
}
.empty-state h5 {
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.5rem;
}
.empty-state p {
    color: #94a3b8;
    font-size: 0.9rem;
}

/* Invoice Link */
.invoice-link-card {
    text-align: center;
    padding: 1.5rem;
    margin-top: 1.5rem;
    background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
    border-radius: 16px;
    border: 1px dashed #6ee7b7;
    animation: fadeSlideUp 0.7s ease-out;
}
.btn-invoice {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 99px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
}
.btn-invoice:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
    color: #fff;
}

/* Animations */
@keyframes fadeSlideUp {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ── Print invoice styles remain the same ── */
.card-stamp {
    position: absolute;
    top: 3rem;
    right: 50%;
    width: 7rem;
    height: 7rem;
    max-height: 100%;
    border-top-right-radius: 4px;
    opacity: .3;
    overflow: hidden;
    pointer-events: none;
    z-index: 5;
}
.profile-pic.stamp-image {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ccc;
}
.company-stamp {
    position: absolute;
    bottom: 60px;
    left: 10rem;
    width: 200px;
    height: 200px;
    opacity: 0.7;
    transform: rotate(10deg);
    pointer-events: none;
}
.stamp {
    position: absolute;
    bottom: 24px;
    right: 24px;
    width: 120px;
    height: 120px;
    opacity: 0.7;
    transform: rotate(10deg);
    pointer-events: none;
}
@media print {
    .d-print-none { display: none !important; }
}
</style>

<div class="proforma-details-page">
    {{-- Voice Note --}}
    @if($proforma->voice_note_path)
        <div class="voice-note-card">
            <div class="voice-label">
                <i class="bx bx-volume-full"></i> Voice Note Attached
            </div>
            <audio controls>
                <source src="{{ asset('storage/' . $proforma->voice_note_path) }}" type="audio/webm">
                <source src="{{ asset('storage/' . $proforma->voice_note_path) }}" type="audio/mp3">
                Your browser does not support the audio element.
            </audio>
        </div>
    @endif

    {{-- Applications --}}
    @if($proforma->has('applications'))
        <h4 class="section-title text-center">Spare Part Shops</h4>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                @php $hasShops = false; @endphp
                @foreach($shops as $application)
                        @php $hasShops = true; @endphp
                        <div class="application-card">
                            {{-- Stamp Overlay --}}
                            <div class="stamp-overlay">
                                @if($application->applicationBy->stamp_image)
                                    <img src="{{ asset('storage/' . $application->applicationBy->stamp_image) }}" alt="Stamp" />
                                @else
                                    <img src="{{ asset('assets/images/stamp.png') }}" alt="No Stamp" />
                                @endif
                            </div>

                            {{-- Header --}}
                            <div class="app-header">
                                <div class="avatar-circle">
                                    {{ strtoupper(substr($application->applicationBy->name, 0, 2)) }}
                                </div>
                                <div class="shop-info">
                                    <h6>{{ $application->applicationBy->name }}</h6>
                                    <small><i class="bx bx-store-alt"></i> Spare Part Shop</small>
                                </div>
                            </div>

                            {{-- Info Tags --}}
                            <div class="info-tags">
                                <span class="info-tag">
                                    <i class="bx bx-phone"></i> {{ $application->applicationBy->phone_number }}
                                </span>
                                @if($application->applicationBy->store_id)
                                <span class="info-tag">
                                    <i class="bx bx-id-card"></i> Store: {{ $application->applicationBy->store_id }}
                                </span>
                                @endif
                                @if($application->applicationBy->tin_number)
                                <span class="info-tag">
                                    <i class="bx bx-receipt"></i> TIN: {{ $application->applicationBy->tin_number }}
                                </span>
                                @endif
                                @if($application->applicationBy->location)
                                <span class="info-tag">
                                    <i class="bx bx-map"></i> {{ $application->applicationBy->location }}
                                </span>
                                @endif
                            </div>

                            {{-- Parts Table --}}
                            <div class="parts-table-wrapper">
                                <table class="parts-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Part Name & Number</th>
                                            <th>Component</th>
                                            <th>Condition</th>
                                            <th>Grade</th>
                                            <th>Country</th>
                                            <th>Qty</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($proforma->parts as $part)
                                            @php
                                                $partPrice = $application->prices->where('car_part_id', $part->id)->first();
                                                if (!$partPrice) {
                                                    $partPrice = $application->prices->values()->get($loop->index);
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td>{{ $part->number }}</td>
                                                <td>{{ $part->component ?? 'N/A' }}</td>
                                                <td>{{ $part->condition ?? 'N/A' }}</td>
                                                <td>{{ $part->grade }}</td>
                                                <td>{{ $part->country }}</td>
                                                <td>{{ $part->quantity }}</td>
                                                @if($partPrice)
                                                    <td class="price-tag">{{ number_format($partPrice->unit_price, 2) }} ETB</td>
                                                    <td class="price-tag">{{ number_format($partPrice->part_total ?? ($partPrice->unit_price * ($part->quantity ?? 1)), 2) }} ETB</td>
                                                @else
                                                    <td class="price-tag">0.00 ETB</td>
                                                    <td class="price-tag">0.00 ETB</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        @php
                                            $discountPct = (float)($application->discount ?? 0);
                                            $subtotalParts = (float) $application->prices->sum('part_total');
                                            $usingParts = $subtotalParts > 0;
                                            $subtotal = $usingParts ? $subtotalParts : (float) $application->amount;
                                            $discountAmt = $usingParts ? (($subtotal * $discountPct) / 100) : 0.0;
                                            $netTotal = $usingParts ? ($subtotal - $discountAmt) : (float) $application->amount;
                                        @endphp
                                        <tr>
                                            <td colspan="8" style="text-align:right; color:#64748b;">Subtotal</td>
                                            <td class="price-tag">{{ number_format($subtotal, 2) }} ETB</td>
                                        </tr>
                                        <tr>
                                            <td colspan="8" style="text-align:right; color:#64748b;">Discount</td>
                                            <td class="price-tag">{{ number_format($discountAmt, 2) }} ETB ({{ $discountPct }}%)</td>
                                        </tr>
                                        <tr class="total-row">
                                            <td colspan="8" style="text-align:right;">Grand Total</td>
                                            <td class="price-tag" style="font-size:0.95rem; color:#3b82f6;">{{ number_format($netTotal, 2) }} ETB</td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="vat-note">
                                    <i class="bx bx-info-circle"></i> All prices not including VAT
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="app-footer">
                                <button class="btn-select" onclick="openPrintPage(this)">
                                    <i class="bx bx-printer" style="margin-right:0.3rem;"></i> Select & Print
                                </button>
                            </div>
                        </div>
                @endforeach

                @if(!$hasShops)
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bx bx-package"></i>
                        </div>
                        <h5>No Applications Yet</h5>
                        <p>No spare part shop has submitted an application for this proforma.</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bx bx-package"></i>
            </div>
            <h5>No Applications Yet</h5>
            <p>No proforma applications have been submitted yet.</p>
        </div>
    @endif

    {{-- Proforma Media --}}
    @include('components.proforma-media', ['proforma' => $proforma])

    {{-- Invoice Link --}}
    @if($proforma->proformaInvoice && $proforma->proformaInvoice?->sku)
        <div class="invoice-link-card">
            <a href="{{ url('/transaction/' . $proforma->proformaInvoice?->sku) }}" class="btn-invoice" target="_blank">
                <i class="bx bx-file"></i> View Invoice
            </a>
        </div>
    @endif
</div>

{{-- Details Modal --}}
<div class="modal fade" id="details" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label for="input1" class="form-label">Name</label>
                        <input name="name" type="text" class="form-control" id="input1" placeholder="Company Name">
                    </div>
                    <div class="col-lg-6">
                        <label for="input7" class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" id="input7" placeholder="Email">
                    </div>
                    <div class="col-lg-6">
                        <label for="input2" class="form-label">Phone Number</label>
                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="251...">
                    </div>
                    <div class="col-lg-6">
                        <label for="input3" class="form-label">Tin #</label>
                        <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Tin #">
                    </div>
                    <div class="col-lg-6">
                        <label for="input4" class="form-label">Location / Address</label>
                        <input name="location" type="text" class="form-control" id="input4" placeholder="Location">
                    </div>
                    <div class="col-12 col-lg-6">
                        @php
                            $brands = App\Models\Brand::orderBy('name', 'asc')->get();
                            $parts = App\Models\CarPart::orderBy('name', 'asc')->get();
                        @endphp
                        <label for="InputCountry" class="form-label">Brand</label>
                        <select name="brand_id" id="InputCountry">
                            @foreach ($brands as $brand)
                                <option {{ old('brand_id') == $brand->id ? 'selected' : '' }} value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-lg-6">
                        <label for="InputCountry" class="form-label">Year</label>
                        <select name="year" id="InputCountry">
                            @for($i = 1980; $i <= date('Y'); $i++)
                                <option value="{{$i}}" {{ old('year') == $i ? 'selected' : '' }}>{{$i}}</option>
                            @endfor
                        </select>
                        @error('year')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-lg-4">
                        <label for="inputName1" class="form-label">Grade</label>
                        <select name="parts[grade][]" id="inputName1">
                            <option value="1st Grade" {{ old('parts.grade.*') == '1st Grade' ? 'selected' : '' }}>1st Grade</option>
                            <option value="2nd Grade" {{ old('parts.grade.*') == '2nd Grade' ? 'selected' : '' }}>2nd Grade</option>
                            <option value="3rd Grade" {{ old('parts.grade.*') == '3rd Grade' ? 'selected' : '' }}>3rd Grade</option>
                        </select>
                        @error('parts.grade.*')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-lg-2">
                        <label for="condition" class="form-label">Component</label>
                        <select name="parts[condition][]" id="condition" required>
                            <option value="">Select Component</option>
                            <option value="Body Parts" {{ old('parts.condition.*') == 'Body Parts' ? 'selected' : '' }}>Body Parts</option>
                            <option value="Mechanical Parts" {{ old('parts.condition.*') == 'Mechanical Parts' ? 'selected' : '' }}>Mechanical Parts</option>
                        </select>
                        @error('parts.condition.*')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-8">
                        <label for="input6" class="form-label">Business License Image</label>
                        <div class="text-center"><img src="{{asset('assets/images/avatars/avatar-1.png')}}"></div>
                    </div>
                    <div class="col-lg-4">
                        <label for="input6" class="form-label">Stamp Image</label>
                        <div class="text-center" style="max-width: 200px; min-width: 200px"><img style="max-width: 300px;" src="{{asset('assets/images/original.png')}}"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
	function openPrintPage(button) {
		let card = button.closest('.application-card, .job-listing, .card');

		// This part is for the Spare Part Shop Invoice
		let isSparePartInvoice = card.querySelector(".shop-info h6, .job-listing-title, h6.mb-0.font-17");
		if (isSparePartInvoice) {
			let storeIdEl = card.querySelector(".info-tag:nth-of-type(2)");
			let storeId = storeIdEl ? storeIdEl.textContent.replace('Store:', '').trim() : "N/A";
			let tinEl = card.querySelector(".info-tag:nth-of-type(3)");
			let tinNumber = tinEl ? tinEl.textContent.replace('TIN:', '').trim() : "N/A";
			let locationEl = card.querySelector(".info-tag:nth-of-type(4)");
			let location = locationEl ? locationEl.textContent.trim() : "N/A";
			let shopName = card.querySelector(".shop-info h6, .job-listing-title, h6.mb-0.font-17")?.textContent.trim() || "N/A";
			let phoneEl = card.querySelector(".info-tag:nth-of-type(1)");
			let phoneNumber = phoneEl ? phoneEl.textContent.trim() : "N/A";
			let stampImage = card.querySelector(".stamp-overlay img")?.src || "{{ asset('assets/images/stamp.png') }}";

			let table = card.querySelector(".parts-table, table");
			let rows = table?.querySelectorAll("tbody tr") || [];
			let partsData = [];

			rows.forEach((row, index) => {
				let cells = row.querySelectorAll("td");
				if (cells.length >= 8) {
					partsData.push({
						no: index + 1,
						partNumber: cells[1].textContent.trim(),
						component: cells[2].textContent.trim(),
						condition: cells[3].textContent.trim(),
						grade: cells[4].textContent.trim(),
						country: cells[5].textContent.trim(),
						quantity: cells[6].textContent.trim(),
						unitPrice: cells[7].textContent.trim(),
						total: cells[8].textContent.trim()
					});
				}
			});

			function parseETB(value) {
				return parseFloat(value.replace(/[^0-9.-]+/g, "")) || 0;
			}
            function formatETB(num) {
                return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " ETB";
            }

			let subtotal = partsData.reduce((sum, p) => sum + parseETB(p.total), 0);
			let discountPct = parseFloat(card.dataset?.discount) || 0;
			let discountAmt = (subtotal * discountPct) / 100;
			let netTotal = subtotal - discountAmt;

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
						.company-stamp {
						position: absolute;
						bottom: 160px;
						left: 4px;
						width: 300px;
						height:300px;
						opacity: 0.7;
						transform: rotate(10deg);
						pointer-events: none;
						}
						.stamp-image {
							width: 200px;
							height: 200px;
							border-radius: 50%;
							object-fit: cover;
							border: 2px solid #ccc;
						}
						.card-stamp {
							position: absolute;
							top: 3rem;
							right: 50%;
							opacity: .3;
							z-index:5;
						}
						.invoice-container { position: relative; }
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

							<div class="row gy-3 align-items-start">
								<div class="col-sm-6">
									<p class="mb-1"><strong>Store ID:</strong> ${storeId}</p>
									<p class="mb-1"><strong>Shop Name:</strong> ${shopName}</p>
									<p class="mb-1"><strong>Tin #:</strong> ${tinNumber}</p>
									<p class="mb-1"><strong>Location:</strong> ${location}</p>
									<p class="mb-1"><strong>Phone:</strong> ${phoneNumber}</p>
								</div>
								<div class="col-sm-6 text-sm-end">
									<strong>Author:</strong>
									<address>
										etera<br />
										portal.eteraet.com<br />
										Addis Ababa, Ethiopia
									</address>
								</div>
							</div>

							<div class="table-responsive mt-4">
								<table class="table border">
									<thead>
										<tr>
											<th>No</th>
											<th>Part Name & Number</th>
											<th>Component</th>
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
												<td>${part.component}</td>
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
											<td colspan="7" class="text-end"><strong>SUBTOTAL:</strong></td>
											<td class="text-end">${formatETB(subtotal)}</td>
										</tr>
										<tr>
											<td colspan="7" class="text-end"><strong>DISCOUNT:</strong></td>
											<td class="text-end">${formatETB(discountAmt)} (${discountPct}%)</td>
										</tr>
										<tr>
											<td colspan="7" class="text-end"><strong>NET TOTAL:</strong></td>
											<td class="text-end">${formatETB(netTotal)}</td>
										</tr>
										<tr style="background-color: #e3f2fd; font-weight: bold; border-top: 2px solid #1976d2;">
											<td colspan="7" class="text-end"><strong>GRAND TOTAL:</strong></td>
											<td class="text-end" style="color: #1976d2; font-size: 1.1em;">${formatETB(netTotal)}</td>
										</tr>
									</tfoot>
								</table>
							</div>

							<p class="text-danger mt-4"><strong>NOTE:</strong> All prices not including VAT</p>

							<div class="card-stamp">
								<img class="stamp-image" src="${stampImage}" alt="Stamp" />
							</div>
						</main>

						<footer class="text-center mt-4">
							<p><strong>NOTE:</strong> Price is NOT including 15% VAT.</p>
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
			let createdAt = "{{ $proforma->proformaInvoice?->created_at?->format('M d, Y') }}";
			let brand = "{{ $proforma->brand->name ?? 'N/A' }}";
			let year = "{{ $proforma->year ?? 'N/A' }}";
			let description = "{{ $proforma->description ?? 'N/A' }}";

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
						.table th, .table td { padding: 8px; }
						.text-end { text-align: right; }
						.stamp-image {
							width: 200px;
							height: 200px;
							border-radius: 50%;
							object-fit: cover;
							border: 2px solid #ccc;
						}
						.card-stamp {
							position: absolute;
							top: 3rem;
							right: 50%;
							opacity: .3;
							z-index:5;
						}
						
						.company-stamp {
							position: absolute;
							bottom: 0;
							right: 10rem;
							width: 200px;
							height:200px;
							opacity: 0.7;
							transform: rotate(10deg);
							pointer-events: none;
							z-index:5;
						}
						.invoice-container { position: relative; }
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
									    <tr style="background-color: #e3f2fd; font-weight: bold; border-top: 2px solid #1976d2;">
											<td colspan="2" class="text-end"><strong></strong></td>
											<td class="text-end" style="color: #1976d2; font-size: 1.1em;"></td>
										</tr>
										<tr style="background-color: #e3f2fd; font-weight: bold; border-top: 2px solid #1976d2;">
											<td colspan="2" class="text-end"><strong>VAT ({{ number_format($proforma->proformaInvoice?->vat_rate, 2) }}%):</strong></td>
											<td class="text-end" style="color: #1976d2; font-size: 1.1em;">{{ number_format($proforma->proformaInvoice?->vat_amount, 2) }} ETB</td>
										</tr>
										<tr style="background-color: #e3f2fd; font-weight: bold; border-top: 2px solid #1976d2;">
											<td colspan="2" class="text-end"><strong>GRAND TOTAL:</strong></td>
											<td class="text-end" style="color: #1976d2; font-size: 1.1em;">{{ number_format($proforma->proformaInvoice?->total_amount, 2) }} ETB</td>
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
</script>
@endsection