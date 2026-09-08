@php
    $shop_data = [];
    $garage_data = [];
    foreach (($proforma->inboxes ?? collect()) as $inbox) {
        if (($inbox->source ?? '') !== 'insurance') continue;
        $role = $inbox->user?->role;
        $name = $inbox->user?->name ?? 'N/A';
        if ($role === 'shop') { $shop_data[] = $name; }
        else { $garage_data[] = $name; }
    }

    // Also check if a partner has already applied (inbox deleted on apply)
    $appliedInsuranceShop = $proforma->applications()
        ->where('application_source', 'partner')
        ->where('from', 'shop')
        ->with('applicationBy')
        ->first();
    $appliedInsuranceGarage = $proforma->applications()
        ->where('application_source', 'partner')
        ->where('from', 'garage')
        ->with('applicationBy')
        ->first();

    if ($appliedInsuranceShop && !count($shop_data)) {
        $shop_data[] = ($appliedInsuranceShop->applicationBy?->name ?? 'Partner') . ' ✓ Applied';
    }
    if ($appliedInsuranceGarage && !count($garage_data)) {
        $garage_data[] = ($appliedInsuranceGarage->applicationBy?->name ?? 'Partner') . ' ✓ Applied';
    }

    // Count client-side applications (non-insurance-partner) to lock filled slots
    $clientShopApplied = $proforma->applications()
        ->where('from', 'shop')
        ->where('application_source', '!=', 'partner')
        ->count();
    $clientGarageApplied = $proforma->applications()
        ->where('from', 'garage')
        ->where('application_source', '!=', 'partner')
        ->count();

    // Effective insurance quota — use stored column, fall back to inbox count
    // so proformas created before the quota column was added still lock correctly.
    $effShopQuota   = $proforma->shopPartnerQuota() > 0
        ? $proforma->shopPartnerQuota()
        : count($shop_data);
    $effGarageQuota = $proforma->garagePartnerQuota() > 0
        ? $proforma->garagePartnerQuota()
        : count($garage_data);

    $reqShops   = (int) ($proforma->required_number_of_shops   ?? 0);
    $reqGarages = (int) ($proforma->required_number_of_garages ?? 0);

    $statusLocked = in_array($proforma->status ?? '', ['closed', 'completed']);

    // ── Type-based section locking ────────────────────────────────────────
    $isGarageOnly = $proforma->isGarageOnlyInsurance(); // insurance_garage_only → lock shops
    $isShopOnly   = $proforma->isShopOnlyInsurance();   // insurance_shop_only  → lock garages

    // Fix caps: type-locked side MUST be 0, not fall back to 2
    $adminShopCap   = $isGarageOnly ? 0 : ($reqShops   > 0 ? max(0, $reqShops   - $effShopQuota)   : 2);
    $adminGarageCap = $isShopOnly   ? 0 : ($reqGarages > 0 ? max(0, $reqGarages - $effGarageQuota) : 2);

    // ── Per-slot application check ────────────────────────────────────────
    // Lock only the specific slot whose admin-inboxed user has already applied.
    // This keeps other slots editable after float, even if some applications arrived.
    $shop1Applied   = $selectedClientShop1   && $proforma->applications()->where('application_by', $selectedClientShop1)->where('from', 'shop')->exists();
    $shop2Applied   = $selectedClientShop2   && $proforma->applications()->where('application_by', $selectedClientShop2)->where('from', 'shop')->exists();
    $garage1Applied = $selectedClientGarage1 && $proforma->applications()->where('application_by', $selectedClientGarage1)->where('from', 'garage')->exists();
    $garage2Applied = $selectedClientGarage2 && $proforma->applications()->where('application_by', $selectedClientGarage2)->where('from', 'garage')->exists();

    // After float (published): empty slots are locked — no point adding new inboxes
    // when the proforma is already public. Only assigned slots stay editable.
    $isFloated = $proforma->status === 'published';
@endphp
<div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="mb-3">Spare Part Shops
                        @if($isGarageOnly)
                            <span class="badge bg-danger ms-2"><i class="bx bx-lock me-1"></i>Garage Only — Locked</span>
                        @endif
                    </h4>
                    @if($isGarageOnly)
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="bx bx-info-circle me-1"></i> This proforma is <strong>Garage Only</strong>. Shop inbox slots are disabled.
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="mt-2 mb-1">Insurance Side</label>
                @if($isGarageOnly)
                        <div class="input-group">
                            <input type="text" class="form-control bg-light text-muted" value="Not applicable (Garage Only)" readonly>
                            <span class="input-group-text"><i class="bx bx-lock text-secondary"></i></span>
                        </div>
                @elseif(count($shop_data))
                    <div class="input-group">
    <input type="text" 
           name="shopPay" 
           class="form-control bg-light text-muted" 
           value="{{ implode(', ', $shop_data ?? []) }}" 
           readonly>
    <span class="input-group-text">
        <i class="bx bx-lock text-danger"></i>
    </span>
</div>


                            
                @else
                        <div class="input-group">
                            <select name="spare_part_partners[]" {{$selectedInsuranceShop || $proforma->status != 'pending' ? 'disabled' : ''}} multiple class="form-select" id="multiple1" wire:model.live="selectedInsuranceShop">
                                <option value="">Select Spare Part Shop</option>
                                @foreach($shops as $shop)
                                    <option value="{{$shop->id}}">{{$shop->store_id}} - {{$shop->name}}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text">
                                @if($selectedInsuranceShop)
                                    <i class="bx bx-lock text-danger"></i>
                                @else
                                    <i class="bx bx-lock-open text-success"></i>
                                @endif
                            </span>
                        </div>
                         @endif
                        @error('selected_insurance_shop')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="mt-2 mb-1">Client Side #1</label>
                        <div class="input-group">
                            <select name="spare_part_partners[]" {{ ($shop1Applied || $statusLocked || $adminShopCap < 1 || $isGarageOnly || ($isFloated && !$selectedClientShop1)) ? 'disabled' : '' }} class="form-select" id="multiple2" wire:model.live="selectedClientShop1">
                                <option value="">— Clear slot —</option>
                                @foreach($shops as $shop)
                                    <option value="{{$shop->id}}">{{$shop->store_id}} - {{$shop->name}}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text">
                                @if($shop1Applied)
                                    <i class="bx bx-lock text-danger" title="Applied — locked"></i>
                                @elseif($isGarageOnly || $adminShopCap < 1)
                                    <i class="bx bx-lock text-secondary" title="Not available"></i>
                                @elseif($isFloated && !$selectedClientShop1)
                                    <i class="bx bx-lock text-secondary" title="Floated — empty slot locked"></i>
                                @elseif($selectedClientShop1)
                                    <i class="bx bx-lock-open text-warning" title="Inboxed — can still change"></i>
                                @else
                                    <i class="bx bx-lock-open text-success"></i>
                                @endif
                            </span>
                        </div>
                        @if($shop1Applied)
                            <small class="text-danger"><i class="bx bx-check-circle"></i> Shop applied — slot locked</small>
                        @endif
                        @error('selected_client_shop_1')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                    {{-- <div class="mb-3">
                        <label class="mt-2 mb-1">Client Side #1</label>
                        <div class="input-group">
                            <select name="spare_part_partners[]" {{$selectedClientShop1 || $proforma->status != 'pending' ? 'disabled' : ''}} class="form-select" id="multiple2" wire:model.live="selectedClientShop1">
                                <option value="">Select Spare Part Shop</option>
                                @foreach($shops as $shop)
                                    <option value="{{$shop->id}}">{{$shop->store_id}} - {{$shop->name}}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text">
                                @if($selectedClientShop1)
                                    <i class="bx bx-lock text-danger"></i>
                                @else
                                    <i class="bx bx-lock-open text-success"></i>
                                @endif
                            </span>
                        </div>
                        @error('selected_client_shop_1')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                     --}}
                    <div class="mb-3">
                        <label class="mt-2 mb-1">Client Side #2</label>
                        <div class="input-group">
                            <select name="spare_part_partners[]" {{ ($shop2Applied || $statusLocked || $adminShopCap < 2 || $isGarageOnly || ($isFloated && !$selectedClientShop2)) ? 'disabled' : '' }} class="form-select" id="multiple3" wire:model.live="selectedClientShop2">
                                <option value="">— Clear slot —</option>
                                @foreach($shops as $shop)
                                    <option value="{{$shop->id}}">{{$shop->store_id}} - {{$shop->name}}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text">
                                @if($shop2Applied)
                                    <i class="bx bx-lock text-danger" title="Applied — locked"></i>
                                @elseif($isGarageOnly || $adminShopCap < 2)
                                    <i class="bx bx-lock text-secondary" title="Not available"></i>
                                @elseif($isFloated && !$selectedClientShop2)
                                    <i class="bx bx-lock text-secondary" title="Floated — empty slot locked"></i>
                                @elseif($selectedClientShop2)
                                    <i class="bx bx-lock-open text-warning" title="Inboxed — can still change"></i>
                                @else
                                    <i class="bx bx-lock-open text-success"></i>
                                @endif
                            </span>
                        </div>
                        @if($shop2Applied)
                            <small class="text-danger"><i class="bx bx-check-circle"></i> Shop applied — slot locked</small>
                        @endif
                        @error('selected_client_shop_2')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                    {{-- <div class="mb-3">
                        <label class="mt-2 mb-1">Client Side #2</label>
                        <div class="input-group">
                            <select name="spare_part_partners[]" {{$selectedClientShop2 || $proforma->status != 'pending' ? 'disabled' : ''}} class="form-select" id="multiple3" wire:model.live="selectedClientShop2">
                                <option value="">Select Spare Part Shop</option>
                                @foreach($shops as $shop)
                                    <option value="{{$shop->id}}">{{$shop->store_id}} - {{$shop->name}}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text">
                                @if($selectedClientShop2)
                                    <i class="bx bx-lock text-danger"></i>
                                @else
                                    <i class="bx bx-lock-open text-success"></i>
                                @endif
                            </span>
                        </div>
                        @error('selected_client_shop_2')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div> --}}
                    
                </div>

                <div class="col-sm-6">
                    <h4 class="mb-3">Garages
                        @if($isShopOnly)
                            <span class="badge bg-danger ms-2"><i class="bx bx-lock me-1"></i>Shop Only — Locked</span>
                        @endif
                        @if($proforma->isShopGarageInsurance())
                            <span class="badge bg-info ms-2"><i class="bx bx-building-house me-1"></i>Dual Service Only</span>
                        @endif
                    </h4>
                    @if($isShopOnly)
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="bx bx-info-circle me-1"></i> This proforma is <strong>Shop Only</strong>. Garage inbox slots are disabled.
                        </div>
                    @endif
                    @if($proforma->isShopGarageInsurance())
                        <div class="alert alert-info py-2 mb-3">
                            <i class="bx bx-info-circle me-1"></i> This is a <strong>Dual Service</strong> proforma. Only garages with Dual Service enabled are shown.
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="mt-2 mb-1">Insurance Side</label>
                @if($isShopOnly)
                        <div class="input-group">
                            <input type="text" class="form-control bg-light text-muted" value="Not applicable (Shop Only)" readonly>
                            <span class="input-group-text"><i class="bx bx-lock text-secondary"></i></span>
                        </div>
                @elseif(count($garage_data))
                <div class="input-group">
                    <input type="text"
                           name="Garage"
                           class="form-control bg-light text-muted"
                           value="{{ implode(', ', $garage_data) }}"
                           readonly>
                    <span class="input-group-text"><i class="bx bx-lock text-danger"></i></span>
                </div>
                @else
                        <div class="input-group">
                            <select name="garage_partners[]" {{$selectedInsuranceGarage || $proforma->status != 'pending' ? 'disabled' : ''}} multiple class="form-select" id="multiple4" wire:model.live="selectedInsuranceGarage">
                                <option value="">Select Garage</option>
                                @foreach($garages as $garage)
                                    <option value="{{$garage->id}}">{{$garage->store_id}} - {{$garage->name}}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text">
                                @if($selectedInsuranceGarage)
                                    <i class="bx bx-lock text-danger"></i>
                                @else
                                    <i class="bx bx-lock-open text-success"></i>
                                @endif
                            </span>
                        </div>
                        @endif
                        @error('selected_insurance_garage')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                    

                    <div class="mb-3">
                        <label class="mt-2 mb-1">Client Side #1</label>
                        <div class="input-group">
                            <select name="garage_partners[]" {{ ($garage1Applied || $statusLocked || $adminGarageCap < 1 || $isShopOnly || ($isFloated && !$selectedClientGarage1)) ? 'disabled' : '' }} class="form-select" id="multiple5" wire:model.live="selectedClientGarage1">
                                <option value="">— Clear slot —</option>
                                @foreach($garages as $garage)
                                    <option value="{{$garage->id}}">{{$garage->store_id}} - {{$garage->name}}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text">
                                @if($garage1Applied)
                                    <i class="bx bx-lock text-danger" title="Applied — locked"></i>
                                @elseif($isShopOnly || $adminGarageCap < 1)
                                    <i class="bx bx-lock text-secondary" title="Not available"></i>
                                @elseif($isFloated && !$selectedClientGarage1)
                                    <i class="bx bx-lock text-secondary" title="Floated — empty slot locked"></i>
                                @elseif($selectedClientGarage1)
                                    <i class="bx bx-lock-open text-warning" title="Inboxed — can still change"></i>
                                @else
                                    <i class="bx bx-lock-open text-success"></i>
                                @endif
                            </span>
                        </div>
                        @if($garage1Applied)
                            <small class="text-danger"><i class="bx bx-check-circle"></i> Garage applied — slot locked</small>
                        @endif
                        @error('selected_client_garage_1')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="mt-2 mb-1">Client Side #2</label>
                        <div class="input-group">
                            <select name="garage_partners[]" {{ ($garage2Applied || $statusLocked || $adminGarageCap < 2 || $isShopOnly || ($isFloated && !$selectedClientGarage2)) ? 'disabled' : '' }} class="form-select" id="multiple6" wire:model.live="selectedClientGarage2">
                                <option value="">— Clear slot —</option>
                                @foreach($garages as $garage)
                                    <option value="{{$garage->id}}">{{$garage->store_id}} - {{$garage->name}}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text">
                                @if($garage2Applied)
                                    <i class="bx bx-lock text-danger" title="Applied — locked"></i>
                                @elseif($isShopOnly || $adminGarageCap < 2)
                                    <i class="bx bx-lock text-secondary" title="Not available"></i>
                                @elseif($isFloated && !$selectedClientGarage2)
                                    <i class="bx bx-lock text-secondary" title="Floated — empty slot locked"></i>
                                @elseif($selectedClientGarage2)
                                    <i class="bx bx-lock-open text-warning" title="Inboxed — can still change"></i>
                                @else
                                    <i class="bx bx-lock-open text-success"></i>
                                @endif
                            </span>
                        </div>
                        @if($garage2Applied)
                            <small class="text-danger"><i class="bx bx-check-circle"></i> Garage applied — slot locked</small>
                        @endif
                        @error('selected_client_garage_2')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="my-0">

@php $allInsuranceFull = $adminShopCap === 0 && $adminGarageCap === 0; @endphp
 @if(($proforma?->status == 'pending' || $proforma?->status == 'opened') && (!$proforma?->selected() || $proforma->selectedBy()->employee_id == auth()->id()) && !$allInsuranceFull)
                <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Proforma Posted')"> Send to inbox
                </button>
                @endif
                @if($proforma?->status == 'published')
                <button type="submit" class="btn btn-success radius-30 px-4">Save Inbox Changes</button>
                @endif

                &nbsp
                <a href="proforma" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                </a>
            </div>
        </div>
    </div>
</div>
