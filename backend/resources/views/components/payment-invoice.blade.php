<div class="card mt-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bx bx-check-circle"></i> Payment Invoice</h5>
    </div>
    <div class="card-body">
        @php
            $baseAmount = $payment->amount;
            $vatRate = 15;
            $vatAmount = ($baseAmount * $vatRate) / 100;
            $totalAmount = $baseAmount + $vatAmount;
        @endphp
        
        <div class="row">
            <div class="col-md-6">
                <h6><strong>Payment Details</strong></h6>
                <p class="mb-1"><strong>Payment ID:</strong> #{{ $payment->id }}</p>
                <p class="mb-1"><strong>Proforma ID:</strong> #{{ $payment->proforma_id }}</p>
                <p class="mb-1"><strong>Payment Date:</strong> {{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : 'N/A' }}</p>
                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-success">Paid</span></p>
            </div>
            <div class="col-md-6">
                <h6><strong>Recipient Details</strong></h6>
                <p class="mb-1"><strong>Name:</strong> {{ $payment->user->name ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Role:</strong> {{ ucfirst($payment->user->role ?? 'N/A') }}</p>
            </div>
        </div>
        
        <hr>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Commission/Service Payment</td>
                    <td class="text-end">{{ number_format($baseAmount, 2) }} Birr</td>
                </tr>
                <tr>
                    <td>VAT ({{ $vatRate }}%)</td>
                    <td class="text-end">{{ number_format($vatAmount, 2) }} Birr</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="table-success">
                    <th>Total Paid Amount</th>
                    <th class="text-end">{{ number_format($totalAmount, 2) }} Birr</th>
                </tr>
            </tfoot>
        </table>
        
        <div class="alert alert-info mt-3">
            <i class="bx bx-info-circle"></i> This payment has been processed and credited to your account.
        </div>
        
        <div class="text-end mt-3">
            <button class="btn btn-success" onclick="window.print()">
                <i class="bx bx-printer"></i> Print Invoice
            </button>
        </div>
    </div>
</div>
