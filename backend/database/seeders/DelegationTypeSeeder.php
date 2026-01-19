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
            ['name' => 'Direct'],
            ['name' => 'Cascaded'],
            ['name' => 'Shared'],
            ['name' => 'Contributed'],
        ];

        foreach ($delegationTypes as $delegationType) {
            DB::table('delegation_type')->insert($delegationType);
        }
    }
}
