<div>
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('payroll.runs') }}" class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg">
            <flux:icon.arrow-left class="w-5 h-5 text-zinc-600 dark:text-zinc-400" />
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                    {{ $run->period_label }}
                </flux:heading>
                <flux:badge size="lg" color="{{ $run->status_color }}">{{ $run->status_label }}</flux:badge>
            </div>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                Utbetalingsdato: {{ $run->utbetalingsdato->format('d.m.Y') }}
            </flux:text>
        </div>
        <div class="flex gap-2">
            @if($run->status === 'draft')
                <flux:button wire:click="calculate" variant="primary" icon="calculator">
                    Beregn lønn
                </flux:button>
            @elseif($run->status === 'calculated')
                <flux:button wire:click="approve" variant="primary" icon="check">
                    Godkjenn
                </flux:button>
            @elseif($run->status === 'approved')
                <flux:button wire:click="markAsPaid" variant="primary" icon="banknotes">
                    Marker som utbetalt
                </flux:button>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-4 text-center">
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Ansatte</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $run->entries->count() }}</div>
            </div>
        </flux:card>
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-4 text-center">
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Bruttolønn</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($run->total_bruttolonn, 0, ',', ' ') }}</div>
            </div>
        </flux:card>
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-4 text-center">
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Forskuddstrekk</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($run->total_forskuddstrekk, 0, ',', ' ') }}</div>
            </div>
        </flux:card>
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-4 text-center">
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Nettolønn</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($run->total_nettolonn, 0, ',', ' ') }}</div>
            </div>
        </flux:card>
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-4 text-center">
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Arb.giveravgift</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($run->total_arbeidsgiveravgift, 0, ',', ' ') }}</div>
            </div>
        </flux:card>
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-4 text-center">
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">OTP</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($run->total_otp, 0, ',', ' ') }}</div>
            </div>
        </flux:card>
    </div>

    <!-- Entries Table -->
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
            <flux:heading size="lg" class="text-zinc-900 dark:text-white">Lønnslinjer per ansatt</flux:heading>
        </div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Ansatt</flux:table.column>
                <flux:table.column class="text-right">Grunnlønn</flux:table.column>
                <flux:table.column class="text-right">Overtid</flux:table.column>
                <flux:table.column class="text-right">Brutto</flux:table.column>
                <flux:table.column class="text-right">Skatt</flux:table.column>
                <flux:table.column class="text-right">Netto</flux:table.column>
                <flux:table.column class="text-right">AGA</flux:table.column>
                <flux:table.column class="text-right">OTP</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($run->entries as $entry)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar size="sm" name="{{ $entry->user->name }}" />
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $entry->user->name }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="text-right">{{ number_format($entry->grunnlonn, 0, ',', ' ') }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ number_format($entry->overtid_belop, 0, ',', ' ') }}</flux:table.cell>
                        <flux:table.cell class="text-right font-medium">{{ number_format($entry->bruttolonn, 0, ',', ' ') }}</flux:table.cell>
                        <flux:table.cell class="text-right text-red-600 dark:text-red-400">-{{ number_format($entry->forskuddstrekk, 0, ',', ' ') }}</flux:table.cell>
                        <flux:table.cell class="text-right font-medium text-green-600 dark:text-green-400">{{ number_format($entry->nettolonn, 0, ',', ' ') }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ number_format($entry->arbeidsgiveravgift, 0, ',', ' ') }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ number_format($entry->otp_belop, 0, ',', ' ') }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center py-8">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">
                                Ingen lønnslinjer ennå. Klikk "Beregn lønn" for å beregne.
                            </flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <!-- Meta Info -->
    <div class="mt-6 text-sm text-zinc-500 dark:text-zinc-400">
        <div class="flex gap-6">
            @if($run->createdByUser)
                <span>Opprettet av {{ $run->createdByUser->name }} {{ $run->created_at->format('d.m.Y H:i') }}</span>
            @endif
            @if($run->approvedByUser)
                <span>Godkjent av {{ $run->approvedByUser->name }} {{ $run->approved_at->format('d.m.Y H:i') }}</span>
            @endif
            @if($run->paid_at)
                <span>Utbetalt {{ $run->paid_at->format('d.m.Y') }}</span>
            @endif
        </div>
    </div>
</div>
