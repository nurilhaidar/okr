<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckInSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $checkIns = [
            [
                'objective_id' => 1,
                'date' => '2026-01-15 10:00:00',
                'current_value' => 0.5000,
                'comments' => 'OAuth2 integration is halfway complete. Initial testing shows promising results.',
                'evidence_path' => '/evidence/oauth2-progress-jan15.pdf',
            ],
            [
                'objective_id' => 1,
                'date' => '2026-01-30 10:00:00',
                'current_value' => 0.7500,
                'comments' => 'OAuth2 module is 75% complete. Some edge cases need to be addressed.',
                'evidence_path' => '/evidence/oauth2-progress-jan30.pdf',
            ],
            [
                'objective_id' => 2,
                'date' => '2026-01-20 14:00:00',
                'current_value' => 85.0,
                'comments' => 'Code coverage reached 85%. Need to add more tests for legacy modules.',
                'evidence_path' => '/evidence/coverage-jan20.png',
            ],
            [
                'objective_id' => 3,
                'date' => '2026-01-25 16:00:00',
                'current_value' => 235.0,
                'comments' => 'Current API response time is 235ms. Optimization work in progress.',
                'evidence_path' => '/evidence/api-performance-jan25.pdf',
            ],
            [
                'objective_id' => 5,
                'date' => '2026-01-10 11:00:00',
                'current_value' => 0.3000,
                'comments' => 'Database migration script created. Testing environment setup complete.',
                'evidence_path' => '/evidence/migration-progress-jan10.pdf',
            ],
            [
                'objective_id' => 5,
                'date' => '2026-01-20 11:00:00',
                'current_value' => 0.7000,
                'comments' => '70% of data migrated. Performance optimization needed for large tables.',
                'evidence_path' => '/evidence/migration-progress-jan20.pdf',
            ],
        ];

        foreach ($checkIns as $checkIn) {
            DB::table('check_in')->insert(array_merge($checkIn, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
