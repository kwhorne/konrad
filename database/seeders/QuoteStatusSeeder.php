<?php

namespace Database\Seeders;

use App\Models\QuoteStatus;
use Illuminate\Database\Seeder;

class QuoteStatusSeeder extends Seeder
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
                'name' => 'Akseptert',
                'code' => 'accepted',
                'color' => 'green',
                'sort_order' => 3,
            ],
            [
                'name' => 'Avslått',
                'code' => 'rejected',
                'color' => 'red',
                'sort_order' => 4,
            ],
            [
                'name' => 'Utløpt',
                'code' => 'expired',
                'color' => 'amber',
                'sort_order' => 5,
            ],
            [
                'name' => 'Konvertert',
                'code' => 'converted',
                'color' => 'purple',
                'sort_order' => 6,
            ],
        ];

        foreach ($statuses as $status) {
            QuoteStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
