<div>
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Fakturaer</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">Administrer fakturaer og kreditnotaer</flux:text>
        </div>
        <flux:button variant="primary" wire:click="openModal" icon="plus">
            Ny faktura
        </flux:button>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mb-4">
            <flux:callout variant="success" icon="check-circle">
                {{ session('success') }}
            </flux:callout>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4">
            <flux:callout variant="danger" icon="x-circle">
                {{ session('error') }}
            </flux:callout>
        </div>
    @endif

    <!-- Filters -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Sok..." icon="magnifying-glass" />

        <flux:select wire:model.live="filterType">
            <flux:select.option value="all">Alle typer</flux:select.option>
            <flux:select.option value="invoices">Kun fakturaer</flux:select.option>
            <flux:select.option value="credit_notes">Kun kreditnotaer</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="filterStatus">
            <flux:select.option value="">Alle statuser</flux:select.option>
            @foreach ($statuses as $status)
                <flux:select.option value="{{ $status->id }}">{{ $status->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filterContact">
            <flux:select.option value="">Alle kunder</flux:select.option>
            @foreach ($contacts as $contact)
                <flux:select.option value="{{ $contact->id }}">{{ $contact->company_name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <!-- Table -->
    <flux:card>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nummer</flux:table.column>
                <flux:table.column>Tittel</flux:table.column>
                <flux:table.column>Kunde</flux:table.column>
                <flux:table.column>Dato</flux:table.column>
                <flux:table.column>Forfall</flux:table.column>
                <flux:table.column class="text-right">Total</flux:table.column>
                <flux:table.column class="text-right">Betalt</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($invoices as $invoice)
                    <flux:table.row wire:key="invoice-{{ $invoice->id }}">
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                @if ($invoice->is_credit_note)
                                    <flux:badge color="purple" size="sm">K</flux:badge>
                                @endif
                                <span class="font-medium">{{ $invoice->invoice_number }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $invoice->title }}</flux:table.cell>
                        <flux:table.cell>
                            {{ $invoice->contact?->company_name ?? $invoice->customer_name }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $invoice->invoice_date?->format('d.m.Y') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($invoice->due_date)
                                <span @class([
                                    'text-red-600 dark:text-red-400 font-medium' => $invoice->is_overdue,
                                ])>
                                    {{ $invoice->due_date->format('d.m.Y') }}
                                </span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-right font-medium">
                            kr {{ number_format($invoice->total, 2, ',', ' ') }}
                        </flux:table.cell>
                        <flux:table.cell class="text-right">
                            @if ($invoice->paid_amount > 0)
                                <span @class([
                                    'text-green-600 dark:text-green-400' => $invoice->balance == 0,
                                    'text-yellow-600 dark:text-yellow-400' => $invoice->balance > 0,
                                ])>
                                    kr {{ number_format($invoice->paid_amount, 2, ',', ' ') }}
                                </span>
                            @else
                                <span class="text-zinc-400">-</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($invoice->invoiceStatus)
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $this->getStatusColorClass($invoice->invoiceStatus->color) }}">
                                    {{ $invoice->invoiceStatus->name }}
                                </span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item wire:click="openModal({{ $invoice->id }})" icon="pencil">
                                        Rediger
                                    </flux:menu.item>
                                    <flux:menu.item onclick="event.preventDefault(); if(confirm('Send {{ $invoice->is_credit_note ? 'kreditnotaen' : 'fakturaen' }} til {{ $invoice->contact?->email ?? 'kunden' }}?')) { document.getElementById('send-invoice-{{ $invoice->id }}').submit(); }" icon="paper-airplane">
                                        {{ $invoice->sent_at ? 'Send pa nytt' : 'Send pa e-post' }}
                                    </flux:menu.item>
                                    <form id="send-invoice-{{ $invoice->id }}" action="{{ route('invoices.send', $invoice) }}" method="POST" class="hidden">@csrf</form>
                                    @if (!$invoice->is_credit_note)
                                        <flux:menu.item wire:click="createCreditNote({{ $invoice->id }})" icon="arrow-uturn-left">
                                            Opprett kreditnota
                                        </flux:menu.item>
                                    @endif
                                    <flux:menu.item href="{{ route('invoices.preview', $invoice) }}" icon="eye" target="_blank">
                                        Forhandsvis
                                    </flux:menu.item>
                                    <flux:menu.item href="{{ route('invoices.pdf', $invoice) }}" icon="document-arrow-down" target="_blank">
                                        Last ned PDF
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="delete({{ $invoice->id }})" wire:confirm="Er du sikker pa at du vil slette denne fakturaen?" icon="trash" variant="danger">
                                        Slett
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="text-center text-zinc-500 dark:text-zinc-400">
                            Ingen fakturaer funnet.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($invoices->hasPages())
            <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                {{ $invoices->links() }}
            </div>
        @endif
    </flux:card>

    <!-- Invoice Modal -->
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Rediger faktura' : 'Ny faktura' }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                    {{ $editingId ? 'Oppdater fakturainformasjon' : 'Opprett en ny faktura' }}
                </flux:text>
            </div>

            <form wire:submit="save" class="space-y-6">
                <!-- Basic Info -->
                <div class="space-y-4">
                    <flux:input wire:model="title" label="Tittel" placeholder="Fakturatittel" required />

                    <flux:textarea wire:model="description" label="Beskrivelse" placeholder="Valgfri beskrivelse" rows="2" />
                </div>

                <!-- Customer Selection -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:select wire:model.live="contact_id" label="Kunde" required>
                        <flux:select.option value="">Velg kunde</flux:select.option>
                        @foreach ($contacts as $contact)
                            <flux:select.option value="{{ $contact->id }}">{{ $contact->company_name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="project_id" label="Prosjekt">
                        <flux:select.option value="">Ingen prosjekt</flux:select.option>
                        @foreach ($projects as $project)
                            <flux:select.option value="{{ $project->id }}">{{ $project->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <!-- Status and Dates -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <flux:select wire:model="invoice_status_id" label="Status">
                        <flux:select.option value="">Velg status</flux:select.option>
                        @foreach ($statuses as $status)
                            <flux:select.option value="{{ $status->id }}">{{ $status->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="invoice_date" type="date" label="Fakturadato" />

                    <flux:input wire:model="due_date" type="date" label="Forfallsdato" />
                </div>

                <!-- Payment Terms -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input wire:model.live="payment_terms_days" type="number" label="Betalingsfrist (dager)" min="0" />

                    <flux:input wire:model="reminder_days" type="number" label="Purring etter (dager)" min="0" />
                </div>

                <!-- References -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input wire:model="our_reference" label="Var referanse" placeholder="Var referanse" />

                    <flux:input wire:model="customer_reference" label="Kundens referanse" placeholder="Kundens referanse" />
                </div>

                <!-- Customer Address -->
                <div class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm">Kundeadresse</flux:heading>

                    <flux:input wire:model="customer_name" label="Firmanavn" placeholder="Firmanavn" />

                    <flux:input wire:model="customer_address" label="Adresse" placeholder="Gateadresse" />

                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <flux:input wire:model="customer_postal_code" label="Postnr" placeholder="0000" />
                        <flux:input wire:model="customer_city" label="Sted" placeholder="Sted" />
                        <flux:input wire:model="customer_country" label="Land" placeholder="Norge" />
                    </div>
                </div>

                <!-- Lines Section (only when editing) -->
                @if ($currentInvoiceId)
                    <div class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <flux:heading size="sm">Fakturalinjer</flux:heading>
                            <flux:button wire:click="openLineModal" size="sm" variant="ghost" icon="plus">
                                Legg til linje
                            </flux:button>
                        </div>

                        @if (count($invoiceLines) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <thead>
                                        <tr class="text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            <th class="px-2 py-2">Beskrivelse</th>
                                            <th class="px-2 py-2 text-right">Antall</th>
                                            <th class="px-2 py-2 text-right">Pris</th>
                                            <th class="px-2 py-2 text-right">Sum</th>
                                            <th class="px-2 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach ($invoiceLines as $line)
                                            <tr wire:key="line-{{ $line['id'] }}">
                                                <td class="px-2 py-2 text-sm">{{ $line['description'] }}</td>
                                                <td class="px-2 py-2 text-right text-sm">{{ $line['quantity'] }} {{ $line['unit'] }}</td>
                                                <td class="px-2 py-2 text-right text-sm">kr {{ number_format($line['unit_price'], 2, ',', ' ') }}</td>
                                                <td class="px-2 py-2 text-right text-sm font-medium">
                                                    kr {{ number_format($line['quantity'] * $line['unit_price'] * (1 - ($line['discount_percent'] ?? 0) / 100), 2, ',', ' ') }}
                                                </td>
                                                <td class="px-2 py-2 text-right">
                                                    <div class="flex justify-end gap-1">
                                                        <flux:button wire:click="openLineModal({{ $line['id'] }})" size="xs" variant="ghost" icon="pencil" />
                                                        <flux:button wire:click="deleteLine({{ $line['id'] }})" wire:confirm="Slette denne linjen?" size="xs" variant="ghost" icon="trash" />
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <flux:text class="text-center text-zinc-500 dark:text-zinc-400">
                                Ingen linjer lagt til enna.
                            </flux:text>
                        @endif
                    </div>

                    <!-- Payments Section -->
                    <div class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <flux:heading size="sm">Betalinger</flux:heading>
                            <flux:button wire:click="openPaymentModal" size="sm" variant="ghost" icon="plus">
                                Registrer betaling
                            </flux:button>
                        </div>

                        @if (count($invoicePayments) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <thead>
                                        <tr class="text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            <th class="px-2 py-2">Dato</th>
                                            <th class="px-2 py-2">Metode</th>
                                            <th class="px-2 py-2">Referanse</th>
                                            <th class="px-2 py-2 text-right">Belop</th>
                                            <th class="px-2 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach ($invoicePayments as $payment)
                                            <tr wire:key="payment-{{ $payment['id'] }}">
                                                <td class="px-2 py-2 text-sm">
                                                    {{ \Carbon\Carbon::parse($payment['payment_date'])->format('d.m.Y') }}
                                                </td>
                                                <td class="px-2 py-2 text-sm">{{ $payment['payment_method']['name'] ?? '-' }}</td>
                                                <td class="px-2 py-2 text-sm">{{ $payment['reference'] ?? '-' }}</td>
                                                <td class="px-2 py-2 text-right text-sm font-medium text-green-600 dark:text-green-400">
                                                    kr {{ number_format($payment['amount'], 2, ',', ' ') }}
                                                </td>
                                                <td class="px-2 py-2 text-right">
                                                    <div class="flex justify-end gap-1">
                                                        <flux:button wire:click="openPaymentModal({{ $payment['id'] }})" size="xs" variant="ghost" icon="pencil" />
                                                        <flux:button wire:click="deletePayment({{ $payment['id'] }})" wire:confirm="Slette denne betalingen?" size="xs" variant="ghost" icon="trash" />
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <flux:text class="text-center text-zinc-500 dark:text-zinc-400">
                                Ingen betalinger registrert.
                            </flux:text>
                        @endif
                    </div>
                @endif

                <!-- Terms and Notes -->
                <div class="space-y-4">
                    <flux:textarea wire:model="terms_conditions" label="Betingelser" placeholder="Betalingsbetingelser og vilkar" rows="2" />

                    <flux:textarea wire:model="internal_notes" label="Interne notater" placeholder="Interne notater (vises ikke pa faktura)" rows="2" />
                </div>

                <!-- Active Toggle -->
                <flux:switch wire:model="is_active" label="Aktiv" description="Deaktiver for a skjule fakturaen" />

                <!-- Submit -->
                <div class="flex justify-end gap-3">
                    <flux:button wire:click="closeModal" variant="ghost">Avbryt</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? 'Oppdater' : 'Opprett' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Line Modal -->
    <flux:modal wire:model="showLineModal" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingLineId ? 'Rediger linje' : 'Ny linje' }}</flux:heading>
            </div>

            <form wire:submit="saveLine" class="space-y-4">
                <flux:select wire:model.live="line_product_id" label="Produkt">
                    <flux:select.option value="">Velg produkt (valgfritt)</flux:select.option>
                    @foreach ($products as $product)
                        <flux:select.option value="{{ $product->id }}">
                            {{ $product->name }} - kr {{ number_format($product->price, 2, ',', ' ') }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:textarea wire:model="line_description" label="Beskrivelse" rows="2" required />

                <div class="grid grid-cols-3 gap-4">
                    <flux:input wire:model="line_quantity" type="number" step="0.01" label="Antall" required />
                    <flux:input wire:model="line_unit" label="Enhet" placeholder="stk" required />
                    <flux:input wire:model="line_unit_price" type="number" step="0.01" label="Enhetspris" required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="line_discount_percent" type="number" step="0.01" min="0" max="100" label="Rabatt %" />

                    <flux:select wire:model.live="line_vat_rate_id" label="MVA-sats">
                        <flux:select.option value="">Velg MVA</flux:select.option>
                        @foreach ($vatRates as $vatRate)
                            <flux:select.option value="{{ $vatRate->id }}">
                                {{ $vatRate->name }} ({{ $vatRate->rate }}%)
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button wire:click="closeLineModal" variant="ghost">Avbryt</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingLineId ? 'Oppdater' : 'Legg til' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Payment Modal -->
    <flux:modal wire:model="showPaymentModal" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingPaymentId ? 'Rediger betaling' : 'Registrer betaling' }}</flux:heading>
            </div>

            <form wire:submit="savePayment" class="space-y-4">
                <flux:select wire:model="payment_method_id" label="Betalingsmate" required>
                    <flux:select.option value="">Velg betalingsmate</flux:select.option>
                    @foreach ($paymentMethods as $method)
                        <flux:select.option value="{{ $method->id }}">{{ $method->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="payment_date" type="date" label="Betalingsdato" required />
                    <flux:input wire:model="payment_amount" type="number" step="0.01" label="Belop" required />
                </div>

                <flux:input wire:model="payment_reference" label="Referanse" placeholder="Transaksjonsreferanse" />

                <flux:textarea wire:model="payment_notes" label="Notater" placeholder="Valgfrie notater" rows="2" />

                <div class="flex justify-end gap-3">
                    <flux:button wire:click="closePaymentModal" variant="ghost">Avbryt</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingPaymentId ? 'Oppdater' : 'Registrer' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
