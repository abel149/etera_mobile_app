@extends('layouts.marketer')

@section('content')
<!-- Start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">              
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Edit Insurance</h5>
                <hr/>
                <form class="row g-3" action="{{ route('update-insurance.marketer', $insurance->id) }}" method="POST">
                    @csrf
                    @method('PUT') <!-- Specify that this form submits a PUT request -->
                    
                    <div class="col-md-12">
                        <label for="input1" class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" id="input1" value="{{ $insurance->name }}" required>
                    </div>
                    @error('name')
                    <span class="text-danger">{{$message}}</span>
                    @enderror

                    <div class="col-md-12">
                        <label for="input2" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" id="input2" value="{{ $insurance->email }}" required>
                    </div>
                    @error('email')
                    <span class="text-danger">{{$message}}</span>
                    @enderror

                    <div class="col-md-12">
                        <label for="input3" class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" id="input3" value="{{ $insurance->phone_number }}" required>
                    </div>
                    @error('phone_number')
                    <span class="text-danger">{{$message}}</span>
                    @enderror

                    <div class="col-md-12">
                        <label for="input4" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" id="input4" placeholder="Leave blank to keep the same">
                    </div>
                    @error('password')
                    <span class="text-danger">{{$message}}</span>
                    @enderror


                    <div class="col-md-12">
                        <label for="input8" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" id="input8" placeholder="********">
                    </div>
                    

                    <hr/>
                    <div class="my-0">
                        <button type="submit" class="btn btn-primary radius-30 px-4">Update</button>
                        &nbsp;
                        <a href="/marketer/insurances" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End page wrapper -->
@endsection