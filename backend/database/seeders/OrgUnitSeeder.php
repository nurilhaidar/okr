<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrgUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgUnits = [
            [
                'name' => 'Engineering Department',
                'custom_type' => null,
                'orgunit_type_id' => 1, // Department
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Product Division',
                'custom_type' => null,
                'orgunit_type_id' => 2, // Division
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Software Development Team',
                'custom_type' => null,
                'orgunit_type_id' => 3, // Team
                'parent_id' => 1, // Engineering Department
                'is_active' => true,
            ],
            [
                'name' => 'QA Team',
                'custom_type' => null,
                'orgunit_type_id' => 3, // Team
                'parent_id' => 1, // Engineering Department
                'is_active' => true,
            ],
            [
                'name' => 'DevOps Unit',
                'custom_type' => null,
                'orgunit_type_id' => 4, // Unit
                'parent_id' => 1, // Engineering Department
                'is_active' => true,
            ],
            [
                'name' => 'Human Resources Department',
                'custom_type' => null,
                'orgunit_type_id' => 1, // Department
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Finance Department',
                'custom_type' => null,
                'orgunit_type_id' => 1, // Department
                'parent_id' => null,
                'is_active' => true,
            ],
        ];

        foreach ($orgUnits as $orgUnit) {
            DB::table('orgunit')->insert(array_merge($orgUnit, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
