<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel App') }}</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- FontAwesome (optional) --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- CSRF Token for AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('styles')

    @include('partials.green-theme')
</head>

<body>

{{-- NAVBAR --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <a class="navbar-brand" href="/">
            {{ config('app.name', 'Laravel') }}
        </a>

        <div class="ms-auto d-flex align-items-center gap-2">
            <a class="nav-link dark-mode-icon" href="javascript:;" aria-label="Toggle dark mode">
                <i class="fa-solid fa-moon"></i>
            </a>

            @auth
                <span class="text-white me-3">
                    {{ auth()->user()->email }}
                </span>

                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-danger">
                        Logout
                    </button>
                </form>
            @endauth
        </div>

    </div>
</nav>


{{-- PAGE CONTENT --}}
<div class="container mt-4">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @yield('content')

</div>


{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

{{-- jQuery (useful for AJAX log viewer) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- Global AJAX CSRF --}}
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content')
    }
});
</script>

@stack('scripts')

</body>
</html>
