<div>
    <!-- Year Filter -->
    <div class="mb-6">
        <flux:select wire:model.live="selectedYear" class="w-40">
            @for($y = now()->year; $y >= now()->year - 5; $y--)
                <flux:select.option value="{{ $y }}">{{ $y }}</flux:select.option>
            @endfor
        </flux:select>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-4 text-center">
                <div class="text-sm text-zinc-500 mb-1">Feriepengegrunnlag</div>
                <div class="text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($totals['grunnlag'], 0, ',', ' ') }}</div>
            </div>
        </flux:card>
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-4 text-center">
                <div class="text-sm text-zinc-500 mb-1">Opptjent</div>
                <div class="text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($totals['opptjent'], 0, ',', ' ') }}</div>
            </div>
        </flux:card>
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-4 text-center">
                <div class="text-sm text-zinc-500 mb-1">Utbetalt</div>
                <div class="text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($totals['utbetalt'], 0, ',', ' ') }}</div>
            </div>
        </flux:card>
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-4 text-center">
                <div class="text-sm text-zinc-500 mb-1">Gjenstående</div>
                <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ number_format($totals['gjenstaaende'], 0, ',', ' ') }}</div>
            </div>
        </flux:card>
    </div>

    <!-- Balances Table -->
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Ansatt</flux:table.column>
                <flux:table.column class="text-right">Grunnlag</flux:table.column>
                <flux:table.column class="text-right">Opptjent</flux:table.column>
                <flux:table.column class="text-right">Utbetalt</flux:table.column>
                <flux:table.column class="text-right">Gjenstående</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($balances as $balance)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar size="sm" name="{{ $balance->user->name }}" />
                                <span class="font-medium">{{ $balance->user->name }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="text-right">{{ number_format($balance->grunnlag, 0, ',', ' ') }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ number_format($balance->opptjent, 0, ',', ' ') }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ number_format($balance->utbetalt, 0, ',', ' ') }}</flux:table.cell>
                        <flux:table.cell class="text-right font-medium text-green-600 dark:text-green-400">{{ number_format($balance->gjenstaaende, 0, ',', ' ') }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-8">
                            <flux:icon.sun class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-600 mb-3" />
                            <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen feriepengeopptjening for dette året</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <div class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
        <flux:callout icon="information-circle" variant="info">
            Feriepenger opptjent i {{ $selectedYear }} utbetales normalt i juni {{ $selectedYear + 1 }}.
        </flux:callout>
    </div>
</div>
