<div>
    {{-- Compact contact person cards --}}
    <div class="space-y-3">
        @if(count($persons) > 0)
            <div class="grid grid-cols-1 gap-3">
                @foreach($persons as $index => $person)
                    <div class="flex items-center gap-3 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-blue-600 dark:text-blue-400 font-semibold text-xs">
                                {{ strtoupper(substr($person['name'], 0, 2)) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $person['name'] }}</span>
                                @if($person['is_primary'])
                                    <flux:badge variant="success" size="sm">Primær</flux:badge>
                                @endif
                                @if(!$person['is_active'])
                                    <flux:badge variant="outline" size="sm">Inaktiv</flux:badge>
                                @endif
                                @if($person['title'])
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $person['title'] }}@if($person['department']) · {{ $person['department'] }}@endif</span>
                                @endif
                            </div>
                            @if($person['email'] || $person['phone'])
                                <div class="flex items-center gap-4 mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    @if($person['email'])
                                        <span class="flex items-center gap-1">
                                            <flux:icon.envelope class="w-3.5 h-3.5" />
                                            {{ $person['email'] }}
                                        </span>
                                    @endif
                                    @if($person['phone'])
                                        <span class="flex items-center gap-1">
                                            <flux:icon.phone class="w-3.5 h-3.5" />
                                            {{ $person['phone'] }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-1">
                            @if(!$person['is_primary'])
                                <flux:button type="button" wire:click="setPrimary({{ $index }})" variant="ghost" size="sm" title="Sett som primær">
                                    <flux:icon.star class="w-4 h-4" />
                                </flux:button>
                            @endif
                            <flux:button type="button" wire:click="openModal({{ $index }})" variant="ghost" size="sm">
                                <flux:icon.pencil class="w-4 h-4" />
                            </flux:button>
                            <flux:button type="button" wire:click="deletePerson({{ $index }})" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                                <flux:icon.trash class="w-4 h-4" />
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700">
                <flux:icon.users class="h-12 w-12 text-zinc-400 mx-auto mb-3" />
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    Ingen kontaktpersoner lagt til ennå
                </flux:text>
            </div>
        @endif

        <flux:button type="button" wire:click="openModal" variant="primary" class="w-full">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Legg til kontaktperson
        </flux:button>
    </div>

    <flux:modal wire:model="showModal" class="min-w-[600px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingIndex !== null ? 'Rediger kontaktperson' : 'Ny kontaktperson' }}
                </flux:heading>
            </div>

            <flux:separator />

            <div class="space-y-6">
                <flux:field>
                    <flux:label>Navn *</flux:label>
                    <flux:input wire:model="name" type="text" />
                    @error('name')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Tittel</flux:label>
                        <flux:input wire:model="title" type="text" />
                        @error('title')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Avdeling</flux:label>
                        <flux:input wire:model="department" type="text" />
                        @error('department')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>E-post</flux:label>
                        <flux:input wire:model="email" type="email" />
                        @error('email')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Telefon</flux:label>
                        <flux:input wire:model="phone" type="text" />
                        @error('phone')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>LinkedIn</flux:label>
                    <flux:input wire:model="linkedin" type="url" placeholder="https://" />
                    @error('linkedin')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Fødselsdag</flux:label>
                    <flux:input wire:model="birthday" type="date" />
                    @error('birthday')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Notater</flux:label>
                    <flux:textarea wire:model="notes" rows="3"></flux:textarea>
                    @error('notes')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="space-y-3">
                    <flux:checkbox wire:model="is_primary" label="Primærkontakt" />
                    <flux:checkbox wire:model="is_active" label="Aktiv" />
                </div>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button type="button" wire:click="closeModal" variant="ghost">
                    Avbryt
                </flux:button>
                <flux:button type="button" wire:click="savePerson" variant="primary">
                    {{ $editingIndex !== null ? 'Oppdater' : 'Legg til' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <input type="hidden" name="contact_persons" id="contact_persons" value='@json($persons)'>
</div>

@script
<script>
    $wire.on('persons-updated', (event) => {
        document.getElementById('contact_persons').value = JSON.stringify(event.persons);
    });
</script>
@endscript
