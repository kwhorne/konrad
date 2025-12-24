<div>
    {{-- Header with filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row flex-wrap gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Sok etter tilbud..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterStatus" class="w-full sm:w-40">
                <option value="">Alle statuser</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterContact" class="w-full sm:w-48">
                <option value="">Alle kunder</option>
                @foreach($contacts as $contact)
                    <option value="{{ $contact->id }}">{{ $contact->company_name }}</option>
                @endforeach
            </flux:select>
        </div>

        <flux:button wire:click="openModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Nytt tilbud
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

    {{-- Quotes table --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($quotes->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Tilbud</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kunde</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Gyldig til</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Belop</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Handlinger</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($quotes as $quote)
                                <tr wire:key="quote-{{ $quote->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $quote->title }}</flux:text>
                                            <div class="flex items-center gap-2 mt-1">
                                                <flux:badge variant="outline">{{ $quote->quote_number }}</flux:badge>
                                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $quote->quote_date?->format('d.m.Y') }}</flux:text>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($quote->contact)
                                            <flux:text class="text-zinc-900 dark:text-white">{{ $quote->customer_name }}</flux:text>
                                        @else
                                            <flux:text class="text-zinc-400 dark:text-zinc-500 italic">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($quote->quoteStatus)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColorClass($quote->quoteStatus->color) }}">
                                                {{ $quote->quoteStatus->name }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($quote->valid_until)
                                            <flux:text class="{{ $quote->is_expired ? 'text-red-600 dark:text-red-400' : 'text-zinc-600 dark:text-zinc-400' }}">
                                                {{ $quote->valid_until->format('d.m.Y') }}
                                            </flux:text>
                                        @else
                                            <flux:text class="text-zinc-400 dark:text-zinc-500 italic">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                            {{ number_format($quote->total, 2, ',', ' ') }} kr
                                        </flux:text>
                                        @if($quote->vat_total > 0)
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                                inkl. {{ number_format($quote->vat_total, 2, ',', ' ') }} MVA
                                            </flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($quote->can_convert)
                                                <flux:button wire:click="convertToOrder({{ $quote->id }})" wire:confirm="Konverter tilbudet til ordre?" variant="ghost" size="sm" title="Konverter til ordre">
                                                    <flux:icon.arrow-right-circle class="w-4 h-4" />
                                                </flux:button>
                                            @endif
                                            <form action="{{ route('quotes.send', $quote) }}" method="POST" class="inline" onsubmit="return confirm('Send tilbudet til {{ $quote->contact?->email ?? 'kunden' }}?')">
                                                @csrf
                                                <flux:button type="submit" variant="ghost" size="sm" title="{{ $quote->sent_at ? 'Sendt '.$quote->sent_at->format('d.m.Y H:i') : 'Send pa e-post' }}" class="{{ $quote->sent_at ? 'text-green-600' : '' }}">
                                                    <flux:icon.paper-airplane class="w-4 h-4" />
                                                </flux:button>
                                            </form>
                                            <a href="{{ route('quotes.preview', $quote) }}" target="_blank">
                                                <flux:button variant="ghost" size="sm" title="Forhandsvis">
                                                    <flux:icon.eye class="w-4 h-4" />
                                                </flux:button>
                                            </a>
                                            <a href="{{ route('quotes.pdf', $quote) }}" target="_blank">
                                                <flux:button variant="ghost" size="sm" title="Last ned PDF">
                                                    <flux:icon.document-arrow-down class="w-4 h-4" />
                                                </flux:button>
                                            </a>
                                            <flux:button wire:click="openModal({{ $quote->id }})" variant="ghost" size="sm">
                                                <flux:icon.pencil class="w-4 h-4" />
                                            </flux:button>
                                            <flux:button wire:click="delete({{ $quote->id }})" wire:confirm="Er du sikker pa at du vil slette dette tilbudet?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                                                <flux:icon.trash class="w-4 h-4" />
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $quotes->links() }}</div>
            @else
                <div class="text-center py-12">
                    <flux:icon.document-text class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        @if($search || $filterStatus || $filterContact)
                            Ingen tilbud funnet
                        @else
                            Ingen tilbud enna
                        @endif
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        @if($search || $filterStatus || $filterContact)
                            Prov a endre sokekriteriene
                        @else
                            Kom i gang ved a opprette ditt forste tilbud
                        @endif
                    </flux:text>
                    @if(!$search && !$filterStatus && !$filterContact)
                        <flux:button wire:click="openModal" variant="primary">
                            <flux:icon.plus class="w-5 h-5 mr-2" />
                            Opprett tilbud
                        </flux:button>
                    @endif
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Quote Flyout Modal --}}
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Rediger tilbud' : 'Nytt tilbud' }}</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ $editingId ? 'Oppdater tilbudsinformasjon' : 'Opprett et nytt tilbud' }}
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Tittel *</flux:label>
                    <flux:input wire:model="title" type="text" placeholder="Tittel pa tilbudet" />
                    @error('title')<flux:error>{{ $message }}</flux:error>@enderror
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:textarea wire:model="description" rows="2" placeholder="Beskrivelse..."></flux:textarea>
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Kunde *</flux:label>
                        <flux:select wire:model.live="contact_id">
                            <option value="">Velg kunde</option>
                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}">{{ $contact->company_name }}</option>
                            @endforeach
                        </flux:select>
                        @error('contact_id')<flux:error>{{ $message }}</flux:error>@enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Prosjekt</flux:label>
                        <flux:select wire:model="project_id">
                            <option value="">Velg prosjekt</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select wire:model="quote_status_id">
                            <option value="">Velg status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Tilbudsdato</flux:label>
                        <flux:input wire:model="quote_date" type="date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Gyldig til</flux:label>
                        <flux:input wire:model="valid_until" type="date" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Betalingsbetingelser (dager)</flux:label>
                    <flux:input wire:model="payment_terms_days" type="number" min="0" />
                </flux:field>

                {{-- Customer Address --}}
                <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg space-y-3">
                    <flux:text class="font-medium text-zinc-700 dark:text-zinc-300">Kundeadresse</flux:text>
                    <flux:input wire:model="customer_name" type="text" placeholder="Firmanavn" />
                    <flux:input wire:model="customer_address" type="text" placeholder="Adresse" />
                    <div class="grid grid-cols-3 gap-2">
                        <flux:input wire:model="customer_postal_code" type="text" placeholder="Postnr" />
                        <flux:input wire:model="customer_city" type="text" placeholder="Sted" class="col-span-2" />
                    </div>
                </div>

                <flux:field>
                    <flux:label>Interne notater</flux:label>
                    <flux:textarea wire:model="internal_notes" rows="2" placeholder="Interne notater..."></flux:textarea>
                </flux:field>
            </div>

            {{-- Quote Lines Section --}}
            @if($editingId)
                <flux:separator />
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="md">Linjer</flux:heading>
                        <flux:button wire:click="openLineModal" variant="ghost" size="sm">
                            <flux:icon.plus class="w-4 h-4 mr-1" />
                            Legg til linje
                        </flux:button>
                    </div>

                    @if(count($quoteLines) > 0)
                        <div class="space-y-2">
                            @foreach($quoteLines as $line)
                                <div wire:key="line-{{ $line['id'] }}" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                    <div class="flex-1">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $line['description'] }}</flux:text>
                                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ number_format($line['quantity'], 2, ',', ' ') }} {{ $line['unit'] }} x {{ number_format($line['unit_price'], 2, ',', ' ') }} kr
                                            @if($line['discount_percent'] > 0)({{ number_format($line['discount_percent'], 0) }}% rabatt)@endif
                                            - MVA {{ number_format($line['vat_percent'], 0) }}%
                                        </flux:text>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                            {{ number_format($line['quantity'] * $line['unit_price'] * (1 - $line['discount_percent'] / 100), 2, ',', ' ') }} kr
                                        </flux:text>
                                        <div class="flex items-center gap-1">
                                            <flux:button wire:click="openLineModal({{ $line['id'] }})" variant="ghost" size="sm">
                                                <flux:icon.pencil class="w-3 h-3" />
                                            </flux:button>
                                            <flux:button wire:click="deleteLine({{ $line['id'] }})" wire:confirm="Slett linjen?" variant="ghost" size="sm" class="text-red-600">
                                                <flux:icon.trash class="w-3 h-3" />
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-zinc-500 dark:text-zinc-400 text-center py-4">Ingen linjer enna.</flux:text>
                    @endif
                </div>
            @endif

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="save" variant="primary">{{ $editingId ? 'Oppdater' : 'Opprett' }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Line Modal --}}
    <flux:modal wire:model="showLineModal" variant="flyout" class="w-full max-w-md">
        <div class="space-y-6">
            <flux:heading size="lg">{{ $editingLineId ? 'Rediger linje' : 'Ny linje' }}</flux:heading>
            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Produkt</flux:label>
                    <flux:select wire:model.live="line_product_id">
                        <option value="">Velg produkt...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse *</flux:label>
                    <flux:input wire:model="line_description" type="text" />
                    @error('line_description')<flux:error>{{ $message }}</flux:error>@enderror
                </flux:field>

                <div class="grid grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>Antall *</flux:label>
                        <flux:input wire:model="line_quantity" type="number" step="0.01" min="0.01" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Enhet</flux:label>
                        <flux:input wire:model="line_unit" type="text" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Pris *</flux:label>
                        <flux:input wire:model="line_unit_price" type="number" step="0.01" min="0" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Rabatt %</flux:label>
                        <flux:input wire:model="line_discount_percent" type="number" step="0.01" min="0" max="100" />
                    </flux:field>
                    <flux:field>
                        <flux:label>MVA-sats</flux:label>
                        <flux:select wire:model.live="line_vat_rate_id">
                            <option value="">Velg MVA...</option>
                            @foreach($vatRates as $rate)
                                <option value="{{ $rate->id }}">{{ $rate->name }} ({{ $rate->rate }}%)</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>
            </div>

            <flux:separator />
            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeLineModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="saveLine" variant="primary">{{ $editingLineId ? 'Oppdater' : 'Legg til' }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
