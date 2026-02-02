<div wire:key="transaction-{{ $transaction->id }}" class="p-4 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 transition-colors">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        {{-- Transaction info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
                <flux:badge color="{{ $transaction->isCredit ? 'green' : 'red' }}" size="sm">
                    {{ $transaction->isCredit ? 'Inn' : 'Ut' }}
                </flux:badge>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $transaction->transaction_date->format('d.m.Y') }}
                </flux:text>
                @if($transaction->reference)
                    <flux:badge variant="outline" size="sm">KID: {{ $transaction->reference }}</flux:badge>
                @endif
            </div>
            <flux:text class="text-zinc-900 dark:text-white mb-1">
                {{ $transaction->description }}
            </flux:text>
            @if($transaction->counterparty_name)
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $transaction->counterparty_name }}
                </flux:text>
            @endif
        </div>

        {{-- Amount --}}
        <div class="text-right lg:text-left lg:w-32">
            <flux:text class="font-mono text-lg font-medium {{ $transaction->isCredit ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                {{ $transaction->formattedAmount }} kr
            </flux:text>
        </div>

        {{-- Match status / Draft status --}}
        <div class="lg:w-48">
            @if($transaction->match_status === 'auto_matched' || $transaction->match_status === 'manual_matched')
                <div class="flex items-center gap-2">
                    <flux:icon.check-circle class="w-5 h-5 text-green-500" />
                    <div>
                        <flux:badge color="{{ $transaction->matchStatusColor }}" size="sm">
                            {{ $transaction->matchStatusLabel }}
                        </flux:badge>
                        @if($transaction->match_confidence)
                            <flux:text class="text-xs text-zinc-500 mt-1">
                                {{ round($transaction->match_confidence * 100) }}% sikkerhet
                            </flux:text>
                        @endif
                    </div>
                </div>
            @elseif($transaction->draftVoucher)
                <div class="flex items-center gap-2">
                    <flux:icon.document-text class="w-5 h-5 text-blue-500" />
                    <div>
                        <flux:badge color="blue" size="sm">Kladd opprettet</flux:badge>
                        <flux:text class="text-xs text-zinc-500 mt-1">
                            {{ $transaction->draftVoucher->account?->account_number ?? 'Ingen konto' }}
                        </flux:text>
                    </div>
                </div>
            @else
                <flux:badge color="amber" size="sm">{{ $transaction->matchStatusLabel }}</flux:badge>
            @endif
        </div>

        {{-- Actions --}}
        @if($showActions ?? true)
            <div class="flex items-center gap-2">
                @if($transaction->match_status === 'unmatched')
                    {{-- Manual match --}}
                    <flux:button wire:click="openMatchModal({{ $transaction->id }})" variant="outline" size="sm" title="Match manuelt">
                        <flux:icon.link class="w-4 h-4" />
                    </flux:button>

                    {{-- Create draft voucher --}}
                    <flux:button wire:click="openDraftModal({{ $transaction->id }})" variant="outline" size="sm" title="Opprett kladd-bilag">
                        <flux:icon.document-plus class="w-4 h-4" />
                    </flux:button>

                    {{-- Ignore --}}
                    <flux:button wire:click="ignoreTransaction({{ $transaction->id }})" variant="ghost" size="sm" title="Ignorer">
                        <flux:icon.eye-slash class="w-4 h-4" />
                    </flux:button>
                @elseif($transaction->draftVoucher && !$transaction->draftVoucher->is_processed)
                    {{-- Edit draft --}}
                    <flux:button wire:click="openDraftModal({{ $transaction->id }})" variant="outline" size="sm" title="Rediger kladd">
                        <flux:icon.pencil class="w-4 h-4" />
                    </flux:button>

                    {{-- Delete draft --}}
                    <flux:button wire:click="deleteDraft({{ $transaction->id }})" variant="ghost" size="sm" class="text-red-600" title="Slett kladd">
                        <flux:icon.trash class="w-4 h-4" />
                    </flux:button>
                @elseif($transaction->match_status === 'auto_matched' || $transaction->match_status === 'manual_matched')
                    {{-- Unmatch --}}
                    <flux:button wire:click="unmatchTransaction({{ $transaction->id }})" variant="ghost" size="sm" title="Fjern match">
                        <flux:icon.x-mark class="w-4 h-4" />
                    </flux:button>
                @endif
            </div>
        @endif
    </div>

    {{-- Match details if matched --}}
    @if(($transaction->match_status === 'auto_matched' || $transaction->match_status === 'manual_matched') && $transaction->confirmedMatch)
        <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
            <div class="flex items-center gap-2 text-sm">
                <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    Matchet med:
                    @php
                        $matchable = $transaction->confirmedMatch->matchable;
                        $matchType = class_basename($matchable);
                    @endphp
                    @if($matchType === 'Invoice')
                        <span class="font-medium text-zinc-900 dark:text-white">Faktura {{ $matchable->invoice_number }}</span>
                        @if($matchable->contact)
                            <span class="text-zinc-500">- {{ $matchable->contact->company_name }}</span>
                        @endif
                    @elseif($matchType === 'SupplierInvoice')
                        <span class="font-medium text-zinc-900 dark:text-white">Leverandorfaktura {{ $matchable->invoice_number }}</span>
                        @if($matchable->contact)
                            <span class="text-zinc-500">- {{ $matchable->contact->company_name }}</span>
                        @endif
                    @elseif($matchType === 'Voucher')
                        <span class="font-medium text-zinc-900 dark:text-white">Bilag {{ $matchable->voucher_number }}</span>
                    @else
                        <span class="font-medium text-zinc-900 dark:text-white">{{ $matchType }} #{{ $matchable->id }}</span>
                    @endif
                </flux:text>
            </div>
        </div>
    @endif
</div>
