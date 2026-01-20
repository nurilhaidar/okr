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
                'position' => 'Chief Technology Officer',
                'role_id' => 1, // Admin
                'is_active' => true,
            ],
            [
                'name' => 'John Manager',
                'email' => 'john@okr.com',
                'username' => 'johnm',
                'password' => Hash::make('password'),
                'position' => 'Project Manager',
                'role_id' => 2, // Manager
                'is_active' => true,
            ],
            [
                'name' => 'Jane Developer',
                'email' => 'jane@okr.com',
                'username' => 'janed',
                'password' => Hash::make('password'),
                'position' => 'Senior Software Engineer',
                'role_id' => 3, // Employee
                'is_active' => true,
            ],
            [
                'name' => 'Bob Supervisor',
                'email' => 'bob@okr.com',
                'username' => 'bobs',
                'password' => Hash::make('password'),
                'position' => 'Tech Lead',
                'role_id' => 4, // Supervisor
                'is_active' => true,
            ],
            [
                'name' => 'Alice Director',
                'email' => 'alice@okr.com',
                'username' => 'aliced',
                'password' => Hash::make('password'),
                'position' => 'Human Resources Manager',
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
