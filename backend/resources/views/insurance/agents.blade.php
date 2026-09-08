@extends('layouts.insurance')
@section('content')

<div class="row">
    <div class="col-12 col-xl-8">

        <h3 class="mb-1">claim officer Accounts</h3>
        <p class="text-secondary mb-4">Create and manage claim officer logins under your company. Each claim officer has their own independent dashboard and data.</p>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bx bx-error-circle me-2"></i>{{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ── Add / Edit Claim Officer Card ─────────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bx bx-user-plus me-2 text-primary"></i>
                {{ isset($editAgent) ? 'Edit Claim Officer' : 'Add New Claim Officer' }}
            </div>
            <div class="card-body">
                @if(isset($editAgent))
                    <form method="POST" action="{{ route('insurance.agents.update', $editAgent) }}">
                        @csrf
                        @method('PUT')
                @else
                    <form method="POST" action="{{ route('insurance.agents.store') }}">
                        @csrf
                @endif

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Claim Officer's full name" autocomplete="off"
                                   value="{{ old('name', isset($editAgent) ? $editAgent->name : '') }}" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="number" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                                   placeholder="" autocomplete="off"
                                   value="{{ old('phone_number', isset($editAgent) ? $editAgent->phone_number : '') }}" required maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">
                                Password
                                @if(isset($editAgent))
                                    <span class="text-muted small">(leave blank to keep current)</span>
                                @else
                                    <span class="text-muted small">(default: 123456)</span>
                                @endif
                            </label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="{{ isset($editAgent) ? 'New password' : 'Min 6 characters' }}" minlength="6">
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary radius-30 px-4">
                            <i class="bx {{ isset($editAgent) ? 'bx-save' : 'bx-user-plus' }} me-2"></i>
                            {{ isset($editAgent) ? 'Save Changes' : 'Create Claim Officer' }}
                        </button>
                        @if(isset($editAgent))
                            <a href="{{ route('insurance.agents') }}" class="btn btn-outline-secondary radius-30 px-4">Cancel</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Claim Officers List ───────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
                <span><i class="bx bx-group me-2 text-primary"></i> My Claim Officers</span>
                <span class="badge bg-primary rounded-pill">{{ $agents->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($agents->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bx bx-user-x fs-1 d-block mb-2"></i>
                        <p class="mb-0">No claim officers yet. Add your first claim officer above.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agents as $i => $agent)
                                <tr class="{{ isset($editAgent) && $editAgent->id === $agent->id ? 'table-warning' : '' }}">
                                    <td class="text-secondary small">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                <span class="text-primary fw-bold small">{{ strtoupper(substr($agent->name, 0, 1)) }}</span>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $agent->name }}</div>
                                                <div class="text-muted small">Claim Officer</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $agent->phone_number }}</td>
                                    <td class="text-secondary small">{{ $agent->created_at->format('M d, Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('insurance.agents.edit', $agent) }}"
                                           class="btn btn-sm btn-outline-secondary me-1" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('insurance.agents.delete', $agent) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Delete claim officer {{ addslashes($agent->name) }}? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── Info sidebar ────────────────────────────────────────── --}}
    <div class="col-12 col-xl-4 mt-4 mt-xl-0">
        <div class="card border-0 bg-light">
            <div class="card-body">
                <h6 class="fw-semibold mb-2"><i class="bx bx-info-circle me-1 text-primary"></i> How Claim Officer Accounts Work</h6>
                <ul class="text-secondary small mb-0 ps-3">
                    <li class="mb-2">Each claim officer gets their own <strong>separate login</strong> with their own independent data.</li>
                    <li class="mb-2">Claim officers can create proformas, receive applications, and manage inboxes <strong>just like a normal insurance account</strong>.</li>
                    <li class="mb-2">Claim officers <strong>cannot</strong> see each other's proformas or manage claim officer accounts.</li>
                    <li class="mb-2">The default password is <code>123456</code> if none is set. Share login credentials privately with the claim officer.</li>
                    <li>Deleting a claim officer account is permanent and cannot be undone.</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 bg-light mt-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-2"><i class="bx bx-lock me-1 text-warning"></i> Note on Encryption</h6>
                <p class="text-secondary small mb-0">
                    Each claim officer sets up their own <strong>Encryption PIN</strong> independently.
                    Shop and garage prices sent to a claim officer are encrypted to that claim officer's key only.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
