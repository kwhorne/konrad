<div>
    {{-- Header with filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk..." icon="magnifying-glass" class="w-full sm:w-48" />

            <flux:select wire:model.live="filterType" class="w-full sm:w-40">
                <option value="">Alle typer</option>
                <option value="issue">Emisjon</option>
                <option value="transfer">Overdragelse</option>
                <option value="redemption">Innlosning</option>
                <option value="split">Aksjesplitt</option>
                <option value="bonus">Fondsemisjon</option>
            </flux:select>

            <flux:select wire:model.live="filterYear" class="w-full sm:w-32">
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterShareClass" class="w-full sm:w-40">
                <option value="">Alle klasser</option>
                @foreach($shareClasses as $class)
                    <option value="{{ $class->id }}">{{ $class->code }} - {{ $class->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <flux:button wire:click="openModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Ny transaksjon
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

    {{-- Transactions table --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($transactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Nr</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Dato</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Klasse</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Fra/Til</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Antall</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Belop</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($transactions as $transaction)
                                <tr wire:key="tx-{{ $transaction->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-6 py-4">
                                        <flux:badge variant="outline">{{ $transaction->transaction_number }}</flux:badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text>{{ $transaction->transaction_date->format('d.m.Y') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:badge variant="{{ $transaction->getTransactionTypeBadgeColor() }}">
                                            {{ $transaction->getTransactionTypeLabel() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text>{{ $transaction->shareClass->code }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            @if($transaction->fromShareholder)
                                                <div class="text-zinc-500">Fra: {{ $transaction->fromShareholder->name }}</div>
                                            @endif
                                            @if($transaction->toShareholder)
                                                <div class="text-zinc-900 dark:text-white">Til: {{ $transaction->toShareholder->name }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <flux:text class="font-medium">{{ number_format($transaction->number_of_shares, 0, ',', ' ') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <flux:text>{{ $transaction->getFormattedTotalAmount() }}</flux:text>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.arrows-right-left class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="mb-2">Ingen transaksjoner</flux:heading>
                    <flux:text class="text-zinc-600 mb-6">Registrer din første aksjetransaksjon</flux:text>
                    <flux:button wire:click="openModal" variant="primary">
                        <flux:icon.plus class="w-5 h-5 mr-2" />
                        Registrer transaksjon
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Ny aksjetransaksjon</flux:heading>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Transaksjonstype *</flux:label>
                    <flux:select wire:model.live="transaction_type">
                        <option value="issue">Emisjon (nye aksjer)</option>
                        <option value="transfer">Overdragelse (kjop/salg)</option>
                        <option value="redemption">Innlosning</option>
                        <option value="bonus">Fondsemisjon</option>
                        <option value="split">Aksjesplitt</option>
                    </flux:select>
                    @error('transaction_type') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Dato *</flux:label>
                    <flux:input wire:model="transaction_date" type="date" />
                    @error('transaction_date') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

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

                @if(in_array($transaction_type, ['transfer', 'redemption']))
                    <flux:field>
                        <flux:label>Fra aksjonær *</flux:label>
                        <flux:select wire:model="from_shareholder_id">
                            <option value="">Velg selger</option>
                            @foreach($shareholders as $sh)
                                <option value="{{ $sh->id }}">{{ $sh->name }}</option>
                            @endforeach
                        </flux:select>
                        @error('from_shareholder_id') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                @endif

                @if(in_array($transaction_type, ['issue', 'transfer', 'bonus']))
                    <flux:field>
                        <flux:label>Til aksjonær *</flux:label>
                        <flux:select wire:model="to_shareholder_id">
                            <option value="">Velg kjøper</option>
                            @foreach($shareholders as $sh)
                                <option value="{{ $sh->id }}">{{ $sh->name }}</option>
                            @endforeach
                        </flux:select>
                        @error('to_shareholder_id') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Antall aksjer *</flux:label>
                    <flux:input wire:model.live="number_of_shares" type="number" min="1" />
                    @error('number_of_shares') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Pris per aksje</flux:label>
                        <flux:input wire:model.live="price_per_share" type="number" step="0.0001" min="0" />
                        @error('price_per_share') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Totalbelop</flux:label>
                        <flux:input wire:model="total_amount" type="number" step="0.01" min="0" />
                        @error('total_amount') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:textarea wire:model="description" rows="2"></flux:textarea>
                    @error('description') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Dokumentreferanse</flux:label>
                    <flux:input wire:model="document_reference" type="text" placeholder="Protokoll, avtale, etc." />
                    @error('document_reference') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="save" variant="primary">Registrer</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
