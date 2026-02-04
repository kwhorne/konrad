<x-layouts.public
    :title="($post->meta_title ?? $post->title) . ' - Innsikt - Konrad Office'"
    :description="$post->meta_description ?? $post->excerpt ?? $post->excerpt_or_truncated_body"
    :image="$post->featured_image ? Storage::url($post->featured_image) : null"
    type="article"
>
    @php
        $articleSchema = [
            "@context" => "https://schema.org",
            "@type" => "BlogPosting",
            "@id" => route('blog.show', $post->slug) . "#article",
            "mainEntityOfPage" => [
                "@type" => "WebPage",
                "@id" => route('blog.show', $post->slug)
            ],
            "headline" => $post->title,
            "description" => $post->meta_description ?? $post->excerpt_or_truncated_body,
            "datePublished" => $post->published_at?->toISOString() ?? $post->created_at->toISOString(),
            "dateModified" => $post->updated_at->toISOString(),
            "publisher" => [
                "@type" => "Organization",
                "name" => "Konrad Office",
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => url('/images/konrad-logo.png')
                ]
            ],
            "wordCount" => str_word_count(strip_tags($post->body ?? '')),
            "inLanguage" => "nb-NO"
        ];
        $articleSchema["author"] = [
            "@type" => "Organization",
            "name" => "Konrad Office AS"
        ];
        if ($post->featured_image) {
            $articleSchema["image"] = Storage::url($post->featured_image);
        }
    @endphp
    @push('jsonld')
    <script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush

    <section class="py-16 lg:py-24 bg-white dark:bg-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Breadcrumb -->
                    <nav class="mb-8">
                        <ol class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                            <li>
                                <a href="{{ route('blog.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    Innsikt
                                </a>
                            </li>
                            @if($post->category)
                                <li class="flex items-center gap-2">
                                    <flux:icon.chevron-right class="w-4 h-4" />
                                    <a href="{{ route('blog.category', $post->category->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ $post->category->name }}
                                    </a>
                                </li>
                            @endif
                        </ol>
                    </nav>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-zinc-900 dark:text-white mb-6 leading-tight">
                        {{ $post->title }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-4 text-zinc-600 dark:text-zinc-400 mb-8">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                                <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                    KO
                                </span>
                            </div>
                            <span>Konrad Office AS</span>
                        </div>
                        <span class="hidden sm:inline">&middot;</span>
                        <time datetime="{{ $post->published_at?->toDateString() }}">
                            {{ $post->published_at?->format('d. F Y') ?? $post->created_at->format('d. F Y') }}
                        </time>
                        <span class="hidden sm:inline">&middot;</span>
                        <div class="flex items-center gap-1">
                            <flux:icon.clock class="w-4 h-4" />
                            <span>{{ $post->reading_time }} min lesing</span>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    @if($post->featured_image)
                        <div class="mb-10 rounded-2xl overflow-hidden shadow-xl">
                            <img src="{{ Storage::url($post->featured_image) }}"
                                 alt="{{ $post->title }}"
                                 class="w-full h-auto">
                        </div>
                    @endif

                    <!-- Article Body -->
                    <div class="
                        prose prose-lg dark:prose-invert max-w-none
                        prose-headings:font-bold prose-headings:text-zinc-900 dark:prose-headings:text-white
                        prose-h1:text-4xl prose-h1:mt-12 prose-h1:mb-6 first:prose-h1:mt-0
                        prose-h2:text-3xl prose-h2:mt-10 prose-h2:mb-4 prose-h2:border-b prose-h2:border-zinc-200 prose-h2:pb-3 dark:prose-h2:border-zinc-700 first:prose-h2:mt-0
                        prose-h3:text-2xl prose-h3:mt-8 prose-h3:mb-3 first:prose-h3:mt-0
                        prose-h4:text-xl prose-h4:mt-6 prose-h4:mb-2
                        prose-p:text-zinc-600 dark:prose-p:text-zinc-400 prose-p:leading-relaxed
                        prose-a:text-indigo-600 dark:prose-a:text-indigo-400 prose-a:no-underline hover:prose-a:underline
                        prose-strong:text-zinc-900 dark:prose-strong:text-white
                        prose-ul:my-6 prose-ol:my-6
                        prose-li:text-zinc-600 dark:prose-li:text-zinc-400
                        prose-blockquote:border-l-4 prose-blockquote:border-indigo-500 prose-blockquote:bg-zinc-50 dark:prose-blockquote:bg-zinc-800/50 prose-blockquote:py-1 prose-blockquote:px-6 prose-blockquote:rounded-r-lg
                        prose-code:bg-zinc-100 dark:prose-code:bg-zinc-800 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-indigo-600 dark:prose-code:text-indigo-400
                        prose-pre:bg-zinc-900 dark:prose-pre:bg-zinc-950 prose-pre:rounded-xl
                        prose-img:rounded-xl prose-img:shadow-lg
                        [&>*:first-child]:mt-0
                    ">
                        {!! $post->body !!}
                    </div>

                    <!-- Share Section -->
                    <div class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-800">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Del denne artikkelen</h3>
                        <div class="flex gap-3">
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-indigo-100 hover:text-indigo-600 dark:hover:bg-indigo-900/50 dark:hover:text-indigo-400 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                </svg>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-indigo-100 hover:text-indigo-600 dark:hover:bg-indigo-900/50 dark:hover:text-indigo-400 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </a>
                            <a href="mailto:?subject={{ urlencode($post->title) }}&body={{ urlencode(request()->url()) }}"
                               class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-indigo-100 hover:text-indigo-600 dark:hover:bg-indigo-900/50 dark:hover:text-indigo-400 transition-colors">
                                <flux:icon.envelope class="w-5 h-5" />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <aside class="lg:col-span-1">
                    <div class="sticky top-8 space-y-8">
                        <!-- Categories -->
                        @if($categories->isNotEmpty())
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-6">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                                    <flux:icon.folder class="w-5 h-5 text-indigo-500" />
                                    Kategorier
                                </h3>
                                <ul class="space-y-2">
                                    @foreach($categories as $category)
                                        <li>
                                            <a href="{{ route('blog.category', $category->slug) }}"
                                               class="flex items-center justify-between py-2 px-3 rounded-lg text-zinc-600 dark:text-zinc-400 hover:bg-white dark:hover:bg-zinc-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                                <span>{{ $category->name }}</span>
                                                <span class="text-sm text-zinc-400 dark:text-zinc-500">{{ $category->posts_count }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Featured Posts -->
                        @if($featuredPosts->isNotEmpty())
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-6">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                                    <flux:icon.fire class="w-5 h-5 text-orange-500" />
                                    Populære artikler
                                </h3>
                                <ul class="space-y-4">
                                    @foreach($featuredPosts as $featuredPost)
                                        <li>
                                            <a href="{{ route('blog.show', $featuredPost->slug) }}"
                                               class="group block">
                                                <h4 class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-2">
                                                    {{ $featuredPost->title }}
                                                </h4>
                                                <div class="flex items-center gap-2 mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                    <flux:icon.eye class="w-3 h-3" />
                                                    <span>{{ number_format($featuredPost->views) }} visninger</span>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <!-- Related Posts -->
    @if($relatedPosts->isNotEmpty())
        <section class="py-16 lg:py-24 bg-zinc-50 dark:bg-zinc-800/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-8">Relaterte artikler</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($relatedPosts as $relatedPost)
                        <article class="bg-white dark:bg-zinc-900 rounded-2xl overflow-hidden shadow-sm border border-zinc-100 dark:border-zinc-800 hover:shadow-lg transition-shadow group">
                            @if($relatedPost->featured_image)
                                <a href="{{ route('blog.show', $relatedPost->slug) }}" class="block aspect-video overflow-hidden">
                                    <img src="{{ Storage::url($relatedPost->featured_image) }}"
                                         alt="{{ $relatedPost->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </a>
                            @else
                                <a href="{{ route('blog.show', $relatedPost->slug) }}" class="block aspect-video bg-gradient-to-br from-indigo-100 to-orange-100 dark:from-indigo-900/30 dark:to-orange-900/30 flex items-center justify-center">
                                    <flux:icon.document-text class="w-12 h-12 text-indigo-400" />
                                </a>
                            @endif

                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                                    <a href="{{ route('blog.show', $relatedPost->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        {{ $relatedPost->title }}
                                    </a>
                                </h3>

                                <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                                    <flux:icon.clock class="w-4 h-4" />
                                    <span>{{ $relatedPost->reading_time }} min lesing</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- CTA -->
    <section class="py-16 lg:py-24 bg-gradient-to-r from-indigo-600 to-orange-500">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">
                Klar til a effektivisere bedriften?
            </h2>
            <p class="text-lg text-indigo-100 mb-8">
                Prøv Konrad Office gratis og se hvordan vi kan hjelpe deg.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <flux:button href="{{ route('contact') }}" variant="primary" class="px-8 py-3 bg-white text-indigo-600 hover:bg-gray-50 border-white">
                    Ta kontakt
                </flux:button>
                <flux:button href="{{ route('blog.index') }}" variant="ghost" class="px-8 py-3 text-white border-white/30 hover:bg-white/10">
                    Flere artikler
                </flux:button>
            </div>
        </div>
    </section>
</x-layouts.public>
