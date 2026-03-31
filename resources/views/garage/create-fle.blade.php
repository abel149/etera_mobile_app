@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Garage File</h2>
    <form method="POST" action="{{ route('garage.create-fle') }}" enctype="multipart/form-data">
        @csrf
        <!-- Example Dropdown -->
        <div class="mb-3">
            <label for="fileType" class="form-label">File Type</label>
            <select class="form-select" id="fileType" name="fileType" required>
                <option value="">Select Type</option>
                <option value="report">Report</option>
                <option value="invoice">Invoice</option>
                <option value="estimate">Estimate</option>
            </select>
        </div>
        <!-- Example HH:MM Timer -->
        <div class="mb-3">
            <label for="time" class="form-label">Time (HH:MM)</label>
            <input type="time" class="form-control" id="time" name="time" required>
        </div>
        <!-- File Upload -->
        <div class="mb-3">
            <label for="file" class="form-label">Upload File</label>
            <input class="form-control" type="file" id="file" name="file" required>
        </div>
        <button type="submit" class="btn btn-primary">Request Proforma</button>
    </form>
</div>
@endsection
