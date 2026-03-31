@extends('layouts.admin')

@section('title', 'User Approvals')

@section('content')
<div class="page-wrapper">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">User Approvals</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Approvals</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- SECTION 1: PENDING / UNAPPROVED USERS -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-top border-3 border-warning shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-warning">
                            <i class="fas fa-hourglass-half me-2"></i> Pending Approvals
                        </h5>
                        <span class="badge bg-warning text-dark">{{ $unapprovedUsers->total() }} Pending</span>
                    </div>
                    <div class="card-body">
                        
                        <!-- Filter & Search for Pending -->
                        <form action="{{ route('admin.user-approval.index') }}" method="GET" class="mb-3">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    {{-- JS Auto-Submit on Change --}}
                                    <select name="role_unapproved" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Roles</option>
                                        <option value="business_owner" {{ request('role_unapproved') == 'business_owner' ? 'selected' : '' }}>Business Owner</option>
                                        <option value="insurance" {{ request('role_unapproved') == 'insurance' ? 'selected' : '' }}>Insurance</option>
                                        <option value="shop" {{ request('role_unapproved') == 'shop' ? 'selected' : '' }}>Shop</option>
                                        <option value="garage" {{ request('role_unapproved') == 'garage' ? 'selected' : '' }}>Garage</option>
                                        <option value="employee" {{ request('role_unapproved') == 'employee' ? 'selected' : '' }}>Employee</option>
                                        <option value="marketer" {{ request('role_unapproved') == 'marketer' ? 'selected' : '' }}>Marketer</option>
                                        <option value="individual" {{ request('role_unapproved') == 'individual' ? 'selected' : '' }}>Individual</option>
                                        <option value="admin" {{ request('role_unapproved') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                        <input type="text" name="search_unapproved" class="form-control" value="{{ request('search_unapproved') }}" placeholder="Search Pending (Name, Email, Phone)... press Enter">
                                    </div>
                                </div>
                            </div>
                            {{-- Preserve Approved table state --}}
                            @if(request('search_approved')) <input type="hidden" name="search_approved" value="{{ request('search_approved') }}"> @endif
                            @if(request('role_approved')) <input type="hidden" name="role_approved" value="{{ request('role_approved') }}"> @endif
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($unapprovedUsers as $user)
                                        @if($user->id !== auth()->id())
                                        <tr>
                                            <td class="fw-bold">
                                                {{ $user->name }}
                                                <div class="small text-muted">{{ $user->store_id ?? '' }}</div>
                                            </td>
                                            <td>{{ $user->phone_number ?? '-' }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span></td>
                                            <td>{!! $user->getApprovalStatusBadge() !!}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#userModal-{{ $user->id }}">
                                                    <i class="fas fa-eye me-1"></i> View Details
                                                </button>
                                            </td>
                                        </tr>
                                        @endif
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-3">No pending approvals found matching your filters.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $unapprovedUsers->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: APPROVED USERS -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-3 border-success shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-success">
                            <i class="fas fa-check-circle me-2"></i> Approved History
                        </h5>
                        <span class="badge bg-success">{{ $approvedUsers->total() }} Approved</span>
                    </div>
                    <div class="card-body">

                        <!-- Filter & Search for Approved -->
                        <form action="{{ route('admin.user-approval.index') }}" method="GET" class="mb-3">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    {{-- JS Auto-Submit on Change --}}
                                    <select name="role_approved" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Roles</option>
                                        <option value="business_owner" {{ request('role_approved') == 'business_owner' ? 'selected' : '' }}>Business Owner</option>
                                        <option value="insurance" {{ request('role_approved') == 'insurance' ? 'selected' : '' }}>Insurance</option>
                                        <option value="shop" {{ request('role_approved') == 'shop' ? 'selected' : '' }}>Shop</option>
                                        <option value="garage" {{ request('role_approved') == 'garage' ? 'selected' : '' }}>Garage</option>
                                        <option value="employee" {{ request('role_approved') == 'employee' ? 'selected' : '' }}>Employee</option>
                                        <option value="marketer" {{ request('role_approved') == 'marketer' ? 'selected' : '' }}>Marketer</option>
                                        <option value="individual" {{ request('role_approved') == 'individual' ? 'selected' : '' }}>Individual</option>
                                        <option value="admin" {{ request('role_approved') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                        <input type="text" name="search_approved" class="form-control" value="{{ request('search_approved') }}" placeholder="Search Approved (Name, Email, Phone)... press Enter">
                                    </div>
                                </div>
                            </div>
                            {{-- Preserve Pending table state --}}
                            @if(request('search_unapproved')) <input type="hidden" name="search_unapproved" value="{{ request('search_unapproved') }}"> @endif
                            @if(request('role_unapproved')) <input type="hidden" name="role_unapproved" value="{{ request('role_unapproved') }}"> @endif
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Approved Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($approvedUsers as $user)
                                        @if($user->id !== auth()->id())
                                        <tr>
                                            <td class="fw-bold">
                                                {{ $user->name }}
                                                <div class="small text-muted">{{ $user->store_id ?? '' }}</div>
                                            </td>
                                            <td>{{ $user->phone_number ?? '-' }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td><span class="badge bg-info text-dark">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span></td>
                                            <td>{{ $user->approved_at ? $user->approved_at->format('Y-m-d') : 'N/A' }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#userModal-{{ $user->id }}">
                                                    <i class="fas fa-eye me-1"></i> View Details
                                                </button>
                                            </td>
                                        </tr>
                                        @endif
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-3">No approved users found matching your filters.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $approvedUsers->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODALS SECTION --}}
@php
    $allUsersForModals = $unapprovedUsers->concat($approvedUsers);
@endphp

@foreach($allUsersForModals as $user)
    @if($user->id !== auth()->id())
    <div class="modal fade" id="userModal-{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">User Details: {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <h6 class="text-uppercase text-muted border-bottom pb-2">Personal Info</h6>
                                <p class="mb-1"><strong>Name:</strong> {{ $user->name }}</p>
                                <p class="mb-1"><strong>Email:</strong> {{ $user->email }}</p>
                                <p class="mb-1"><strong>Phone:</strong> {{ $user->phone_number ?? '-' }}</p>
                                <p class="mb-1"><strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
                                <p class="mb-0"><strong>Location:</strong> {{ $user->location ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <h6 class="text-uppercase text-muted border-bottom pb-2">Business Details</h6>
                                <p class="mb-1"><strong>TIN:</strong> {{ $user->tin_number ?? '-' }}</p>
                                <p class="mb-1"><strong>License:</strong> {{ $user->business_license_number ?? '-' }}</p>
                                <p class="mb-1"><strong>Expires:</strong> {{ $user->license_expire_date ? $user->license_expire_date->format('d M Y') : '-' }}</p>
                                <p class="mb-1"><strong>Brands:</strong> {{ $user->brands->pluck('name')->implode(', ') ?: 'None' }}</p>
                                <p class="mb-0"><strong>Status:</strong> {!! $user->getApprovalStatusBadge() !!}</p>
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <small class="d-block text-muted mb-2">License Image</small>
                                    @if($user->license_image)
                                        <a href="{{ asset('storage/' . $user->license_image) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $user->license_image) }}" class="img-thumbnail" style="max-height: 120px;">
                                        </a>
                                    @else
                                        <span class="badge bg-secondary">No File</span>
                                    @endif
                                </div>
                                <div class="col-6 text-center">
                                    <small class="d-block text-muted mb-2">Stamp Image</small>
                                    @if($user->stamp_image)
                                        <a href="{{ asset('storage/' . $user->stamp_image) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $user->stamp_image) }}" class="img-thumbnail" style="max-height: 120px;">
                                        </a>
                                    @else
                                        <span class="badge bg-secondary">No File</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="p-3 border rounded bg-light">
                                <h6 class="text-uppercase text-muted border-bottom pb-2">Bank Accounts</h6>
                                @if($user->bankAccounts->isEmpty())
                                <span class="badge bg-secondary">No Bank Accounts</span>
                                @else
                                <ul class="list-group list-group-flush">
                                    @foreach($user->bankAccounts as $account)
                                    <li class="list-group-item">
                                        <strong>{{ $account->bank_name }}:</strong> {{ $account->account_number }}
                                        </li>
                                        @endforeach
                                </ul>
                                @endif
                            </div>
                        </div>

                        
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <div>
                        @if(auth()->user()->canApproveUsers())
                            
                            @if(!$user->approved_at)
                                {{-- UNAPPROVED USER: Show Text Button --}}
                                <form action="{{ route('admin.user-approval.approve', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm Approval for {{ $user->name }}?');">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        Set to Approved
                                    </button>
                                </form>
                            @else
                                {{-- APPROVED USER: Show Text Button --}}
                                <button type="button" class="btn btn-danger" 
                                        data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $user->id }}">
                                    Set to Not Approved
                                </button>
                            @endif

                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectModal-{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.user-approval.reject', $user->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Rejecting: <strong>{{ $user->name }}</strong></p>
                        <div class="mb-3">
                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#userModal-{{ $user->id }}">Back</button>
                        <button type="submit" class="btn btn-danger">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection