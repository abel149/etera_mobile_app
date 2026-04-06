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

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Operator Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Total Quota</th>
                                <th>Used Files</th>
                                <th>Available</th>
                                <th>Processed Files</th>
                                <th>Total Assigned</th>
                                <th>Total Comsmissions</th>
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
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $operator->phone_number ?? 'N/A' }}</td>
                                <td>{{ $operator->email }}</td>
                                <td><span class="badge bg-primary">{{ $operator->total_quota }}</span></td>
                                <td><span class="badge bg-warning">{{ $operator->used_quota }}</span></td>
                                <td><span class="badge bg-success">{{ $operator->available_quota }}</span></td>
                                <td><span class="badge bg-info">{{ $operator->processed_files_count }}</span></td>
                                <td><span class="badge bg-secondary">{{ $operator->total_files_assigned }}</span></td>
                                <td><span class="badge bg-primary">{{ number_format($operator->total_commissions, 2) }} ETB</span></td>
                                <td>
                                    <a href="{{ route('manager.operators.files', $operator->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-file me-1"></i> View Files
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No operators assigned to you yet.</td>
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
