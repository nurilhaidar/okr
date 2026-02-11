<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Workflow demonstration:
     * - Check-in 1: PENDING → APPROVED (completed workflow)
     * - Check-in 2: PENDING (waiting for approval)
     * - Check-in 3: PENDING → APPROVED (completed workflow)
     * - Check-in 4: PENDING → REJECTED (needs new check-in)
     * - Check-in 5: PENDING → APPROVED (completed workflow)
     * - Check-in 6: PENDING (waiting for approval)
     */
    public function run(): void
    {
        $approvalLogs = [
            // Check-in 1: Approved (shows complete pending → approved workflow)
            [
                'check_in_id' => 1,
                'status' => 'pending',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
            [
                'check_in_id' => 1,
                'status' => 'approved',
                'created_at' => now()->subDays(9),
                'updated_at' => now()->subDays(9),
            ],

            // Check-in 2: Still pending (waiting for approver action)
            [
                'check_in_id' => 2,
                'status' => 'pending',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],

            // Check-in 3: Approved
            [
                'check_in_id' => 3,
                'status' => 'pending',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(8),
            ],
            [
                'check_in_id' => 3,
                'status' => 'approved',
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],

            // Check-in 4: Rejected (tracker needs to create new check-in)
            [
                'check_in_id' => 4,
                'status' => 'pending',
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(6),
            ],
            [
                'check_in_id' => 4,
                'status' => 'rejected',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],

            // Check-in 5: Approved
            [
                'check_in_id' => 5,
                'status' => 'pending',
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(15),
            ],
            [
                'check_in_id' => 5,
                'status' => 'approved',
                'created_at' => now()->subDays(14),
                'updated_at' => now()->subDays(14),
            ],

            // Check-in 6: Still pending (waiting for approver action)
            [
                'check_in_id' => 6,
                'status' => 'pending',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
        ];

        foreach ($approvalLogs as $approvalLog) {
            DB::table('approval_log')->insert($approvalLog);
        }
    }
}
