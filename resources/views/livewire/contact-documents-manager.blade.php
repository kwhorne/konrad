<div>
    <flux:tab.group>
        <flux:tabs>
            <flux:tab name="quotes" icon="document-text">
                Tilbud
                @if($this->quotes->count() > 0)
                    <flux:badge size="sm" color="zinc" class="ml-1">{{ $this->quotes->count() }}</flux:badge>
                @endif
            </flux:tab>
            <flux:tab name="orders" icon="shopping-cart">
                Ordrer
                @if($this->orders->count() > 0)
                    <flux:badge size="sm" color="zinc" class="ml-1">{{ $this->orders->count() }}</flux:badge>
                @endif
            </flux:tab>
            <flux:tab name="invoices" icon="banknotes">
                Fakturaer
                @if($this->invoices->count() > 0)
                    <flux:badge size="sm" color="zinc" class="ml-1">{{ $this->invoices->count() }}</flux:badge>
                @endif
            </flux:tab>
        </flux:tabs>

        {{-- Quotes Tab --}}
        <flux:tab.panel name="quotes" class="pt-4">
            <div class="flex justify-end mb-4">
                <flux:button href="{{ route('quotes.create', ['contact_id' => $contactId]) }}" variant="primary" size="sm" icon="plus">
                    Nytt tilbud
                </flux:button>
            </div>
            @if($this->quotes->count() > 0)
                <div class="overflow-x-auto">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Nr</flux:table.column>
                            <flux:table.column>Tittel</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Dato</flux:table.column>
                            <flux:table.column class="text-right">Belop</flux:table.column>
                            <flux:table.column class="text-right"></flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($this->quotes as $quote)
                                <flux:table.row wire:key="quote-{{ $quote->id }}" class="cursor-pointer" wire:click="showDetail('quote', {{ $quote->id }})">
                                    <flux:table.cell>
                                        <flux:badge variant="outline" size="sm">{{ $quote->quote_number }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $quote->title ?: '-' }}</flux:table.cell>
                                    <flux:table.cell>
                                        @if($quote->quoteStatus)
                                            <flux:badge size="sm" :color="$quote->quoteStatus->color ?? 'zinc'">
                                                {{ $quote->quoteStatus->name }}
                                            </flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">{{ $quote->quote_date?->format('d.m.Y') }}</flux:table.cell>
                                    <flux:table.cell class="text-right font-medium">{{ number_format($quote->total, 2, ',', ' ') }} kr</flux:table.cell>
                                    <flux:table.cell class="text-right">
                                        <a href="{{ route('quotes.preview', $quote) }}" target="_blank" wire:click.stop>
                                            <flux:button variant="ghost" size="xs" icon="eye" />
                                        </a>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @else
                <div class="text-center py-8">
                    <flux:icon.document-text class="w-12 h-12 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" />
                    <flux:text class="text-zinc-500">Ingen tilbud for denne kontakten</flux:text>
                </div>
            @endif
        </flux:tab.panel>

        {{-- Orders Tab --}}
        <flux:tab.panel name="orders" class="pt-4">
            <div class="flex justify-end mb-4">
                <flux:button href="{{ route('orders.create', ['contact_id' => $contactId]) }}" variant="primary" size="sm" icon="plus">
                    Ny ordre
                </flux:button>
            </div>
            @if($this->orders->count() > 0)
                <div class="overflow-x-auto">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Nr</flux:table.column>
                            <flux:table.column>Tittel</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Dato</flux:table.column>
                            <flux:table.column class="text-right">Belop</flux:table.column>
                            <flux:table.column class="text-right"></flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($this->orders as $order)
                                <flux:table.row wire:key="order-{{ $order->id }}" class="cursor-pointer" wire:click="showDetail('order', {{ $order->id }})">
                                    <flux:table.cell>
                                        <flux:badge variant="outline" size="sm">{{ $order->order_number }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $order->title ?: '-' }}</flux:table.cell>
                                    <flux:table.cell>
                                        @if($order->orderStatus)
                                            <flux:badge size="sm" :color="$order->orderStatus->color ?? 'zinc'">
                                                {{ $order->orderStatus->name }}
                                            </flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">{{ $order->order_date?->format('d.m.Y') }}</flux:table.cell>
                                    <flux:table.cell class="text-right font-medium">{{ number_format($order->total, 2, ',', ' ') }} kr</flux:table.cell>
                                    <flux:table.cell class="text-right">
                                        <a href="{{ route('orders.preview', $order) }}" target="_blank" wire:click.stop>
                                            <flux:button variant="ghost" size="xs" icon="eye" />
                                        </a>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @else
                <div class="text-center py-8">
                    <flux:icon.shopping-cart class="w-12 h-12 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" />
                    <flux:text class="text-zinc-500">Ingen ordrer for denne kontakten</flux:text>
                </div>
            @endif
        </flux:tab.panel>

        {{-- Invoices Tab --}}
        <flux:tab.panel name="invoices" class="pt-4">
            <div class="flex justify-end mb-4">
                <flux:button href="{{ route('invoices.create', ['contact_id' => $contactId]) }}" variant="primary" size="sm" icon="plus">
                    Ny faktura
                </flux:button>
            </div>
            @if($this->invoices->count() > 0)
                <div class="overflow-x-auto">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Nr</flux:table.column>
                            <flux:table.column>Type</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Dato</flux:table.column>
                            <flux:table.column>Forfall</flux:table.column>
                            <flux:table.column class="text-right">Belop</flux:table.column>
                            <flux:table.column class="text-right"></flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($this->invoices as $invoice)
                                <flux:table.row wire:key="invoice-{{ $invoice->id }}" class="cursor-pointer" wire:click="showDetail('invoice', {{ $invoice->id }})">
                                    <flux:table.cell>
                                        <flux:badge variant="outline" size="sm">{{ $invoice->invoice_number }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($invoice->is_credit_note)
                                            <flux:badge color="purple" size="sm">Kreditnota</flux:badge>
                                        @else
                                            <flux:badge color="blue" size="sm">Faktura</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($invoice->invoiceStatus)
                                            <flux:badge size="sm" :color="$invoice->invoiceStatus->color ?? 'zinc'">
                                                {{ $invoice->invoiceStatus->name }}
                                            </flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">{{ $invoice->invoice_date?->format('d.m.Y') }}</flux:table.cell>
                                    <flux:table.cell class="{{ $invoice->is_overdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-zinc-500' }}">
                                        {{ $invoice->due_date?->format('d.m.Y') }}
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right">
                                        <span class="font-medium">{{ number_format($invoice->total, 2, ',', ' ') }} kr</span>
                                        @if($invoice->balance > 0 && !$invoice->is_credit_note)
                                            <div class="text-xs text-red-600 dark:text-red-400">Rest: {{ number_format($invoice->balance, 2, ',', ' ') }} kr</div>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right">
                                        <a href="{{ route('invoices.preview', $invoice) }}" target="_blank" wire:click.stop>
                                            <flux:button variant="ghost" size="xs" icon="eye" />
                                        </a>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @else
                <div class="text-center py-8">
                    <flux:icon.banknotes class="w-12 h-12 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" />
                    <flux:text class="text-zinc-500">Ingen fakturaer for denne kontakten</flux:text>
                </div>
            @endif
        </flux:tab.panel>
    </flux:tab.group>

    {{-- Document Detail Modal --}}
    <flux:modal wire:model="showDetailModal" variant="flyout" class="w-full max-w-2xl">
        @if($this->selectedDocument)
            <div class="space-y-6">
                {{-- Header --}}
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        @if($detailType === 'quote')
                            <flux:badge color="blue">Tilbud</flux:badge>
                            <flux:badge variant="outline">{{ $this->selectedDocument->quote_number }}</flux:badge>
                        @elseif($detailType === 'order')
                            <flux:badge color="green">Ordre</flux:badge>
                            <flux:badge variant="outline">{{ $this->selectedDocument->order_number }}</flux:badge>
                        @elseif($detailType === 'invoice')
                            <flux:badge color="{{ $this->selectedDocument->is_credit_note ? 'purple' : 'amber' }}">
                                {{ $this->selectedDocument->is_credit_note ? 'Kreditnota' : 'Faktura' }}
                            </flux:badge>
                            <flux:badge variant="outline">{{ $this->selectedDocument->invoice_number }}</flux:badge>
                        @endif
                    </div>
                    <flux:heading size="lg">{{ $this->selectedDocument->title ?: 'Uten tittel' }}</flux:heading>
                    @if($this->selectedDocument->description)
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ $this->selectedDocument->description }}</flux:text>
                    @endif
                </div>

                <flux:separator />

                {{-- Info Grid --}}
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-zinc-500 dark:text-zinc-400">Status</span>
                        <div class="mt-1">
                            @if($detailType === 'quote')
                                <flux:select wire:model="selectedStatusId" wire:change="updateQuoteStatus" size="sm" class="w-full">
                                    @foreach($this->quoteStatuses as $status)
                                        <flux:select.option value="{{ $status->id }}">{{ $status->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @else
                                @php
                                    $status = match($detailType) {
                                        'order' => $this->selectedDocument->orderStatus,
                                        'invoice' => $this->selectedDocument->invoiceStatus,
                                        default => null
                                    };
                                @endphp
                                @if($status)
                                    <flux:badge size="sm" :color="$status->color ?? 'zinc'">
                                        {{ $status->name }}
                                    </flux:badge>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div>
                        <span class="text-zinc-500 dark:text-zinc-400">Dato</span>
                        <div class="mt-1 text-zinc-900 dark:text-white">
                            @if($detailType === 'quote')
                                {{ $this->selectedDocument->quote_date?->format('d.m.Y') }}
                            @elseif($detailType === 'order')
                                {{ $this->selectedDocument->order_date?->format('d.m.Y') }}
                            @elseif($detailType === 'invoice')
                                {{ $this->selectedDocument->invoice_date?->format('d.m.Y') }}
                            @endif
                        </div>
                    </div>
                    @if($detailType === 'quote' && $this->selectedDocument->valid_until)
                        <div>
                            <span class="text-zinc-500 dark:text-zinc-400">Gyldig til</span>
                            <div class="mt-1 {{ $this->selectedDocument->is_expired ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">
                                {{ $this->selectedDocument->valid_until->format('d.m.Y') }}
                            </div>
                        </div>
                    @endif
                    @if($detailType === 'invoice' && $this->selectedDocument->due_date)
                        <div>
                            <span class="text-zinc-500 dark:text-zinc-400">Forfallsdato</span>
                            <div class="mt-1 {{ $this->selectedDocument->is_overdue ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">
                                {{ $this->selectedDocument->due_date->format('d.m.Y') }}
                            </div>
                        </div>
                    @endif
                    @if($this->selectedDocument->project)
                        <div>
                            <span class="text-zinc-500 dark:text-zinc-400">Prosjekt</span>
                            <div class="mt-1 text-zinc-900 dark:text-white">{{ $this->selectedDocument->project->name }}</div>
                        </div>
                    @endif
                </div>

                <flux:separator />

                {{-- Lines --}}
                <div>
                    <flux:heading size="sm" class="mb-3">Linjer</flux:heading>
                    @if($this->selectedDocument->lines->count() > 0)
                        <div class="space-y-2">
                            @foreach($this->selectedDocument->lines as $line)
                                <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $line->description }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ number_format($line->quantity, 2, ',', ' ') }} {{ $line->unit }} x {{ number_format($line->unit_price, 2, ',', ' ') }} kr
                                            @if($line->discount_percent > 0)
                                                <span class="text-amber-600 dark:text-amber-400">({{ number_format($line->discount_percent, 0) }}% rabatt)</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ number_format($line->line_total, 2, ',', ' ') }} kr
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen linjer</flux:text>
                    @endif
                </div>

                <flux:separator />

                {{-- Totals --}}
                <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">Sum eks. MVA</span>
                        <span class="text-zinc-900 dark:text-white">{{ number_format($this->selectedDocument->subtotal, 2, ',', ' ') }} kr</span>
                    </div>
                    @if($this->selectedDocument->discount_total > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">Rabatt</span>
                            <span class="text-zinc-900 dark:text-white">- {{ number_format($this->selectedDocument->discount_total, 2, ',', ' ') }} kr</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">MVA</span>
                        <span class="text-zinc-900 dark:text-white">{{ number_format($this->selectedDocument->vat_total, 2, ',', ' ') }} kr</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold pt-2 border-t border-zinc-200 dark:border-zinc-700">
                        <span class="text-zinc-900 dark:text-white">Totalt</span>
                        <span class="text-blue-600 dark:text-blue-400">{{ number_format($this->selectedDocument->total, 2, ',', ' ') }} kr</span>
                    </div>
                    @if($detailType === 'invoice' && !$this->selectedDocument->is_credit_note)
                        <div class="flex justify-between text-sm pt-2 border-t border-zinc-200 dark:border-zinc-700">
                            <span class="text-zinc-600 dark:text-zinc-400">Betalt</span>
                            <span class="text-green-600 dark:text-green-400">{{ number_format($this->selectedDocument->paid_amount, 2, ',', ' ') }} kr</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">Restbelop</span>
                            <span class="{{ $this->selectedDocument->balance > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ number_format($this->selectedDocument->balance, 2, ',', ' ') }} kr
                            </span>
                        </div>
                    @endif
                </div>

                <flux:separator />

                {{-- Actions --}}
                <div class="flex flex-col gap-3">
                    @if($detailType === 'quote')
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                            <div>
                                <flux:text class="font-medium text-zinc-900 dark:text-white">Send på e-post</flux:text>
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                    @if($this->selectedDocument->sent_at)
                                        Sist sendt {{ $this->selectedDocument->sent_at->format('d.m.Y H:i') }}
                                    @else
                                        Ikke sendt ennå
                                    @endif
                                </flux:text>
                            </div>
                            <flux:button
                                wire:click="sendQuoteEmail"
                                wire:confirm="Send tilbudet til {{ $this->contact->email ?? 'kunden' }}?"
                                variant="{{ $this->selectedDocument->sent_at ? 'outline' : 'primary' }}"
                                icon="paper-airplane"
                                size="sm"
                            >
                                {{ $this->selectedDocument->sent_at ? 'Send på nytt' : 'Send e-post' }}
                            </flux:button>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <flux:button type="button" wire:click="closeDetail" variant="ghost">Lukk</flux:button>
                        <div class="flex gap-2">
                            @if($detailType === 'quote')
                                <flux:button href="{{ route('quotes.preview', $this->selectedDocument) }}" target="_blank" variant="outline" icon="eye">
                                    Forhåndsvis
                                </flux:button>
                                <flux:button href="{{ route('quotes.pdf', $this->selectedDocument) }}" target="_blank" variant="primary" icon="document-arrow-down">
                                    Last ned PDF
                                </flux:button>
                            @elseif($detailType === 'order')
                                <flux:button href="{{ route('orders.preview', $this->selectedDocument) }}" target="_blank" variant="outline" icon="eye">
                                    Forhåndsvis
                                </flux:button>
                                <flux:button href="{{ route('orders.pdf', $this->selectedDocument) }}" target="_blank" variant="primary" icon="document-arrow-down">
                                    Last ned PDF
                                </flux:button>
                            @elseif($detailType === 'invoice')
                                <flux:button href="{{ route('invoices.preview', $this->selectedDocument) }}" target="_blank" variant="outline" icon="eye">
                                    Forhåndsvis
                                </flux:button>
                                <flux:button href="{{ route('invoices.pdf', $this->selectedDocument) }}" target="_blank" variant="primary" icon="document-arrow-down">
                                    Last ned PDF
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
