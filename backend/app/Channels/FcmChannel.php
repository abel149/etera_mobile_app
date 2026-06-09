<?php

namespace App\Channels;

use App\Helpers\FcmHelper;
use Illuminate\Notifications\Notification;

class FcmChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        $token = $notifiable->device_token ?? null;
        if (empty($token) || !method_exists($notification, 'toFcm')) {
            return;
        }

        [$title, $body, $data] = $notification->toFcm($notifiable);
        FcmHelper::send($token, $title, $body, $data);
    }
}
