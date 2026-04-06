@extends('layouts.sparepart')
@section('applications')
class="current"
@endsection
@section('content')
<div class="margin-top-45 margin-bottom-45"></div>

<div class="container" style="max-width: 1100px;">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-1"><i class='bx bx-file me-2'></i>Dashboard</h4>
            <!-- <p class="text-muted mb-0">All proformas you have applied on</p> -->
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-2">
            <div class="card radius-10 border-top border-3 border-primary shadow-sm">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0">{{ $totalCount }}</h5>
                    <small class="text-muted">Total Proforma Invoices</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card radius-10 border-top border-3 border-warning shadow-sm">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0 text-warning">{{ $pendingCount + $closedCount }}</h5>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <!-- <div class="col-md-3 col-6 mb-2">
            <div class="card radius-10 border-top border-3 border-danger shadow-sm">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0 text-danger">{{ $closedCount }}</h5>
                    <small class="text-muted">Closed</small>
                </div>
            </div>
        </div> -->
        <div class="col-md-3 col-6 mb-2">
            <div class="card radius-10 border-top border-3 border-success shadow-sm">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0 text-success">{{ $completedCount }}</h5>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Applications Table --}}
    <div class="card radius-10 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Proforma #</th>
                            <th>Brand</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Discount</th>
                            <th>Status</th>
                            <th>Applied On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $index => $application)
                        @php
                            $proforma = $application->proforma;
                            $status = $application->status ?? 'pending';
                            $statusColors = [
                                'pending' => 'bg-warning text-dark',
                                'accepted' => 'bg-success',
                                'rejected' => 'bg-danger',
                                'selected' => 'bg-primary',
                            ];
                            $statusClass = $statusColors[$status] ?? 'bg-secondary';

                            // Calculate final amount
                            if ($application->from === 'shop' && $application->prices->count() > 0) {
                                $subtotal = $application->prices->sum('part_total');
                                $discountPct = (float)($application->discount ?? 0);
                                $discountAmt = ($subtotal * $discountPct) / 100;
                                $finalAmount = $subtotal - $discountAmt;
                            } else {
                                $finalAmount = $application->amount ?? 0;
                            }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($proforma)
                                    <span class="badge bg-light text-dark border">#{{ $proforma->file_number }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $proforma->brand->name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $application->from === 'shop' ? 'bg-info' : 'bg-secondary' }}">
                                    {{ ucfirst($application->from ?? '-') }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ number_format($finalAmount, 2) }}</strong> Birr
                            </td>
                            <td>
                                @if($application->discount > 0)
                                    <span class="text-success">{{ $application->discount }}%</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
                            </td>
                            <td>
                                <small>{{ $application->created_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                @if($proforma)
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#applicationModal{{ $application->id }}">
                                        <i class='bx bx-show'></i> View
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class='bx bx-file bx-lg d-block mb-2'></i>
                                You haven't applied on any proformas yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Application Detail Modals --}}
@foreach($applications as $application)
    @if($application->proforma)
    @php
        $proforma = $application->proforma;
        $parts = $proforma->parts ?? collect();
        $prices = $application->prices ?? collect();
        $discountPct = (float)($application->discount ?? 0);
        $subtotalParts = (float) $prices->sum('part_total');
        $usingParts = $subtotalParts > 0;
        $subtotal = $usingParts ? $subtotalParts : (float) $application->amount;
        $discountAmt = $usingParts ? (($subtotal * $discountPct) / 100) : 0.0;
        $netTotal = $usingParts ? ($subtotal - $discountAmt) : (float) $application->amount;
    @endphp
    <div class="modal fade" id="applicationModal{{ $application->id }}" tabindex="-1"
         aria-labelledby="applicationModalLabel{{ $application->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                {{-- Header --}}
                <div class="modal-header" style="background: rgba(13,148,136,0.2); color: white; border-bottom: 1px solid rgba(255,255,255,0.08);">
                    <h5 class="modal-title" id="applicationModalLabel{{ $application->id }}">
                        <i class='bx bx-file me-2'></i>Application Details — Proforma #{{ $proforma->file_number }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Body --}}
                <div class="modal-body">
                    {{-- Proforma Info --}}
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>Brand:</strong> {{ $proforma->brand->name ?? '-' }}</p>
                            <p class="mb-1"><strong>File #:</strong> {{ $proforma->file_number }}</p>
                            <p class="mb-1"><strong>Status:</strong>
                                @php
                                    $st = $application->status ?? 'pending';
                                    $sc = ['pending'=>'bg-warning text-dark','accepted'=>'bg-success','rejected'=>'bg-danger','selected'=>'bg-primary'][$st] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $sc }}">{{ ucfirst($st) }}</span>
                            </p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <p class="mb-1"><strong>Applied As:</strong>
                                <span class="badge {{ $application->from === 'shop' ? 'bg-info' : 'bg-secondary' }}">
                                    {{ ucfirst($application->from ?? '-') }}
                                </span>
                            </p>
                            <p class="mb-1"><strong>Applied On:</strong> {{ $application->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>

                    <hr>

                    {{-- Parts & Prices Table --}}
                    @if($parts->count() > 0 && $prices->count() > 0)
                    <h6 class="mb-3"><i class='bx bx-list-ul me-1'></i>Parts & Pricing</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Part Name / Number</th>
                                    <th>Condition</th>
                                    <th>Grade</th>
                                    <th>Country</th>
                                    <th>Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parts as $pIdx => $part)
                                    @php
                                        $partPrice = $prices->where('car_part_id', $part->id)->first();
                                        if (!$partPrice) {
                                            $partPrice = $prices->values()->get($loop->index);
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $pIdx + 1 }}</td>
                                        <td>{{ $part->number }}</td>
                                        <td>{{ $part->condition ?? '-' }}</td>
                                        <td>{{ $part->grade ?? '-' }}</td>
                                        <td>{{ $part->country ?? '-' }}</td>
                                        <td>{{ $part->quantity ?? 1 }}</td>
                                        @if($partPrice)
                                            <td class="text-end">{{ number_format($partPrice->unit_price, 2) }} ETB</td>
                                            <td class="text-end">{{ number_format($partPrice->part_total ?? ($partPrice->unit_price * ($part->quantity ?? 1)), 2) }} ETB</td>
                                        @else
                                            <td class="text-end">0.00 ETB</td>
                                            <td class="text-end">0.00 ETB</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="text-end"><strong>SUBTOTAL</strong></td>
                                    <td class="text-end"><strong>{{ number_format($subtotal, 2) }} ETB</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="text-end"><strong>DISCOUNT</strong></td>
                                    <td class="text-end"><strong>{{ number_format($discountAmt, 2) }} ETB ({{ $discountPct }}%)</strong></td>
                                </tr>
                                <tr style="background: rgba(13,148,136,0.12); font-weight: bold; border-top: 2px solid var(--etera-teal);">
                                    <td colspan="7" class="text-end"><strong>NET TOTAL</strong></td>
                                    <td class="text-end" style="color: var(--etera-teal-light); font-size: 1.1em;">
                                        <strong>{{ number_format($netTotal, 2) }} ETB</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @else
                    {{-- Garage / lump-sum application --}}
                    <div class="text-center py-3">
                        <h6 class="text-muted mb-2">Total Amount</h6>
                        <h3 style="color: var(--etera-teal-light);">{{ number_format($netTotal, 2) }} ETB</h3>
                    </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

<div class="margin-top-45 margin-bottom-45"></div>
@endsection
