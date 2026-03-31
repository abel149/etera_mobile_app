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

        $emailEnabled = \App\Models\EmailSetting::isEnabled('proforma_floated');

        // Find all shops that serve this proforma's brand (distinct to avoid duplicates)
        $shopUsers = \App\Models\User::where('role', 'shop')
            ->whereHas('brands', function ($q) use ($proforma) {
                $q->where('brand_id', $proforma->car_brand_id);
            })
            ->distinct()
            ->get();

        $subject = 'Proforma #' . $proforma->file_number . ' Published';
        $telegramService = app(TelegramService::class);

        foreach ($shopUsers as $user) {
            // Send email if enabled
            if ($emailEnabled && !empty($user->email)) {
                try {
                    Mail::to($user->email)
                        ->queue(new ProformaFloatedMail($proforma));

                    \App\Models\SentEmail::log(
                        'proforma_floated',
                        $user->email,
                        $user->name,
                        $user->id,
                        $proforma->id,
                        $subject,
                        'sent'
                    );
                } catch (\Throwable $e) {
                    Log::warning('Failed to send proforma float email', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'proforma_id' => $proforma->id,
                        'error' => $e->getMessage(),
                    ]);

                    \App\Models\SentEmail::log(
                        'proforma_floated',
                        $user->email,
                        $user->name,
                        $user->id,
                        $proforma->id,
                        $subject,
                        'failed',
                        $e->getMessage()
                    );
                }
            }

            // Send Telegram notification if user has linked their Telegram
            if (!empty($user->telegram_chat_id)) {
                try {
                    $telegramService->sendProformaNotification($user->telegram_chat_id, $proforma);
                } catch (\Throwable $e) {
                    Log::warning('Failed to send Telegram proforma notification', [
                        'user_id' => $user->id,
                        'chat_id' => $user->telegram_chat_id,
                        'proforma_id' => $proforma->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}

