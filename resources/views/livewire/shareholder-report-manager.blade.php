<div>
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="lg">Aksjonaerregisteroppgaven (RF-1086)</flux:heading>
            <flux:text class="text-zinc-600">Arlig rapport til Skatteetaten - frist 31. januar</flux:text>
        </div>
        <flux:button wire:click="openCreateModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Opprett rapport
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

    {{-- Reports list --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($reports->count() > 0)
                <div class="space-y-4">
                    @foreach($reports as $report)
                        <div wire:key="report-{{ $report->id }}" class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.document-text class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <flux:heading size="sm" class="text-zinc-900 dark:text-white">
                                        Aksjonaeroppgave {{ $report->year }}
                                    </flux:heading>
                                    <div class="flex items-center gap-3 mt-1">
                                        <flux:text class="text-sm text-zinc-500">
                                            {{ $report->getReportPeriod() }}
                                        </flux:text>
                                        <flux:text class="text-sm text-zinc-500">
                                            {{ $report->number_of_shareholders }} aksjonaerer
                                        </flux:text>
                                        <flux:text class="text-sm text-zinc-500">
                                            {{ $report->getFormattedShareCapital() }}
                                        </flux:text>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                {{-- Deadline indicator --}}
                                @if(!$report->isSubmitted())
                                    @php $daysUntil = $report->getDaysUntilDeadline(); @endphp
                                    @if($daysUntil < 0)
                                        <flux:badge variant="danger">Forfalt</flux:badge>
                                    @elseif($daysUntil <= 7)
                                        <flux:badge variant="warning">{{ $daysUntil }} dager</flux:badge>
                                    @elseif($daysUntil <= 30)
                                        <flux:badge variant="info">{{ $daysUntil }} dager</flux:badge>
                                    @endif
                                @endif

                                <flux:badge variant="{{ $report->getStatusBadgeColor() }}">
                                    {{ $report->getStatusLabel() }}
                                </flux:badge>

                                <div class="flex items-center gap-1">
                                    <flux:button wire:click="viewReport({{ $report->id }})" variant="ghost" size="sm" title="Vis detaljer">
                                        <flux:icon.eye class="w-4 h-4" />
                                    </flux:button>

                                    @if($report->canBeEdited())
                                        <flux:button wire:click="regenerateSnapshot({{ $report->id }})" variant="ghost" size="sm" title="Regenerer snapshot">
                                            <flux:icon.arrow-path class="w-4 h-4" />
                                        </flux:button>
                                    @endif

                                    @if($report->isDraft())
                                        <flux:button wire:click="markAsReady({{ $report->id }})" variant="ghost" size="sm" title="Merk som klar">
                                            <flux:icon.check class="w-4 h-4 text-green-600" />
                                        </flux:button>
                                    @endif

                                    @if($report->isReady())
                                        <flux:button wire:click="submitToAltinn({{ $report->id }})" variant="ghost" size="sm" title="Send til Altinn">
                                            <flux:icon.paper-airplane class="w-4 h-4 text-blue-600" />
                                        </flux:button>
                                    @endif

                                    @if(!$report->isSubmitted())
                                        <flux:button wire:click="delete({{ $report->id }})" wire:confirm="Er du sikker?" variant="ghost" size="sm" class="text-red-600">
                                            <flux:icon.trash class="w-4 h-4" />
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.document-text class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="mb-2">Ingen rapporter</flux:heading>
                    <flux:text class="text-zinc-600 mb-6">Opprett din forste aksjonaeroppgave</flux:text>
                    <flux:button wire:click="openCreateModal" variant="primary">
                        <flux:icon.plus class="w-5 h-5 mr-2" />
                        Opprett rapport
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Create Modal --}}
    <flux:modal wire:model="showCreateModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Opprett aksjonaeroppgave</flux:heading>
                <flux:text class="mt-1 text-zinc-600">Velg regnskapsar for rapporten</flux:text>
            </div>

            <flux:separator />

            <flux:field>
                <flux:label>Regnskapsar *</flux:label>
                <flux:select wire:model="createYear">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </flux:select>
                <flux:description>Rapport for aksjonaeropplysninger per 31.12.{{ $createYear }}</flux:description>
                @error('createYear') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeCreateModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="createReport" variant="primary">Opprett</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Detail Modal --}}
    <flux:modal wire:model="showDetailModal" class="max-w-4xl">
        @if($viewingReport)
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">Aksjonaeroppgave {{ $viewingReport->year }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-600">{{ $viewingReport->getReportPeriod() }}</flux:text>
                    </div>
                    <flux:badge variant="{{ $viewingReport->getStatusBadgeColor() }}" size="lg">
                        {{ $viewingReport->getStatusLabel() }}
                    </flux:badge>
                </div>

                <flux:separator />

                {{-- Summary --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:text class="text-sm text-zinc-500">Aksjekapital</flux:text>
                        <flux:heading size="lg">{{ $viewingReport->getFormattedShareCapital() }}</flux:heading>
                    </div>
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:text class="text-sm text-zinc-500">Antall aksjer</flux:text>
                        <flux:heading size="lg">{{ number_format($viewingReport->total_shares, 0, ',', ' ') }}</flux:heading>
                    </div>
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:text class="text-sm text-zinc-500">Antall aksjonaerer</flux:text>
                        <flux:heading size="lg">{{ $viewingReport->number_of_shareholders }}</flux:heading>
                    </div>
                </div>

                {{-- Shareholders from snapshot --}}
                @if($viewingReport->snapshot_data && isset($viewingReport->snapshot_data['shareholders']))
                    <div>
                        <flux:heading size="sm" class="mb-3">Aksjonaerer ved arsslutt</flux:heading>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 uppercase">Aksjonaer</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 uppercase">Type</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 uppercase">Land</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 uppercase">Aksjer</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($viewingReport->snapshot_data['shareholders'] as $sh)
                                        <tr>
                                            <td class="px-4 py-2 font-medium">{{ $sh['name'] }}</td>
                                            <td class="px-4 py-2">{{ $sh['type'] === 'person' ? 'Person' : 'Selskap' }}</td>
                                            <td class="px-4 py-2">{{ $sh['country_code'] }}</td>
                                            <td class="px-4 py-2 text-right">
                                                {{ number_format(collect($sh['holdings'])->sum('number_of_shares'), 0, ',', ' ') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Transaction summary --}}
                @if($viewingReport->changes_during_year && isset($viewingReport->changes_during_year['statistics']))
                    <div>
                        <flux:heading size="sm" class="mb-3">Endringer i lopet av aret</flux:heading>
                        <div class="grid grid-cols-4 gap-4">
                            @php $stats = $viewingReport->changes_during_year['statistics']; @endphp
                            <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg text-center">
                                <flux:text class="text-2xl font-bold">{{ $stats['total_transactions'] }}</flux:text>
                                <flux:text class="text-sm text-zinc-500">Transaksjoner</flux:text>
                            </div>
                            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg text-center">
                                <flux:text class="text-2xl font-bold text-green-600">{{ $stats['issues'] }}</flux:text>
                                <flux:text class="text-sm text-zinc-500">Emisjoner</flux:text>
                            </div>
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center">
                                <flux:text class="text-2xl font-bold text-blue-600">{{ $stats['transfers'] }}</flux:text>
                                <flux:text class="text-sm text-zinc-500">Overdragelser</flux:text>
                            </div>
                            <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg text-center">
                                <flux:text class="text-2xl font-bold text-orange-600">{{ $stats['redemptions'] }}</flux:text>
                                <flux:text class="text-sm text-zinc-500">Innlosninger</flux:text>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Dividend summary --}}
                @if($viewingReport->dividend_summary && $viewingReport->dividend_summary['dividend_count'] > 0)
                    <div>
                        <flux:heading size="sm" class="mb-3">Utbytter</flux:heading>
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <flux:text class="text-sm text-zinc-500">Antall utbytter</flux:text>
                                    <flux:text class="text-lg font-medium">{{ $viewingReport->dividend_summary['dividend_count'] }}</flux:text>
                                </div>
                                <div class="text-right">
                                    <flux:text class="text-sm text-zinc-500">Totalt utbetalt</flux:text>
                                    <flux:text class="text-lg font-medium text-emerald-600">
                                        {{ number_format($viewingReport->dividend_summary['total_dividends'], 2, ',', ' ') }} NOK
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <flux:separator />

                <div class="flex justify-between">
                    <div class="text-sm text-zinc-500">
                        Opprettet av {{ $viewingReport->creator->name }} - {{ $viewingReport->created_at->format('d.m.Y H:i') }}
                    </div>
                    <flux:button wire:click="closeDetailModal" variant="ghost">Lukk</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
