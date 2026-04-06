<div class="card">
    <div class="card-body p-4">
        <h5 class="card-title">Edit Employee</h5>
        <hr />
        <form wire:submit.prevent="submit" class="row g-3">
            <!-- Name Input -->
            <div class="col-md-6">
                <label for="name" class="form-label">Name</label>
                <input wire:model.live="name" type="text" class="form-control" id="name" placeholder="Employee's Name">
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Phone Number Input -->
            <div class="col-md-6">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input wire:model.live="phone_number" type="text" class="form-control" id="phone_number" placeholder="09...">
                @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Email Input -->
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input wire:model.live="email" type="email" class="form-control" id="email" placeholder="Employee's Email">
                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Password Input -->
            <div class="col-md-6">
                <label for="password" class="form-label">Password</label>
                <input wire:model.live="password" type="password" class="form-control" id="password" placeholder="********">
                @error('password') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Role Selection -->
            <div class="col-md-6">
                <label for="role_id" class="form-label">Role</label>
                <select wire:model.live="role_id" class="form-select mb-3" id="role_id">
                    <option value="" selected>Select Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }} - Rank: {{ $role->rank }}</option>
                    @endforeach
                </select>
                @error('role_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Manager Selection (Only visible if managers are available) -->
            <div class="col-md-6">
                <label for="manager_id" class="form-label">Manager</label>
                <select wire:model.live="manager_id" id="manager_id" class="form-select" {{ empty($managers) ? 'disabled' : '' }}>
                    <option value="" selected>Select Manager</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}">{{ $manager->name }} ({{ $manager->email }})</option>
                    @endforeach
                </select>
                @error('manager_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Submit and Cancel Buttons -->
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary radius-30 px-4">Update</button>
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary radius-30 px-3">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
