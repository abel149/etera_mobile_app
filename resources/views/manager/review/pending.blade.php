@extends('layouts.manager')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Pending Files for Review</h3>
            <a href="{{ route('manager.dashboard') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
            </a>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Operator</th>
                                <th>Proforma #</th>
                                <th>Customer</th>
                                <th>Payment</th>
                                <th>Processed At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingSelections as $selection)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0 font-14">{{ $selection->operator->name ?? 'N/A' }}</h6>
                                            <p class="mb-0 font-13 text-secondary">{{ $selection->operator->email ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $selection->proforma->file_number ?? 'N/A' }}</td>
                                <td>{{ $selection->proforma->customer_name ?? 'N/A' }}</td>
                                <td><span class="badge bg-primary">{{ number_format($selection->proforma->calculatePrice(), 2) }} ETB</span></td>
                                <td>{{ $selection->created_at?->format('M d, Y h:i A') }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <form action="{{ route('manager.review.approve', $selection->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this file?')">
                                                <i class="bx bx-check me-1"></i> Send back to owner.
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $selection->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('manager.review.reject', $selection->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reject File</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Rejection Reason *</label>
                                                            <textarea name="rejection_reason" class="form-control" rows="4" required 
                                                                placeholder="Please provide a detailed reason for rejection..." minlength="10"></textarea>
                                                            <small class="text-muted">Minimum 10 characters required</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject File</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bx bx-check-double fs-1 text-success"></i>
                                    <p class="mb-0">No pending files to review. Great job!</p>
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
