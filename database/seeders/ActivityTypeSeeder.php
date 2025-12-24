<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Telefon inn',
                'icon' => 'phone-arrow-down-left',
                'color' => 'green',
                'description' => 'Innkommende telefonsamtale',
                'sort_order' => 1,
            ],
            [
                'name' => 'Telefon ut',
                'icon' => 'phone-arrow-up-right',
                'color' => 'blue',
                'description' => 'Utgående telefonsamtale',
                'sort_order' => 2,
            ],
            [
                'name' => 'E-post inn',
                'icon' => 'inbox-arrow-down',
                'color' => 'green',
                'description' => 'Innkommende e-post',
                'sort_order' => 3,
            ],
            [
                'name' => 'E-post ut',
                'icon' => 'paper-airplane',
                'color' => 'blue',
                'description' => 'Utgående e-post',
                'sort_order' => 4,
            ],
            [
                'name' => 'Møte',
                'icon' => 'calendar',
                'color' => 'purple',
                'description' => 'Fysisk møte med kontakt',
                'sort_order' => 5,
            ],
            [
                'name' => 'Videomøte',
                'icon' => 'video-camera',
                'color' => 'indigo',
                'description' => 'Videomøte (Teams, Zoom, etc.)',
                'sort_order' => 6,
            ],
            [
                'name' => 'Besøk',
                'icon' => 'map-pin',
                'color' => 'amber',
                'description' => 'Kundebesøk',
                'sort_order' => 7,
            ],
            [
                'name' => 'Oppgave',
                'icon' => 'clipboard-document-check',
                'color' => 'cyan',
                'description' => 'Intern oppgave relatert til kontakt',
                'sort_order' => 8,
            ],
            [
                'name' => 'Notat',
                'icon' => 'document-text',
                'color' => 'zinc',
                'description' => 'Generelt notat',
                'sort_order' => 9,
            ],
        ];

        foreach ($types as $type) {
            ActivityType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
