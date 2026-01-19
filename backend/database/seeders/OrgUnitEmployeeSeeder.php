<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrgUnitEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgUnitEmployees = [
            [
                'orgunit_id' => 1, // Engineering Department
                'employee_id' => 2, // John Manager
                'orgunit_role_id' => 2, // Department Manager
            ],
            [
                'orgunit_id' => 3, // Software Development Team
                'employee_id' => 3, // Jane Developer
                'orgunit_role_id' => 8, // Member
            ],
            [
                'orgunit_id' => 3, // Software Development Team
                'employee_id' => 4, // Bob Supervisor
                'orgunit_role_id' => 3, // Team Lead
            ],
            [
                'orgunit_id' => 4, // QA Team
                'employee_id' => 3, // Jane Developer
                'orgunit_role_id' => 8, // Member
            ],
            [
                'orgunit_id' => 6, // Human Resources Department
                'employee_id' => 5, // Alice Director
                'orgunit_role_id' => 1, // Head of Department
            ],
        ];

        foreach ($orgUnitEmployees as $orgUnitEmployee) {
            DB::table('orgunit_employee')->insert(array_merge($orgUnitEmployee, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
