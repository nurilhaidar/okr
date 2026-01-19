<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            ['name' => 'Software Engineer'],
            ['name' => 'Senior Software Engineer'],
            ['name' => 'Tech Lead'],
            ['name' => 'Project Manager'],
            ['name' => 'Product Manager'],
            ['name' => 'Business Analyst'],
            ['name' => 'Quality Assurance'],
            ['name' => 'DevOps Engineer'],
            ['name' => 'Data Analyst'],
            ['name' => 'HR Manager'],
            ['name' => 'Finance Manager'],
            ['name' => 'Marketing Manager'],
            ['name' => 'Sales Manager'],
            ['name' => 'Operations Manager'],
            ['name' => 'Executive Assistant'],
        ];

        foreach ($positions as $position) {
            DB::table('position')->insert($position);
        }
    }
}
