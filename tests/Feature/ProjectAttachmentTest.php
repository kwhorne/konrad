<?php

use App\Livewire\ProjectManager;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($this->user)->create();
    $this->user->update(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);
    $this->actingAs($this->user);
});

it('shows attachment count when project has attachments', function () {
    $project = Project::factory()->create(['company_id' => $this->company->id]);

    ProjectAttachment::create([
        'project_id' => $project->id,
        'filename' => 'test.pdf',
        'original_filename' => 'document.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
        'path' => 'project-attachments/test.pdf',
        'uploaded_by' => $this->user->id,
    ]);

    $projectWithCount = Project::withCount('attachments')->find($project->id);
    expect($projectWithCount->attachments_count)->toBe(1);

    Livewire::test(ProjectManager::class)
        ->assertSee($project->name)
        ->assertSee('1 vedlegg');
});

it('does not show attachment icon when project has no attachments', function () {
    $project = Project::factory()->create(['company_id' => $this->company->id]);

    $response = Livewire::test(ProjectManager::class);

    $response->assertSee($project->name);
    expect($project->attachments()->count())->toBe(0);
});

it('can upload attachments to a project', function () {
    Storage::fake('public');

    $project = Project::factory()->create(['company_id' => $this->company->id]);
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    Livewire::test(ProjectManager::class)
        ->call('openModal', $project->id)
        ->set('uploadedFiles', [$file])
        ->call('saveAttachments')
        ->assertSet('uploadedFiles', []);

    expect($project->fresh()->attachments()->count())->toBe(1);

    $attachment = $project->attachments()->first();
    expect($attachment->original_filename)->toBe('document.pdf');
    expect($attachment->mime_type)->toBe('application/pdf');
    expect($attachment->uploaded_by)->toBe($this->user->id);

    Storage::disk('public')->assertExists($attachment->path);
});

it('can upload multiple attachments', function () {
    Storage::fake('public');

    $project = Project::factory()->create(['company_id' => $this->company->id]);
    $files = [
        UploadedFile::fake()->create('doc1.pdf', 100, 'application/pdf'),
        UploadedFile::fake()->create('doc2.pdf', 200, 'application/pdf'),
        UploadedFile::fake()->image('image.jpg', 640, 480),
    ];

    Livewire::test(ProjectManager::class)
        ->call('openModal', $project->id)
        ->set('uploadedFiles', $files)
        ->call('saveAttachments');

    expect($project->fresh()->attachments()->count())->toBe(3);
});

it('can delete an attachment', function () {
    Storage::fake('public');

    $project = Project::factory()->create(['company_id' => $this->company->id]);

    $path = 'project-attachments/'.$project->id.'/test.pdf';
    Storage::disk('public')->put($path, 'test content');

    $attachment = ProjectAttachment::create([
        'project_id' => $project->id,
        'filename' => 'test.pdf',
        'original_filename' => 'document.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
        'path' => $path,
        'uploaded_by' => $this->user->id,
    ]);

    Livewire::test(ProjectManager::class)
        ->call('openModal', $project->id)
        ->call('deleteAttachment', $attachment->id);

    expect($project->fresh()->attachments()->count())->toBe(0);
    Storage::disk('public')->assertMissing($path);
});

it('can remove pending upload before saving', function () {
    $project = Project::factory()->create(['company_id' => $this->company->id]);
    $files = [
        UploadedFile::fake()->create('doc1.pdf', 100),
        UploadedFile::fake()->create('doc2.pdf', 100),
    ];

    Livewire::test(ProjectManager::class)
        ->call('openModal', $project->id)
        ->set('uploadedFiles', $files)
        ->call('removeUploadedFile', 0)
        ->assertCount('uploadedFiles', 1);
});

it('loads existing attachments when opening modal', function () {
    $project = Project::factory()->create(['company_id' => $this->company->id]);

    ProjectAttachment::create([
        'project_id' => $project->id,
        'filename' => 'test.pdf',
        'original_filename' => 'existing-doc.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
        'path' => 'project-attachments/test.pdf',
        'uploaded_by' => $this->user->id,
    ]);

    Livewire::test(ProjectManager::class)
        ->call('openModal', $project->id)
        ->assertCount('existingAttachments', 1)
        ->assertSee('existing-doc.pdf');
});

it('clears attachments when closing modal', function () {
    Storage::fake('public');

    $project = Project::factory()->create(['company_id' => $this->company->id]);
    $file = UploadedFile::fake()->create('document.pdf', 100);

    ProjectAttachment::create([
        'project_id' => $project->id,
        'filename' => 'test.pdf',
        'original_filename' => 'existing.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
        'path' => 'project-attachments/test.pdf',
        'uploaded_by' => $this->user->id,
    ]);

    Livewire::test(ProjectManager::class)
        ->call('openModal', $project->id)
        ->set('uploadedFiles', [$file])
        ->assertCount('existingAttachments', 1)
        ->assertCount('uploadedFiles', 1)
        ->call('closeModal')
        ->assertSet('uploadedFiles', [])
        ->assertSet('existingAttachments', []);
});
