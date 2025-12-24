<div>
    {{-- Header with filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Sok etter prosjekt..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterStatus" class="w-full sm:w-48">
                <option value="">Alle statuser</option>
                @foreach($projectStatuses as $status)
                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterType" class="w-full sm:w-48">
                <option value="">Alle typer</option>
                @foreach($projectTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterContact" class="w-full sm:w-48">
                <option value="">Alle kontakter</option>
                @foreach($contacts as $contact)
                    <option value="{{ $contact->id }}">{{ $contact->company_name ?? $contact->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <flux:button wire:click="openModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Nytt prosjekt
        </flux:button>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <flux:text class="text-green-800 dark:text-green-200">{{ session('success') }}</flux:text>
        </div>
    @endif

    {{-- Projects table --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($projects->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Prosjekt
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Kontakt
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Budsjett
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Timer
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Handlinger
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($projects as $project)
                                <tr wire:key="project-{{ $project->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                {{ $project->name }}
                                            </flux:text>
                                            <flux:badge variant="outline" class="mt-1">{{ $project->project_number }}</flux:badge>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($project->contact)
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                                {{ $project->contact->company_name ?? $project->contact->name }}
                                            </flux:text>
                                        @else
                                            <flux:text class="text-zinc-400 dark:text-zinc-500 italic">
                                                Ingen kontakt
                                            </flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($project->projectType)
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                                {{ $project->projectType->name }}
                                            </flux:text>
                                        @else
                                            <flux:text class="text-zinc-400 dark:text-zinc-500 italic">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($project->projectStatus)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColorClass($project->projectStatus->color) }}">
                                                {{ $project->projectStatus->name }}
                                            </span>
                                        @else
                                            <flux:text class="text-zinc-400 dark:text-zinc-500 italic">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if($project->budget)
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                {{ number_format($project->budget, 0, ',', ' ') }} kr
                                            </flux:text>
                                            @if($project->total > 0)
                                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    Brukt: {{ number_format($project->total, 0, ',', ' ') }} kr
                                                </flux:text>
                                            @endif
                                        @else
                                            <flux:text class="text-zinc-400 dark:text-zinc-500 italic">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if($project->estimated_hours)
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                                {{ number_format($project->estimated_hours, 1, ',', ' ') }} t
                                            </flux:text>
                                        @else
                                            <flux:text class="text-zinc-400 dark:text-zinc-500 italic">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button wire:click="openModal({{ $project->id }})" variant="ghost" size="sm">
                                                <flux:icon.pencil class="w-4 h-4" />
                                            </flux:button>
                                            <flux:button wire:click="delete({{ $project->id }})" wire:confirm="Er du sikker pa at du vil slette dette prosjektet?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
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
                    {{ $projects->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.folder class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        @if($search || $filterType || $filterStatus || $filterContact)
                            Ingen prosjekter funnet
                        @else
                            Ingen prosjekter enna
                        @endif
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        @if($search || $filterType || $filterStatus || $filterContact)
                            Prov a endre sokekriteriene
                        @else
                            Kom i gang ved a opprette ditt forste prosjekt
                        @endif
                    </flux:text>
                    @if(!$search && !$filterType && !$filterStatus && !$filterContact)
                        <flux:button wire:click="openModal" variant="primary">
                            <flux:icon.plus class="w-5 h-5 mr-2" />
                            Opprett prosjekt
                        </flux:button>
                    @endif
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Project Flyout Modal --}}
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Rediger prosjekt' : 'Nytt prosjekt' }}
                </flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ $editingId ? 'Oppdater prosjektinformasjon' : 'Opprett et nytt prosjekt' }}
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Prosjektnavn *</flux:label>
                    <flux:input wire:model="name" type="text" placeholder="Prosjektnavn" />
                    @error('name')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:textarea wire:model="description" rows="3" placeholder="Prosjektbeskrivelse..."></flux:textarea>
                    @error('description')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Kontakt</flux:label>
                    <flux:select wire:model="contact_id">
                        <option value="">Velg kontakt</option>
                        @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->company_name ?? $contact->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('contact_id')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Prosjekttype</flux:label>
                        <flux:select wire:model="project_type_id">
                            <option value="">Velg type</option>
                            @foreach($projectTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </flux:select>
                        @error('project_type_id')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select wire:model="project_status_id">
                            <option value="">Velg status</option>
                            @foreach($projectStatuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </flux:select>
                        @error('project_status_id')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Startdato</flux:label>
                        <flux:input wire:model="start_date" type="date" />
                        @error('start_date')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Sluttdato</flux:label>
                        <flux:input wire:model="end_date" type="date" />
                        @error('end_date')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Budsjett (kr)</flux:label>
                        <flux:input wire:model="budget" type="number" step="0.01" min="0" placeholder="0.00" />
                        @error('budget')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Estimerte timer</flux:label>
                        <flux:input wire:model="estimated_hours" type="number" step="0.5" min="0" placeholder="0" />
                        @error('estimated_hours')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:checkbox wire:model="is_active" label="Aktiv" />
                </flux:field>
            </div>

            {{-- Project Lines Section (only when editing) --}}
            @if($editingId)
                <flux:separator />

                <div>
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="md">Prosjektlinjer</flux:heading>
                        <flux:button wire:click="openLineModal" variant="ghost" size="sm">
                            <flux:icon.plus class="w-4 h-4 mr-1" />
                            Legg til linje
                        </flux:button>
                    </div>

                    @if(count($projectLines) > 0)
                        <div class="space-y-2">
                            @foreach($projectLines as $line)
                                <div wire:key="line-{{ $line['id'] }}" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                    <div class="flex-1">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                            {{ $line['description'] ?? 'Ingen beskrivelse' }}
                                        </flux:text>
                                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ number_format($line['quantity'], 2, ',', ' ') }} x {{ number_format($line['unit_price'], 2, ',', ' ') }} kr
                                            @if($line['discount_percent'] > 0)
                                                ({{ number_format($line['discount_percent'], 0) }}% rabatt)
                                            @endif
                                        </flux:text>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                            {{ number_format($line['quantity'] * $line['unit_price'] * (1 - $line['discount_percent'] / 100), 2, ',', ' ') }} kr
                                        </flux:text>
                                        <div class="flex items-center gap-1">
                                            <flux:button wire:click="openLineModal({{ $line['id'] }})" variant="ghost" size="sm">
                                                <flux:icon.pencil class="w-3 h-3" />
                                            </flux:button>
                                            <flux:button wire:click="deleteLine({{ $line['id'] }})" wire:confirm="Vil du slette denne linjen?" variant="ghost" size="sm" class="text-red-600">
                                                <flux:icon.trash class="w-3 h-3" />
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="flex justify-end pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                <flux:text class="font-semibold text-zinc-900 dark:text-white">
                                    Totalt: {{ number_format(collect($projectLines)->sum(fn($l) => $l['quantity'] * $l['unit_price'] * (1 - $l['discount_percent'] / 100)), 2, ',', ' ') }} kr
                                </flux:text>
                            </div>
                        </div>
                    @else
                        <flux:text class="text-zinc-500 dark:text-zinc-400 text-center py-4">
                            Ingen prosjektlinjer enna. Klikk "Legg til linje" for a legge til produkter.
                        </flux:text>
                    @endif
                </div>
            @endif

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

    {{-- Project Line Modal --}}
    <flux:modal wire:model="showLineModal" variant="flyout" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingLineId ? 'Rediger prosjektlinje' : 'Ny prosjektlinje' }}
                </flux:heading>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Produkt (valgfritt)</flux:label>
                    <flux:select wire:model.live="line_product_id">
                        <option value="">Velg produkt...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:input wire:model="line_description" type="text" placeholder="Beskrivelse av linjen" />
                    @error('line_description')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Antall *</flux:label>
                        <flux:input wire:model="line_quantity" type="number" step="0.01" min="0.01" placeholder="1" />
                        @error('line_quantity')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Enhetspris *</flux:label>
                        <flux:input wire:model="line_unit_price" type="number" step="0.01" min="0" placeholder="0.00" />
                        @error('line_unit_price')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Rabatt (%)</flux:label>
                    <flux:input wire:model="line_discount_percent" type="number" step="0.01" min="0" max="100" placeholder="0" />
                    @error('line_discount_percent')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeLineModal" variant="ghost">
                    Avbryt
                </flux:button>
                <flux:button wire:click="saveLine" variant="primary">
                    {{ $editingLineId ? 'Oppdater' : 'Legg til' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
