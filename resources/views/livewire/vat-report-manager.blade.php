<div>
    @if(!$editingReport)
        {{-- List View --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
            <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk..." icon="magnifying-glass" class="w-full sm:w-48" />

                <flux:select wire:model.live="filterYear" class="w-full sm:w-32">
                    @foreach($this->availableYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filterStatus" class="w-full sm:w-40">
                    <option value="">Alle statuser</option>
                    <option value="draft">Utkast</option>
                    <option value="calculated">Beregnet</option>
                    <option value="submitted">Sendt</option>
                    <option value="accepted">Godkjent</option>
                    <option value="rejected">Avvist</option>
                </flux:select>
            </div>

            <flux:button wire:click="openCreateModal" variant="primary">
                <flux:icon.plus class="w-5 h-5 mr-2" />
                Ny MVA-melding
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

        {{-- Reports table --}}
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                @if($this->reports->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Periode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Utg. MVA</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Inng. MVA</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">A betale</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Handlinger</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($this->reports as $report)
                                    <tr wire:key="report-{{ $report->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div>
                                                <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $report->period_name }}</flux:text>
                                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $report->period_from->format('d.m') }} - {{ $report->period_to->format('d.m.Y') }}</flux:text>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <flux:text class="text-zinc-700 dark:text-zinc-300">{{ $report->report_type_name }}</flux:text>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <flux:badge color="{{ $report->status_color }}">{{ $report->status_name }}</flux:badge>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <flux:text class="font-mono text-zinc-900 dark:text-white">{{ number_format($report->total_output_vat, 2, ',', ' ') }}</flux:text>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <flux:text class="font-mono text-zinc-900 dark:text-white">{{ number_format($report->total_input_vat, 2, ',', ' ') }}</flux:text>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <flux:text class="font-mono font-medium {{ $report->vat_payable >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                                {{ number_format($report->vat_payable, 2, ',', ' ') }}
                                            </flux:text>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <flux:button wire:click="openEditModal({{ $report->id }})" variant="ghost" size="sm" title="Åpne">
                                                    <flux:icon.eye class="w-4 h-4" />
                                                </flux:button>
                                                @if($report->status === 'draft')
                                                    <flux:button wire:click="delete({{ $report->id }})" wire:confirm="Er du sikker på at du vil slette denne MVA-meldingen?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700" title="Slett">
                                                        <flux:icon.trash class="w-4 h-4" />
                                                    </flux:button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">{{ $this->reports->links() }}</div>
                @else
                    <div class="text-center py-12">
                        <flux:icon.document-chart-bar class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                        <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                            Ingen MVA-meldinger funnet
                        </flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                            Opprett din første MVA-melding for å komme i gang
                        </flux:text>
                        <flux:button wire:click="openCreateModal" variant="primary">
                            <flux:icon.plus class="w-5 h-5 mr-2" />
                            Ny MVA-melding
                        </flux:button>
                    </div>
                @endif
            </div>
        </flux:card>
    @else
        {{-- Detail/Edit View --}}
        <div class="space-y-6">
            {{-- Action buttons --}}
            <div class="flex flex-col sm:flex-row justify-between gap-4">
                <flux:button wire:click="closeEditModal" variant="ghost">
                    <flux:icon.arrow-left class="w-4 h-4 mr-2" />
                    Tilbake til liste
                </flux:button>

                <div class="flex flex-wrap gap-2">
                    @if($editingReport->status === 'draft' || $editingReport->status === 'calculated')
                        <flux:button wire:click="calculate" variant="primary">
                            <flux:icon.calculator class="w-4 h-4 mr-2" />
                            Beregn MVA
                        </flux:button>
                    @endif

                    @if($editingReport->status === 'calculated')
                        <flux:button wire:click="openSubmitModal" variant="primary">
                            <flux:icon.paper-airplane class="w-4 h-4 mr-2" />
                            Merk som sendt
                        </flux:button>
                    @endif

                    @if($editingReport->status === 'submitted')
                        <flux:button wire:click="markAccepted" variant="primary">
                            <flux:icon.check class="w-4 h-4 mr-2" />
                            Godkjent
                        </flux:button>
                        <flux:button wire:click="markRejected" variant="danger">
                            <flux:icon.x-mark class="w-4 h-4 mr-2" />
                            Avvist
                        </flux:button>
                    @endif
                </div>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <flux:text class="text-green-800 dark:text-green-200">{{ session('success') }}</flux:text>
                </div>
            @endif

            {{-- Summary cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                    <div class="p-4">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Utgaende MVA</flux:text>
                        <flux:text class="text-2xl font-bold font-mono text-zinc-900 dark:text-white">{{ number_format($editingReport->total_output_vat, 2, ',', ' ') }}</flux:text>
                    </div>
                </flux:card>
                <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                    <div class="p-4">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Inngaende MVA (fradrag)</flux:text>
                        <flux:text class="text-2xl font-bold font-mono text-zinc-900 dark:text-white">{{ number_format($editingReport->total_input_vat, 2, ',', ' ') }}</flux:text>
                    </div>
                </flux:card>
                <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                    <div class="p-4">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">MVA a betale</flux:text>
                        <flux:text class="text-2xl font-bold font-mono {{ $editingReport->vat_payable >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ number_format($editingReport->vat_payable, 2, ',', ' ') }}
                        </flux:text>
                    </div>
                </flux:card>
                <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                    <div class="p-4">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Status</flux:text>
                        <div class="mt-1">
                            <flux:badge color="{{ $editingReport->status_color }}" size="lg">{{ $editingReport->status_name }}</flux:badge>
                        </div>
                    </div>
                </flux:card>
            </div>

            {{-- Report lines by category --}}
            @php
                $groupedLines = $editingReport->lines->groupBy(fn($line) => $line->vatCode->category ?? 'other');
                $categoryNames = [
                    'salg_norge' => 'Salg av varer og tjenester i Norge',
                    'kjop_norge' => 'Kjop av varer og tjenester i Norge',
                    'import' => 'Kjop av tjenester fra utlandet (import)',
                    'export' => 'Utforsel av varer og tjenester',
                    'other' => 'Andre forhold',
                ];
            @endphp

            @foreach($categoryNames as $categoryKey => $categoryName)
                @if($groupedLines->has($categoryKey))
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="p-6">
                            <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-4">{{ $categoryName }}</flux:heading>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-20">MVA-kode</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Beskrivelse</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-48">Merknad</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-32">Grunnlag</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-24">MVA-sats</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-32">Avgift</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-20"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach($groupedLines[$categoryKey] as $line)
                                            <tr wire:key="line-{{ $line->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                                <td class="px-4 py-3">
                                                    <flux:badge variant="outline">{{ $line->vatCode->code }}</flux:badge>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <flux:text class="text-zinc-900 dark:text-white">{{ $line->vatCode->name }}</flux:text>
                                                    @if($line->is_manual_override)
                                                        <flux:badge size="sm" color="yellow" class="ml-2">Manuell</flux:badge>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $line->note ?? '-' }}</flux:text>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <flux:text class="font-mono text-zinc-900 dark:text-white">{{ number_format($line->base_amount, 2, ',', ' ') }}</flux:text>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <flux:text class="font-mono text-zinc-700 dark:text-zinc-300">
                                                        {{ $line->vat_rate !== null ? number_format($line->vat_rate, 0) . ' %' : '-' }}
                                                    </flux:text>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <flux:text class="font-mono font-medium text-zinc-900 dark:text-white">{{ number_format($line->vat_amount, 2, ',', ' ') }}</flux:text>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    @if($editingReport->status !== 'submitted' && $editingReport->status !== 'accepted')
                                                        <flux:button wire:click="openLineModal({{ $line->id }})" variant="ghost" size="sm">
                                                            <flux:icon.pencil class="w-4 h-4" />
                                                        </flux:button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </flux:card>
                @endif
            @endforeach

            {{-- No lines message --}}
            @if($editingReport->lines->isEmpty())
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-12 text-center">
                        <flux:icon.calculator class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                        <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                            Ingen beregninger ennå
                        </flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                            Klikk "Beregn MVA" for å beregne avgifter fra fakturaer og bilag
                        </flux:text>
                        <flux:button wire:click="calculate" variant="primary">
                            <flux:icon.calculator class="w-5 h-5 mr-2" />
                            Beregn MVA
                        </flux:button>
                    </div>
                </flux:card>
            @endif

            {{-- Note and Attachments --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Note --}}
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <flux:heading size="lg" class="text-zinc-900 dark:text-white">Merknad</flux:heading>
                            @if($editingReport->status !== 'submitted' && $editingReport->status !== 'accepted')
                                <flux:button wire:click="openNoteModal" variant="ghost" size="sm">
                                    <flux:icon.pencil class="w-4 h-4 mr-1" />
                                    Rediger
                                </flux:button>
                            @endif
                        </div>
                        @if($editingReport->note)
                            <flux:text class="text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ $editingReport->note }}</flux:text>
                        @else
                            <flux:text class="text-zinc-500 dark:text-zinc-400 italic">Ingen merknad</flux:text>
                        @endif
                    </div>
                </flux:card>

                {{-- Attachments --}}
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-4">Vedlegg</flux:heading>

                        @if($editingReport->attachments->count() > 0)
                            <div class="flex flex-col gap-2 mb-4">
                                @foreach($editingReport->attachments as $att)
                                    <flux:file-item wire:key="att-{{ $att->id }}" :heading="$att->original_filename" :description="$att->human_size">
                                        <x-slot name="actions">
                                            <a href="{{ $att->url }}" target="_blank" class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 p-1">
                                                <flux:icon.arrow-down-tray class="w-4 h-4" />
                                            </a>
                                            @if($editingReport->status !== 'submitted' && $editingReport->status !== 'accepted')
                                                <flux:file-item.remove wire:click="removeAttachment({{ $att->id }})" wire:confirm="Slette vedlegget?" />
                                            @endif
                                        </x-slot>
                                    </flux:file-item>
                                @endforeach
                            </div>
                        @else
                            <flux:text class="text-zinc-500 dark:text-zinc-400 italic mb-4">Ingen vedlegg</flux:text>
                        @endif

                        @if($editingReport->status !== 'submitted' && $editingReport->status !== 'accepted')
                            <flux:file-upload wire:model="attachment" label="Last opp vedlegg">
                                <flux:file-upload.dropzone
                                    heading="Slipp fil her eller klikk for a velge"
                                    text="PDF, JPG, PNG opptil 10MB"
                                    inline
                                />
                            </flux:file-upload>
                            @error('attachment')<flux:error class="mt-2">{{ $message }}</flux:error>@enderror

                            @if($attachment)
                                <div class="mt-3 flex flex-col gap-2">
                                    <flux:file-item :heading="$attachment->getClientOriginalName()">
                                        <x-slot name="actions">
                                            <flux:file-item.remove wire:click="$set('attachment', null)" />
                                        </x-slot>
                                    </flux:file-item>
                                </div>
                                <div class="mt-3">
                                    <flux:button wire:click="uploadAttachment" variant="primary" size="sm">
                                        <flux:icon.arrow-up-tray class="w-4 h-4 mr-1" />
                                        Last opp
                                    </flux:button>
                                </div>
                            @endif
                        @endif
                    </div>
                </flux:card>
            </div>

            {{-- Info --}}
            @if($editingReport->submitted_at || $editingReport->altinn_reference)
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-4">Innsendingsinfo</flux:heading>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @if($editingReport->submitted_at)
                                <div>
                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Sendt</flux:text>
                                    <flux:text class="text-zinc-900 dark:text-white">{{ $editingReport->submitted_at->format('d.m.Y H:i') }}</flux:text>
                                </div>
                            @endif
                            @if($editingReport->submitter)
                                <div>
                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Sendt av</flux:text>
                                    <flux:text class="text-zinc-900 dark:text-white">{{ $editingReport->submitter->name }}</flux:text>
                                </div>
                            @endif
                            @if($editingReport->altinn_reference)
                                <div>
                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Altinn-referanse</flux:text>
                                    <flux:text class="text-zinc-900 dark:text-white font-mono">{{ $editingReport->altinn_reference }}</flux:text>
                                </div>
                            @endif
                        </div>
                    </div>
                </flux:card>
            @endif
        </div>
    @endif

    {{-- Create Modal --}}
    <flux:modal wire:model="showCreateModal" variant="flyout" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Ny MVA-melding</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    Opprett en ny MVA-melding for innsending til Altinn
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Ar *</flux:label>
                    <flux:select wire:model.live="createYear">
                        @foreach($this->availableYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Periode *</flux:label>
                    <flux:select wire:model="createPeriod">
                        <option value="0">Velg periode...</option>
                        @foreach($this->availablePeriods as $period)
                            <option value="{{ $period['period'] }}">{{ $period['name'] }}</option>
                        @endforeach
                    </flux:select>
                    @error('createPeriod')<flux:error>{{ $message }}</flux:error>@enderror
                    @if($this->availablePeriods->isEmpty())
                        <flux:description class="text-yellow-600">Alle perioder for {{ $createYear }} er allerede opprettet.</flux:description>
                    @endif
                </flux:field>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeCreateModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="create" variant="primary" :disabled="$this->availablePeriods->isEmpty()">Opprett</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Line Edit Modal --}}
    <flux:modal wire:model="showLineModal" variant="flyout" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Rediger linje</flux:heading>
                @if($editingLine)
                    <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                        {{ $editingLine->vatCode->code }} - {{ $editingLine->vatCode->name }}
                    </flux:text>
                @endif
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Grunnlag</flux:label>
                    <flux:input wire:model="lineBaseAmount" type="number" step="0.01" placeholder="0,00" />
                    @error('lineBaseAmount')<flux:error>{{ $message }}</flux:error>@enderror
                </flux:field>

                <flux:field>
                    <flux:label>Avgift (MVA)</flux:label>
                    <flux:input wire:model="lineVatAmount" type="number" step="0.01" placeholder="0,00" />
                    @error('lineVatAmount')<flux:error>{{ $message }}</flux:error>@enderror
                </flux:field>

                <flux:field>
                    <flux:label>Merknad</flux:label>
                    <flux:textarea wire:model="lineNote" rows="3" placeholder="Eventuell merknad..." />
                </flux:field>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeLineModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="saveLine" variant="primary">Lagre</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Note Modal --}}
    <flux:modal wire:model="showNoteModal" variant="flyout" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Rediger merknad</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    Legg til en merknad som sendes med meldingen
                </flux:text>
            </div>

            <flux:separator />

            <flux:field>
                <flux:label>Merknad</flux:label>
                <flux:textarea wire:model="reportNote" rows="6" placeholder="Skriv merknad her..." />
            </flux:field>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeNoteModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="saveNote" variant="primary">Lagre</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Submit Modal --}}
    <flux:modal wire:model="showSubmitModal" variant="flyout" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Merk som sendt</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    Merk MVA-meldingen som sendt til Altinn
                </flux:text>
            </div>

            <flux:separator />

            <flux:field>
                <flux:label>Altinn-referanse (valgfritt)</flux:label>
                <flux:input wire:model="altinnReference" type="text" placeholder="AR123456789" />
                <flux:description>Referansenummeret du fikk fra Altinn</flux:description>
            </flux:field>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeSubmitModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="submit" variant="primary">Merk som sendt</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
