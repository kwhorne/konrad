<x-layouts.app title="Dashboard">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="dashboard" />
        <x-app-header current="dashboard" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-6">
                <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                    God {{ $greeting }}, {{ auth()->user()->name }}
                </flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    Her er en oversikt over bedriften din
                </flux:text>
            </div>

            <!-- Key Financial Stats -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-6">
                <!-- Utestående kundefakturaer -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0">
                                <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                    Utestående
                                </flux:text>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white truncate">
                                    {{ number_format($unpaidInvoices, 0, ',', ' ') }} kr
                                </flux:heading>
                            </div>
                            <div class="p-2 rounded-full bg-blue-100 dark:bg-blue-900/30 shrink-0">
                                <flux:icon.banknotes class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <flux:button href="{{ route('invoices.index') }}" variant="ghost" size="xs" class="w-full mt-3">
                            Se fakturaer
                        </flux:button>
                    </div>
                </flux:card>

                <!-- Forfalt kundefakturaer -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm {{ $overdueInvoicesCount > 0 ? 'ring-2 ring-red-500' : '' }}">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0">
                                <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                    Forfalt
                                </flux:text>
                                <flux:heading size="lg" class="{{ $overdueInvoicesCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }} truncate">
                                    {{ number_format($overdueInvoices, 0, ',', ' ') }} kr
                                </flux:heading>
                            </div>
                            <div class="p-2 rounded-full {{ $overdueInvoicesCount > 0 ? 'bg-red-100 dark:bg-red-900/30' : 'bg-green-100 dark:bg-green-900/30' }} shrink-0">
                                @if($overdueInvoicesCount > 0)
                                    <flux:icon.exclamation-triangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                                @else
                                    <flux:icon.check-circle class="h-5 w-5 text-green-600 dark:text-green-400" />
                                @endif
                            </div>
                        </div>
                        @if($overdueInvoicesCount > 0)
                            <flux:badge color="red" size="sm" class="mt-2">{{ $overdueInvoicesCount }} stk</flux:badge>
                        @endif
                    </div>
                </flux:card>

                <!-- Leverandørgjeld -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0">
                                <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                    Leverandørgjeld
                                </flux:text>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white truncate">
                                    {{ number_format($unpaidSupplierInvoices, 0, ',', ' ') }} kr
                                </flux:heading>
                            </div>
                            <div class="p-2 rounded-full bg-orange-100 dark:bg-orange-900/30 shrink-0">
                                <flux:icon.building-office class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            </div>
                        </div>
                        @if($overdueSupplierInvoicesCount > 0)
                            <flux:badge color="amber" size="sm" class="mt-2">{{ $overdueSupplierInvoicesCount }} forfalt</flux:badge>
                        @endif
                    </div>
                </flux:card>

                <!-- Innboks -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm {{ $incomingVouchersCount > 0 ? 'ring-2 ring-amber-500' : '' }}">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0">
                                <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                    Innboks
                                </flux:text>
                                <flux:heading size="lg" class="{{ $incomingVouchersCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-white' }}">
                                    {{ $incomingVouchersCount }}
                                </flux:heading>
                            </div>
                            <div class="p-2 rounded-full {{ $incomingVouchersCount > 0 ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-zinc-100 dark:bg-zinc-800' }} shrink-0">
                                <flux:icon.inbox-arrow-down class="h-5 w-5 {{ $incomingVouchersCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-400' }}" />
                            </div>
                        </div>
                        @if($incomingVouchersCount > 0)
                            <flux:button href="{{ route('accounting.incoming') }}" variant="primary" size="xs" class="w-full mt-3">
                                Behandle
                            </flux:button>
                        @else
                            <flux:text class="mt-2 text-xs text-zinc-400">Ingen ventende</flux:text>
                        @endif
                    </div>
                </flux:card>
            </div>

            <!-- Feature-dependent Stats -->
            @if(config('features.sales') || config('features.projects') || config('features.work_orders') || config('features.contacts'))
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-6">
                    @if(config('features.sales') && isset($stats['activeQuotes']))
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0">
                                        <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                            Aktive tilbud
                                        </flux:text>
                                        <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                                            {{ $stats['activeQuotes'] }}
                                        </flux:heading>
                                        @if($stats['activeQuotesValue'] > 0)
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ number_format($stats['activeQuotesValue'], 0, ',', ' ') }} kr
                                            </flux:text>
                                        @endif
                                    </div>
                                    <div class="p-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 shrink-0">
                                        <flux:icon.document-text class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                </div>
                            </div>
                        </flux:card>

                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0">
                                        <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                            Åpne ordrer
                                        </flux:text>
                                        <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                                            {{ $stats['openOrders'] }}
                                        </flux:heading>
                                    </div>
                                    <div class="p-2 rounded-full bg-purple-100 dark:bg-purple-900/30 shrink-0">
                                        <flux:icon.shopping-cart class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                    </div>
                                </div>
                            </div>
                        </flux:card>
                    @endif

                    @if(config('features.projects') && isset($stats['activeProjects']))
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0">
                                        <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                            Aktive prosjekter
                                        </flux:text>
                                        <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                                            {{ $stats['activeProjects'] }}
                                        </flux:heading>
                                    </div>
                                    <div class="p-2 rounded-full bg-cyan-100 dark:bg-cyan-900/30 shrink-0">
                                        <flux:icon.folder class="h-5 w-5 text-cyan-600 dark:text-cyan-400" />
                                    </div>
                                </div>
                            </div>
                        </flux:card>
                    @endif

                    @if(config('features.work_orders') && isset($stats['openWorkOrders']))
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0">
                                        <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                            Åpne arbeidsordrer
                                        </flux:text>
                                        <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                                            {{ $stats['openWorkOrders'] }}
                                        </flux:heading>
                                    </div>
                                    <div class="p-2 rounded-full bg-teal-100 dark:bg-teal-900/30 shrink-0">
                                        <flux:icon.clipboard-document-list class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                                    </div>
                                </div>
                            </div>
                        </flux:card>
                    @endif

                    @if(config('features.contacts') && isset($stats['totalContacts']))
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0">
                                        <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                            Kontakter
                                        </flux:text>
                                        <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                                            {{ $stats['totalContacts'] }}
                                        </flux:heading>
                                    </div>
                                    <div class="p-2 rounded-full bg-emerald-100 dark:bg-emerald-900/30 shrink-0">
                                        <flux:icon.users class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                    </div>
                                </div>
                            </div>
                        </flux:card>
                    @endif
                </div>
            @endif

            <!-- Revenue & Expense Chart -->
            <div class="mb-6">
                <livewire:dashboard.revenue-expense-chart />
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
                <!-- Recent Invoices -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <flux:heading size="base" level="2" class="text-zinc-900 dark:text-white">
                                Siste fakturaer
                            </flux:heading>
                            <flux:button href="{{ route('invoices.index') }}" variant="ghost" size="xs">
                                Se alle
                            </flux:button>
                        </div>

                        @if($recentInvoices->count() > 0)
                            <div class="space-y-2">
                                @foreach($recentInvoices as $invoice)
                                    <div class="flex items-center justify-between py-1.5 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <flux:text class="text-sm font-medium text-zinc-900 dark:text-white truncate">
                                                    {{ $invoice->invoice_number }}
                                                </flux:text>
                                                <flux:badge size="sm" :color="match($invoice->invoiceStatus?->code ?? '') {
                                                    'paid' => 'green',
                                                    'sent' => 'blue',
                                                    'overdue' => 'red',
                                                    'partially_paid' => 'amber',
                                                    default => 'zinc'
                                                }">
                                                    {{ $invoice->invoiceStatus?->name ?? 'Ukjent' }}
                                                </flux:badge>
                                            </div>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                                                {{ $invoice->contact?->name ?? $invoice->customer_name ?? 'Ingen kunde' }}
                                            </flux:text>
                                        </div>
                                        <div class="text-right ml-3">
                                            <flux:text class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ number_format($invoice->total, 0, ',', ' ') }} kr
                                            </flux:text>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $invoice->invoice_date?->format('d.m.Y') }}
                                            </flux:text>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <flux:icon.document-text class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mx-auto mb-2" />
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Ingen fakturaer ennå
                                </flux:text>
                            </div>
                        @endif
                    </div>
                </flux:card>

                <!-- Recent Supplier Invoices -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <flux:heading size="base" level="2" class="text-zinc-900 dark:text-white">
                                Siste leverandørfakturaer
                            </flux:heading>
                            <flux:button href="{{ route('accounting.supplier-ledger') }}" variant="ghost" size="xs">
                                Se alle
                            </flux:button>
                        </div>

                        @if($recentSupplierInvoices->count() > 0)
                            <div class="space-y-2">
                                @foreach($recentSupplierInvoices as $supplierInvoice)
                                    <div class="flex items-center justify-between py-1.5 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <flux:text class="text-sm font-medium text-zinc-900 dark:text-white truncate">
                                                    {{ $supplierInvoice->internal_number }}
                                                </flux:text>
                                                <flux:badge size="sm" :color="$supplierInvoice->status_color">
                                                    {{ $supplierInvoice->status_label }}
                                                </flux:badge>
                                            </div>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                                                {{ $supplierInvoice->contact?->name ?? 'Ukjent leverandør' }}
                                            </flux:text>
                                        </div>
                                        <div class="text-right ml-3">
                                            <flux:text class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ number_format($supplierInvoice->total, 0, ',', ' ') }} kr
                                            </flux:text>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $supplierInvoice->invoice_date?->format('d.m.Y') }}
                                            </flux:text>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <flux:icon.building-office class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mx-auto mb-2" />
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Ingen leverandørfakturaer ennå
                                </flux:text>
                            </div>
                        @endif
                    </div>
                </flux:card>
            </div>

            <!-- Quick Actions -->
            <div class="mb-6">
                <flux:heading size="base" level="2" class="text-zinc-900 dark:text-white mb-3">
                    Hurtighandlinger
                </flux:heading>
                <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                    @if(config('features.sales'))
                        <flux:button href="{{ route('invoices.create') }}" variant="ghost" class="flex-col h-auto py-3">
                            <flux:icon.plus-circle class="h-6 w-6 mb-1 text-blue-600" />
                            <span class="text-xs">Ny faktura</span>
                        </flux:button>
                        <flux:button href="{{ route('quotes.create') }}" variant="ghost" class="flex-col h-auto py-3">
                            <flux:icon.document-plus class="h-6 w-6 mb-1 text-indigo-600" />
                            <span class="text-xs">Nytt tilbud</span>
                        </flux:button>
                    @endif

                    <flux:button href="{{ route('accounting.vouchers') }}" variant="ghost" class="flex-col h-auto py-3">
                        <flux:icon.document-text class="h-6 w-6 mb-1 text-green-600" />
                        <span class="text-xs">Nytt bilag</span>
                    </flux:button>

                    @if(config('features.contacts'))
                        <flux:button href="{{ route('contacts.create') }}" variant="ghost" class="flex-col h-auto py-3">
                            <flux:icon.user-plus class="h-6 w-6 mb-1 text-emerald-600" />
                            <span class="text-xs">Ny kontakt</span>
                        </flux:button>
                    @endif

                    @if(config('features.projects'))
                        <flux:button href="{{ route('projects.create') }}" variant="ghost" class="flex-col h-auto py-3">
                            <flux:icon.folder-plus class="h-6 w-6 mb-1 text-cyan-600" />
                            <span class="text-xs">Nytt prosjekt</span>
                        </flux:button>
                    @endif

                    <flux:button href="{{ route('reports.index') }}" variant="ghost" class="flex-col h-auto py-3">
                        <flux:icon.chart-bar class="h-6 w-6 mb-1 text-purple-600" />
                        <span class="text-xs">Rapporter</span>
                    </flux:button>
                </div>
            </div>

            @if(auth()->user()->is_admin)
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border-2 border-indigo-200 dark:border-indigo-800">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:heading size="base" level="2" class="text-zinc-900 dark:text-white">
                                    Administrasjon
                                </flux:heading>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                    Administrer brukere og systeminnstillinger
                                </flux:text>
                            </div>
                            <flux:button href="{{ route('admin.users') }}" variant="primary" size="sm">
                                Gå til admin
                            </flux:button>
                        </div>
                    </div>
                </flux:card>
            @endif
        </flux:main>

    </div>
</x-layouts.app>
