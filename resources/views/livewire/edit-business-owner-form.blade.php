{{-- resources/views/livewire/edit-business-owner-form.blade.php --}}
<div>
    <div class="card">
        <div class="card-body p-4">
            <h5 class="card-title">Edit Business Owner</h5>
            <hr/>

            {{-- Flash message display --}}
            @if(session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit.prevent="update" class="row g-3" enctype="multipart/form-data">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input wire:model="name" type="text" class="form-control" id="name">
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input wire:model="phone_number" type="text" class="form-control" id="phone_number">
                    @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label for="tin_number" class="form-label">Tin #</label>
                    <input wire:model="tin_number" type="text" class="form-control" id="tin_number">
                    @error('tin_number') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label for="location" class="form-label">Location / Address</label>
                    <input wire:model="location" type="text" class="form-control" id="location">
                    @error('location') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label for="business_license_number" class="form-label">Business License Proc. Number</label>
                    <input wire:model="business_license_number" type="text" class="form-control" id="business_license_number">
                    @error('business_license_number') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label for="license_expiry_date" class="form-label">Business License Expiry Date</label>
                    <input wire:model="license_expiry_date" type="date" class="form-control" id="license_expiry_date">
                    @error('license_expiry_date') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label for="license_image" class="form-label">Business License Image</label>
                    <input wire:model="license_image" type="file" accept="image/*">
                    @if ($existing_license_image)
                        <img src="{{ asset('storage/' . $existing_license_image) }}" alt="Current License" class="img-thumbnail mt-2" width="100">
                    @endif
                    @error('license_image') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label for="stamp_image" class="form-label">Stamp Image</label>
                    <input wire:model="stamp_image" type="file" accept="image/*">
                    @if ($existing_stamp_image)
                        <img src="{{ asset('storage/' . $existing_stamp_image) }}" alt="Current Stamp" class="img-thumbnail mt-2" width="100">
                    @endif
                    @error('stamp_image') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label for="email" class="form-label">Email</label>
                    <input wire:model="email" type="email" class="form-control" id="email">
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label for="password" class="form-label">New Password (Leave blank to keep current password)</label>
                    <input wire:model="password" type="password" class="form-control" id="password">
                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input wire:model="password_confirmation" type="password" class="form-control" id="password_confirmation">
                    @error('password_confirmation') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="pt-3">
                    <hr/>
                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="/marketer/business-owners" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
