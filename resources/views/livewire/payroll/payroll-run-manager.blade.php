<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <flux:select wire:model.live="filterYear" class="w-40">
            @foreach($years as $year)
                <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:button wire:click="openCreateModal" icon="plus" variant="primary">
            Ny lonnskjoring
        </flux:button>
    </div>

    <!-- Payroll Runs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($runs as $run)
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                <a href="{{ route('payroll.runs.show', $run) }}" class="block p-6">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg" class="text-zinc-900 dark:text-white">{{ $run->period_label }}</flux:heading>
                        <flux:badge size="sm" color="{{ $run->status_color }}">{{ $run->status_label }}</flux:badge>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Bruttolonn</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($run->total_bruttolonn, 0, ',', ' ') }} kr</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Nettolonn</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($run->total_nettolonn, 0, ',', ' ') }} kr</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Arb.giveravgift</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($run->total_arbeidsgiveravgift, 0, ',', ' ') }} kr</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700 text-xs text-zinc-500">
                        <div class="flex justify-between">
                            <span>Utbetalingsdato</span>
                            <span>{{ $run->utbetalingsdato->format('d.m.Y') }}</span>
                        </div>
                    </div>
                </a>

                @if($run->is_editable)
                    <div class="px-6 pb-4 flex gap-2">
                        @if($run->status === 'draft')
                            <flux:button wire:click="calculateRun({{ $run->id }})" size="sm" variant="ghost" icon="calculator">
                                Beregn
                            </flux:button>
                        @endif
                        <flux:button wire:click="deleteRun({{ $run->id }})" wire:confirm="Er du sikker pa at du vil slette denne lonnskjoringen?" size="sm" variant="ghost" icon="trash" class="text-red-600 dark:text-red-400">
                            Slett
                        </flux:button>
                    </div>
                @endif
            </flux:card>
        @empty
            <div class="col-span-full">
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-12 text-center">
                        <flux:icon.calculator class="w-16 h-16 mx-auto text-zinc-400 dark:text-zinc-600 mb-4" />
                        <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-2">Ingen lonnskjoringer</flux:heading>
                        <flux:text class="text-zinc-500 dark:text-zinc-400 mb-4">
                            Opprett din forste lonnskjoring for a komme i gang.
                        </flux:text>
                        <flux:button wire:click="openCreateModal" icon="plus" variant="primary">
                            Opprett lonnskjoring
                        </flux:button>
                    </div>
                </flux:card>
            </div>
        @endforelse
    </div>

    @if($runs->hasPages())
        <div class="mt-6">
            {{ $runs->links() }}
        </div>
    @endif

    <!-- Create Modal -->
    <flux:modal wire:model="showCreateModal" class="max-w-md">
        <div class="p-6">
            <flux:heading size="lg" class="mb-6">Ny lonnskjoring</flux:heading>

            <form wire:submit="createRun" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Ar</flux:label>
                        <flux:select wire:model="newYear">
                            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                <flux:select.option value="{{ $y }}">{{ $y }}</flux:select.option>
                            @endfor
                        </flux:select>
                        <flux:error name="newYear" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Maned</flux:label>
                        <flux:select wire:model.live="newMonth">
                            @foreach($months as $num => $name)
                                <flux:select.option value="{{ $num }}">{{ $name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="newMonth" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Utbetalingsdato</flux:label>
                    <flux:input type="date" wire:model="newPaymentDate" />
                    <flux:error name="newPaymentDate" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="closeCreateModal" variant="ghost">Avbryt</flux:button>
                    <flux:button type="submit" variant="primary">Opprett</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
