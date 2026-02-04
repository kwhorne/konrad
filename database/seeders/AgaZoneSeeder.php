<?php

namespace Database\Seeders;

use App\Models\AgaZone;
use Illuminate\Database\Seeder;

class AgaZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            ['code' => '1', 'name' => 'Sone I', 'rate' => 14.1, 'fribeloep' => null],
            ['code' => '1a', 'name' => 'Sone Ia', 'rate' => 10.6, 'fribeloep' => 500000],
            ['code' => '2', 'name' => 'Sone II', 'rate' => 10.6, 'fribeloep' => null],
            ['code' => '3', 'name' => 'Sone III', 'rate' => 6.4, 'fribeloep' => null],
            ['code' => '4', 'name' => 'Sone IV', 'rate' => 5.1, 'fribeloep' => null],
            ['code' => '4a', 'name' => 'Sone IVa', 'rate' => 7.9, 'fribeloep' => null],
            ['code' => '5', 'name' => 'Sone V (Svalbard)', 'rate' => 0.0, 'fribeloep' => null],
        ];

        foreach ($zones as $zone) {
            AgaZone::updateOrCreate(
                ['code' => $zone['code']],
                [
                    'name' => $zone['name'],
                    'rate' => $zone['rate'],
                    'fribeloep' => $zone['fribeloep'],
                    'is_active' => true,
                ]
            );
        }
    }
}
