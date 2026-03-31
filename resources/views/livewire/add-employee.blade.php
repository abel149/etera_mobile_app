<div class="card">
    <div class="card-body p-4">
        <h5 class="card-title">Add Employee</h5>
        <hr/>

        <form wire:submit.prevent="submit" class="row g-3">

            <!-- Name -->
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input wire:model="name" type="text" class="form-control">
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Phone -->
            <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input wire:model="phone_number" type="text" class="form-control">
                @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Email -->
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input wire:model="email" type="email" class="form-control">
                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Role -->
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select wire:model="role_id" class="form-select">
                    <option value="">Select Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Manager (ONLY for operators) -->
            @php
                $selectedLevel = $roles->firstWhere('id', $role_id);
            @endphp

            @if($selectedLevel && strtolower($selectedLevel->name) === 'operator')
                <div class="col-md-6">
                    <label class="form-label">Manager</label>
                    <select wire:model="manager_id" class="form-select">
                        <option value="">Select Manager</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">
                                {{ $manager->name }} ({{ $manager->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            @endif

            <!-- Actions -->
            <div class="mt-3">
                <button class="btn btn-primary px-4">Add</button>
                <a href="{{ route('admin.employees.index') }}"
                   class="btn btn-outline-secondary px-3">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>
