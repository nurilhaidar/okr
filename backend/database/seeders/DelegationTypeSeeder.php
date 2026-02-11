<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DelegationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $delegationTypes = [
            ['name' => 'Full Delegate'],
            ['name' => 'Full Delegate (Multiple Team)'],
            ['name' => 'Partial Delegate (Without Weight)'],
            ['name' => 'Partial Delegate (With Weight)'],
        ];

        foreach ($delegationTypes as $delegationType) {
            DB::table('delegation_type')->insert($delegationType);
        }
    }
}
