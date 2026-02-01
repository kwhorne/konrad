<div>
    {{-- Header with filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk etter artikkel..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterCategory" class="w-full sm:w-48">
                <option value="">Alle kategorier</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterStatus" class="w-full sm:w-48">
                <option value="">Alle statuser</option>
                <option value="published">Publisert</option>
                <option value="draft">Utkast</option>
            </flux:select>
        </div>

        <flux:button wire:click="openModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Ny artikkel
        </flux:button>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <flux:text class="text-green-800 dark:text-green-200">{{ session('success') }}</flux:text>
        </div>
    @endif

    {{-- Posts table --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($posts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Artikkel
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Kategori
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Forfatter
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Visninger
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Handlinger
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($posts as $post)
                                <tr wire:key="post-{{ $post->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            @if($post->featured_image)
                                                <img src="{{ Storage::url($post->featured_image) }}" alt="" class="w-12 h-12 rounded object-cover">
                                            @else
                                                <div class="w-12 h-12 rounded bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                                                    <flux:icon.document-text class="w-6 h-6 text-zinc-400" />
                                                </div>
                                            @endif
                                            <div>
                                                <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                    {{ $post->title }}
                                                </flux:text>
                                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    /innsikt/{{ $post->slug }}
                                                </flux:text>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($post->category)
                                            <flux:badge variant="outline">{{ $post->category->name }}</flux:badge>
                                        @else
                                            <flux:text class="text-zinc-400">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ $post->author?->name ?? '-' }}
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($post->is_published)
                                            <flux:badge variant="success">Publisert</flux:badge>
                                            @if($post->published_at)
                                                <flux:text class="text-xs text-zinc-500 mt-1">
                                                    {{ $post->published_at->format('d.m.Y H:i') }}
                                                </flux:text>
                                            @endif
                                        @else
                                            <flux:badge variant="warning">Utkast</flux:badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($post->views) }}
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank">
                                                <flux:button variant="ghost" size="sm" title="Se artikkel">
                                                    <flux:icon.eye class="w-4 h-4" />
                                                </flux:button>
                                            </a>
                                            <flux:button wire:click="togglePublished({{ $post->id }})" variant="ghost" size="sm" title="{{ $post->is_published ? 'Avpubliser' : 'Publiser' }}">
                                                @if($post->is_published)
                                                    <flux:icon.eye-slash class="w-4 h-4" />
                                                @else
                                                    <flux:icon.check class="w-4 h-4" />
                                                @endif
                                            </flux:button>
                                            <flux:button wire:click="openModal({{ $post->id }})" variant="ghost" size="sm" title="Rediger">
                                                <flux:icon.pencil class="w-4 h-4" />
                                            </flux:button>
                                            <flux:button wire:click="delete({{ $post->id }})" wire:confirm="Er du sikker på at du vil slette denne artikkelen?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700" title="Slett">
                                                <flux:icon.trash class="w-4 h-4" />
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.document-text class="w-12 h-12 mx-auto text-zinc-400 mb-4" />
                    <flux:heading size="lg">Ingen artikler</flux:heading>
                    <flux:text class="text-zinc-500 dark:text-zinc-400 mt-2">
                        Opprett din første artikkel for å komme i gang.
                    </flux:text>
                    <flux:button wire:click="openModal" variant="primary" class="mt-4">
                        <flux:icon.plus class="w-5 h-5 mr-2" />
                        Ny artikkel
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="w-full max-w-4xl">
        <div class="p-6">
            <flux:heading size="lg" class="mb-6">
                {{ $editingId ? 'Rediger artikkel' : 'Ny artikkel' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <flux:field>
                            <flux:label>Tittel *</flux:label>
                            <flux:input wire:model.live.debounce.500ms="title" placeholder="Skriv inn tittel..." />
                            <flux:error name="title" />
                        </flux:field>
                    </div>

                    <div class="md:col-span-2">
                        <flux:field>
                            <flux:label>URL-slug *</flux:label>
                            <flux:input wire:model="slug" placeholder="url-slug">
                                <x-slot name="iconLeading">
                                    <span class="text-zinc-400 text-sm">/innsikt/</span>
                                </x-slot>
                            </flux:input>
                            <flux:error name="slug" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Kategori</flux:label>
                            <flux:select wire:model="post_category_id">
                                <option value="">Velg kategori...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="post_category_id" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Publiseringsdato</flux:label>
                            <flux:input type="datetime-local" wire:model="published_at" />
                            <flux:error name="published_at" />
                        </flux:field>
                    </div>

                    <div class="md:col-span-2">
                        <flux:field>
                            <flux:label>Utdrag</flux:label>
                            <flux:textarea wire:model="excerpt" rows="2" placeholder="Kort beskrivelse av artikkelen..." />
                            <flux:description>Vises i artikkeloversikten. Maks 500 tegn.</flux:description>
                            <flux:error name="excerpt" />
                        </flux:field>
                    </div>

                    <div class="md:col-span-2">
                        <flux:editor
                            wire:model="body"
                            label="Innhold"
                            description:trailing="Bruk verktoylinjen for formatering av tekst."
                            placeholder="Skriv innholdet her..."
                        />
                        <flux:error name="body" />
                    </div>

                    <div class="md:col-span-2">
                        <flux:file-upload wire:model="featured_image" label="Fremhevet bilde" accept="image/*">
                            <flux:file-upload.dropzone
                                heading="Slipp bilde her eller klikk for a bla"
                                text="JPG, PNG, GIF opptil 2MB. Anbefalt: 1200x630px"
                                with-progress
                                inline
                            />
                        </flux:file-upload>

                        <div class="mt-3 flex flex-col gap-2">
                            @if($featured_image)
                                <flux:file-item
                                    :heading="$featured_image->getClientOriginalName()"
                                    :image="$featured_image->temporaryUrl()"
                                    :size="$featured_image->getSize()"
                                >
                                    <x-slot name="actions">
                                        <flux:file-item.remove wire:click="removeImage" aria-label="Fjern bilde" />
                                    </x-slot>
                                </flux:file-item>
                            @elseif($existing_image)
                                <flux:file-item
                                    heading="Eksisterende bilde"
                                    :image="Storage::url($existing_image)"
                                >
                                    <x-slot name="actions">
                                        <flux:file-item.remove wire:click="removeImage" aria-label="Fjern bilde" />
                                    </x-slot>
                                </flux:file-item>
                            @endif
                        </div>

                        <flux:error name="featured_image" />
                    </div>

                    <div class="md:col-span-2 border-t border-zinc-200 dark:border-zinc-700 pt-6">
                        <flux:heading size="sm" class="mb-4">SEO-innstillinger</flux:heading>

                        <div class="space-y-4">
                            <flux:field>
                                <flux:label>Meta-tittel</flux:label>
                                <flux:input wire:model="meta_title" placeholder="Tittel for søkemotorer..." />
                                <flux:description>La stå tom for å bruke artikkeltittelen.</flux:description>
                                <flux:error name="meta_title" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Meta-beskrivelse</flux:label>
                                <flux:textarea wire:model="meta_description" rows="2" placeholder="Beskrivelse for søkemotorer..." />
                                <flux:description>Anbefalt lengde: 150-160 tegn.</flux:description>
                                <flux:error name="meta_description" />
                            </flux:field>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <flux:switch wire:model="is_published" label="Publisert" description="Gjør artikkelen synlig på nettsiden" />
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button wire:click="closeModal" variant="ghost" type="button">
                        Avbryt
                    </flux:button>
                    <flux:button variant="primary" type="submit">
                        {{ $editingId ? 'Lagre endringer' : 'Opprett artikkel' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
