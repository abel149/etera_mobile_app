<?php

namespace App\Services;

use App\Models\Proforma;
use App\Models\User;
use App\Models\Inbox;
use App\Notifications\InboxNotification;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class InboxNotificationService
{
    /**
     * Send proforma to spare-part users and notify them
     */
    public function sendToSparePartUsers(Proforma $proforma, array $userIds = [])
    {
        try {
            // If no specific users provided, get all spare-part users
            if (empty($userIds)) {
                $sparePartUsers = User::where('role', 'shop')->get();
            } else {
                $sparePartUsers = User::whereIn('id', $userIds)
                    ->where('role', 'shop')
                    ->get();
            }

            foreach ($sparePartUsers as $user) {
                // Create inbox record (idempotent)
                $inbox = Inbox::firstOrCreate([
                    'proforma_id' => $proforma->id,
                    'user_id' => $user->id,
                ]);

                // Send notification
                $user->notify(new InboxNotification($proforma));

                // Telegram
                try {
                    if (!empty($user->telegram_chat_id)) {
                        $telegram = new TelegramService();
                        if ($telegram->isConfigured()) {
                            $sent = $telegram->sendInboxReceivedNotification((string) $user->telegram_chat_id, $proforma);
                            Log::info('Inbox Telegram notification attempted (shop)', [
                                'proforma_id' => $proforma->id,
                                'user_id' => $user->id,
                                'inbox_recently_created' => (bool) $inbox->wasRecentlyCreated,
                                'sent' => $sent,
                            ]);
                        } else {
                            Log::info('Inbox Telegram notification skipped: Telegram not configured (shop)', [
                                'proforma_id' => $proforma->id,
                                'user_id' => $user->id,
                            ]);
                        }
                    } elseif (empty($user->telegram_chat_id)) {
                        Log::info('Inbox Telegram notification skipped: user not linked (shop)', [
                            'proforma_id' => $proforma->id,
                            'user_id' => $user->id,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to send inbox received Telegram notification (shop)', [
                        'proforma_id' => $proforma->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info("Inbox notifications sent to " . $sparePartUsers->count() . " spare-part users for proforma {$proforma->id}");

            return [
                'success' => true,
                'message' => 'Inbox notifications sent successfully',
                'count' => $sparePartUsers->count()
            ];

        } catch (\Exception $e) {
            Log::error("Error sending inbox notifications: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error sending inbox notifications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send proforma to garage users and notify them
     */
    public function sendToGarageUsers(Proforma $proforma, array $userIds = [])
    {
        try {
            // If no specific users provided, get all garage users
            if (empty($userIds)) {
                $garageUsers = User::where('role', 'garage')->get();
            } else {
                $garageUsers = User::whereIn('id', $userIds)
                    ->where('role', 'garage')
                    ->get();
            }

            foreach ($garageUsers as $user) {
                // Create inbox record (idempotent)
                $inbox = Inbox::firstOrCreate([
                    'proforma_id' => $proforma->id,
                    'user_id' => $user->id,
                ]);

                // Send notification
                $user->notify(new InboxNotification($proforma));

                // Telegram
                try {
                    if (!empty($user->telegram_chat_id)) {
                        $telegram = new TelegramService();
                        if ($telegram->isConfigured()) {
                            $sent = $telegram->sendInboxReceivedNotification((string) $user->telegram_chat_id, $proforma);
                            Log::info('Inbox Telegram notification attempted (garage)', [
                                'proforma_id' => $proforma->id,
                                'user_id' => $user->id,
                                'inbox_recently_created' => (bool) $inbox->wasRecentlyCreated,
                                'sent' => $sent,
                            ]);
                        } else {
                            Log::info('Inbox Telegram notification skipped: Telegram not configured (garage)', [
                                'proforma_id' => $proforma->id,
                                'user_id' => $user->id,
                            ]);
                        }
                    } elseif (empty($user->telegram_chat_id)) {
                        Log::info('Inbox Telegram notification skipped: user not linked (garage)', [
                            'proforma_id' => $proforma->id,
                            'user_id' => $user->id,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to send inbox received Telegram notification (garage)', [
                        'proforma_id' => $proforma->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info("Inbox notifications sent to " . $garageUsers->count() . " garage users for proforma {$proforma->id}");

            return [
                'success' => true,
                'message' => 'Inbox notifications sent successfully',
                'count' => $garageUsers->count()
            ];

        } catch (\Exception $e) {
            Log::error("Error sending inbox notifications: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error sending inbox notifications: ' . $e->getMessage()
            ];
        }
    }
}
