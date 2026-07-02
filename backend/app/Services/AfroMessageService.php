<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfroMessageService
{
    public function send(string $to, string $message): bool
    {
        $token = config('services.afromessage.token');
        $from = config('services.afromessage.identifier_id');
        $sender = config('services.afromessage.sender_name');
        $callback = config('services.afromessage.callback');

        if (empty($token) || empty($from) || empty($sender)) {
            Log::warning('Afromessage is not configured.');
            return false;
        }

        try {
            $query = [
                'from' => $from,
                'sender' => $sender,
                'to' => $to,
                'message' => $message,
            ];

            if (!empty($callback)) {
                $query['callback'] = $callback;
            }

            $response = Http::withToken($token)
                ->timeout(15)
                ->get('https://api.afromessage.com/api/send', $query);

            if (!$response->ok()) {
                Log::warning('Afromessage request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            $data = $response->json();
            return data_get($data, 'acknowledge') === 'success';
        } catch (\Throwable $e) {
            Log::warning('Afromessage request exception.', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
