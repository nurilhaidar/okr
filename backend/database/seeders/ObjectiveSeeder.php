<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObjectiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $objectives = [
            [
                'okr_id' => 1, // Q1 2026 Engineering Goals
                'description' => 'Complete the new user authentication module with OAuth2 support',
                'weight' => 0.3000,
                'target_type' => 'binary',
                'target_value' => 1.0,
                'deadline' => '2026-02-28 23:59:59',
                'tracker' => 3, // Jane Developer
                'approver' => 4, // Bob Supervisor
            ],
            [
                'okr_id' => 1, // Q1 2026 Engineering Goals
                'description' => 'Achieve 95% code coverage for all critical modules',
                'weight' => 0.2500,
                'target_type' => 'numeric',
                'target_value' => 95.0,
                'deadline' => '2026-03-15 23:59:59',
                'tracker' => 3, // Jane Developer
                'approver' => 4, // Bob Supervisor
            ],
            [
                'okr_id' => 2, // Q1 2026 Team Objectives
                'description' => 'Reduce API response time to under 200ms for 95% of requests',
                'weight' => 0.2000,
                'target_type' => 'numeric',
                'target_value' => 200.0,
                'deadline' => '2026-03-31 23:59:59',
                'tracker' => 3, // Jane Developer
                'approver' => 2, // John Manager
            ],
            [
                'okr_id' => 3, // Q1 2026 Department OKRs
                'description' => 'Conduct 5 security audits and implement all recommendations',
                'weight' => 0.2500,
                'target_type' => 'numeric',
                'target_value' => 5.0,
                'deadline' => '2026-03-31 23:59:59',
                'tracker' => 4, // Bob Supervisor
                'approver' => 2, // John Manager
            ],
            [
                'okr_id' => 1, // Q1 2026 Engineering Goals
                'description' => 'Migrate legacy database to new infrastructure',
                'weight' => 0.4000,
                'target_type' => 'binary',
                'target_value' => 1.0,
                'deadline' => '2026-02-15 23:59:59',
                'tracker' => 3, // Jane Developer
                'approver' => 4, // Bob Supervisor
            ],
        ];

        foreach ($objectives as $objective) {
            DB::table('objective')->insert(array_merge($objective, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
