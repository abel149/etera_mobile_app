<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Register all the broadcast channels that your application supports.
|
*/

// Public channels (no auth needed)
// 'proformas' — public channel for all proforma status changes
// 'admin-proformas' — public channel for admin dashboard

// Private channel — user-specific notifications
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
