<h2>etera – Proforma Billing Information</h2>

<p>Dear Customer,</p>

<p>Your proforma <strong>#{{ $proforma->file_number }}</strong> has been closed.</p>

<p>Below is the billing summary:</p>

<table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse;">
    <tr style="background-color: #f2f2f2;">
        <th align="left">Description</th>
        <th align="right">Amount (Birr)</th>
    </tr>
    <tr>
        <td>Service Charge</td>
        <td align="right">{{ number_format($charge, 2) }}</td>
    </tr>
    <tr>
        <td>VAT (15%)</td>
        <td align="right">{{ number_format($vatAmount, 2) }}</td>
    </tr>
    <tr style="font-weight: bold;">
        <td>Total</td>
        <td align="right">{{ number_format($total, 2) }}</td>
    </tr>
</table>

<p>Your invoice will be available once the proforma is completed.</p>

<p>Thank you for using etera!</p>
