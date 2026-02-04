<?php

use App\Livewire\Admin\PostManager;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createAdminUserForPostTest(): User
{
    return User::factory()->create([
        'is_admin' => true,
        'onboarding_completed' => true,
    ]);
}

describe('PostManager Component', function () {
    test('renders successfully for admin', function () {
        $admin = createAdminUserForPostTest();

        $this->actingAs($admin);

        Livewire::test(PostManager::class)
            ->assertOk();
    });

    test('can open category modal', function () {
        $admin = createAdminUserForPostTest();

        $this->actingAs($admin);

        Livewire::test(PostManager::class)
            ->assertSet('showCategoryModal', false)
            ->call('openCategoryModal')
            ->assertSet('showCategoryModal', true);
    });

    test('can close category modal', function () {
        $admin = createAdminUserForPostTest();

        $this->actingAs($admin);

        Livewire::test(PostManager::class)
            ->call('openCategoryModal')
            ->assertSet('showCategoryModal', true)
            ->call('closeCategoryModal')
            ->assertSet('showCategoryModal', false);
    });

    test('can create new category', function () {
        $admin = createAdminUserForPostTest();

        $this->actingAs($admin);

        Livewire::test(PostManager::class)
            ->set('newCategoryName', 'Test Kategori')
            ->set('newCategoryDescription', 'Test beskrivelse')
            ->call('createCategory')
            ->assertSet('showCategoryModal', false);

        expect(PostCategory::where('name', 'Test Kategori')->exists())->toBeTrue();
        $category = PostCategory::where('name', 'Test Kategori')->first();
        expect($category->slug)->toBe('test-kategori')
            ->and($category->description)->toBe('Test beskrivelse');
    });

    test('newly created category is auto-selected', function () {
        $admin = createAdminUserForPostTest();

        $this->actingAs($admin);

        $component = Livewire::test(PostManager::class)
            ->set('newCategoryName', 'Auto Selected')
            ->call('createCategory');

        $category = PostCategory::where('name', 'Auto Selected')->first();

        $component->assertSet('post_category_id', $category->id);
    });

    test('category creation validates required name', function () {
        $admin = createAdminUserForPostTest();

        $this->actingAs($admin);

        Livewire::test(PostManager::class)
            ->set('newCategoryName', '')
            ->call('createCategory')
            ->assertHasErrors(['newCategoryName']);
    });

    test('category creation validates unique name', function () {
        $admin = createAdminUserForPostTest();
        PostCategory::factory()->create(['name' => 'Existing Category']);

        $this->actingAs($admin);

        Livewire::test(PostManager::class)
            ->set('newCategoryName', 'Existing Category')
            ->call('createCategory')
            ->assertHasErrors(['newCategoryName']);
    });

    test('can create post with category', function () {
        $admin = createAdminUserForPostTest();
        $category = PostCategory::factory()->create();

        $this->actingAs($admin);

        Livewire::test(PostManager::class)
            ->set('title', 'Test Post')
            ->set('slug', 'test-post')
            ->set('post_category_id', $category->id)
            ->call('save');

        expect(Post::where('title', 'Test Post')
            ->where('post_category_id', $category->id)
            ->exists())->toBeTrue();
    });
});
