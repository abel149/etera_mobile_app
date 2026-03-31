{{-- CSRF Token Auto-Refresh --}}
{{-- Prevents 419 errors by refreshing the CSRF token every 15 minutes --}}
<script>
(function() {
    var CSRF_REFRESH_INTERVAL = 15 * 60 * 1000; // 15 minutes

    function refreshCsrfToken() {
        fetch('/csrf-token', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.token) {
                // Update meta tag
                var meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', data.token);

                // Update all hidden _token inputs
                document.querySelectorAll('input[name="_token"]').forEach(function(el) {
                    el.value = data.token;
                });

                // Update Axios if available
                if (window.axios) {
                    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = data.token;
                }

                // Update jQuery if available
                if (window.jQuery) {
                    jQuery.ajaxSetup({ headers: { 'X-CSRF-TOKEN': data.token } });
                }
            }
        })
        .catch(function() { /* silently ignore – next interval will retry */ });
    }

    setInterval(refreshCsrfToken, CSRF_REFRESH_INTERVAL);

    // Also refresh on visibility change (user returns to tab)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) refreshCsrfToken();
    });
})();
</script>
