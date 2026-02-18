<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule check-in reminder command to run daily at 9 AM
Schedule::command('check-ins:send-reminders --days=3,7')
    ->dailyAt('09:00')
    ->description('Send check-in reminder notifications to trackers');
