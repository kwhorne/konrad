<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PostManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $search = '';

    public string $filterCategory = '';

    public string $filterStatus = '';

    // Form fields
    public string $title = '';

    public string $slug = '';

    public ?string $excerpt = null;

    public ?string $body = null;

    public $featured_image = null;

    public ?string $existing_image = null;

    public ?int $post_category_id = null;

    public bool $is_published = false;

    public ?string $published_at = null;

    public ?string $meta_title = null;

    public ?string $meta_description = null;

    // Category modal
    public bool $showCategoryModal = false;

    public string $newCategoryName = '';

    public ?string $newCategoryDescription = null;

    protected function rules(): array
    {
        $slugRule = 'required|string|max:255|unique:posts,slug';
        if ($this->editingId) {
            $slugRule .= ','.$this->editingId;
        }

        return [
            'title' => 'required|string|max:255',
            'slug' => $slugRule,
            'excerpt' => 'nullable|string|max:500',
            'body' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'post_category_id' => 'nullable|exists:post_categories,id',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ];
    }

    protected array $messages = [
        'title.required' => 'Tittel er påkrevd.',
        'slug.required' => 'URL-slug er påkrevd.',
        'slug.unique' => 'Denne URL-slugen er allerede i bruk.',
        'featured_image.image' => 'Filen må være et bilde.',
        'featured_image.max' => 'Bildet kan ikke være større enn 2MB.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedTitle(): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $this->editingId = $id;
            $post = Post::findOrFail($id);

            $this->title = $post->title;
            $this->slug = $post->slug;
            $this->excerpt = $post->excerpt;
            $this->body = $post->body;
            $this->existing_image = $post->featured_image;
            $this->post_category_id = $post->post_category_id;
            $this->is_published = $post->is_published;
            $this->published_at = $post->published_at?->format('Y-m-d\TH:i');
            $this->meta_title = $post->meta_title;
            $this->meta_description = $post->meta_description;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'post_category_id' => $this->post_category_id ?: null,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at ?: null,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
        ];

        if ($this->featured_image) {
            $data['featured_image'] = $this->featured_image->store('posts', 'public');
        } elseif ($this->existing_image === null && $this->editingId) {
            $data['featured_image'] = null;
        }

        if ($this->editingId) {
            $post = Post::findOrFail($this->editingId);
            $post->update($data);
            session()->flash('success', 'Artikkelen ble oppdatert.');
        } else {
            $data['author_id'] = auth()->id();
            Post::create($data);
            session()->flash('success', 'Artikkelen ble opprettet.');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        Post::findOrFail($id)->delete();
        session()->flash('success', 'Artikkelen ble slettet.');
    }

    public function togglePublished(int $id): void
    {
        $post = Post::findOrFail($id);
        $post->update([
            'is_published' => ! $post->is_published,
            'published_at' => ! $post->is_published ? now() : $post->published_at,
        ]);
    }

    public function removeImage(): void
    {
        if ($this->featured_image) {
            $this->featured_image = null;
        }
        $this->existing_image = null;
    }

    public function openCategoryModal(): void
    {
        $this->newCategoryName = '';
        $this->newCategoryDescription = null;
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
        $this->newCategoryName = '';
        $this->newCategoryDescription = null;
    }

    public function createCategory(): void
    {
        $this->validate([
            'newCategoryName' => 'required|string|max:255|unique:post_categories,name',
        ], [
            'newCategoryName.required' => 'Kategorinavn er påkrevd.',
            'newCategoryName.unique' => 'Denne kategorien finnes allerede.',
        ]);

        $category = PostCategory::create([
            'name' => $this->newCategoryName,
            'slug' => Str::slug($this->newCategoryName),
            'description' => $this->newCategoryDescription,
        ]);

        $this->post_category_id = $category->id;
        $this->closeCategoryModal();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->slug = '';
        $this->excerpt = null;
        $this->body = null;
        $this->featured_image = null;
        $this->existing_image = null;
        $this->post_category_id = null;
        $this->is_published = false;
        $this->published_at = null;
        $this->meta_title = null;
        $this->meta_description = null;
        $this->resetValidation();
    }

    public function render()
    {
        $query = Post::with(['category', 'author'])->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('excerpt', 'like', '%'.$this->search.'%')
                    ->orWhere('body', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterCategory) {
            $query->where('post_category_id', $this->filterCategory);
        }

        if ($this->filterStatus === 'published') {
            $query->where('is_published', true);
        } elseif ($this->filterStatus === 'draft') {
            $query->where('is_published', false);
        }

        return view('livewire.admin.post-manager', [
            'posts' => $query->paginate(15),
            'categories' => PostCategory::ordered()->get(),
        ]);
    }
}
