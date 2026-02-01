<div>
    {{-- Header with filters --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <flux:select wire:model.live="filterStatus" class="w-40">
                <option value="submitted">Venter pa godkjenning</option>
                <option value="">Alle statuser</option>
                <option value="approved">Godkjent</option>
                <option value="rejected">Avvist</option>
            </flux:select>

            <flux:select wire:model.live="filterUser" class="w-48">
                <option value="">Alle ansatte</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </flux:select>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <flux:callout variant="success" class="mb-6">
            {{ session('success') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout variant="danger" class="mb-6">
            {{ session('error') }}
        </flux:callout>
    @endif

    {{-- Timesheets list --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($timesheets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Ansatt
                                </th>
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
                                        <div class="flex items-center gap-3">
                                            <flux:avatar size="sm" :initials="substr($timesheet->user->name, 0, 2)" />
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                {{ $timesheet->user->name }}
                                            </flux:text>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text class="font-medium">
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
                                            @if($timesheet->status === 'submitted')
                                                <flux:button wire:click="approve({{ $timesheet->id }})" variant="primary" size="sm">
                                                    <flux:icon.check class="w-4 h-4" />
                                                </flux:button>
                                                <flux:button wire:click="openRejectModal({{ $timesheet->id }})" variant="danger" size="sm">
                                                    <flux:icon.x-mark class="w-4 h-4" />
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
                    <flux:icon.check-badge class="w-12 h-12 mx-auto text-zinc-300 dark:text-zinc-600 mb-3" />
                    <flux:heading size="lg" class="text-zinc-700 dark:text-zinc-300 mb-2">
                        Ingen timesedler a vise
                    </flux:heading>
                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                        @if($filterStatus === 'submitted')
                            Det er ingen timesedler som venter pa godkjenning.
                        @else
                            Ingen timesedler matcher filteret ditt.
                        @endif
                    </flux:text>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Detail modal --}}
    <flux:modal wire:model.self="showDetailModal" class="max-w-3xl">
        @if($selectedTimesheet)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $selectedTimesheet->week_label }}</flux:heading>
                    <flux:text class="text-sm text-zinc-500">
                        {{ $selectedTimesheet->user->name }} - {{ $selectedTimesheet->date_range_label }}
                    </flux:text>
                </div>

                <flux:separator />

                {{-- Status and total --}}
                <div class="flex items-center justify-between bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <flux:text>Status:</flux:text>
                        <flux:badge :color="$selectedTimesheet->status_color">
                            {{ $selectedTimesheet->status_label }}
                        </flux:badge>
                    </div>
                    <div class="text-right">
                        <flux:text class="text-sm text-zinc-500">Totalt timer</flux:text>
                        <flux:text class="font-bold text-2xl">{{ number_format($selectedTimesheet->total_hours, 1) }}</flux:text>
                    </div>
                </div>

                {{-- Entries breakdown --}}
                @if($selectedTimesheet->entries->count() > 0)
                    <div>
                        <flux:heading size="sm" class="mb-3">Timeforinger</flux:heading>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                        <th class="pb-2 text-left font-medium text-zinc-500">Prosjekt/Arbeidsordre</th>
                                        <th class="pb-2 text-center font-medium text-zinc-500">Dato</th>
                                        <th class="pb-2 text-right font-medium text-zinc-500">Timer</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @foreach($selectedTimesheet->entries->sortBy('entry_date') as $entry)
                                        <tr>
                                            <td class="py-2">{{ $entry->target_label }}</td>
                                            <td class="py-2 text-center">{{ $entry->entry_date->format('d.m') }} ({{ $entry->day_of_week }})</td>
                                            <td class="py-2 text-right font-medium">{{ number_format($entry->hours, 1) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Notes --}}
                @if($selectedTimesheet->notes)
                    <div>
                        <flux:heading size="sm" class="mb-2">Kommentar fra ansatt</flux:heading>
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $selectedTimesheet->notes }}</flux:text>
                        </div>
                    </div>
                @endif

                {{-- Approval info --}}
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

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="closeDetailModal" variant="ghost">Lukk</flux:button>
                    @if($selectedTimesheet->status === 'submitted')
                        <flux:button wire:click="openRejectModal({{ $selectedTimesheet->id }})" variant="danger">
                            <flux:icon.x-mark class="w-4 h-4 mr-2" />
                            Avvis
                        </flux:button>
                        <flux:button wire:click="approve({{ $selectedTimesheet->id }})" variant="primary">
                            <flux:icon.check class="w-4 h-4 mr-2" />
                            Godkjenn
                        </flux:button>
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Reject modal --}}
    <flux:modal wire:model.self="showRejectModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Avvis timeseddel</flux:heading>
            </div>

            <flux:separator />

            <div class="space-y-4">
                @if($selectedTimesheet)
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        Du er i ferd med a avvise timeseddelen for {{ $selectedTimesheet->user->name }}
                        ({{ $selectedTimesheet->week_label }}).
                    </flux:text>
                @endif

                <flux:field>
                    <flux:label>Grunn for avvisning</flux:label>
                    <flux:textarea
                        wire:model="rejectionReason"
                        placeholder="Forklar hvorfor timeseddelen avvises..."
                        rows="4"
                    />
                    @error('rejectionReason')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <flux:button wire:click="closeRejectModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="reject" variant="danger">
                    <flux:icon.x-mark class="w-4 h-4 mr-2" />
                    Avvis timeseddel
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
