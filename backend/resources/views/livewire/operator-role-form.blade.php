{{-- resources/views/livewire/operator-role-form.blade.php --}}
<div>
    <div class="card">
        <div class="card-body p-4">
            <h5 class="card-title">Add Operators Role</h5>
            <hr/>
            <form wire:submit.prevent="submit" class="row g-3">
                <div class="col-md-12">
                    <label for="name" class="form-label">Name</label>
                    <input wire:model.live="name" type="text" class="form-control" id="name" placeholder="Enter The Role name...">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label for="rank" class="form-label">Rank</label>
                    <input wire:model.live="rank" type="number" class="form-control" id="rank" placeholder="Ex. 1">
                    @error('rank')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label for="status_label" class="form-label">Status Label</label>
                    <input wire:model.live="status_label" type="text" class="form-control" id="status_label" placeholder="Payment Accept from Client">
                    @error('status_label')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="pt-3">
                    <hr/>
                    <button type="submit" class="btn btn-primary radius-30 px-4">Add</button>
                    &nbsp
                    <a href="/admin/roles" class="btn btn-outline-secondary radius-30 px-3">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
