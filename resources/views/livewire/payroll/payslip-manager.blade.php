<div>
    {{-- Filters and bulk actions --}}
    <div class="mb-6 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
            <flux:select wire:model.live="filterYear" class="w-32">
                <option value="">Alle år</option>
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterUserId" class="w-48">
                <option value="">Alle ansatte</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="flex-1"></div>

        @if($payrollRuns->isNotEmpty())
            <flux:dropdown>
                <flux:button variant="primary" icon="paper-airplane">
                    Send lønnsslipper
                </flux:button>

                <flux:menu>
                    @foreach($payrollRuns as $run)
                        <flux:menu.item wire:click="sendAllForRun({{ $run->id }})" icon="envelope">
                            {{ $run->period_label }}
                            <span class="text-xs text-zinc-500 ml-2">({{ $run->entries->count() }} ansatte)</span>
                        </flux:menu.item>
                    @endforeach
                </flux:menu>
            </flux:dropdown>
        @endif
    </div>

    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Ansatt</flux:table.column>
                <flux:table.column>Periode</flux:table.column>
                <flux:table.column class="text-right">Brutto</flux:table.column>
                <flux:table.column class="text-right">Netto</flux:table.column>
                <flux:table.column>Utbetalt</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($entries as $entry)
                    <flux:table.row wire:key="entry-{{ $entry->id }}">
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
                            @if($entry->payslip_sent_at)
                                <flux:badge color="green" size="sm">
                                    Sendt {{ $entry->payslip_sent_at->format('d.m') }}
                                </flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">
                                    Ikke sendt
                                </flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-1">
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="document-arrow-down"
                                    wire:click="downloadPayslip({{ $entry->id }})"
                                    title="Last ned PDF"
                                />
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="paper-airplane"
                                    wire:click="sendPayslip({{ $entry->id }})"
                                    wire:confirm="Vil du sende lønnsslipp til {{ $entry->user->name }}?"
                                    title="Send på e-post"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8">
                            <flux:icon.document-text class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-600 mb-3" />
                            <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen lønnsslipper ennå</flux:text>
                            <flux:text class="text-sm text-zinc-400 dark:text-zinc-500 mt-1">
                                Lønnsslipper vises etter at lønnskjøringer er utbetalt
                            </flux:text>
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

    {{-- Info box about password protection --}}
    <div class="mt-6">
        <flux:callout icon="lock-closed" color="blue">
            <flux:callout.heading>Passordbeskyttede lønnsslipper</flux:callout.heading>
            <flux:callout.text>
                Alle lønnsslipper sendes som passordbeskyttede PDF-filer. Passordet er de 5 siste sifrene i den ansattes personnummer.
                Ansatte uten registrert personnummer vil ikke kunne motta lønnsslipper på e-post.
            </flux:callout.text>
        </flux:callout>
    </div>
</div>
