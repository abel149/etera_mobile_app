<h2 style="color: #2c3e50;">New Proforma Available – #{{ $proforma->file_number }}</h2>

<p>Hello,</p>

<p>A new proforma has been published that matches one of the brands you serve. Here are the details:</p>

<table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold; width: 35%;">File Number</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{ $proforma->file_number }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;">Customer</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{ $proforma->customer_name }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;">Brand</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{ $proforma->brand?->name }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;">Vehicle Model</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{ $proforma->model }} ({{ $proforma->year }})</td>
    </tr>
</table>

@if($proforma->parts && $proforma->parts->count() > 0)
<h3 style="color: #2c3e50;">Parts Needed</h3>
<table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
    <thead>
        <tr style="background: #3498db; color: #fff;">
            <th style="padding: 8px; text-align: left;">#</th>
            <th style="padding: 8px; text-align: left;">Part / Component</th>
            <th style="padding: 8px; text-align: center;">Qty</th>
            <th style="padding: 8px; text-align: left;">Condition</th>
        </tr>
    </thead>
    <tbody>
        @foreach($proforma->parts as $index => $part)
        <tr style="background: {{ $index % 2 == 0 ? '#fff' : '#f8f9fa' }};">
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $index + 1 }}</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $part->component ?: $part->number }}</td>
            <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">{{ $part->quantity ?? 1 }}</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $part->condition ?? 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<p>Log in to your account to submit your price quote for this proforma.</p>

<p style="color: #7f8c8d; font-size: 12px;">Thank you for using etera.</p>
