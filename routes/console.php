<?php

use App\Console\Commands\SendTaskDeadlineReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Напоминания о дедлайнах — каждый день в 9:00
Schedule::command(SendTaskDeadlineReminders::class)->dailyAt('09:00');
