@extends('layouts.admin')
@section('content')
<!-- start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Add Insurance</h5>
                <hr/>
                @if ($errors->has('error'))
                    <div class="alert alert-danger">
                        {{ $errors->first('error') }}
                    </div>
                @endif
                <form class="row g-3" action="{{ route('add-insurance') }}" method="POST">
                    @csrf
                    @method('POST')

                    <!-- Name Field -->
                    <div class="col-md-6">
                        <label for="input1" class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" id="input1" placeholder="Insurance Name..." required>
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Phone Number Field -->
                    <div class="col-md-6">
                        <label for="input2" class="form-label">Phone Number</label>
                        <input name="phone_number" type="text" class="form-control" id="input2" placeholder="09..." required>
                        @error('phone_number')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div class="col-md-6">
                        <label for="input7" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" id="input7" placeholder="Email...">
                        @error('email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <hr/>
                    <div class="my-0">
                        <button type="submit" class="btn btn-primary radius-30 px-4">Add</button>
                        &nbsp;
                        <a href="/admin/insurances" class="btn btn-outline-secondary radius-30 px-3">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end page wrapper -->
@endsection
