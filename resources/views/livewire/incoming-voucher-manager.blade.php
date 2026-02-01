<div>
    {{-- Header with status tabs and actions --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row flex-wrap gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterStatus" class="w-full sm:w-44">
                <option value="">Alle statuser</option>
                <option value="pending">Venter ({{ $statusCounts['pending'] }})</option>
                <option value="parsing">Tolkes ({{ $statusCounts['parsing'] }})</option>
                <option value="parsed">Tolket ({{ $statusCounts['parsed'] }})</option>
                <option value="attested">Attestert ({{ $statusCounts['attested'] }})</option>
                <option value="approved">Godkjent ({{ $statusCounts['approved'] }})</option>
                <option value="posted">Bokført ({{ $statusCounts['posted'] }})</option>
                <option value="rejected">Avvist ({{ $statusCounts['rejected'] }})</option>
            </flux:select>

            <flux:select wire:model.live="filterSource" class="w-full sm:w-36">
                <option value="">Alle kilder</option>
                <option value="upload">Opplastet</option>
                <option value="email">E-post</option>
            </flux:select>
        </div>

        <flux:button wire:click="openUploadModal" variant="primary">
            <flux:icon.arrow-up-tray class="w-5 h-5 mr-2" />
            Last opp bilag
        </flux:button>
    </div>

    {{-- Status badges summary --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @if($statusCounts['parsed'] > 0)
            <flux:badge color="amber" size="sm">
                {{ $statusCounts['parsed'] }} venter på attestering
            </flux:badge>
        @endif
        @if($statusCounts['attested'] > 0)
            <flux:badge color="cyan" size="sm">
                {{ $statusCounts['attested'] }} venter på godkjenning
            </flux:badge>
        @endif
        @if($statusCounts['pending'] + $statusCounts['parsing'] > 0)
            <flux:badge color="zinc" size="sm">
                {{ $statusCounts['pending'] + $statusCounts['parsing'] }} under behandling
            </flux:badge>
        @endif
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

    {{-- Vouchers table --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($vouchers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Referanse</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Fil</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Leverandør</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Faktura</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Beløp</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Handlinger</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($vouchers as $voucher)
                                <tr wire:key="voucher-{{ $voucher->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer" wire:click="openDetail({{ $voucher->id }})">
                                    <td class="px-4 py-4">
                                        <div>
                                            <flux:badge variant="outline" size="sm">{{ $voucher->reference_number }}</flux:badge>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $voucher->created_at->format('d.m.Y H:i') }}</flux:text>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            @if($voucher->is_pdf)
                                                <flux:icon.document class="w-5 h-5 text-red-500" />
                                            @else
                                                <flux:icon.photo class="w-5 h-5 text-blue-500" />
                                            @endif
                                            <div>
                                                <flux:text class="text-sm text-zinc-900 dark:text-white truncate max-w-xs">{{ $voucher->original_filename }}</flux:text>
                                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $voucher->file_size_formatted }}</flux:text>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        @if($voucher->suggestedSupplier)
                                            <flux:text class="text-sm text-zinc-900 dark:text-white">{{ $voucher->suggestedSupplier->company_name }}</flux:text>
                                        @elseif($voucher->parsed_data['supplier_name'] ?? null)
                                            <flux:text class="text-sm text-amber-600 dark:text-amber-400 italic">{{ $voucher->parsed_data['supplier_name'] }} (ny)</flux:text>
                                        @else
                                            <flux:text class="text-sm text-zinc-400">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        @if($voucher->suggested_invoice_number)
                                            <flux:text class="text-sm text-zinc-900 dark:text-white">{{ $voucher->suggested_invoice_number }}</flux:text>
                                            @if($voucher->suggested_invoice_date)
                                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $voucher->suggested_invoice_date->format('d.m.Y') }}</flux:text>
                                            @endif
                                        @else
                                            <flux:text class="text-sm text-zinc-400">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        @if($voucher->suggested_total)
                                            <flux:text class="font-mono text-sm text-zinc-900 dark:text-white">{{ number_format($voucher->suggested_total, 2, ',', ' ') }}</flux:text>
                                            @if($voucher->suggested_vat_total)
                                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">MVA: {{ number_format($voucher->suggested_vat_total, 2, ',', ' ') }}</flux:text>
                                            @endif
                                        @else
                                            <flux:text class="text-sm text-zinc-400">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center" wire:click.stop>
                                        <flux:badge color="{{ $voucher->status_color }}" size="sm">
                                            {{ $voucher->status_label }}
                                        </flux:badge>
                                        @if($voucher->confidence_score)
                                            <div class="mt-1">
                                                <flux:text class="text-xs text-zinc-500">{{ $voucher->confidence_percent }}% sikkerhet</flux:text>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-right" wire:click.stop>
                                        <div class="flex items-center justify-end gap-1">
                                            @if(in_array($voucher->status, ['pending', 'parsed']))
                                                <flux:button wire:click="reParse({{ $voucher->id }})" variant="ghost" size="sm" title="Tolk på nytt">
                                                    <flux:icon.arrow-path class="w-4 h-4" />
                                                </flux:button>
                                            @endif
                                            <flux:button wire:click="openDetail({{ $voucher->id }})" variant="ghost" size="sm" title="Vis detaljer">
                                                <flux:icon.eye class="w-4 h-4" />
                                            </flux:button>
                                            @if(!in_array($voucher->status, ['approved', 'posted']))
                                                <flux:button wire:click="delete({{ $voucher->id }})" wire:confirm="Er du sikker på at du vil slette dette bilaget?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700" title="Slett">
                                                    <flux:icon.trash class="w-4 h-4" />
                                                </flux:button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $vouchers->links() }}</div>
            @else
                <div class="text-center py-12">
                    <flux:icon.inbox-arrow-down class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        @if($search || $filterStatus || $filterSource)
                            Ingen bilag funnet
                        @else
                            Ingen inngående bilag
                        @endif
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        @if($search || $filterStatus || $filterSource)
                            Prøv å endre søkekriteriene
                        @else
                            Last opp leverandørfakturaer for AI-tolkning og godkjenning
                        @endif
                    </flux:text>
                    @if(!$search && !$filterStatus && !$filterSource)
                        <flux:button wire:click="openUploadModal" variant="primary">
                            <flux:icon.arrow-up-tray class="w-5 h-5 mr-2" />
                            Last opp bilag
                        </flux:button>
                    @endif
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Upload Modal --}}
    <flux:modal wire:model="showUploadModal" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Last opp bilag</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    Last opp en eller flere leverandørfakturaer for AI-tolkning
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Velg filer</flux:label>
                    <input type="file" wire:model="uploadFiles" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.webp"
                        class="block w-full text-sm text-zinc-500 dark:text-zinc-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-lg file:border-0
                        file:text-sm file:font-semibold
                        file:bg-indigo-50 file:text-indigo-700
                        dark:file:bg-indigo-900/30 dark:file:text-indigo-400
                        hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50
                        cursor-pointer" />
                    <flux:description>PDF, JPG, PNG, GIF, WebP (maks {{ config('voucher.max_file_size', 10240) / 1024 }} MB per fil)</flux:description>
                    @error('uploadFiles')<flux:error>{{ $message }}</flux:error>@enderror
                    @error('uploadFiles.*')<flux:error>{{ $message }}</flux:error>@enderror
                </flux:field>

                @if(count($uploadFiles) > 0)
                    <div class="space-y-2">
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Valgte filer:</flux:text>
                        @foreach($uploadFiles as $file)
                            <div class="flex items-center gap-2 p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                <flux:icon.document class="w-4 h-4 text-zinc-400" />
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300">{{ $file->getClientOriginalName() }}</flux:text>
                                <flux:text class="text-xs text-zinc-500">({{ number_format($file->getSize() / 1024, 1) }} KB)</flux:text>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeUploadModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="uploadVouchers" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="uploadVouchers">Last opp og tolk</span>
                    <span wire:loading wire:target="uploadVouchers">Laster opp...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Detail Modal --}}
    <flux:modal wire:model="showDetailModal" variant="flyout" class="w-full max-w-4xl">
        @if($selectedVoucher)
            <div class="space-y-6">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:heading size="lg">{{ $selectedVoucher->reference_number }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                            {{ $selectedVoucher->original_filename }}
                        </flux:text>
                    </div>
                    <flux:badge color="{{ $selectedVoucher->status_color }}">
                        {{ $selectedVoucher->status_label }}
                    </flux:badge>
                </div>

                <flux:separator />

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Left column: File preview --}}
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3">Bilagsfil</flux:text>
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden bg-zinc-50 dark:bg-zinc-800">
                            @if($selectedVoucher->is_image)
                                <img src="{{ Storage::disk(config('voucher.storage.disk', 'local'))->url($selectedVoucher->file_path) }}" alt="Bilag" class="w-full h-auto max-h-96 object-contain" />
                            @elseif($selectedVoucher->is_pdf)
                                <div class="p-8 text-center">
                                    <flux:icon.document class="w-16 h-16 text-red-500 mx-auto mb-4" />
                                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-4">PDF-dokument</flux:text>
                                    <a href="{{ Storage::disk(config('voucher.storage.disk', 'local'))->url($selectedVoucher->file_path) }}" target="_blank" class="inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <flux:icon.arrow-top-right-on-square class="w-4 h-4" />
                                        Åpne i ny fane
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Parsed data preview --}}
                        @if($selectedVoucher->parsed_data && !isset($selectedVoucher->parsed_data['error']))
                            <div class="mt-4">
                                <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">AI-tolket data</flux:text>
                                <div class="text-xs bg-zinc-100 dark:bg-zinc-800 rounded-lg p-3 max-h-48 overflow-y-auto">
                                    <pre class="whitespace-pre-wrap text-zinc-600 dark:text-zinc-400">{{ json_encode($selectedVoucher->parsed_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        @elseif(isset($selectedVoucher->parsed_data['error']))
                            <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <flux:text class="text-sm text-red-800 dark:text-red-200">Feil ved tolkning: {{ $selectedVoucher->parsed_data['error'] }}</flux:text>
                            </div>
                        @endif
                    </div>

                    {{-- Right column: Form fields --}}
                    <div class="space-y-4">
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Fakturaopplysninger</flux:text>

                        {{-- Supplier --}}
                        <flux:field>
                            <flux:label>Leverandør *</flux:label>
                            <flux:input wire:model.live.debounce.300ms="supplierSearch" type="text" placeholder="Søk på leverandør..." />
                            @error('editSupplierId')<flux:error>{{ $message }}</flux:error>@enderror

                            @if($supplierSearch && !$editSupplierId)
                                <div class="mt-2 max-h-32 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg">
                                    @forelse($this->suppliers as $supplier)
                                        <button wire:click="selectSupplier({{ $supplier->id }})" type="button" class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-800 border-b border-zinc-100 dark:border-zinc-700 last:border-b-0">
                                            <span class="text-sm text-zinc-900 dark:text-white">{{ $supplier->company_name }}</span>
                                            @if($supplier->organization_number)
                                                <span class="text-xs text-zinc-500 ml-2">({{ $supplier->organization_number }})</span>
                                            @endif
                                        </button>
                                    @empty
                                        <div class="px-3 py-2 text-sm text-zinc-500">Ingen leverandører funnet</div>
                                    @endforelse
                                </div>
                            @endif

                            @if($editSupplierId)
                                <div class="mt-2 flex items-center gap-2">
                                    <flux:badge variant="outline">Valgt: {{ $supplierSearch }}</flux:badge>
                                    <flux:button wire:click="$set('editSupplierId', null)" variant="ghost" size="sm">
                                        <flux:icon.x-mark class="w-3 h-3" />
                                    </flux:button>
                                </div>
                            @endif
                        </flux:field>

                        <div class="grid grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Fakturanummer *</flux:label>
                                <flux:input wire:model="editInvoiceNumber" type="text" />
                                @error('editInvoiceNumber')<flux:error>{{ $message }}</flux:error>@enderror
                            </flux:field>
                            <flux:field>
                                <flux:label>Fakturadato *</flux:label>
                                <flux:input wire:model="editInvoiceDate" type="date" />
                                @error('editInvoiceDate')<flux:error>{{ $message }}</flux:error>@enderror
                            </flux:field>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Forfallsdato</flux:label>
                                <flux:input wire:model="editDueDate" type="date" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Totalbeløp *</flux:label>
                                <flux:input wire:model="editTotal" type="number" step="0.01" />
                                @error('editTotal')<flux:error>{{ $message }}</flux:error>@enderror
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>MVA-beløp</flux:label>
                            <flux:input wire:model="editVatTotal" type="number" step="0.01" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Kostnadskonto *</flux:label>
                            <flux:select wire:model="editAccountId">
                                <option value="">Velg konto...</option>
                                @foreach($this->accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->account_number }} - {{ $account->name }}</option>
                                @endforeach
                            </flux:select>
                            @error('editAccountId')<flux:error>{{ $message }}</flux:error>@enderror
                        </flux:field>

                        {{-- Workflow info --}}
                        @if($selectedVoucher->attestedByUser)
                            <div class="p-3 bg-cyan-50 dark:bg-cyan-900/20 rounded-lg">
                                <flux:text class="text-sm text-cyan-800 dark:text-cyan-200">
                                    Attestert av {{ $selectedVoucher->attestedByUser->name }} {{ $selectedVoucher->attested_at->format('d.m.Y H:i') }}
                                </flux:text>
                            </div>
                        @endif
                        @if($selectedVoucher->approvedByUser)
                            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <flux:text class="text-sm text-green-800 dark:text-green-200">
                                    Godkjent av {{ $selectedVoucher->approvedByUser->name }} {{ $selectedVoucher->approved_at->format('d.m.Y H:i') }}
                                </flux:text>
                            </div>
                        @endif
                        @if($selectedVoucher->rejectedByUser)
                            <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                <flux:text class="text-sm text-red-800 dark:text-red-200">
                                    Avvist av {{ $selectedVoucher->rejectedByUser->name }} {{ $selectedVoucher->rejected_at->format('d.m.Y H:i') }}
                                </flux:text>
                                <flux:text class="text-sm text-red-600 dark:text-red-300 mt-1">
                                    Grunn: {{ $selectedVoucher->rejection_reason }}
                                </flux:text>
                            </div>
                        @endif
                    </div>
                </div>

                <flux:separator />

                {{-- Action buttons based on status --}}
                <div class="flex justify-between">
                    <div>
                        @if(!in_array($selectedVoucher->status, ['approved', 'posted']))
                            <flux:button wire:click="openRejectModal" variant="outline" class="text-red-600 border-red-300 hover:bg-red-50">
                                <flux:icon.x-circle class="w-4 h-4 mr-2" />
                                Avvis
                            </flux:button>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <flux:button wire:click="closeDetail" variant="ghost">Lukk</flux:button>
                        @if($selectedVoucher->status === 'parsed')
                            <flux:button wire:click="attest" variant="primary">
                                <flux:icon.check class="w-4 h-4 mr-2" />
                                Attester
                            </flux:button>
                        @elseif($selectedVoucher->status === 'attested')
                            <flux:button wire:click="approve" variant="primary">
                                <flux:icon.check-badge class="w-4 h-4 mr-2" />
                                Godkjenn og bokfør
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Reject Modal --}}
    <flux:modal wire:model="showRejectModal" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Avvis bilag</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    Oppgi en grunn for avvisningen
                </flux:text>
            </div>

            <flux:separator />

            <flux:field>
                <flux:label>Avvisningsgrunn *</flux:label>
                <flux:textarea wire:model="rejectReason" rows="3" placeholder="Beskriv hvorfor bilaget avvises..." />
                @error('rejectReason')<flux:error>{{ $message }}</flux:error>@enderror
            </flux:field>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeRejectModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="reject" variant="primary" class="bg-red-600 hover:bg-red-700">
                    <flux:icon.x-circle class="w-4 h-4 mr-2" />
                    Avvis
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
