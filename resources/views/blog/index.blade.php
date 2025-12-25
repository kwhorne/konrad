<x-layouts.public
    :title="(isset($category) ? $category->name . ' - Innsikt' : 'Innsikt - Tips og guider for bedrifter') . ' - Konrad Office'"
    :description="isset($category) && $category->description ? $category->description : 'Les artikler om regnskap, fakturering, prosjektstyring og andre tips for a drive bedrift i Norge. Innsikt fra Konrad Office.'"
>
    @php
        $blogSchema = [
            "@context" => "https://schema.org",
            "@type" => "Blog",
            "@id" => route('blog.index') . "#blog",
            "url" => route('blog.index'),
            "name" => "Innsikt - Konrad Office",
            "description" => "Tips, guider og nyheter for deg som driver bedrift i Norge",
            "publisher" => [
                "@type" => "Organization",
                "name" => "Konrad Office",
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => url('/images/konrad-logo.png')
                ]
            ],
            "inLanguage" => "nb-NO"
        ];
    @endphp
    @push('jsonld')
    <script type="application/ld+json">{!! json_encode($blogSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush
    <!-- Hero Section -->
    <section class="bg-white dark:bg-zinc-800 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-zinc-900 dark:text-white sm:text-5xl">
                    @if(isset($category))
                        {{ $category->name }}
                    @else
                        Innsikt
                    @endif
                </h1>
                <p class="mt-4 text-xl text-zinc-600 dark:text-zinc-300 max-w-2xl mx-auto">
                    @if(isset($category) && $category->description)
                        {{ $category->description }}
                    @else
                        Tips, guider og nyheter for deg som driver bedrift i Norge.
                    @endif
                </p>
            </div>

            <!-- Search and Filter Bar -->
            <div class="mt-8 max-w-2xl mx-auto">
                <flux:card class="p-6">
                    <form method="GET" action="{{ route('blog.index') }}" class="space-y-4">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <flux:input
                                    name="search"
                                    placeholder="Sok i artikler..."
                                    value="{{ request('search') }}"
                                    icon="magnifying-glass"
                                />
                            </div>
                            <div class="sm:w-48">
                                <flux:select name="category">
                                    <option value="">Alle kategorier</option>
                                    @foreach($categories as $cat)
                                        <option
                                            value="{{ $cat->slug }}"
                                            {{ request('category') === $cat->slug ? 'selected' : '' }}
                                        >
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </flux:select>
                            </div>
                            <flux:button type="submit" variant="primary">
                                Sok
                            </flux:button>
                        </div>
                    </form>
                </flux:card>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-zinc-50 dark:bg-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Blog Posts -->
                <div class="lg:col-span-2">
                    @if($posts->count() > 0)
                        <div class="grid gap-8">
                            @foreach($posts as $post)
                                <flux:card class="overflow-hidden hover:shadow-lg transition-shadow duration-300">
                                    <div class="md:flex">
                                        <!-- Featured Image -->
                                        @if($post->featured_image)
                                            <div class="md:w-1/3 shrink-0">
                                                <a href="{{ route('blog.show', $post->slug) }}">
                                                    <img
                                                        src="{{ Storage::url($post->featured_image) }}"
                                                        alt="{{ $post->title }}"
                                                        class="w-full h-48 md:h-full object-cover"
                                                    />
                                                </a>
                                            </div>
                                        @endif

                                        <!-- Content -->
                                        <div class="p-6 flex-1">
                                            <!-- Category & Date -->
                                            <div class="flex items-center flex-wrap gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-3">
                                                @if($post->category)
                                                    <flux:badge color="indigo" size="sm">
                                                        {{ $post->category->name }}
                                                    </flux:badge>
                                                @endif
                                                <span class="hidden sm:inline">&middot;</span>
                                                <time datetime="{{ $post->published_at?->toDateString() }}">
                                                    {{ $post->published_at?->format('d. M Y') ?? $post->created_at->format('d. M Y') }}
                                                </time>
                                                <span class="hidden sm:inline">&middot;</span>
                                                <span>{{ number_format($post->views) }} visninger</span>
                                            </div>

                                            <!-- Title -->
                                            <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-3 hover:text-indigo-600 dark:hover:text-indigo-400">
                                                <a href="{{ route('blog.show', $post->slug) }}">
                                                    {{ $post->title }}
                                                </a>
                                            </h2>

                                            <!-- Description -->
                                            <p class="text-zinc-600 dark:text-zinc-300 mb-4 line-clamp-2">
                                                {{ $post->excerpt_or_truncated_body }}
                                            </p>

                                            <!-- Author & Read More -->
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    @if($post->author)
                                                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                                                {{ substr($post->author->name, 0, 2) }}
                                                            </span>
                                                        </div>
                                                        <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                                            {{ $post->author->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <flux:button
                                                    variant="ghost"
                                                    size="sm"
                                                    href="{{ route('blog.show', $post->slug) }}"
                                                >
                                                    Les mer
                                                    <flux:icon.arrow-right class="w-4 h-4 ml-1" />
                                                </flux:button>
                                            </div>
                                        </div>
                                    </div>
                                </flux:card>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($posts->hasPages())
                            <div class="mt-12">
                                {{ $posts->appends(request()->query())->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <flux:card class="p-8">
                                <flux:icon.document-text class="w-12 h-12 mx-auto text-zinc-400 mb-4" />
                                <div class="text-zinc-500 dark:text-zinc-400 text-lg">
                                    Ingen artikler funnet
                                </div>
                                <p class="mt-2 text-zinc-400 dark:text-zinc-500">
                                    Prov a endre sokekriteriene dine.
                                </p>
                            </flux:card>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Featured Posts -->
                    @if($popularPosts->count() > 0)
                        <flux:card class="p-6">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                                <flux:icon.fire class="w-5 h-5 text-orange-500" />
                                Populaere artikler
                            </h3>
                            <div class="space-y-4">
                                @foreach($popularPosts as $popular)
                                    <div class="flex gap-3">
                                        @if($popular->featured_image)
                                            <img
                                                src="{{ Storage::url($popular->featured_image) }}"
                                                alt="{{ $popular->title }}"
                                                class="w-16 h-16 object-cover rounded-lg shrink-0"
                                            />
                                        @else
                                            <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-indigo-100 to-orange-100 dark:from-indigo-900/30 dark:to-orange-900/30 flex items-center justify-center shrink-0">
                                                <flux:icon.document-text class="w-6 h-6 text-indigo-400" />
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-medium text-zinc-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 line-clamp-2">
                                                <a href="{{ route('blog.show', $popular->slug) }}">
                                                    {{ $popular->title }}
                                                </a>
                                            </h4>
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ number_format($popular->views) }} visninger
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </flux:card>
                    @endif

                    <!-- Categories -->
                    @if($categories->count() > 0)
                        <flux:card class="p-6">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                                <flux:icon.folder class="w-5 h-5 text-indigo-500" />
                                Kategorier
                            </h3>
                            <div class="space-y-2">
                                <a
                                    href="{{ route('blog.index') }}"
                                    class="flex items-center justify-between p-2 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors {{ !isset($category) && !request('category') ? 'bg-indigo-50 dark:bg-indigo-900/30' : '' }}"
                                >
                                    <span class="text-zinc-700 dark:text-zinc-300">Alle artikler</span>
                                </a>
                                @foreach($categories as $cat)
                                    <a
                                        href="{{ route('blog.category', $cat->slug) }}"
                                        class="flex items-center justify-between p-2 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors {{ (isset($category) && $category->id === $cat->id) || request('category') === $cat->slug ? 'bg-indigo-50 dark:bg-indigo-900/30' : '' }}"
                                    >
                                        <span class="text-zinc-700 dark:text-zinc-300">{{ $cat->name }}</span>
                                        <flux:badge color="zinc" size="sm">
                                            {{ $cat->posts_count }}
                                        </flux:badge>
                                    </a>
                                @endforeach
                            </div>
                        </flux:card>
                    @endif

                    <!-- Newsletter Signup -->
                    <flux:card class="p-6 bg-gradient-to-br from-indigo-50 to-orange-50 dark:from-indigo-900/20 dark:to-orange-900/20 border-0">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                            Hold deg oppdatert
                        </h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-300 mb-4">
                            Vil du vite mer om hvordan Konrad Office kan hjelpe din bedrift?
                        </p>
                        <flux:button href="{{ route('contact') }}" variant="primary" class="w-full">
                            Ta kontakt
                        </flux:button>
                    </flux:card>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
