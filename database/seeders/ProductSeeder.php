<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductType;
use App\Models\Unit;
use App\Models\VatRate;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get VAT rates
        $vat25 = VatRate::where('code', 'MVA25')->first();
        $vat15 = VatRate::where('code', 'MVA15')->first();
        $vat0 = VatRate::where('code', 'MVA0')->first();

        // Get units
        $stk = Unit::where('code', 'STK')->first();
        $time = Unit::where('code', 'TIME')->first();
        $mnd = Unit::where('code', 'MND')->first();
        $aar = Unit::where('code', 'AAR')->first();

        // Create product groups
        $groups = [
            [
                'name' => 'Konsulenttjenester',
                'code' => 'KONSULENT',
                'description' => 'Rådgivning og konsulenttjenester',
                'sort_order' => 1,
            ],
            [
                'name' => 'Programvare',
                'code' => 'SOFTWARE',
                'description' => 'Programvare og lisenser',
                'sort_order' => 2,
            ],
            [
                'name' => 'Maskinvare',
                'code' => 'HARDWARE',
                'description' => 'Fysisk utstyr og maskinvare',
                'sort_order' => 3,
            ],
            [
                'name' => 'Support',
                'code' => 'SUPPORT',
                'description' => 'Supporttjenester og vedlikehold',
                'sort_order' => 4,
            ],
        ];

        foreach ($groups as $group) {
            ProductGroup::updateOrCreate(
                ['code' => $group['code']],
                $group
            );
        }

        $konsulentGruppe = ProductGroup::where('code', 'KONSULENT')->first();
        $softwareGruppe = ProductGroup::where('code', 'SOFTWARE')->first();
        $hardwareGruppe = ProductGroup::where('code', 'HARDWARE')->first();
        $supportGruppe = ProductGroup::where('code', 'SUPPORT')->first();

        // Create product types
        $types = [
            [
                'name' => 'Tjeneste',
                'code' => 'TJENESTE',
                'vat_rate_id' => $vat25->id,
                'description' => 'Generelle tjenester med 25% MVA',
                'sort_order' => 1,
            ],
            [
                'name' => 'Programvarelisens',
                'code' => 'LISENS',
                'vat_rate_id' => $vat25->id,
                'description' => 'Programvarelisenser',
                'sort_order' => 2,
            ],
            [
                'name' => 'Fysisk produkt',
                'code' => 'FYSISK',
                'vat_rate_id' => $vat25->id,
                'description' => 'Fysiske produkter og utstyr',
                'sort_order' => 3,
            ],
            [
                'name' => 'Abonnement',
                'code' => 'ABONNEMENT',
                'vat_rate_id' => $vat25->id,
                'description' => 'Løpende abonnementer',
                'sort_order' => 4,
            ],
            [
                'name' => 'Eksportert tjeneste',
                'code' => 'EKSPORT',
                'vat_rate_id' => $vat0->id,
                'description' => 'Tjenester til utlandet (0% MVA)',
                'sort_order' => 5,
            ],
        ];

        foreach ($types as $type) {
            ProductType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }

        $tjenesteType = ProductType::where('code', 'TJENESTE')->first();
        $lisensType = ProductType::where('code', 'LISENS')->first();
        $fysiskType = ProductType::where('code', 'FYSISK')->first();
        $abonnementType = ProductType::where('code', 'ABONNEMENT')->first();

        // Create products
        $products = [
            // Konsulenttjenester
            [
                'name' => 'Seniorkonsulent',
                'sku' => 'KONS-001',
                'description' => 'Seniorkonsulent med bred erfaring',
                'product_group_id' => $konsulentGruppe->id,
                'product_type_id' => $tjenesteType->id,
                'unit_id' => $time->id,
                'price' => 1850.00,
                'cost_price' => 850.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Konsulent',
                'sku' => 'KONS-002',
                'description' => 'Konsulent med god erfaring',
                'product_group_id' => $konsulentGruppe->id,
                'product_type_id' => $tjenesteType->id,
                'unit_id' => $time->id,
                'price' => 1450.00,
                'cost_price' => 650.00,
                'sort_order' => 2,
            ],
            [
                'name' => 'Juniorkonsulent',
                'sku' => 'KONS-003',
                'description' => 'Juniorkonsulent under opplæring',
                'product_group_id' => $konsulentGruppe->id,
                'product_type_id' => $tjenesteType->id,
                'unit_id' => $time->id,
                'price' => 950.00,
                'cost_price' => 450.00,
                'sort_order' => 3,
            ],
            [
                'name' => 'Prosjektledelse',
                'sku' => 'KONS-004',
                'description' => 'Prosjektledelse og koordinering',
                'product_group_id' => $konsulentGruppe->id,
                'product_type_id' => $tjenesteType->id,
                'unit_id' => $time->id,
                'price' => 1650.00,
                'cost_price' => 750.00,
                'sort_order' => 4,
            ],

            // Programvare
            [
                'name' => 'Microsoft 365 Business',
                'sku' => 'SOFT-001',
                'description' => 'Microsoft 365 Business Premium lisens',
                'product_group_id' => $softwareGruppe->id,
                'product_type_id' => $abonnementType->id,
                'unit_id' => $mnd->id,
                'price' => 220.00,
                'cost_price' => 180.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Adobe Creative Cloud',
                'sku' => 'SOFT-002',
                'description' => 'Adobe Creative Cloud lisens',
                'product_group_id' => $softwareGruppe->id,
                'product_type_id' => $abonnementType->id,
                'unit_id' => $mnd->id,
                'price' => 650.00,
                'cost_price' => 520.00,
                'sort_order' => 2,
            ],
            [
                'name' => 'Antiviruslisens',
                'sku' => 'SOFT-003',
                'description' => 'Antivirus og sikkerhetsprogramvare',
                'product_group_id' => $softwareGruppe->id,
                'product_type_id' => $lisensType->id,
                'unit_id' => $aar->id,
                'price' => 450.00,
                'cost_price' => 280.00,
                'sort_order' => 3,
            ],

            // Maskinvare
            [
                'name' => 'Laptop Dell Latitude',
                'sku' => 'HW-001',
                'description' => 'Dell Latitude 5540 laptop',
                'product_group_id' => $hardwareGruppe->id,
                'product_type_id' => $fysiskType->id,
                'unit_id' => $stk->id,
                'price' => 12500.00,
                'cost_price' => 9800.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Monitor 27"',
                'sku' => 'HW-002',
                'description' => '27" IPS monitor med USB-C',
                'product_group_id' => $hardwareGruppe->id,
                'product_type_id' => $fysiskType->id,
                'unit_id' => $stk->id,
                'price' => 4500.00,
                'cost_price' => 3200.00,
                'sort_order' => 2,
            ],
            [
                'name' => 'Tastatur og mus',
                'sku' => 'HW-003',
                'description' => 'Trådløst tastatur og mus sett',
                'product_group_id' => $hardwareGruppe->id,
                'product_type_id' => $fysiskType->id,
                'unit_id' => $stk->id,
                'price' => 890.00,
                'cost_price' => 520.00,
                'sort_order' => 3,
            ],

            // Support
            [
                'name' => 'Supportavtale Basis',
                'sku' => 'SUP-001',
                'description' => 'Grunnleggende supportavtale med e-post support',
                'product_group_id' => $supportGruppe->id,
                'product_type_id' => $abonnementType->id,
                'unit_id' => $mnd->id,
                'price' => 1500.00,
                'cost_price' => 500.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Supportavtale Premium',
                'sku' => 'SUP-002',
                'description' => 'Premium support med telefon og prioritert behandling',
                'product_group_id' => $supportGruppe->id,
                'product_type_id' => $abonnementType->id,
                'unit_id' => $mnd->id,
                'price' => 3500.00,
                'cost_price' => 1200.00,
                'sort_order' => 2,
            ],
            [
                'name' => 'Ad-hoc support',
                'sku' => 'SUP-003',
                'description' => 'Timesbasert support uten avtale',
                'product_group_id' => $supportGruppe->id,
                'product_type_id' => $tjenesteType->id,
                'unit_id' => $time->id,
                'price' => 1250.00,
                'cost_price' => 400.00,
                'sort_order' => 3,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}
