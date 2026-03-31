@extends('layouts.manager')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Files Processed by {{ $operator->name }}</h3>
            <a href="{{ route('manager.operators') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Operators
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Proforma #</th>
                                <th>Customer</th>
                                <th>Commission</th>
                                <th>Status</th>
                                <th>Processed At</th>
                                <th>Reviewed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($processedFiles as $proforma)
                            @php
                                $selection = $proforma->selections->first();
                            @endphp
                            <tr>
                                <td>{{ $proforma->file_number ?? 'N/A' }}</td>
                                <td>{{ $proforma->customer_name ?? 'N/A' }}</td>
                                <td><span class="badge bg-primary">{{ number_format($proforma->calculatePrice(), 2) }} ETB</span></td>
                                <td>
                                    @if($selection)
                                        @if($selection->review_status === null || $selection->review_status === 'pending')
                                            <span class="badge bg-warning">Pending Review</span>
                                        @elseif($selection->review_status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($selection->review_status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">No Selection</span>
                                    @endif
                                </td>
                                <td>{{ $proforma->created_at?->format('M d, Y h:i A') }}</td>
                                <td>{{ $selection->reviewed_at?->format('M d, Y h:i A') ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No files processed yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
