<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrgUnitRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgUnitRoles = [
            ['name' => 'Team Lead', 'is_exclusive' => true],
            ['name' => 'Manager', 'is_exclusive' => true],
            ['name' => 'Member', 'is_exclusive' => false],
        ];

        foreach ($orgUnitRoles as $orgUnitRole) {
            DB::table('orgunit_role')->insert(array_merge($orgUnitRole, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
