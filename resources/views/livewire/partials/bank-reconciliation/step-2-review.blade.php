<div>
    {{-- Statement info --}}
    @if($statement)
        <div class="mb-6 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <flux:icon.document-text class="w-10 h-10 text-indigo-500" />
                    <div>
                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                            {{ $statement->original_filename }}
                        </flux:text>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $statement->reference_number }}
                            @if($statement->bank_name)
                                &middot; {{ $statement->bank_name }}
                            @endif
                            @if($statement->from_date && $statement->to_date)
                                &middot; {{ $statement->from_date->format('d.m.Y') }} - {{ $statement->to_date->format('d.m.Y') }}
                            @endif
                        </flux:text>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <flux:text class="text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ $statement->transaction_count }}
                        </flux:text>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">transaksjoner</flux:text>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Transactions preview --}}
    <div class="mb-6">
        <flux:heading size="md" class="text-zinc-900 dark:text-white mb-4">
            Importerte transaksjoner
        </flux:heading>

        @if($transactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Dato</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Beskrivelse</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Referanse</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Beløp</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($transactions->take(10) as $transaction)
                            <tr wire:key="preview-{{ $transaction->id }}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <flux:text class="text-sm text-zinc-900 dark:text-white">
                                        {{ $transaction->transaction_date->format('d.m.Y') }}
                                    </flux:text>
                                </td>
                                <td class="px-4 py-3">
                                    <flux:text class="text-sm text-zinc-900 dark:text-white truncate max-w-xs">
                                        {{ $transaction->description }}
                                    </flux:text>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($transaction->reference)
                                        <flux:badge variant="outline" size="sm">{{ $transaction->reference }}</flux:badge>
                                    @else
                                        <flux:text class="text-sm text-zinc-400">-</flux:text>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <flux:text class="font-mono text-sm {{ $transaction->isCredit ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $transaction->formattedAmount }} kr
                                    </flux:text>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($transactions->count() > 10)
                <div class="mt-4 text-center">
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        Viser 10 av {{ $transactions->count() }} transaksjoner
                    </flux:text>
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen transaksjoner funnet</flux:text>
            </div>
        @endif
    </div>

    {{-- Info about auto-matching --}}
    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg mb-6">
        <div class="flex items-start gap-3">
            <flux:icon.sparkles class="w-6 h-6 text-blue-500 flex-shrink-0 mt-0.5" />
            <div>
                <flux:text class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-1">
                    Automatisk matching
                </flux:text>
                <flux:text class="text-sm text-blue-700 dark:text-blue-300">
                    Systemet vil forsøke å matche transaksjoner mot eksisterende bilag, fakturaer og leverandørfakturaer basert på beløp, dato og KID-referanser.
                </flux:text>
            </div>
        </div>
    </div>

    {{-- Navigation buttons --}}
    <div class="flex justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
        <flux:button wire:click="goToStep(1)" variant="ghost" disabled>
            <flux:icon.arrow-left class="w-4 h-4 mr-2" />
            Tilbake
        </flux:button>
        <flux:button wire:click="runMatching" variant="primary" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="runMatching">
                <flux:icon.sparkles class="w-4 h-4 mr-2" />
                Start auto-matching
            </span>
            <span wire:loading wire:target="runMatching">
                <flux:icon.arrow-path class="w-4 h-4 mr-2 animate-spin" />
                Matcher...
            </span>
        </flux:button>
    </div>
</div>
