@extends('layouts.sparepart')

@section('content')
<div class="container py-4">

<h2 class="mb-4">My Balance & Transactions</h2>

{{-- ================= SUMMARY ================= --}}
@if(in_array($user->role, ['shop', 'operator']))
<div class="row mb-4 justify-content-center">
<div class="col-md-6">
<div class="row text-center">

<div class="col-6 mb-3">
<div class="card shadow-sm border-start border-warning border-4">
<div class="card-body">
<h6 class="text-muted">Pending FROM Etera</h6>
<h4 class="text-warning">{{ number_format($summary['pending_from_etera'] ?? 0, 2) }} ETB</h4>
<small class="text-muted">Unpaid Commissions</small>
</div>
</div>
</div>

<div class="col-6 mb-3">
<div class="card shadow-sm border-start border-success border-4">
<div class="card-body">
<h6 class="text-muted">Paid FROM Etera</h6>
<h4 class="text-success">{{ number_format($summary['paid_from_etera'] ?? 0, 2) }} ETB</h4>
<small class="text-muted">Commissions Received</small>
</div>
</div>
</div>

<div class="col-12 mb-3">
<div class="card shadow-sm border-start border-primary border-4">
<div class="card-body">
<h6 class="text-muted">Total Revenue</h6>
<h4 class="text-primary">{{ number_format($summary['total_earned_from_etera'] ?? 0, 2) }} ETB</h4>
<small class="text-muted">Commissions Earned</small>
</div>
</div>
</div>

</div>
</div>
</div>

@elseif($user->role === 'garage')
<div class="row mb-4">

<div class="col-md-6">
<div class="row text-center">

<div class="col-6 mb-3">
<div class="card shadow-sm border-start border-warning border-4">
<div class="card-body">
<h6 class="text-muted">Pending FROM Etera</h6>
<h4 class="text-warning">{{ number_format($summary['pending_from_etera'] ?? 0, 2) }} ETB</h4>
<small class="text-muted">Unpaid Commissions</small>
</div>
</div>
</div>

<div class="col-6 mb-3">
<div class="card shadow-sm border-start border-success border-4">
<div class="card-body">
<h6 class="text-muted">Paid FROM Etera</h6>
<h4 class="text-success">{{ number_format($summary['paid_from_etera'] ?? 0, 2) }} ETB</h4>
<small class="text-muted">Commissions Received</small>
</div>
</div>
</div>

<div class="col-12 mb-3">
<div class="card shadow-sm border-start border-primary border-4">
<div class="card-body">
<h6 class="text-muted">Total Revenue</h6>
<h4 class="text-primary">{{ number_format($summary['total_earned_from_etera'] ?? 0, 2) }} ETB</h4>
<small class="text-muted">Commissions Earned</small>
</div>
</div>
</div>

</div>
</div>

<div class="col-md-6">
<div class="row text-center">

<div class="col-6 mb-3">
<div class="card shadow-sm border-start border-warning border-4">
<div class="card-body">
<h6 class="text-muted">Pending TO Etera</h6>
<h4 class="text-warning">{{ number_format($summary['pending_to_etera'] ?? 0, 2) }} ETB</h4>
<small class="text-muted">Invoices Unpaid</small>
</div>
</div>
</div>

<div class="col-6 mb-3">
<div class="card shadow-sm border-start border-danger border-4">
<div class="card-body">
<h6 class="text-muted">Paid TO Etera</h6>
<h4 class="text-danger">{{ number_format($summary['paid_to_etera'] ?? 0, 2) }} ETB</h4>
<small class="text-muted">Invoices Paid</small>
</div>
</div>
</div>

<div class="col-12 mb-3">
<div class="card shadow-sm border-start border-primary border-4">
<div class="card-body">
<h6 class="text-muted">Total Paid TO Etera</h6>
<h4 class="text-primary">{{ number_format($summary['total_paid_to_etera'] ?? 0, 2) }} ETB</h4>
<small class="text-muted">Total Outgoing</small>
</div>
</div>
</div>

</div>
</div>

</div>
@endif

{{-- ================= TRANSACTIONS ================= --}}
<div class="row" id="transactionCards"></div>
</div>

{{-- ================= MODAL ================= --}}
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
document.addEventListener("DOMContentLoaded", function(){

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
<div class="card shadow-sm">
<div class="card-body d-flex justify-content-between align-items-center">
<div>
<strong>${new Date(t.date).toLocaleString()}</strong><br>
<small>${t.type}</small><br>
<strong>${t.reference}</strong><br>
<small>User: ${username}</small>
</div>

<div class="text-end">
<button class="btn btn-sm btn-outline-primary mb-1"
onclick='viewDetails(${JSON.stringify(t)})'>
View Details
</button>
<div>${amountLabel}</div>
<div>${statusLabel}</div>
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