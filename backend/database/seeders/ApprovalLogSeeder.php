<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $approvalLogs = [
            [
                'check_in_id' => 1,
                'status' => 'approved',
            ],
            [
                'check_in_id' => 2,
                'status' => 'pending',
            ],
            [
                'check_in_id' => 3,
                'status' => 'approved',
            ],
            [
                'check_in_id' => 4,
                'status' => 'rejected',
            ],
            [
                'check_in_id' => 5,
                'status' => 'approved',
            ],
            [
                'check_in_id' => 6,
                'status' => 'approved',
            ],
        ];

        foreach ($approvalLogs as $approvalLog) {
            DB::table('approval_log')->insert(array_merge($approvalLog, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
