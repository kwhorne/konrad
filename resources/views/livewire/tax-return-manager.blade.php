<div>
    {{-- Header with actions --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                Oversikt over skattemeldinger og beregninger
            </flux:text>
        </div>

        <flux:button wire:click="openCreateModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Ny skattemelding
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
    @if(session('info'))
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <flux:text class="text-blue-800 dark:text-blue-200">{{ session('info') }}</flux:text>
        </div>
    @endif

    {{-- Tax returns list --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($taxReturns->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    År
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Regnskapsmessig resultat
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Skattepliktig inntekt
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Betalbar skatt
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
                            @foreach($taxReturns as $return)
                                <tr wire:key="return-{{ $return->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                {{ $return->fiscal_year }}
                                            </flux:text>
                                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $return->period_start->format('d.m.Y') }} - {{ $return->period_end->format('d.m.Y') }}
                                            </flux:text>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($return->accounting_profit, 0, ',', ' ') }} kr
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                            {{ number_format($return->taxable_income, 0, ',', ' ') }} kr
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium {{ $return->tax_payable > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                            {{ number_format($return->tax_payable, 0, ',', ' ') }} kr
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:badge variant="{{ $return->getStatusBadgeColor() }}">
                                            {{ $return->getStatusLabel() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button wire:click="viewReturn({{ $return->id }})" variant="ghost" size="sm">
                                                <flux:icon.eye class="w-4 h-4" />
                                            </flux:button>
                                            @if($return->canBeEdited())
                                                <flux:button wire:click="calculateTax({{ $return->id }})" variant="ghost" size="sm" title="Beregn skatt">
                                                    <flux:icon.calculator class="w-4 h-4" />
                                                </flux:button>
                                            @endif
                                            <flux:button wire:click="delete({{ $return->id }})" wire:confirm="Er du sikker på at du vil slette denne skattemeldingen?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                                                <flux:icon.trash class="w-4 h-4" />
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.document-text class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        Ingen skattemeldinger ennå
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        Opprett din første skattemelding for å komme i gang
                    </flux:text>
                    <flux:button wire:click="openCreateModal" variant="primary">
                        <flux:icon.plus class="w-5 h-5 mr-2" />
                        Ny skattemelding
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Create Modal --}}
    <flux:modal wire:model="showCreateModal" variant="flyout" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    Ny skattemelding
                </flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    Opprett skattemelding for et regnskapsår
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Regnskapsår *</flux:label>
                    <flux:select wire:model="createYear">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </flux:select>
                    @error('createYear')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:callout variant="info">
                    <flux:text class="text-sm">
                        Skattemeldingen vil automatisk hente regnskapsmessig resultat og initialisere saldogrupper for avskrivning.
                    </flux:text>
                </flux:callout>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeCreateModal" variant="ghost">
                    Avbryt
                </flux:button>
                <flux:button wire:click="createReturn" variant="primary">
                    Opprett
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Detail Modal --}}
    <flux:modal wire:model="showDetailModal" variant="flyout" class="w-full max-w-2xl">
        @if($viewingReturn)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        Skattemelding {{ $viewingReturn->fiscal_year }}
                    </flux:heading>
                    <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                        {{ $viewingReturn->period_start->format('d.m.Y') }} - {{ $viewingReturn->period_end->format('d.m.Y') }}
                    </flux:text>
                </div>

                <flux:separator />

                {{-- Status --}}
                <div class="flex items-center justify-between">
                    <flux:text class="font-medium">Status</flux:text>
                    <flux:badge variant="{{ $viewingReturn->getStatusBadgeColor() }}">
                        {{ $viewingReturn->getStatusLabel() }}
                    </flux:badge>
                </div>

                {{-- Inntektsberegning --}}
                <div class="space-y-3">
                    <flux:heading size="sm">Inntektsberegning</flux:heading>
                    <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 space-y-2">
                        <div class="flex justify-between">
                            <flux:text>Regnskapsmessig resultat</flux:text>
                            <flux:text class="font-medium">{{ number_format($viewingReturn->accounting_profit, 0, ',', ' ') }} kr</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text>+ Permanente forskjeller</flux:text>
                            <flux:text class="font-medium">{{ number_format($viewingReturn->permanent_differences, 0, ',', ' ') }} kr</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text>+ Midlertidige forskjeller (endring)</flux:text>
                            <flux:text class="font-medium">{{ number_format($viewingReturn->temporary_differences_change, 0, ',', ' ') }} kr</flux:text>
                        </div>
                        <flux:separator />
                        <div class="flex justify-between">
                            <flux:text>- Fremførbart underskudd benyttet</flux:text>
                            <flux:text class="font-medium">{{ number_format($viewingReturn->losses_used, 0, ',', ' ') }} kr</flux:text>
                        </div>
                        <flux:separator />
                        <div class="flex justify-between font-semibold">
                            <flux:text>= Skattepliktig inntekt</flux:text>
                            <flux:text>{{ number_format($viewingReturn->taxable_income, 0, ',', ' ') }} kr</flux:text>
                        </div>
                    </div>
                </div>

                {{-- Skatteberegning --}}
                <div class="space-y-3">
                    <flux:heading size="sm">Skatteberegning</flux:heading>
                    <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 space-y-2">
                        <div class="flex justify-between">
                            <flux:text>Skattepliktig inntekt x {{ $viewingReturn->tax_rate }}%</flux:text>
                            <flux:text class="font-medium">{{ number_format($viewingReturn->tax_payable, 0, ',', ' ') }} kr</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text>+ Endring utsatt skatt</flux:text>
                            <flux:text class="font-medium">{{ number_format($viewingReturn->deferred_tax_change, 0, ',', ' ') }} kr</flux:text>
                        </div>
                        <flux:separator />
                        <div class="flex justify-between font-semibold">
                            <flux:text>= Total skattekostnad</flux:text>
                            <flux:text>{{ number_format($viewingReturn->total_tax_expense, 0, ',', ' ') }} kr</flux:text>
                        </div>
                    </div>
                </div>

                {{-- Underskudd --}}
                @if($viewingReturn->losses_brought_forward > 0 || $viewingReturn->losses_carried_forward > 0)
                    <div class="space-y-3">
                        <flux:heading size="sm">Fremførbart underskudd</flux:heading>
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 space-y-2">
                            <div class="flex justify-between">
                                <flux:text>IB underskudd</flux:text>
                                <flux:text class="font-medium">{{ number_format($viewingReturn->losses_brought_forward, 0, ',', ' ') }} kr</flux:text>
                            </div>
                            <div class="flex justify-between">
                                <flux:text>- Benyttet</flux:text>
                                <flux:text class="font-medium">{{ number_format($viewingReturn->losses_used, 0, ',', ' ') }} kr</flux:text>
                            </div>
                            <flux:separator />
                            <div class="flex justify-between font-semibold">
                                <flux:text>= UB underskudd</flux:text>
                                <flux:text>{{ number_format($viewingReturn->losses_carried_forward, 0, ',', ' ') }} kr</flux:text>
                            </div>
                        </div>
                    </div>
                @endif

                <flux:separator />

                {{-- Actions --}}
                <div class="flex flex-wrap gap-2">
                    @if($viewingReturn->canBeEdited())
                        <flux:button wire:click="calculateTax({{ $viewingReturn->id }})" variant="outline">
                            <flux:icon.calculator class="w-4 h-4 mr-2" />
                            Beregn på nytt
                        </flux:button>
                        <flux:button wire:click="validateReturn({{ $viewingReturn->id }})" variant="outline">
                            <flux:icon.check-circle class="w-4 h-4 mr-2" />
                            Valider
                        </flux:button>
                    @endif

                    @if($viewingReturn->isDraft())
                        <flux:button wire:click="markAsReady({{ $viewingReturn->id }})" variant="primary">
                            <flux:icon.arrow-right class="w-4 h-4 mr-2" />
                            Marker som klar
                        </flux:button>
                    @endif

                    @if($viewingReturn->isReady())
                        <flux:button wire:click="markAsDraft({{ $viewingReturn->id }})" variant="outline">
                            <flux:icon.arrow-left class="w-4 h-4 mr-2" />
                            Tilbake til utkast
                        </flux:button>
                        <flux:button wire:click="submitToAltinn({{ $viewingReturn->id }})" variant="primary">
                            <flux:icon.paper-airplane class="w-4 h-4 mr-2" />
                            Send til Altinn
                        </flux:button>
                    @endif
                </div>

                <div class="flex justify-end">
                    <flux:button wire:click="closeDetailModal" variant="ghost">
                        Lukk
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
