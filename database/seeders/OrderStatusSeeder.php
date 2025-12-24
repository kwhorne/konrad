<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Utkast',
                'code' => 'draft',
                'color' => 'zinc',
                'sort_order' => 1,
            ],
            [
                'name' => 'Bekreftet',
                'code' => 'confirmed',
                'color' => 'blue',
                'sort_order' => 2,
            ],
            [
                'name' => 'Under arbeid',
                'code' => 'in_progress',
                'color' => 'yellow',
                'sort_order' => 3,
            ],
            [
                'name' => 'Fullført',
                'code' => 'completed',
                'color' => 'green',
                'sort_order' => 4,
            ],
            [
                'name' => 'Kansellert',
                'code' => 'cancelled',
                'color' => 'red',
                'sort_order' => 5,
            ],
            [
                'name' => 'Fakturert',
                'code' => 'invoiced',
                'color' => 'purple',
                'sort_order' => 6,
            ],
        ];

        foreach ($statuses as $status) {
            OrderStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
