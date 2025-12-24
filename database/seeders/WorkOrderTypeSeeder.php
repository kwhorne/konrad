<?php

namespace Database\Seeders;

use App\Models\WorkOrderType;
use Illuminate\Database\Seeder;

class WorkOrderTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Service',
                'code' => 'SERVICE',
                'description' => 'Generell service og vedlikehold',
                'sort_order' => 1,
            ],
            [
                'name' => 'Reparasjon',
                'code' => 'REPAIR',
                'description' => 'Reparasjon av utstyr eller systemer',
                'sort_order' => 2,
            ],
            [
                'name' => 'Installasjon',
                'code' => 'INSTALLATION',
                'description' => 'Installasjon av nytt utstyr eller systemer',
                'sort_order' => 3,
            ],
            [
                'name' => 'Vedlikehold',
                'code' => 'MAINTENANCE',
                'description' => 'Planlagt vedlikehold og inspeksjon',
                'sort_order' => 4,
            ],
            [
                'name' => 'Konsultasjon',
                'code' => 'CONSULTATION',
                'description' => 'Rådgivning og konsultasjonstjenester',
                'sort_order' => 5,
            ],
        ];

        foreach ($types as $type) {
            WorkOrderType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
