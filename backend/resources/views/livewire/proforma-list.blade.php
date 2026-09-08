<div class="page-wrapper">
    <div class="page-content">
        <h3>Proforma List</h3>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="page-breadcrumb d-flex align-items-center mb-3">
                            <form wire:submit.prevent="search">
                                <div class="row row-cols-auto g-2">
                                    <div class="col">
                                        <div class="position-relative">
                                            <input type="text" wire:model.live="search" class="form-control ps-5" placeholder="Search Proformas...">
                                            <span class="position-absolute top-50 product-show translate-middle-y">
                                                <i class="bx bx-search"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-white">
                                                <i class="bx bx-filter"></i> Status
                                            </button>
                                            <div class="btn-group" role="group">
                                                <button id="statusDropdown" type="button" class="btn btn-white dropdown-toggle dropdown-toggle-nocaret px-1" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class='bx bx-chevron-down'></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="statusDropdown">
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('filter', 'pending')">New File</a></li>
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('filter', 'published')">Published</a></li>
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('filter', 'opened')">Opened</a></li>
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('filter', 'closed')">Closed</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-white">
                                                <i class="bx bx-filter"></i> Sort
                                            </button>
                                            <div class="btn-group" role="group">
                                                <button id="sortDropdown" type="button" class="btn btn-white dropdown-toggle dropdown-toggle-nocaret px-1" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class='bx bx-chevron-down'></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('sortBy', 'desc')">Latest</a></li>
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('sortBy', 'asc')">Oldest</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    @if((auth()->user()->role == 'employee' || auth()->user()->role == 'operator') && !empty($selectedProformas ?? []))
                                        <div class="col">
                                            <button wire:click.prevent="takeFiles" type="button" class="btn btn-primary radius-30">Take</button>
                                        </div>
                                    @endif
                                </div>
                            </form>
                            @if(auth()->user()->role == 'employee')
                                <div class="ms-auto">
                                    <h5>Remaining Files: <span class="text-purple">{{ $proformas->count() - count($selectedProformas) }}</span></h5>
                                </div>
                            @endif
                        </div>

                        <div class="table-responsive lead-table">
                            <table class="table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><input class="form-check-input" type="checkbox" wire:model.live="selectedProformas"></th>
                                        <th>File #</th>
                                        <th>Poster</th>
                                        <th>Customer</th>
                                        <th>Car</th>
                                        <th>License Plate</th>
                                        <th>Status</th>
                                        <th>Type</th>
                                        <th>Call</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($proformas as $proforma)
                                        @if(auth()->user()->role == 'employee' && ($proforma->selected() || $proforma->status != 'pending'))
                                            @continue
                                        @endif
                                        <tr>
                                            <td>
                                                <input class="form-check-input" type="checkbox" value="{{ $proforma->id }}" wire:model.live="selectedProformas">
                                            </td>
                                            <td>{{ $proforma->file_number ?? 'N/A' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ asset('assets/images/avatars/avatar-9.jpg') }}" class="rounded-circle" width="40" height="40" alt="">
                                                    <div class="ms-2">
                                                        <h6 class="mb-0 font-14">
                                                            {{ $proforma->poster?->name ?? 'Unknown' }}
                                                            @if($proforma->poster?->role === 'insurance_agent')
                                                                - {{ $proforma->poster?->parentInsurance?->name ?? 'Unknown Insurance' }} (Agent)
                                                            @else
                                                                - {{ ucfirst($proforma->poster?->role) }}
                                                            @endif
                                                        </h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 font-14">{{ $proforma->customer_name ?? 'N/A' }}</h6>
                                                        <p class="mb-0 font-13 text-secondary">{{ $proforma->customer_phone_number ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 font-14">{{ $proforma->brand?->name ?? 'N/A' }}</h6>
                                                        <p class="mb-0 font-13 text-secondary">{{ $proforma->model ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $proforma->license_plate_number ?? 'N/A' }}</td>
                                            <td>
                                                @if($proforma->status == 'completed')
                                                    <div class="badge rounded-pill bg-secondary w-100">{{ ucfirst($proforma->status) }}</div>
                                                @elseif($proforma->status == 'published')
                                                    <div class="badge rounded-pill {{ $proforma->close_request ? 'bg-danger' : 'bg-info' }} w-100">{{ $proforma->close_request ? 'Close Requested' : ucfirst($proforma->status) }}</div>
                                                @elseif($proforma->status == 'pending' || $proforma->status == 'opened')
                                                    <div class="badge rounded-pill {{ $proforma->close_request ? 'bg-danger' : 'bg-warning' }} w-100">{{ $proforma->close_request ? 'Close Requested' : ($proforma->selected() && $proforma->status == 'pending' ? 'File Assigned' : ucfirst($proforma->status)) }}</div>
                                                @elseif($proforma->status == 'closed')
                                                    <div class="badge rounded-pill bg-danger w-100">{{ ucfirst($proforma->status) }}</div>
                                                @elseif($proforma->status == 'rejected')
                                                    <div class="badge rounded-pill bg-secondary w-100">Rejected</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($proforma->insured)
                                                    <span class="badge rounded-pill bg-primary w-100"
                                                          data-remaining-time="{{ $proforma->timer_expires_at?->toISOString() }}">
                                                        Insured
                                                    </span>
                                                @else
                                                    <span class="text-muted">Not Insured</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($proforma->call_customer)
                                                    <span class="badge bg-warning text-dark"><i class="bx bx-phone-call me-1"></i>Call</span>
                                                @endif
                                            </td>
                                            <td>{{ $proforma->created_at?->format('d M Y') ?? 'N/A' }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    @if(auth()->user()->role === 'insurance')
                                                        @php
                                                            $myApplicationsCount = $proforma->applications()->count();
                                                        @endphp
                                                        @if(in_array($proforma->status, ['published','pending','opened']) && !$proforma->close_request && $myApplicationsCount > 0)
                                                            <form action="{{ route('insurance.proforma.request-close', ['proforma' => $proforma->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-primary btn-sm">Request Close Proforma</button>
                                                            </form>
                                                        @elseif($proforma->close_request && in_array($proforma->status, ['published','pending','opened']))
                                                            <span class="fw-bold">Close Requested</span>
                                                        @endif
                                                    @elseif(auth()->user()->role === 'operator')
                                                        <a href="{{ route('operator.proforma.show', $proforma->id) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bx bx-show me-0"></i>
                                                        </a>
                                                    @elseif(auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')
                                                        <a href="/admin/post-proforma?proforma_id={{ $proforma->id }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bx bx-show me-0"></i>
                                                        </a>
                                                        @if($proforma->status === 'pending')
                                                        <button type="button"
                                                                class="btn btn-sm btn-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#rejectModal{{ $proforma->id }}"
                                                                title="Reject this proforma">
                                                            Reject
                                                        </button>

                                                        <div class="modal fade" id="rejectModal{{ $proforma->id }}" tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered">
                                                                <div class="modal-content">
                                                                    <form action="{{ route('proformas.reject', $proforma->id) }}" method="POST">
                                                                        @csrf
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title text-danger">Reject Proforma #{{ $proforma->file_number }}</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <p>To confirm rejection, type <strong>reject</strong> below:</p>
                                                                            <input type="text" name="confirmation" class="form-control" placeholder="Type 'reject' here" required autocomplete="off">
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                            <button type="submit" class="btn btn-danger">Reject</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                        @php
                                                            $reqShops   = (int)($proforma->required_number_of_shops ?? 0);
                                                            $reqGarages = (int)($proforma->required_number_of_garages ?? 0);
                                                            // Hide float only when all public float slots are taken by admin inboxes.
                                                            // Insurance partner inboxes (source='insurance') must NOT count here.
                                                            $allSlotsInboxed = !$proforma->isEteraCheretaMode()
                                                                && ($reqShops === 0   || $proforma->floatShopQuota()   <= 0)
                                                                && ($reqGarages === 0 || $proforma->floatGarageQuota() <= 0)
                                                                && ($reqShops > 0 || $reqGarages > 0);
                                                        @endphp
                                                        @if(($proforma->status === 'pending' || $proforma->status === 'opened') && !$proforma?->selected() && !$allSlotsInboxed)
                                                            <a href="/float?proforma_id={{ $proforma->id }}" class="btn btn-sm btn-primary">Float</a>
                                                        @endif
                                                        @if($proforma->status === 'closed')
                                                            <a href="/admin/verify/{{ $proforma->id }}" class="btn btn-sm btn-primary">Send To Owner</a>
                                                        @endif
                                                        @if($proforma->status !== 'closed' && $proforma->status !== 'completed')
                                                            @if(!$proforma->applications->isEmpty())
                                                                <form action="{{ route('admin.proforma.close', $proforma->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="btn btn-primary btn-sm"
                                                                        @if(!$proforma->close_request && ($proforma->status === 'pending' || $proforma->status === 'opened') && !$allSlotsInboxed) hidden @endif>
                                                                        Close
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="mx-auto text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div>
                                                        <h6 class="mb-0 font-14">No Proformas found</h6>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $proformas->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(auth()->user()->role == 'superadmin')
        <!-- Logs Modal -->
        @if($showLogsModal)
            <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Activity Logs for Proforma #{{ $selectedProformaId }}</h5>
                            <button type="button" class="btn-close" wire:click="closeLogsModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Action</th>
                                            <th>User</th>
                                            <th>Details</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activityLogs as $log)
                                            <tr>
                                                <td><span class="badge bg-secondary">{{ ucfirst($log->action) }}</span></td>
                                                <td>{{ $log->user->name ?? 'System' }} ({{ $log->user->role ?? 'N/A' }})</td>
                                                <td>{{ $log->details }}</td>
                                                <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">No activity logs found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeLogsModal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>