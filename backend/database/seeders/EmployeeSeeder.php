<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'name' => 'Admin User',
                'email' => 'admin@okr.com',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'rank_id' => 7, // C-Level
                'position_id' => 3, // Tech Lead
                'role_id' => 1, // Admin
                'is_active' => true,
            ],
            [
                'name' => 'John Manager',
                'email' => 'john@okr.com',
                'username' => 'johnm',
                'password' => Hash::make('password'),
                'rank_id' => 5, // Principal
                'position_id' => 4, // Project Manager
                'role_id' => 2, // Manager
                'is_active' => true,
            ],
            [
                'name' => 'Jane Developer',
                'email' => 'jane@okr.com',
                'username' => 'janed',
                'password' => Hash::make('password'),
                'rank_id' => 3, // Senior
                'position_id' => 2, // Senior Software Engineer
                'role_id' => 3, // Employee
                'is_active' => true,
            ],
            [
                'name' => 'Bob Supervisor',
                'email' => 'bob@okr.com',
                'username' => 'bobs',
                'password' => Hash::make('password'),
                'rank_id' => 4, // Lead
                'position_id' => 3, // Tech Lead
                'role_id' => 4, // Supervisor
                'is_active' => true,
            ],
            [
                'name' => 'Alice Director',
                'email' => 'alice@okr.com',
                'username' => 'aliced',
                'password' => Hash::make('password'),
                'rank_id' => 6, // VP
                'position_id' => 11, // HR Manager
                'role_id' => 5, // Director
                'is_active' => true,
            ],
        ];

        foreach ($employees as $employee) {
            DB::table('employee')->insert(array_merge($employee, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
