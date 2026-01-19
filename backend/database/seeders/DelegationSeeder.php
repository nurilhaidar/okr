<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DelegationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $delegations = [
            [
                'delegation_type_id' => 1, // Direct
                'weight' => 0.5000,
                'parent_objective_id' => 1,
                'child_objective_id' => 2,
            ],
            [
                'delegation_type_id' => 2, // Cascaded
                'weight' => 0.3000,
                'parent_objective_id' => 1,
                'child_objective_id' => 3,
            ],
            [
                'delegation_type_id' => 1, // Direct
                'weight' => 0.7000,
                'parent_objective_id' => 2,
                'child_objective_id' => 5,
            ],
            [
                'delegation_type_id' => 3, // Shared
                'weight' => 0.5000,
                'parent_objective_id' => 3,
                'child_objective_id' => 4,
            ],
        ];

        foreach ($delegations as $delegation) {
            DB::table('delegation')->insert(array_merge($delegation, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
