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
                                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                            <button type="button" class="btn btn-white">
                                                <i class="bx bx-filter"></i> Status
                                            </button>
                                            <div class="btn-group" role="group">
                                                <button id="btnGroupDrop1" type="button" class="btn btn-white dropdown-toggle dropdown-toggle-nocaret px-1" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class='bx bx-chevron-down'></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('filter', 'pending')">Pending</a></li>
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('filter', 'published')">Published</a></li>
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('filter', 'opened')">Opened</a></li>
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('filter', 'closed')">Closed</a></li>
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('filter', 'rejected')">Rejected</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
  <div class="col">
                                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                            <button type="button" class="btn btn-white">
                                                <i class="bx bx-filter"></i> Sort
                                            </button>
                                            <div class="btn-group" role="group">
                                                <button id="btnGroupDrop1" type="button" class="btn btn-white dropdown-toggle dropdown-toggle-nocaret px-1" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class='bx bx-chevron-down'></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('sortBy', 'asc')">Latest</a></li>
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="$set('sortBy', 'desc')">Oldest</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                            @if((auth()->user()->role == 'employee' || auth()->user()->role == 'operator') && $selectedProformas)
                            	<div class="col">
											            <div class="position-relative">
											              	<button wire:click.prevent="takeFiles" type="button" class="btn btn-primary radius-30 "><i class="bx bx-plus me-0"></i> Take </button>
                                  </div>
                              </div>
                            @endif
                                </div>
                            </form>
                            @if(auth()->user()->role == 'employee')
                            <div class="ms-auto">
                                <h5>Remaining Files: <span class="text-purple">{{$proformas->count() - count($selectedProformas)}}</span></h5>
                            </div>
                            @endif
                        </div>
                        <div class="table-responsive lead-table">
                            <table class="table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <input class="form-check-input" type="checkbox" wire:model.live="selectedProformas">
                                        </th>
                                        <th>File #</th>
                                        <th>Poster</th>
                                        <th>Customer</th>
                                        <th>Car</th>
                                        <th>License Plate</th>
                                        <th>Status</th>
                                        <th>
                                            Remaining Time
                                            <i class="bx bx-info-circle ms-1" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="Shows remaining time for Etera-Chereta proformas (HH:MM format)"></i>
                                        </th>
                                        <th>Created At</th>
                                        <th></th>
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
                                            <td>{{ $proforma->file_number }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{asset('assets/images/avatars/avatar-9.jpg')}}" class="rounded-circle" width="40" height="40" alt="">
                                                    <div class="ms-2">
                                                        <h6 class="mb-0 font-14">{{ $proforma->poster->name }} - {{ ucfirst($proforma->poster->role) }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 font-14">{{ $proforma->customer_name }}</h6>
                                                        <p class="mb-0 font-13 text-secondary">{{ $proforma->cusomer_phone_number }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 font-14">{{ $proforma->brand?->name }}</h6>
                                                        <p class="mb-0 font-13 text-secondary">{{ $proforma->model }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $proforma->license_plate_number }}</td>
                                            <td>
                                                <!--<div class="badge rounded-pill bg-{{ $proforma->status === 'pending' ? 'primary' : ($proforma->status === 'published' ? 'success' : 'danger') }} w-100">-->
                                                <!--    {{ $proforma->status === 'pending' && $proforma->selected() ? "File Assigned": ucfirst($proforma->status) }}-->
                                                <!--</div>-->
                                                @if($proforma->close_request && $proforma->status == 'published')
                                                <span class="badge bg-danger fw-bold">Close Requested</span>
                                                @elseif($proforma->status == 'completed')
               									<div class="badge rounded-pill bg-secondary w-100">{{ucfirst($proforma->status)}}</div>
               					@elseif($proforma->status == 'published')
                                <div class="badge rounded-pill bg-info w-100">{{ucfirst($proforma->status)}}</div>
                                @elseif($proforma->status == 'pending' || $proforma->status == 'opened')
                                <div class="badge rounded-pill bg-warning w-100">{{$proforma->selected() && $proforma->status == 'pending' ? "File Assigned" : ucfirst($proforma->status)}}</div>
                                @elseif($proforma->status == 'closed')
                                <div class="badge rounded-pill bg-danger w-100">{{ucfirst($proforma->status)}}</div>
                                @elseif($proforma->status == 'rejected')
                                <div class="badge rounded-pill bg-danger w-100">{{ucfirst($proforma->status)}}</div>
                                @endif
                                            </td>
                                            <td>
                                                @if($proforma->isEteraCheretaMode())
                                                    <span class="badge rounded-pill bg-primary w-100" 
                                                          data-remaining-time="{{ $proforma->timer_expires_at?->toISOString() }}">
                                                        {{ $proforma->getFormattedRemainingTime() }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ $proforma->created_at->format('d M Y') }}</td>
                                            @if(auth()->user()->role == 'admin')
                                            <td>
                                                <a href="{{ route('admin.proformas.details', $proforma->id) }}" class="btn">
                                                    <i class="bx bx-show me-0"></i>
                                                </a>
                                            </td>
                                            @endif
                                            @if(($proforma->status == 'pending' || $proforma->status == 'opened') && !$proforma?->selected() && auth()->user()->role == 'admin')
                                              <td>
                                                <a href="/float?proforma_id={{ $proforma->id }}" class="btn btn-primary">
                                                    Float
                                                </a>
                                            </td>
                                            @endif
                                     
                                            @if(($proforma->status == 'closed') && auth()->user()->role == 'admin')
                                            <td>
                                                <a href="/admin/verify/{{ $proforma->id }}" class="btn btn-primary">
                                                    Send To Owner
                                                </a>
                                            </td>
                                            @endif



                                            <td>
                                                @if(auth()->user()->role == 'admin' && $proforma->status !== 'closed' && $proforma->status !== 'completed')
                                                    @if($proforma->applications->isEmpty())
                                                        <!-- If there are no applications, disable the button -->
                                                    @else
                                                        <form action="{{ route('proforma.close', $proforma->id) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-primary btn-sm"
                                                                @if($proforma->status === 'pending' || $proforma->status === 'opened') hidden @endif>
                                                                Close
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="mx-auto">

                                            <td colspan="9 mx-auto text-bold">
                                              <div class="d-flex align-items-center mx-auto">
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
</div>
