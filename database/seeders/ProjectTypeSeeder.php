<?php

namespace Database\Seeders;

use App\Models\ProjectType;
use Illuminate\Database\Seeder;

class ProjectTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Konsulentoppdrag',
                'code' => 'CONSULTING',
                'description' => 'Rådgivnings- og konsulentoppdrag',
                'sort_order' => 1,
            ],
            [
                'name' => 'Utviklingsprosjekt',
                'code' => 'DEVELOPMENT',
                'description' => 'Programvareutvikling og tekniske prosjekter',
                'sort_order' => 2,
            ],
            [
                'name' => 'Supportavtale',
                'code' => 'SUPPORT',
                'description' => 'Løpende support- og vedlikeholdsavtaler',
                'sort_order' => 3,
            ],
            [
                'name' => 'Implementering',
                'code' => 'IMPLEMENTATION',
                'description' => 'Implementering og utrulling av systemer',
                'sort_order' => 4,
            ],
            [
                'name' => 'Opplæring',
                'code' => 'TRAINING',
                'description' => 'Kurs og opplæringsprosjekter',
                'sort_order' => 5,
            ],
        ];

        foreach ($types as $type) {
            ProjectType::withoutGlobalScopes()->updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
