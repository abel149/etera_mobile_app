    <div id="test-vl-2" role="tabpane3" class="bs-stepper-pane content fade" aria-labelledby="stepper3trigger2">
                                <h5 class="mb-1">Car Information</h5>
                                <p class="mb-4">Enter the car details</p>

                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <label for="InputUsername" class="form-label">Owner Name</label>
                                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="form-control" id="InputUsername" placeholder="">
                                        @error('customer_name')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="InputEmail2" class="form-label">Phone Number</label>
                                        <input type="text" name="customer_phone_number" value="{{ old('customer_phone_number') }}" class="form-control" id="InputEmail2" placeholder="">
                                        @error('customer_phone_number')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="InputPassword" class="form-label">License Plate Number</label>
                                        <input type="text" name="license_plate_number" value="{{ old('license_plate_number') }}" class="form-control" id="InputPassword" value="">
                                        @error('license_plate_number')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label for="InputConfirmPassword" class="form-label">Chassis Number</label>
                                        <input type="text" name="chassis_number" value="{{ old('chassis_number') }}" class="form-control" id="InputConfirmPassword" value="">
                                        @error('chassis_number')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="stepper3.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Previous</button>
                                            <button type="button" class="btn btn-primary rounded-pill px-4" onclick="stepper3.next()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>


