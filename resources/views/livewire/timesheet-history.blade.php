<div>
    {{-- Header with filters --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <flux:select wire:model.live="filterStatus" class="w-40">
                <option value="">Alle statuser</option>
                <option value="draft">Utkast</option>
                <option value="submitted">Innsendt</option>
                <option value="approved">Godkjent</option>
                <option value="rejected">Avvist</option>
            </flux:select>

            <flux:select wire:model.live="filterYear" class="w-32">
                <option value="">Alle ar</option>
                @foreach($this->availableYears as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </flux:select>
        </div>

        <flux:button href="{{ route('timesheets.index') }}" variant="primary">
            <flux:icon.plus class="w-4 h-4 mr-2" />
            Ny timeregistrering
        </flux:button>
    </div>

    {{-- Timesheets list --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($timesheets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Uke
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Periode
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Timer
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Handlinger
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($timesheets as $timesheet)
                                <tr wire:key="timesheet-{{ $timesheet->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                            {{ $timesheet->week_label }}
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ $timesheet->date_range_label }}
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <flux:text class="font-semibold">
                                            {{ number_format($timesheet->total_hours, 1) }}
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <flux:badge :color="$timesheet->status_color">
                                            {{ $timesheet->status_label }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button wire:click="viewDetails({{ $timesheet->id }})" variant="ghost" size="sm">
                                                <flux:icon.eye class="w-4 h-4" />
                                            </flux:button>
                                            @if($timesheet->is_editable)
                                                <flux:button href="{{ route('timesheets.index') }}?week={{ $timesheet->week_start->format('Y-m-d') }}" variant="ghost" size="sm">
                                                    <flux:icon.pencil class="w-4 h-4" />
                                                </flux:button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $timesheets->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.clock class="w-12 h-12 mx-auto text-zinc-300 dark:text-zinc-600 mb-3" />
                    <flux:heading size="lg" class="text-zinc-700 dark:text-zinc-300 mb-2">
                        Ingen timesedler funnet
                    </flux:heading>
                    <flux:text class="text-zinc-500 dark:text-zinc-400 mb-4">
                        @if($filterStatus || $filterYear)
                            Ingen timesedler matcher filteret ditt.
                        @else
                            Du har ikke registrert noen timer enna.
                        @endif
                    </flux:text>
                    <flux:button href="{{ route('timesheets.index') }}" variant="primary">
                        Registrer timer
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Detail modal --}}
    <flux:modal wire:model.self="showDetailModal" class="max-w-2xl">
        @if($selectedTimesheet)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $selectedTimesheet->week_label }}</flux:heading>
                    <flux:text class="text-sm text-zinc-500">{{ $selectedTimesheet->date_range_label }}</flux:text>
                </div>

                <flux:separator />

                {{-- Status --}}
                <div class="flex items-center justify-between">
                    <flux:text>Status:</flux:text>
                    <flux:badge :color="$selectedTimesheet->status_color">
                        {{ $selectedTimesheet->status_label }}
                    </flux:badge>
                </div>

                {{-- Approval/Rejection info --}}
                @if($selectedTimesheet->status === 'approved')
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <flux:text class="text-green-800 dark:text-green-200">
                            Godkjent av {{ $selectedTimesheet->approvedByUser?->name ?? 'Ukjent' }}
                            {{ $selectedTimesheet->approved_at?->format('d.m.Y H:i') }}
                        </flux:text>
                    </div>
                @elseif($selectedTimesheet->status === 'rejected')
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                        <flux:text class="font-medium text-red-800 dark:text-red-200 mb-1">
                            Avvist av {{ $selectedTimesheet->rejectedByUser?->name ?? 'Ukjent' }}
                            {{ $selectedTimesheet->rejected_at?->format('d.m.Y H:i') }}
                        </flux:text>
                        @if($selectedTimesheet->rejection_reason)
                            <flux:text class="text-red-700 dark:text-red-300">
                                Grunn: {{ $selectedTimesheet->rejection_reason }}
                            </flux:text>
                        @endif
                    </div>
                @endif

                {{-- Hours summary --}}
                <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <flux:text class="font-medium">Totalt timer:</flux:text>
                        <flux:text class="font-bold text-xl">{{ number_format($selectedTimesheet->total_hours, 1) }}</flux:text>
                    </div>
                </div>

                {{-- Entries --}}
                @if($selectedTimesheet->entries->count() > 0)
                    <div>
                        <flux:heading size="sm" class="mb-3">Timeforinger</flux:heading>
                        <div class="space-y-2">
                            @foreach($selectedTimesheet->entries->groupBy(fn($e) => $e->target_label) as $label => $entries)
                                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-3">
                                    <div class="flex items-center justify-between">
                                        <flux:text class="font-medium truncate max-w-[300px]">{{ $label }}</flux:text>
                                        <flux:text class="font-semibold">{{ number_format($entries->sum('hours'), 1) }} t</flux:text>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Notes --}}
                @if($selectedTimesheet->notes)
                    <div>
                        <flux:heading size="sm" class="mb-2">Kommentar</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $selectedTimesheet->notes }}</flux:text>
                    </div>
                @endif

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="closeDetailModal" variant="ghost">Lukk</flux:button>
                    @if($selectedTimesheet->is_editable)
                        <flux:button href="{{ route('timesheets.index') }}?week={{ $selectedTimesheet->week_start->format('Y-m-d') }}" variant="primary">
                            Rediger
                        </flux:button>
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>
</div>
