@extends('layouts.admin')

@section('content')
<div class="page-wrapper">
    <h2 class="mb-4">Admin Settings</h2>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ================= COST SETTINGS ================= --}}
    <div class="card mb-5">
        <div class="card-header bg-primary text-white">
            <strong>Cost Settings</strong>
        </div>
        <div class="card-body">

            {{-- Current Cost --}}
            <h5 class="mb-3">Current Cost</h5>
            @if($currentCost)
                <p><strong>Proforma Cost (1):</strong> {{ $currentCost->{'1_proforma_cost'} }}</p>
                <p><strong>Proforma Cost (2):</strong> {{ $currentCost->{'2_proforma_cost'} }}</p>
                <p><strong>Proforma Cost (3):</strong> {{ $currentCost->{'3_proforma_cost'} }}</p>
                <p><strong>Proforma Cost (4):</strong> {{ $currentCost->{'4_proforma_cost'} }}</p>
                <p><strong>Etera-Chereta Cost:</strong> {{ $currentCost->etera_chereta_cost }}</p>
                <p><strong>Insurance Proforma:</strong> {{ $currentCost->insurance_proforma ?? 'N/A' }}</p>
                <p><strong>Insured Cost:</strong> {{ $currentCost->insured_cost ?? '0.00' }}</p>
                <p><strong>Set On:</strong> {{ $currentCost->created_at->format('d M Y, H:i') }}</p>
            @else
                <p>No cost data available yet.</p>
            @endif

            <hr>

            {{-- Add New Cost --}}
            <h5>Add New Cost</h5>
            <form action="{{ route('admin.settings.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Proforma Cost (1)</label>
                        <input type="number" step="0.01" class="form-control" name="1_proforma_cost" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Proforma Cost (2)</label>
                        <input type="number" step="0.01" class="form-control" name="2_proforma_cost" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Proforma Cost (3)</label>
                        <input type="number" step="0.01" class="form-control" name="3_proforma_cost" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Proforma Cost (4)</label>
                        <input type="number" step="0.01" class="form-control" name="4_proforma_cost" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Etera-Chereta Cost</label>
                        <input type="number" step="0.01" class="form-control" name="etera_chereta_cost" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Insurance Proforma</label>
                        <input type="number" step="0.01" class="form-control" name="insurance_proforma" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Insured Cost</label>
                        <input type="number" step="0.01" class="form-control" name="insured_cost">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save"></i> Save New Cost
                </button>
            </form>
        </div>
    </div>

    {{-- Cost History --}}
    <div class="card mb-5">
        <div class="card-header">
            <strong>Cost History</strong>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Proforma (1)</th>
                        <th>Proforma (2)</th>
                        <th>Proforma (3)</th>
                        <th>Proforma (4)</th>
                        <th>Etera-Chereta</th>
                        <th>Insurance Proforma</th>
                        <th>Insured Cost</th>
                        <th>Set On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($costs as $index => $cost)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $cost->{'1_proforma_cost'} }}</td>
                            <td>{{ $cost->{'2_proforma_cost'} }}</td>
                            <td>{{ $cost->{'3_proforma_cost'} }}</td>
                            <td>{{ $cost->{'4_proforma_cost'} }}</td>
                            <td>{{ $cost->etera_chereta_cost }}</td>
                            <td>{{ $cost->insurance_proforma ?? 'N/A' }}</td>
                            <td>{{ $cost->insured_cost ?? '0.00' }}</td>
                            <td>{{ $cost->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                @if($currentCost && $cost->id === $currentCost->id)
                                    <span class="badge bg-success">Current</span>
                                @else
                                    <form action="{{ route('admin.settings.destroy', $cost) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i> Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No cost history available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================= COMMISSION SETTINGS ================= --}}
    <div class="card">
        <div class="card-header bg-success text-white">
            <strong>Commission Settings</strong>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.commission.store') }}" method="POST" class="mb-4">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Shop Insurance PI Commision (Birr)</label>
                        <input type="number" step="0.01" name="shopPay" class="form-control" value="{{ old('shopPay', $commission->shopPay ?? 0) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Garage Insurance PI Commision (Birr)</label>
                        <input type="number" step="0.01" name="garagePay" class="form-control" value="{{ old('garagePay', $commission->garagePay ?? 0) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Insurance PI Commision (Birr)</label>
                        <input type="number" step="0.01" name="insurancePay" class="form-control" value="{{ old('insurancePay', $commission->insurancePay ?? 0) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Operator PI Commision (Birr)</label>
                        <input type="number" step="0.01" name="operatorPay" class="form-control" value="{{ old('operatorPay', $commission->operatorPay ?? 0) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Shop Others PI Commision (Birr)</label>
                        <input type="number" step="0.01" name="othersPay" class="form-control" value="{{ old('othersPay', $commission->othersPay ?? 0) }}" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100">
                    <i class="bx bx-save"></i> Save Commission
                </button>
            </form>

            <h5>Current Commission Values</h5>
            @if($commission)
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Shop Insueance PI Commision</th>
                            <th>Garage Insurance PI Commision</th>
                            <th>Insurance PI Commision</th>
                            <th>Operator PI Commision</th>
                            <th>Shop Others PI Commision</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>{{ $commission->shopPay }}</td>
                            <td>{{ $commission->garagePay }}</td>
                            <td>{{ $commission->insurancePay }}</td>
                            <td>{{ $commission->operatorPay }}</td>
                            <td>{{ $commission->othersPay }}</td>
                            <td>{{ $commission->updated_at->format('d M Y, H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
            @else
                <p>No commission data yet.</p>
            @endif
        </div>
    </div>




</div>

{{-- Toast container for AJAX feedback --}}
<div id="toast-container" style="position:fixed; top:20px; right:20px; z-index:9999;"></div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Email toggle switches
    document.querySelectorAll('.email-toggle-switch').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const key = this.dataset.key;
            const switchEl = this;
            switchEl.disabled = true;

            fetch('{{ route("admin.settings.email-toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ key: key })
            })
            .then(r => r.json())
            .then(data => {
                switchEl.disabled = false;
                const badge = document.getElementById('email-badge-' + key);
                if (data.success) {
                    badge.className = 'badge ' + (data.enabled ? 'bg-success' : 'bg-danger');
                    badge.textContent = data.enabled ? 'Enabled' : 'Disabled';
                    showToast(data.message, 'success');
                } else {
                    switchEl.checked = !switchEl.checked;
                    showToast('Failed to toggle email setting', 'danger');
                }
            })
            .catch(err => {
                switchEl.disabled = false;
                switchEl.checked = !switchEl.checked;
                showToast('Network error. Please try again.', 'danger');
            });
        });
    });

    function showToast(message, type) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'alert alert-' + type + ' alert-dismissible fade show shadow';
        toast.style.cssText = 'min-width:300px; animation: slideIn 0.3s ease;';
        toast.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        container.appendChild(toast);
        setTimeout(() => { toast.remove(); }, 4000);
    }
});
</script>
@endpush
