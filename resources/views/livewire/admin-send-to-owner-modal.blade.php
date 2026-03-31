<div>
    <div class="modal fade" id="sendToOwnerModal" tabindex="-1" aria-labelledby="sendToOwnerModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendToOwnerModalLabel">
                        <i class="bx bx-send me-2"></i>Send Proforma to Owner
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    @if($proforma)
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="bx bx-file me-2"></i>Proforma Details
                                        </h6>
                                        <p class="mb-1"><strong>File #:</strong> {{ $proforma->file_number }}</p>
                                        <p class="mb-1"><strong>Customer:</strong> {{ $proforma->customer_name }}</p>
                                        <p class="mb-1"><strong>Vehicle:</strong> {{ $proforma->brand->name ?? 'N/A' }} {{ $proforma->model }}</p>
                                        <p class="mb-0"><strong>License:</strong> {{ $proforma->license_plate_number }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <h6 class="card-title text-info">
                                            <i class="bx bx-info-circle me-2"></i>Request Information
                                        </h6>
                                        <p class="mb-1"><strong>Type:</strong> 
                                            <span class="badge bg-{{ $proforma->isEteraCheretaMode() ? 'warning' : 'primary' }}">
                                                {{ $proforma->isEteraCheretaMode() ? 'Etera-Chereta' : 'Regular' }}
                                            </span>
                                        </p>
                                        @if($proforma->isEteraCheretaMode())
                                            <p class="mb-1"><strong>Required Shops:</strong> {{ $proforma->required_number_of_shops ?? 'N/A' }}</p>
                                            <p class="mb-0"><strong>Duration:</strong> {{ $proforma->timer_duration ? ($proforma->timer_duration / 60) . ' hours' : 'N/A' }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="sendToOwner">
                        <div class="row">
                            @if($proformaType === 'etera_chereta')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="hourlyPrice" class="form-label">Hourly Price (Before VAT)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" wire:model.live="hourlyPrice" id="hourlyPrice" step="0.01" min="0">
                                                <span class="input-group-text">ETB</span>
                                            </div>
                                            @error('hourlyPrice') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="hours" class="form-label">Number of Hours</label>
                                            <input type="number" class="form-control" wire:model.live="hours" id="hours" min="1">
                                            @error('hours') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="requestedCount" class="form-label">Requested Count</label>
                                            <input type="number" class="form-control" wire:model.live="requestedCount" id="requestedCount" min="1">
                                            @error('requestedCount') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="unitPrice" class="form-label">Proforma Unit Price (Before VAT)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" wire:model.live="unitPrice" id="unitPrice" step="0.01" min="0">
                                                <span class="input-group-text">ETB</span>
                                            </div>
                                            @error('unitPrice') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-calculator me-2"></i>Price Calculation
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong>Subtotal:</strong> 
                                            <span class="text-primary">
                                                {{ number_format($subtotal, 2) }} ETB
                                            </span>
                                        </p>
                                        <p class="mb-2">
                                            <strong>VAT ({{ $vatRate }}%):</strong> 
                                            <span class="text-warning">{{ number_format($vatAmount, 2) }} ETB</span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-0">
                                            <strong>Total Amount:</strong> 
                                            <span class="text-success fs-5">{{ number_format($totalAmount, 2) }} ETB</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="closeModal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="sendToOwner" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendToOwner">
                            <i class="bx bx-send me-1"></i>Send to Owner
                        </span>
                        <span wire:loading wire:target="sendToOwner">
                            <i class="bx bx-loader-alt bx-spin me-1"></i>Sending...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('open-send-modal', (event) => {
                @this.openModal(event.proformaId);
                var modal = new bootstrap.Modal(document.getElementById('sendToOwnerModal'));
                modal.show();
            });

            Livewire.on('proforma-sent', (event) => {
                var modal = bootstrap.Modal.getInstance(document.getElementById('sendToOwnerModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Show notification
                if (event.type === 'success') {
                    toastr.success(event.message);
                } else {
                    toastr.error(event.message);
                }
            });
        });
    </script>
</div>