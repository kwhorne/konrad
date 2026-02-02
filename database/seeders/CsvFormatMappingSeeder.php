<?php

namespace Database\Seeders;

use App\Models\CsvFormatMapping;
use Illuminate\Database\Seeder;

class CsvFormatMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formats = CsvFormatMapping::getSystemFormats();

        foreach ($formats as $key => $format) {
            CsvFormatMapping::updateOrCreate(
                [
                    'bank_name' => $format['bank_name'],
                    'is_system' => true,
                ],
                [
                    'company_id' => null,
                    'name' => $format['name'],
                    'delimiter' => $format['delimiter'],
                    'encoding' => $format['encoding'],
                    'date_format' => $format['date_format'],
                    'has_header' => $format['has_header'],
                    'column_mapping' => $format['column_mapping'],
                    'is_active' => true,
                    'is_system' => true,
                ]
            );
        }

        $this->command->info('Created '.count($formats).' system CSV format mappings.');
    }
}
