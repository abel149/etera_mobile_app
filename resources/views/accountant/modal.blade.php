@extends('layouts.accountant')

@section('content')
<div class="container">

    <h3 class="mb-3 text-capitalize">{{ $type }} Transactions</h3>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>User</th>
                <th>Amount</th>
                <th>Reference</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($items as $item)
            <tr>
                <td>{{ $item->user->name }}</td>
                <td>{{ $item->amount }}</td>

                <td>
                    @if ($type == "insurance")
                        Requested File: {{ $item->proforma->file_number }}
                    @else
                        Filled Application #{{ $item->application_id }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <form action="{{ route('finance.markPaid') }}" method="POST">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

        <button class="btn btn-success">
            Mark All as Paid
        </button>
    </form>

</div>
@endsection
