{{-- @extends('layouts.marketer')
@section('content')
<!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">              
                <div class="card">
                  
                  <div class="card-body p-4">
                    
                      <h5 class="card-title">Add Spare Parts Shop</h5>
                      <hr/>
                       <form class="row g-3" action="{{route('add-shop.marketer')}}" method="POST">
                        @csrf
                        @method('POST')
                                    <div class="col-md-6">
                                        <label for="input1" class="form-label">Name</label>
                                        <input name="name" type="text" class="form-control" id="input1" placeholder="Your Company">
                                    </div>
                                      @error('name')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-6">
                                        <label for="input2" class="form-label">Phone Number</label>
                                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09...">
                                    </div>
                                     @error('phone_number')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-6">
                                        <label for="input3" class="form-label">Tin #</label>
                                        <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Your Company Tin #">
                                    </div>
                                      @error('tin_number')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-6">
                                        <label for="input4" class="form-label">Location / Address</label>
                                        <input name="location" type="text" class="form-control" id="input4" placeholder="">
                                    </div>
                                    @error('location')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-12">
                                        <label for="multiple-select-clear-field" class="form-label">Car Brands To Serve</label>
                                        <select required name="brands[]" class="form-select" id="multiple-select-clear-field" data-placeholder="Add Brands..." multiple>
                                            @foreach($brands as $brand)
                                            <option value="{{$brand->id}}">{{$brand->name}}</option>
                                       @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="input6" class="form-label">Business License Proc. Number</label>
                                        <input name="business_license_number" type="text" class="form-control" id="input6" placeholder="Proclamation Number">
                                    </div>
                                      @error('business_license_number')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-6">
                                        <label for="input6" class="form-label">Business License Expiry Date</label>
                                        <input name="license_expire_date" type="date" class="form-control" id="input6" placeholder="Select Date">
                                    </div>
                                       @error('license_expire_date')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-6">
                                        <label for="input6" class="form-label">Business License Image</label>
                                        <input type="file" id="inputLicenseImage" name="license_image" >
                                    </div>
                                    <div class="col-md-6">
                                        <label for="input6" class="form-label">Stamp Image</label>
                                         <input type="file" id="inputStampImage" name="stamp_image" >
                                    </div>
                                    <div class="col-md-4">
                                        <label for="input7" class="form-label">Email</label>
                                        <input name="email" type="email" class="form-control" id="input7" placeholder="Your Email">
                                    </div>
                                      @error('email')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-4">
                                        <label for="input8" class="form-label">Password</label>
                                        <input name="password" type="password" class="form-control" id="input8" placeholder="********">
                                    </div>
                                     @error('pasword')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-4">
                                        <label for="input9" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="input9" placeholder="Confirm Password">
                                    </div>
                                    <hr/>
                                    <div class="my-0">
                                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Spare Part Shop Added Successfully')"> Add
                                        </button>
                                        &nbsp
                                        <a href="/marketer/spare-part-shops" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                                        </a>
                                    </div>
                                </form>
                  </div>
              </div>


            </div>
        </div>
        <!--end page wrapper -->
@endsection --}}


@extends('layouts.insurance')
@section('content')
<!-- Start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Add Spare Parts Shop</h5>
                <hr/>
                <form class="row g-3" action="{{ route('add-shop.marketer') }}" method="POST" enctype="multipart/form-data">
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

                    <div class="col-md-6">
                        <label for="multiple-select-clear-field" class="form-label">Car Brands To Serve</label>
                        <select required name="brands[]" class="form-select" id="multiple-select-clear-field" data-placeholder="Add Brands..." multiple>
                            @foreach($brands as $brand)
                            <option value="{{$brand->id}}">{{$brand->name}}</option>
                            @endforeach
                        </select>
                        @error('brands')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>



                    <div class="col-md-6">
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
                    </div>

                    <div class="col-md-6">
                        <label for="input6" class="form-label">Business License Image</label>
                        <input type="file" id="inputLicenseImage" name="license_image">
                        @error('license_image')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="input6" class="form-label">Stamp Image</label>
                        <input type="file" id="inputStampImage" name="stamp_image">
                        @error('stamp_image')
                        <span class="text-danger">{{$message}}</span>
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
                        <input type="password" name="password_confirmation" class="form-control" id="input9" placeholder="Confirm Password">
                    </div>

                    <hr/>
                    <div class="my-0">
                        <button type="submit" class="btn btn-primary radius-30 px-4"> Add </button>
                        &nbsp;
                        <a href="/marketer/spare-part-shops" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End page wrapper -->
@endsection
