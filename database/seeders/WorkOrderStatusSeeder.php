<?php

namespace Database\Seeders;

use App\Models\WorkOrderStatus;
use Illuminate\Database\Seeder;

class WorkOrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Ny',
                'code' => 'NEW',
                'color' => 'blue',
                'sort_order' => 1,
            ],
            [
                'name' => 'Planlagt',
                'code' => 'SCHEDULED',
                'color' => 'indigo',
                'sort_order' => 2,
            ],
            [
                'name' => 'Pågår',
                'code' => 'IN_PROGRESS',
                'color' => 'yellow',
                'sort_order' => 3,
            ],
            [
                'name' => 'Venter',
                'code' => 'ON_HOLD',
                'color' => 'gray',
                'sort_order' => 4,
            ],
            [
                'name' => 'Fullført',
                'code' => 'COMPLETED',
                'color' => 'green',
                'sort_order' => 5,
            ],
            [
                'name' => 'Godkjent',
                'code' => 'APPROVED',
                'color' => 'emerald',
                'sort_order' => 6,
            ],
            [
                'name' => 'Fakturert',
                'code' => 'INVOICED',
                'color' => 'purple',
                'sort_order' => 7,
            ],
            [
                'name' => 'Kansellert',
                'code' => 'CANCELLED',
                'color' => 'red',
                'sort_order' => 8,
            ],
        ];

        foreach ($statuses as $status) {
            WorkOrderStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
