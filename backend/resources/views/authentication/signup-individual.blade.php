@extends('layouts.authentication')

@section('img')
    <img src="assets/images/login-images/register-cover.svg" class="img-fluid auth-img-cover-login" width="550" alt=""/>
<style>
.role-fields {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-top: 15px;
    transition: all 0.3s ease-in-out;
}

.form-label .text-danger {
    font-weight: bold;
}

.role-fields .form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>

@endsection

@section('content')
<div class="text-center mb-4">
    <div>
        <img src="{{ asset('assets/images/transparent.svg') }}" class="logo-text mb-4" style="max-width: 7.5rem;" alt="etera">
    </div>
    <p class="mb-0">Individual Registration - Please fill the below details to create your account</p>
</div>

<div class="form-body">
    <form class="row g-3" action="{{ route('register.individual') }}" method="POST">
        @csrf
        <input type="hidden" name="role" value="individual">

        <!-- Basic Information Section -->
        <div class="row g-3">
            <div class="col-6">
                <label for="inputName" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="inputName" name="name" placeholder="John Doe" required value="{{ old('name') }}">
            </div>
    
            <div class="col-6">
                <label for="inputPhone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="inputPhone" name="phone_number" placeholder="0912131415" maxlength="10" inputmode="numeric" pattern="\d{10}" >
            </div>
        </div>

        <div class="row g-3">
            <div class="col-6">
                <label for="inputLocation" class="form-label">Location <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="inputLocation" name="location" placeholder="Addis Ababa, Ethiopia" required value="{{ old('location') }}">
            </div>

            <div class="col-6">
                <label for="inputEmailAddress" class="form-label">Email Address <span class="text-muted">(optional)</span></label>
                <input type="email" class="form-control" id="inputEmailAddress" name="email" placeholder="example@user.com" value="{{ old('email') }}">
            </div>
        </div>

        <div class="row g-3">
            <div class="col-6">
                <label for="inputChoosePassword" class="form-label">Password <span class="text-danger">*</span></label>
                <div class="input-group" id="show_hide_password">
                    <input type="password" class="form-control border-end-0" id="inputChoosePassword" name="password" placeholder="Enter Password" required>
                    <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                </div>
            </div>
            <div class="col-6">
                <label for="inputConfirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <div class="input-group" id="show_hide_password">
                    <input type="password" class="form-control border-end-0" id="inputConfirmPassword" name="password_confirmation" placeholder="Confirm Password" required>
                    <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" name="terms" required>
                <label class="form-check-label" for="flexSwitchCheckChecked">I read and agree to Terms & Conditions</label>
            </div>
        </div>

        <div class="col-12">
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Sign up</button>
            </div>
        </div>

        <div class="col-12">
            <div class="text-center">
                <p class="mb-0">Already have an account? <a href="/login" class="text-purple">Sign in here</a></p>
                <p class="mb-0 mt-2">
                    <a href="{{ route('signup.business-owner') }}" class="text-info">Business Owner Registration</a> | 
                    <a href="{{ route('signup.garage-sparepart') }}" class="text-info">Garage/Spare Part Registration</a>
                </p>
            </div>
        </div>
    </form>
</div>

<div class="login-separater text-center mb-5">
    <span>OR SIGN UP WITH EMAIL</span>
    <hr/>
</div>

<div class="list-inline contacts-social text-center">
    <a href="javascript:;" class="list-inline-item bg-facebook text-white border-0 rounded-3"><i class="bx bxl-facebook"></i></a>
    <a href="javascript:;" class="list-inline-item bg-twitter text-white border-0 rounded-3"><i class="bx bxl-twitter"></i></a>
    <a href="javascript:;" class="list-inline-item bg-google text-white border-0 rounded-3"><i class="bx bxl-google"></i></a>
    <a href="javascript:;" class="list-inline-item bg-linkedin text-white border-0 rounded-3"><i class="bx bxl-linkedin"></i></a>
</div>

@endsection