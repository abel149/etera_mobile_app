<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TelegramConnectMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()
            && empty(Auth::user()->telegram_chat_id)
            && !session('telegram_skipped')
            && $request->isMethod('GET')
            && !$request->is('telegram-connect')
            && !$request->is('telegram-skip')
            && !$request->is('telegram*')
            && !$request->is('terms-agree')
            && !$request->is('logout')
            && !$request->ajax()
            && !$request->wantsJson()
            && app(\App\Services\TelegramService::class)->isConfigured()) {
            return redirect('/telegram-connect');
        }

        return $next($request);
    }
}
