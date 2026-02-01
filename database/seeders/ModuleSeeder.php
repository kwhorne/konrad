<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            // Standard modules (always included, not premium)
            [
                'slug' => 'contacts',
                'name' => 'Kontakter',
                'description' => 'Kunder, leverandører og kontaktpersoner',
                'is_premium' => false,
                'price_monthly' => 0,
                'sort_order' => 1,
            ],
            [
                'slug' => 'products',
                'name' => 'Varer',
                'description' => 'Produkter og tjenester med priser',
                'is_premium' => false,
                'price_monthly' => 0,
                'sort_order' => 2,
            ],
            [
                'slug' => 'work_orders',
                'name' => 'Arbeidsordrer',
                'description' => 'Arbeidsordrer og oppgaver',
                'is_premium' => false,
                'price_monthly' => 0,
                'sort_order' => 3,
            ],
            [
                'slug' => 'sales',
                'name' => 'Salg',
                'description' => 'Tilbud, ordrer og fakturaer',
                'is_premium' => false,
                'price_monthly' => 0,
                'sort_order' => 4,
            ],
            [
                'slug' => 'shareholders',
                'name' => 'Aksjonærer',
                'description' => 'Aksjonærregister og aksjebok',
                'is_premium' => false,
                'price_monthly' => 0,
                'sort_order' => 5,
            ],
            [
                'slug' => 'altinn',
                'name' => 'Altinn',
                'description' => 'Altinn-integrasjon for rapportering',
                'is_premium' => false,
                'price_monthly' => 0,
                'sort_order' => 6,
            ],

            // Premium modules (paid add-ons)
            [
                'slug' => 'contracts',
                'name' => 'Kontrakter',
                'description' => 'Kontraktsregistrering og oppfølging',
                'is_premium' => true,
                'price_monthly' => 14900, // 149 kr
                'sort_order' => 10,
            ],
            [
                'slug' => 'assets',
                'name' => 'Eiendeler',
                'description' => 'Eiendelsregister og avskrivninger',
                'is_premium' => true,
                'price_monthly' => 14900, // 149 kr
                'sort_order' => 11,
            ],
            [
                'slug' => 'projects',
                'name' => 'Prosjekter',
                'description' => 'Prosjektstyring med budsjett og timer',
                'is_premium' => true,
                'price_monthly' => 29900, // 299 kr
                'sort_order' => 12,
            ],
            [
                'slug' => 'inventory',
                'name' => 'Lager',
                'description' => 'Lagerstyring, innkjøp og varemottak',
                'is_premium' => true,
                'price_monthly' => 34900, // 349 kr
                'sort_order' => 13,
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::updateOrCreate(
                ['slug' => $moduleData['slug']],
                $moduleData
            );
        }
    }
}
