 <div id="test-vl-1" role="tabpane3" class="bs-stepper-pane content fade" aria-labelledby="stepper3trigger1">
                                <h5 class="mb-1">Basic Information</h5>
                                <p class="mb-4">Enter the basic proforma request</p>

                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <label for="FirstName" class="form-label">File Number</label>
                                        <input type="text" name="file_number" value="{{ old('file_number') }}" class="form-control" id="FisrtName" placeholder="">
                                        @error('file_number')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="InputCountry" class="form-label">Brandasds</label>
                                        <select class="form-select" name="brand_id" value="old('brand_id')" id="InputCountry" aria-label="Default select example">

                                            @foreach($brands as $brand)
                                                <option value="{{$brand->id}}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{$brand->name}}</option>
                                            @endforeach
                                        </select>
                                        @error('brand_id')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="PhoneNumber" class="form-label">Model</label>
                                        <input type="text" name="model" value="{{ old('model') }}" class="form-control" id="PhoneNumber" placeholder="example: yarris">
                                        @error('model')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="InputCountry" class="form-label">Year</label>
                                        <select class="form-select" name="year" value="{{ old('year') }}" id="InputCountry" aria-label="Default select example">
                                            <option value="2024">2024</option>
                                        </select>
                                        @error('year')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <button type="button" class="btn btn-primary px-4 rounded-pill" onclick="stepper3.next()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
                                    </div>
                                </div>
                            </div>


