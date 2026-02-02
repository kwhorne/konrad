<div>
    {{-- Statistics summary --}}
    @if($this->statistics)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg text-center">
                <flux:text class="text-2xl font-bold text-zinc-900 dark:text-white">
                    {{ $this->statistics['total_transactions'] }}
                </flux:text>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Totalt</flux:text>
            </div>
            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg text-center">
                <flux:text class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ $this->statistics['matched_count'] }}
                </flux:text>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Matchet</flux:text>
            </div>
            <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg text-center">
                <flux:text class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                    {{ $this->statistics['unmatched_count'] }}
                </flux:text>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Umatchet</flux:text>
            </div>
            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg text-center">
                <flux:text class="text-2xl font-bold text-zinc-900 dark:text-white">
                    {{ $this->statistics['matched_percent'] }}%
                </flux:text>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Ferdig</flux:text>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="mb-6">
            <div class="h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                <div class="h-full bg-green-500 transition-all duration-300" style="width: {{ $this->statistics['matched_percent'] }}%"></div>
            </div>
        </div>
    @endif

    {{-- Tabs for transaction types --}}
    <div x-data="{ activeTab: 'unmatched' }" class="space-y-4">
        <div class="flex gap-2 border-b border-zinc-200 dark:border-zinc-700">
            <button @click="activeTab = 'unmatched'" :class="activeTab === 'unmatched' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700'"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                Umatchede ({{ $unmatchedTransactions->count() }})
            </button>
            <button @click="activeTab = 'matched'" :class="activeTab === 'matched' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700'"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                Matchede ({{ $matchedTransactions->count() }})
            </button>
            <button @click="activeTab = 'ignored'" :class="activeTab === 'ignored' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700'"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                Ignorerte ({{ $ignoredTransactions->count() }})
            </button>
        </div>

        {{-- Unmatched transactions --}}
        <div x-show="activeTab === 'unmatched'" x-cloak>
            @if($unmatchedTransactions->count() > 0)
                <div class="space-y-2">
                    @foreach($unmatchedTransactions as $transaction)
                        @include('livewire.partials.bank-reconciliation.transaction-row', ['transaction' => $transaction, 'showActions' => true])
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.check-circle class="w-16 h-16 text-green-500 mx-auto mb-4" />
                    <flux:heading size="md" class="text-zinc-900 dark:text-white mb-2">
                        Alle transaksjoner er håndtert!
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        Du kan nå gå videre til neste steg for å fullføre avstemmingen.
                    </flux:text>
                </div>
            @endif
        </div>

        {{-- Matched transactions --}}
        <div x-show="activeTab === 'matched'" x-cloak>
            @if($matchedTransactions->count() > 0)
                <div class="space-y-2">
                    @foreach($matchedTransactions as $transaction)
                        @include('livewire.partials.bank-reconciliation.transaction-row', ['transaction' => $transaction, 'showActions' => false])
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen matchede transaksjoner enna</flux:text>
                </div>
            @endif
        </div>

        {{-- Ignored transactions --}}
        <div x-show="activeTab === 'ignored'" x-cloak>
            @if($ignoredTransactions->count() > 0)
                <div class="space-y-2">
                    @foreach($ignoredTransactions as $transaction)
                        <div wire:key="ignored-{{ $transaction->id }}" class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $transaction->transaction_date->format('d.m.Y') }}
                                    </flux:text>
                                    <flux:text class="text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $transaction->description }}
                                    </flux:text>
                                    <flux:text class="font-mono text-sm {{ $transaction->isCredit ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->formattedAmount }} kr
                                    </flux:text>
                                </div>
                                <flux:button wire:click="unignoreTransaction({{ $transaction->id }})" variant="ghost" size="sm">
                                    <flux:icon.arrow-uturn-left class="w-4 h-4 mr-1" />
                                    Angre
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen ignorerte transaksjoner</flux:text>
                </div>
            @endif
        </div>
    </div>

    {{-- Navigation buttons --}}
    <div class="flex justify-between pt-6 mt-6 border-t border-zinc-200 dark:border-zinc-700">
        <flux:button wire:click="goToStep(2)" variant="ghost">
            <flux:icon.arrow-left class="w-4 h-4 mr-2" />
            Tilbake
        </flux:button>
        <div class="flex gap-2">
            @if($unmatchedTransactions->whereNull('draftVoucher')->count() === 0 && $unmatchedTransactions->count() > 0)
                <flux:button wire:click="processAllDrafts" variant="outline">
                    <flux:icon.document-plus class="w-4 h-4 mr-2" />
                    Opprett alle kladd-bilag
                </flux:button>
            @endif
            <flux:button wire:click="goToStep(4)" variant="primary" :disabled="$unmatchedTransactions->count() > 0">
                <flux:icon.arrow-right class="w-4 h-4 mr-2" />
                Gå til oppsummering
            </flux:button>
        </div>
    </div>
</div>
