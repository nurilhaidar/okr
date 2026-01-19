<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin'],
            ['name' => 'Manager'],
            ['name' => 'Employee'],
            ['name' => 'Supervisor'],
            ['name' => 'Director'],
        ];

        foreach ($roles as $role) {
            DB::table('role')->insert($role);
        }
    }
}
