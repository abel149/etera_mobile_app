@extends('layouts.admin')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <h3 class="mb-3">Operator Management</h3>

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
                                <th>Manager</th>
                                <th>File Quota</th>
                                <th>Used/Available</th>
                                <th>Commission/File</th>
                                <th>Total Earned</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($operators as $operator)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0 font-14">{{ $operator->name }}</h6>
                                            <p class="mb-0 font-13 text-secondary">{{ $operator->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $operator->phone_number }}</td>
                                <td>
                                    @if($operator->myManager && $operator->myManager->manager)
                                        {{ $operator->myManager->manager->name }}
                                    @else
                                        <span class="badge bg-warning">Unassigned</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-primary">{{ $operator->total_quota }}</span></td>
                                <td>
                                    <span class="badge bg-secondary">{{ $operator->used_quota }}</span> / 
                                    <span class="badge bg-success">{{ $operator->available_quota }}</span>
                                </td>
                                <td><span class="badge bg-info">{{ number_format($operator->commission_per_file ?? 0, 2) }} ETB</span></td>
                                <td><span class="badge bg-success">{{ number_format($operator->total_commissions, 2) }} ETB</span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Assign Manager Button -->
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignManagerModal{{ $operator->id }}">
                                            <i class="bx bx-user-plus"></i> Assign
                                        </button>
                                        
                                        <!-- Set Quota Button -->
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#setQuotaModal{{ $operator->id }}">
                                            <i class="bx bx-file"></i> Quota
                                        </button>
                                        
                                        <!-- Set Commission Button -->
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#setCommissionModal{{ $operator->id }}">
                                            <i class="bx bx-money"></i> Commission
                                        </button>
                                    </div>

                                    <!-- Assign Manager Modal -->
                                    <div class="modal fade" id="assignManagerModal{{ $operator->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.operators.assign-manager', $operator->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Assign Manager to {{ $operator->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Select Manager</label>
                                                            <select name="manager_id" class="form-select" required>
                                                                <option value="">Choose a manager...</option>
                                                                @foreach($managers as $manager)
                                                                    <option value="{{ $manager->id }}" 
                                                                        {{ $operator->myManager && $operator->myManager->manager_id == $manager->id ? 'selected' : '' }}>
                                                                        {{ $manager->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Assign Manager</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Set Quota Modal -->
                                    <div class="modal fade" id="setQuotaModal{{ $operator->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.operators.set-quota', $operator->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Set File Quota for {{ $operator->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">File Quota</label>
                                                            <input type="number" name="file_quota" class="form-control" 
                                                                value="{{ $operator->file_quota ?? 0 }}" 
                                                                min="0" max="1000" required>
                                                            <small class="text-muted">Maximum number of files this operator can process</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-warning">Set Quota</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Set Commission Modal -->
                                    <div class="modal fade" id="setCommissionModal{{ $operator->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.operators.set-commission', $operator->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Set Commission for {{ $operator->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Commission Per File (ETB)</label>
                                                            <input type="number" name="commission_per_file" class="form-control" 
                                                                value="{{ $operator->commission_per_file ?? 0 }}" 
                                                                min="0" max="100000" step="0.01" required>
                                                            <small class="text-muted">Amount operator earns per processed file</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Set Commission</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No operators found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle AJAX form submissions for operators
document.querySelectorAll('[data-bs-target^="#assignManagerModal"], [data-bs-target^="#setQuotaModal"], [data-bs-target^="#setCommissionModal"]').forEach(btn => {
    const modalId = btn.dataset.bsTarget;
    const modal = document.querySelector(modalId);
    if (modal) {
        modal.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'An error occurred');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        });
    }
});
</script>
@endsection
