<?php

use App\Livewire\ReleaseNotesModal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('modal shows when user has not seen current version', function () {
    $this->actingAs($this->user);

    Livewire::test(ReleaseNotesModal::class)
        ->assertSet('showModal', true)
        ->assertSet('currentVersion', config('app.version'))
        ->assertSee('Hva er nytt?');
});

test('modal does not show when user has seen current version', function () {
    $this->user->update(['seen_version' => config('app.version')]);
    $this->actingAs($this->user);

    Livewire::test(ReleaseNotesModal::class)
        ->assertSet('showModal', false);
});

test('user can mark version as seen', function () {
    $this->actingAs($this->user);

    Livewire::test(ReleaseNotesModal::class)
        ->assertSet('showModal', true)
        ->call('markAsSeen')
        ->assertSet('showModal', false);

    expect($this->user->fresh()->seen_version)->toBe(config('app.version'));
});

test('modal shows when version is upgraded', function () {
    $this->user->update(['seen_version' => '0.9.0']);
    $this->actingAs($this->user);

    Livewire::test(ReleaseNotesModal::class)
        ->assertSet('showModal', true);
});

test('release notes content is loaded from markdown file', function () {
    $this->actingAs($this->user);

    Livewire::test(ReleaseNotesModal::class)
        ->assertSet('showModal', true)
        ->assertSee('Konrad Office');
});

test('release notes strips raw HTML from markdown', function () {
    // Temporarily override RELEASENOTES.md with malicious content
    $path = base_path('RELEASENOTES.md');
    $original = file_get_contents($path);

    file_put_contents($path, "# Test\n\n<script>alert('xss')</script>\n\nSafe content");

    $this->actingAs($this->user);

    $component = Livewire::test(ReleaseNotesModal::class);
    $releaseNotes = $component->get('releaseNotes');

    expect($releaseNotes)->not->toContain('<script>')
        ->and($releaseNotes)->toContain('Safe content');

    // Restore original file
    file_put_contents($path, $original);
});
