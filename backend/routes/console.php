<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Auto-close expired etera chereta proformas every minute
Schedule::command('proformas:close-expired')->everyMinute();

// Send Telegram patience notifications to users waiting for proforma results
Schedule::command('proformas:send-waiting-notifications')->daily();

// Send daily "How did you hear about us?" survey summary to admin Telegram
Schedule::call(function () {
    $admins = \App\Models\User::where('role', 'admin')
        ->whereNotNull('telegram_chat_id')
        ->pluck('telegram_chat_id');

    if ($admins->isEmpty()) return;

    $counts = \App\Models\SentEmail::where('type', 'telegram_survey_response')
        ->selectRaw('subject, count(*) as total')
        ->groupBy('subject')
        ->pluck('total', 'subject');

    if ($counts->isEmpty()) return;

    $summary = "📊 <b>How did you hear about us? — Daily Summary</b>\n\n";
    foreach (['Facebook', 'Instagram', 'TikTok', 'Others'] as $platform) {
        $summary .= "• {$platform}: <b>" . ($counts[$platform] ?? 0) . "</b>\n";
    }
    $summary .= "\nTotal responses: <b>" . $counts->sum() . "</b>";

    $telegram = app(\App\Services\TelegramService::class);
    foreach ($admins as $chatId) {
        $telegram->sendMessage((string) $chatId, $summary);
    }
})->dailyAt('08:00')->name('survey-summary')->withoutOverlapping();
