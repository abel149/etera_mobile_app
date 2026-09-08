@extends('layouts.insurance')

@section('content')

<h2 class="mb-4">My Balance & Transactions</h2>

{{-- ================= SUMMARY ================= --}}
<div class="row mb-4">

{{-- LEFT: Incoming from Etera --}}
<div class="col-md-6">
<div class="row text-center">

<div class="col-6 mb-3">
<div class="card shadow-sm border-start border-warning border-4">
<div class="card-body">
<h6 class="text-muted">Pending FROM Etera</h6>
<h4 class="text-warning">{{ number_format($summary['pending_from_etera'], 2) }} ETB</h4>
<small class="text-muted">Commissions Owed</small>
</div>
</div>
</div>

<div class="col-6 mb-3">
<div class="card shadow-sm border-start border-success border-4">
<div class="card-body">
<h6 class="text-muted">Paid FROM Etera</h6>
<h4 class="text-success">{{ number_format($summary['paid_from_etera'], 2) }} ETB</h4>
<small class="text-muted">Commissions Received</small>
</div>
</div>
</div>

<div class="col-12 mb-3">
<div class="card shadow-sm border-start border-primary border-4">
<div class="card-body">
<h6 class="text-muted">Total Earned FROM Etera</h6>
<h4 class="text-primary">{{ number_format($summary['total_earned_from_etera'], 2) }} ETB</h4>
<small class="text-muted">Total Commissions</small>
</div>
</div>
</div>

</div>
</div>

{{-- RIGHT: Outgoing to Etera --}}
<div class="col-md-6">
<div class="row text-center">

<div class="col-6 mb-3">
<div class="card shadow-sm border-start border-warning border-4">
<div class="card-body">
<h6 class="text-muted">Pending TO Etera</h6>
<h4 class="text-warning">{{ number_format($summary['pending_to_etera'], 2) }} ETB</h4>
<small class="text-muted">Invoices Unpaid</small>
</div>
</div>
</div>

<div class="col-6 mb-3">
<div class="card shadow-sm border-start border-success border-4">
<div class="card-body">
<h6 class="text-muted">Paid TO Etera</h6>
<h4 class="text-success">{{ number_format($summary['paid_to_etera'], 2) }} ETB</h4>
<small class="text-muted">Invoices Paid</small>
</div>
</div>
</div>

<div class="col-12 mb-3">
<div class="card shadow-sm border-start border-primary border-4">
<div class="card-body">
<h6 class="text-muted">Total Paid TO Etera</h6>
<h4 class="text-primary">{{ number_format($summary['total_paid_to_etera'], 2) }} ETB</h4>
<small class="text-muted">Total Outgoing</small>
</div>
</div>
</div>

</div>
</div>

</div>

{{-- ================= CLAIM OFFICERS & COMPANY TOTALS ================= --}}
@if($user->role === 'insurance')
<div class="card shadow-sm mb-4">
<div class="card-body">
<h5 class="mb-3">Claim Officers Balance (Owed TO Etera)</h5>

<div class="table-responsive">
<table class="table align-middle mb-0">
<thead class="table-light">
<tr>
<th>Claim Officer</th>
<th>Phone</th>
<th class="text-end">Pending TO Etera</th>
<th class="text-end">Paid TO Etera</th>
</tr>
</thead>
<tbody>
@forelse($agents as $agent)
<tr>
<td>{{ $agent['name'] }}</td>
<td>{{ $agent['phone_number'] ?? 'N/A' }}</td>
<td class="text-end text-warning fw-bold">{{ number_format($agent['pending_to_etera'], 2) }} ETB</td>
<td class="text-end text-success fw-bold">{{ number_format($agent['paid_to_etera'], 2) }} ETB</td>
</tr>
@empty
<tr>
<td colspan="4" class="text-center text-muted">No claim officers found.</td>
</tr>
@endforelse
</tbody>
<tfoot class="table-light">
<tr class="fw-bold">
<td colspan="2">Insurance Company Total</td>
<td class="text-end text-warning">{{ number_format($companyTotals['pending_to_etera'], 2) }} ETB</td>
<td class="text-end text-success">{{ number_format($companyTotals['paid_to_etera'], 2) }} ETB</td>
</tr>
</tfoot>
</table>
</div>

<div class="row text-center mt-4">
<div class="col-md-6 mb-3">
<div class="card shadow-sm border-start border-danger border-4">
<div class="card-body">
<h6 class="text-muted">Total Pending TO Etera</h6>
<h4 class="text-danger">{{ number_format($companyTotals['pending_to_etera'], 2) }} ETB</h4>
<small class="text-muted">Company + All Claim Officers (Unpaid)</small>
</div>
</div>
</div>

<div class="col-md-6 mb-3">
<div class="card shadow-sm border-start border-success border-4">
<div class="card-body">
<h6 class="text-muted">Total Paid TO Etera</h6>
<h4 class="text-success">{{ number_format($companyTotals['paid_to_etera'], 2) }} ETB</h4>
<small class="text-muted">Company + All Claim Officers (Paid)</small>
</div>
</div>
</div>
</div>

</div>
</div>
@endif

{{-- ================= TRANSACTIONS ================= --}}
<div class="row" id="transactionCards"></div>

{{-- ================= RECEIPT MODAL ================= --}}
<div class="modal fade" id="txModal" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content" id="printArea">

<div class="modal-header">
<h5 class="modal-title">Transaction Receipt</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<div class="d-flex justify-content-between mb-3">
<div>
<strong>Etera</strong><br>
TIN: 123456789<br>
Phone: +251 911 000 000<br>
Address: Addis Ababa, Ethiopia
</div>

<div class="text-end">
<strong>Paid User</strong><br>
<span id="uName"></span><br>
Phone: <span id="uPhone"></span>
</div>
</div>

<hr>

<table class="table">
<tr><th>Net Amount</th><td id="netVal"></td></tr>
<tr><th>VAT (15%)</th><td id="vatVal"></td></tr>
<tr class="fw-bold"><th>Gross Total</th><td id="grossVal"></td></tr>
</table>
</div>

<div class="modal-footer">
<button class="btn btn-primary" onclick="printReceipt()">Print</button>
<button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>

</div>
</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

const username = @json($user->name ?? '');
const transactions = @json($transactionsArray);

function renderTransactionCards(data){
const container = document.getElementById('transactionCards');
container.innerHTML='';

data.forEach(t=>{
const amountLabel = t.amount>0
? `<span class="text-success fw-bold">+${t.amount.toFixed(2)} ETB</span>`
: `<span class="text-danger fw-bold">-${Math.abs(t.amount).toFixed(2)} ETB</span>`;

const statusLabel = `<small class="badge ${t.is_paid?'bg-success':'bg-warning text-dark'}">
${t.is_paid?'Paid':'Pending'}</small>`;

container.innerHTML+=`
<div class="col-12 mb-3">
<div class="card shadow-sm h-100">
<div class="card-body d-flex justify-content-between align-items-center flex-wrap">
<div>
<strong>${new Date(t.date).toLocaleString()}</strong><br>
<small class="text-muted">${t.type}</small><br>
<strong>${t.reference}</strong><br>
<small>User: ${username}</small>
</div>

<div class="text-end mt-2 mt-md-0">
<button class="btn btn-sm btn-outline-primary mb-1"
onclick='viewDetails(${JSON.stringify(t)})'>
View Details
</button>
<div>${amountLabel}</div>
<div class="mt-1">${statusLabel}</div>
</div>

</div>
</div>
</div>`;
});
}

function viewDetails(t){
const net = Math.abs(t.amount)/1.15;
const vat = net * 0.15;
const gross = net + vat;

uName.innerText = username;
uPhone.innerText = t.user_phone || 'N/A';
netVal.innerText = net.toFixed(2)+' ETB';
vatVal.innerText = vat.toFixed(2)+' ETB';
grossVal.innerText = gross.toFixed(2)+' ETB';

new bootstrap.Modal(txModal).show();
}

function printReceipt(){
const w = window.open('','','width=800,height=600');
w.document.write(`<html><body>${printArea.innerHTML}</body></html>`);
w.print();
w.close();
}

renderTransactionCards(transactions);

});
</script>

@endsection