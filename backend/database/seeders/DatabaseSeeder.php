<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            OrgUnitTypeSeeder::class,
            OrgUnitRoleSeeder::class,
            DelegationTypeSeeder::class,
            OrgUnitSeeder::class,
            EmployeeSeeder::class,
            OrgUnitEmployeeSeeder::class,
            OkrTypeSeeder::class,
            OKRSeeder::class,
            ObjectiveSeeder::class,
            DelegationSeeder::class,
            DelegationTargetSeeder::class,
            CheckInSeeder::class,
            ApprovalLogSeeder::class,
        ]);
    }
}
