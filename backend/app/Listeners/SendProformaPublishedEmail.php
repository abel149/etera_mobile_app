<?php

namespace App\Listeners;

use App\Events\ProformaPublished;
use App\Mail\ProformaFloatedMail;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendProformaPublishedEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProformaPublished $event): void
    {
        $proforma = $event->proforma;

        // Must have a brand to notify relevant shops
        if (!$proforma->car_brand_id) {
            return;
        }

        $isTest = $proforma->poster ? ($proforma->poster->is_test ?? false) : false;
        $emailEnabled = \App\Models\EmailSetting::isEnabled('proforma_floated');
        $telegramService = app(TelegramService::class);
        $subject = 'Proforma #' . $proforma->file_number . ' Published';

        // ── Notify shops (brand-filtered + is_test matched) ──────────────────
        $shopQuery = \App\Models\User::where('role', 'shop')
            ->where(function ($q) use ($isTest) {
                if ($isTest) {
                    $q->where('is_test', true);
                } else {
                    $q->where(fn($q2) => $q2->where('is_test', false)->orWhereNull('is_test'));
                }
            })
            ->whereHas('brands', function ($q) use ($proforma) {
                $q->where('brand_id', $proforma->car_brand_id);
            });

        // For insurance_shop_garage type, only notify users with shop_garage = 1
        if ($proforma->proforma_type === 'insurance_shop_garage') {
            $shopQuery->where('shop_garage', 1);
        }

        $shopUsers = $shopQuery->distinct()->get();

        foreach ($shopUsers as $user) {
            if ($emailEnabled && !empty($user->email)) {
                try {
                    Mail::to($user->email)->queue(new ProformaFloatedMail($proforma));
                    \App\Models\SentEmail::log('proforma_floated', $user->email, $user->name, $user->id, $proforma->id, $subject, 'sent');
                } catch (\Throwable $e) {
                    Log::warning('Failed to send proforma float email', ['user_id' => $user->id, 'proforma_id' => $proforma->id, 'error' => $e->getMessage()]);
                    \App\Models\SentEmail::log('proforma_floated', $user->email, $user->name, $user->id, $proforma->id, $subject, 'failed', $e->getMessage());
                }
            }
            if (!empty($user->telegram_chat_id)) {
                try {
                    $telegramService->sendProformaNotification($user->telegram_chat_id, $proforma);
                } catch (\Throwable $e) {
                    Log::warning('Failed to send Telegram shop notification', ['user_id' => $user->id, 'proforma_id' => $proforma->id, 'error' => $e->getMessage()]);
                }
            }
        }

        // ── Notify garages (is_test matched, treat null as false) ────────────
        if ($proforma->proforma_type === 'insurance_shop_only') {
            $garageUsers = collect();
        } else {
            $garageQuery = \App\Models\User::where('role', 'garage')
                ->where(function ($q) use ($isTest) {
                    if ($isTest) {
                        $q->where('is_test', true);
                    } else {
                        $q->where(fn($q2) => $q2->where('is_test', false)->orWhereNull('is_test'));
                    }
                })
                ->whereNotNull('telegram_chat_id');

            if ($proforma->proforma_type === 'insurance_shop_garage') {
                $garageQuery->where('shop_garage', 1);
            }

            $garageUsers = $garageQuery->get();
        }

        foreach ($garageUsers as $user) {
            try {
                $telegramService->sendProformaNotification($user->telegram_chat_id, $proforma);
            } catch (\Throwable $e) {
                Log::warning('Failed to send Telegram garage notification', ['user_id' => $user->id, 'proforma_id' => $proforma->id, 'error' => $e->getMessage()]);
            }
        }

        // ── Notify marketers (real proformas only — marketers have no is_test) ─
        if (!$isTest) {
            try {
                $telegramService->sendProformaFloatedNotificationToMarketers($proforma);
            } catch (\Throwable $e) {
                Log::warning('Failed to send Telegram marketer float notification', ['proforma_id' => $proforma->id, 'error' => $e->getMessage()]);
            }
        }
    }
}

