<div>
    {{-- Header with filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row flex-wrap gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk etter arbeidsordre..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterStatus" class="w-full sm:w-40">
                <option value="">Alle statuser</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterPriority" class="w-full sm:w-40">
                <option value="">Alle prioriteter</option>
                @foreach($priorities as $priority)
                    <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterType" class="w-full sm:w-40">
                <option value="">Alle typer</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterAssigned" class="w-full sm:w-40">
                <option value="">Alle ansvarlige</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <flux:button wire:click="openModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Ny arbeidsordre
        </flux:button>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <flux:text class="text-green-800 dark:text-green-200">{{ session('success') }}</flux:text>
        </div>
    @endif

    {{-- Work Orders table --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($workOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Arbeidsordre
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Kontakt / Prosjekt
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Prioritet
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Ansvarlig
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Timer / Belop
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Handlinger
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($workOrders as $workOrder)
                                <tr wire:key="work-order-{{ $workOrder->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                {{ $workOrder->title }}
                                            </flux:text>
                                            <div class="flex items-center gap-2 mt-1">
                                                <flux:badge variant="outline">{{ $workOrder->work_order_number }}</flux:badge>
                                                @if($workOrder->due_date)
                                                    <flux:text class="text-xs {{ $workOrder->isOverdue ? 'text-red-600 dark:text-red-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                                                        Frist: {{ $workOrder->due_date->format('d.m.Y') }}
                                                    </flux:text>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            @if($workOrder->contact)
                                                <flux:text class="text-zinc-900 dark:text-white">
                                                    {{ $workOrder->contact->company_name ?? $workOrder->contact->name }}
                                                </flux:text>
                                            @endif
                                            @if($workOrder->project)
                                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $workOrder->project->name }}
                                                </flux:text>
                                            @endif
                                            @if(!$workOrder->contact && !$workOrder->project)
                                                <flux:text class="text-zinc-400 dark:text-zinc-500 italic">-</flux:text>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($workOrder->workOrderType)
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                                {{ $workOrder->workOrderType->name }}
                                            </flux:text>
                                        @else
                                            <flux:text class="text-zinc-400 dark:text-zinc-500 italic">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($workOrder->workOrderStatus)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColorClass($workOrder->workOrderStatus->color) }}">
                                                {{ $workOrder->workOrderStatus->name }}
                                            </span>
                                        @else
                                            <flux:text class="text-zinc-400 dark:text-zinc-500 italic">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($workOrder->workOrderPriority)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColorClass($workOrder->workOrderPriority->color) }}">
                                                {{ $workOrder->workOrderPriority->name }}
                                            </span>
                                        @else
                                            <flux:text class="text-zinc-400 dark:text-zinc-500 italic">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($workOrder->assignedUser)
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                                {{ $workOrder->assignedUser->name }}
                                            </flux:text>
                                        @else
                                            <flux:text class="text-zinc-400 dark:text-zinc-500 italic">Ikke tildelt</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div>
                                            @if($workOrder->estimated_hours || $workOrder->totalHours > 0)
                                                <flux:text class="text-zinc-900 dark:text-white">
                                                    {{ number_format($workOrder->totalHours, 1, ',', ' ') }}
                                                    @if($workOrder->estimated_hours)
                                                        / {{ number_format($workOrder->estimated_hours, 1, ',', ' ') }}
                                                    @endif
                                                    t
                                                </flux:text>
                                            @endif
                                            @if($workOrder->totalAmount > 0)
                                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ number_format($workOrder->totalAmount, 0, ',', ' ') }} kr
                                                </flux:text>
                                            @endif
                                            @if(!$workOrder->estimated_hours && $workOrder->totalHours == 0 && $workOrder->totalAmount == 0)
                                                <flux:text class="text-zinc-400 dark:text-zinc-500 italic">-</flux:text>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button wire:click="openModal({{ $workOrder->id }})" variant="ghost" size="sm">
                                                <flux:icon.pencil class="w-4 h-4" />
                                            </flux:button>
                                            <flux:button wire:click="delete({{ $workOrder->id }})" wire:confirm="Er du sikker på at du vil slette denne arbeidsordren?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
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
                    {{ $workOrders->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.clipboard-document-list class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        @if($search || $filterStatus || $filterPriority || $filterType || $filterAssigned)
                            Ingen arbeidsordrer funnet
                        @else
                            Ingen arbeidsordrer ennå
                        @endif
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        @if($search || $filterStatus || $filterPriority || $filterType || $filterAssigned)
                            Prøv å endre søkekriteriene
                        @else
                            Kom i gang ved å opprette din første arbeidsordre
                        @endif
                    </flux:text>
                    @if(!$search && !$filterStatus && !$filterPriority && !$filterType && !$filterAssigned)
                        <flux:button wire:click="openModal" variant="primary">
                            <flux:icon.plus class="w-5 h-5 mr-2" />
                            Opprett arbeidsordre
                        </flux:button>
                    @endif
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Work Order Flyout Modal --}}
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Rediger arbeidsordre' : 'Ny arbeidsordre' }}
                </flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ $editingId ? 'Oppdater arbeidsordreinformasjon' : 'Opprett en ny arbeidsordre' }}
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Tittel *</flux:label>
                    <flux:input wire:model="title" type="text" placeholder="Tittel på arbeidsordren" />
                    @error('title')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:textarea wire:model="description" rows="3" placeholder="Beskrivelse av arbeidet..."></flux:textarea>
                    @error('description')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
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

                    <flux:field>
                        <flux:label>Prosjekt</flux:label>
                        <flux:select wire:model="project_id">
                            <option value="">Velg prosjekt</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </flux:select>
                        @error('project_id')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>Type</flux:label>
                        <flux:select wire:model="work_order_type_id">
                            <option value="">Velg type</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </flux:select>
                        @error('work_order_type_id')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select wire:model="work_order_status_id">
                            <option value="">Velg status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </flux:select>
                        @error('work_order_status_id')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Prioritet</flux:label>
                        <flux:select wire:model="work_order_priority_id">
                            <option value="">Velg prioritet</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                            @endforeach
                        </flux:select>
                        @error('work_order_priority_id')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Ansvarlig</flux:label>
                    <flux:select wire:model="assigned_to">
                        <option value="">Velg ansvarlig</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('assigned_to')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Planlagt dato</flux:label>
                        <flux:input wire:model="scheduled_date" type="date" />
                        @error('scheduled_date')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Forfallsdato</flux:label>
                        <flux:input wire:model="due_date" type="date" />
                        @error('due_date')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Estimerte timer</flux:label>
                        <flux:input wire:model="estimated_hours" type="number" step="0.5" min="0" placeholder="0" />
                        @error('estimated_hours')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Budsjett (kr)</flux:label>
                        <flux:input wire:model="budget" type="number" step="0.01" min="0" placeholder="0.00" />
                        @error('budget')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Interne notater</flux:label>
                    <flux:textarea wire:model="internal_notes" rows="2" placeholder="Interne notater (ikke synlig for kunde)..."></flux:textarea>
                    @error('internal_notes')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="is_active" label="Aktiv" />
                </flux:field>
            </div>

            {{-- Work Order Lines Section (only when editing) --}}
            @if($editingId)
                <flux:separator />

                <div>
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="md">Linjer (timer og produkter)</flux:heading>
                        <flux:button wire:click="openLineModal" variant="ghost" size="sm">
                            <flux:icon.plus class="w-4 h-4 mr-1" />
                            Legg til linje
                        </flux:button>
                    </div>

                    @if(count($workOrderLines) > 0)
                        <div class="space-y-2">
                            @foreach($workOrderLines as $line)
                                <div wire:key="line-{{ $line['id'] }}" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <flux:badge variant="{{ $line['line_type'] === 'time' ? 'info' : 'success' }}" size="sm">
                                                {{ $line['line_type'] === 'time' ? 'Timer' : 'Produkt' }}
                                            </flux:badge>
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                {{ $line['description'] ?? 'Ingen beskrivelse' }}
                                            </flux:text>
                                        </div>
                                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                            {{ number_format($line['quantity'], 2, ',', ' ') }} x {{ number_format($line['unit_price'], 2, ',', ' ') }} kr
                                            @if($line['discount_percent'] > 0)
                                                ({{ number_format($line['discount_percent'], 0) }}% rabatt)
                                            @endif
                                            @if($line['line_type'] === 'time' && $line['performed_at'])
                                                &middot; {{ \Carbon\Carbon::parse($line['performed_at'])->format('d.m.Y') }}
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

                            <div class="flex justify-between pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    Timer totalt: {{ number_format(collect($workOrderLines)->where('line_type', 'time')->sum('quantity'), 1, ',', ' ') }} t
                                </flux:text>
                                <flux:text class="font-semibold text-zinc-900 dark:text-white">
                                    Totalt: {{ number_format(collect($workOrderLines)->sum(fn($l) => $l['quantity'] * $l['unit_price'] * (1 - $l['discount_percent'] / 100)), 2, ',', ' ') }} kr
                                </flux:text>
                            </div>
                        </div>
                    @else
                        <flux:text class="text-zinc-500 dark:text-zinc-400 text-center py-4">
                            Ingen linjer ennå. Klikk "Legg til linje" for å registrere timer eller produkter.
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

    {{-- Work Order Line Modal --}}
    <flux:modal wire:model="showLineModal" variant="flyout" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingLineId ? 'Rediger linje' : 'Ny linje' }}
                </flux:heading>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Linjetype *</flux:label>
                    <flux:radio.group wire:model.live="line_type" variant="segmented" class="w-full">
                        <flux:radio value="time" label="Timer" />
                        <flux:radio value="product" label="Produkt" />
                    </flux:radio.group>
                </flux:field>

                @if($line_type === 'product')
                    <flux:field>
                        <flux:label>Produkt</flux:label>
                        <flux:select wire:model.live="line_product_id">
                            <option value="">Velg produkt...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:input wire:model="line_description" type="text" placeholder="Beskrivelse av arbeid/produkt" />
                    @error('line_description')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ $line_type === 'time' ? 'Timer' : 'Antall' }} *</flux:label>
                        <flux:input wire:model="line_quantity" type="number" step="0.01" min="0.01" placeholder="1" />
                        @error('line_quantity')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ $line_type === 'time' ? 'Timepris' : 'Enhetspris' }} *</flux:label>
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

                @if($line_type === 'time')
                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Utfort dato</flux:label>
                            <flux:input wire:model="line_performed_at" type="date" />
                            @error('line_performed_at')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>Utfort av</flux:label>
                            <flux:select wire:model="line_performed_by">
                                <option value="">Velg bruker</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </flux:select>
                            @error('line_performed_by')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>
                    </div>
                @endif
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
