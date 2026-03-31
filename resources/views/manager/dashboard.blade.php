@extends('layouts.manager')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <h3 class="mb-3">Manager Dashboard</h3>
        
        <!-- Statistics Row -->
        <div class="row">
            <div class="col-12 col-md-3 col-sm-6 col-lg-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-0">My Operators</p>
                                <h5 class="mb-0">{{ $operators->count() }}</h5>
                            </div>
                            <div class="fs-1 text-primary"><i class="bx bx-group"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3 col-sm-6 col-lg-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-0">Pending Review</p>
                                <h5 class="mb-0 text-warning">{{ $pendingFilesCount }}</h5>
                            </div>
                            <div class="fs-1 text-warning"><i class="bx bx-time-five"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3 col-sm-6 col-lg-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-0">Approved Files</p>
                                <h5 class="mb-0 text-success">{{ $approvedFilesCount }}</h5>
                            </div>
                            <div class="fs-1 text-success"><i class="bx bx-check-circle"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3 col-sm-6 col-lg-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-0">Rejected Files</p>
                                <h5 class="mb-0 text-danger">{{ $rejectedFilesCount }}</h5>
                            </div>
                            <div class="fs-1 text-danger"><i class="bx bx-x-circle"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Quick Actions</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('manager.review.pending') }}" class="btn btn-warning">
                                <i class="bx bx-list-check me-1"></i> Review Pending Files
                            </a>
                            <a href="{{ route('manager.operators') }}" class="btn btn-primary">
                                <i class="bx bx-group me-1"></i> View My Operators
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Operators Table -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">My Operators</h5>
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Operator Name</th>
                                        <th>Phone</th>
                                        <th>Total Quota</th>
                                        <th>Used Files</th>
                                        <th>Available</th>
                                        <th>Pending Review</th>
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
                                        <td><span class="badge bg-primary">{{ $operator->file_quota ?? 0 }}</span></td>
                                        <td>{{ $operator->proformaSelections()->where('active', true)->count() }}</td>
                                        <td><span class="badge bg-success">{{ $operator->getAvailableFileQuota() }}</span></td>
                                        <td>
                                            @php
                                                $pendingCount = \App\Models\Proforma::where('status', 'closed')
                                                    ->whereHas('selections', function($q) use ($operator) {
                                                        $q->where('employee_id', $operator->id)
                                                          ->where(function($subQ) {
                                                              $subQ->whereNull('review_status')
                                                                   ->orWhere('review_status', 'pending');
                                                          });
                                                    })
                                                    ->count();
                                            @endphp
                                            <span class="badge bg-warning">{{ $pendingCount }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('manager.operators.files', $operator->id) }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-file me-1"></i> View Files
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No operators assigned yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
