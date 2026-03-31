@extends('layouts.admin')

@section('content')
<div class="page-wrapper">
<div class="page-content">
<h2 class="mb-4">Transactions & Activities</h2>

{{-- Filters --}}
<div class="card mb-4 shadow-sm">
<div class="card-body">
<div class="row g-3 align-items-end">
<div class="col-md-3">
<label class="form-label">From</label>
<input type="datetime-local" id="fromDate" class="form-control">
</div>
<div class="col-md-3">
<label class="form-label">To</label>
<input type="datetime-local" id="toDate" class="form-control">
</div>
<div class="col-md-6 text-md-end">
<label class="form-label d-block">Quick Filters</label>
<div class="btn-group">
<button class="btn btn-outline-primary" onclick="setPreset('all')">All</button>
<button class="btn btn-outline-primary" onclick="setPreset('today')">Today</button>
<button class="btn btn-outline-primary" onclick="setPreset('month')">This Month</button>
<button class="btn btn-outline-primary" onclick="setPreset('year')">This Year</button>
</div>
</div>
</div>
</div>
</div>

{{-- Summary --}}
<div class="row mb-4" id="summaryCards"></div>

{{-- Transactions --}}
<div class="row" id="transactionCards"></div>
<hr>
<h6 class="mt-4">Debug: Raw Transactions</h6>
<pre id="debugOutput"
     style="max-height:400px; overflow:auto; background:#111; color:#0f0; padding:10px; font-size:12px;">
</pre>

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

function startOfDay(d){ return new Date(d.getFullYear(),d.getMonth(),d.getDate()); }
function endOfDay(d){ return new Date(d.getFullYear(),d.getMonth(),d.getDate(),23,59,59); }

function applyFilter(){
const from = fromDate.value;
const to = toDate.value;

filteredTransactions = transactions.filter(t=>{
const d = new Date(t.date);
if(from && d < new Date(from)) return false;
if(to && d > new Date(to)) return false;
return true;
});
renderSummary(filteredTransactions);
renderTransactionCards(filteredTransactions);
}

function setPreset(type){
const now = new Date();
let from = null, to = endOfDay(now);

if(type==='today') from=startOfDay(now);
else if(type==='month') from=new Date(now.getFullYear(),now.getMonth(),1);
else if(type==='year') from=new Date(now.getFullYear(),0,1);
else { fromDate.value=''; toDate.value=''; applyFilter(); return; }

fromDate.value = from.toISOString().slice(0,16);
toDate.value = to.toISOString().slice(0,16);
applyFilter();
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

fromDate.addEventListener('change',applyFilter);
toDate.addEventListener('change',applyFilter);

renderSummary(filteredTransactions);
renderTransactionCards(filteredTransactions);
document.getElementById('debugOutput').textContent =
    JSON.stringify(filteredTransactions, null, 2);

</script>

@endsection
