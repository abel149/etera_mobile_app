@extends('layouts.operator')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <h3 class="mb-3">Operator Dashboard</h3>
        
        <!-- Statistics Row -->
        <div class="row">
            <div class="col-12 col-md-3 col-sm-6 col-lg-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-0">Total Quota</p>
                                <h5 class="mb-0">{{ $stats['total_quota'] }}</h5>
                            </div>
                            <div class="fs-1 text-primary"><i class="bx bx-file"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3 col-sm-6 col-lg-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-0">Used Quota</p>
                                <h5 class="mb-0 text-warning">{{ $stats['used_quota'] }}</h5>
                            </div>
                            <div class="fs-1 text-warning"><i class="bx bx-folder"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3 col-sm-6 col-lg-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-0">Available Files</p>
                                <h5 class="mb-0 text-success">{{ $stats['available_quota'] }}</h5>
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
                                <p class="mb-0">Total Earned</p>
                                <h5 class="mb-0 text-success">{{ number_format($stats['total_commissions'], 2) }} ETB</h5>
                            </div>
                            <div class="fs-1 text-success"><i class="bx bx-money"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commission Overview -->
        <div class="row mt-3">
            <div class="col-12 col-md-6">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6>Pending Commissions</h6>
                            <span class="badge bg-warning">Under Review</span>
                        </div>
                        <h4 class="mb-0">{{ number_format($stats['pending_commissions'], 2) }} ETB</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6>Approved Commissions</h6>
                            <span class="badge bg-success">Ready for Payment</span>
                        </div>
                        <h4 class="mb-0">{{ number_format($stats['approved_commissions'], 2) }} ETB</h4>
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
                            <a href="{{ route('operator.commissions') }}" class="btn btn-primary">
                                <i class="bx bx-money me-1"></i> View Commission History
                            </a>
                            <a href="{{ route('operator.balance') }}" class="btn btn-success">
                                <i class="bx bx-wallet me-1"></i> View Balance
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Files -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Recent Files</h5>
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Proforma #</th>
                                        <th>Customer</th>
                                        <th>Commission</th>
                                        <th>Closed At</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentFiles as $file)
                                    <tr>
                                        <td>{{ $file->proforma->file_number ?? 'N/A' }}</td>
                                        <td>{{ $file->proforma->customer_name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-primary">{{ number_format($file->commission_earned, 2) }} ETB</span></td>
                                        <td>{{ $file->closed_at?->format('M d, Y h:i A') ?? 'Active' }}</td>
                                        <td>
                                            @if($file->active)
                                                <span class="badge bg-info">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Completed</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No files processed yet.</td>
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
