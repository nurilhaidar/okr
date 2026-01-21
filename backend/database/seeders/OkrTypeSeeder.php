<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OkrTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $okrTypes = [
            ['name' => 'Individual', 'is_employee' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Team', 'is_employee' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Department', 'is_employee' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Company', 'is_employee' => false, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('okr_type')->insert($okrTypes);
    }
}
