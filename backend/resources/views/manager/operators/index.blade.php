@extends('layouts.manager')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <h3 class="mb-3">My Operators</h3>

        <div class="d-flex justify-content-between align-items-center mb-3">
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
                                <th>Operator Name</th>
                                <th>Phone</th>
                                <th>Total Quota</th>
                                <th>Used Files</th>
                                <th>Available</th>
                                <th>Total Commissions</th>
                                <th>Pending</th>
                                <th>Approved</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($operators as $operator)
                            <tr data-operator-id="{{ $operator->id }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0 font-14">{{ $operator->name }}</h6>
                                            <p class="mb-0 font-13 text-secondary">{{ $operator->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $operator->phone_number }}</td>
                                <td><span class="badge bg-primary">{{ $operator->total_quota }}</span></td>
                                <td>{{ $operator->used_quota }}</td>
                                <td><span class="badge bg-success">{{ $operator->available_quota }}</span></td>
                                <td><span class="badge bg-info">{{ number_format($operator->total_commissions, 2) }} ETB</span></td>
                                <td><span class="badge bg-warning">{{ number_format($operator->pending_commissions, 2) }} ETB</span></td>
                                <td><span class="badge bg-success">{{ number_format($operator->approved_commissions, 2) }} ETB</span></td>
                                <td>
                                    <a href="{{ route('manager.operators.files', $operator->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-file me-1"></i> View Files
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addFilesModal">
                                        <i class="bx bx-folder-plus me-0"></i> Add Files
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No operators assigned to you yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Files Modal -->
<div class="modal fade" id="addFilesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Files to Operator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addFilesForm" method="POST" action="">
                    @csrf
                    <label class="form-label">Enter the number of files (quota) to assign to this operator</label>
                    <input type="number" name="file_count" class="form-control" placeholder="Enter number of files" min="1" max="100" required>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addFilesForm" class="btn btn-success radius-30">Assign Files</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addFilesModal = document.getElementById('addFilesModal');
    if (addFilesModal) {
        addFilesModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const operatorId = button.closest('tr').getAttribute('data-operator-id');
            document.getElementById('addFilesForm').action = '{{ route("manager.operators.assign-files", ":id") }}'.replace(':id', operatorId);
        });
    }
});
</script>
@endsection
