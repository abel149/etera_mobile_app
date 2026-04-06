<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bx bx-receipt"></i>Etera Invoice</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6><strong>Invoice Details</strong></h6>
                <p class="mb-1"><strong>Etera</strong></p>
                <p class="mb-1"><strong>Proforma ID:</strong> #{{ $invoice->proforma_id }}</p>
                <p class="mb-1"><strong>Date:</strong> {{ $invoice->created_at->format('M d, Y') }}</p>
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
                    <td>Platform Service Charge</td>
                    <td class="text-end">{{ number_format($invoice->unit_price, 2) }} Birr</td>
                </tr>
                <tr>
                    <td>VAT ({{ $invoice->vat_rate }}%)</td>
                    <td class="text-end">{{ number_format($invoice->vat_amount, 2) }} Birr</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="table-primary">
                    <th>Total Amount</th>
                    <th class="text-end">{{ number_format($invoice->total_amount, 2) }} Birr</th>
                </tr>
            </tfoot>
        </table>
        
        
    </div>
</div>
