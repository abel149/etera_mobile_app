<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TermsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()
            && Auth::user()->terms_agreed_at === null
            && !$request->is('terms-agree')
            && !$request->is('logout')
            && !$request->ajax()
            && !$request->wantsJson()) {
            return redirect('/terms-agree');
        }

        return $next($request);
    }
}
