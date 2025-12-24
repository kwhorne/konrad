<?php

namespace Database\Seeders;

use App\Models\WorkOrderPriority;
use Illuminate\Database\Seeder;

class WorkOrderPrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = [
            [
                'name' => 'Lav',
                'code' => 'LOW',
                'color' => 'gray',
                'sort_order' => 1,
            ],
            [
                'name' => 'Normal',
                'code' => 'NORMAL',
                'color' => 'blue',
                'sort_order' => 2,
            ],
            [
                'name' => 'Høy',
                'code' => 'HIGH',
                'color' => 'yellow',
                'sort_order' => 3,
            ],
            [
                'name' => 'Kritisk',
                'code' => 'CRITICAL',
                'color' => 'red',
                'sort_order' => 4,
            ],
        ];

        foreach ($priorities as $priority) {
            WorkOrderPriority::updateOrCreate(
                ['code' => $priority['code']],
                $priority
            );
        }
    }
}
