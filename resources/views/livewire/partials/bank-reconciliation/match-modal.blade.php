<flux:modal wire:model="showMatchModal" class="w-full max-w-2xl">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Match transaksjon manuelt</flux:heading>
            <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                Velg en faktura eller leverandørfaktura å matche mot
            </flux:text>
        </div>

        @if($selectedTransaction)
            {{-- Transaction info --}}
            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $selectedTransaction->transaction_date->format('d.m.Y') }}
                        </flux:text>
                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                            {{ $selectedTransaction->description }}
                        </flux:text>
                    </div>
                    <flux:text class="font-mono text-lg font-bold {{ $selectedTransaction->isCredit ? 'text-green-600' : 'text-red-600' }}">
                        {{ $selectedTransaction->formattedAmount }} kr
                    </flux:text>
                </div>
            </div>

            <flux:separator />

            {{-- Search --}}
            <flux:field>
                <flux:label>Søk</flux:label>
                <flux:input wire:model.live.debounce.300ms="matchSearch" type="text" placeholder="Søk på fakturanummer, kontakt..." />
            </flux:field>

            {{-- Suggestions --}}
            <div class="max-h-64 overflow-y-auto space-y-2">
                @forelse($this->matchSuggestions as $suggestion)
                    <button
                        wire:click="manualMatch('{{ $suggestion['type'] }}', {{ $suggestion['id'] }})"
                        type="button"
                        class="w-full p-4 text-left rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-indigo-500 dark:hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="font-medium text-zinc-900 dark:text-white">
                                    {{ $suggestion['label'] }}
                                </flux:text>
                                @if($suggestion['description'])
                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $suggestion['description'] }}
                                    </flux:text>
                                @endif
                                @if($suggestion['date'])
                                    <flux:text class="text-xs text-zinc-400">
                                        Forfall: {{ $suggestion['date']->format('d.m.Y') }}
                                    </flux:text>
                                @endif
                            </div>
                            <div class="text-right">
                                <flux:text class="font-mono font-medium text-zinc-900 dark:text-white">
                                    {{ number_format($suggestion['amount'], 2, ',', ' ') }} kr
                                </flux:text>
                                @if($suggestion['confidence'] >= 0.95)
                                    <flux:badge color="green" size="sm">Godt treff</flux:badge>
                                @elseif($suggestion['confidence'] >= 0.80)
                                    <flux:badge color="amber" size="sm">Mulig treff</flux:badge>
                                @endif
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="text-center py-8">
                        <flux:icon.document-magnifying-glass class="w-12 h-12 text-zinc-400 mx-auto mb-4" />
                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                            @if($matchSearch)
                                Ingen resultater for «{{ $matchSearch }}»
                            @else
                                Ingen åpne {{ $selectedTransaction->isCredit ? 'fakturaer' : 'leverandørfakturaer' }} funnet
                            @endif
                        </flux:text>
                    </div>
                @endforelse
            </div>
        @endif

        <flux:separator />

        <div class="flex justify-end gap-2">
            <flux:button wire:click="closeMatchModal" variant="ghost">Avbryt</flux:button>
        </div>
    </div>
</flux:modal>
