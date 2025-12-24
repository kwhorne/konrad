<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'Stykk',
                'code' => 'STK',
                'symbol' => 'stk',
                'description' => 'Enkelt enhet/stykk',
                'sort_order' => 1,
            ],
            [
                'name' => 'Time',
                'code' => 'TIME',
                'symbol' => 't',
                'description' => 'Time (60 minutter)',
                'sort_order' => 2,
            ],
            [
                'name' => 'Dag',
                'code' => 'DAG',
                'symbol' => 'd',
                'description' => 'Dag (24 timer)',
                'sort_order' => 3,
            ],
            [
                'name' => 'Uke',
                'code' => 'UKE',
                'symbol' => 'u',
                'description' => 'Uke (7 dager)',
                'sort_order' => 4,
            ],
            [
                'name' => 'Måned',
                'code' => 'MND',
                'symbol' => 'mnd',
                'description' => 'Måned',
                'sort_order' => 5,
            ],
            [
                'name' => 'Kvartal',
                'code' => 'KVARTAL',
                'symbol' => 'kv',
                'description' => 'Kvartal (3 måneder)',
                'sort_order' => 6,
            ],
            [
                'name' => 'År',
                'code' => 'AAR',
                'symbol' => 'år',
                'description' => 'År (12 måneder)',
                'sort_order' => 7,
            ],
            [
                'name' => 'Kilometer',
                'code' => 'KM',
                'symbol' => 'km',
                'description' => 'Kilometer',
                'sort_order' => 8,
            ],
            [
                'name' => 'Kvadratmeter',
                'code' => 'KVM',
                'symbol' => 'm²',
                'description' => 'Kvadratmeter',
                'sort_order' => 9,
            ],
            [
                'name' => 'Liter',
                'code' => 'LITER',
                'symbol' => 'l',
                'description' => 'Liter',
                'sort_order' => 10,
            ],
            [
                'name' => 'Kilogram',
                'code' => 'KG',
                'symbol' => 'kg',
                'description' => 'Kilogram',
                'sort_order' => 11,
            ],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['code' => $unit['code']],
                $unit
            );
        }
    }
}
