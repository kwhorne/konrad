<flux:modal wire:model="showDraftModal" class="w-full max-w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Opprett kladd-bilag</flux:heading>
            <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                Opprett et nytt bilag for denne transaksjonen
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

            <div class="space-y-4">
                {{-- Voucher type --}}
                <flux:field>
                    <flux:label>Bilagstype</flux:label>
                    <flux:select wire:model="draftVoucherType">
                        @if($selectedTransaction->isCredit)
                            <option value="payment">Innbetaling</option>
                        @else
                            <option value="supplier_payment">Leverandorbetaling</option>
                        @endif
                        <option value="manual">Manuelt bilag</option>
                    </flux:select>
                </flux:field>

                {{-- Description --}}
                <flux:field>
                    <flux:label>Beskrivelse *</flux:label>
                    <flux:input wire:model="draftDescription" type="text" placeholder="Beskrivelse av transaksjonen" />
                    @error('draftDescription')<flux:error>{{ $message }}</flux:error>@enderror
                </flux:field>

                {{-- Account --}}
                <flux:field>
                    <flux:label>
                        @if($selectedTransaction->isCredit)
                            Inntektskonto *
                        @else
                            Kostnadskonto *
                        @endif
                    </flux:label>
                    <flux:select wire:model="draftAccountId">
                        <option value="">Velg konto...</option>
                        @if($selectedTransaction->isCredit)
                            @foreach($this->incomeAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_number }} - {{ $account->name }}</option>
                            @endforeach
                        @else
                            @foreach($this->expenseAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_number }} - {{ $account->name }}</option>
                            @endforeach
                        @endif
                        <optgroup label="Alle kontoer">
                            @foreach($this->allAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_number }} - {{ $account->name }}</option>
                            @endforeach
                        </optgroup>
                    </flux:select>
                    @error('draftAccountId')<flux:error>{{ $message }}</flux:error>@enderror
                </flux:field>

                {{-- Amount --}}
                <flux:field>
                    <flux:label>Beløp *</flux:label>
                    <flux:input wire:model="draftAmount" type="number" step="0.01" />
                    @error('draftAmount')<flux:error>{{ $message }}</flux:error>@enderror
                </flux:field>
            </div>
        @endif

        <flux:separator />

        <div class="flex justify-end gap-2">
            <flux:button wire:click="closeDraftModal" variant="ghost">Avbryt</flux:button>
            <flux:button wire:click="saveDraft" variant="primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveDraft">
                    <flux:icon.document-plus class="w-4 h-4 mr-2" />
                    @if($selectedTransaction?->draftVoucher)
                        Oppdater kladd
                    @else
                        Opprett kladd
                    @endif
                </span>
                <span wire:loading wire:target="saveDraft">
                    <flux:icon.arrow-path class="w-4 h-4 mr-2 animate-spin" />
                    Lagrer...
                </span>
            </flux:button>
        </div>
    </div>
</flux:modal>
