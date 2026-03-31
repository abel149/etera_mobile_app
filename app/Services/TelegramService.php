<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected string $botUsername;
    protected string $apiBase;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN', ''));
        $this->botUsername = config('services.telegram.bot_username', env('TELEGRAM_BOT_USERNAME', ''));
        $this->apiBase = "https://api.telegram.org/bot{$this->botToken}";
    }

    public function deleteMessage(string $chatId, int $messageId): bool
    {
        if (empty($this->botToken) || empty($chatId) || empty($messageId)) {
            Log::warning('TelegramService: Missing bot token/chat ID/message ID', [
                'has_token' => !empty($this->botToken),
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
            return false;
        }

        try {
            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->retry(1, 200)
                ->post("{$this->apiBase}/deleteMessage", [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                ]);

            if ($response->successful() && $response->json('ok')) {
                Log::info('TelegramService: Message deleted', ['chat_id' => $chatId, 'message_id' => $messageId]);
                return true;
            }

            Log::warning('TelegramService: API error (deleteMessage)', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'response' => $response->json(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('TelegramService: Exception (deleteMessage)', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): bool
    {
        if (empty($this->botToken) || empty($callbackQueryId)) {
            Log::warning('TelegramService: Missing bot token or callback query id', [
                'has_token' => !empty($this->botToken),
                'callback_query_id' => $callbackQueryId,
            ]);
            return false;
        }

        try {
            $payload = [
                'callback_query_id' => $callbackQueryId,
            ];
            if ($text !== '') {
                $payload['text'] = $text;
            }
            if ($showAlert) {
                $payload['show_alert'] = true;
            }

            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->retry(1, 200)
                ->post("{$this->apiBase}/answerCallbackQuery", $payload);

            if ($response->successful() && $response->json('ok')) {
                return true;
            }

            Log::warning('TelegramService: API error (answerCallbackQuery)', [
                'response' => $response->json(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('TelegramService: Exception (answerCallbackQuery)', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send a text message to a Telegram chat.
     */
    public function sendMessage(string $chatId, string $text): bool
    {
        if (empty($this->botToken) || empty($chatId)) {
            Log::warning('TelegramService: Missing bot token or chat ID', [
                'has_token' => !empty($this->botToken),
                'chat_id' => $chatId,
            ]);
            return false;
        }

        try {
            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->retry(1, 200)
                ->post("{$this->apiBase}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                ]);

            if ($response->successful() && $response->json('ok')) {
                Log::info('TelegramService: Message sent', ['chat_id' => $chatId]);
                return true;
            }

            Log::warning('TelegramService: API error', [
                'chat_id' => $chatId,
                'response' => $response->json(),
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('TelegramService: Exception', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendMessageWithButtons(string $chatId, string $text, array $buttons, ?int &$messageId = null): bool
    {
        if (empty($this->botToken) || empty($chatId)) {
            Log::warning('TelegramService: Missing bot token or chat ID', [
                'has_token' => !empty($this->botToken),
                'chat_id' => $chatId,
            ]);
            return false;
        }

        try {
            $inlineButtons = [];
            foreach ($buttons as $button) {
                if (!isset($button['text'])) {
                    continue;
                }

                if (isset($button['url'])) {
                    $inlineButtons[] = ['text' => (string) $button['text'], 'url' => (string) $button['url']];
                    continue;
                }

                if (isset($button['callback_data'])) {
                    $inlineButtons[] = ['text' => (string) $button['text'], 'callback_data' => (string) $button['callback_data']];
                    continue;
                }
            }

            if (empty($inlineButtons)) {
                Log::warning('TelegramService: No valid buttons provided', ['chat_id' => $chatId]);
                return false;
            }

            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->retry(1, 200)
                ->post("{$this->apiBase}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        $inlineButtons,
                    ],
                ]),
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful() && $response->json('ok')) {
                $messageId = (int) $response->json('result.message_id');
                Log::info('TelegramService: Message sent (with buttons)', ['chat_id' => $chatId]);
                return true;
            }

            Log::warning('TelegramService: API error (with buttons)', [
                'chat_id' => $chatId,
                'response' => $response->json(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('TelegramService: Exception (with buttons)', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendMessageWithButton(string $chatId, string $text, string $buttonText, string $buttonUrl): bool
    {
        if (empty($this->botToken) || empty($chatId)) {
            Log::warning('TelegramService: Missing bot token or chat ID', [
                'has_token' => !empty($this->botToken),
                'chat_id' => $chatId,
            ]);
            return false;
        }

        try {
            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->retry(1, 200)
                ->post("{$this->apiBase}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => $buttonText, 'url' => $buttonUrl],
                        ],
                    ],
                ]),
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful() && $response->json('ok')) {
                Log::info('TelegramService: Message sent (with button)', ['chat_id' => $chatId]);
                return true;
            }

            Log::warning('TelegramService: API error (with button)', [
                'chat_id' => $chatId,
                'response' => $response->json(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('TelegramService: Exception (with button)', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate a Telegram deep link for user to connect their account.
     * When user clicks this link and sends /start, the bot receives the user ID.
     */
    public function generateStartLink(int $userId): string
    {
        return "https://t.me/{$this->botUsername}?start={$userId}";
    }

    /**
     * Check if the Telegram service is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->botUsername);
    }

    /**
     * Send proforma floated notification to a user.
     */
    public function sendProformaNotification(string $chatId, $proforma): bool
    {
        $brandName = $proforma->brand?->name ?? 'N/A';
        $text = "🔔 <b>New Proforma Invoice Request!</b>\n\n"
            . "📋 File: <b>{$proforma->file_number}</b>\n"
            . "🚗 Brand: {$brandName}\n"
            . "📌 Model: {$proforma->model} ({$proforma->year})\n"
            . "🪪 Plate: {$proforma->license_plate_number}\n"
            . "🔧 Type: " . ($proforma->isEteraCheretaMode() ? 'Etera Chereta' : 'Regular') . "\n\n"
            . "Login to your account to view and apply.";

        return $this->sendMessage($chatId, $text);
    }

    public function sendInboxReceivedNotification(string $chatId, $proforma): bool
    {
        $brandName = $proforma->brand?->name ?? 'N/A';
        $text = "📥 <b>New Inbox Received</b>\n\n"
            . "A new proforma has been sent to your inbox.\n\n"
            . "📋 File: <b>{$proforma->file_number}</b>\n"
            . "🚗 Brand: {$brandName}\n"
            . "📌 Model: {$proforma->model} ({$proforma->year})\n"
            . "🪪 Plate: {$proforma->license_plate_number}";

        $loginUrl = url('/login');
        if ($this->sendMessageWithButton($chatId, $text, 'Go to Login', $loginUrl)) {
            return true;
        }

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Notify all admins that a new proforma has been requested/created so they can float/publish it.
     * Sends to all approved admins/superadmins with a linked Telegram chat ID.
     */
    public function sendProformaRequestedNotificationToAdmins($proforma): void
    {
        try {
            if (!$this->isConfigured()) {
                return;
            }

            $admins = \App\Models\User::whereIn('role', ['admin', 'superadmin'])
                ->where('approved', true)
                ->whereNotNull('telegram_chat_id')
                ->get();

            if ($admins->isEmpty()) {
                Log::info('sendProformaRequestedNotificationToAdmins: No admins with linked Telegram found');
                return;
            }

            $posterName = $proforma->poster?->name ?? 'Unknown';
            $posterRole = $proforma->poster?->role ?? 'Unknown';
            $brandName = $proforma->brand?->name ?? 'N/A';
            $fileNumber = $proforma->file_number ?? $proforma->id;

            $adminUrl = url('/login');

            $text = "🆕 <b>Proforma Requested</b>\n\n"
                . "📋 File: <b>{$fileNumber}</b>\n"
                . "👤 Requested by: <b>{$posterName}</b> ({$posterRole})\n"
                . "🚗 Brand: {$brandName}\n"
                . "📌 Model: {$proforma->model} ({$proforma->year})\n"
                . "🪪 Plate: {$proforma->license_plate_number}\n\n"
                . "Please review, float, and publish in the admin panel.";

            foreach ($admins as $admin) {
                $this->sendMessageWithButton(
                    (string) $admin->telegram_chat_id,
                    $text,
                    'Go to Login',
                    $adminUrl
                );
            }

            Log::info('sendProformaRequestedNotificationToAdmins: Sent to admins', [
                'proforma_id' => $proforma->id ?? null,
                'file_number' => $proforma->file_number ?? null,
                'admin_count' => $admins->count(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('sendProformaRequestedNotificationToAdmins: Failed', [
                'proforma_id' => $proforma->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send proforma closed notification with billing info.
     */
    public function sendClosedNotification(string $chatId, $proforma): bool
    {
        $appCount = $proforma->applications()->count();
        $required = $proforma->required_number_of_shops ?: '∞';
        $text = "🔒 <b>Proforma Closed</b>\n\n"
            . "📋 File: <b>{$proforma->file_number}</b>\n"
            . "📊 Applications: {$appCount}/{$required}\n"
            . "🚗 {$proforma->brand?->name} {$proforma->model} ({$proforma->year})\n"
            . "🪪 Plate: {$proforma->license_plate_number}";

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Send proforma applications received notification with billing info.
     * Shows x/y progress for the applicant's role, with FULL or close messages.
     */
    public function sendApplicationReceivedNotification(string $chatId, $proforma, string $applicantRole = 'shop'): bool
    {
        $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);
        $requiredShops = (int) ($proforma->required_number_of_shops ?? 0);

        if ($applicantRole === 'garage' && $requiredGarages > 0) {
            $currentCount = $proforma->applications()->where('from', 'garage')->count();
            $required = $requiredGarages;
            $roleLabel = 'garages';
        } else {
            $currentCount = $proforma->applications()->where('from', 'shop')->count();
            $required = $requiredShops;
            $roleLabel = 'spare part shops';
        }

        $text = "🔔 <b>Application Received</b>\n\n"
            . "📋 A new application has been received for your proforma <b>{$proforma->file_number}</b>\n"
            . "📊 {$currentCount}/{$required} {$roleLabel} have applied.\n\n";

        if ($required > 0 && $currentCount >= $required) {
            $text .= "🚫 <b>FULL!!!</b>";
        } elseif ($required > 0 && $currentCount < $required) {
            $text .= "✅ You may request to close your proforma invoice at any time by logging in to your etera account.\n"
                . "Thank you.";
        } else {
            $text .= "You may choose to close the request at any time";
        }

        return $this->sendMessage($chatId, $text);
    }

    
    /**
     * Send a notification to admins when a pending user attempts to log in.
     * Sends to all admins with a linked Telegram chat ID.
     */
    public function sendPendingUserLoginNotification(int $userId, string $userName, ?string $userRole, ?string $email, ?string $phoneNumber): void
    {
        try {
            $admins = \App\Models\User::whereIn('role', ['admin', 'superadmin'])
                ->where('approved', true)
                ->whereNotNull('telegram_chat_id')
                ->get();

            if ($admins->isEmpty()) {
                Log::info('sendPendingUserLoginNotification: No admins with linked Telegram found');
                return;
            }

            // Build contact string
            $parts = [];
            if (!empty($email)) {
                $parts[] = $email;
            }
            if (!empty($phoneNumber)) {
                $parts[] = $phoneNumber;
            }
            $contact = count($parts) ? (' (' . implode(' / ', $parts) . ')') : '';
            $roleText = $userRole ? ('Role: ' . $userRole) : 'Role: N/A';

            $text = "🔔 <b>Pending User Login Attempt</b>\n\n"
                . "👤 <b>User:</b> {$userName}{$contact}\n"
                . "🏷️ <b>{$roleText}</b>\n"
                . "⏰ <b>Time:</b> " . now()->format('M d, Y h:i A') . "\n\n"
                . "Please review and approve the user in the admin panel.";

            foreach ($admins as $admin) {
                $this->sendMessage($admin->telegram_chat_id, $text);
            }

            Log::info('sendPendingUserLoginNotification: Sent to admins', [
                'user_id' => $userId,
                'user_name' => $userName,
                'admin_count' => $admins->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('sendPendingUserLoginNotification: Failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send rejection notification.
     */
    public function sendRejectedNotification(string $chatId, $proforma): bool
    {
        $text = "❌ <b>Application Rejected</b>\n\n"
            . "📋 File: <b>{$proforma->file_number}</b>\n"
            . "🚗 {$proforma->brand?->name} {$proforma->model} ({$proforma->year})\n"
            . "Your application for this proforma has been rejected.";

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Send "sent to owner" notification with invoice link.
     */
    public function sendSentToOwnerNotification(string $chatId, $proforma, string $invoiceUrl = ''): bool
    {
        $text = "✅ <b>Proforma Completed!</b>\n\n"
            . "📋 File: <b>{$proforma->file_number}</b>\n"
            . "🚗 {$proforma->brand?->name} {$proforma->model} ({$proforma->year})\n"
            . "Your proforma has been completed and returned to you.\n";

        if ($invoiceUrl) {
            $text .= "\n🧾 View Invoice: {$invoiceUrl}";
        }

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Send application progress notification.
     */
    public function sendApplicationProgressNotification(string $chatId, $proforma): bool
    {
        $appCount = $proforma->applications()->count();
        $required = $proforma->required_number_of_shops ?: '∞';
        $text = "📋 <b>Application Update</b>\n\n"
            . "File: <b>{$proforma->file_number}</b>\n"
            . "Applications: {$appCount}/{$required} filled\n";

        if (is_numeric($required) && $appCount >= (int)$required) {
            $text .= "\n✅ All slots filled! You can now request close.";
        }

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Send notification to the admin who floated a proforma when it is closed.
     */
    public function sendFloaterClosedNotification(string $chatId, $proforma): bool
    {
        $text = "📋 <b>Proforma Closed</b>\n\n"
            . "The proforma <b>{$proforma->file_number}</b> which you floated is closed.\n"
            . "Please Accept payment and send it back to owner.\n\n"
            . "🚗 {$proforma->brand?->name} {$proforma->model} ({$proforma->year})\n"
            . "🪪 Plate: {$proforma->license_plate_number}";

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Send billing details to the poster via Telegram when proforma is closed.
     */
    public function sendBillingDetailsNotification(string $chatId, $proforma, float $charge, float $vatAmount, float $total): bool
    {
        $text = "🧾 <b>Proforma Billing Information</b>\n\n"
            . "📋 File: <b>{$proforma->file_number}</b>\n"
            . "🚗 {$proforma->brand?->name} {$proforma->model} ({$proforma->year})\n"
            . "🪪 Plate: {$proforma->license_plate_number}\n\n"
            . "💰 <b>Billing Summary:</b>\n"
            . "━━━━━━━━━━━━━━━━━━\n"
            . "Service Charge: " . number_format($charge, 2) . " Birr\n"
            . "VAT (15%): " . number_format($vatAmount, 2) . " Birr\n"
            . "━━━━━━━━━━━━━━━━━━\n"
            . "<b>Total: " . number_format($total, 2) . " Birr</b>\n\n"
            . "please deposit full amount into\n"
            . "CBE: 1000687074638\n"
            . "etera trading one member plc.\n"
            . "and send screenshot of the payment with License plate info to our telegram.\n"
            . "Telegram: @etera_et\n\n"
            . "Your proforma has been closed. Thank you for using etera!";

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Send notification to processed_by user that proforma is closed and needs payment collection.
     */
    public function sendProcessedByClosedNotification(string $chatId, $proforma): bool
    {
        $text = "📋 <b>Proforma Closed</b>\n\n"
            . "The proforma <b>{$proforma->file_number}</b> you processed is closed.\n"
            . "Please accept payment and send back to owner.\n\n"
            . "🚗 {$proforma->brand?->name} {$proforma->model} ({$proforma->year})\n"
            . "🪪 Plate: {$proforma->license_plate_number}";

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Send notification to admins when a new garage/shop registers.
     */
    public function sendNewRegistrationNotification(string $chatId, $user): bool
    {
        $roleLabel = $user->role === 'garage' ? 'Garage' : 'Spare Part Shop';
        $text = "🆕 <b>New {$roleLabel} Registered!</b>\n\n"
            . "👤 Name: <b>{$user->name}</b>\n"
            . "📞 Phone: {$user->phone_number}\n"
            . "📍 Location: " . ($user->location ?? 'N/A') . "\n"
            . "🏷️ TIN: " . ($user->tin_number ?? 'N/A') . "\n\n"
            . "⏳ Pending approval. Please review in the admin panel.";

        return $this->sendMessage($chatId, $text);
    }

    public function sendPasswordResetLink(string $chatId, string $resetUrl, string $rejectAction, bool $rejectIsCallback = false, ?int &$messageId = null): bool
    {
        $text = "🔐 <b>Password Reset</b>\n\n"
            . "If this was you, tap <b>Yes, reset</b>. If not, tap <b>No, reject</b>.\n"
            . "This request expires in <b>5 minutes</b>.";

        $rejectButton = $rejectIsCallback
            ? ['text' => 'No, reject', 'callback_data' => $rejectAction]
            : ['text' => 'No, reject', 'url' => $rejectAction];

        return $this->sendMessageWithButtons($chatId, $text, [
            ['text' => 'Yes, reset', 'url' => $resetUrl],
            $rejectButton,
        ], $messageId);
    }
}

