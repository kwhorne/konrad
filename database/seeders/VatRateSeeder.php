<?php

namespace Database\Seeders;

use App\Models\VatRate;
use Illuminate\Database\Seeder;

class VatRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = [
            [
                'name' => 'Ingen moms',
                'code' => 'MVA0',
                'rate' => 0,
                'description' => '0% moms - fritatt',
                'is_default' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Lav sats',
                'code' => 'MVA15',
                'rate' => 15,
                'description' => '15% moms - mat og drikke',
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Standard sats',
                'code' => 'MVA25',
                'rate' => 25,
                'description' => '25% moms - standard sats',
                'is_default' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($rates as $rate) {
            VatRate::updateOrCreate(
                ['code' => $rate['code']],
                $rate
            );
        }
    }
}
