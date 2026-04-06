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
                    <td>Platform Service Charge</td>
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
