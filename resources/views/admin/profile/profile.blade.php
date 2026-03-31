@extends('layouts.admin')
@section('content')
<!-- Start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="container">
            <div class="main-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-column align-items-center text-center">
                                    <img src="{{asset('assets/images/avatars/avatar-9.jpg')}}" alt="Admin"
                                        class="rounded-circle p-1 bg-primary mt-2" width="150">
                                    <div class="mt-3">
                                        <h4>{{ auth()->user()->name }}</h4>
                                        <p class="text-secondary mb-1">{{ ucfirst(auth()->user()->role) }}</p>
                                        <p class="text-muted font-size-sm">Date Registered: {{ auth()->user()->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="text-center mb-4 mt-1">Account Details</h4>

                                <form action="{{ route('profile.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Name</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="text" class="form-control" name="name" value="{{ auth()->user()->name }}" />
                                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Email</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="email" class="form-control" name="email" value="{{ auth()->user()->email }}" />
                                            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Phone</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="text" class="form-control" name="phone_number" value="{{ auth()->user()->phone_number }}" />
                                            @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">New Password</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="password" class="form-control" name="password" placeholder="Enter new password" />
                                            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>


									<div class="row mb-3">
										<div class="col-sm-3">
											<h6 class="mb-0">Confirm Password</h6>
										</div>
										<div class="col-sm-9 text-secondary">
											<input type="password" name="password_confirmation" class="form-control" placeholder="Confirm new password" />
										</div>
									</div>

                                    <div class="row">
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-9 text-secondary">
                                            <button type="submit" class="btn btn-primary px-4 radius-30">Save Changes</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End page wrapper -->
@endsection
