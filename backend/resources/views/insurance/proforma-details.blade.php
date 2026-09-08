@extends('layouts.insurance')
@section('content')

<style type="text/css">
.card-stamp {
    position: absolute;
    bottom: 3rem;
    left: 0;
    width: calc(var(7rem)* 1);
    height: calc(var(7rem)* 1);
    max-height: 100%;
    border-top-left-radius: 4px;
    opacity: .3;
    overflow: hidden;
    pointer-events: none;
    z-index:5;
}

.card-stamp-icon {
    background: rgba(255, 255, 255, 0.5);
    color: rgba(0, 255, 255, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 100rem;
    width: calc(var(7rem)* 1);
    height: calc(var(7rem)* 1);
    position: relative;
    top: calc(var(7rem)* -.25);
    left: calc(var(7rem)* -.25);
    font-size: calc(var(7rem)* .75);
    transform: rotate(-10deg);
}
.invoice-card {
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
.invoice-header {
    background-color: #1976d2;
    color: white;
    padding: 24px;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.invoice-title {
    font-size: 2.25rem;
    font-weight: 700;
    margin: 0;
}
.invoice-details {
    padding: 24px;
}
.invoice-details p {
    margin-bottom: 8px;
    font-size: 1rem;
}
.invoice-details strong {
    font-weight: 600;
    color: #333;
}
.table-container {
    overflow-x: auto;
}
.invoice-table th,
.invoice-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}
.invoice-table thead th {
    background-color: #f5f5f5;
    font-weight: 600;
}
.invoice-summary {
    padding: 24px;
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
    background-color: #f9f9f9;
}
.invoice-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    font-size: 1rem;
}
.invoice-summary-row strong {
    font-weight: 600;
}
.grand-total {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1976d2;
    border-top: 2px solid #1976d2;
    padding-top: 16px;
    margin-top: 16px;
}
.download-button {
    background-color: #1976d2;
    color: white;
    border-radius: 20px;
    padding: 10px 24px;
    font-size: 1rem;
    transition: background-color 0.3s;
}
.download-button:hover {
    background-color: #1565c0;
}
.center-content {
    display: flex;
    justify-content: center;
    align-items: center;
}

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

.profile-pic.stamp-image {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ccc;
}

/* Invoice Link */
.invoice-link-card {
    text-align: center;
    padding: 1.5rem;
    margin-top: 1.5rem;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-radius: 16px;
    border: 1px dashed #6ee7b7;
    animation: fadeSlideUp 0.7s ease-out;
}
.btn-invoice {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    padding: 12px 32px;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
}
.btn-invoice:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
    color: #fff;
    text-decoration: none;
}

</style>

@if(auth()->user()->has_encryption)
<div id="decryptPanel" class="alert alert-warning d-flex align-items-start gap-3 mb-3 p-3" role="alert">
    <i class="bx bx-lock fs-2 flex-shrink-0 mt-1"></i>
    <div class="flex-grow-1">
        <div class="fw-semibold mb-1">Prices are encrypted &mdash; enter your Encryption PIN to view them.</div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="input-group input-group-sm" style="max-width:220px;">
                <input type="password" id="decryptPin" class="form-control form-control-sm"
                       placeholder="Encryption PIN" autocomplete="new-password">
                <span class="input-group-text" style="cursor:pointer;" onclick="togglePinVisibility('decryptPin', this)">
                    <i class="bx bx-show"></i>
                </span>
            </div>
            <button id="btnDecrypt" class="btn btn-warning btn-sm px-4 radius-30">
                <i class="bx bx-lock-open me-1"></i> Decrypt Prices
            </button>
        </div>
        <div id="decryptError" class="text-danger small mt-2 d-none"></div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body">
        @php $progress = $proforma->partsPricingProgress(); @endphp
        @if($progress['total'] > 0)
        <div class="mb-3 text-center">
            <span class="badge {{ $progress['filled'] >= $progress['total'] ? 'bg-success' : 'bg-warning' }} px-3 py-2" style="font-size: 0.95rem;">
                Parts Priced: {{ $progress['filled'] }} / {{ $progress['total'] }}
            </span>
        </div>
        @endif
    
        <div class="row">
            @if(!$proforma->isGarageOnlyInsurance())
            <div class="col-12 col-md-6 mx-auto">
                <h4 class="mb-3 steper-title text-center">Spare Part Shops</h4>
                @php
                    $shopApps = $applications->filter(fn($a) => $a->from === 'shop');
                    $shopGroups = $shopApps->groupBy('inbox_group');
                    $isCollaborative = $shopGroups->count() > 1
                        || ($shopGroups->count() === 1 && $shopGroups->keys()->first() !== null);
                @endphp
                @foreach($shopGroups as $groupKey => $groupApplications)
                @if($isCollaborative)
                <div style="margin-bottom:8px; padding:6px 12px; background:rgba(13,148,136,0.08); border-left:3px solid rgba(13,148,136,0.5); border-radius:0 8px 8px 0;">
                    <span style="font-size:0.82rem; font-weight:600; color:var(--etera-teal-light,#4dd0c4);">
                        Group {{ $groupKey ?? 'Unassigned' }}
                        <span style="font-weight:400; color:rgba(255,255,255,0.5); margin-left:6px;">{{ $groupApplications->count() }} shop(s)</span>
                    </span>
                </div>
                @endif
                @foreach($groupApplications as $application)
                @if($application->from == 'shop')
                @php $appHasPdf = $application->pdf !== null; $appPdfOnly = $appHasPdf && $application->prices->isEmpty(); @endphp
                @if($appPdfOnly)
                {{-- PDF-only card: show shop name + stamp + View PDF button --}}
                <div class="col-lg-12 mb-3">
                    <div class="card shadow application-card pdf-application-card"
                         style="position:relative; overflow:hidden;"
                         data-application-id="{{ $application->id }}"
                         data-shop-name="{{ $application->applicationBy->name }}"
                         data-store-id="{{ $application->applicationBy->store_id }}"
                         data-tin-number="{{ $application->applicationBy->tin_number }}"
                         data-location="{{ $application->applicationBy->location }}"
                         data-phone="{{ $application->applicationBy->phone_number ?? 'N/A' }}"
                         data-stamp-image="{{ $application->applicationBy->stamp_image ? asset('storage/' . $application->applicationBy->stamp_image) : asset('assets/images/stamp.png') }}"
                         data-notes='@json($application->notes ?? '')'
                         data-customer-name="{{ $proforma->customer_name ?? 'N/A' }}"
                         data-customer-phone="{{ $proforma->customer_phone_number ?? 'N/A' }}"
                         data-brand="{{ $proforma->brand->name ?? 'N/A' }}"
                         data-year="{{ $proforma->year ?? 'N/A' }}"
                         data-plate="{{ $proforma->license_plate_number ?? 'N/A' }}"
                         data-is-shop-garage="{{ $proforma->isShopGarageInsurance() ? '1' : '0' }}"
                         data-pdf-app-id="{{ $application->id }}"
                         data-pdf-encrypted="{{ $application->pdf->isEncrypted() ? '1' : '0' }}"
                         data-pdf-filename="{{ $application->pdf->original_filename }}"
                         data-pdf-serve-url="{{ route('application.pdf.serve', $application->id) }}"
                         data-pdf-encrypted-url="{{ route('application.pdf.encrypted', $application->id) }}">
                        <div class="card-stamp">
                            @if($application->applicationBy->stamp_image)
                            <img class="profile-pic stamp-image" src="{{ asset('storage/' . $application->applicationBy->stamp_image) }}" alt="Stamp" />
                            @else
                            <img class="profile-pic stamp-image" src="{{ asset('assets/images/stamp.png') }}" alt="No Stamp Here" />
                            @endif
                        </div>
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/images/avatars/avatar-9.jpg') }}" class="rounded-circle" width="40" height="40" alt="">
                                <div class="ms-2">
                                    <h6 class="mb-0 font-17">{{ $application->applicationBy->name }}</h6>
                                    <small class="text-muted">Spare Part Shop</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body py-3 px-4">
                            <div class="row g-2 mb-3">
                                <div class="col-6"><span><b class="font-17">Store ID: </b><span class="text-secondary font-16">{{ $application->applicationBy->store_id }}</span></span></div>
                                <div class="col-6"><span><b class="font-17">Tin #: </b><span class="text-secondary font-16">{{ $application->applicationBy->tin_number }}</span></span></div>
                                <div class="col-12"><span><b class="font-17">Location: </b><span class="text-secondary font-16">{{ $application->applicationBy->location }}</span></span></div>
                            </div>

                            {{-- Cover page: proforma invoice details + parts requested. Prices live inside the attached (encrypted) PDF. --}}
                            <div class="invoice mb-3" style="overflow-y: hidden; overflow-x: auto; white-space: nowrap;">
                                <div style="font-size:11px; font-weight:600; color:#4dd0c4; margin-bottom:4px;">
                                    <i class="bx bx-list-ul" style="margin-right:3px;"></i>Parts Requested
                                </div>
                                <table style="font-size: 10px; border-collapse: collapse; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; padding: 4px;">No</th>
                                            <th style="text-align: left; padding: 4px;">Part Name and Number</th>
                                            <th style="text-align: left; padding: 4px;">Condition</th>
                                            <th style="text-align: left; padding: 4px;">Grade</th>
                                            <th style="text-align: left; padding: 4px;">Country</th>
                                            <th style="text-align: left; padding: 4px;">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($proforma->parts->sortBy('id')->values() as $part)
                                        <tr>
                                            <td style="padding: 4px;">{{ $loop->index + 1 }}</td>
                                            <td style="padding: 4px;">{{ $part->number }}</td>
                                            <td style="padding: 4px;">{{ $part->condition ?? 'N/A' }}</td>
                                            <td style="padding: 4px;">{{ $part->grade }}</td>
                                            <td style="padding: 4px;">{{ $part->country }}</td>
                                            <td style="padding: 4px;">{{ $part->quantity }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                @if($proforma->isShopGarageInsurance())
                                <div style="margin-top:10px; padding:10px 12px; border:1px solid rgba(59,130,246,0.25); border-radius:8px; background:rgba(59,130,246,0.06);">
                                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:center;">
                                        <strong style="color:#2563eb;">Garage Repair Service Estimate</strong>
                                        <span class="{{ $application->amount_is_encrypted && $application->encrypted_amount ? 'encrypted-price' : '' }}" data-app-id="{{ $application->id }}">
                                            @if($application->amount_is_encrypted && $application->encrypted_amount)
                                                <i class="bx bx-lock text-warning"></i> <em class="text-warning">Encrypted</em>
                                            @else
                                                {{ number_format((float) $application->amount * 1.15, 2) }} ETB
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @endif

                                <p style="font-size: 9px; margin-top: 4px;">
                                    <strong class="text-danger">NOTE:</strong> Part prices are contained in the attached PDF quotation below.
                                </p>
                            </div>

                            <div class="d-flex align-items-center gap-2 py-2 px-3" style="background:rgba(13,148,136,0.08);border:1px solid rgba(13,148,136,0.2);border-radius:8px;">
                                <i class="bx bxs-file-pdf fs-4" style="color:#ef4444;"></i>
                                <div class="flex-grow-1">
                                    <div style="font-weight:600;font-size:0.88rem;">PDF Quotation</div>
                                    <div style="font-size:0.78rem;color:#aaa;">{{ $application->pdf->original_filename }}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="openPdfViewer(this)"
                                        data-app-id="{{ $application->id }}"
                                        data-encrypted="{{ $application->pdf->isEncrypted() ? '1' : '0' }}"
                                        data-stamp="{{ $application->applicationBy->stamp_image ? asset('storage/' . $application->applicationBy->stamp_image) : asset('assets/images/stamp.png') }}"
                                        data-encrypted-url="{{ route('application.pdf.encrypted', $application->id) }}"
                                        data-serve-url="{{ route('application.pdf.serve', $application->id) }}">
                                    <i class="bx bx-show"></i> View PDF
                                </button>
                            </div>
                            @if($application->notes)
                            <div style="margin-top:10px; background:rgba(13,148,136,0.06); border-left:3px solid #4dd0c4; border-radius:0 6px 6px 0; padding:8px 12px;">
                                <span style="font-size:10px;font-weight:600;color:#4dd0c4;display:block;margin-bottom:3px;"><i class="bx bx-message-detail" style="margin-right:3px;"></i>Applicant Notes</span>
                                <span style="font-size:11px;color:#374151;white-space:pre-wrap;">{{ $application->notes }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @else
                {{-- Normal card (price table) --}}
                <div class="col-lg-12 mb-3">
                    <div class="card shadow application-card"
                         data-application-id="{{ $application->id }}"
                         data-store-id="{{ $application->applicationBy->store_id }}"
                         data-tin-number="{{ $application->applicationBy->tin_number }}"
                         data-location="{{ $application->applicationBy->location }}"
                         data-shop-name="{{ $application->applicationBy->name }}"
                         data-phone="{{ $application->applicationBy->phone_number ?? 'N/A' }}"
                         data-stamp-image="{{ $application->applicationBy->stamp_image ? asset('storage/' . $application->applicationBy->stamp_image) : asset('assets/images/stamp.png') }}"
                         data-vat-rate="15"
                         data-amount="{{ $application->amount ?? 0 }}"
                         data-amount-is-encrypted="{{ $application->amount_is_encrypted ? '1' : '0' }}"
                         data-notes="{{ $application->notes ?? '' }}">

                         
                        <div class="card-stamp">
                            @if($application->applicationBy->stamp_image)
                            <img class="profile-pic stamp-image" src="{{ asset('storage/' . $application->applicationBy->stamp_image) }}" alt="Stamp" />
                            @else
                            <img class="profile-pic stamp-image" src="{{ asset('assets/images/stamp.png') }}" alt="No Stamp Here" />
                            @endif
                        </div>
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="">
                                        <img src="{{asset('assets/images/avatars/avatar-9.jpg')}}" class="rounded-circle" width="40" height="40" alt="">
                                    </div>
                                    <div class="ms-2">
                                        <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#details"><h6 class="mb-0 font-17">{{$application->applicationBy->name}}</h6></a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @if($application->inbox_group !== null)
                                    <span style="font-size:10px; padding:2px 8px; border-radius:50px; background:rgba(13,148,136,0.15); color:var(--etera-teal-light,#4dd0c4); border:1px solid rgba(13,148,136,0.3);">
                                        Grp {{ $application->inbox_group }}
                                    </span>
                                    @endif
                                    @if($application->filled_parts_count && $application->total_parts_count)
                                    <span style="font-size:10px; padding:2px 8px; border-radius:50px; background:rgba(251,146,60,0.12); color:#fb923c; border:1px solid rgba(251,146,60,0.3);">
                                        {{ $application->filled_parts_count }}/{{ $application->total_parts_count }} parts
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-3 px-4 pb-0">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <span><b class="font-17">Store ID: </b>
                                        <span class="text-secondary font-16">{{ $application->applicationBy->store_id }}</span>
                                    </span>
                                </div>
                                <div class="col-6">
                                    <span><b class="font-17">Tin #: </b>
                                        <span class="text-secondary font-16">{{ $application->applicationBy->tin_number }}</span>
                                    </span>
                                </div>
                                <div class="col-10">
                                    <span><b class="font-17">Location: </b>
                                        <span class="text-secondary font-16">{{ $application->applicationBy->location }}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="invoice mb-1" style="overflow-y: hidden; overflow-x: auto; white-space: nowrap;">
                                <table style="font-size: 10px; border-collapse: collapse; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; padding: 4px;">No</th>
                                            <th style="text-align: left; padding: 4px;">Part Name and Number</th>
                                            <th style="text-align: left; padding: 4px;">Condition</th>
                                            <th style="text-align: left; padding: 4px;">Grade</th>
                                            <th style="text-align: left; padding: 4px;">Country</th>
                                            <th style="text-align: left; padding: 4px;">Qty</th>
                                            <th style="text-align: left; padding: 4px;">Unit Price</th>
                                            <th style="text-align: left; padding: 4px;">Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $shopSubtotal = 0; $partIdx = 0; @endphp
                                        @foreach($proforma->parts->sortBy('id')->values() as $part)
                                        @php
                                            $carPartId     = $partCarPartIds[$partIdx] ?? null;
                                            $partPrice     = $carPartId
                                                ? $application->prices->firstWhere('car_part_id', $carPartId)
                                                : $application->prices->values()->get($partIdx);
                                            $partIdx++;
                                            $isEncPart     = $partPrice && !empty($partPrice->price_is_encrypted);
                                            $hasPrice      = $partPrice && !$isEncPart && $partPrice->unit_price > 0;
                                            $unitPrice     = $hasPrice ? (float)$partPrice->unit_price : 0;
                                            $totalPrice    = $hasPrice ? $unitPrice * $part->quantity : 0;
                                            $shopSubtotal += $totalPrice;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $part->number }}</td>
                                            <td>{{ $part->condition ?? 'N/A' }}</td>
                                            <td>{{ $part->grade }}</td>
                                            <td>{{ $part->country }}</td>
                                            <td>{{ $part->quantity }}</td>
                                            @if($isEncPart)
                                            <td class="enc-unit-cell" data-price-id="{{ $partPrice->id }}" data-qty="{{ $part->quantity }}">
                                                <span class="enc-unit-price" data-price-id="{{ $partPrice->id }}">
                                                    <i class="bx bx-lock text-warning"></i> <em class="text-warning">Encrypted</em>
                                                </span>
                                            </td>
                                            <td class="enc-total-cell" data-price-id="{{ $partPrice->id }}">
                                                <span class="enc-part-total" data-price-id="{{ $partPrice->id }}">
                                                    <em class="text-warning">—</em>
                                                </span>
                                            </td>
                                            @elseif($hasPrice)
                                            <td>{{ number_format($unitPrice, 2) }} ETB</td>
                                            <td>{{ number_format($totalPrice, 2) }} ETB</td>
                                            @else
                                            <td class="text-muted fst-italic">— Not available</td>
                                            <td class="text-muted">—</td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        @php
                                        $vatRate = 15;
                                        $netTotal = $shopSubtotal;
                                        $vatAmount = $netTotal * ($vatRate / 100);
                                        $grandTotal = $netTotal + $vatAmount;
                                        @endphp
                                        <tr>
                                            <td colspan="7"></td>
                                            <td>SUBTOTAL (Net)</td>
                                            <td class="shop-subtotal-val" data-app-id="{{ $application->id }}">{{ number_format($netTotal, 2) }} ETB</td>
                                        </tr>
                                        <tr>
                                            <td colspan="7"></td>
                                            <td>VAT (15%)</td>
                                            <td class="shop-vat-val" data-app-id="{{ $application->id }}">{{ number_format($vatAmount, 2) }} ETB</td>
                                        </tr>
                                        <tr>
                                            <td colspan="7"></td>
                                            <td>GRAND TOTAL (VAT Included)</td>
                                            <td class="shop-nettotal-val" data-app-id="{{ $application->id }}">{{ number_format($grandTotal, 2) }} ETB</td>
                                        </tr>
                                    </tfoot>
                                </table>

                                @if($proforma->isShopGarageInsurance())
                                <div style="margin-top:10px; padding:10px 12px; border:1px solid rgba(59,130,246,0.25); border-radius:8px; background:rgba(59,130,246,0.06);">
                                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:center;">
                                        <strong style="color:#2563eb;">Garage Repair Service Estimate</strong>
                                        <span class="{{ $application->amount_is_encrypted && $application->encrypted_amount ? 'encrypted-price' : '' }}" data-app-id="{{ $application->id }}">
                                            @if($application->amount_is_encrypted && $application->encrypted_amount)
                                                <i class="bx bx-lock text-warning"></i> <em class="text-warning">Encrypted</em>
                                            @else
                                                {{ number_format((float) $application->amount * 1.15, 2) }} ETB
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @endif

                                <p style="font-size: 9px; margin-top: 4px;">
                                    <strong class="text-danger">NOTE:</strong> All prices including VAT
                                </p>

                                <div style="font-size: 10px; margin-top: 2px;">
                                    <strong>VAT:</strong> 15% (included in total)
                                </div>
                            </div>
                            @if($application->notes)
                            <div style="margin: 10px 0 4px; background: rgba(13,148,136,0.06); border-left: 3px solid #4dd0c4; border-radius: 0 6px 6px 0; padding: 8px 12px;">
                                <span style="font-size:10px; font-weight:600; color:#4dd0c4; display:block; margin-bottom:3px;"><i class="bx bx-message-detail" style="margin-right:3px;"></i>Applicant Notes</span>
                                <span style="font-size:11px; color:#374151; white-space:pre-wrap;">{{ $application->notes }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-outline-primary select-shop-btn" data-application-id="{{ $application->id }}">Select</button>
                        </div>
                    </div>
                </div>
                @endif
                {{-- end @if($appPdfOnly) --}}
                @endif
                @endforeach
                {{-- end @foreach($groupApplications) --}}
                @endforeach
                {{-- end @foreach($shopGroups) --}}
            </div>
            
            @endif
            @if(!$proforma->isShopOnlyInsurance())
            <div class="col-12 col-md-6 mx-auto">
                <h4 class="mb-3 steper-title text-center">Garages</h4>
                @foreach($applications as $application)
                @if($application->from == 'garage')
                <div class="col-lg-12 mb-3">
                    <div class="card shadow garage-card"
                         data-application-id="{{ $application->id }}"
                         data-store-id="{{ $application->applicationBy->store_id }}"
                         data-tin-number="{{ $application->applicationBy->tin_number }}"
                         data-location="{{ $application->applicationBy->location }}"
                         data-garage-name="{{ $application->applicationBy->name }}"
                         data-phone="{{ $application->applicationBy->phone_number ?? 'N/A' }}"
                         data-stamp-image="{{ $application->applicationBy->stamp_image ? asset('storage/' . $application->applicationBy->stamp_image) : asset('assets/images/stamp.png') }}"
                         data-vat-rate="15"
                         data-amount="{{ $application->amount ?? 0 }}"
                         data-encrypted-amount="{{ $application->encrypted_amount ?? '' }}"
                         data-amount-is-encrypted="{{ $application->amount_is_encrypted ? '1' : '0' }}"
                         data-notes="{{ $application->notes ?? '' }}">

                        <div class="card-stamp">
                            @if($application->applicationBy->stamp_image)
                            <img class="profile-pic stamp-image" src="{{ asset('storage/' . $application->applicationBy->stamp_image) }}" alt="Stamp" />
                            @else
                            <img class="profile-pic stamp-image" src="{{ asset('assets/images/stamp.png') }}" alt="No Stamp Here" />
                            @endif
                        </div>
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <div class="">
                                    <img src="{{asset('assets/images/avatars/avatar-9.jpg')}}" class="rounded-circle" width="40" height="40" alt="">
                                </div>
                                <div class="ms-2">
                                    <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#details"><h6 class="mb-0 font-17">{{$application->applicationBy->name}}</h6></a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-3 px-4 pb-0">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <span><b class="font-17">Store ID: </b>
                                        <span class="text-secondary font-16">{{ $application->applicationBy->store_id }}</span>
                                    </span>
                                </div>
                                <div class="col-6">
                                    <span><b class="font-17">Tin #: </b>
                                        <span class="text-secondary font-16">{{ $application->applicationBy->tin_number }}</span>
                                    </span>
                                </div>
                                <div class="col-10">
                                    <span><b class="font-17">Location: </b>
                                        <span class="text-secondary font-16">{{ $application->applicationBy->location }}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="invoice mb-1" style="overflow-y: hidden; overflow-x: auto; white-space: nowrap;">
                                <table style="font-size: 10px; border-collapse: collapse; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; padding: 4px;">No</th>
                                            <th style="text-align: left; padding: 4px;">Service Name</th>
                                            <th style="text-align: left; padding: 4px;">Description</th>
                                            <th style="text-align: left; padding: 4px;">Service Type</th>
                                            <th style="text-align: left; padding: 4px;">Estimate Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                        $garageAmount    = (float) ($application->amount ?? 0);
                                        $isEncAmt        = !empty($application->amount_is_encrypted);
                                        $garageVatRate   = 15;
                                        $garageNetTotal  = $garageAmount;
                                        $garageVatAmount = $garageNetTotal * ($garageVatRate / 100);
                                        $garageGrandTotal = $garageNetTotal + $garageVatAmount;
                                        @endphp
                                        <tr>
                                            <td style="padding: 4px;">1</td>
                                            <td style="padding: 4px;">Garage Repair Service</td>
                                            <td style="padding: 4px;">Complete repair service</td>
                                            <td style="padding: 4px;">Full Service</td>
                                            <td style="padding: 4px;" class="price-cell" data-app-id="{{ $application->id }}">
                                                @if($isEncAmt)
                                                    <span class="encrypted-price" data-app-id="{{ $application->id }}">
                                                        <i class="bx bx-lock text-warning"></i> <em class="text-warning">Encrypted</em>
                                                    </span>
                                                @else
                                                    {{ number_format($garageAmount, 2) }} ETB
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" style="padding: 4px; font-weight: bold;">SUBTOTAL (Net)</td>
                                            <td style="padding: 4px; font-weight: bold;" class="price-cell" data-app-id="{{ $application->id }}">
                                                @if($isEncAmt)
                                                    <span class="encrypted-price" data-app-id="{{ $application->id }}">
                                                        <i class="bx bx-lock text-warning"></i> <em class="text-warning">Encrypted</em>
                                                    </span>
                                                @else
                                                    {{ number_format($garageNetTotal, 2) }} ETB
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" style="padding: 4px; font-weight: bold;">VAT (15%)</td>
                                            <td style="padding: 4px; font-weight: bold;">
                                                @if(!$isEncAmt)
                                                    {{ number_format($garageVatAmount, 2) }} ETB
                                                @else
                                                    <em class="text-warning">—</em>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" style="padding: 4px; font-weight: bold;">GRAND TOTAL (VAT Included)</td>
                                            <td style="padding: 4px; font-weight: bold;" class="price-cell" data-app-id="{{ $application->id }}">
                                                @if($isEncAmt)
                                                    <span class="encrypted-price" data-app-id="{{ $application->id }}">
                                                        <i class="bx bx-lock text-warning"></i> <em class="text-warning">Encrypted</em>
                                                    </span>
                                                @else
                                                    {{ number_format($garageGrandTotal, 2) }} ETB
                                                @endif
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <p style="font-size: 9px; margin-top: 4px;">
                                    <strong class="text-danger">NOTE:</strong> All prices including VAT
                                </p>
                            </div>
                            @if($application->notes)
                            <div style="margin: 10px 16px 4px; background: rgba(13,148,136,0.06); border-left: 3px solid #4dd0c4; border-radius: 0 6px 6px 0; padding: 8px 12px;">
                                <span style="font-size:10px; font-weight:600; color:#4dd0c4; display:block; margin-bottom:3px;"><i class="bx bx-message-detail" style="margin-right:3px;"></i>Applicant Notes</span>
                                <span style="font-size:11px; color:#374151; white-space:pre-wrap;">{{ $application->notes }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-outline-primary select-garage-btn" data-application-id="{{ $application->id }}">Select</button>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
        </div>

@include('components.proforma-media', ['proforma' => $proforma])

        {{-- Invoice Link --}}
        @if($proforma->proformaInvoice && $proforma->proformaInvoice->sku)
            <div class="invoice-link-card">
                <a href="{{ url('/transaction/' . $proforma->proformaInvoice->sku) }}" class="btn-invoice" target="_blank">
                    <i class="bx bx-file"></i> View Invoice
                </a>
            </div>
        @endif

    </div>
</div>

<!-- PDF Viewer Modal -->
<div class="modal fade" id="pdfViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="height:90vh;">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bxs-file-pdf fs-5" style="color:#ef4444;"></i>
                    <h5 class="modal-title mb-0" id="pdfViewerModalTitle">PDF Quotation</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closePdfViewer()"></button>
            </div>
            <div class="modal-body p-0" style="position:relative; flex:1; overflow:hidden;">
                <div id="pdfViewerLoading" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;z-index:10;background:rgba(0,0,0,0.3);">
                    <div class="text-center text-white">
                        <div class="spinner-border mb-2" role="status"></div>
                        <div id="pdfViewerLoadingMsg">Loading PDF…</div>
                    </div>
                </div>
                <div id="pdfViewerError" style="display:none;position:absolute;inset:0;display:none;align-items:center;justify-content:center;padding:20px;">
                    <div class="alert alert-danger mb-0" id="pdfViewerErrorMsg"></div>
                </div>
                <!-- PDF iframe (no stamp overlaid — stamp lives on the cover card) -->
                <div id="pdfIframeContainer" style="position:relative;width:100%;height:100%;">
                    <iframe id="pdfViewerIframe" src="" style="width:100%;height:100%;border:none;" title="PDF Quotation"></iframe>
                </div>
            </div>
            <div class="modal-footer d-print-none">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" onclick="closePdfViewer()">Close</button>
                <button type="button" id="pdfDownloadBtn" class="btn btn-outline-success" onclick="downloadPdfViewer()"><i class="bx bx-download"></i> Download</button>
                <button type="button" class="btn btn-outline-primary" onclick="printPdfViewer()"><i class="bx bx-printer"></i> Print</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal (unchanged) -->
<div class="modal fade" id="details" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Modal content unchanged -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


@endsection

<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script>{!! file_get_contents(base_path('resources/js/e2e-encryption.js')) !!}</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Spare Part Shop select buttons
    document.querySelectorAll('.select-shop-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const applicationId = this.getAttribute('data-application-id');
            const card = document.querySelector(`.application-card[data-application-id="${applicationId}"]`);
            if (card) {
                openPrintPage(card);
            }
        });
    });
    
    // Handle Garage select buttons
    document.querySelectorAll('.select-garage-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const applicationId = this.getAttribute('data-application-id');
            const card = document.querySelector(`.garage-card[data-application-id="${applicationId}"]`);
            if (card) {
                openPrintPages(card);
            }
        });
    });
});

function openPrintPage(card) {
    if (card.querySelector('.enc-unit-price, .encrypted-price')) {
        alert('Decrypt this application before selecting it so all parts and service prices are included.');
        return;
    }

    // Extract data from the specific card
    const storeId = card.dataset.storeId || "N/A";
    const tinNumber = card.dataset.tinNumber || "N/A";
    const location = card.dataset.location || "N/A";
    const shopName = card.dataset.shopName || "N/A";
    const phoneNumber = card.dataset.phone || "N/A";
    const stampImage = card.dataset.stampImage || "{{ asset('assets/images/stamp.png') }}";
    const vatRate = parseFloat(card.dataset.vatRate) || 15;
    const garageAmount = parseFloat(card.dataset.amount) || 0;
    const isDualService = {{ $proforma->isShopGarageInsurance() ? 'true' : 'false' }};
    const applicantNotes = card.dataset.notes || "";
    
    // Get proforma data
    const customerName = "{{ $proforma->customer_name ?? 'N/A' }}";
    const customerPhone = "{{ $proforma->customer_phone_number ?? 'N/A' }}";
    const brand = "{{ $proforma->brand->name ?? 'N/A' }}";
    const year = "{{ $proforma->year ?? 'N/A' }}";
    const plate = "{{ $proforma->license_plate_number ?? 'N/A' }}";
    const createdAt = "{{ $proforma->proformaInvoice?->created_at->format('M d, Y') }}";
    
    // Extract table data from THIS card
    const table = card.querySelector("table");
    const rows = table?.querySelectorAll("tbody tr") || [];
    const partsData = [];
    
    rows.forEach((row, index) => {
        const cells = row.querySelectorAll("td");
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
    
    // Calculate totals (part prices are NET)
    const parseETB = (value) => parseFloat(value.replace(/[^0-9.-]+/g, "")) || 0;
    const netTotal = partsData.reduce((sum, p) => sum + parseETB(p.total), 0);
    const vatAmount = netTotal * (vatRate / 100);
    const grandTotal = netTotal + vatAmount;
    const garageVat = garageAmount * (vatRate / 100);
    const garageGrand = garageAmount + garageVat;
    const combinedTotal = grandTotal + (isDualService ? garageGrand : 0);
    
    const formatETB = (num) => {
        return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " ETB";
    };

    // Build CSV data for Excel download
    const _csvEsc = (v) => '"' + String(v ?? '').replace(/"/g, '""') + '"';
    const _csvShopRows = [
        ['Proforma Invoice'],
        ['Date', new Date().toLocaleDateString(), 'Shop', shopName],
        ['Store ID', storeId, 'TIN #', tinNumber],
        ['Location', location, 'Phone', phoneNumber],
        ['Customer', customerName, 'Customer Phone', customerPhone],
        ['Car', year + ' ' + brand + ' [' + plate + ']'],
        [],
        ['No', 'Part Name & Number', 'Condition', 'Grade', 'Country', 'Qty', 'Unit Price', 'Total Price'],
        ...partsData.map(p => [p.no, p.partNumber, p.condition, p.grade, p.country, p.quantity, p.unitPrice, p.total]),
        [],
        ['', '', '', '', '', '', 'SUBTOTAL (Net)', netTotal.toFixed(2) + ' ETB'],
        ['', '', '', '', '', '', 'VAT (15%)', vatAmount.toFixed(2) + ' ETB'],
        ['', '', '', '', '', '', 'PARTS GRAND TOTAL (VAT Incl.)', grandTotal.toFixed(2) + ' ETB'],
    ];
    if (isDualService) {
        _csvShopRows.push(['', '', '', '', '', '', 'GARAGE REPAIR (VAT Incl.)', garageGrand.toFixed(2) + ' ETB']);
        _csvShopRows.push(['', '', '', '', '', '', 'COMBINED GRAND TOTAL', combinedTotal.toFixed(2) + ' ETB']);
    }
    const _csvShopUri = 'data:text/csv;charset=utf-8,' + encodeURIComponent(_csvShopRows.map(r => r.map(_csvEsc).join(',')).join('\r\n'));

    // Open print window
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>etera - Spare Parts Invoice</title>
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
                .company-stamp {
                    position: absolute;
                    bottom: 160px;
                    left: 4px;
                    width: 30px;
                    height:30px;
                    opacity: 0.7;
                    transform: rotate(10deg);
                    pointer-events: none;
                }
                .card-stamp {
                    position: absolute;
                    top: 3rem;
                    left: 5rem;
                    opacity: .3;
                    z-index:5;
                }
                .invoice-container { position: relative; }
                .text-primary { color: #1976d2 !important; }
                .border-top { border-top: 2px solid #1976d2 !important; }
            </style>
        </head>
        <body>
            <div class="container-fluid invoice-container">
                <header>
                    <div class="row align-items-center gy-3">
                        <div class="col-sm-7 text-center text-sm-start">
                            <h4 class="text-7 mb-0 text-primary">Proforma Invoice</h4>
                        </div>
                        <div class="col-sm-5 text-center text-sm-end">
                            <h6 class="mb-0">Shop: ${shopName}</h6>
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
                            <p class="mb-1"><strong>Customer:</strong> ${customerName}</p>
                            <p class="mb-1"><strong>Customer Phone:</strong> ${customerPhone}</p>
                            <p class="mb-1"><strong>Car:</strong> ${year} ${brand} [${plate}]</p>
                            <br><br>
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
                                <tr>
                                    <td colspan="7" class="text-end"><strong>PARTS GRAND TOTAL (VAT Included):</strong></td>
                                    <td class="text-end">${formatETB(grandTotal)}</td>
                                </tr>
                                ${isDualService ? `
                                <tr>
                                    <td colspan="7" class="text-end"><strong>GARAGE REPAIR SERVICE (VAT Included):</strong></td>
                                    <td class="text-end">${formatETB(garageGrand)}</td>
                                </tr>
                                ` : ''}
                                <tr style="background-color: #e3f2fd; font-weight: bold; border-top: 2px solid #1976d2;">
                                    <td colspan="7" class="text-end"><strong>${isDualService ? 'COMBINED GRAND TOTAL (VAT Included)' : 'GRAND TOTAL (VAT Included)'}:</strong></td>
                                    <td class="text-end text-primary" style="font-size: 1.1em;">${formatETB(combinedTotal)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p class="text-danger mt-4"><strong>NOTE:</strong> All prices including VAT</p>
                    ${applicantNotes ? `<div style="margin-top:16px;background:#f0fdf9;border-left:4px solid #14b8a6;border-radius:0 6px 6px 0;padding:10px 16px;"><p style="font-size:0.8rem;font-weight:700;color:#0f766e;margin-bottom:4px;">&#128172; Applicant Notes</p><p style="font-size:0.9rem;margin:0;white-space:pre-wrap;color:#1f2937;">${applicantNotes}</p></div>` : ''}

                    <div class="card-stamp">
                        <img class="stamp-image" src="${stampImage}" alt="Stamp" />
                    </div>
                </main>

                <footer class="text-center mt-4">
                    <p><strong>NOTE:</strong> Price is including 15% VAT.</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap d-print-none">
                        <a href="javascript:window.print()" class="btn btn-light border text-black-50 shadow-none">
                            <i class="fa fa-print"></i> Print & Download
                        </a>
                        <a href="${_csvShopUri}" download="proforma-invoice.csv" class="btn btn-success shadow-none">
                            &#128202; Download Excel
                        </a>
                    </div>
                </footer>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
}

function openPrintPages(card) {
    // Extract data from the specific garage card
    const storeId = card.dataset.storeId || "N/A";
    const tinNumber = card.dataset.tinNumber || "N/A";
    const location = card.dataset.location || "N/A";
    const garageName = card.dataset.garageName || "N/A";
    const phoneNumber = card.dataset.phone || "N/A";
    const stampImage = card.dataset.stampImage || "{{ asset('assets/images/stamp.png') }}";
    const vatRate = parseFloat(card.dataset.vatRate) || 15;
    const amount = parseFloat(card.dataset.amount) || 0;
    const applicantNotes = card.dataset.notes || "";
    
    // Get proforma data
    const customerName = "{{ $proforma->customer_name ?? 'N/A' }}";
    const customerPhone = "{{ $proforma->customer_phone_number ?? 'N/A' }}";
    const brand = "{{ $proforma->brand->name ?? 'N/A' }}";
    const year = "{{ $proforma->year ?? 'N/A' }}";
    const plate = "{{ $proforma->license_plate_number ?? 'N/A' }}";
    const createdAt = "{{ $proforma->proformaInvoice?->created_at->format('M d, Y') }}";
    
    // Calculate garage totals (amount is NET, add VAT)
    const netTotal = amount;
    const vatAmount = netTotal * (vatRate / 100);
    
    const formatETB = (num) => {
        return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " ETB";
    };

    // Build CSV data for Excel download
    const _csvEscG = (v) => '"' + String(v ?? '').replace(/"/g, '""') + '"';
    const _csvGarageRows = [
        ['Garage Service Invoice'],
        ['Date', new Date().toLocaleDateString(), 'Garage', garageName],
        ['Store ID', storeId, 'TIN #', tinNumber],
        ['Location', location, 'Phone', phoneNumber],
        ['Customer', customerName, 'Customer Phone', customerPhone],
        ['Car', year + ' ' + brand + ' [' + plate + ']'],
        [],
        ['No', 'Service Name', 'Description', 'Service Type', 'Estimate Price'],
        [1, 'Garage Repair Service', 'Complete repair service', 'Full Service', amount.toFixed(2) + ' ETB'],
        [],
        ['', '', '', 'SUBTOTAL (Net)', netTotal.toFixed(2) + ' ETB'],
        ['', '', '', 'VAT (15%)', vatAmount.toFixed(2) + ' ETB'],
        ['', '', '', 'GRAND TOTAL (VAT Incl.)', (netTotal + vatAmount).toFixed(2) + ' ETB'],
    ];
    const _csvGarageUri = 'data:text/csv;charset=utf-8,' + encodeURIComponent(_csvGarageRows.map(r => r.map(_csvEscG).join(',')).join('\r\n'));

    // Open print window
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>etera - Garage Service Invoice</title>
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
                .card-stamp {
                    position: absolute;
                    top: 3rem;
                    left: 5rem;
                    opacity: .3;
                    z-index:5;
                }
                .invoice-container { position: relative; }
                .text-primary { color: #1976d2 !important; }
                .border-top { border-top: 2px solid #1976d2 !important; }
            </style>
        </head>
        <body>
            <div class="container-fluid invoice-container">
                <header>
                    <div class="row align-items-center gy-3">
                        <div class="col-sm-7 text-center text-sm-start">
                            <h4 class="text-7 mb-0 text-primary">Garage Service Invoice</h4>
                        </div>
                        <div class="col-sm-5 text-center text-sm-end">
                            <h6 class="mb-0">Garage: ${garageName}</h6>
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
                            <p class="mb-1"><strong>Garage Name:</strong> ${garageName}</p>
                            <p class="mb-1"><strong>Tin #:</strong> ${tinNumber}</p>
                            <p class="mb-1"><strong>Location:</strong> ${location}</p>
                            <p class="mb-1"><strong>Phone:</strong> ${phoneNumber}</p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <p class="mb-1"><strong>Customer:</strong> ${customerName}</p>
                            <p class="mb-1"><strong>Customer Phone:</strong> ${customerPhone}</p>
                            <p class="mb-1"><strong>Car:</strong> ${year} ${brand} [${plate}]</p>
                            <br><br>
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
                                    <th>Service Name</th>
                                    <th>Description</th>
                                    <th>Service Type</th>
                                    <th>Estimate Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Garage Repair Service</td>
                                    <td>Complete repair service</td>
                                    <td>Full Service</td>
                                    <td>${formatETB(amount)}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>SUBTOTAL (Net):</strong></td>
                                    <td class="text-end">${formatETB(netTotal)}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>VAT (15%):</strong></td>
                                    <td class="text-end">${formatETB(vatAmount)}</td>
                                </tr>
                                <tr style="background-color: #e3f2fd; font-weight: bold; border-top: 2px solid #1976d2;">
                                    <td colspan="4" class="text-end"><strong>GRAND TOTAL (VAT Included):</strong></td>
                                    <td class="text-end text-primary" style="font-size: 1.1em;">${formatETB(netTotal + vatAmount)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p class="text-danger mt-4"><strong>NOTE:</strong> All prices including VAT</p>
                    ${applicantNotes ? `<div style="margin-top:16px;background:#f0fdf9;border-left:4px solid #14b8a6;border-radius:0 6px 6px 0;padding:10px 16px;"><p style="font-size:0.8rem;font-weight:700;color:#0f766e;margin-bottom:4px;">&#128172; Applicant Notes</p><p style="font-size:0.9rem;margin:0;white-space:pre-wrap;color:#1f2937;">${applicantNotes}</p></div>` : ''}

                    <div class="card-stamp">
                        <img class="stamp-image" src="${stampImage}" alt="Stamp" />
                    </div>
                </main>

                <footer class="text-center mt-4">
                    <p><strong>NOTE:</strong> Price is including 15% VAT.</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap d-print-none">
                        <a href="javascript:window.print()" class="btn btn-light border text-black-50 shadow-none">
                            <i class="fa fa-print"></i> Print & Download
                        </a>
                        <a href="${_csvGarageUri}" download="garage-invoice.csv" class="btn btn-success shadow-none">
                            &#128202; Download Excel
                        </a>
                    </div>
                </footer>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// etera Receipt for Insured Proformas
function openPrintingPage() {
    // This part is for the etera Receipt
    const customerName = "{{ $proforma->customer_name ?? 'N/A' }}";
    const customerPhone = "{{ $proforma->customer_phone_number ?? 'N/A' }}";
    const createdAt = "{{ $proforma->proformaInvoice?->created_at->format('M d, Y') }}";
    const brand = "{{ $proforma->brand->name ?? 'N/A' }}";
    const year = "{{ $proforma->year ?? 'N/A' }}";
    const description = "{{ $proforma->description ?? 'N/A' }}";

    const printWindow = window.open('', '_blank');
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
                    position: absolute;
                    top: 10rem;
                    left: 17rem;
                    opacity: .8;
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
                            <h4 class="text-7 mb-0">etera - Receipt</h4>
                        </div>
                    </div>
                    <hr>
                </header>

                <main>
                    <div class="row gy-3 align-items-start">
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>etera:</strong></p>
                            <p class="mb-1"><strong>Phone:</strong> phone</p>
                            <p class="mb-1"><strong>TIN:</strong> TIN</p>
                            <p class="mb-1"><strong>Date:</strong> ${createdAt}</p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <strong>Customer:</strong> ${customerName}<br>
                            <strong>Phone:</strong> ${customerPhone}
                        </div>
                    </div>
        @php
            $baseAmount = $proforma->proformaInvoice?->unit_price;
            $vatRate = 15;
            $vatAmount = ($baseAmount * $vatRate) / 100;
            $totalAmount = $baseAmount + $vatAmount;
        @endphp
	<div class="table-responsive mt-4">
          <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Platform Service Charge</td>
                    <td class="text-end">{{ number_format($baseAmount, 2) }} Birr</td>
                </tr>
                <tr>
                    <td>VAT ({{ $vatRate }}%)</td>
                    <td class="text-end">{{ number_format($vatAmount, 2) }} Birr</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="table-success">
                    <th>Total Paid Amount</th>
                    <th class="text-end">{{ number_format($totalAmount, 2) }} Birr</th>
                </tr>
            </tfoot>
        </table>

                         <img src="{{ asset('assets/invoice/images/stamp.png') }}" class="stamp-image" alt="Stamp">
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

@php
    // Build encrypted data maps in PHP to avoid complex expressions inside @json()
    $encryptedAppsMap = [];
    foreach ($applications as $_app) {
        if (!empty($_app->amount_is_encrypted) && $_app->encrypted_amount) {
            $encryptedAppsMap[$_app->id] = $_app->encrypted_amount;
        }
    }
    $encryptedPricesMap = [];
    foreach ($applications->where('from', 'shop') as $_app) {
        foreach ($_app->prices as $_price) {
            if (!empty($_price->price_is_encrypted) && $_price->encrypted_unit_price) {
                $encryptedPricesMap[$_price->id] = [
                    'id'     => $_price->id,
                    'cipher' => $_price->encrypted_unit_price,
                    'qty'    => $_price->quantity,
                    'app_id' => $_app->id,
                ];
            }
        }
    }
@endphp
// ── E2E Decryption ───────────────────────────────────────────────────────────────────
// Garage encrypted amounts: { application_id: encrypted_amount_ciphertext }
const _encryptedApps = @json($encryptedAppsMap);

// Shop encrypted part prices: { price_id: { id, cipher, qty, app_id } }
const _encryptedPrices = @json($encryptedPricesMap);

// ── Shared: apply all DOM decryption updates given a CryptoKey ───────────────
async function applyDecryption(privateKey) {
    const fmt = n => n.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + ' ETB';

    for (const [appId, cipher] of Object.entries(_encryptedApps)) {
        if (!cipher) continue;
        const amount = parseFloat(await E2EEncryption.decryptValue(cipher, privateKey));
        document.querySelectorAll(`.encrypted-price[data-app-id="${appId}"]`).forEach(el => {
            el.outerHTML = `<span>${fmt(amount * 1.15)}</span>`;
        });
        // Update card datasets so selected quotations read the decrypted value, not 0
        document.querySelectorAll(`[data-application-id="${appId}"]`).forEach(card => {
            card.dataset.amount = amount;
            card.dataset.amountIsEncrypted = '0';
        });
    }

    const appSubtotals = {};
    for (const [priceId, data] of Object.entries(_encryptedPrices)) {
        if (!data.cipher) continue;
        const unitPrice = parseFloat(await E2EEncryption.decryptValue(data.cipher, privateKey));
        const partTotal = unitPrice * (data.qty || 1);

        document.querySelectorAll(`.enc-unit-price[data-price-id="${priceId}"]`).forEach(el => {
            el.outerHTML = `<span>${fmt(unitPrice)}</span>`;
        });
        document.querySelectorAll(`.enc-part-total[data-price-id="${priceId}"]`).forEach(el => {
            el.outerHTML = `<span>${fmt(partTotal)}</span>`;
        });

        const appId = data.app_id;
        if (appId) appSubtotals[appId] = (appSubtotals[appId] || 0) + partTotal;
    }

    for (const [appId, subtotal] of Object.entries(appSubtotals)) {
        const vatCell = document.querySelector(`.shop-vat-val[data-app-id="${appId}"]`);
        const vatRate  = 15;
        const netAmount  = subtotal;
        const vatAmount  = netAmount * (vatRate / 100);
        const grandTotal = netAmount + vatAmount;

        const subtotalCell = document.querySelector(`.shop-subtotal-val[data-app-id="${appId}"]`);
        const grandTotalCell = document.querySelector(`.shop-nettotal-val[data-app-id="${appId}"]`);
        if (subtotalCell) subtotalCell.textContent = fmt(netAmount);
        if (vatCell) vatCell.textContent = fmt(vatAmount);
        if (grandTotalCell) grandTotalCell.textContent = fmt(grandTotal);
    }

    const panel = document.getElementById('decryptPanel');
    if (panel) panel.innerHTML =
        '<div class="alert alert-success mb-0"><i class="bx bx-check-circle me-2"></i>Prices decrypted successfully.</div>';
}

function togglePinVisibility(inputId, toggleEl) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const icon = toggleEl.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) { icon.classList.remove('bx-show'); icon.classList.add('bx-hide'); }
    } else {
        input.type = 'password';
        if (icon) { icon.classList.remove('bx-hide'); icon.classList.add('bx-show'); }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const btnDecrypt = document.getElementById('btnDecrypt');
    const decryptPin = document.getElementById('decryptPin');
    const decryptErr = document.getElementById('decryptError');

    if (!btnDecrypt) return;

    btnDecrypt.addEventListener('click', async function() {
        const pin = decryptPin ? decryptPin.value.trim() : '';
        if (!pin) {
            decryptErr.textContent = 'Please enter your Encryption PIN.';
            decryptErr.classList.remove('d-none');
            return;
        }
        decryptErr.classList.add('d-none');
        btnDecrypt.disabled = true;
        btnDecrypt.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Decrypting…';

        try {
            const resp = await fetch('{{ route("insurance.encryption.private-key") }}');
            if (!resp.ok) throw new Error('Could not load private key from server.');
            const keyBlob = await resp.json();

            const privateKey = await E2EEncryption.decryptPrivateKey(
                keyBlob.encrypted_private_key,
                keyBlob.key_iv,
                keyBlob.key_salt,
                pin
            );

            await applyDecryption(privateKey);
            _cachedPrivateKey = privateKey;

        } catch (err) {
            decryptErr.textContent = 'Decryption failed — check your PIN and try again. (' + err.message + ')';
            decryptErr.classList.remove('d-none');
            btnDecrypt.disabled = false;
            btnDecrypt.innerHTML = '<i class="bx bx-lock-open me-1"></i> Decrypt Prices';
        }
    });
});

// ── PDF Viewer ────────────────────────────────────────────────────────────────
let _pdfBlobUrl = null;
let _mergedPdfUrl = null;
let _cachedPrivateKey = null;

function closePdfViewer() {
    const iframe = document.getElementById('pdfViewerIframe');
    if (iframe) iframe.src = '';
    if (_pdfBlobUrl) { URL.revokeObjectURL(_pdfBlobUrl); _pdfBlobUrl = null; }
    if (_mergedPdfUrl) { URL.revokeObjectURL(_mergedPdfUrl); _mergedPdfUrl = null; }
}

async function buildPdfOnlyCover(card) {
const escapeHtml = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const formatETB = (n) => n.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + ' ETB';

const shopName = card.dataset.shopName || 'N/A';
const storeId = card.dataset.storeId || 'N/A';
const tin = card.dataset.tinNumber || 'N/A';
const location = card.dataset.location || 'N/A';
const phone = card.dataset.phone || 'N/A';
const stamp = card.dataset.stampImage || '';

async function loadStampAsDataUrl(url) {
  return new Promise((resolve) => {
    if (!url) return resolve(url);
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
      const c = document.createElement('canvas');
      c.width = img.naturalWidth || 200;
      c.height = img.naturalHeight || 200;
      const ctx = c.getContext('2d');
      ctx.drawImage(img, 0, 0);
      try { resolve(c.toDataURL('image/png')); } catch(e) { resolve(url); }
    };
    img.onerror = () => resolve(url);
    img.src = url;
  });
}

const stampDataUrl = await loadStampAsDataUrl(stamp);

const customerName = card.dataset.customerName || 'N/A';
const customerPhone = card.dataset.customerPhone || 'N/A';
const brand = card.dataset.brand || 'N/A';
const year = card.dataset.year || 'N/A';
const plate = card.dataset.plate || 'N/A';
const isDualService = card.dataset.isShopGarage === '1';
const garageAmount = parseFloat(card.dataset.amount) || 0;
const rawNotes = card.dataset.notes || 'null';
let notes = '';
try { notes = JSON.parse(rawNotes) || ''; } catch(e) { notes = ''; }

const table = card.querySelector('.invoice table');
const rows = table ? table.querySelectorAll('tbody tr') : [];
let partRows = '';
rows.forEach((tr) => {
  const cells = tr.querySelectorAll('td');
  if (cells.length >= 6) {
    partRows += '<tr>' +
      '<td style=\'border:1px solid #ddd;padding:8px;\'>' + escapeHtml(cells[0].textContent.trim()) + '</td>' +
      '<td style=\'border:1px solid #ddd;padding:8px;\'>' + escapeHtml(cells[1].textContent.trim()) + '</td>' +
      '<td style=\'border:1px solid #ddd;padding:8px;\'>' + escapeHtml(cells[2].textContent.trim()) + '</td>' +
      '<td style=\'border:1px solid #ddd;padding:8px;\'>' + escapeHtml(cells[3].textContent.trim()) + '</td>' +
      '<td style=\'border:1px solid #ddd;padding:8px;\'>' + escapeHtml(cells[4].textContent.trim()) + '</td>' +
      '<td style=\'border:1px solid #ddd;padding:8px;\'>' + escapeHtml(cells[5].textContent.trim()) + '</td>' +
      '</tr>';
  }
});

const garageBlock = isDualService ? '<div style=\'display:flex;justify-content:flex-end;margin-top:16px;\'>' +
  '<table style=\'width:320px;border-collapse:collapse;\'><tbody>' +
  '<tr style=\'background:#e3f2fd;font-weight:bold;border-top:2px solid #1976d2;\'>' +
  '<td style=\'border:1px solid #ddd;padding:8px;\'><strong>Garage Repair Service (VAT Included):</strong></td>' +
  '<td style=\'border:1px solid #ddd;padding:8px;text-align:right;\'>' + formatETB(garageAmount * 1.15) + '</td>' +
  '</tr></tbody></table></div>' : '';

const notesBlock = notes ? '<div style=\'margin-top:16px;background:#f0fdf9;border-left:4px solid #14b8a6;border-radius:0 6px 6px 0;padding:10px 16px;\'>' +
  '<p style=\'font-size:12px;font-weight:700;color:#0f766e;margin-bottom:4px;\'>Applicant Notes</p>' +
  '<p class=\'pdf-cover-notes\' style=\'font-size:13px;margin:0;white-space:pre-wrap;color:#1f2937;\'></p></div>' : '';

let coverHtml = '<div class=\'pdf-only-cover\' style=\'width:794px;min-height:1123px;padding:48px;background:#fff;box-sizing:border-box;font-family:Arial,Helvetica,sans-serif;color:#333;position:relative;\'>' +
  '<header style=\'border-bottom:2px solid #1976d2;padding-bottom:16px;margin-bottom:24px;\'>' +
  '<div style=\'display:flex;justify-content:space-between;align-items:center;\'>' +
  '<h2 style=\'color:#1976d2;margin:0;font-size:28px;\'>Proforma Invoice</h2>' +
  '<h4 style=\'margin:0;font-size:18px;\'>Shop: ' + escapeHtml(shopName) + '</h4>' +
  '</div></header>' +
  '<main>' +
  '<div style=\'display:flex;justify-content:space-between;margin-bottom:24px;font-size:14px;line-height:1.6;\'>' +
  '<div>' +
  '<p style=\'margin:0 0 4px 0;\'><strong>Date:</strong> ' + new Date().toLocaleDateString() + '</p>' +
  '<p style=\'margin:0 0 4px 0;\'><strong>Store ID:</strong> ' + escapeHtml(storeId) + '</p>' +
  '<p style=\'margin:0 0 4px 0;\'><strong>Shop Name:</strong> ' + escapeHtml(shopName) + '</p>' +
  '<p style=\'margin:0 0 4px 0;\'><strong>Tin #:</strong> ' + escapeHtml(tin) + '</p>' +
  '<p style=\'margin:0 0 4px 0;\'><strong>Location:</strong> ' + escapeHtml(location) + '</p>' +
  '<p style=\'margin:0 0 4px 0;\'><strong>Phone:</strong> ' + escapeHtml(phone) + '</p>' +
  '</div>' +
  '<div style=\'text-align:right;\'>' +
  '<p style=\'margin:0 0 4px 0;\'><strong>Customer:</strong> ' + escapeHtml(customerName) + '</p>' +
  '<p style=\'margin:0 0 4px 0;\'><strong>Customer Phone:</strong> ' + escapeHtml(customerPhone) + '</p>' +
  '<p style=\'margin:0 0 4px 0;\'><strong>Car:</strong> ' + escapeHtml(year) + ' ' + escapeHtml(brand) + ' [' + escapeHtml(plate) + ']</p>' +
  '</div></div>' +
  '<table style=\'width:100%;border-collapse:collapse;font-size:13px;\'>' +
  '<thead><tr style=\'background:#f5f5f5;\'>' +
  '<th style=\'border:1px solid #ddd;padding:8px;text-align:left;\'>No</th>' +
  '<th style=\'border:1px solid #ddd;padding:8px;text-align:left;\'>Part Name & Number</th>' +
  '<th style=\'border:1px solid #ddd;padding:8px;text-align:left;\'>Condition</th>' +
  '<th style=\'border:1px solid #ddd;padding:8px;text-align:left;\'>Grade</th>' +
  '<th style=\'border:1px solid #ddd;padding:8px;text-align:left;\'>Country</th>' +
  '<th style=\'border:1px solid #ddd;padding:8px;text-align:left;\'>Qty</th>' +
  '</tr></thead><tbody>' + partRows + '</tbody></table>' +
  garageBlock +
  '<p style=\'color:#d32f2f;font-size:12px;margin-top:24px;\'><strong>NOTE:</strong> Part prices are contained in the attached PDF quotation.</p>' +
  notesBlock +
  '<div style=\'position:absolute;top:3rem;left:5rem;opacity:0.3;z-index:5;\'>' +
  '<img src=\'' + stampDataUrl + '\' style=\'width:200px;height:200px;border-radius:50%;object-fit:cover;border:2px solid #ccc;\' />' +
  '</div></main></div>';

const container = document.createElement('div');
container.style.position = 'fixed';
container.style.left = '-9999px';
container.style.top = '0';
container.innerHTML = coverHtml;
document.body.appendChild(container);

const notesEl = container.querySelector('.pdf-cover-notes');
if (notesEl && notes) notesEl.textContent = notes;

const target = container.querySelector('.pdf-only-cover');
const canvas = await html2canvas(target, { scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false });
document.body.removeChild(container);
return canvas.toDataURL('image/jpeg', 0.9);
}

async function buildCoverMergedPdfUrl(card, originalPdfBytes, privateKey) {
    const { PDFDocument } = PDFLib;

    // If we have the private key, decrypt any encrypted amounts on the card first
    // so the cover page shows the real garage price.
    if (privateKey && typeof applyDecryption === 'function') {
        try { await applyDecryption(privateKey); } catch(e) { /* already decrypted or failed */ }
    }

    // Build a full-page invoice-style cover from the card data
    const dataUrl = await buildPdfOnlyCover(card);
    const b64 = dataUrl.split(',')[1];
    const coverBytes = Uint8Array.from(atob(b64), c => c.charCodeAt(0));

    const originalDoc = await PDFDocument.load(originalPdfBytes);
    const mergedDoc = await PDFDocument.create();

    const coverImg = await mergedDoc.embedJpg(coverBytes);
    const pageWidth = 595;
    const pageHeight = coverImg.height * pageWidth / coverImg.width;
    const coverPage = mergedDoc.addPage([pageWidth, pageHeight]);
    coverPage.drawImage(coverImg, { x: 0, y: 0, width: pageWidth, height: pageHeight });

    const copiedPages = await mergedDoc.copyPages(originalDoc, originalDoc.getPageIndices());
    copiedPages.forEach(page => mergedDoc.addPage(page));

    const mergedBytes = await mergedDoc.save();
    if (_mergedPdfUrl) URL.revokeObjectURL(_mergedPdfUrl);
    _mergedPdfUrl = URL.createObjectURL(new Blob([mergedBytes], { type: 'application/pdf' }));
    return _mergedPdfUrl;
}

async function printPdfViewer() {
    const iframe = document.getElementById('pdfViewerIframe');
    if (!iframe || !iframe.src) return;
    // Print the merged cover + original PDF shown in the viewer.
    try {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    } catch(ex) {
        window.open(iframe.src, '_blank');
    }
}

async function downloadPdfViewer() {
    const iframe = document.getElementById('pdfViewerIframe');
    if (!iframe || !iframe.src) return;
    // Download the merged cover + original PDF shown in the viewer.
    const a = document.createElement('a');
    a.href = iframe.src; a.download = 'quotation.pdf';
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
}

async function openPdfViewer(btn) {
    const isEncrypted  = btn.dataset.encrypted === '1';
    const encryptedUrl = btn.dataset.encryptedUrl || '';
    const serveUrl     = btn.dataset.serveUrl || '';
    const card         = btn.closest('.pdf-application-card');

    const modal       = new bootstrap.Modal(document.getElementById('pdfViewerModal'));
    const loading     = document.getElementById('pdfViewerLoading');
    const loadingMsg  = document.getElementById('pdfViewerLoadingMsg');
    const errBox      = document.getElementById('pdfViewerError');
    const errMsg      = document.getElementById('pdfViewerErrorMsg');
    const iframe      = document.getElementById('pdfViewerIframe');

    errBox.style.display = 'none';
    loading.style.display = 'flex';
    iframe.src = '';

    modal.show();

    try {
        if (isEncrypted) {
            loadingMsg.textContent = 'Fetching encrypted PDF…';
            const resp = await fetch(encryptedUrl);
            if (!resp.ok) throw new Error('Could not fetch PDF data.');
            const data = await resp.json();

            // Helper: decrypt PDF bytes, build cover page, and show merged document
            const decryptAndShow = async (privateKey) => {
                const unb64 = s => Uint8Array.from(atob(s), c => c.charCodeAt(0));
                const rawAesKey = await crypto.subtle.decrypt(
                    { name: 'RSA-OAEP' }, privateKey, unb64(data.encrypted_aes_key)
                );
                const aesKey = await crypto.subtle.importKey(
                    'raw', rawAesKey, { name: 'AES-GCM', length: 256 }, false, ['decrypt']
                );
                const pdfBytes = await crypto.subtle.decrypt(
                    { name: 'AES-GCM', iv: unb64(data.aes_iv) }, aesKey, unb64(data.encrypted_pdf)
                );
                const blob = new Blob([pdfBytes], { type: 'application/pdf' });
                if (_pdfBlobUrl) URL.revokeObjectURL(_pdfBlobUrl);
                _pdfBlobUrl = URL.createObjectURL(blob);

                loadingMsg.textContent = 'Building cover page…';
                const mergedUrl = await buildCoverMergedPdfUrl(card, pdfBytes, privateKey);
                iframe.src = mergedUrl;
                loading.style.display = 'none';
            };

            if (_cachedPrivateKey) {
                // Prices already decrypted — reuse key without asking for PIN again
                loadingMsg.textContent = 'Decrypting PDF…';
                await decryptAndShow(_cachedPrivateKey);
            } else {
                // Show inline PIN prompt
                loading.style.display = 'flex';
                loadingMsg.innerHTML = `
                    <div style="background:rgba(0,0,0,0.6);border-radius:10px;padding:20px;max-width:320px;margin:auto;">
                        <p style="margin-bottom:10px;font-size:0.9rem;">Enter your Encryption PIN to decrypt this PDF:</p>
                        <div class="input-group mb-2">
                            <input type="password" id="pdfDecryptPin" class="form-control" placeholder="Encryption PIN" autocomplete="new-password">
                            <span class="input-group-text" style="cursor:pointer;" onclick="togglePinVisibility('pdfDecryptPin', this)">
                                <i class="bx bx-show"></i>
                            </span>
                        </div>
                        <button class="btn btn-primary w-100" id="pdfDecryptBtn">Decrypt &amp; View</button>
                        <div id="pdfDecryptErr" class="text-danger small mt-2"></div>
                    </div>`;

                document.getElementById('pdfDecryptBtn').addEventListener('click', async function() {
                    const pin = document.getElementById('pdfDecryptPin').value.trim();
                    if (!pin) {
                        document.getElementById('pdfDecryptErr').textContent = 'Please enter your PIN.';
                        return;
                    }
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Decrypting…';
                    document.getElementById('pdfDecryptErr').textContent = '';
                    try {
                        const keyResp = await fetch('{{ route("insurance.encryption.private-key") }}');
                        if (!keyResp.ok) throw new Error('Could not load private key.');
                        const keyBlob = await keyResp.json();
                        const privateKey = await E2EEncryption.decryptPrivateKey(
                            keyBlob.encrypted_private_key, keyBlob.key_iv, keyBlob.key_salt, pin
                        );
                        _cachedPrivateKey = privateKey;
                        await decryptAndShow(privateKey);
                    } catch(err) {
                        document.getElementById('pdfDecryptErr').textContent = 'Decryption failed — check your PIN. (' + err.message + ')';
                        this.disabled = false;
                        this.textContent = 'Decrypt & View';
                    }
                });
            }
        } else {
            // Plain PDF — fetch bytes and prepend cover page
            loadingMsg.textContent = 'Loading PDF…';
            const resp = await fetch(serveUrl);
            if (!resp.ok) throw new Error('Could not fetch PDF.');
            const pdfBytes = await resp.arrayBuffer();
            const blob = new Blob([pdfBytes], { type: 'application/pdf' });
            if (_pdfBlobUrl) URL.revokeObjectURL(_pdfBlobUrl);
            _pdfBlobUrl = URL.createObjectURL(blob);

            loadingMsg.textContent = 'Building cover page…';
            const mergedUrl = await buildCoverMergedPdfUrl(card, pdfBytes, _cachedPrivateKey);
            iframe.src = mergedUrl;
            loading.style.display = 'none';
        }
    } catch(err) {
        loading.style.display = 'none';
        errMsg.textContent = 'Error: ' + err.message;
        errBox.style.display = 'flex';
    }
}

// ── Etera-Chereta dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const proformaTypeSelect = document.getElementById('insuranceProformaType');
    const eteraCheretaDropdown = document.getElementById('insuranceEteraCheretaDropdown');

    function toggleEteraCheretaDropdown() {
        if (proformaTypeSelect && proformaTypeSelect.value === '-1') {
            eteraCheretaDropdown.style.display = 'block';
        } else if (eteraCheretaDropdown) {
            eteraCheretaDropdown.style.display = 'none';
        }
    }

    if (proformaTypeSelect) {
        proformaTypeSelect.addEventListener('change', toggleEteraCheretaDropdown);
        toggleEteraCheretaDropdown();
    }
});
</script>
