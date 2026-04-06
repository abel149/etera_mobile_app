<h2>Server Error Detected</h2>

<p><strong>Message:</strong> {{ $errorMessage }}</p>
<p><strong>Status:</strong> {{ $status }}</p>
<p><strong>URL:</strong> {{ $url }}</p>
<p><strong>Method:</strong> {{ $method }}</p>

<p><strong>User:</strong> {{ $user['email'] ?? 'Guest' }}</p>

<hr>

<pre style="background:#111;color:#0f0;padding:10px">
{{ $trace }}
</pre>

