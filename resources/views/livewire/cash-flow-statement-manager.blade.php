<div>
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

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Drift</flux:text>
                <flux:heading size="xl" class="mt-2 {{ $totals['net_operating'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ number_format($totals['net_operating'], 0, ',', ' ') }} kr
                </flux:heading>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Investering</flux:text>
                <flux:heading size="xl" class="mt-2 {{ $totals['net_investing'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ number_format($totals['net_investing'], 0, ',', ' ') }} kr
                </flux:heading>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Finansiering</flux:text>
                <flux:heading size="xl" class="mt-2 {{ $totals['net_financing'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ number_format($totals['net_financing'], 0, ',', ' ') }} kr
                </flux:heading>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Netto endring</flux:text>
                <flux:heading size="xl" class="mt-2 {{ $totals['net_change'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ number_format($totals['net_change'], 0, ',', ' ') }} kr
                </flux:heading>
            </div>
        </flux:card>
    </div>

    {{-- Cash flow statement --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <flux:heading size="lg">Kontantstrømoppstilling</flux:heading>
                @if($annualAccount->canBeEdited())
                    <flux:button wire:click="openModal" variant="primary">
                        <flux:icon.pencil class="w-4 h-4 mr-2" />
                        Rediger
                    </flux:button>
                @endif
            </div>

            <div class="space-y-6">
                {{-- Operasjonelle aktiviteter --}}
                <div>
                    <flux:heading size="sm" class="mb-3">Kontantstrøm fra operasjonelle aktiviteter</flux:heading>
                    <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg overflow-hidden">
                        <table class="min-w-full">
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                <tr>
                                    <td class="px-4 py-2">Resultat før skatt</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($profit_before_tax, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Betalt skatt</td>
                                    <td class="px-4 py-2 text-right text-red-600">-{{ number_format($tax_paid, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Avskrivninger</td>
                                    <td class="px-4 py-2 text-right text-green-600">+{{ number_format($depreciation, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Endring i varelager</td>
                                    <td class="px-4 py-2 text-right {{ $change_in_inventory <= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format(-$change_in_inventory, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Endring i kundefordringer</td>
                                    <td class="px-4 py-2 text-right {{ $change_in_receivables <= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format(-$change_in_receivables, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Endring i leverandørgjeld</td>
                                    <td class="px-4 py-2 text-right {{ $change_in_payables >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($change_in_payables, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr class="bg-zinc-100 dark:bg-zinc-700 font-semibold">
                                    <td class="px-4 py-2">Netto kontantstrøm fra drift</td>
                                    <td class="px-4 py-2 text-right {{ $totals['net_operating'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($totals['net_operating'], 0, ',', ' ') }} kr</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Investeringsaktiviteter --}}
                <div>
                    <flux:heading size="sm" class="mb-3">Kontantstrøm fra investeringsaktiviteter</flux:heading>
                    <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg overflow-hidden">
                        <table class="min-w-full">
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                <tr>
                                    <td class="px-4 py-2">Kjøp av driftsmidler</td>
                                    <td class="px-4 py-2 text-right text-red-600">-{{ number_format($purchase_of_fixed_assets, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Salg av driftsmidler</td>
                                    <td class="px-4 py-2 text-right text-green-600">+{{ number_format($sale_of_fixed_assets, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Kjøp av investeringer</td>
                                    <td class="px-4 py-2 text-right text-red-600">-{{ number_format($purchase_of_investments, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Salg av investeringer</td>
                                    <td class="px-4 py-2 text-right text-green-600">+{{ number_format($sale_of_investments, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr class="bg-zinc-100 dark:bg-zinc-700 font-semibold">
                                    <td class="px-4 py-2">Netto kontantstrøm fra investering</td>
                                    <td class="px-4 py-2 text-right {{ $totals['net_investing'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($totals['net_investing'], 0, ',', ' ') }} kr</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Finansieringsaktiviteter --}}
                <div>
                    <flux:heading size="sm" class="mb-3">Kontantstrøm fra finansieringsaktiviteter</flux:heading>
                    <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg overflow-hidden">
                        <table class="min-w-full">
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                <tr>
                                    <td class="px-4 py-2">Opptak av lån</td>
                                    <td class="px-4 py-2 text-right text-green-600">+{{ number_format($proceeds_from_borrowings, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Nedbetaling av lån</td>
                                    <td class="px-4 py-2 text-right text-red-600">-{{ number_format($repayment_of_borrowings, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Kapitalforhøyelse</td>
                                    <td class="px-4 py-2 text-right text-green-600">+{{ number_format($share_capital_increase, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Utbetalt utbytte</td>
                                    <td class="px-4 py-2 text-right text-red-600">-{{ number_format($dividends_paid, 0, ',', ' ') }} kr</td>
                                </tr>
                                <tr class="bg-zinc-100 dark:bg-zinc-700 font-semibold">
                                    <td class="px-4 py-2">Netto kontantstrøm fra finansiering</td>
                                    <td class="px-4 py-2 text-right {{ $totals['net_financing'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($totals['net_financing'], 0, ',', ' ') }} kr</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Netto endring --}}
                <div class="bg-zinc-100 dark:bg-zinc-700 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <flux:text class="font-medium">Netto endring i kontanter</flux:text>
                        <flux:text class="font-semibold {{ $totals['net_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($totals['net_change'], 0, ',', ' ') }} kr</flux:text>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <flux:text>Kontanter IB</flux:text>
                        <flux:text>{{ number_format($opening_cash_balance, 0, ',', ' ') }} kr</flux:text>
                    </div>
                    <flux:separator class="my-2" />
                    <div class="flex justify-between items-center">
                        <flux:text class="font-semibold">Kontanter UB</flux:text>
                        <flux:text class="font-semibold">{{ number_format($totals['closing_balance'], 0, ',', ' ') }} kr</flux:text>
                    </div>
                </div>
            </div>
        </div>
    </flux:card>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-2xl overflow-y-auto">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    Rediger kontantstrømoppstilling
                </flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    Oppdater kontantstrømdata
                </flux:text>
            </div>

            <flux:separator />

            {{-- Operasjonelle aktiviteter --}}
            <div class="space-y-4">
                <flux:heading size="sm">Operasjonelle aktiviteter</flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Resultat før skatt</flux:label>
                        <flux:input wire:model.live="profit_before_tax" type="number" step="0.01" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Betalt skatt</flux:label>
                        <flux:input wire:model.live="tax_paid" type="number" step="0.01" min="0" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Avskrivninger</flux:label>
                        <flux:input wire:model.live="depreciation" type="number" step="0.01" min="0" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Endring varelager</flux:label>
                        <flux:input wire:model.live="change_in_inventory" type="number" step="0.01" />
                        <flux:description>Positiv = økning</flux:description>
                    </flux:field>
                    <flux:field>
                        <flux:label>Endring kundefordringer</flux:label>
                        <flux:input wire:model.live="change_in_receivables" type="number" step="0.01" />
                        <flux:description>Positiv = økning</flux:description>
                    </flux:field>
                    <flux:field>
                        <flux:label>Endring leverandørgjeld</flux:label>
                        <flux:input wire:model.live="change_in_payables" type="number" step="0.01" />
                        <flux:description>Positiv = økning</flux:description>
                    </flux:field>
                </div>
            </div>

            <flux:separator />

            {{-- Investeringsaktiviteter --}}
            <div class="space-y-4">
                <flux:heading size="sm">Investeringsaktiviteter</flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Kjøp av driftsmidler</flux:label>
                        <flux:input wire:model.live="purchase_of_fixed_assets" type="number" step="0.01" min="0" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Salg av driftsmidler</flux:label>
                        <flux:input wire:model.live="sale_of_fixed_assets" type="number" step="0.01" min="0" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Kjøp av investeringer</flux:label>
                        <flux:input wire:model.live="purchase_of_investments" type="number" step="0.01" min="0" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Salg av investeringer</flux:label>
                        <flux:input wire:model.live="sale_of_investments" type="number" step="0.01" min="0" />
                    </flux:field>
                </div>
            </div>

            <flux:separator />

            {{-- Finansieringsaktiviteter --}}
            <div class="space-y-4">
                <flux:heading size="sm">Finansieringsaktiviteter</flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Opptak av lån</flux:label>
                        <flux:input wire:model.live="proceeds_from_borrowings" type="number" step="0.01" min="0" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Nedbetaling av lån</flux:label>
                        <flux:input wire:model.live="repayment_of_borrowings" type="number" step="0.01" min="0" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Kapitalforhøyelse</flux:label>
                        <flux:input wire:model.live="share_capital_increase" type="number" step="0.01" min="0" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Utbetalt utbytte</flux:label>
                        <flux:input wire:model.live="dividends_paid" type="number" step="0.01" min="0" />
                    </flux:field>
                </div>
            </div>

            <flux:separator />

            {{-- Kontanter --}}
            <div class="space-y-4">
                <flux:heading size="sm">Kontantbeholdning</flux:heading>
                <flux:field>
                    <flux:label>Kontanter IB</flux:label>
                    <flux:input wire:model.live="opening_cash_balance" type="number" step="0.01" min="0" />
                </flux:field>
            </div>

            {{-- Live totals --}}
            <div class="bg-zinc-100 dark:bg-zinc-700 rounded-lg p-4 space-y-2">
                <div class="flex justify-between">
                    <flux:text>Netto drift</flux:text>
                    <flux:text class="{{ $totals['net_operating'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($totals['net_operating'], 0, ',', ' ') }} kr</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text>Netto investering</flux:text>
                    <flux:text class="{{ $totals['net_investing'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($totals['net_investing'], 0, ',', ' ') }} kr</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text>Netto finansiering</flux:text>
                    <flux:text class="{{ $totals['net_financing'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($totals['net_financing'], 0, ',', ' ') }} kr</flux:text>
                </div>
                <flux:separator />
                <div class="flex justify-between font-semibold">
                    <flux:text>Kontanter UB</flux:text>
                    <flux:text>{{ number_format($totals['closing_balance'], 0, ',', ' ') }} kr</flux:text>
                </div>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeModal" variant="ghost">
                    Avbryt
                </flux:button>
                <flux:button wire:click="save" variant="primary">
                    Lagre
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
