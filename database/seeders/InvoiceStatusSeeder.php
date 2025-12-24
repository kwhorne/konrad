<?php

namespace Database\Seeders;

use App\Models\InvoiceStatus;
use Illuminate\Database\Seeder;

class InvoiceStatusSeeder extends Seeder
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
                'name' => 'Sendt',
                'code' => 'sent',
                'color' => 'blue',
                'sort_order' => 2,
            ],
            [
                'name' => 'Delvis betalt',
                'code' => 'partially_paid',
                'color' => 'yellow',
                'sort_order' => 3,
            ],
            [
                'name' => 'Betalt',
                'code' => 'paid',
                'color' => 'green',
                'sort_order' => 4,
            ],
            [
                'name' => 'Forfalt',
                'code' => 'overdue',
                'color' => 'red',
                'sort_order' => 5,
            ],
            [
                'name' => 'Kreditert',
                'code' => 'credited',
                'color' => 'purple',
                'sort_order' => 6,
            ],
        ];

        foreach ($statuses as $status) {
            InvoiceStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
