@extends('layouts.operator')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Commission History</h3>
            <a href="{{ route('operator.dashboard') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
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
                                <th>Reviewed By</th>
                                <th>Reviewed At</th>
                                <th>Rejection Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($commissions as $commission)
                            <tr>
                                <td>{{ $commission->proforma->file_number ?? 'N/A' }}</td>
                                <td>{{ $commission->proforma->customer_name ?? 'N/A' }}</td>
                                <td><span class="badge bg-primary">{{ number_format($commission->amount, 2) }} ETB</span></td>
                                <td>
                                    @if($commission->status === 'pending_review')
                                        <span class="badge bg-warning">Pending Review</span>
                                    @elseif($commission->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($commission->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @elseif($commission->status === 'paid')
                                        <span class="badge bg-info">Paid</span>
                                    @endif
                                </td>
                                <td>{{ $commission->reviewedBy->name ?? 'Not reviewed' }}</td>
                                <td>{{ $commission->reviewed_at?->format('M d, Y h:i A') ?? '-' }}</td>
                                <td>
                                    @if($commission->rejection_reason)
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reasonModal{{ $commission->id }}">
                                            View Reason
                                        </button>

                                        <!-- Reason Modal -->
                                        <div class="modal fade" id="reasonModal{{ $commission->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Rejection Reason</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>{{ $commission->rejection_reason }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bx bx-money fs-1 text-muted"></i>
                                    <p class="mb-0">No commissions yet. Start processing files to earn!</p>
                                </td>
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
