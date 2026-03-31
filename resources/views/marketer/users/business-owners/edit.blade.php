@extends('layouts.marketer')

@section('content')
<!-- Start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <h3>Edit Business Owner</h3>
        <form action="{{ route('marketer.business-owners.update', $businessOwner->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{ old('name', $businessOwner->name) }}">
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email', $businessOwner->email) }}">
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone_number" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" id="phone_number" value="{{ old('phone_number', $businessOwner->phone_number) }}">
                                        @error('phone_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                  
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tin_number" class="form-label">TIN Number</label>
                                        <input type="text" class="form-control @error('tin_number') is-invalid @enderror" name="tin_number" id="tin_number" value="{{ old('tin_number', $businessOwner->tin_number) }}">
                                        @error('tin_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{-- <div class="mb-3">
                                        <label for="business_license_number" class="form-label">Business License Number</label>
                                        <input type="text" class="form-control @error('business_license_number') is-invalid @enderror" name="business_license_number" id="business_license_number" value="{{ old('business_license_number', $businessOwner->business_license_number) }}">
                                        @error('business_license_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="license_expire_date" class="form-label">License Expiry Date</label>
                                        <input type="date" class="form-control @error('license_expire_date') is-invalid @enderror" name="license_expire_date" id="license_expire_date" value="{{ old('license_expire_date', $businessOwner->license_expire_date ? $businessOwner->license_expire_date->format('Y-m-d') : '') }}">
                                        @error('license_expire_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div> --}}
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password">
                                        @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" id="password_confirmation">
                                        @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Update Business Owner</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End page wrapper -->
@endsection
