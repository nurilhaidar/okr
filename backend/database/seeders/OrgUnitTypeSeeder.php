<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrgUnitTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgUnitTypes = [
            ['name' => 'Department'],
            ['name' => 'Division'],
            ['name' => 'Team'],
            ['name' => 'Unit'],
            ['name' => 'Branch'],
            ['name' => 'Section'],
            ['name' => 'Sub-unit'],
        ];

        foreach ($orgUnitTypes as $orgUnitType) {
            DB::table('orgunit_type')->insert($orgUnitType);
        }
    }
}
