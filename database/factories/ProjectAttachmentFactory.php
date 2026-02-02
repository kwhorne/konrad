<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectAttachment>
 */
class ProjectAttachmentFactory extends Factory
{
    public function definition(): array
    {
        $extension = fake()->randomElement(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'png']);
        $filename = Str::uuid().'.'.$extension;
        $originalFilename = fake()->word().'.'.$extension;

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        return [
            'project_id' => Project::factory(),
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeTypes[$extension],
            'size' => fake()->numberBetween(1024, 10485760),
            'path' => 'project-attachments/'.$filename,
            'uploaded_by' => User::factory(),
        ];
    }

    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'filename' => Str::uuid().'.pdf',
            'original_filename' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
        ]);
    }

    public function image(): static
    {
        $extension = fake()->randomElement(['jpg', 'png']);

        return $this->state(fn (array $attributes) => [
            'filename' => Str::uuid().'.'.$extension,
            'original_filename' => fake()->word().'.'.$extension,
            'mime_type' => $extension === 'jpg' ? 'image/jpeg' : 'image/png',
        ]);
    }
}
