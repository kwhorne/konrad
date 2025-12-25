<div>
    {{-- Header with filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:select wire:model.live="filterYear" class="w-full sm:w-32">
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterType" class="w-full sm:w-48">
                <option value="">Alle typer</option>
                <option value="permanent">Permanente</option>
                <option value="temporary_deductible">Midlertidige (fradrag)</option>
                <option value="temporary_taxable">Midlertidige (skattbare)</option>
            </flux:select>
        </div>

        <flux:button wire:click="openModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Ny forskjell
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

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Permanente forskjeller</flux:text>
                <flux:heading size="xl" class="mt-2 {{ $permanentTotal >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                    {{ number_format($permanentTotal, 0, ',', ' ') }} kr
                </flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ $permanentTotal >= 0 ? 'Oker' : 'Reduserer' }} skattepliktig inntekt
                </flux:text>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Midlertidige forskjeller</flux:text>
                <flux:heading size="xl" class="mt-2 {{ $temporaryTotal >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                    {{ number_format($temporaryTotal, 0, ',', ' ') }} kr
                </flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Pavirker utsatt skatt
                </flux:text>
            </div>
        </flux:card>
    </div>

    {{-- Adjustments table --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($adjustments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Beskrivelse
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Regnskapsmessig
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Skattemessig
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Forskjell
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Handlinger
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($adjustments as $adjustment)
                                <tr wire:key="adjustment-{{ $adjustment->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                {{ $adjustment->description }}
                                            </flux:text>
                                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $categories[$adjustment->category] ?? $adjustment->category }}
                                            </flux:text>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:badge variant="{{ $adjustment->getTypeBadgeColor() }}">
                                            {{ $adjustment->getTypeLabel() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($adjustment->accounting_amount, 0, ',', ' ') }} kr
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($adjustment->tax_amount, 0, ',', ' ') }} kr
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium {{ $adjustment->difference >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                            {{ number_format($adjustment->difference, 0, ',', ' ') }} kr
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button wire:click="openModal({{ $adjustment->id }})" variant="ghost" size="sm">
                                                <flux:icon.pencil class="w-4 h-4" />
                                            </flux:button>
                                            <flux:button wire:click="delete({{ $adjustment->id }})" wire:confirm="Er du sikker på at du vil slette denne forskjellen?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
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
                    {{ $adjustments->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.adjustments-horizontal class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        Ingen forskjeller registrert
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        Registrer permanente og midlertidige skatteforskjeller
                    </flux:text>
                    <flux:button wire:click="openModal" variant="primary">
                        <flux:icon.plus class="w-5 h-5 mr-2" />
                        Ny forskjell
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Rediger forskjell' : 'Ny forskjell' }}
                </flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ $editingId ? 'Oppdater skatteforskjell' : 'Registrer en ny skatteforskjell' }}
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Regnskapsår *</flux:label>
                    <flux:input wire:model="fiscal_year" type="number" min="2000" max="2100" />
                    @error('fiscal_year')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Type forskjell *</flux:label>
                    <flux:select wire:model="adjustment_type">
                        <option value="permanent">Permanent</option>
                        <option value="temporary_deductible">Midlertidig (fradragsberettiget)</option>
                        <option value="temporary_taxable">Midlertidig (skattbar)</option>
                    </flux:select>
                    @error('adjustment_type')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Kategori *</flux:label>
                    <flux:select wire:model="category">
                        <option value="">Velg kategori</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    @error('category')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse *</flux:label>
                    <flux:input wire:model="description" type="text" placeholder="Beskriv forskjellen" />
                    @error('description')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Konto</flux:label>
                    <flux:select wire:model="account_id">
                        <option value="">Ingen konto</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->account_number }} - {{ $account->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('account_id')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Regnskapsmessig beløp *</flux:label>
                        <flux:input wire:model="accounting_amount" type="number" step="0.01" />
                        @error('accounting_amount')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Skattemessig beløp *</flux:label>
                        <flux:input wire:model="tax_amount" type="number" step="0.01" />
                        @error('tax_amount')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Notater</flux:label>
                    <flux:textarea wire:model="notes" rows="3" placeholder="Interne notater..."></flux:textarea>
                    @error('notes')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
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
