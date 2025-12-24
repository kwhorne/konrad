<?php

namespace Database\Seeders;

use App\Models\ProjectStatus;
use Illuminate\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Planlegging',
                'code' => 'PLANNING',
                'color' => 'blue',
                'description' => 'Prosjektet er under planlegging',
                'sort_order' => 1,
            ],
            [
                'name' => 'Pågår',
                'code' => 'IN_PROGRESS',
                'color' => 'yellow',
                'description' => 'Prosjektet er aktivt og pågår',
                'sort_order' => 2,
            ],
            [
                'name' => 'Fullført',
                'code' => 'COMPLETED',
                'color' => 'green',
                'description' => 'Prosjektet er fullført',
                'sort_order' => 3,
            ],
            [
                'name' => 'Pause',
                'code' => 'ON_HOLD',
                'color' => 'gray',
                'description' => 'Prosjektet er satt på pause',
                'sort_order' => 4,
            ],
            [
                'name' => 'Kansellert',
                'code' => 'CANCELLED',
                'color' => 'red',
                'description' => 'Prosjektet er kansellert',
                'sort_order' => 5,
            ],
        ];

        foreach ($statuses as $status) {
            ProjectStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
