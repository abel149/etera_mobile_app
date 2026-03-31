@extends('layouts.app')

@section('content')

<h3>Laravel Logs</h3>

<div id="logs-container"
     style="height:600px;
            overflow:auto;
            background:black;
            color:#00ff00;
            padding:15px;
            font-family:monospace;">
Loading logs...
</div>

@endsection


@push('scripts')
<script>

function loadLogs()
{
    fetch('/logs/fetch')
    .then(response => response.json())
    .then(data => {

        document.getElementById('logs-container')
            .textContent = data.logs;

    })
    .catch(error => {
        console.error(error);
    });
}

// load first time
loadLogs();

// auto refresh every 5 seconds
setInterval(loadLogs, 5000);

</script>
@endpush
