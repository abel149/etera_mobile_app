<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmHelper
{
    /**
     * Send a push notification to a single device token.
     *
     * Requires FCM_SERVER_KEY in .env
     * (Settings → Cloud Messaging → Legacy server key in Firebase Console)
     */
    public static function send(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        $serverKey = config('services.fcm.server_key');
        if (empty($serverKey) || empty($deviceToken)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type'  => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to'           => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                    'sound' => 'default',
                    'badge' => 1,
                ],
                'data'         => $data,
                'priority'     => 'high',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('FCM send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send to multiple tokens at once.
     */
    public static function sendMulti(array $deviceTokens, string $title, string $body, array $data = []): void
    {
        foreach (array_filter($deviceTokens) as $token) {
            static::send($token, $title, $body, $data);
        }
    }
}
