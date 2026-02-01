<div>
    {{-- Header with week navigation --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <flux:button wire:click="previousWeek" variant="ghost" icon="chevron-left" />
            <div class="text-center">
                <flux:heading size="lg">{{ $this->timesheet?->week_label }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $this->timesheet?->date_range_label }}
                </flux:text>
            </div>
            <flux:button wire:click="nextWeek" variant="ghost" icon="chevron-right" />
        </div>

        <div class="flex items-center gap-3">
            <flux:button wire:click="goToCurrentWeek" variant="ghost" size="sm">
                I dag
            </flux:button>

            @if($this->timesheet?->is_editable)
                <flux:button wire:click="openQuickEntryModal" variant="primary" size="sm">
                    <flux:icon.plus class="w-4 h-4 mr-1" />
                    Registrer timer
                </flux:button>
            @endif

            @if($this->timesheet)
                <flux:badge :color="$this->timesheet->status_color">
                    {{ $this->timesheet->status_label }}
                </flux:badge>
            @endif
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

    {{-- Rejection message --}}
    @if($this->timesheet?->status === 'rejected' && $this->timesheet->rejection_reason)
        <flux:callout variant="danger" class="mb-6">
            <flux:callout.heading>Timeseddelen ble avvist</flux:callout.heading>
            <flux:callout.text>{{ $this->timesheet->rejection_reason }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Timesheet grid --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="pb-3 pr-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider min-w-[200px]">
                                Prosjekt / Arbeidsordre
                            </th>
                            @foreach($this->days as $day)
                                <th class="pb-3 px-2 text-center text-xs font-medium {{ $day['is_weekend'] ? 'text-zinc-400 dark:text-zinc-500' : 'text-zinc-500 dark:text-zinc-400' }} uppercase tracking-wider w-20">
                                    <div>{{ $day['day_name'] }}</div>
                                    <div class="text-xs font-normal">{{ $day['date']->format('d.m') }}</div>
                                </th>
                            @endforeach
                            <th class="pb-3 pl-4 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-20">
                                Sum
                            </th>
                            @if($this->timesheet?->is_editable)
                                <th class="pb-3 pl-2 w-10"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($rows as $index => $row)
                            <tr wire:key="row-{{ $row['key'] }}" class="group">
                                <td class="py-3 pr-4">
                                    <div class="flex items-center gap-2">
                                        @if($row['type'] === 'work_order')
                                            <flux:icon.wrench-screwdriver class="w-4 h-4 text-zinc-400" />
                                        @elseif($row['type'] === 'project')
                                            <flux:icon.folder class="w-4 h-4 text-zinc-400" />
                                        @else
                                            <flux:icon.document-text class="w-4 h-4 text-zinc-400" />
                                        @endif
                                        <flux:text class="text-sm font-medium text-zinc-900 dark:text-white truncate max-w-[180px]" title="{{ $row['label'] }}">
                                            {{ $row['label'] }}
                                        </flux:text>
                                    </div>
                                </td>
                                @foreach($this->days as $day)
                                    @php $dateKey = $day['date']->format('Y-m-d'); @endphp
                                    <td class="py-3 px-1">
                                        @if($this->timesheet?->is_editable)
                                            <input
                                                type="number"
                                                step="0.5"
                                                min="0"
                                                max="24"
                                                wire:change="updateHours({{ $index }}, '{{ $dateKey }}', $event.target.value)"
                                                wire:dblclick="openQuickEntryModal('{{ $dateKey }}', {{ $index }})"
                                                value="{{ $row['hours'][$dateKey] ?? '' }}"
                                                class="w-16 text-center text-sm rounded-md border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 focus:border-indigo-500 focus:ring-indigo-500 {{ $day['is_weekend'] ? 'bg-zinc-50 dark:bg-zinc-800/50' : '' }} cursor-pointer"
                                                placeholder="-"
                                                title="Dobbelklikk for å åpne registreringsskjema"
                                            />
                                        @else
                                            <div class="w-16 text-center text-sm {{ $day['is_weekend'] ? 'text-zinc-400' : 'text-zinc-600 dark:text-zinc-300' }}">
                                                {{ $row['hours'][$dateKey] ?? '-' }}
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="py-3 pl-4 text-right">
                                    <flux:text class="font-semibold">{{ number_format($this->getRowTotal($index), 1) }}</flux:text>
                                </td>
                                @if($this->timesheet?->is_editable)
                                    <td class="py-3 pl-2">
                                        <flux:button
                                            wire:click="deleteRow({{ $index }})"
                                            wire:confirm="Er du sikker på at du vil slette denne raden?"
                                            variant="ghost"
                                            size="xs"
                                            class="opacity-0 group-hover:opacity-100"
                                        >
                                            <flux:icon.trash class="w-4 h-4 text-red-500" />
                                        </flux:button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($this->days) + 3 }}" class="py-8 text-center">
                                    <flux:icon.clock class="w-12 h-12 mx-auto text-zinc-300 dark:text-zinc-600 mb-3" />
                                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                                        Ingen timer registrert denne uken.
                                    </flux:text>
                                    @if($this->timesheet?->is_editable)
                                        <flux:button wire:click="openAddRowModal" variant="ghost" size="sm" class="mt-3">
                                            Legg til første rad
                                        </flux:button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($rows) > 0)
                        <tfoot>
                            <tr class="border-t-2 border-zinc-300 dark:border-zinc-600">
                                <td class="pt-3 pr-4">
                                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">Totalt</flux:text>
                                </td>
                                @foreach($this->days as $day)
                                    @php $dateKey = $day['date']->format('Y-m-d'); @endphp
                                    <td class="pt-3 px-1 text-center">
                                        <flux:text class="font-medium {{ $day['is_weekend'] ? 'text-zinc-400' : '' }}">
                                            {{ number_format($this->getTotalHoursForDay($dateKey), 1) }}
                                        </flux:text>
                                    </td>
                                @endforeach
                                <td class="pt-3 pl-4 text-right">
                                    <flux:text class="font-bold text-lg">{{ number_format($this->timesheet?->total_hours ?? 0, 1) }}</flux:text>
                                </td>
                                @if($this->timesheet?->is_editable)
                                    <td></td>
                                @endif
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- Actions --}}
            @if($this->timesheet?->is_editable)
                <div class="mt-6 flex flex-wrap items-center justify-between gap-4 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button wire:click="openAddRowModal" variant="ghost">
                        <flux:icon.plus class="w-4 h-4 mr-2" />
                        Legg til linje
                    </flux:button>

                    <flux:button
                        wire:click="openSubmitModal"
                        variant="primary"
                        :disabled="!$this->timesheet?->is_submittable"
                    >
                        <flux:icon.paper-airplane class="w-4 h-4 mr-2" />
                        Send til godkjenning
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Add row modal --}}
    <flux:modal wire:model.self="showAddRowModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Legg til linje</flux:heading>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Type</flux:label>
                    <flux:select wire:model.live="newRowType">
                        <option value="project">Prosjekt</option>
                        <option value="workorder">Arbeidsordre</option>
                        <option value="other">Annet (intern tid)</option>
                    </flux:select>
                </flux:field>

                @if($newRowType === 'project')
                    <flux:field>
                        <flux:label>Prosjekt</flux:label>
                        <flux:select wire:model="newRowProjectId">
                            <option value="">Velg prosjekt...</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->project_number }}: {{ $project->name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                @elseif($newRowType === 'workorder')
                    <flux:field>
                        <flux:label>Arbeidsordre</flux:label>
                        <flux:select wire:model="newRowWorkOrderId">
                            <option value="">Velg arbeidsordre...</option>
                            @foreach($workOrders as $workOrder)
                                <option value="{{ $workOrder->id }}">{{ $workOrder->work_order_number }}: {{ $workOrder->title }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                @elseif($newRowType === 'other')
                    <flux:field>
                        <flux:label>Beskrivelse</flux:label>
                        <flux:input wire:model="newRowDescription" placeholder="F.eks. Internt mote, Opplaering, etc." />
                    </flux:field>
                @endif
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <flux:button wire:click="closeAddRowModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="addRow" variant="primary">Legg til</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Submit modal --}}
    <flux:modal wire:model.self="showSubmitModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Send til godkjenning</flux:heading>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    Du er i ferd med a sende timeseddelen for {{ $this->timesheet?->week_label }} til godkjenning.
                </flux:text>

                <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <flux:text>Totalt timer:</flux:text>
                        <flux:text class="font-bold text-lg">{{ number_format($this->timesheet?->total_hours ?? 0, 1) }}</flux:text>
                    </div>
                </div>

                <flux:field>
                    <flux:label>Kommentar (valgfritt)</flux:label>
                    <flux:textarea wire:model="notes" placeholder="Legg til en kommentar til godkjenneren..." rows="3" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <flux:button wire:click="closeSubmitModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="submit" variant="primary">
                    <flux:icon.paper-airplane class="w-4 h-4 mr-2" />
                    Send til godkjenning
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Quick entry modal --}}
    <flux:modal wire:model.self="showQuickEntryModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Registrer timer</flux:heading>
                <flux:text class="text-sm text-zinc-500">{{ $this->timesheet?->week_label }}</flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                {{-- Type selection --}}
                <flux:field>
                    <flux:label>Type</flux:label>
                    <flux:select wire:model.live="quickEntryType">
                        <option value="project">Prosjekt</option>
                        <option value="workorder">Arbeidsordre</option>
                        <option value="other">Annet (intern tid)</option>
                    </flux:select>
                </flux:field>

                {{-- Project/WorkOrder/Description based on type --}}
                @if($quickEntryType === 'project')
                    <flux:field>
                        <flux:label>Prosjekt</flux:label>
                        <flux:select wire:model="quickEntryProjectId">
                            <option value="">Velg prosjekt...</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->project_number }}: {{ $project->name }}</option>
                            @endforeach
                        </flux:select>
                        @error('quickEntryProjectId') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                @elseif($quickEntryType === 'workorder')
                    <flux:field>
                        <flux:label>Arbeidsordre</flux:label>
                        <flux:select wire:model="quickEntryWorkOrderId">
                            <option value="">Velg arbeidsordre...</option>
                            @foreach($workOrders as $workOrder)
                                <option value="{{ $workOrder->id }}">{{ $workOrder->work_order_number }}: {{ $workOrder->title }}</option>
                            @endforeach
                        </flux:select>
                        @error('quickEntryWorkOrderId') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                @elseif($quickEntryType === 'other')
                    <flux:field>
                        <flux:label>Beskrivelse</flux:label>
                        <flux:input wire:model="quickEntryDescription" placeholder="F.eks. Internt møte, Opplæring" />
                        @error('quickEntryDescription') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                @endif

                {{-- Date and hours in a row --}}
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Dato</flux:label>
                        <flux:select wire:model="quickEntryDate">
                            @foreach($this->days as $day)
                                <option value="{{ $day['date']->format('Y-m-d') }}">
                                    {{ $day['day_name'] }} {{ $day['date']->format('d.m') }}
                                </option>
                            @endforeach
                        </flux:select>
                        @error('quickEntryDate') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Timer</flux:label>
                        <flux:input type="number" wire:model="quickEntryHours" step="0.5" min="0.5" max="24" placeholder="0.0" />
                        @error('quickEntryHours') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                </div>

                {{-- Note field --}}
                <flux:field>
                    <flux:label>Notat (valgfritt)</flux:label>
                    <flux:textarea wire:model="quickEntryNote" rows="2" placeholder="Beskriv hva du har jobbet med..." />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <flux:button wire:click="closeQuickEntryModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="saveQuickEntry" variant="primary">
                    <flux:icon.plus class="w-4 h-4 mr-2" />
                    Registrer
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
