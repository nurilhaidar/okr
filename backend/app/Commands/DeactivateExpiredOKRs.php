<?php

namespace App\Commands;

use App\Models\Okr;
use Illuminate\Console\Command;

class DeactivateExpiredOKRs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'okrs:deactivate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically deactivate OKRs whose end date has passed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for expired OKRs...');

        // Find all active OKRs whose end date has passed
        $expiredOKRs = Okr::where('is_active', true)
            ->where('end_date', '<', now()->startOfDay())
            ->get();

        if ($expiredOKRs->isEmpty()) {
            $this->info('No expired OKRs found.');
            return self::SUCCESS;
        }

        $count = $expiredOKRs->count();
        $this->info("Found {$count} expired OKR(s). Deactivating...");

        // Deactivate all expired OKRs
        $deactivatedCount = Okr::where('is_active', true)
            ->where('end_date', '<', now()->startOfDay())
            ->update(['is_active' => false]);

        $this->info("Successfully deactivated {$deactivatedCount} OKR(s).");

        // Log the action
        \Log::info("Expired OKRs deactivated successfully", [
            'count' => $deactivatedCount,
            'date' => now()->toDateString()
        ]);

        return self::SUCCESS;
    }
}
