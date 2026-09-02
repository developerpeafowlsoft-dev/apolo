<?php

namespace Database\Seeders;

use App\Models\FinancialYear;
use Illuminate\Database\Seeder;

class FinancialYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $financialYears = [
            [
                'name' => 'FY 2023-24',
                'start_date' => '2023-04-01',
                'end_date' => '2024-03-31',
                'is_active' => 0,
            ],
            [
                'name' => 'FY 2024-25',
                'start_date' => '2024-04-01',
                'end_date' => '2025-03-31',
                'is_active' => 0,
            ],
            [
                'name' => 'FY 2025-26',
                'start_date' => '2025-04-01',
                'end_date' => '2026-03-31',
                'is_active' => 0,
            ],
            [
                'name' => 'FY 2026-27',
                'start_date' => '2026-04-01',
                'end_date' => '2027-03-31',
                'is_active' => 1,
            ],
            [
                'name' => 'FY 2027-28',
                'start_date' => '2027-04-01',
                'end_date' => '2028-03-31',
                'is_active' => 0,
            ],
        ];

        foreach ($financialYears as $fy) {
            FinancialYear::updateOrCreate(
                ['name' => $fy['name']],
                $fy
            );
        }
    }
}
