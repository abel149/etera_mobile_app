<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>etera Invoice – {{ $invoice->sku }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Times New Roman', Times, Georgia, serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f0fdf4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #1a1a2e;
        }

        .invoice-wrapper {
            width: 100%;
            max-width: 680px;
            animation: fadeUp 0.5s ease-out;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── Header ────────────────────────────── */
        .invoice-header {
            background: linear-gradient(135deg, #14532d, #166534, #15803d);
            color: #fff;
            padding: 28px 32px 24px;
            border-radius: 16px 16px 0 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .invoice-header::before {
            content: '';
            position: absolute;
            width: 260px; height: 260px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            top: -100px; right: -60px;
        }
        .invoice-header > * { position: relative; z-index: 1; }

        .invoice-logo {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 2px;
        }
        .invoice-subtitle {
            opacity: 0.8;
            font-size: 0.82rem;
            margin-bottom: 10px;
        }
        .invoice-badge {
            display: inline-block;
            background: rgba(255,255,255,0.16);
            backdrop-filter: blur(6px);
            padding: 5px 16px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* ─── Card body ─────────────────────────── */
        .invoice-body {
            background: #fff;
            padding: 28px 32px;
            border: 1px solid rgba(22,101,52,0.08);
            border-top: none;
        }

        /* ─── Info grid ─────────────────────────── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 500px) { .info-grid { grid-template-columns: 1fr; } }

        .info-section-title {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #16a34a;
            margin-bottom: 8px;
        }
        .info-row {
            font-size: 0.85rem;
            margin-bottom: 4px;
            color: #374151;
            line-height: 1.5;
        }
        .info-row strong { color: #111827; font-weight: 600; }
        .info-right { text-align: right; }
        @media (max-width: 500px) { .info-right { text-align: left; } }

        /* Status badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .status-paid   { background: rgba(22,163,74,0.1); color: #15803d; }
        .status-unpaid  { background: rgba(245,158,11,0.1); color: #b45309; }
        .status-dot     { width: 6px; height: 6px; border-radius: 50%; }
        .status-paid   .status-dot { background: #16a34a; }
        .status-unpaid .status-dot { background: #f59e0b; }

        /* Vehicle bar */
        .vehicle-bar {
            background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
            border: 1px solid rgba(22,163,74,0.1);
            border-radius: 10px;
            padding: 10px 16px;
            margin-bottom: 18px;
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
            font-size: 0.84rem;
        }
        .vehicle-bar span  { color: #6b7280; }
        .vehicle-bar strong { color: #111827; }

        /* Divider */
        .invoice-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(22,163,74,0.15), transparent);
            margin: 16px 0;
        }

        /* ─── Stamp ─────────────────────────────── */
        .stamp-section {
            text-align: center;
            margin-bottom: 14px;
        }
        .stamp-section img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            opacity: 0.85;
        }

        /* ─── Billing table wrapper with watermark ── */
        .billing-table-wrapper {
            position: relative;
        }
        .table-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            width: 150px;
            height: 150px;
            object-fit: contain;
            opacity: 0.75;
            pointer-events: none;
            z-index: 1;
        }

        /* ─── Billing table ─────────────────────── */
        .billing-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            position: relative;
            z-index: 0;
        }
        .billing-table th {
            background: #f9fafb;
            padding: 10px 16px;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }
        .billing-table th:last-child { text-align: right; }
        .billing-table td {
            padding: 11px 16px;
            font-size: 0.88rem;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }
        .billing-table td:last-child { text-align: right; font-variant-numeric: tabular-nums; }
        .billing-table tr:last-child td { border-bottom: none; }

        .billing-total {
            background: linear-gradient(135deg, rgba(22,163,74,0.06), rgba(16,185,129,0.06));
        }
        .billing-total td {
            font-weight: 700;
            font-size: 0.95rem;
            color: #111827;
            border-bottom: none;
        }

        /* ─── QR ────────────────────────────────── */
        .qr-section {
            text-align: center;
            padding: 16px 0 4px;
        }
        .qr-label {
            font-size: 0.7rem;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .qr-frame {
            display: inline-block;
            padding: 8px;
            border: 2px solid rgba(22,163,74,0.12);
            border-radius: 12px;
            background: #fff;
        }
        .qr-frame img {
            display: block;
            border-radius: 4px;
            width: 120px;
            height: 120px;
        }

        /* ─── Print button ──────────────────────── */
        .print-section { text-align: center; margin-top: 16px; }
        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: 50px;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #16a34a, #10b981);
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(22,163,74,0.3);
            transition: all 0.3s ease;
        }
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(22,163,74,0.4);
        }
        .print-btn svg { width: 16px; height: 16px; }

        /* ─── Footer ────────────────────────────── */
        .invoice-footer {
            background: #f9fafb;
            border: 1px solid rgba(22,163,74,0.08);
            border-top: none;
            border-radius: 0 0 16px 16px;
            padding: 12px;
            text-align: center;
            font-size: 0.72rem;
            color: #9ca3af;
        }

        /* ─── Service Breakdown ──────────────────────── */
        .service-details {
            background: #f8fafc;
            border: 1px solid rgba(22,163,74,0.1);
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 0;
        }
        .service-details-title {
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #16a34a;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .service-type-badge {
            display: inline-block;
            background: rgba(22,163,74,0.08);
            color: #15803d;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 3px 12px;
            border-radius: 999px;
            margin-bottom: 12px;
        }
        .svc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.83rem;
        }
        .svc-table th {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #9ca3af;
            padding: 0 10px 6px;
            text-align: center;
            border-bottom: 1px solid #f3f4f6;
        }
        .svc-table th:first-child { text-align: left; padding-left: 0; }
        .svc-table td {
            padding: 7px 10px;
            color: #374151;
            border-bottom: 1px solid #f9fafb;
            text-align: center;
            vertical-align: middle;
        }
        .svc-table td:first-child { text-align: left; padding-left: 0; color: #6b7280; font-size: 0.81rem; }
        .svc-table tr:last-child td { border-bottom: none; }
        .svc-val { font-weight: 600; color: #111827; }
        .rate-full    { color: #16a34a; font-weight: 700; font-size: 0.78rem; }
        .rate-partial { color: #d97706; font-weight: 700; font-size: 0.78rem; }
        .rate-zero    { color: #dc2626; font-weight: 700; font-size: 0.78rem; }
        .partial-badge {
            display: inline-flex; align-items: center; gap: 3px;
            background: rgba(245,158,11,0.12); color: #b45309;
            font-size: 0.7rem; font-weight: 700;
            padding: 2px 8px; border-radius: 999px;
        }
        .fill-bar-row { margin-top: 10px; }
        .fill-bar-wrap { display: flex; align-items: center; gap: 10px; }
        .fill-bar {
            flex: 1; height: 5px;
            background: #e5e7eb; border-radius: 999px; overflow: hidden;
        }
        .fill-bar-inner {
            height: 100%; border-radius: 999px;
            background: linear-gradient(90deg, #16a34a, #10b981);
        }
        .fill-bar-inner.partial { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .fill-note { font-size: 0.73rem; color: #b45309; margin-top: 5px; }
        .svc-divider { border: none; border-top: 1px dashed #e9ecef; margin: 6px 0; }

        /* ═══ Print — fit on ONE page ═══════════════ */
        @media print {
            @page {
                size: A4;
                margin: 12mm 14mm;
            }

            html, body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                min-height: auto !important;
                font-family: 'Times New Roman', Times, Georgia, serif !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            * { font-family: 'Times New Roman', Times, Georgia, serif !important; }

            .no-print { display: none !important; }

            .invoice-wrapper {
                max-width: 100% !important;
                width: 100% !important;
                animation: none !important;
            }

            /* Header — keep colours, compact spacing */
            .invoice-header {
                border-radius: 0 !important;
                padding: 18px 24px 14px !important;
                background: linear-gradient(135deg, #14532d, #166534, #15803d) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .invoice-logo   { font-size: 1.6rem !important; }
            .invoice-subtitle { font-size: 0.75rem !important; margin-bottom: 6px !important; }
            .invoice-badge  { font-size: 0.75rem !important; padding: 4px 12px !important; }

            /* Body — tighter */
            .invoice-body {
                padding: 18px 24px !important;
                border: none !important;
            }

            .info-grid     { gap: 12px !important; margin-bottom: 12px !important; }
            .info-row       { font-size: 0.8rem !important; margin-bottom: 2px !important; }
            .info-section-title { font-size: 0.6rem !important; margin-bottom: 5px !important; }

            .vehicle-bar {
                padding: 8px 12px !important;
                margin-bottom: 10px !important;
                font-size: 0.78rem !important;
                background: #f0fdf4 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .invoice-divider { margin: 10px 0 !important; }

            .stamp-section { margin-bottom: 8px !important; }
            .stamp-section img {
                width: 80px !important;
                height: 80px !important;
            }

            /* Table — compact */
            .billing-table th { padding: 7px 12px !important; font-size: 0.68rem !important; }
            .billing-table td { padding: 8px 12px !important; font-size: 0.82rem !important; }
            .billing-total td { font-size: 0.88rem !important; }
            .billing-total {
                background: rgba(22,163,74,0.06) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* QR — smaller */
            .qr-section { padding: 10px 0 2px !important; }
            .qr-label   { font-size: 0.65rem !important; margin-bottom: 4px !important; }
            .qr-frame   { padding: 6px !important; }
            .qr-frame img { width: 90px !important; height: 90px !important; }

            /* Footer */
            .invoice-footer {
                border-radius: 0 !important;
                border: none !important;
                padding: 8px !important;
                font-size: 0.65rem !important;
                background: #f9fafb !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Prevent page breaks inside the invoice */
            .invoice-wrapper, .invoice-body {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

@php
    $transactionUrl = url('/transaction/' . $invoice->sku);
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($transactionUrl);

    // ── Service breakdown stats ──────────────────────────────────
    $totalParts      = $proforma->parts()->count();
    $isEteraChereta  = $proforma->isEteraCheretaMode();
    $isGarageOnly    = $proforma->isGarageOnlyInsurance();
    $isShopOnly      = $proforma->isShopOnlyInsurance();
    $isShopGarage    = $proforma->isShopGarageInsurance();
    $isInsurance     = $invoice->type === 'insurance';
    $isRegular       = $invoice->type === 'regular';

    $requiredShops   = (int)($proforma->required_number_of_shops ?? 0);
    $requiredGarages = (int)($proforma->required_number_of_garages ?? 0);

    $shopApps        = $proforma->applicationsFromShops()->get();
    $garageApps      = $proforma->applicationsFromGarages()->get();
    $filledShops     = $shopApps->count();
    $filledGarages   = $garageApps->count();

    $partsFilled     = (int)$shopApps->sum('filled_parts_count');
    $totalPartSlots  = ($isInsurance && !$isGarageOnly && $totalParts > 0)
                        ? ($totalParts * max(1, $requiredShops))
                        : 0;

    $fillPct         = $totalPartSlots > 0
                        ? min(100, round(($partsFilled / $totalPartSlots) * 100, 1))
                        : null;
    $isPartial       = $fillPct !== null && $fillPct < 100;

    $shopCountFillPct  = $requiredShops  > 0 ? min(100, round(($filledShops   / $requiredShops)   * 100, 1)) : null;
    $garageFillPct     = $requiredGarages > 0 ? min(100, round(($filledGarages / $requiredGarages) * 100, 1)) : null;

    // Type label
    if ($isEteraChereta)                                               $typeLabel = 'Etera Chereta';
    elseif ($isGarageOnly)                                             $typeLabel = 'Insurance – Garage Only';
    elseif ($isShopOnly)                                               $typeLabel = 'Insurance – Shop Only';
    elseif ($isShopGarage || ($isInsurance && $requiredShops > 0 && $requiredGarages > 0))
                                                                       $typeLabel = 'Insurance – Shop & Garage';
    elseif ($isInsurance)                                              $typeLabel = 'Insurance';
    elseif ($isRegular)                                                $typeLabel = 'Regular Proforma';
    else                                                               $typeLabel = ucfirst(str_replace('_', ' ', $invoice->type));

    // Billing table description
    if ($isEteraChereta) {
        $chargeDescription = 'Platform Service – Etera Chereta (Flat Rate)';
    } elseif ($isGarageOnly) {
        $chargeDescription = "Garage Proformas — {$filledGarages} of {$requiredGarages} group" . ($requiredGarages != 1 ? 's' : '') . ' received';
    } elseif ($isShopGarage || ($isInsurance && $requiredGarages > 0)) {
        $sd = $totalPartSlots > 0 ? "{$partsFilled}/{$totalPartSlots} part slots priced" : "{$filledShops}/{$requiredShops} shops";
        $gd = "{$filledGarages}/{$requiredGarages} garage groups";
        $chargeDescription = "Shop ({$sd}) + Garage ({$gd})";
    } elseif ($isInsurance) {
        $sd = $totalPartSlots > 0 ? "{$partsFilled} of {$totalPartSlots} part slots priced" : "{$filledShops} of {$requiredShops} shops received";
        $chargeDescription = "Shop Proformas — {$sd}";
    } elseif ($isRegular) {
        $n = $invoice->requested_count;
        $chargeDescription = "Platform Service — {$n} proforma" . ($n != 1 ? 's' : '') . ' received';
    } else {
        $chargeDescription = 'Platform Service Charge';
    }
@endphp

<div class="invoice-wrapper">

    {{-- Header --}}
    <div class="invoice-header">
        <div class="invoice-logo">etera</div>
        <div class="invoice-subtitle">Platform Service Invoice</div>
        <span class="invoice-badge">Invoice #: {{ $invoice->sku }}</span>
    </div>

    {{-- Body --}}
    <div class="invoice-body">

        {{-- Info Grid --}}
        <div class="info-grid">
            <div>
                <div class="info-section-title">Proforma Details</div>
                <div class="info-row"><strong>File #:</strong> {{ $proforma->file_number }}</div>
                <div class="info-row"><strong>Customer:</strong> {{ $proforma->customer_name }}</div>
                <div class="info-row"><strong>Phone:</strong> {{ $proforma->customer_phone_number ?? 'N/A' }}</div>
            </div>
            <div class="info-right">
                <div class="info-section-title">Invoice Info</div>
                <div class="info-row"><strong>Date:</strong> {{ $invoice->created_at->format('M d, Y') }}</div>
                <div class="info-row"><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $invoice->type)) }}</div>
                <div class="info-row">
                    <strong>Status:</strong>
                    @if($invoice->is_paid)
                        <span class="status-badge status-paid"><span class="status-dot"></span> Paid</span>
                    @else
                        <span class="status-badge status-unpaid"><span class="status-dot"></span> Unpaid</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Vehicle Info --}}
        @if($proforma->brand)
        <div class="vehicle-bar">
            <div><span>Vehicle:</span> <strong>{{ $proforma->brand->name }} {{ $proforma->model }} ({{ $proforma->year }})</strong></div>
            <div><span>Plate:</span> <strong>{{ $proforma->license_plate_number ?? 'N/A' }}</strong></div>
        </div>
        @endif

        <div class="invoice-divider"></div>

        {{-- Service Breakdown Section --}}
        <div class="service-details">
            <div class="service-details-title">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                Service Breakdown
            </div>
            <div class="service-type-badge">{{ $typeLabel }}</div>

            @if(!$isEteraChereta)
            <table class="svc-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Requested</th>
                        <th>Received</th>
                        <th>Fill Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!$isGarageOnly && $requiredShops > 0)
                    <tr>
                        <td>Shop Proformas</td>
                        <td><span class="svc-val">{{ $requiredShops }}</span></td>
                        <td><span class="svc-val">{{ $filledShops }}</span></td>
                        <td>
                            @if($shopCountFillPct >= 100)
                                <span class="rate-full">&#10003; 100%</span>
                            @elseif($shopCountFillPct > 0)
                                <span class="rate-partial">{{ $shopCountFillPct }}%</span>
                            @else
                                <span class="rate-zero">0%</span>
                            @endif
                        </td>
                    </tr>
                    @endif

                    @if(!$isShopOnly && $requiredGarages > 0)
                    <tr>
                        <td>Garage Proformas</td>
                        <td><span class="svc-val">{{ $requiredGarages }}</span></td>
                        <td><span class="svc-val">{{ $filledGarages }}</span></td>
                        <td>
                            @if($garageFillPct >= 100)
                                <span class="rate-full">&#10003; 100%</span>
                            @elseif($garageFillPct > 0)
                                <span class="rate-partial">{{ $garageFillPct }}%</span>
                            @else
                                <span class="rate-zero">0%</span>
                            @endif
                        </td>
                    </tr>
                    @endif

                    @if($totalParts > 0 && !$isGarageOnly)
                    <tr><td colspan="4"><hr class="svc-divider" style="margin:2px 0;"></td></tr>
                    <tr>
                        <td>Parts per Proforma</td>
                        <td><span class="svc-val">{{ $totalParts }}</span></td>
                        <td style="color:#9ca3af;font-size:0.78rem;">—</td>
                        <td style="color:#9ca3af;font-size:0.72rem;">on file</td>
                    </tr>
                    @if($totalPartSlots > 0)
                    <tr>
                        <td>Part Slots Priced</td>
                        <td><span class="svc-val">{{ $totalPartSlots }}</span></td>
                        <td><span class="svc-val">{{ $partsFilled }}</span></td>
                        <td>
                            @if($fillPct >= 100)
                                <span class="rate-full">&#10003; 100%</span>
                            @elseif($fillPct > 0)
                                <span class="partial-badge">&#9888; {{ $fillPct }}%</span>
                            @else
                                <span class="rate-zero">0%</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @endif
                </tbody>
            </table>

            @if($totalPartSlots > 0)
            <div class="fill-bar-row">
                <div class="fill-bar-wrap">
                    <div class="fill-bar">
                        <div class="fill-bar-inner {{ $isPartial ? 'partial' : '' }}" style="width:{{ $fillPct ?? 0 }}%;"></div>
                    </div>
                    <span style="font-size:0.74rem;color:#6b7280;white-space:nowrap;">{{ $partsFilled }} / {{ $totalPartSlots }} part slots</span>
                </div>
                @if($isPartial)
                <div class="fill-note">&#9888; Partial fill — charge is prorated to {{ $fillPct }}% of the full service fee</div>
                @endif
            </div>
            @endif

            @else
            <div style="font-size:0.82rem;color:#6b7280;line-height:1.6;">
                Open competition, timer-based proforma. A flat platform fee applies regardless of the number of shop applications received.
            </div>
            @endif
        </div>

        <div class="invoice-divider" style="margin-top:18px;"></div>

        {{-- Billing Table with Stamp Watermark --}}
        <div class="billing-table-wrapper">
        <img src="{{ asset('assets/invoice/images/stamp.png') }}" alt="etera Stamp" class="table-watermark">
        <table class="billing-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $chargeDescription }}</td>
                    <td>{{ number_format($invoice->unit_price ?: $invoice->hourly_price, 2) }} Birr</td>
                </tr>
                <tr>
                    <td>VAT ({{ $invoice->vat_rate }}%)</td>
                    <td>{{ number_format($invoice->vat_amount, 2) }} Birr</td>
                </tr>
                <tr class="billing-total">
                    <td>Total Amount</td>
                    <td>{{ number_format($invoice->total_amount, 2) }} Birr</td>
                </tr>
            </tbody>
        </table>
        </div>

        {{-- QR Code --}}
        <div class="invoice-divider"></div>
        <div class="qr-section">
            <div class="qr-label">Scan to verify this transaction</div>
            <div class="qr-frame">
                <img src="{{ $qrCodeUrl }}" alt="Transaction QR Code">
            </div>
        </div>

        {{-- Print Button --}}
        <div class="print-section no-print">
            <button class="print-btn" onclick="window.print()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
                Print Invoice
            </button>
        </div>

    </div>

    {{-- Footer --}}
    <div class="invoice-footer">
        © <script>document.write(new Date().getFullYear())</script> etera. All rights reserved.
    </div>

</div>

</body>
</html>
