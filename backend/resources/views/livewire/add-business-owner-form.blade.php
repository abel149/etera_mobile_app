{{-- resources/views/livewire/add-business-owner-form.blade.php --}}
<div>
    <div class="card">
        <div class="card-body p-4">
            <h5 class="card-title">Add Business Owner</h5>
            <hr/>

            @if(session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit.prevent="submit" class="row g-3" enctype="multipart/form-data">

                <!-- Name Field -->
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input wire:model.live="name" type="text" class="form-control" id="name" placeholder="Your Name">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Phone Number Field -->
                <div class="col-md-6">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input wire:model.live="phone_number" type="text" class="form-control" id="phone_number" placeholder="09...">
                    @error('phone_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Tin Number Field -->
                <div class="col-md-6">
                    <label for="tin_number" class="form-label">Tin #</label>
                    <input wire:model.live="tin_number" type="text" class="form-control" id="tin_number" placeholder="Your Company Tin #">
                    @error('tin_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Location Field -->
                <div class="col-md-6">
                    <label for="location" class="form-label">Location / Address</label>
                    <input wire:model.live="location" type="text" class="form-control" id="location" placeholder="">
                    @error('location')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email Field -->
                <div class="col-md-4">
                    <label for="email" class="form-label">Email</label>
                    <input wire:model.live="email" type="email" class="form-control" id="email" placeholder="Your Email">
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="col-md-4">
                    <label for="password" class="form-label">Password</label>
                    <input wire:model.live="password" type="password" class="form-control" id="password" placeholder="********">
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password Field -->
                <div class="col-md-4">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input wire:model.live="password_confirmation" type="password" class="form-control" id="password_confirmation" placeholder="Confirm Password">
                    @error('password_confirmation')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="pt-3">
                    <hr/>
                    <button type="submit" class="btn btn-primary radius-30 px-4">Add</button>
                    &nbsp;
                    <a href="/marketer/business-owners" class="btn btn-outline-secondary radius-30 px-3">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
