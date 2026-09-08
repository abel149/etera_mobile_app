<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    /**
     * Handle incoming Telegram webhook (bot updates).
     * When a user sends /start {userId}, we save their chat_id.
     */
    public function handle(Request $request)
    {
        $update = $request->all();

        Log::info('Telegram webhook received', ['update' => $update]);

        $callback = $update['callback_query'] ?? null;
        if ($callback) {
            $callbackId = $callback['id'] ?? null;
            $data = $callback['data'] ?? '';
            $chatId = $callback['message']['chat']['id'] ?? null;
            $messageId = $callback['message']['message_id'] ?? null;

            if ($callbackId) {
                try {
                    app(TelegramService::class)->answerCallbackQuery((string) $callbackId);
                } catch (\Throwable $e) {
                    Log::warning('Telegram callback: answerCallbackQuery failed', ['error' => $e->getMessage()]);
                }
            }

            if (is_string($data) && strpos($data, 'pw_reject:') === 0) {
                $identifier = substr($data, strlen('pw_reject:'));

                if (!empty($identifier)) {
                    $record = DB::table('password_reset_tokens')->where('email', $identifier)->first();
                    if ($record) {
                        DB::table('password_reset_tokens')->where('email', $identifier)->delete();
                    }
                }

                if ($chatId && $messageId) {
                    try {
                        app(TelegramService::class)->deleteMessage((string) $chatId, (int) $messageId);
                    } catch (\Throwable $e) {
                        Log::warning('Telegram callback: deleteMessage failed', ['error' => $e->getMessage()]);
                    }
                }

                return response()->json(['ok' => true]);
            }

            if (is_string($data) && strpos($data, 'hear_about_us_') === 0) {
                $option = substr($data, strlen('hear_about_us_'));

                $labels = [
                    'facebook'  => 'Facebook',
                    'instagram' => 'Instagram',
                    'tiktok'    => 'TikTok',
                    'others'    => 'Others',
                ];
                $label = $labels[$option] ?? ucfirst($option);

                // Find user by chat ID
                $responder = $chatId ? User::where('telegram_chat_id', (string) $chatId)->first() : null;

                // Record the response (only once per user per option)
                $alreadyAnswered = $responder
                    ? \App\Models\SentEmail::where('user_id', $responder->id)
                        ->where('type', 'telegram_survey_response')
                        ->exists()
                    : false;

                if (!$alreadyAnswered) {
                    \App\Models\SentEmail::log(
                        'telegram_survey_response',
                        'via-telegram',
                        $responder?->name,
                        $responder?->id,
                        null,
                        $label,
                        'sent'
                    );
                }

                // Reply with thank-you + a login_url button (opens in Chrome/Safari, not Telegram WebView)
                if ($chatId) {
                    try {
                        $dashboardUrl = match($responder?->role) {
                            'garage'    => config('app.url') . '/garage/',
                            'shop'      => config('app.url') . '/spare-part-shops/proformas',
                            'admin'     => config('app.url') . '/admin',
                            'insurance' => config('app.url') . '/insurance',
                            'others'    => config('app.url') . '/business-owner',
                            'marketer'  => config('app.url') . '/marketer',
                            'operator'  => config('app.url') . '/operator/dashboard',
                            'employee'  => config('app.url') . '/employee',
                            default     => config('app.url') . '/',
                        };

                        app(TelegramService::class)->sendMessageWithLoginUrlButton(
                            (string) $chatId,
                            "🙏 <b>Thank you for letting us know!</b>",
                            "🏠 Go to My Account →",
                            $dashboardUrl
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Telegram survey: reply failed', ['error' => $e->getMessage()]);
                    }
                }

                // Delete the survey message to keep the chat clean
                if ($chatId && $messageId) {
                    try {
                        app(TelegramService::class)->deleteMessage((string) $chatId, (int) $messageId);
                    } catch (\Throwable $e) {}
                }

                return response()->json(['ok' => true]);
            }

            if (is_string($data) && $data === 'tg_disconnect') {
                try {
                    $user = null;
                    if ($chatId) {
                        $user = User::where('telegram_chat_id', (string) $chatId)->first();
                    }

                    // Handle disconnect actions
                    if ($user) {
                        $user->update(['telegram_chat_id' => null]);
                    }

                    if ($chatId) {
                        $text = "✅ Disconnected.\n\nTo connect again: log in to your etera account and use the Telegram connect button/page.\n\nIf you lost your old Telegram, this lets you connect a new one.";
                        app(TelegramService::class)->sendMessage((string) $chatId, $text);
                    }
                } catch (\Throwable $e) {
                    Log::error('Telegram disconnect callback failed', ['error' => $e->getMessage()]);
                }

                return response()->json(['ok' => true]);
            }

            return response()->json(['ok' => true]);
        }

        // Extract message text and chat info
        $message = $update['message'] ?? null;
        if (!$message) {
            return response()->json(['ok' => true]);
        }

        $text = $message['text'] ?? '';
        $chatId = $message['chat']['id'] ?? null;
        $telegramName = $message['from']['first_name'] ?? 'User';

        if (!$chatId) {
            return response()->json(['ok' => true]);
        }

        if (is_string($text) && trim($text) === '/end') {
            try {
                $user = User::where('telegram_chat_id', (string) $chatId)->first();
                if ($user) {
                    $user->update(['telegram_chat_id' => null]);
                    app(TelegramService::class)->sendMessage((string) $chatId, "✅ Disconnected.\n\nTo connect again: log in to your etera account and use the Telegram connect button/page.");
                } else {
                    app(TelegramService::class)->sendMessage((string) $chatId, "ℹ️ This Telegram chat is not linked to any etera account.");
                }
            } catch (\Throwable $e) {
                Log::warning('Telegram /end disconnect failed', ['error' => $e->getMessage()]);
            }

            return response()->json(['ok' => true]);
        }

        if (is_string($text) && (trim($text) === '/start' || trim($text) === '/settings')) {
            try {
                $user = User::where('telegram_chat_id', (string) $chatId)->first();
                if ($user) {
                    $manageText = "⚙️ <b>Telegram Connection</b>\n\n"
                        . "This Telegram is linked to etera account (<b>{$user->name}</b>).\n\n"
                        . "To disconnect anytime, type <b>/end</b> or use the button below.";

                    app(TelegramService::class)->sendMessageWithButtons((string) $chatId, $manageText, [
                        ['text' => 'Disconnect', 'callback_data' => 'tg_disconnect'],
                       
                    ]);

                    return response()->json(['ok' => true]);
                }
            } catch (\Throwable $e) {
                Log::warning('Telegram manage connection message failed', ['error' => $e->getMessage()]);
            }
        }

        // Handle /start command with user ID payload
        if (str_starts_with($text, '/start')) {
            $parts = explode(' ', $text, 2);
            $userId = isset($parts[1]) ? intval($parts[1]) : null;

            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    // Check if this telegram account is already linked to another user
                    $existing = User::where('telegram_chat_id', (string) $chatId)
                        ->where('id', '!=', $userId)
                        ->first();
                    if ($existing) {
                        // Send error via Telegram
                        $botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN', ''));
                        if ($botToken) {
                            try {
                                app(TelegramService::class)->sendMessageWithButtons((string) $chatId, "You've registered using this account. Please use another account, or disconnect below.", [
                                    ['text' => 'Disconnect', 'callback_data' => 'tg_disconnect'],
                                ]);
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Http::post(
                                    "https://api.telegram.org/bot{$botToken}/sendMessage",
                                    [
                                        'chat_id' => $chatId,
                                        'text' => "You've registered using this account. Please use another account, or disconnect with /end.",
                                    ]
                                );
                            }
                        }
                        return response()->json(['ok' => true, 'duplicate' => true]);
                    }

                    $user->update(['telegram_chat_id' => (string) $chatId]);

                    // Send any missed billing notifications the user skipped by not being on Telegram
                    try {
                        app(TelegramService::class)->sendMissedBillingNotifications($user, (string) $chatId);
                    } catch (\Throwable $e) {
                        Log::warning('Telegram connect: sendMissedBillingNotifications failed', [
                            'user_id' => $userId,
                            'error'   => $e->getMessage(),
                        ]);
                    }

                    Log::info('Telegram chat ID linked', [
                        'user_id' => $userId,
                        'chat_id' => $chatId,
                    ]);

                    // Send confirmation via Telegram Bot API
                    $botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN', ''));
                    if ($botToken) {
                        $confirmText = "✅ <b>Connected!</b>\n\n"
                            . "Hello {$telegramName}! Your Telegram is now linked to your etera account (<b>{$user->name}</b>).\n\n"
                            . "👉 <b>Switch back to your browser tab</b> — it will automatically redirect you to your account.\n\n"
                            . "To disconnect anytime, type <b>/end</b> or use the button below.";

                        try {
                            app(TelegramService::class)->sendMessageWithButtons((string) $chatId, $confirmText, [
                                ['text' => 'Disconnect', 'callback_data' => 'tg_disconnect'],
                                
                            ]);
                        } catch (\Throwable $e) {
                            Log::warning('Telegram connect: sendMessageWithButtons failed, falling back to plain message', [
                                'user_id' => $userId,
                                'chat_id' => $chatId,
                                'error' => $e->getMessage(),
                            ]);

                            try {
                                \Illuminate\Support\Facades\Http::post(
                                    "https://api.telegram.org/bot{$botToken}/sendMessage",
                                    [
                                        'chat_id' => $chatId,
                                        'text' => $confirmText,
                                        'parse_mode' => 'HTML',
                                    ]
                                );
                            } catch (\Throwable $e2) {
                                Log::warning('Telegram connect: fallback sendMessage failed', ['error' => $e2->getMessage()]);
                            }
                        }
                    }

                    // Send "How did you hear about us?" survey once per user
                    $surveySent = \App\Models\SentEmail::where('user_id', $userId)
                        ->where('type', 'telegram_survey_sent')
                        ->exists();
                    if (!$surveySent) {
                        try {
                            app(TelegramService::class)->sendHowDidYouHearSurvey((string) $chatId);
                            \App\Models\SentEmail::log('telegram_survey_sent', 'via-telegram', $user->name, $userId, null, 'How did you hear about us?', 'sent');
                        } catch (\Throwable $e) {
                            Log::warning('Telegram connect: sendHowDidYouHearSurvey failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
                        }
                    }

                    return response()->json(['ok' => true, 'linked' => true]);
                }
            }

            // /start without valid user ID
            $botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN', ''));
            if ($botToken) {
                \Illuminate\Support\Facades\Http::post(
                    "https://api.telegram.org/bot{$botToken}/sendMessage",
                    [
                        'chat_id' => $chatId,
                        'text' => "👋 Welcome to etera Bot!\n\nPlease use the link from your etera account to connect.",
                        'parse_mode' => 'HTML',
                    ]
                );
            }
        }

        return response()->json(['ok' => true]);
    }
}
