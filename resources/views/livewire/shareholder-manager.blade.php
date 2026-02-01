<div>
    {{-- Header with filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk etter aksjonær..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterType" class="w-full sm:w-40">
                <option value="">Alle typer</option>
                <option value="person">Person</option>
                <option value="company">Selskap</option>
            </flux:select>

            <flux:select wire:model.live="filterActive" class="w-full sm:w-40">
                <option value="">Alle statuser</option>
                <option value="1">Aktive</option>
                <option value="0">Inaktive</option>
            </flux:select>
        </div>

        <flux:button wire:click="openModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Ny aksjonær
        </flux:button>
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

    {{-- Shareholders table --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($shareholders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Aksjonaer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Identifikator
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Aksjer
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Eierandel
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Handlinger
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($shareholders as $shareholder)
                                <tr wire:key="shareholder-{{ $shareholder->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                {{ $shareholder->name }}
                                            </flux:text>
                                            @if($shareholder->email)
                                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $shareholder->email }}
                                                </flux:text>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:badge variant="{{ $shareholder->getTypeBadgeColor() }}">
                                            {{ $shareholder->getTypeLabel() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ $shareholder->getIdentifier() }}
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                            {{ number_format($shareholder->getTotalShares(), 0, ',', ' ') }}
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($shareholder->getOwnershipPercentage(), 2, ',', ' ') }}%
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button wire:click="toggleActive({{ $shareholder->id }})">
                                            <flux:badge variant="{{ $shareholder->is_active ? 'success' : 'outline' }}">
                                                {{ $shareholder->is_active ? 'Aktiv' : 'Inaktiv' }}
                                            </flux:badge>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button wire:click="openModal({{ $shareholder->id }})" variant="ghost" size="sm">
                                                <flux:icon.pencil class="w-4 h-4" />
                                            </flux:button>
                                            <flux:button wire:click="delete({{ $shareholder->id }})" wire:confirm="Er du sikker på at du vil slette denne aksjonæren?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                                                <flux:icon.trash class="w-4 h-4" />
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $shareholders->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.users class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        @if($search || $filterType || $filterActive !== '')
                            Ingen aksjonærer funnet
                        @else
                            Ingen aksjonærer ennå
                        @endif
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        @if($search || $filterType || $filterActive !== '')
                            Prøv å endre søkekriteriene
                        @else
                            Kom i gang ved å registrere din første aksjonær
                        @endif
                    </flux:text>
                    @if(!$search && !$filterType && $filterActive === '')
                        <flux:button wire:click="openModal" variant="primary">
                            <flux:icon.plus class="w-5 h-5 mr-2" />
                            Registrer aksjonær
                        </flux:button>
                    @endif
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Rediger aksjonær' : 'Ny aksjonær' }}
                </flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ $editingId ? 'Oppdater aksjonærinfo' : 'Registrer en ny aksjonær' }}
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Type aksjonær *</flux:label>
                    <flux:select wire:model.live="shareholder_type">
                        <option value="person">Person</option>
                        <option value="company">Selskap</option>
                    </flux:select>
                    @error('shareholder_type')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Navn *</flux:label>
                    <flux:input wire:model="name" type="text" placeholder="{{ $shareholder_type === 'company' ? 'Selskapsnavn' : 'Fullt navn' }}" />
                    @error('name')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                @if($shareholder_type === 'person')
                    <flux:field>
                        <flux:label>Fodselsnummer</flux:label>
                        <flux:input wire:model="national_id" type="text" placeholder="11 siffer" maxlength="11" />
                        <flux:description>Krypteres ved lagring</flux:description>
                        @error('national_id')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                @else
                    <flux:field>
                        <flux:label>Organisasjonsnummer</flux:label>
                        <flux:input wire:model="organization_number" type="text" placeholder="9 siffer" maxlength="9" />
                        @error('organization_number')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Land *</flux:label>
                    <flux:select wire:model="country_code">
                        <option value="NO">Norge</option>
                        <option value="SE">Sverige</option>
                        <option value="DK">Danmark</option>
                        <option value="FI">Finland</option>
                        <option value="GB">Storbritannia</option>
                        <option value="US">USA</option>
                        <option value="DE">Tyskland</option>
                    </flux:select>
                    @error('country_code')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Adresse</flux:label>
                    <flux:input wire:model="address" type="text" placeholder="Gateadresse" />
                    @error('address')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Postnummer</flux:label>
                        <flux:input wire:model="postal_code" type="text" placeholder="0000" />
                        @error('postal_code')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Poststed</flux:label>
                        <flux:input wire:model="city" type="text" placeholder="By" />
                        @error('city')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>E-post</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="epost@eksempel.no" />
                    @error('email')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Telefon</flux:label>
                    <flux:input wire:model="phone" type="text" placeholder="+47 XXX XX XXX" />
                    @error('phone')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Kobling til kontakt</flux:label>
                    <flux:select wire:model="contact_id">
                        <option value="">Ingen kobling</option>
                        @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->company_name ?? $contact->contact_number }}</option>
                        @endforeach
                    </flux:select>
                    @error('contact_id')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Notater</flux:label>
                    <flux:textarea wire:model="notes" rows="3" placeholder="Interne notater..."></flux:textarea>
                    @error('notes')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="is_active" label="Aktiv aksjonær" />
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
