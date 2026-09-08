<?php

namespace App\Console\Commands;

use App\Models\Proforma;
use App\Models\SentEmail;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendProformaWaitingNotifications extends Command
{
    protected $signature   = 'proformas:send-waiting-notifications';
    protected $description = 'Send Telegram patience notifications to users waiting for proforma results (every 24 h, final notice after 3 days)';

    public function handle(): void
    {
        $telegram = app(TelegramService::class);

        if (!$telegram->isConfigured()) {
            $this->info('Telegram not configured, skipping.');
            return;
        }

        // Only published proformas whose poster has Telegram connected
        $proformas = Proforma::where('status', 'published')
            ->with('poster')
            ->whereHas('poster', fn($q) => $q->whereNotNull('telegram_chat_id'))
            ->get();

        $this->info("Found {$proformas->count()} published proforma(s) to check.");

        foreach ($proformas as $proforma) {
            try {
                $this->processProforma($proforma, $telegram);
            } catch (\Throwable $e) {
                Log::warning("SendProformaWaitingNotifications: error for proforma {$proforma->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info('Done.');
    }

    private function processProforma(Proforma $proforma, TelegramService $telegram): void
    {
        $poster  = $proforma->poster;
        $chatId  = (string) $poster->telegram_chat_id;
        $ageHours = $proforma->created_at->diffInHours(now());

        // If the final "unsuccessful" notice was already sent, stop entirely
        $finalAlreadySent = SentEmail::where('proforma_id', $proforma->id)
            ->where('type', 'telegram_patience_final')
            ->exists();

        if ($finalAlreadySent) {
            return;
        }

        if ($ageHours >= 120) {
            // ── 3+ days: send the final notice exactly once ──────────────
            $message = "Hi, your request was unsuccessful, please try again later, "
                     . "or contact us for international sourcing.";

            $telegram->sendMessage($chatId, $message);

            SentEmail::log(
                'telegram_patience_final',
                'via-telegram',
                $poster->name,
                $poster->id,
                $proforma->id,
                "Telegram: Final notice #{$proforma->file_number}",
                'sent'
            );

            $this->info("  [final] {$proforma->file_number}");
            Log::info("SendProformaWaitingNotifications: final notice sent for proforma {$proforma->id}");

        } else {
            // ── Under 3 days: send "still looking" if 24 h have elapsed since the last one ──
            $lastSent = SentEmail::where('proforma_id', $proforma->id)
                ->where('type', 'telegram_patience_12h')
                ->latest('created_at')
                ->first();

            if ($lastSent && $lastSent->created_at->diffInHours(now()) < 24) {
                return; // Too soon
            }

            $message = "Hi, we still haven't found the auto parts you requested, "
                     . "we'll keep looking, thank you for your patience.";

            $telegram->sendMessage($chatId, $message);

            SentEmail::log(
                'telegram_patience_12h',
                'via-telegram',
                $poster->name,
                $poster->id,
                $proforma->id,
                "Telegram: Patience #{$proforma->file_number}",
                'sent'
            );

            $this->info("  [12h] {$proforma->file_number}");
            Log::info("SendProformaWaitingNotifications: patience notice sent for proforma {$proforma->id}");
        }
    }
}
