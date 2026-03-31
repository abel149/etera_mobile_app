@extends('layouts.admin')

@section('title', 'Sent Emails')

@section('content')
<div class="page-wrapper">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Sent Emails</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Sent Emails</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="row mb-3">
            <div class="col-md-3 col-6">
                <div class="card radius-10 border-top border-3 border-primary shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="mb-1">{{ $stats['total'] }}</h5>
                        <p class="mb-0 text-muted">Total Emails</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card radius-10 border-top border-3 border-success shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="mb-1 text-success">{{ $stats['sent'] }}</h5>
                        <p class="mb-0 text-muted">Sent</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card radius-10 border-top border-3 border-danger shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="mb-1 text-danger">{{ $stats['failed'] }}</h5>
                        <p class="mb-0 text-muted">Failed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card radius-10 border-top border-3 border-info shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="mb-1 text-info">{{ $stats['today'] }}</h5>
                        <p class="mb-0 text-muted">Today</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card radius-10 shadow-sm mb-3">
            <div class="card-body py-2">
                <form action="{{ route('admin.sent-emails') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="proforma_floated" {{ request('type') == 'proforma_floated' ? 'selected' : '' }}>Proforma Floated</option>
                            <option value="proforma_created" {{ request('type') == 'proforma_created' ? 'selected' : '' }}>Proforma Created</option>
                            <option value="proforma_verified" {{ request('type') == 'proforma_verified' ? 'selected' : '' }}>Proforma Verified</option>
                            <option value="proforma_closed_billing" {{ request('type') == 'proforma_closed_billing' ? 'selected' : '' }}>Closed (Billing)</option>
                            <option value="proforma_completed" {{ request('type') == 'proforma_completed' ? 'selected' : '' }}>Completed (Invoice)</option>
                            <option value="sent_to_owner" {{ request('type') == 'sent_to_owner' ? 'selected' : '' }}>Sent to Owner</option>
                            <option value="application_received" {{ request('type') == 'application_received' ? 'selected' : '' }}>Application Received</option>
                            <option value="application_submitted" {{ request('type') == 'application_submitted' ? 'selected' : '' }}>Application Submitted</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by email, name, or proforma #...">
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        @if(request()->hasAny(['type', 'status', 'search']))
                            <a href="{{ route('admin.sent-emails') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card radius-10 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>To</th>
                                <th>Proforma</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($emails as $email)
                            <tr>
                                <td>{{ $email->id }}</td>
                                <td>
                                    @php
                                        $typeLabels = [
                                            'proforma_floated' => ['label' => 'Proforma Floated', 'class' => 'bg-primary'],
                                            'proforma_created' => ['label' => 'Proforma Created', 'class' => 'bg-success'],
                                            'proforma_verified' => ['label' => 'Verified', 'class' => 'bg-warning text-dark'],
                                            'proforma_closed_billing' => ['label' => 'Closed (Billing)', 'class' => 'bg-secondary'],
                                            'proforma_completed' => ['label' => 'Completed', 'class' => 'bg-success'],
                                            'sent_to_owner' => ['label' => 'Sent to Owner', 'class' => 'bg-dark'],
                                            'application_received' => ['label' => 'App. Received', 'class' => 'bg-info'],
                                            'application_submitted' => ['label' => 'App. Submitted', 'class' => 'bg-secondary'],
                                            'password_reset' => ['label' => 'Password Reset', 'class' => 'bg-warning text-dark'],
                                            'email_otp' => ['label' => 'Email OTP', 'class' => 'bg-info'],
                                        ];
                                        $t = $typeLabels[$email->type] ?? ['label' => ucfirst(str_replace('_', ' ', $email->type)), 'class' => 'bg-dark'];
                                    @endphp
                                    <span class="badge {{ $t['class'] }}">{{ $t['label'] }}</span>
                                </td>
                                <td>
                                    <div>{{ $email->to_name ?? '-' }}</div>
                                    <small class="text-muted">{{ $email->to_email }}</small>
                                </td>
                                <td>
                                    @if($email->proforma)
                                        <span class="badge bg-light text-dark border">#{{ $email->proforma->file_number }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><small>{{ Str::limit($email->subject, 40) }}</small></td>
                                <td>
                                    @if($email->status === 'sent')
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Sent</span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Failed</span>
                                    @endif
                                </td>
                                <td><small>{{ $email->created_at->format('M d, H:i') }}</small></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#emailModal{{ $email->id }}">
                                        <i class="bx bx-show"></i> View
                                    </button>
                                    @if($email->error_message)
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" 
                                                data-bs-toggle="tooltip" data-bs-placement="left"
                                                title="{{ Str::limit($email->error_message, 200) }}">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No sent emails found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $emails->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Email Body Modals --}}
@foreach($emails as $email)
<div class="modal fade" id="emailModal{{ $email->id }}" tabindex="-1" aria-labelledby="emailModalLabel{{ $email->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="emailModalLabel{{ $email->id }}">
                        {{ $email->subject ?? 'Email Details' }}
                    </h5>
                    <small class="text-muted">
                        To: {{ $email->to_name }} &lt;{{ $email->to_email }}&gt; &bull; 
                        {{ $email->created_at->format('M d, Y H:i:s') }}
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                @if($email->body)
                    <iframe srcdoc="{{ htmlspecialchars($email->body) }}" 
                            style="width: 100%; height: 500px; border: none;"
                            sandbox="allow-same-origin"></iframe>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="bx bx-envelope bx-lg d-block mb-2"></i>
                        <p>Email body not available.</p>
                        <small>Older emails may not have the body saved. New emails will include the full content.</small>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                @php
                    $statusBadge = $email->status === 'sent' 
                        ? '<span class="badge bg-success">Sent</span>' 
                        : '<span class="badge bg-danger">Failed</span>';
                    $typeBadge = $typeLabels[$email->type] ?? ['label' => ucfirst(str_replace('_', ' ', $email->type)), 'class' => 'bg-dark'];
                @endphp
                <span class="badge {{ $typeBadge['class'] }}">{{ $typeBadge['label'] }}</span>
                {!! $statusBadge !!}
                <button type="button" class="btn btn-secondary ms-auto" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    // Initialize tooltips for error messages
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });
</script>
@endpush
@endsection
