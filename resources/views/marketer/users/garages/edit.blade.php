{{-- @extends('layouts.admin')
@section('content')
<!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">              
                <div class="card">
                  <div class="card-body p-4">
                      <h5 class="card-title">Edit Garage</h5>
                      <hr/>
                       <form class="row g-3" action="{{route('edit-garage')}}" method="POST">
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
                                    @error('password')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-4">
                                        <label for="input9" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="input9" placeholder="Confirm Password">
                                    </div>
                                   <!--  <div class="col-md-12">
                                        <label for="inputProductDescription" class="form-label">Business License Image</label>
                                <input id="image-uploadify" type="file" accept="image/*,.pdf" multiple>
                                    </div> -->
                                    <hr/>
                                    <div class="my-0">
                                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Garage Updated Successfully')"> Update
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
@endsection --}}
{{-- @extends('layouts.marketer')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Edit Garsdfage</h5>
                <hr/>
                <form class="row g-3" action="{{ route('update-garage.marketer', $garage->id) }}" method="POST">
                    @csrf
                    @method('PUT') <!-- Method for updating -->
                    
                    <div class="col-md-6">
                        <label for="input1" class="form-label">Name</label>
                        <input name="name" type="text" class="form-control" id="input1" placeholder="Your Company" value="{{ old('name', $garage->name) }}">
                    </div>
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input2" class="form-label">Phone Number</label>
                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09..." value="{{ old('phone_number', $garage->phone_number) }}">
                    </div>
                    @error('phone_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input3" class="form-label">Tin #</label>
                        <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Your Company Tin #" value="{{ old('tin_number', $garage->tin_number) }}">
                    </div>
                    @error('tin_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input4" class="form-label">Location / Address</label>
                        <input name="location" type="text" class="form-control" id="input4" placeholder="Location" value="{{ old('location', $garage->location) }}">
                    </div>
                    @error('location')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input6" class="form-label">Business License Proc. Number</label>
                        <input name="business_license_number" type="text" class="form-control" id="input6" placeholder="Proclamation Number" value="{{ old('business_license_number', $garage->business_license_number) }}">
                    </div>
                    @error('business_license_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input6" class="form-label">Business License Expiry Date</label>
                        <input name="license_expire_date" type="date" class="form-control" id="input6" value="{{ old('license_expire_date', $garage->license_expire_date) }}">
                    </div>
                    @error('license_expire_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-4">
                        <label for="input7" class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" id="input7" placeholder="Your Email" value="{{ old('email', $garage->email) }}">
                    </div>
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-4">
                        <label for="input8" class="form-label">Password</label>
                        <input name="password" type="password" class="form-control" id="input8" placeholder="********">
                    </div>
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-4">
                        <label for="input10" class="form-label">Confirm Password</label>
                        <input name="password_confirmation" type="password" class="form-control" id="input10" placeholder="Confirm Password">
                    </div>



                    <hr/>
                    <div class="my-0">
                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Garage Updated Successfully')"> Update
                        </button>
                        &nbsp;
                        <a href="/marketer/garages" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->
@endsection --}}



@extends('layouts.marketer')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Edit Garage</h5>
                <hr/>
                <form class="row g-3" action="{{ route('update-garage.marketer', $garage->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') <!-- Method for updating -->

                    <!-- Garage Details -->
                    <div class="col-md-6">
                        <label for="input1" class="form-label">Name</label>
                        <input name="name" type="text" class="form-control" id="input1" placeholder="Your Company" value="{{ old('name', $garage->name) }}">
                    </div>
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Phone Number -->
                    <div class="col-md-6">
                        <label for="input2" class="form-label">Phone Number</label>
                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09..." value="{{ old('phone_number', $garage->phone_number) }}">
                    </div>
                    @error('phone_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Tin Number -->
                    <div class="col-md-6">
                        <label for="input3" class="form-label">Tin #</label>
                        <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Your Company Tin #" value="{{ old('tin_number', $garage->tin_number) }}">
                    </div>
                    @error('tin_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Location -->
                    <div class="col-md-6">
                        <label for="input4" class="form-label">Location / Address</label>
                        <input name="location" type="text" class="form-control" id="input4" placeholder="Location" value="{{ old('location', $garage->location) }}">
                    </div>
                    @error('location')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                  

                    <!-- Email -->
                    <div class="col-md-4">
                        <label for="input7" class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" id="input7" placeholder="Your Email" value="{{ old('email', $garage->email) }}">
                    </div>
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Password -->
                    <div class="col-md-4">
                        <label for="input8" class="form-label">Password</label>
                        <input name="password" type="password" class="form-control" id="input8" placeholder="********">
                    </div>
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Confirm Password -->
                    <div class="col-md-4">
                        <label for="input10" class="form-label">Confirm Password</label>
                        <input name="password_confirmation" type="password" class="form-control" id="input10" placeholder="Confirm Password">
                    </div>

                    <!-- Business License Image Upload -->
                    <div class="col-md-6">
                        <label for="license_image" class="form-label">Business License Image</label>
                        <input name="license_image" type="file" class="form-control" id="license_image">
                        @if($garage->license_image)
                            <p>Current Image: 
                                <a href="{{ asset('storage/' . $garage->license_image) }}">View Image</a>
                            </p>
                        @endif
                    </div>
                    @error('license_image')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <!-- Stamp Image Upload -->
                    <div class="col-md-6">
                        <label for="stamp_image" class="form-label">Stamp Image</label>
                        <input name="stamp_image" type="file" class="form-control" id="stamp_image">
                        @if($garage->stamp_image)
                            <p>Current Image: 
                                <a href="{{ asset('storage/' . $garage->stamp_image) }}">View Image</a>
                            </p>
                        @endif
                    </div>
                    @error('stamp_image')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                    
                    <hr/>
                    <div class="my-0">
                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Garage Updated Successfully')"> Update
                        </button>
                        &nbsp;
                        <a href="/marketer/garages" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->
@endsection
