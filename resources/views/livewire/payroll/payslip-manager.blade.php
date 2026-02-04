<div>
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Ansatt</flux:table.column>
                <flux:table.column>Periode</flux:table.column>
                <flux:table.column class="text-right">Brutto</flux:table.column>
                <flux:table.column class="text-right">Netto</flux:table.column>
                <flux:table.column>Utbetalt</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($entries as $entry)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar size="sm" name="{{ $entry->user->name }}" />
                                <span class="font-medium">{{ $entry->user->name }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $entry->payrollRun->period_label }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ number_format($entry->bruttolonn, 0, ',', ' ') }} kr</flux:table.cell>
                        <flux:table.cell class="text-right font-medium text-green-600 dark:text-green-400">{{ number_format($entry->nettolonn, 0, ',', ' ') }} kr</flux:table.cell>
                        <flux:table.cell>{{ $entry->payrollRun->utbetalingsdato->format('d.m.Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button variant="ghost" size="sm" icon="document-arrow-down">
                                Last ned
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8">
                            <flux:icon.document-text class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-600 mb-3" />
                            <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen lonnsslipper enna</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($entries->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                {{ $entries->links() }}
            </div>
        @endif
    </flux:card>
</div>
