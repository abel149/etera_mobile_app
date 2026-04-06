@extends('layouts.marketer')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Add Spare Parts Shop</h5>
                <hr/>

                <form class="row g-3" action="{{ route('add-shop.marketer') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input name="name" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input name="phone_number" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tin #</label>
                        <input name="tin_number" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Location</label>
                        <input name="location" class="form-control">
                    </div>

                    <!-- ✅ BRANDS -->
                    <div class="col-md-6">
                        <label class="form-label">Car Brands To Serve</label>
                        <select name="brands[]" id="brands-select" class="form-select" multiple required>
                            <option value="all">Select All</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">License Image</label>
                        <input type="file" name="license_image" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Stamp Image</label>
                        <input type="file" name="stamp_image" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input name="email" type="email" class="form-control">
                    </div>

                    <hr>

                    <button class="btn btn-primary">Add</button>
                    <a href="/marketer/spare-part-shops" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    const $select = $('#brands-select');

    $select.select2({
        placeholder: "Select car brands",
        closeOnSelect: false,
        width: '100%'
    });

    /**
     * ✅ HANDLE SELECT ALL RELIABLY
     */
    $select.on('change', function () {
        let values = $select.val() || [];

        // If "Select All" was chosen
        if (values.includes('all')) {

            // Remove "all" immediately
            values = values.filter(v => v !== 'all');

            const allBrandValues = $select.find('option')
                .not('[value="all"]')
                .map(function () {
                    return this.value;
                }).get();

            // 🔁 TOGGLE LOGIC
            if (values.length === allBrandValues.length) {
                // 🔴 All selected → CLEAR
                $select.val([]).trigger('change.select2');
            } else {
                // 🟢 Select ALL
                $select.val(allBrandValues).trigger('change.select2');
            }
        }
    });

});
</script>

@endsection
