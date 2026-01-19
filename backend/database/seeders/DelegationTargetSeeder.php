<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DelegationTargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $delegationTargets = [
            [
                'delegation_id' => 1,
                'target_type' => 'App\\Models\\OKR',
                'target_id' => 1, // Q1 2026 Engineering Goals
            ],
            [
                'delegation_id' => 2,
                'target_type' => 'App\\Models\\OKR',
                'target_id' => 2, // Q1 2026 Team Objectives
            ],
            [
                'delegation_id' => 3,
                'target_type' => 'App\\Models\\Objective',
                'target_id' => 5, // Migration objective
            ],
            [
                'delegation_id' => 4,
                'target_type' => 'App\\Models\\Employee',
                'target_id' => 3, // Jane Developer
            ],
        ];

        foreach ($delegationTargets as $delegationTarget) {
            DB::table('delegation_target')->insert(array_merge($delegationTarget, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
