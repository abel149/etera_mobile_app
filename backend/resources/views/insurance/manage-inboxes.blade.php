@extends('layouts.insurance')
@section('content')

@php
    // ── Dynamic group counts based on required slots ─────────────────────
    $shopGroupCount   = max(0, (int)($proforma->required_number_of_shops ?? 3));
    $garageGroupCount = max(0, (int)($proforma->required_number_of_garages ?? 3));
    // Type-based override: lock the irrelevant side completely
    if ($proforma->isShopOnlyInsurance())   $garageGroupCount = 0;
    if ($proforma->isGarageOnlyInsurance()) $shopGroupCount   = 0;

    // ── Per-group current inbox user IDs (covers up to 5 groups) ──────────
    $shopGrp   = [];
    $garageGrp = [];
    foreach (range(1, 5) as $g) {
        $shopGrp[$g]   = $shopInboxes->get($g, collect())->map(fn($i) => $i->user->id ?? null)->filter()->values()->toArray();
        $garageGrp[$g] = $garageInboxes->get($g, collect())->map(fn($i) => $i->user->id ?? null)->filter()->values()->toArray();
    }

    // ── Global editable flag (proforma must be pending) ───────────────────────
    $proformaPending = $proforma->status === 'pending';

    $partnerIds      = $spare_part_partners->pluck('id')->toArray();
    $garagePartnerIds= $garage_partners->pluck('id')->toArray();

    // ── Per-group applied status: which inbox groups were satisfied by a partner application
    $shopAppliedGroups   = $shopApplications
        ->where('application_source', 'partner')
        ->pluck('inbox_group')->filter()->unique()->values()->toArray();
    $garageAppliedGroups = $garageApplications
        ->where('application_source', 'partner')
        ->pluck('inbox_group')->filter()->unique()->values()->toArray();

    // Returns [locked, badgeClass, badgeText] for a group
    $groupStatus = function(array $ids, bool $proformaPending, array $appliedGroups, int $grp) {
        if (!$proformaPending) {
            return [true, 'bg-secondary', 'Proforma Closed'];
        }
        if (empty($ids)) {
            if (in_array($grp, $appliedGroups)) {
                return [true, 'bg-success', 'Applied ✓'];
            }
            return [true, 'bg-warning text-dark', 'Admin Slot'];
        }
        return [false, '', ''];
    };
@endphp

{{-- Pre-hide raw selects so the browser never paints them before Select2 initialises --}}
<style>
select.js-select2-shop,
select.js-select2-garage { display: none; }
</style>

<div class="container-fluid py-4">

    {{-- Back + Title --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ url('/insurance') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back
        </a>
        <h4 class="mb-0">Manage Inboxes — File #{{ $proforma->file_number }}</h4>
        <span class="badge rounded-pill
            {{ $proforma->status === 'pending' ? 'bg-warning text-dark' : ($proforma->status === 'completed' ? 'bg-success' : 'bg-secondary') }}">
            {{ ucfirst($proforma->status) }}
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!$proformaPending)
        <div class="alert alert-secondary d-flex align-items-center gap-2">
            <i class="bx bx-lock-alt fs-5"></i>
            <span>This proforma is <strong>{{ $proforma->status }}</strong> — all inbox slots are locked.</span>
        </div>
    @endif

    {{-- Proforma Summary --}}
    <div class="card radius-10 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-sm-4">
                    <small class="text-muted d-block">Customer</small>
                    <strong>{{ $proforma->customer_name }}</strong>
                </div>
                <div class="col-sm-4">
                    <small class="text-muted d-block">Car</small>
                    <strong>{{ $proforma->brand?->name }} {{ $proforma->model }} {{ $proforma->year }}</strong>
                </div>
                <div class="col-sm-4">
                    <small class="text-muted d-block">License Plate</small>
                    <strong>{{ $proforma->license_plate_number }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Applied users (info) --}}
    @if($shopApplications->isNotEmpty() || $garageApplications->isNotEmpty())
    <div class="alert alert-info d-flex flex-wrap align-items-center gap-2">
        <i class="bx bx-check-circle fs-5"></i>
        <strong>Already applied:</strong>
        @foreach($shopApplications as $app)
            <span class="badge bg-primary">{{ $app->applicationBy?->name ?? ('Shop #'.$app->application_by) }} <small>(shop)</small></span>
        @endforeach
        @foreach($garageApplications as $app)
            <span class="badge bg-secondary">{{ $app->applicationBy?->name ?? ('Garage #'.$app->application_by) }} <small>(garage)</small></span>
        @endforeach
        <span class="text-muted small ms-auto">Applied users cannot be re-added to any slot.</span>
    </div>
    @endif

    <form method="POST" action="{{ route('insurance.manage-inboxes.update', $proforma) }}">
        @csrf

        <div class="row g-4">

            {{-- ── SHOP GROUPS ─────────────────────────────────────────── --}}
            <div class="col-12 col-xl-6">
                <div class="card radius-10 h-100">
                    <div class="card-header bg-light d-flex align-items-center gap-2">
                        <i class="bx bx-store"></i>
                        <strong>Shop Inbox Groups</strong>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-4">
                            Each group is a chereta slot — one winner per group.
                            <strong>Locked</strong> groups have either been satisfied (applied ✓) or are reserved for admin.
                            Clear a group to free it for admin.
                        </p>

                        @if($shopGroupCount === 0)
                            <div class="alert alert-secondary d-flex align-items-center gap-2">
                                <i class="bx bx-lock fs-5"></i>
                                <span>Shop inbox groups are <strong>not applicable</strong> for this <em>Garage Only</em> proforma.</span>
                            </div>
                        @else
                        @for($grp = 1; $grp <= $shopGroupCount; $grp++)
                        @php
                            [$isLocked, $badgeClass, $badgeText] = $groupStatus($shopGrp[$grp], $proformaPending, $shopAppliedGroups, $grp);
                            $isPartnerGroup = ($grp === 1);
                        @endphp
                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center gap-2">
                                Group {{ $grp }}
                                @if($isPartnerGroup)
                                    <span class="badge bg-info text-dark">Partners</span>
                                @endif
                                @if($isLocked)
                                    <span class="badge {{ $badgeClass }} ms-1">
                                        <i class="bx bx-lock-alt me-1"></i>{{ $badgeText }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border ms-1">
                                        <i class="bx bx-pencil me-1"></i>Editable
                                    </span>
                                @endif
                            </label>

                            @if($isLocked)
                                <div class="rounded border bg-light p-2 text-muted d-flex align-items-center gap-2" style="min-height:38px;">
                                    <i class="bx bx-lock-alt"></i>
                                    <span class="small">{{ $badgeText === 'Applied ✓' ? 'This slot has been satisfied by an application.' : ($badgeText === 'Proforma Closed' ? 'Proforma is closed.' : 'This slot was not sent by insurance — managed by admin.') }}</span>
                                </div>
                            @else
                                <select name="shop_group_{{ $grp }}[]"
                                        id="shopGroup{{ $grp }}"
                                        class="form-select js-select2-shop"
                                        multiple>
                                    @foreach($all_shops as $shop)
                                        <option value="{{ $shop->id }}"
                                            {{ in_array($shop->id, $shopGrp[$grp]) ? 'selected' : '' }}
                                            {{ in_array($shop->id, $appliedUserIds) ? 'disabled' : '' }}>
                                            {{ $shop->name }}{{ in_array($shop->id, $partnerIds) ? ' ★' : '' }}
                                            {{ in_array($shop->id, $appliedUserIds) ? ' (Applied)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($isPartnerGroup)
                                    <div class="form-text">★ = your partner &nbsp;|&nbsp; Applied users are greyed out and cannot be selected.</div>
                                @endif
                            @endif
                        </div>
                        @endfor
                        @endif

                    </div>
                </div>
            </div>

            {{-- ── GARAGE GROUPS ────────────────────────────────────────── --}}
            <div class="col-12 col-xl-6">
                <div class="card radius-10 h-100">
                    <div class="card-header bg-light d-flex align-items-center gap-2">
                        <i class="bx bx-wrench"></i>
                        <strong>Garage Inbox Groups</strong>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-4">
                            Each group is a chereta slot — one winner per group.
                            <strong>Locked</strong> groups have either been satisfied (applied ✓) or are reserved for admin.
                            Clear a group to free it for admin.
                        </p>

                        @if($garageGroupCount === 0)
                            <div class="alert alert-secondary d-flex align-items-center gap-2">
                                <i class="bx bx-lock fs-5"></i>
                                <span>Garage inbox groups are <strong>not applicable</strong> for this <em>Shop Only</em> proforma.</span>
                            </div>
                        @else
                        @for($grp = 1; $grp <= $garageGroupCount; $grp++)
                        @php
                            [$isLocked, $badgeClass, $badgeText] = $groupStatus($garageGrp[$grp], $proformaPending, $garageAppliedGroups, $grp);
                            $isPartnerGroup = ($grp === 1);
                        @endphp
                        <div class="mb-4">
            <label class="form-label fw-semibold d-flex align-items-center gap-2">
                                Group {{ $grp }}
                                @if($isPartnerGroup)
                                    <span class="badge bg-info text-dark">Partners</span>
                                @endif
                                @if($isLocked)
                                    <span class="badge {{ $badgeClass }} ms-1">
                                        <i class="bx bx-lock-alt me-1"></i>{{ $badgeText }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border ms-1">
                                        <i class="bx bx-pencil me-1"></i>Editable
                                    </span>
                                @endif
                            </label>

                            @if($isLocked)
                                <div class="rounded border bg-light p-2 text-muted d-flex align-items-center gap-2" style="min-height:38px;">
                                    <i class="bx bx-lock-alt"></i>
                                    <span class="small">{{ $badgeText === 'Applied ✓' ? 'This slot has been satisfied by an application.' : ($badgeText === 'Proforma Closed' ? 'Proforma is closed.' : 'This slot was not sent by insurance — managed by admin.') }}</span>
                                </div>
                            @else
                                <select name="garage_group_{{ $grp }}[]"
                                        id="garageGroup{{ $grp }}"
                                        class="form-select js-select2-garage"
                                        multiple>
                                    @foreach($all_garages as $garage)
                                        <option value="{{ $garage->id }}"
                                            {{ in_array($garage->id, $garageGrp[$grp]) ? 'selected' : '' }}
                                            {{ in_array($garage->id, $appliedUserIds) ? 'disabled' : '' }}>
                                            {{ $garage->name }}{{ in_array($garage->id, $garagePartnerIds) ? ' ★' : '' }}
                                            {{ in_array($garage->id, $appliedUserIds) ? ' (Applied)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($isPartnerGroup)
                                    <div class="form-text">★ = your partner &nbsp;|&nbsp; Applied users are greyed out and cannot be selected.</div>
                                @endif
                            @endif
                        </div>
                        @endfor
                        @endif

                    </div>
                </div>
            </div>

        </div>{{-- end row --}}

        @if($proformaPending)
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bx bx-save me-1"></i> Save Changes
            </button>
            <a href="{{ url('/insurance') }}" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>
        @else
        <div class="mt-4">
            <a href="{{ url('/insurance') }}" class="btn btn-outline-secondary px-4">Back to Dashboard</a>
        </div>
        @endif

    </form>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var cfg = {
        theme: 'bootstrap-5',
        placeholder: 'Select or search...',
        allowClear: true,
        width: '100%',
    };
    $('.js-select2-shop, .js-select2-garage').each(function () {
        $(this).select2(cfg);
    });
});
</script>
@endpush