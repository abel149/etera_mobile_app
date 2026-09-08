<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class BusinessOwnerMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Please login again!');
        }

        if (Auth::user()->role !== \App\Models\User::ROLE_BUSINESS_OWNER) {
            Auth::logout();
            return redirect('/login')->with('error', 'Please login again!');
        }

        return $next($request);
    }
}
