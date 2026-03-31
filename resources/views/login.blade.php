@extends('layouts.authentication')

@section('img')
    <img src="{{ asset('assets/images/login-images/login-cover.svg') }}" class="img-fluid auth-img-cover-login" width="650" alt=""/>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
    </div>

    <script>
        setTimeout(function() {
            document.querySelector('.alert-success')?.remove();
        }, 3000); // Hides after 3 seconds
    </script>
@endif

<div class="text-center mb-4">
    
        <div class="col-12">
            <div class="text-center">
                <p class="mb-0">Rate Shops and Garages <a href="/rating" class="text-purple">here</a></p>
            </div>
        </div>
    <div>
        <img src="{{ asset('assets/images/transparent.svg') }}" class="logo-text mb-4" style="max-width: 7.5rem;" alt="etera">
    </div>
    
    <p class="mb-0">Please fill in the below details to login to your account</p>
</div>

<div class="form-body">
    <form class="row g-3" action="{{ route('login') }}" method="POST">
    @csrf

    <div class="col-12">
        <label for="inputEmailOrPhone" class="form-label">Email or Phone Number</label>
        <input type="text" class="form-control" name="email_or_phone" id="inputEmailOrPhone"
               placeholder="jhon@etera.com or 0940000000" value="{{ old('email_or_phone') }}">
        @error('email_or_phone')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-12">
        <label for="inputChoosePassword" class="form-label">Password</label>
        <div class="input-group" id="show_hide_password">
            <input type="password" name="password" class="form-control border-end-0" id="inputChoosePassword"
                   placeholder="Enter Password">
            <a href="javascript:;" class="input-group-text bg-transparent"><i class="bx bx-hide"></i></a>
        </div>
        @error('password')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>


        <div class="col-md-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" name="remember">
                <label class="form-check-label" for="flexSwitchCheckChecked">Remember Me</label>
            </div>
        </div>

        <div class="col-md-6 text-end">
            <a href="/forgot-password" class="text-purple">Forgot Password?</a>
        </div>

        <div class="col-12">
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Sign in</button>
            </div>
        </div>

        <div class="col-12">
            <div class="text-center">
                <p class="mb-0">Don't have an account yet? <a href="/signup" class="text-purple">Sign up here</a></p>
            </div>
        </div>
    </form>
</div>

@endsection
