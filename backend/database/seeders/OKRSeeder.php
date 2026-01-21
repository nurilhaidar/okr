<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OKRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $okrs = [
            [
                'name' => 'Q1 2026 Engineering Goals',
                'weight' => 1.0000,
                'okr_type_id' => 1, // Individual
                'start_date' => '2026-01-01 00:00:00',
                'end_date' => '2026-03-31 23:59:59',
                'owner_type' => 'App\\Models\\Employee',
                'owner_id' => 2, // John Manager
                'is_active' => true,
            ],
            [
                'name' => 'Q1 2026 Team Objectives',
                'weight' => 0.8000,
                'okr_type_id' => 2, // Team
                'start_date' => '2026-01-01 00:00:00',
                'end_date' => '2026-03-31 23:59:59',
                'owner_type' => 'App\\Models\\OrgUnit',
                'owner_id' => 3, // Software Development Team
                'is_active' => true,
            ],
            [
                'name' => 'Q1 2026 Department OKRs',
                'weight' => 1.0000,
                'okr_type_id' => 3, // Department
                'start_date' => '2026-01-01 00:00:00',
                'end_date' => '2026-03-31 23:59:59',
                'owner_type' => 'App\\Models\\OrgUnit',
                'owner_id' => 1, // Engineering Department
                'is_active' => true,
            ],
        ];

        foreach ($okrs as $okr) {
            DB::table('okr')->insert(array_merge($okr, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
