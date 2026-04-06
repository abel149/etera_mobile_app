<div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-12">
                    <h4 class="mb-3">Spare Part Shops To Float To:</h4>
                    <div class="row">
                    @for($i = 1; $i <= $proforma->number_of_proformas; $i++)
                    <div class="col-sm-6">
                    <div class="mb-3">
                        <label class="mt-2 mb-1">Spare Part Shop #{{$i}}</label>
                        <div class="input-group">
                            <select name="spare_part_partners[]" class="form-select" multiple id="multiple{{$i}}" >
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
                        @error('selected_insurance_shop')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                    </div>
                    
@endfor
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
