<div>
    {{-- Header with filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:select wire:model.live="filterYear" class="w-full sm:w-32">
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="flex gap-2">
            @if(!$hasSchedules)
                <flux:button wire:click="initializeYear" variant="primary">
                    <flux:icon.plus class="w-5 h-5 mr-2" />
                    Initialiser {{ $filterYear }}
                </flux:button>
            @else
                <flux:button wire:click="recalculateAll" variant="outline">
                    <flux:icon.calculator class="w-5 h-5 mr-2" />
                    Beregn alle
                </flux:button>
            @endif
        </div>
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
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total avskrivning</flux:text>
                <flux:heading size="xl" class="mt-2 text-zinc-900 dark:text-white">
                    {{ number_format($totalDepreciation, 0, ',', ' ') }} kr
                </flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Skattemessig avskrivning {{ $filterYear }}
                </flux:text>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total utgaende saldo</flux:text>
                <flux:heading size="xl" class="mt-2 text-zinc-900 dark:text-white">
                    {{ number_format($totalClosingBalance, 0, ',', ' ') }} kr
                </flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Skattemessig verdi ved arsslutt
                </flux:text>
            </div>
        </flux:card>
    </div>

    {{-- Depreciation schedules --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($hasSchedules)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Gruppe
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Sats
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    IB
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    + Tilgang
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    - Avgang
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    = Grunnlag
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Avskrivning
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    UB
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">

                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($schedules as $schedule)
                                <tr wire:key="schedule-{{ $schedule->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-4 py-4">
                                        <div>
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                Gruppe {{ strtoupper($schedule->depreciation_group) }}
                                            </flux:text>
                                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $schedule->group_name }}
                                            </flux:text>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($schedule->depreciation_rate, 0) }}%
                                        </flux:text>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($schedule->opening_balance, 0, ',', ' ') }}
                                        </flux:text>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-green-600 dark:text-green-400">
                                            {{ number_format($schedule->additions, 0, ',', ' ') }}
                                        </flux:text>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-red-600 dark:text-red-400">
                                            {{ number_format($schedule->disposals, 0, ',', ' ') }}
                                        </flux:text>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                            {{ number_format($schedule->basis_for_depreciation, 0, ',', ' ') }}
                                        </flux:text>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium text-orange-600 dark:text-orange-400">
                                            {{ number_format($schedule->depreciation_amount, 0, ',', ' ') }}
                                        </flux:text>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                            {{ number_format($schedule->closing_balance, 0, ',', ' ') }}
                                        </flux:text>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <flux:button wire:click="openModal({{ $schedule->id }})" variant="ghost" size="sm">
                                            <flux:icon.pencil class="w-4 h-4" />
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-100 dark:bg-zinc-800 font-semibold">
                            <tr>
                                <td class="px-4 py-3 text-left" colspan="2">
                                    <flux:text class="font-semibold text-zinc-900 dark:text-white">Sum</flux:text>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                                        {{ number_format($schedules->sum('opening_balance'), 0, ',', ' ') }}
                                    </flux:text>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <flux:text class="text-green-600 dark:text-green-400">
                                        {{ number_format($schedules->sum('additions'), 0, ',', ' ') }}
                                    </flux:text>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <flux:text class="text-red-600 dark:text-red-400">
                                        {{ number_format($schedules->sum('disposals'), 0, ',', ' ') }}
                                    </flux:text>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <flux:text class="font-medium text-zinc-900 dark:text-white">
                                        {{ number_format($schedules->sum('basis_for_depreciation'), 0, ',', ' ') }}
                                    </flux:text>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <flux:text class="font-semibold text-orange-600 dark:text-orange-400">
                                        {{ number_format($totalDepreciation, 0, ',', ' ') }}
                                    </flux:text>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <flux:text class="font-semibold text-zinc-900 dark:text-white">
                                        {{ number_format($totalClosingBalance, 0, ',', ' ') }}
                                    </flux:text>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.chart-bar class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        Ingen saldogrupper for {{ $filterYear }}
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        Initialiser saldogrupper for å starte skattemessig avskrivning
                    </flux:text>
                    <flux:button wire:click="initializeYear" variant="primary">
                        <flux:icon.plus class="w-5 h-5 mr-2" />
                        Initialiser {{ $filterYear }}
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
                    Rediger saldogruppe
                </flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    Gruppe {{ strtoupper($depreciation_group) }}
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Inngaende balanse *</flux:label>
                    <flux:input wire:model="opening_balance" type="number" step="0.01" />
                    <flux:description>Skattemessig verdi ved arets start</flux:description>
                    @error('opening_balance')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Tilgang *</flux:label>
                    <flux:input wire:model="additions" type="number" step="0.01" />
                    <flux:description>Nye anskaffelser i aret</flux:description>
                    @error('additions')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Avgang *</flux:label>
                    <flux:input wire:model="disposals" type="number" step="0.01" />
                    <flux:description>Salg eller utrangering i aret</flux:description>
                    @error('disposals')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:callout variant="info">
                    <flux:text class="text-sm">
                        Avskrivning og utgående balanse beregnes automatisk basert på saldogruppens sats.
                    </flux:text>
                </flux:callout>

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
                    Oppdater
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
