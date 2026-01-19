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
            ['name' => 'Head of Department'],
            ['name' => 'Department Manager'],
            ['name' => 'Team Lead'],
            ['name' => 'Division Head'],
            ['name' => 'Unit Manager'],
            ['name' => 'Branch Manager'],
            ['name' => 'Section Head'],
            ['name' => 'Member'],
            ['name' => 'Assistant'],
        ];

        foreach ($orgUnitRoles as $orgUnitRole) {
            DB::table('orgunit_role')->insert($orgUnitRole);
        }
    }
}
