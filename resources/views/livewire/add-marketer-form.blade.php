
<div>
<div class="card">
    <div class="card-body p-4">
        <h5 class="card-title">Add Marketer</h5>
        <hr/>
        <form wire:submit.prevent="submit" class="row g-3">

            <!-- Name Field -->
            <div class="col-md-6">
                <label for="name" class="form-label">Name</label>
                <input wire:model.live="name" type="text" class="form-control" id="name" placeholder="Your Company">
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Phone Number Field -->
            <div class="col-md-6">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input wire:model.live="phone_number" type="text" class="form-control" id="phone_number" placeholder="09...">
                @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Email Field -->
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input wire:model.live="email" type="email" class="form-control" id="email" placeholder="Your Email">
                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <hr/>
            <div class="my-0">
                <button type="submit" class="btn btn-primary radius-30 px-4">Add</button>
                &nbsp;
                <a href="/admin/marketers" class="btn btn-outline-secondary radius-30 px-3">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
