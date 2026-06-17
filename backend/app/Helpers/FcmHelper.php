<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FcmHelper
{
    private static ?string $accessToken = null;
    private static ?int $tokenExpiry = null;

    /**
     * Get OAuth2 access token from service account credentials.
     * Caches the token until it expires.
     */
    private static function getAccessToken(): ?string
    {
        // Return cached token if still valid
        if (self::$accessToken && self::$tokenExpiry && time() < self::$tokenExpiry) {
            return self::$accessToken;
        }

        $credentialsPath = config('services.fcm.credentials_path');
        if (!$credentialsPath || !file_exists($credentialsPath)) {
            Log::error('FCM service account credentials file not found', ['path' => $credentialsPath]);
            return null;
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);
        if (!$credentials) {
            Log::error('FCM service account credentials file is invalid JSON');
            return null;
        }

        $projectId = $credentials['project_id'] ?? null;
        if (!$projectId) {
            Log::error('FCM service account missing project_id');
            return null;
        }

        // Create JWT assertion
        $header = ['typ' => 'JWT', 'alg' => 'RS256'];
        $now = time();
        $payload = [
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        $signatureInput = $headerEncoded . '.' . $payloadEncoded;

        // Sign with private key
        openssl_sign($signatureInput, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);
        $signatureEncoded = self::base64UrlEncode($signature);

        $jwt = $signatureInput . '.' . $signatureEncoded;

        // Exchange JWT for access token
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (!$response->successful()) {
            Log::error('FCM OAuth2 token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();
        self::$accessToken = $data['access_token'] ?? null;
        self::$tokenExpiry = $now + ($data['expires_in'] ?? 3600) - 60; // Refresh 1 min before expiry

        return self::$accessToken;
    }

    /**
     * Base64 URL-safe encoding.
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Send a push notification to a single device token using HTTP v1 API.
     *
     * Requires:
     * 1. Service account JSON file from Firebase Console
     * 2. FCM_CREDENTIALS_PATH in .env pointing to the JSON file
     */
    public static function send(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            return false;
        }

        if (empty($deviceToken)) {
            Log::error('FCM device token is empty');
            return false;
        }

        $projectId = config('services.fcm.project_id');
        if (!$projectId) {
            Log::error('FCM project_id not configured in services.fcm.project_id');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => $data,
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'channel_id' => 'etera_channel',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ],
                ],
            ]);

            if (!$response->successful()) {
                Log::error('FCM HTTP v1 send failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'token'  => substr($deviceToken, 0, 20) . '...',
                ]);
            }

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('FCM send exception', ['error' => $e->getMessage()]);
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
