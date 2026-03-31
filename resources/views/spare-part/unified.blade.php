@extends('layouts.sparepart')
@section('unified')
class="current"
@endsection
@section('content')
<!-- Spacer -->
<div class="margin-top-45 margin-bottom-45"></div>
<!-- Spacer / End-->

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex gap-2">
                    <span class="badge bg-primary">Insurance Proformas</span>
                    <span class="badge bg-secondary">Other Proformas</span>
                </div>
            </div>
            
            <livewire:unified-proforma-list />
        </div>
    </div>
</div>

@endsection
