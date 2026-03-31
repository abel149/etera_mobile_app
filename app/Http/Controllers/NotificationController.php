<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAllRead(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }
        return redirect()->back();
    }
} 