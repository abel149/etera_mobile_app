@extends('layouts.admin')

@section('content')
<div class="page-wrapper">
<div class="page-content">
<h2 class="mb-4">Transactions & Activities</h2>

{{-- Server-side filter form --}}
<div class="card mb-4 shadow-sm">
<div class="card-body">
<form method="GET" action="{{ route('admin.transactions.index') }}" id="filterForm">
<div class="row g-3 align-items-end">

    <div class="col-md-2">
        <label class="form-label">From</label>
        <input type="date" name="from" id="fromDate" class="form-control"
               value="{{ $filters['from'] }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">To</label>
        <input type="date" name="to" id="toDate" class="form-control"
               value="{{ $filters['to'] }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Search by name or phone</label>
        <input type="text" name="search" id="searchInput" class="form-control"
               placeholder="Name or phone number…"
               value="{{ $filters['search'] }}">
    </div>

    <div class="col-md-4 d-flex gap-2 align-items-end flex-wrap">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">Clear</a>

        <div class="btn-group ms-auto">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="setPreset('today')">Today</button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="setPreset('month')">This Month</button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="setPreset('year')">This Year</button>
        </div>
    </div>

</div>
</form>
</div>
</div>

@if($filters['from'] || $filters['to'] || $filters['search'])
<div class="alert alert-info py-2 mb-3">
    Showing filtered results
    @if($filters['from']) from <strong>{{ $filters['from'] }}</strong>@endif
    @if($filters['to']) to <strong>{{ $filters['to'] }}</strong>@endif
    @if($filters['search']) matching <strong>"{{ $filters['search'] }}"</strong>@endif
    — <strong>{{ count($transactions) }}</strong> record(s) found.
</div>
@endif

{{-- Summary --}}
<div class="row mb-4" id="summaryCards"></div>

{{-- Transactions --}}
<div class="row" id="transactionCards"></div>

</div>
</div>

{{-- Modal --}}
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
<strong id="cName">Etera</strong><br>
TIN: <span id="cTin">123456789</span><br>
Phone: <span id="cPhone">+251 911 000 000</span><br>
Address: <span id="cAddress">Addis Ababa, Ethiopia</span>
</div>
<div class="text-end">
<strong>Paid To:</strong><br>
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
const transactions = @json($transactions);
let filteredTransactions = [...transactions];

function toDateInputValue(d){
    return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');
}

function setPreset(type){
    const now = new Date();
    let from = null;
    const to = toDateInputValue(now);

    if(type==='today')      from = toDateInputValue(now);
    else if(type==='month') from = toDateInputValue(new Date(now.getFullYear(), now.getMonth(), 1));
    else if(type==='year')  from = toDateInputValue(new Date(now.getFullYear(), 0, 1));

    if(from !== null){
        fromDate.value = from;
        toDate.value   = to;
    }

    document.getElementById('filterForm').submit();
}

function renderSummary(data){
let revenue=0,pendingInvoices=0,paidInvoices=0;
let pendingCom=0,paidCom=0,files=0,apps=0;

data.forEach(t=>{
if(t.type==='invoice' && t.amount>0){
revenue+=t.amount;
t.is_paid ? paidInvoices+=t.amount : pendingInvoices+=t.amount;
if(t.user_role==='insurance'){
files++;
}
else{ apps++;}
}

// Outgoing commissions (money Etera owes users). In wallet transactions these are usually negative amounts.
if(t.type==='commission'){
const val = Math.abs(t.amount);
if(t.is_paid === true) paidCom += val;
else if(t.is_paid === false) pendingCom += val;
}
});
const net = revenue-paidCom;

summaryCards.innerHTML = `
${card('Total Revenue',revenue,'secondary')}
${card('Paid Incoming',paidInvoices,'success')}
${card('Pending Incoming',pendingInvoices,'warning')}
${card('Pending Payouts',pendingCom,'warning')}
${card('Paid Payouts',paidCom,'success')}
${card('Total Files Returned',files,'info',false)}
${card('Total PIs Returned|Others',apps,'primary',false)}
${card('Net Revenue',net,'primary')}
`;
}

function card(t,v,c,m=true){
return `<div class="col-md-3 mb-2"><div class="card bg-${c} text-white text-center"><div class="card-body"><h6>${t}</h6><h4>${m? v.toFixed(2)+' ETB':v}</h4></div></div></div>`;
}

function renderTransactionCards(data){
transactionCards.innerHTML='';
data.forEach(t=>{
transactionCards.innerHTML+=`
<div class="col-12 mb-2">
<div class="card shadow-sm">
<div class="card-body d-flex justify-content-between">
<div>
<strong>${new Date(t.date).toLocaleString()}</strong><br>
<small>${t.type}</small><br>
<strong>${t.reference}</strong><br>
<small>User: ${t.user}</small>
</div>
<div class="text-end">
<button class="btn btn-sm btn-outline-primary mb-1"
onclick='viewDetails(${JSON.stringify(t)})'>View Details</button>
<div class="${t.amount>0?'text-success':'text-danger'} fw-bold">
${t.amount>0?'+':'-'}${Math.abs(t.amount).toFixed(2)} ETB
</div>
<span class="badge ${t.is_paid?'bg-success':'bg-warning text-dark'}">
${t.is_paid?'Paid':'Pending'}
</span>
</div>
</div>
</div>
</div>`;
});
}

function viewDetails(t){

const net = Math.abs(t.amount)/1.15;
const vat = net*0.15;
const gross = net+vat;

uName.innerText=t.user;
uPhone.innerText=t.user_phone||'N/A';
netVal.innerText=net.toFixed(2)+' ETB';
vatVal.innerText=vat.toFixed(2)+' ETB';
grossVal.innerText=gross.toFixed(2)+' ETB';

new bootstrap.Modal(txModal).show();
}

function printReceipt(){
const w=window.open('','','width=800,height=600');
w.document.write(`<html><body>${printArea.innerHTML}</body></html>`);
w.print();
w.close();
}

renderSummary(filteredTransactions);
renderTransactionCards(filteredTransactions);

</script>

@endsection
