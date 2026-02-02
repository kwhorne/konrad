<div>
    {{-- Completion status --}}
    @if($statement && $statement->status === 'finalized')
        <div class="text-center py-12">
            <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                <flux:icon.check-circle class="w-12 h-12 text-green-500" />
            </div>
            <flux:heading size="xl" class="text-zinc-900 dark:text-white mb-2">
                Avstemming fullført!
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                Bankavstemming {{ $statement->reference_number }} er ferdig.
            </flux:text>

            <div class="flex justify-center gap-4">
                <flux:button href="{{ route('economy.bank-reconciliation') }}" variant="outline">
                    <flux:icon.plus class="w-4 h-4 mr-2" />
                    Ny avstemming
                </flux:button>
                <flux:button href="{{ route('economy.vouchers') }}" variant="primary">
                    <flux:icon.document-text class="w-4 h-4 mr-2" />
                    Gå til bilag
                </flux:button>
            </div>
        </div>
    @else
        {{-- Summary --}}
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-8">
                <flux:icon.clipboard-document-check class="h-16 w-16 text-indigo-500 mx-auto mb-4" />
                <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-2">
                    Oppsummering
                </flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    Gjennomgå og bekreft avstemmingen
                </flux:text>
            </div>

            @if($this->statistics)
                {{-- Statistics summary --}}
                <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-6 mb-6">
                    <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 mb-4">
                        Transaksjonsstatistikk
                    </flux:heading>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-600 dark:text-zinc-400">Totalt antall</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $this->statistics['total_transactions'] }}</flux:text>
                        </div>
                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-600 dark:text-zinc-400">Matchet</flux:text>
                            <flux:text class="font-medium text-green-600 dark:text-green-400">{{ $this->statistics['matched_count'] }}</flux:text>
                        </div>
                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-600 dark:text-zinc-400">Ignorert</flux:text>
                            <flux:text class="font-medium text-zinc-600 dark:text-zinc-400">{{ $this->statistics['ignored_count'] }}</flux:text>
                        </div>
                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-600 dark:text-zinc-400">Umatchet</flux:text>
                            <flux:text class="font-medium {{ $this->statistics['unmatched_count'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ $this->statistics['unmatched_count'] }}
                            </flux:text>
                        </div>
                    </div>
                </div>

                {{-- Financial summary --}}
                <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-6 mb-6">
                    <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 mb-4">
                        Finansiell oppsummering
                    </flux:heading>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-600 dark:text-zinc-400">Innbetalinger</flux:text>
                            <flux:text class="font-mono font-medium text-green-600 dark:text-green-400">
                                +{{ number_format($this->statistics['total_in'], 2, ',', ' ') }} kr
                            </flux:text>
                        </div>
                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-600 dark:text-zinc-400">Utbetalinger</flux:text>
                            <flux:text class="font-mono font-medium text-red-600 dark:text-red-400">
                                -{{ number_format($this->statistics['total_out'], 2, ',', ' ') }} kr
                            </flux:text>
                        </div>
                        <div class="flex justify-between py-2">
                            <flux:text class="font-medium text-zinc-900 dark:text-white">Netto endring</flux:text>
                            <flux:text class="font-mono font-bold {{ $this->statistics['net_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $this->statistics['net_change'] >= 0 ? '+' : '' }}{{ number_format($this->statistics['net_change'], 2, ',', ' ') }} kr
                            </flux:text>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Warning if there are unmatched --}}
            @if($this->statistics && $this->statistics['unmatched_count'] > 0)
                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg mb-6">
                    <div class="flex items-start gap-3">
                        <flux:icon.exclamation-triangle class="w-6 h-6 text-amber-500 flex-shrink-0" />
                        <div>
                            <flux:text class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                Det gjenstår {{ $this->statistics['unmatched_count'] }} umatchede transaksjoner
                            </flux:text>
                            <flux:text class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                Du må håndtere alle transaksjoner før du kan fullføre avstemmingen.
                            </flux:text>
                            <flux:button wire:click="goToStep(3)" variant="ghost" size="sm" class="mt-2">
                                <flux:icon.arrow-left class="w-4 h-4 mr-1" />
                                Gå tilbake
                            </flux:button>
                        </div>
                    </div>
                </div>
            @else
                {{-- Success message --}}
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg mb-6">
                    <div class="flex items-start gap-3">
                        <flux:icon.check-circle class="w-6 h-6 text-green-500 flex-shrink-0" />
                        <div>
                            <flux:text class="text-sm font-medium text-green-800 dark:text-green-200">
                                Klar til å fullføre
                            </flux:text>
                            <flux:text class="text-sm text-green-700 dark:text-green-300 mt-1">
                                Alle transaksjoner er matchet eller håndtert. Klikk "Fullfør avstemming" for å bokføre eventuelle kladd-bilag og markere avstemmingen som ferdig.
                            </flux:text>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Navigation buttons --}}
            <div class="flex justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button wire:click="goToStep(3)" variant="ghost">
                    <flux:icon.arrow-left class="w-4 h-4 mr-2" />
                    Tilbake
                </flux:button>
                <flux:button
                    wire:click="finalizeReconciliation"
                    variant="primary"
                    wire:loading.attr="disabled"
                    :disabled="$this->statistics && $this->statistics['unmatched_count'] > 0"
                >
                    <span wire:loading.remove wire:target="finalizeReconciliation">
                        <flux:icon.check class="w-4 h-4 mr-2" />
                        Fullfør avstemming
                    </span>
                    <span wire:loading wire:target="finalizeReconciliation">
                        <flux:icon.arrow-path class="w-4 h-4 mr-2 animate-spin" />
                        Fullfører...
                    </span>
                </flux:button>
            </div>
        </div>
    @endif
</div>
