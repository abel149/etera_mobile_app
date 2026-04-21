<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Auto-close expired etera chereta proformas every minute
Schedule::command('proformas:close-expired')->everyMinute();

// Generate monthly billing statements on the 1st of each month at 00:05
Schedule::command('billing:generate monthly')->monthlyOn(1, '00:05');

// Generate weekly billing statements every Monday at 00:05
Schedule::command('billing:generate weekly')->weeklyOn(1, '00:05');
