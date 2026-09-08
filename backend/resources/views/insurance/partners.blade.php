@extends('layouts.insurance')
@section('content')

@php
    $partners   = auth()->user()->partners;
    $partnerIds = $partners ? $partners->pluck('partner_id') : collect();

    $availableShopPartners = \App\Models\User::where('role', 'shop')
        ->whereNotIn('id', $partnerIds)
        ->where('is_test', 0)
        ->orderBy('name', 'asc')
        ->get();

    $availableGaragePartners = \App\Models\User::where('role', 'garage')
        ->whereNotIn('id', $partnerIds)
        ->where('is_test', 0)
        ->orderBy('name', 'asc')
        ->get();

    $sortedPartners = $partners
        ? $partners
            ->filter(fn($p) => $p->partner && !$p->partner->is_test)
            ->sortBy(fn($p) => strtolower($p->partner->name ?? ''))
            ->values()
        : collect();
@endphp

<h3 class="mb-3">Partners List</h3>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                {{-- Toolbar --}}
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4 me-4">
                    <div class="position-relative flex-shrink-0" style="width:280px; max-width:100%;">
                        <input type="text" id="partnerSearch" class="form-control ps-5 radius-30" placeholder="Search partners...">
                        <span class="position-absolute top-50 translate-middle-y ps-3" style="left:0; pointer-events:none;">
                            <i class="bx bx-search text-secondary"></i>
                        </span>
                    </div>
                    <button type="button" class="btn btn-primary radius-30 flex-shrink-0" data-bs-toggle="modal" data-bs-target="#addShopModal">
                        <i class="bx bx-store me-1"></i> Add Shop Partner
                    </button>
                    <button type="button" class="btn btn-primary radius-30 flex-shrink-0" data-bs-toggle="modal" data-bs-target="#addGarageModal">
                        <i class="bx bx-wrench me-1"></i> Add Garage Partner
                    </button>
                </div>

                {{-- Partners Table --}}
                <div class="table-responsive">
                    <table class="table mb-0 align-middle" id="partnersTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Store Number</th>
                                <th>Role</th>
                                <th>TIN #</th>
                                <th>Phone Number</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sortedPartners as $index => $partner)
                            <tr>
                                <td class="text-secondary">{{ $index + 1 }}</td>
                                <td>
                                    <h6 class="mb-0 font-14">{{ $partner->partner->name }}</h6>
                                    <p class="mb-0 font-13 text-secondary">{{ $partner->partner->email }}</p>
                                </td>
                                <td class="fw-semibold">{{ $partner->partner->store_id }}</td>
                                <td>
                                    @if($partner->partner->role === 'shop')
                                        <span class="badge bg-primary text-white px-2 py-1">Shop</span>
                                    @elseif($partner->partner->role === 'garage')
                                        <span class="badge bg-success text-white px-2 py-1">Garage</span>
                                    @else
                                        <span class="badge bg-secondary text-white px-2 py-1">{{ ucfirst($partner->partner->role) }}</span>
                                    @endif
                                </td>
                                <td>{{ $partner->partner->tin_number ?? '—' }}</td>
                                <td>{{ $partner->partner->phone_number }}</td>
                                <td>
                                    <form action="partners/{{ $partner->id }}" method="POST"
                                          onsubmit="return confirm('Remove {{ addslashes($partner->partner->name) }} from your partners?')">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger radius-10">
                                            <i class="bx bx-trash me-1"></i>Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr id="emptyRow">
                                <td colspan="7" class="text-center text-secondary py-4">
                                    <i class="bx bx-group fs-4 d-block mb-1 text-secondary opacity-50"></i>
                                    No partners added yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="noSearchResults" class="text-center text-secondary py-4" style="display:none;">
                    <i class="bx bx-search-alt fs-4 d-block mb-1 opacity-50"></i>
                    No partners match your search.
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ===================== Add Shop Partner Modal ===================== --}}
<div class="modal fade" id="addShopModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-store me-2 text-primary"></i>Add Shop Partners</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('partners.add') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if($availableShopPartners->isEmpty())
                        <div class="text-center text-secondary py-3">
                            <i class="bx bx-check-circle fs-3 d-block mb-2 text-success opacity-75"></i>
                            All available shop partners have already been added.
                        </div>
                    @else
                        <div class="mb-3">
                            <div class="position-relative">
                                <input type="text" class="form-control ps-5" id="shopSearch"
                                       placeholder="Search shops..."
                                       oninput="filterOptions('shopList', this.value)">
                                <span class="position-absolute top-50 translate-middle-y ps-3" style="left:0; pointer-events:none;">
                                    <i class="bx bx-search text-secondary"></i>
                                </span>
                            </div>
                        </div>
                        <p class="text-secondary small mb-2">Hold <kbd>Ctrl</kbd> / <kbd>Cmd</kbd> to select multiple.</p>
                        <select class="form-select" name="partners[]" id="shopList"
                                multiple style="min-height:200px; max-height:320px; overflow-y:auto;">
                            @foreach($availableShopPartners as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} — {{ $p->store_id }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
                    @if(!$availableShopPartners->isEmpty())
                        <button type="submit" class="btn btn-primary radius-30">Add Selected</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== Add Garage Partner Modal ===================== --}}
<div class="modal fade" id="addGarageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-wrench me-2 text-success"></i>Add Garage Partners</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('partners.add') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if($availableGaragePartners->isEmpty())
                        <div class="text-center text-secondary py-3">
                            <i class="bx bx-check-circle fs-3 d-block mb-2 text-success opacity-75"></i>
                            All available garage partners have already been added.
                        </div>
                    @else
                        <div class="mb-3">
                            <div class="position-relative">
                                <input type="text" class="form-control ps-5" id="garageSearch"
                                       placeholder="Search garages..."
                                       oninput="filterOptions('garageList', this.value)">
                                <span class="position-absolute top-50 translate-middle-y ps-3" style="left:0; pointer-events:none;">
                                    <i class="bx bx-search text-secondary"></i>
                                </span>
                            </div>
                        </div>
                        <p class="text-secondary small mb-2">Hold <kbd>Ctrl</kbd> / <kbd>Cmd</kbd> to select multiple.</p>
                        <select class="form-select" name="partners[]" id="garageList"
                                multiple style="min-height:200px; max-height:320px; overflow-y:auto;">
                            @foreach($availableGaragePartners as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} — {{ $p->store_id }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
                    @if(!$availableGaragePartners->isEmpty())
                        <button type="submit" class="btn btn-outline-primary radius-30">Add Selected</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── Live search on the partners table ──────────────────────────────────────
document.getElementById('partnerSearch').addEventListener('input', function () {
    const query = this.value.toLowerCase().trim();
    const rows  = document.querySelectorAll('#partnersTable tbody tr:not(#emptyRow)');
    let visible = 0;

    rows.forEach(row => {
        const match = row.textContent.toLowerCase().includes(query);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    document.getElementById('noSearchResults').style.display =
        (visible === 0 && query !== '') ? 'block' : 'none';
});

// ── Filter options inside a modal select ──────────────────────────────────
function filterOptions(selectId, query) {
    const select = document.getElementById(selectId);
    if (!select) return;
    const lq = query.toLowerCase();
    Array.from(select.options).forEach(opt => {
        opt.style.display = opt.text.toLowerCase().includes(lq) ? '' : 'none';
    });
}

// ── Reset modal state on close ────────────────────────────────────────────
['addShopModal', 'addGarageModal'].forEach(id => {
    document.getElementById(id).addEventListener('hidden.bs.modal', function () {
        const input  = this.querySelector('input[type="text"]');
        const select = this.querySelector('select');
        if (input)  input.value = '';
        if (select) {
            Array.from(select.options).forEach(o => o.style.display = '');
            select.selectedIndex = -1;
        }
    });
});
</script>

@endsection
