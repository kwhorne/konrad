<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $query = Post::query()
            ->with(['category', 'author'])
            ->published();

        // Search filter
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categorySlug = request('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        $posts = $query->ordered()->paginate(9);

        $categories = PostCategory::query()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->roots()
            ->ordered()
            ->get();

        $popularPosts = Post::query()
            ->with(['category'])
            ->published()
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        return view('blog.index', compact('posts', 'categories', 'popularPosts'));
    }

    public function show(string $slug): View
    {
        $post = Post::query()
            ->with(['category', 'author'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $post->incrementViews();

        $relatedPosts = Post::query()
            ->with(['category'])
            ->published()
            ->where('id', '!=', $post->id)
            ->when($post->post_category_id, fn ($q) => $q->where('post_category_id', $post->post_category_id))
            ->ordered()
            ->limit(3)
            ->get();

        $categories = PostCategory::query()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->roots()
            ->ordered()
            ->get();

        $featuredPosts = Post::query()
            ->with(['category'])
            ->published()
            ->where('id', '!=', $post->id)
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        return view('blog.show', compact('post', 'relatedPosts', 'categories', 'featuredPosts'));
    }

    public function category(string $slug): View
    {
        $category = PostCategory::where('slug', $slug)->firstOrFail();

        $posts = Post::query()
            ->with(['category', 'author'])
            ->published()
            ->where('post_category_id', $category->id)
            ->ordered()
            ->paginate(9);

        $categories = PostCategory::query()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->roots()
            ->ordered()
            ->get();

        $popularPosts = Post::query()
            ->with(['category'])
            ->published()
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        return view('blog.index', compact('posts', 'categories', 'category', 'popularPosts'));
    }
}
