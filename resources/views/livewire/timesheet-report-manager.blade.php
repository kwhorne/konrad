<div>
    {{-- Filter section --}}
    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-end gap-4">
            {{-- Report type tabs --}}
            <div class="flex-1">
                <flux:label class="mb-2">Rapporttype</flux:label>
                <div class="flex flex-wrap gap-2">
                    <flux:button
                        wire:click="setReportType('project')"
                        :variant="$reportType === 'project' || $reportType === 'project_detail' ? 'primary' : 'ghost'"
                        size="sm"
                    >
                        Per prosjekt
                    </flux:button>
                    <flux:button
                        wire:click="setReportType('work_order')"
                        :variant="$reportType === 'work_order' ? 'primary' : 'ghost'"
                        size="sm"
                    >
                        Per arbeidsordre
                    </flux:button>
                    <flux:button
                        wire:click="setReportType('employee')"
                        :variant="$reportType === 'employee' ? 'primary' : 'ghost'"
                        size="sm"
                    >
                        Per ansatt
                    </flux:button>
                    <flux:button
                        wire:click="setReportType('weekly')"
                        :variant="$reportType === 'weekly' ? 'primary' : 'ghost'"
                        size="sm"
                    >
                        Per uke
                    </flux:button>
                </div>
            </div>

            {{-- Date range --}}
            <div class="flex flex-wrap items-end gap-3">
                <flux:field class="w-36">
                    <flux:label>Fra dato</flux:label>
                    <flux:input type="date" wire:model.live="fromDate" size="sm" />
                </flux:field>
                <flux:field class="w-36">
                    <flux:label>Til dato</flux:label>
                    <flux:input type="date" wire:model.live="toDate" size="sm" />
                </flux:field>

                {{-- Quick period buttons --}}
                <flux:dropdown>
                    <flux:button variant="ghost" size="sm" icon-trailing="chevron-down">
                        Hurtigvalg
                    </flux:button>
                    <flux:menu>
                        <flux:menu.item wire:click="setQuickPeriod('this_week')">Denne uken</flux:menu.item>
                        <flux:menu.item wire:click="setQuickPeriod('last_week')">Forrige uke</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item wire:click="setQuickPeriod('this_month')">Denne måneden</flux:menu.item>
                        <flux:menu.item wire:click="setQuickPeriod('last_month')">Forrige måned</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item wire:click="setQuickPeriod('this_quarter')">Dette kvartalet</flux:menu.item>
                        <flux:menu.item wire:click="setQuickPeriod('this_year')">Dette året</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item wire:click="setQuickPeriod('all_time')">Hele perioden</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>

        <div class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
            Viser data for: <span class="font-medium">{{ $this->periodLabel }}</span>
        </div>
    </div>

    @php $data = $this->reportData; @endphp

    {{-- Summary cards --}}
    @if(isset($data['summary']))
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="text-sm text-zinc-500 dark:text-zinc-400">Totalt timer</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($data['summary']['total_hours'], 1) }}</div>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="text-sm text-zinc-500 dark:text-zinc-400">Fakturerbare timer</div>
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($data['summary']['billable_hours'], 1) }}</div>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="text-sm text-zinc-500 dark:text-zinc-400">Godkjente timer</div>
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($data['summary']['approved_hours'], 1) }}</div>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="text-sm text-zinc-500 dark:text-zinc-400">Ansatte</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $data['summary']['employee_count'] }}</div>
            </div>
        </div>
    @endif

    {{-- Report content --}}
    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
        @if($reportType === 'project')
            {{-- Project report --}}
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <flux:heading size="lg">Timer per prosjekt</flux:heading>
            </div>
            @if($data['projects']->isEmpty())
                <div class="p-8 text-center text-zinc-500 dark:text-zinc-400">
                    Ingen timeføringer funnet for valgt periode.
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Prosjekt</flux:table.column>
                        <flux:table.column class="text-right">Timer</flux:table.column>
                        <flux:table.column class="text-right">Fakturerbart</flux:table.column>
                        <flux:table.column class="text-right">Registreringer</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($data['projects'] as $project)
                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $project['project_name'] }}</div>
                                    @if($project['project_number'])
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $project['project_number'] }}</div>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="text-right font-medium">
                                    {{ number_format($project['total_hours'], 1) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($project['billable_hours'], 1) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right text-zinc-500">
                                    {{ $project['entry_count'] }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:button wire:click="viewProjectDetails({{ $project['project_id'] }})" variant="ghost" size="sm" icon="eye">
                                        Detaljer
                                    </flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif

        @elseif($reportType === 'project_detail')
            {{-- Project detail view --}}
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                <div>
                    <flux:button wire:click="backToProjectList" variant="ghost" size="sm" icon="arrow-left" class="mb-2">
                        Tilbake
                    </flux:button>
                    <flux:heading size="lg">{{ $data['project']?->name ?? 'Prosjektdetaljer' }}</flux:heading>
                    @if($data['project']?->project_number)
                        <flux:text class="text-sm text-zinc-500">{{ $data['project']->project_number }}</flux:text>
                    @endif
                </div>
            </div>

            {{-- Hours by employee for this project --}}
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <flux:heading size="md" class="mb-3">Timer per ansatt</flux:heading>
                @if($data['employees']->isEmpty())
                    <flux:text class="text-zinc-500">Ingen timeføringer.</flux:text>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($data['employees'] as $employee)
                            <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                                <div class="font-medium text-zinc-900 dark:text-white">{{ $employee['user_name'] }}</div>
                                <div class="flex justify-between text-sm mt-1">
                                    <span class="text-zinc-500">Timer:</span>
                                    <span class="font-medium">{{ number_format($employee['total_hours'], 1) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-500">Fakturerbart:</span>
                                    <span class="text-emerald-600">{{ number_format($employee['billable_hours'], 1) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent entries --}}
            <div class="p-4">
                <flux:heading size="md" class="mb-3">Siste registreringer</flux:heading>
                @if($data['entries']->isEmpty())
                    <flux:text class="text-zinc-500">Ingen registreringer.</flux:text>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Dato</flux:table.column>
                            <flux:table.column>Ansatt</flux:table.column>
                            <flux:table.column>Beskrivelse</flux:table.column>
                            <flux:table.column class="text-right">Timer</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($data['entries']->take(20) as $entry)
                                <flux:table.row>
                                    <flux:table.cell>{{ $entry->entry_date->format('d.m.Y') }}</flux:table.cell>
                                    <flux:table.cell>{{ $entry->timesheet->user->name ?? '-' }}</flux:table.cell>
                                    <flux:table.cell class="text-sm text-zinc-500">{{ $entry->description ?? '-' }}</flux:table.cell>
                                    <flux:table.cell class="text-right font-medium">{{ number_format($entry->hours, 1) }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                    @if($data['entries']->count() > 20)
                        <div class="mt-3 text-sm text-zinc-500 text-center">
                            Viser 20 av {{ $data['entries']->count() }} registreringer
                        </div>
                    @endif
                @endif
            </div>

        @elseif($reportType === 'work_order')
            {{-- Work order report --}}
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <flux:heading size="lg">Timer per arbeidsordre</flux:heading>
            </div>
            @if($data['workOrders']->isEmpty())
                <div class="p-8 text-center text-zinc-500 dark:text-zinc-400">
                    Ingen timeføringer funnet for valgt periode.
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Arbeidsordre</flux:table.column>
                        <flux:table.column>Prosjekt</flux:table.column>
                        <flux:table.column class="text-right">Timer</flux:table.column>
                        <flux:table.column class="text-right">Fakturerbart</flux:table.column>
                        <flux:table.column class="text-right">Registreringer</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($data['workOrders'] as $workOrder)
                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $workOrder['work_order_title'] }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $workOrder['work_order_number'] }}</div>
                                </flux:table.cell>
                                <flux:table.cell class="text-zinc-500">
                                    {{ $workOrder['project_name'] ?? '-' }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right font-medium">
                                    {{ number_format($workOrder['total_hours'], 1) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($workOrder['billable_hours'], 1) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right text-zinc-500">
                                    {{ $workOrder['entry_count'] }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif

        @elseif($reportType === 'employee')
            {{-- Employee report --}}
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <flux:heading size="lg">Timer per ansatt</flux:heading>
            </div>
            @if($data['employees']->isEmpty())
                <div class="p-8 text-center text-zinc-500 dark:text-zinc-400">
                    Ingen timeføringer funnet for valgt periode.
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Ansatt</flux:table.column>
                        <flux:table.column class="text-right">Totalt</flux:table.column>
                        <flux:table.column class="text-right">Fakturerbart</flux:table.column>
                        <flux:table.column class="text-right">Godkjent</flux:table.column>
                        <flux:table.column class="text-right">Ventende</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($data['employees'] as $employee)
                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $employee['user_name'] }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $employee['user_email'] }}</div>
                                </flux:table.cell>
                                <flux:table.cell class="text-right font-medium">
                                    {{ number_format($employee['total_hours'], 1) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($employee['billable_hours'], 1) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right text-blue-600 dark:text-blue-400">
                                    {{ number_format($employee['approved_hours'], 1) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right text-amber-600 dark:text-amber-400">
                                    {{ number_format($employee['pending_hours'], 1) }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif

        @elseif($reportType === 'weekly')
            {{-- Weekly report --}}
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <flux:heading size="lg">Timer per uke</flux:heading>
            </div>
            @if($data['weeks']->isEmpty())
                <div class="p-8 text-center text-zinc-500 dark:text-zinc-400">
                    Ingen timeføringer funnet for valgt periode.
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Uke</flux:table.column>
                        <flux:table.column class="text-right">Totalt</flux:table.column>
                        <flux:table.column class="text-right">Fakturerbart</flux:table.column>
                        <flux:table.column class="text-right">Ansatte</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($data['weeks'] as $week)
                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $week['week_label'] }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ \Carbon\Carbon::parse($week['week_start'])->format('d.m') }} - {{ \Carbon\Carbon::parse($week['week_start'])->addDays(6)->format('d.m.Y') }}</div>
                                </flux:table.cell>
                                <flux:table.cell class="text-right font-medium">
                                    {{ number_format($week['total_hours'], 1) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($week['billable_hours'], 1) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right text-zinc-500">
                                    {{ $week['employee_count'] }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        @endif
    </div>
</div>
