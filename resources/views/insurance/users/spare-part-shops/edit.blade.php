{{-- @extends('layouts.admin')
@section('content')
<!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">              
                <div class="card">
                  
                  <div class="card-body p-4">
                    
                      <h5 class="card-title">Edit Spare Parts Shop</h5>
                      <hr/>
                       <form class="row g-3" action="{{route('edit-shop')}}" method="POST">
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
                                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Spare Part Shop Updated Successfully')"> Update
                                        </button>
                                        &nbsp
                                        <a href="/admin/spare-part-shops" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
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
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">              
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Edit Spare Parts Shop</h5>
                <hr/>
                <form class="row g-3" action="{{ route('update-shop.marketer', $shop->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="col-md-6">
                        <label for="input1" class="form-label">Name</label>
                        <input name="name" type="text" class="form-control" id="input1" placeholder="Your Company" value="{{ old('name', $shop->name) }}">
                    </div>
                    @error('name')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input2" class="form-label">Phone Number</label>
                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09..." value="{{ old('phone_number', $shop->phone_number) }}">
                    </div>
                    @error('phone_number')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input3" class="form-label">Tin #</label>
                        <input name="tin_number" type="text" class="form-control" id="input3" placeholder="Your Company Tin #" value="{{ old('tin_number', $shop->tin_number) }}">
                    </div>
                    @error('tin_number')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input4" class="form-label">Location / Address</label>
                        <input name="location" type="text" class="form-control" id="input4" placeholder="" value="{{ old('location', $shop->location) }}">
                    </div>
                    @error('location')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                   
            
                    <div>
                        <label for="multiple-select-clear-field" class="form-label">Car Brands To Serve</label>
                        <select required name="brands[]" class="form-select" id="multiple-select-clear-field" data-placeholder="Add Brands..." multiple>
                            @foreach ($allBrands as $brand)
                                <option value="{{ $brand->id }}" 
                                        @if(in_array($brand->id, $brands)) selected @endif>
                                    {{ $brand->name }}
                                </option>
                            @endforeach



                            
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="input6" class="form-label">Business License Proc. Number</label>
                        <input name="business_license_number" type="text" class="form-control" id="input6" placeholder="Proclamation Number" value="{{ old('business_license_number', $shop->business_license_number) }}">
                    </div>
                    @error('business_license_number')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6">
                        <label for="input7" class="form-label">Business License Expiry Date</label>
                        <input name="license_expire_date" type="date" class="form-control" id="input7" placeholder="Select Date" value="{{ old('license_expire_date', $shop->license_expire_date) }}">
                    </div>
                    @error('license_expire_date')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-4">
                        <label for="input8" class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" id="input8" placeholder="Your Email" value="{{ old('email', $shop->email) }}">
                    </div>
                    @error('email')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-4">
                        <label for="input9" class="form-label">Password</label>
                        <input name="password" type="password" class="form-control" id="input9" placeholder="********">
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
                        <button type="submit" class="btn btn-primary radius-30 px-4">Update</button>
                        &nbsp;
                        <a href="{{ url('/admin/spare-part-shops') }}" type="button" class="btn btn-outline-secondary radius-30 px-3">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->
@endsection
