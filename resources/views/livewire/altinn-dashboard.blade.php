<div>
    {{-- Year selector --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <flux:select wire:model.live="selectedYear" class="w-32">
                @foreach ($years as $year)
                    <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                Viser frister og innsendinger for regnskapsåret {{ $selectedYear - 1 }}
            </flux:text>
        </div>
    </div>

    {{-- Statistics cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <flux:card class="!p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.document-arrow-up class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Totalt</flux:text>
                    <flux:heading size="lg">{{ $statistics['total'] }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card class="!p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.check-circle class="w-5 h-5 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Akseptert</flux:text>
                    <flux:heading size="lg">{{ $statistics['accepted'] }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card class="!p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.clock class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Under behandling</flux:text>
                    <flux:heading size="lg">{{ $statistics['pending'] }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card class="!p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.x-circle class="w-5 h-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Avvist</flux:text>
                    <flux:heading size="lg">{{ $statistics['rejected'] }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Overdue deadlines --}}
        @if (count($overdueDeadlines) > 0)
            <flux:card class="lg:col-span-3 border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20">
                <div class="flex items-center gap-2 mb-4">
                    <flux:icon.exclamation-triangle class="w-5 h-5 text-red-600 dark:text-red-400" />
                    <flux:heading size="lg" class="text-red-700 dark:text-red-400">Forfalte frister</flux:heading>
                </div>
                <div class="space-y-3">
                    @foreach ($overdueDeadlines as $deadline)
                        <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded-lg border border-red-200 dark:border-red-700">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/50 rounded-lg flex items-center justify-center">
                                    <flux:icon.document-text class="w-5 h-5 text-red-600 dark:text-red-400" />
                                </div>
                                <div>
                                    <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $deadline['name'] }}</flux:text>
                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $deadline['code'] }} &middot; Frist: {{ $deadline['deadline']->format('d.m.Y') }}
                                    </flux:text>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <flux:badge color="danger">{{ $deadline['days_past'] }} dager forfalt</flux:badge>
                                @if ($deadline['route'])
                                    <flux:button href="{{ route($deadline['route']) }}" size="sm" variant="filled">
                                        Gå til
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @endif

        {{-- Upcoming deadlines --}}
        <flux:card class="lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Kommende frister</flux:heading>
            </div>

            @if (count($upcomingDeadlines) > 0)
                <div class="space-y-3">
                    @foreach ($upcomingDeadlines as $deadline)
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700/50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                    @if ($deadline['urgency'] === 'critical') bg-red-100 dark:bg-red-900/50
                                    @elseif ($deadline['urgency'] === 'high') bg-amber-100 dark:bg-amber-900/50
                                    @elseif ($deadline['urgency'] === 'medium') bg-yellow-100 dark:bg-yellow-900/50
                                    @else bg-blue-100 dark:bg-blue-900/50 @endif">
                                    <flux:icon.calendar class="w-5 h-5
                                        @if ($deadline['urgency'] === 'critical') text-red-600 dark:text-red-400
                                        @elseif ($deadline['urgency'] === 'high') text-amber-600 dark:text-amber-400
                                        @elseif ($deadline['urgency'] === 'medium') text-yellow-600 dark:text-yellow-400
                                        @else text-blue-600 dark:text-blue-400 @endif" />
                                </div>
                                <div>
                                    <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $deadline['name'] }}</flux:text>
                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $deadline['code'] }} &middot; {{ $deadline['recipient'] }}
                                    </flux:text>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $deadline['deadline']->format('d.m.Y') }}</flux:text>
                                    <flux:text class="text-sm
                                        @if ($deadline['urgency'] === 'critical') text-red-600 dark:text-red-400
                                        @elseif ($deadline['urgency'] === 'high') text-amber-600 dark:text-amber-400
                                        @else text-zinc-500 dark:text-zinc-400 @endif">
                                        {{ $deadline['days_until'] }} dager igjen
                                    </flux:text>
                                </div>
                                <flux:badge :color="$deadline['status']['color']">{{ $deadline['status']['label'] }}</flux:badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <flux:text class="text-zinc-500 dark:text-zinc-400 py-4 text-center">
                    Ingen kommende frister for dette året
                </flux:text>
            @endif
        </flux:card>

        {{-- Quick actions --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">Hurtiglenker</flux:heading>
            <div class="space-y-2">
                <flux:button href="{{ route('shareholders.reports') }}" variant="ghost" class="w-full justify-start">
                    <flux:icon.users class="w-4 h-4 mr-2" />
                    Aksjonærregisteroppgaven
                </flux:button>
                <flux:button href="{{ route('tax.returns') }}" variant="ghost" class="w-full justify-start">
                    <flux:icon.document-text class="w-4 h-4 mr-2" />
                    Skattemeldinger
                </flux:button>
                <flux:button href="{{ route('annual-accounts.index') }}" variant="ghost" class="w-full justify-start">
                    <flux:icon.chart-bar class="w-4 h-4 mr-2" />
                    Årsregnskap
                </flux:button>
            </div>

            <flux:separator class="my-4" />

            <flux:heading size="sm" class="mb-3">Frister {{ $selectedYear }}</flux:heading>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">RF-1086</flux:text>
                    <flux:text class="font-medium">31. januar</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">RF-1028</flux:text>
                    <flux:text class="font-medium">31. mai</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">Årsregnskap</flux:text>
                    <flux:text class="font-medium">31. juli</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- All deadlines table --}}
    <flux:card class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="lg">Alle frister {{ $selectedYear }}</flux:heading>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Innsending</flux:table.column>
                <flux:table.column>Skjema</flux:table.column>
                <flux:table.column>Regnskapsår</flux:table.column>
                <flux:table.column>Mottaker</flux:table.column>
                <flux:table.column>Frist</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($deadlines as $deadline)
                    <flux:table.row wire:key="deadline-{{ $deadline['type'] }}">
                        <flux:table.cell class="font-medium">{{ $deadline['name'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="zinc" size="sm">{{ $deadline['code'] }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $deadline['fiscal_year'] }}</flux:table.cell>
                        <flux:table.cell>{{ $deadline['recipient'] }}</flux:table.cell>
                        <flux:table.cell>{{ $deadline['deadline']->format('d.m.Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$deadline['status']['color']">{{ $deadline['status']['label'] }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($deadline['route'])
                                <flux:button href="{{ route($deadline['route']) }}" size="xs" variant="ghost">
                                    Åpne
                                </flux:button>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- Submission history --}}
    <flux:card>
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="lg">Innsendingshistorikk</flux:heading>
        </div>

        @if ($submissions->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>År</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Referanse</flux:table.column>
                    <flux:table.column>Sendt</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($submissions as $submission)
                        <flux:table.row wire:key="submission-{{ $submission->id }}">
                            <flux:table.cell class="font-medium">{{ $submission->type_label }}</flux:table.cell>
                            <flux:table.cell>{{ $submission->year }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$submission->status_color">{{ $submission->status_label }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($submission->altinn_reference)
                                    <code class="text-xs bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded">{{ $submission->altinn_reference }}</code>
                                @else
                                    <flux:text class="text-zinc-400">-</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($submission->submitted_at)
                                    {{ $submission->submitted_at->format('d.m.Y H:i') }}
                                @else
                                    <flux:text class="text-zinc-400">Ikke sendt</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button wire:click="viewSubmission({{ $submission->id }})" size="xs" variant="ghost">
                                    Detaljer
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:text class="text-zinc-500 dark:text-zinc-400 py-8 text-center">
                Ingen innsendinger registrert for dette året
            </flux:text>
        @endif
    </flux:card>

    {{-- Submission detail modal --}}
    <flux:modal wire:model="showSubmissionModal" class="max-w-xl">
        @if ($viewingSubmission)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $viewingSubmission->type_label }}</flux:heading>
                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                        Regnskapsår {{ $viewingSubmission->year }}
                    </flux:text>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Status</flux:text>
                        <flux:badge :color="$viewingSubmission->status_color" class="mt-1">
                            {{ $viewingSubmission->status_label }}
                        </flux:badge>
                    </div>

                    <div>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Opprettet</flux:text>
                        <flux:text class="font-medium">{{ $viewingSubmission->created_at->format('d.m.Y H:i') }}</flux:text>
                    </div>

                    @if ($viewingSubmission->submitted_at)
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Sendt inn</flux:text>
                            <flux:text class="font-medium">{{ $viewingSubmission->submitted_at->format('d.m.Y H:i') }}</flux:text>
                        </div>
                    @endif

                    @if ($viewingSubmission->altinn_reference)
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Altinn-referanse</flux:text>
                            <code class="text-sm bg-zinc-100 dark:bg-zinc-700 px-2 py-1 rounded">{{ $viewingSubmission->altinn_reference }}</code>
                        </div>
                    @endif

                    @if ($viewingSubmission->altinn_instance_id)
                        <div class="col-span-2">
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Instance ID</flux:text>
                            <code class="text-xs bg-zinc-100 dark:bg-zinc-700 px-2 py-1 rounded block mt-1 break-all">{{ $viewingSubmission->altinn_instance_id }}</code>
                        </div>
                    @endif
                </div>

                @if ($viewingSubmission->validation_errors && count($viewingSubmission->validation_errors) > 0)
                    <div>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">Valideringsfeil</flux:text>
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 space-y-1">
                                @foreach ($viewingSubmission->validation_errors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button wire:click="closeSubmissionModal" variant="ghost">Lukk</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
