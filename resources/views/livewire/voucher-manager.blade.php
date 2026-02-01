<div>
    {{-- Header with filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row flex-wrap gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk i bilag..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterStatus" class="w-full sm:w-40">
                <option value="">Alle statuser</option>
                <option value="unposted">Ikke bokført</option>
                <option value="posted">Bokført</option>
            </flux:select>
        </div>

        <flux:button wire:click="openModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Nytt bilag
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

    {{-- Vouchers table --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($vouchers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Bilag</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Beskrivelse</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Debet</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kredit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Handlinger</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($vouchers as $voucher)
                                <tr wire:key="voucher-{{ $voucher->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <flux:badge variant="outline">{{ $voucher->voucher_number }}</flux:badge>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $voucher->voucher_date->format('d.m.Y') }}</flux:text>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text class="text-zinc-900 dark:text-white">{{ $voucher->description }}</flux:text>
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $voucher->lines->count() }} linjer</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($voucher->is_posted)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                <flux:icon.check class="w-3 h-3 mr-1" />
                                                Bokført
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                                <flux:icon.clock class="w-3 h-3 mr-1" />
                                                Utkast
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-mono text-zinc-900 dark:text-white">{{ number_format($voucher->total_debit, 2, ',', ' ') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-mono text-zinc-900 dark:text-white">{{ number_format($voucher->total_credit, 2, ',', ' ') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            @if(!$voucher->is_posted)
                                                <flux:button wire:click="post({{ $voucher->id }})" wire:confirm="Bokfor bilaget? Dette kan ikke angres." variant="ghost" size="sm" title="Bokfor">
                                                    <flux:icon.check-circle class="w-4 h-4 text-green-600" />
                                                </flux:button>
                                                <flux:button wire:click="openModal({{ $voucher->id }})" variant="ghost" size="sm" title="Rediger">
                                                    <flux:icon.pencil class="w-4 h-4" />
                                                </flux:button>
                                                <flux:button wire:click="delete({{ $voucher->id }})" wire:confirm="Er du sikker på at du vil slette dette bilaget?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700" title="Slett">
                                                    <flux:icon.trash class="w-4 h-4" />
                                                </flux:button>
                                            @else
                                                <flux:button wire:click="openModal({{ $voucher->id }})" variant="ghost" size="sm" title="Vis">
                                                    <flux:icon.eye class="w-4 h-4" />
                                                </flux:button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                {{-- Expandable lines row --}}
                                <tr wire:key="voucher-lines-{{ $voucher->id }}" class="bg-zinc-50/50 dark:bg-zinc-800/30">
                                    <td colspan="6" class="px-6 py-2">
                                        <div class="text-xs">
                                            <table class="w-full">
                                                <tbody>
                                                    @foreach($voucher->lines as $line)
                                                        <tr wire:key="line-{{ $line->id }}">
                                                            <td class="py-1 pr-4 text-zinc-500 dark:text-zinc-400 font-mono">{{ $line->account->account_number }}</td>
                                                            <td class="py-1 pr-4 text-zinc-700 dark:text-zinc-300">{{ $line->account->name }}</td>
                                                            <td class="py-1 pr-4 text-zinc-500 dark:text-zinc-400">{{ $line->description }}</td>
                                                            @if($line->contact)
                                                                <td class="py-1 pr-4 text-blue-600 dark:text-blue-400">{{ $line->contact->company_name }}</td>
                                                            @else
                                                                <td class="py-1 pr-4"></td>
                                                            @endif
                                                            <td class="py-1 text-right font-mono text-zinc-700 dark:text-zinc-300 w-28">{{ $line->debit > 0 ? number_format($line->debit, 2, ',', ' ') : '' }}</td>
                                                            <td class="py-1 text-right font-mono text-zinc-700 dark:text-zinc-300 w-28">{{ $line->credit > 0 ? number_format($line->credit, 2, ',', ' ') : '' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $vouchers->links() }}</div>
            @else
                <div class="text-center py-12">
                    <flux:icon.document-text class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        @if($search || $filterStatus)
                            Ingen bilag funnet
                        @else
                            Ingen manuelle bilag ennå
                        @endif
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        @if($search || $filterStatus)
                            Prøv å endre søkekriteriene
                        @else
                            Kom i gang ved å opprette ditt første bilag
                        @endif
                    </flux:text>
                    @if(!$search && !$filterStatus)
                        <flux:button wire:click="openModal" variant="primary">
                            <flux:icon.plus class="w-5 h-5 mr-2" />
                            Opprett bilag
                        </flux:button>
                    @endif
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Voucher Modal --}}
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-3xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Rediger bilag' : 'Nytt bilag' }}</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ $editingId ? 'Oppdater bilagsinformasjon' : 'Opprett et nytt manuelt bilag' }}
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Bilagsdato *</flux:label>
                        <flux:input wire:model="voucher_date" type="date" />
                        @error('voucher_date')<flux:error>{{ $message }}</flux:error>@enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Beskrivelse *</flux:label>
                    <flux:input wire:model="description" type="text" placeholder="Beskriv bilaget..." />
                    @error('description')<flux:error>{{ $message }}</flux:error>@enderror
                </flux:field>
            </div>

            {{-- Lines Section --}}
            <flux:separator />
            <div>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="md">Bilagslinjer</flux:heading>
                    <flux:button wire:click="openLineModal" variant="ghost" size="sm">
                        <flux:icon.plus class="w-4 h-4 mr-1" />
                        Legg til linje
                    </flux:button>
                </div>

                @error('workingLines')
                    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <flux:text class="text-red-800 dark:text-red-200 text-sm">{{ $message }}</flux:text>
                    </div>
                @enderror

                @if(count($workingLines) > 0)
                    <div class="space-y-2 mb-4">
                        @foreach($workingLines as $index => $line)
                            <div wire:key="working-line-{{ $index }}" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                <div class="flex-1 grid grid-cols-12 gap-2 items-center">
                                    <div class="col-span-2">
                                        <flux:text class="font-mono text-sm text-zinc-900 dark:text-white">{{ $line['account_number'] }}</flux:text>
                                    </div>
                                    <div class="col-span-3">
                                        <flux:text class="text-sm text-zinc-700 dark:text-zinc-300">{{ $line['account_name'] }}</flux:text>
                                    </div>
                                    <div class="col-span-2">
                                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $line['description'] }}</flux:text>
                                    </div>
                                    <div class="col-span-2">
                                        @if($line['contact_name'])
                                            <flux:badge variant="outline" size="sm">{{ $line['contact_name'] }}</flux:badge>
                                        @endif
                                    </div>
                                    <div class="col-span-1 text-right">
                                        <flux:text class="font-mono text-sm {{ $line['debit'] > 0 ? 'text-zinc-900 dark:text-white' : 'text-zinc-400' }}">
                                            {{ $line['debit'] > 0 ? number_format($line['debit'], 2, ',', ' ') : '-' }}
                                        </flux:text>
                                    </div>
                                    <div class="col-span-1 text-right">
                                        <flux:text class="font-mono text-sm {{ $line['credit'] > 0 ? 'text-zinc-900 dark:text-white' : 'text-zinc-400' }}">
                                            {{ $line['credit'] > 0 ? number_format($line['credit'], 2, ',', ' ') : '-' }}
                                        </flux:text>
                                    </div>
                                    <div class="col-span-1 flex justify-end gap-1">
                                        <flux:button wire:click="openLineModal({{ $index }})" variant="ghost" size="sm">
                                            <flux:icon.pencil class="w-3 h-3" />
                                        </flux:button>
                                        <flux:button wire:click="removeLine({{ $index }})" variant="ghost" size="sm" class="text-red-600">
                                            <flux:icon.trash class="w-3 h-3" />
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Totals --}}
                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                        <div class="grid grid-cols-12 gap-2 items-center px-3">
                            <div class="col-span-9 text-right">
                                <flux:text class="font-medium text-zinc-700 dark:text-zinc-300">Sum:</flux:text>
                            </div>
                            <div class="col-span-1 text-right">
                                <flux:text class="font-mono font-medium text-zinc-900 dark:text-white">{{ number_format($this->totalDebit, 2, ',', ' ') }}</flux:text>
                            </div>
                            <div class="col-span-1 text-right">
                                <flux:text class="font-mono font-medium text-zinc-900 dark:text-white">{{ number_format($this->totalCredit, 2, ',', ' ') }}</flux:text>
                            </div>
                            <div class="col-span-1"></div>
                        </div>
                        @if($this->difference > 0.01)
                            <div class="grid grid-cols-12 gap-2 items-center px-3 mt-2">
                                <div class="col-span-9 text-right">
                                    <flux:text class="text-red-600 dark:text-red-400">Differanse:</flux:text>
                                </div>
                                <div class="col-span-2 text-right">
                                    <flux:text class="font-mono text-red-600 dark:text-red-400">{{ number_format($this->difference, 2, ',', ' ') }}</flux:text>
                                </div>
                                <div class="col-span-1"></div>
                            </div>
                        @else
                            <div class="grid grid-cols-12 gap-2 items-center px-3 mt-2">
                                <div class="col-span-11 text-right">
                                    <span class="inline-flex items-center text-green-600 dark:text-green-400 text-sm">
                                        <flux:icon.check-circle class="w-4 h-4 mr-1" />
                                        I balanse
                                    </span>
                                </div>
                                <div class="col-span-1"></div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:icon.document-plus class="h-10 w-10 text-zinc-400 mx-auto mb-2" />
                        <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen linjer ennå. Legg til minst 2 linjer.</flux:text>
                    </div>
                @endif
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="save" variant="primary" :disabled="!$this->isBalanced">
                    {{ $editingId ? 'Oppdater' : 'Opprett' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Line Modal --}}
    <flux:modal wire:model="showLineModal" variant="flyout" class="w-full max-w-md">
        <div class="space-y-6">
            <flux:heading size="lg">{{ $editingLineId !== null ? 'Rediger linje' : 'Ny linje' }}</flux:heading>
            <flux:separator />

            <div class="space-y-4">
                {{-- Account search --}}
                <flux:field>
                    <flux:label>Konto *</flux:label>
                    <flux:input wire:model.live.debounce.300ms="accountSearch" type="text" placeholder="Søk på kontonummer eller navn..." />
                    @error('line_account_id')<flux:error>{{ $message }}</flux:error>@enderror

                    @if($accountSearch && !$line_account_id)
                        <div class="mt-2 max-h-48 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg">
                            @forelse($this->accounts as $account)
                                <button wire:click="selectAccount({{ $account->id }})" type="button" class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-800 border-b border-zinc-100 dark:border-zinc-700 last:border-b-0">
                                    <span class="font-mono text-sm text-zinc-600 dark:text-zinc-400">{{ $account->account_number }}</span>
                                    <span class="ml-2 text-sm text-zinc-900 dark:text-white">{{ $account->name }}</span>
                                </button>
                            @empty
                                <div class="px-3 py-2 text-sm text-zinc-500">Ingen kontoer funnet</div>
                            @endforelse
                        </div>
                    @endif

                    @if($line_account_id)
                        <div class="mt-2 flex items-center gap-2">
                            <flux:badge variant="outline">Valgt: {{ $accountSearch }}</flux:badge>
                            <flux:button wire:click="$set('line_account_id', null)" variant="ghost" size="sm">
                                <flux:icon.x-mark class="w-3 h-3" />
                            </flux:button>
                        </div>
                    @endif
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:input wire:model="line_description" type="text" placeholder="Linjebeskrivelse..." />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Debet</flux:label>
                        <flux:input wire:model="line_debit" type="number" step="0.01" min="0" placeholder="0,00" />
                        @error('line_debit')<flux:error>{{ $message }}</flux:error>@enderror
                    </flux:field>
                    <flux:field>
                        <flux:label>Kredit</flux:label>
                        <flux:input wire:model="line_credit" type="number" step="0.01" min="0" placeholder="0,00" />
                        @error('line_credit')<flux:error>{{ $message }}</flux:error>@enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Kunde/Leverandør (reskontro)</flux:label>
                    <flux:select wire:model="line_contact_id">
                        <option value="">Ingen (kun hovedbok)</option>
                        @foreach($this->contacts as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->company_name }} ({{ $contact->type === 'supplier' ? 'Leverandør' : 'Kunde' }})</option>
                        @endforeach
                    </flux:select>
                    <flux:description>Velg kunde eller leverandør for å føre mot reskontro</flux:description>
                </flux:field>
            </div>

            <flux:separator />
            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeLineModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="saveLine" variant="primary">{{ $editingLineId !== null ? 'Oppdater' : 'Legg til' }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
