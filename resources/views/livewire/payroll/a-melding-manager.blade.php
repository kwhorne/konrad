<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <flux:select wire:model.live="filterYear" class="w-40">
            @for($y = now()->year; $y >= now()->year - 3; $y--)
                <flux:select.option value="{{ $y }}">{{ $y }}</flux:select.option>
            @endfor
        </flux:select>
        <flux:button wire:click="openGenerateModal" icon="plus" variant="primary">
            Generer A-melding
        </flux:button>
    </div>

    <!-- Info Callout -->
    <flux:callout icon="information-circle" variant="info" class="mb-6">
        A-meldingen skal sendes til Skatteetaten innen den 5. i maneden etter lonnsperioden.
    </flux:callout>

    <!-- Reports Table -->
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Periode</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Frist</flux:table.column>
                <flux:table.column>Referanse</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($reports as $report)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">{{ $report->period_label }}</flux:table.cell>
                        <flux:table.cell>{{ $report->melding_type_label }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="{{ $report->status_color }}">{{ $report->status_label }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @php
                                $deadline = $aMeldingService->getDeadline($report->year, $report->month);
                                $isPast = $deadline->isPast();
                            @endphp
                            <span class="{{ $isPast && !in_array($report->status, ['submitted', 'confirmed']) ? 'text-red-600 dark:text-red-400' : '' }}">
                                {{ $deadline->format('d.m.Y') }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>{{ $report->altinn_reference ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex gap-2">
                                @if($report->xml_content)
                                    <flux:button wire:click="downloadXml({{ $report->id }})" variant="ghost" size="sm" icon="document-arrow-down">
                                        XML
                                    </flux:button>
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8">
                            <flux:icon.cloud-arrow-up class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-600 mb-3" />
                            <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen A-meldinger enna</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($reports->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                {{ $reports->links() }}
            </div>
        @endif
    </flux:card>

    <!-- Generate Modal -->
    <flux:modal wire:model="showGenerateModal" class="max-w-md">
        <div class="p-6">
            <flux:heading size="lg" class="mb-6">Generer A-melding</flux:heading>

            <form wire:submit="generateReport" class="space-y-4">
                <flux:field>
                    <flux:label>Velg lonnskjoring</flux:label>
                    <flux:select wire:model="selectedRunId">
                        <flux:select.option value="">Velg...</flux:select.option>
                        @foreach($availableRuns as $run)
                            <flux:select.option value="{{ $run->id }}">{{ $run->period_label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                @if($availableRuns->isEmpty())
                    <flux:callout icon="exclamation-triangle" variant="warning">
                        Ingen utbetalte lonnskjoringer uten A-melding funnet.
                    </flux:callout>
                @endif

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="closeGenerateModal" variant="ghost">Avbryt</flux:button>
                    <flux:button type="submit" variant="primary" :disabled="$availableRuns->isEmpty()">Generer</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
