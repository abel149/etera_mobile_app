{{-- @extends('layouts.admin')
@section('content')
<!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">              
                <div class="card">
                  
                  <div class="card-body p-4">
                      <h5 class="card-title">Edit Insurance</h5>
                      <hr/>
                       <form class="row g-3" action="{{route('edit-insurance')}}" method="POST">
                        @csrf
                        @method('POST')
                                    <div class="col-md-12">
                                        <label for="input1" class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" id="input1" placeholder="Insurance Name...">
                                    </div>
                                    @error('name')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-12">
                                        <label for="input7" class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" id="input7" placeholder="Email...">
                                    </div>
                                      @error('email')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                     <div class="col-md-12">
                                        <label for="input7" class="form-label">Phone Number</label>
                                        <input type="number" name="phone_number" class="form-control" id="input7" placeholder="251...">
                                    </div>
                                      @error('phone_number')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-12">
                                        <label for="input8" class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" id="input8" placeholder="********">
                                    </div>
                                      @error('password')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                               
                                    <hr/>
                                    <div class="my-0">
                                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Insurance Updated Successfully')"> Update
                                        </button>
                                        &nbsp
                                        <a href="/admin/insurances" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                                        </a>
                                    </div>
                                </form>
                  </div>
              </div>


            </div>
        </div>
        <!--end page wrapper -->
@endsection --}}
@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">              
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Edit Insurance</h5>
                <hr/>
                <!-- Form to update insurance -->
                <form class="row g-3" action="{{ route('update-insurance', $insurance->id) }}" method="POST">
                    @csrf
                    @method('PUT') <!-- Use PUT method for updating -->
                    
                    <div class="col-md-12">
                        <label for="input1" class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" id="input1" placeholder="Insurance Name..." value="{{ old('name', $insurance->name) }}">
                    </div>
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-12">
                        <label for="input7" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" id="input7" placeholder="Email..." value="{{ old('email', $insurance->email) }}">
                    </div>
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <div class="col-md-12">
                        <label for="input7" class="form-label">Phone Number</label>
                        <input type="number" name="phone_number" class="form-control" id="input7" placeholder="251..." value="{{ old('phone_number', $insurance->phone_number) }}">
                    </div>
                    @error('phone_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    

                    <hr/>
                    <div class="my-0">
                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Insurance Updated Successfully')"> Update</button>
                        &nbsp;
                        <a href="/admin/insurances" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->
@endsection
