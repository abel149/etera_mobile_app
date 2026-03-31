
  <div id="test-vl-3" role="tabpane3" class="bs-stepper-pane content fade" aria-labelledby="stepper3trigger3">
                                <div class="repeater-form">
                                    <div id="repeater">
                                        <div class="d-flex align-items-center justify-content-between">
                                            @if ($errors->any())
                                                <div class="alert alert-danger">
                                                    <ul>
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            <div>
                                                <h5 class="mb-1">Spare Parts</h5>
                                                <p class="mb-4">Add the requireds spare parts</p>
                                            </div>
                                            <button type="button" id="add-repeater" class="btn btn-primary repeater-add-btn px-4">Add</button>
                                        </div>
                                        <div class="item-content row g-2">
                                           
                                            <div class="col-12 col-lg-2">
                                                <label for="inputEmail1" class="form-label">Part Name and Number</label>
                                            </div>
                                            <div class="col-12 col-lg-2">
                                                <label for="inputName1" class="form-label">Grade</label>
                                            </div>
                                            <div class="col-12 col-lg-2">
                                                <label for="inputName1" class="form-label">Country</label>
                                            </div>
                                            <div class="col-12 col-lg-1">
                                                <label for="inputName1" class="form-label">Qty</label>
                                            </div>
                           
                                            <div class=" col-12 col-lg-1">
                                                <label for="inputEmail1" class="form-label"> &nbsp</label>
                                            </div>
                                        </div>
                                        <div id="repeater">
                                            <div class="repeater-item">
                                                <div class="item-content row g-3">
                                                    <div class="col-12 col-lg-2">
                                                        <select class="form-select" name="parts[id][]" id="InputCountry" aria-label="Default select example">
                                                            @foreach($parts as $part)
                                                                <option value="{{$part->id}}" {{ old('part_id') == $part->id ? 'selected' : '' }}>{{$part->name}}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('parts')
                                                            <span class="text-danger">{{$message}}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-12 col-lg-2">
                                                        <input name="parts[number][]" type="text" class="form-control" id="inputEmail1" placeholder="Part number" data-skip-name="true" data-name="email">
                                                    </div>
                                                    <div class="col-12 col-lg-2">
                                                        <input name="parts[grade][]" type="text" class="form-control" id="inputName1" placeholder="Grade" data-name="name">
                                                    </div>
                                                    <div class="col-12 col-lg-2">
                                                        <input name="parts[country][]" type="text" class="form-control" id="inputName1" placeholder="Country" data-name="name">
                                                    </div>
                                                    <div class="col-12 col-lg-1">
                                                        <input name="parts[qty][]" type="text" class="form-control" id="inputName1" placeholder="Qty" data-name="name">
                                                    </div>
                  
                                                    <div class="repeater-remove-btn remove-repeater col-12 col-lg-1">
                                                        <button type="button" class="remove-repeater btn btn-danger"><i class="bx bx-trash me-0"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-6">
                                            <label for="multiple-select-sparepart" class="form-label">Spare Part Shop Partners (Option)</label>
                                            <select class="form-select" name="spare_part_partners[]" id="multiple-select-sparepart" data-placeholder="Choose anything" multiple>
                                                @foreach($spare_part_partners as $partner)
                                                    <option value="{{$partner->id}}" {{ old('garage_partners') == $partner->id ? 'selected' : '' }}>{{$partner->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <label for="multiple-select-garage" class="form-label">Garage Partners (Option)</label>
                                            <select class="form-select" id="multiple-select-garage" data-placeholder="Choose anything" multiple>
                                                @foreach($garage_partners as $partner)
                                                    <option value="{{$partner->id}}" {{ old('garage_partners') == $partner->id ? 'selected' : '' }}>{{$partner->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 pt-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="stepper3.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Previous</button>
                                            <button type="button" class="btn btn-primary rounded-pill px-4" onclick="stepper3.next()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
                                        </div>
                                    </div>
                                </div>


