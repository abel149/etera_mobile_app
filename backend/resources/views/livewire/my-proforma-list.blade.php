<div class="page-wrapper">
    <div class="page-content">
        <h3>My Proforma List</h3>
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
                                        <th>File #</th>
                                        <th>Poster</th>
                                        <th>Customer</th>
                                        <th>Car</th>
                                        <th>License Plate</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($proformas as $selection)
                                        <tr>
                                            <td>{{ $selection->proforma->file_number }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{asset('assets/images/avatars/avatar-9.jpg')}}" class="rounded-circle" width="40" height="40" alt="">
                                                    <div class="ms-2">
                                                        <h6 class="mb-0 font-14">{{ $selection->proforma->poster->name }} - {{ ucfirst($selection->proforma->poster->role) }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 font-14">{{ $selection->proforma->customer_name }}</h6>
                                                        <p class="mb-0 font-13 text-secondary">{{ $selection->proforma->customer_phone_number }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 font-14">{{ $selection->proforma->brand->name }}</h6>
                                                        <p class="mb-0 font-13 text-secondary">{{ $selection->proforma->model }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $selection->proforma->license_plate_number }}</td>
                                            <td>
                                                <div class="badge rounded-pill bg-{{ $selection->proforma->status === 'pending' ? 'warning' : ($selection->proforma->status === 'published' ? 'info' : 'success') }} w-100">
                                                    {{ ucfirst($selection->proforma->status) }}
                                                </div>
                                                
                                            </td>
                                            <td>
                                                <a href="/employee/post-proforma?proforma_id={{ $selection->proforma->id }}" class="btn">
                                                    <i class="bx bx-show me-0"></i>
                                                </a>
                                            </td>
                                            
                                           @if((auth()->user()->role == 'employee' || auth()->user()->role == 'operator') && $selection->proforma->status == 'pending')
                                            <td>
                                                <a href="/float?proforma_id={{ $selection->proforma->id }}" class="btn btn-primary">
                                                    Float
                                                </a>
                                            </td>
                                            @endif
                                            @if($selection->proforma->selectedBy()?->active && $selection->proforma->selectedBy()?->employee_id == auth()->user()->id && ($selection->proforma->status == 'closed' || $selection->proforma->status == 'payment collected'))
                                             <td>
                                                <a href="/employee/change-status/{{ $selection->proforma->id }}" class="btn btn-primary">
                                                    {{auth()->user()->level->status_label}}
                                                </a>
                                            </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr class="mx-auto">

                                            <td colspan="8 mx-auto text-bold">
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
