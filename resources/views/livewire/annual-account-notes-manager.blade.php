<div>
    {{-- Header with actions --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ $notes->where('is_visible', true)->count() }} av {{ $notes->count() }} noter er synlige
            </flux:text>
        </div>

        @if($annualAccount->canBeEdited())
            <flux:button wire:click="openModal" variant="primary">
                <flux:icon.plus class="w-5 h-5 mr-2" />
                Ny note
            </flux:button>
        @endif
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <flux:text class="text-green-800 dark:text-green-200">{{ session('success') }}</flux:text>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <flux:text class="text-red-800 dark:text-red-200">{{ session('error') }}</flux:text>
        </div>
    @endif

    {{-- Notes list --}}
    <div class="space-y-4">
        @forelse($notes as $note)
            <flux:card wire:key="note-{{ $note->id }}" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 {{ !$note->is_visible ? 'opacity-50' : '' }}">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <flux:badge variant="outline">Note {{ $note->note_number }}</flux:badge>
                                @if($note->is_required)
                                    <flux:badge variant="warning">Påkrevd</flux:badge>
                                @endif
                                @if(!$note->is_visible)
                                    <flux:badge variant="outline">Skjult</flux:badge>
                                @endif
                            </div>
                            <flux:heading size="md" class="text-zinc-900 dark:text-white mb-2">
                                {{ $note->title }}
                            </flux:heading>
                            <div class="prose prose-sm dark:prose-invert max-w-none">
                                {!! nl2br(e(Str::limit($note->content, 300))) !!}
                            </div>
                        </div>
                        @if($annualAccount->canBeEdited())
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="toggleVisibility({{ $note->id }})" variant="ghost" size="sm" title="{{ $note->is_visible ? 'Skjul' : 'Vis' }}">
                                    @if($note->is_visible)
                                        <flux:icon.eye class="w-4 h-4" />
                                    @else
                                        <flux:icon.eye-slash class="w-4 h-4" />
                                    @endif
                                </flux:button>
                                <flux:button wire:click="openModal({{ $note->id }})" variant="ghost" size="sm">
                                    <flux:icon.pencil class="w-4 h-4" />
                                </flux:button>
                                @if(!$note->is_required)
                                    <flux:button wire:click="delete({{ $note->id }})" wire:confirm="Er du sikker på at du vil slette denne noten?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                                        <flux:icon.trash class="w-4 h-4" />
                                    </flux:button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </flux:card>
        @empty
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-12 text-center">
                    <flux:icon.document-text class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        Ingen noter
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        Legg til noter for årsregnskapet
                    </flux:text>
                    @if($annualAccount->canBeEdited())
                        <flux:button wire:click="openModal" variant="primary">
                            <flux:icon.plus class="w-5 h-5 mr-2" />
                            Ny note
                        </flux:button>
                    @endif
                </div>
            </flux:card>
        @endforelse
    </div>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Rediger note' : 'Ny note' }}
                </flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ $editingId ? 'Oppdater noteinnhold' : 'Opprett en ny note' }}
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                @if(!$editingId)
                    <div>
                        <flux:label>Bruk mal</flux:label>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($noteTypes as $type => $info)
                                <flux:button wire:click="useTemplate('{{ $type }}')" variant="outline" size="sm">
                                    {{ $info['title'] }}
                                </flux:button>
                            @endforeach
                        </div>
                    </div>
                    <flux:separator />
                @endif

                <flux:field>
                    <flux:label>Type *</flux:label>
                    <flux:select wire:model.live="note_type">
                        <option value="">Velg type</option>
                        @foreach($noteTypes as $type => $info)
                            <option value="{{ $type }}">{{ $info['title'] }}</option>
                        @endforeach
                    </flux:select>
                    @error('note_type')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Tittel *</flux:label>
                    <flux:input wire:model="title" type="text" placeholder="Notetittel" />
                    @error('title')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Innhold *</flux:label>
                    <flux:textarea wire:model="content" rows="12" placeholder="Noteinnhold..."></flux:textarea>
                    <flux:description>Støtter markdown-formatering</flux:description>
                    @error('content')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="is_visible" label="Synlig i årsregnskapet" />
                </flux:field>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeModal" variant="ghost">
                    Avbryt
                </flux:button>
                <flux:button wire:click="save" variant="primary">
                    {{ $editingId ? 'Oppdater' : 'Opprett' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
