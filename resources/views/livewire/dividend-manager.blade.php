<div>
    {{-- Header with filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex gap-3">
            <flux:select wire:model.live="filterYear" class="w-32">
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterStatus" class="w-40">
                <option value="">Alle statuser</option>
                <option value="declared">Vedtatt</option>
                <option value="approved">Godkjent</option>
                <option value="paid">Utbetalt</option>
                <option value="cancelled">Kansellert</option>
            </flux:select>
        </div>

        <flux:button wire:click="openModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Nytt utbytte
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

    {{-- Dividends table --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($dividends->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Ar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Klasse</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Per aksje</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Totalt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Utbetaling</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Handlinger</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($dividends as $dividend)
                                <tr wire:key="div-{{ $dividend->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-6 py-4">
                                        <flux:text class="font-medium">{{ $dividend->fiscal_year }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:badge variant="{{ $dividend->getDividendTypeBadgeColor() }}">
                                            {{ $dividend->getDividendTypeLabel() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text>{{ $dividend->shareClass->code }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <flux:text>{{ $dividend->getFormattedAmountPerShare() }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <flux:text class="font-medium">{{ $dividend->getFormattedTotalAmount() }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text>{{ $dividend->payment_date->format('d.m.Y') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:badge variant="{{ $dividend->getStatusBadgeColor() }}">
                                            {{ $dividend->getStatusLabel() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <flux:button wire:click="showDistribution({{ $dividend->id }})" variant="ghost" size="sm" title="Vis fordeling">
                                                <flux:icon.eye class="w-4 h-4" />
                                            </flux:button>
                                            @if($dividend->canBePaid())
                                                <flux:button wire:click="markAsPaid({{ $dividend->id }})" variant="ghost" size="sm" title="Merk som utbetalt">
                                                    <flux:icon.check class="w-4 h-4 text-green-600" />
                                                </flux:button>
                                            @endif
                                            @if($dividend->canBeCancelled())
                                                <flux:button wire:click="cancel({{ $dividend->id }})" variant="ghost" size="sm" title="Kanseller">
                                                    <flux:icon.x-mark class="w-4 h-4 text-red-600" />
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
                    {{ $dividends->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.banknotes class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="mb-2">Ingen utbytter</flux:heading>
                    <flux:text class="text-zinc-600 mb-6">Registrer ditt første utbyttevedtak</flux:text>
                    <flux:button wire:click="openModal" variant="primary">
                        <flux:icon.plus class="w-5 h-5 mr-2" />
                        Registrer utbytte
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Rediger utbytte' : 'Nytt utbytte' }}</flux:heading>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Regnskapsar *</flux:label>
                        <flux:input wire:model="fiscal_year" type="number" min="2000" max="2100" />
                        @error('fiscal_year') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Type *</flux:label>
                        <flux:select wire:model="dividend_type">
                            <option value="ordinary">Ordinaert</option>
                            <option value="extraordinary">Ekstraordinaert</option>
                        </flux:select>
                        @error('dividend_type') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Aksjeklasse *</flux:label>
                    <flux:select wire:model="share_class_id">
                        <option value="">Velg klasse</option>
                        @foreach($shareClasses as $class)
                            <option value="{{ $class->id }}">{{ $class->code }} - {{ $class->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('share_class_id') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Belop per aksje (NOK) *</flux:label>
                    <flux:input wire:model="amount_per_share" type="number" step="0.0001" min="0.0001" />
                    @error('amount_per_share') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <div class="grid grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>Vedtaksdato *</flux:label>
                        <flux:input wire:model="declaration_date" type="date" />
                        @error('declaration_date') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Reg.dato *</flux:label>
                        <flux:input wire:model="record_date" type="date" />
                        @error('record_date') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Utbetaling *</flux:label>
                        <flux:input wire:model="payment_date" type="date" />
                        @error('payment_date') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Protokollreferanse</flux:label>
                    <flux:input wire:model="resolution_reference" type="text" placeholder="Generalforsamling 2024" />
                    @error('resolution_reference') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:textarea wire:model="description" rows="2"></flux:textarea>
                    @error('description') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="save" variant="primary">{{ $editingId ? 'Oppdater' : 'Opprett' }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Distribution Modal --}}
    <flux:modal wire:model="showDistributionModal" class="max-w-2xl">
        @if($viewingDividend && $distribution)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Utbyttefordeling</flux:heading>
                    <flux:text class="mt-1 text-zinc-600">
                        {{ $viewingDividend->getDividendTypeLabel() }} - {{ $viewingDividend->shareClass->name }} ({{ $viewingDividend->fiscal_year }})
                    </flux:text>
                </div>

                <flux:separator />

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 uppercase">Aksjonaer</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 uppercase">Aksjer</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 uppercase">Brutto</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 uppercase">Kildeskatt</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 uppercase">Netto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($distribution as $item)
                                <tr>
                                    <td class="px-4 py-2">
                                        <flux:text class="font-medium">{{ $item['shareholder']->name }}</flux:text>
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <flux:text>{{ number_format($item['shares'], 0, ',', ' ') }}</flux:text>
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <flux:text>{{ number_format($item['gross_amount'], 2, ',', ' ') }}</flux:text>
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <flux:text class="text-red-600">{{ number_format($item['withholding_tax'], 2, ',', ' ') }}</flux:text>
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <flux:text class="font-medium text-green-600">{{ number_format($item['net_amount'], 2, ',', ' ') }}</flux:text>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <td class="px-4 py-2 font-medium">Totalt</td>
                                <td class="px-4 py-2 text-right font-medium">{{ number_format($distribution->sum('shares'), 0, ',', ' ') }}</td>
                                <td class="px-4 py-2 text-right font-medium">{{ number_format($distribution->sum('gross_amount'), 2, ',', ' ') }}</td>
                                <td class="px-4 py-2 text-right font-medium text-red-600">{{ number_format($distribution->sum('withholding_tax'), 2, ',', ' ') }}</td>
                                <td class="px-4 py-2 text-right font-medium text-green-600">{{ number_format($distribution->sum('net_amount'), 2, ',', ' ') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="flex justify-end">
                    <flux:button wire:click="closeDistributionModal" variant="ghost">Lukk</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
