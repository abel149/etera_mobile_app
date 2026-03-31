<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop and Brand Relationship Debugger</title>
    <link rel="icon" href="{{asset('favicon.ico')}}" type="image/x-icon"/>
    <link rel="icon" href="{{asset('assets/images/icon.jpg')}}" type="image/jpeg"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 2rem; background-color: #f8f9fa; }
        .debugger-container { max-width: 1200px; margin: auto; }
        .card-header { font-weight: bold; }
        .no-brands { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="debugger-container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                Shop & Brand Relationship Debugger
            </div>
            <div class="card-body">
                <p class="lead">This page helps you verify that your "Spare Part Shop" users are correctly associated with the "Brands" they serve.</p>
                <p>If a shop doesn't have the correct brands listed next to its name, it will not receive notifications for proformas of that brand. You will need to edit that shop user in your admin panel and assign the correct brands.</p>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Shops and the Brands They Serve
                    </div>
                    <ul class="list-group list-group-flush">
                        @if($shops->isEmpty())
                            <li class="list-group-item">No users with the role 'shop' found.</li>
                        @else
                            @foreach($shops as $shop)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Shop:</strong> {{ $shop->name }} <small class="text-muted">(ID: {{ $shop->id }})</small>
                                    </div>
                                    <div>
                                        <strong>Serves Brands:</strong>
                                        @if($shop->brands->isEmpty())
                                            <span class="badge bg-warning text-dark">NONE ASSIGNED</span>
                                        @else
                                            @foreach($shop->brands as $brand)
                                                <span class="badge bg-success">{{ $brand->name }}</span>
                                            @endforeach
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        All Available Brands
                    </div>
                     <ul class="list-group list-group-flush">
                        @if($brands->isEmpty())
                            <li class="list-group-item">No brands found in the database.</li>
                        @else
                            @foreach($brands as $brand)
                                <li class="list-group-item">{{ $brand->name }} <small class="text-muted">(ID: {{ $brand->id }})</small></li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 