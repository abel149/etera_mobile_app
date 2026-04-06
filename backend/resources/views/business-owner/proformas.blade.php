@extends('layouts.business-owner')
@section('content')

<style type="text/css">
.table td:last-child {
    white-space: nowrap;
    width: 1%;
}
</style>

<h3 class="">Received Proforma</h3>
<div class="row row-cols-12 row-cols-lg-12 row-cols-xl-12">
    <div class="col mx-auto">
        <div class="my-5 my-lg-0 shadow-none">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="table-responsive lead-table">
                        <table class="table mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>File #</th>
                                    <th>Customer Name</th>
                                    <th>Car Brand</th>
                                    <th>Model</th>
                                    <th>Year</th>
                                    <th>License Plate</th>
                                    <th>Phone #</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($proformas as $proforma)
                                <tr>
                                    <td>#{{ $proforma->file_number }}</td>
                                    <td>{{ $proforma->customer_name }}</td>
                                    <td>{{ $proforma->brand->name }}</td>
                                    <td>{{ $proforma->model }}</td>
                                    <td>{{ $proforma->year }}</td>
                                    <td>{{ $proforma->license_plate_number }}</td>
                                    <td>{{ $proforma->customer_phone_number }}</td>
                                    <td>{{ $proforma->status }}</td>
                                    <td>
                                        <a href="{{ url('/business-owner/proforma-details?proforma_id='.$proforma->id) }}" 
                                           class="btn btn-outline-primary radius-30">
                                           <i class="bx bx-show me-0"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
