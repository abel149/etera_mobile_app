<div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="mb-3">Spare Part Shops</h4>
                    <div class="mb-3">
                        <label class="mt-2 mb-1">Insurance Side</label>
                        
                        
                @if($proforma->inboxes?->count())
                @php
                    $shop_data = [];
                    $garage_data = [];
                @endphp
                @foreach($proforma->inboxes as $inbox)
                    @php
                        $role = $inbox->user?->role;
                        $name = $inbox->user?->name ?? 'N/A';
                        if ($role === 'shop') {
                            $shop_data[] = $name;
                        } else {
                            $garage_data[] = $name;
                        }
                    @endphp
                @endforeach
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
                            <select name="spare_part_partners[]" {{$selectedClientShop1 || $proforma->status != 'pending' ? 'disabled' : ''}} multiple class="form-select" id="multiple2" wire:model.live="selectedClientShop1">
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
                            <select name="spare_part_partners[]"  {{$selectedClientShop2 || $proforma->status != 'pending' ? 'disabled' : ''}} multiple class="form-select" id="multiple3" wire:model.live="selectedClientShop2">
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
                    <h4 class="mb-3">Garages</h4>
                    <div class="mb-3">
                        <label class="mt-2 mb-1">Insurance Side</label>
                        <div class="input-group">
                            
                @if($proforma->inboxes?->count())
                <div class="input-group">
                    <input type="text" 
           name="Garage" 
           class="form-control bg-light text-muted" 
           value="{{ implode(', ', $garage_data ?? []) }}" 
           readonly>
    <span class="input-group-text">
        <i class="bx bx-lock text-danger"></i>
    </span>
</div>
                @else
                
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
                            <select name="garage_partners[]"  {{$selectedClientGarage1 || $proforma->status != 'pending' ? 'disabled' : ''}} multiple class="form-select" id="multiple5" wire:model.live="selectedClientGarage1">
                                <option value="">Select Garage</option>
                                @foreach($garages as $garage)
                                    <option value="{{$garage->id}}">{{$garage->store_id}} - {{$garage->name}}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text">
                                @if($selectedClientGarage1)
                                    <i class="bx bx-lock text-danger"></i>
                                @else
                                    <i class="bx bx-lock-open text-success"></i>
                                @endif
                            </span>
                        </div>
                        @error('selected_client_garage_1')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="mt-2 mb-1">Client Side #2</label>
                        <div class="input-group">
                            <select name="garage_partners[]" {{$selectedClientGarage2 || $proforma->status != 'pending' ? 'disabled' : ''}} multiple class="form-select" id="multiple6" wire:model.live="selectedClientGarage2">
                                <option value="">Select Garage</option>
                                @foreach($garages as $garage)
                                    <option value="{{$garage->id}}">{{$garage->store_id}} - {{$garage->name}}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text">
                                @if($selectedClientGarage2)
                                    <i class="bx bx-lock text-danger"></i>
                                @else
                                    <i class="bx bx-lock-open text-success"></i>
                                @endif
                            </span>
                        </div>
                        @error('selected_client_garage_2')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="my-0">

 @if(($proforma?->status == 'pending' || $proforma?->status == 'opened') && (!$proforma?->selected() || $proforma->selectedBy()->employee_id == auth()->id()))
                <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Proforma Posted')"> Float
                </button>
                @endif

                &nbsp
                <a href="proforma" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                </a>
            </div>
        </div>
    </div>
</div>
