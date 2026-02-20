<?php

use App\Models\Okr;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Register commands
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register the deactivate expired OKRs command
Artisan::command('okrs:deactivate-expired', function () {
    $this->info('Checking for expired OKRs...');

    // Find all active OKRs whose end date has passed
    $expiredOKRs = Okr::where('is_active', true)
        ->where('end_date', '<', now()->startOfDay())
        ->get();

    if ($expiredOKRs->isEmpty()) {
        $this->info('No expired OKRs found.');
        return;
    }

    $count = $expiredOKRs->count();
    $this->info("Found {$count} expired OKR(s). Deactivating...");

    // Deactivate all expired OKRs
    $deactivatedCount = Okr::where('is_active', true)
        ->where('end_date', '<', now()->startOfDay())
        ->update(['is_active' => false]);

    $this->info("Successfully deactivated {$deactivatedCount} OKR(s).");

    // Log the action
    Log::info("Expired OKRs deactivated successfully", [
        'count' => $deactivatedCount,
        'date' => now()->toDateString()
    ]);
})->description('Automatically deactivate OKRs whose end date has passed');

// Schedule deactivate expired OKRs command to run daily at midnight
Schedule::command('okrs:deactivate-expired')
    ->daily()
    ->description('Automatically deactivate OKRs whose end date has passed');

// Schedule check-in reminder command to run daily at 9 AM
Schedule::command('check-ins:send-reminders --days=3,7')
    ->dailyAt('09:00')
    ->description('Send check-in reminder notifications to trackers');
