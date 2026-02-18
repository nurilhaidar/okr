<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employee::firstOrCreate(
            ['email' => 'admin@okr.com'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => 'password',
                'position' => 'System Administrator',
                'role_id' => 1,
                'is_active' => true,
            ]
        );

        $this->command->info('Admin account created/updated successfully.');
        $this->command->info('Email: admin@okr.com');
        $this->command->info('Password: password');
    }
}
