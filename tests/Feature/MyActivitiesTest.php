<?php

use App\Livewire\MyActivitiesManager;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Quote;
use App\Models\QuoteStatus;
use App\Models\UserNote;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;
use App\Services\MyActivitiesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = createTestCompanyContext();
    $this->actingAs($this->user);
});

test('my activities page is accessible', function () {
    $response = $this->get(route('my-activities'));

    $response->assertStatus(200);
    $response->assertSee('Mine aktiviteter');
});

test('livewire component renders initial state', function () {
    Livewire::test(MyActivitiesManager::class)
        ->assertStatus(200)
        ->assertSee('Forslag til aktiviteter')
        ->assertSee('Generer forslag');
});

test('livewire component shows notes tab', function () {
    Livewire::test(MyActivitiesManager::class)
        ->set('activeTab', 'notes')
        ->assertSee('Ingen notater ennå')
        ->assertSee('Opprett notat');
});

test('service gathers user data', function () {
    $service = app(MyActivitiesService::class);

    $data = $service->gatherUserData($this->user);

    expect($data)->toHaveKeys([
        'user',
        'company',
        'analysis_date',
        'activities',
        'quotes',
        'work_orders',
        'projects',
        'invoices',
    ]);

    expect($data['user']['name'])->toBe($this->user->name);
    expect($data['company']['name'])->toBe($this->company->name);
});

test('service gathers activities assigned to user', function () {
    $service = app(MyActivitiesService::class);

    $activityType = ActivityType::factory()->create([
        'company_id' => $this->company->id,
    ]);

    Activity::factory()->count(3)->create([
        'company_id' => $this->company->id,
        'assigned_to' => $this->user->id,
        'activity_type_id' => $activityType->id,
        'is_completed' => false,
    ]);

    $data = $service->gatherUserData($this->user);

    expect($data['activities']['pending_count'])->toBe(3);
});

test('service gathers quotes created by user', function () {
    $service = app(MyActivitiesService::class);

    $draftStatus = QuoteStatus::factory()->create([
        'company_id' => $this->company->id,
        'code' => 'draft',
    ]);

    Quote::factory()->count(2)->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
        'quote_status_id' => $draftStatus->id,
    ]);

    $data = $service->gatherUserData($this->user);

    expect($data['quotes']['draft_count'])->toBe(2);
});

test('service gathers work orders assigned to user', function () {
    $service = app(MyActivitiesService::class);

    $status = WorkOrderStatus::factory()->create([
        'company_id' => $this->company->id,
        'code' => 'NEW',
    ]);

    WorkOrder::factory()->count(2)->create([
        'company_id' => $this->company->id,
        'assigned_to' => $this->user->id,
        'work_order_status_id' => $status->id,
    ]);

    $data = $service->gatherUserData($this->user);

    expect($data['work_orders']['pending_count'])->toBe(2);
});

test('service gathers projects where user is manager', function () {
    $service = app(MyActivitiesService::class);

    $status = ProjectStatus::factory()->create([
        'company_id' => $this->company->id,
        'code' => 'IN_PROGRESS',
    ]);

    Project::factory()->count(2)->create([
        'company_id' => $this->company->id,
        'manager_id' => $this->user->id,
        'project_status_id' => $status->id,
        'is_active' => true,
    ]);

    $data = $service->gatherUserData($this->user);

    expect($data['projects']['active_count'])->toBe(2);
});

test('service gathers invoices created by user', function () {
    $service = app(MyActivitiesService::class);

    $status = InvoiceStatus::factory()->create([
        'company_id' => $this->company->id,
        'code' => 'sent',
    ]);

    Invoice::factory()->count(2)->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
        'invoice_status_id' => $status->id,
        'invoice_type' => 'invoice',
        'balance' => 1000,
    ]);

    $data = $service->gatherUserData($this->user);

    expect($data['invoices']['unpaid_count'])->toBe(2);
});

test('user can create a note', function () {
    Livewire::test(MyActivitiesManager::class)
        ->call('openNoteModal')
        ->assertSet('showNoteModal', true)
        ->set('noteTitle', 'Test Note')
        ->set('noteContent', 'This is a test note content')
        ->call('saveNote')
        ->assertSet('showNoteModal', false);

    $this->assertDatabaseHas('user_notes', [
        'user_id' => $this->user->id,
        'title' => 'Test Note',
        'content' => 'This is a test note content',
    ]);
});

test('user can edit a note', function () {
    $note = UserNote::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Original Title',
        'content' => 'Original content',
    ]);

    Livewire::test(MyActivitiesManager::class)
        ->call('openNoteModal', $note->id)
        ->assertSet('editingNoteId', $note->id)
        ->assertSet('noteTitle', 'Original Title')
        ->set('noteTitle', 'Updated Title')
        ->set('noteContent', 'Updated content')
        ->call('saveNote');

    $this->assertDatabaseHas('user_notes', [
        'id' => $note->id,
        'title' => 'Updated Title',
        'content' => 'Updated content',
    ]);
});

test('user can delete a note', function () {
    $note = UserNote::factory()->create([
        'user_id' => $this->user->id,
    ]);

    Livewire::test(MyActivitiesManager::class)
        ->call('deleteNote', $note->id);

    $this->assertDatabaseMissing('user_notes', [
        'id' => $note->id,
    ]);
});

test('user can pin a note', function () {
    $note = UserNote::factory()->create([
        'user_id' => $this->user->id,
        'is_pinned' => false,
    ]);

    Livewire::test(MyActivitiesManager::class)
        ->call('togglePinNote', $note->id);

    $this->assertDatabaseHas('user_notes', [
        'id' => $note->id,
        'is_pinned' => true,
    ]);
});

test('user can unpin a note', function () {
    $note = UserNote::factory()->create([
        'user_id' => $this->user->id,
        'is_pinned' => true,
    ]);

    Livewire::test(MyActivitiesManager::class)
        ->call('togglePinNote', $note->id);

    $this->assertDatabaseHas('user_notes', [
        'id' => $note->id,
        'is_pinned' => false,
    ]);
});

test('user cannot edit another users note', function () {
    $otherUser = \App\Models\User::factory()->create();
    $note = UserNote::factory()->create([
        'user_id' => $otherUser->id,
        'title' => 'Other user note',
    ]);

    Livewire::test(MyActivitiesManager::class)
        ->call('openNoteModal', $note->id)
        ->assertSet('editingNoteId', null)
        ->assertSet('noteTitle', '');
});

test('user cannot delete another users note', function () {
    $otherUser = \App\Models\User::factory()->create();
    $note = UserNote::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Livewire::test(MyActivitiesManager::class)
        ->call('deleteNote', $note->id);

    $this->assertDatabaseHas('user_notes', [
        'id' => $note->id,
    ]);
});

test('notes are ordered by pinned status and updated date', function () {
    $note1 = UserNote::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'First',
        'is_pinned' => false,
        'updated_at' => now()->subDay(),
    ]);

    $note2 = UserNote::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Second (Pinned)',
        'is_pinned' => true,
        'updated_at' => now()->subDays(2),
    ]);

    $note3 = UserNote::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Third',
        'is_pinned' => false,
        'updated_at' => now(),
    ]);

    $notes = UserNote::where('user_id', $this->user->id)->ordered()->get();

    expect($notes->first()->title)->toBe('Second (Pinned)');
    expect($notes->last()->title)->toBe('First');
});

test('livewire component can reset suggestions', function () {
    Livewire::test(MyActivitiesManager::class)
        ->set('hasSuggestions', true)
        ->set('suggestions', ['summary' => 'Test', 'priority_score' => 50])
        ->call('resetSuggestions')
        ->assertSet('hasSuggestions', false)
        ->assertSet('suggestions', null);
});

test('note content is required', function () {
    Livewire::test(MyActivitiesManager::class)
        ->call('openNoteModal')
        ->set('noteTitle', 'Test')
        ->set('noteContent', '')
        ->call('saveNote')
        ->assertHasErrors(['noteContent']);
});

test('notes follow user not company', function () {
    // Create a note
    $note = UserNote::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'My Personal Note',
    ]);

    // The note should not have a company_id column
    expect($note->getAttributes())->not->toHaveKey('company_id');

    // The note should be accessible regardless of company context
    $this->assertDatabaseHas('user_notes', [
        'user_id' => $this->user->id,
        'title' => 'My Personal Note',
    ]);
});
