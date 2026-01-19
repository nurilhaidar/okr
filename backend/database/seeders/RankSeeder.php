<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ranks = [
            ['name' => 'Junior'],
            ['name' => 'Middle'],
            ['name' => 'Senior'],
            ['name' => 'Lead'],
            ['name' => 'Principal'],
            ['name' => 'Director'],
            ['name' => 'VP'],
            ['name' => 'C-Level'],
        ];

        foreach ($ranks as $rank) {
            DB::table('rank')->insert($rank);
        }
    }
}
