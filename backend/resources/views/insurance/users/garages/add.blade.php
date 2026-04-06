@extends('layouts.insurance')
@section('content')
<!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">              
                <div class="card">
                  <div class="card-body p-4">
                      <h5 class="card-title">Add Garage</h5>
                      <hr/>
                       <form class="row g-3" action="{{route('add-garage.marketer')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                                    <div class="col-md-6">
                                        <label for="input1" class="form-label">Name</label>
                                        <input name="name" type="text" class="form-control" id="input1" placeholder="Your Company">
                                     @error('name')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="input2" class="form-label">Phone Number</label>
                                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09...">
                                     @error('phone_number')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="input3" class="form-label">Tin #</label>
                                        <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Your Company Tin #">
                                     @error('tin_number')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="input4" class="form-label">Location / Address</label>
                                        <input name="location" type="text" class="form-control" id="input4" placeholder="">
                                     @error('location')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    </div>
                                    {{-- <div class="col-md-6">
                                        <label for="input6" class="form-label">Business License Proc. Number</label>
                                        <input name="business_license_number" type="text" class="form-control" id="input6" placeholder="Proclamation Number">
                                     @error('business_license_number')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="input6" class="form-label">Business License Expiry Date</label>
                                        <input name="license_expire_date" type="date" class="form-control" id="input6" placeholder="Select Date">
                                     @error('license_expire_date')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    </div> --}}

                                

                                    <div class="col-md-6">
                                        <label for="licenseShop" class="form-label">Business License Image</label>
                                        <input type="file" name="license_image" class="form-control" accept="image/*" required> <!-- Standard file input -->
                                        @error('license_image')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                
                                    <!-- Stamp Image -->
                                    <div class="col-md-6">
                                        <label for="stampShop" class="form-label">Stamp Image</label>
                                        <input type="file"  name="stamp_image" class="form-control" accept="image/*" required> <!-- Standard file input -->
                                        @error('stamp_image')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>













                                    <div class="col-md-4">
                                        <label for="input7" class="form-label">Email</label>
                                        <input name="email" type="email" class="form-control" id="input7" placeholder="Your Email">
                                    @error('email')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="input8" class="form-label">Password</label>
                                        <input name="password" type="password" class="form-control" id="input8" placeholder="********">
                                    @error('password')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="input9" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" name="password_confirmation" id="input9" placeholder="Confirm Password">
                                    </div>
                                  
                                    <hr/>
                                    <div class="my-0">
                                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Garage Added Successfully')"> Add
                                        </button>
                                        &nbsp
                                        <a href="/admin/garages" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                                        </a>
                                    </div>
                                                
                                </form>
                  </div>
              </div>


            </div>
        </div>
        <!--end page wrapper -->
@endsection
