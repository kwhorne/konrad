<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Bankoverføring',
                'code' => 'bank_transfer',
                'sort_order' => 1,
            ],
            [
                'name' => 'Kort',
                'code' => 'card',
                'sort_order' => 2,
            ],
            [
                'name' => 'Kontant',
                'code' => 'cash',
                'sort_order' => 3,
            ],
            [
                'name' => 'Vipps',
                'code' => 'vipps',
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
