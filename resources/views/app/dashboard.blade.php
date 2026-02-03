<x-layouts.app title="Dashboard">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="dashboard" />
        <x-app-header current="dashboard" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            {{-- Header --}}
            <div class="mb-8">
                <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                    God {{ $greeting }}, {{ auth()->user()->name }}
                </flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ now()->locale('nb')->isoFormat('dddd D. MMMM YYYY') }}
                </flux:text>
            </div>

            {{-- Mine timer denne uken --}}
            <div class="mb-6">
                <flux:card class="bg-gradient-to-br from-indigo-500 to-violet-600 shadow-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="text-sm font-medium text-white/80">
                                    Mine timer denne uken
                                </flux:text>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-4xl font-bold text-white">{{ number_format($myHoursThisWeek, 1, ',', ' ') }}</span>
                                    <span class="text-lg text-white/70">timer</span>
                                </div>
                                <div class="mt-2">
                                    @php
                                        $statusLabels = [
                                            'draft' => ['Kladd', 'zinc'],
                                            'submitted' => ['Sendt til godkjenning', 'amber'],
                                            'approved' => ['Godkjent', 'green'],
                                            'rejected' => ['Avvist', 'red'],
                                        ];
                                        [$label, $color] = $statusLabels[$myTimesheetStatus] ?? ['Ukjent', 'zinc'];
                                    @endphp
                                    <flux:badge :color="$color" class="bg-white/20 text-white border-white/30">
                                        {{ $label }}
                                    </flux:badge>
                                </div>
                            </div>
                            <div class="text-right">
                                <flux:button href="{{ route('timesheets.index') }}" class="bg-white/20 hover:bg-white/30 text-white border-white/30">
                                    <flux:icon.clock class="w-4 h-4 mr-2" />
                                    Registrer timer
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </flux:card>
            </div>

            {{-- Grid for stats --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Venstre kolonne --}}
                <div class="space-y-6">
                    {{-- Timer til godkjenning (kun for ledere) --}}
                    @if(auth()->user()->is_admin || auth()->user()->is_economy)
                        @if($pendingTimesheetsCount > 0)
                            <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border-l-4 border-l-amber-500">
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 rounded-full bg-amber-100 dark:bg-amber-900/30">
                                                <flux:icon.clock class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                                            </div>
                                            <div>
                                                <flux:heading size="base" class="text-zinc-900 dark:text-white">
                                                    Timer til godkjenning
                                                </flux:heading>
                                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $pendingTimesheetsCount }} {{ $pendingTimesheetsCount === 1 ? 'timeseddel' : 'timesedler' }} venter
                                                </flux:text>
                                            </div>
                                        </div>
                                        <flux:button href="{{ route('timesheets.approval') }}" variant="primary" size="sm">
                                            Behandle
                                        </flux:button>
                                    </div>

                                    @if($pendingTimesheets->count() > 0)
                                        <div class="space-y-2">
                                            @foreach($pendingTimesheets->take(3) as $timesheet)
                                                <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                                                    <div class="flex items-center gap-3">
                                                        <flux:avatar size="sm" name="{{ $timesheet->user->name }}" />
                                                        <div>
                                                            <flux:text class="text-sm font-medium text-zinc-900 dark:text-white">
                                                                {{ $timesheet->user->name }}
                                                            </flux:text>
                                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                                                Uke {{ $timesheet->week_start->weekOfYear }}
                                                            </flux:text>
                                                        </div>
                                                    </div>
                                                    <flux:text class="text-sm font-medium text-zinc-900 dark:text-white">
                                                        {{ number_format($timesheet->total_hours, 1, ',', ' ') }} t
                                                    </flux:text>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </flux:card>
                        @endif
                    @endif

                    {{-- Utestående og forfalt (kun for okonomi/admin) --}}
                    @if(auth()->user()->is_admin || auth()->user()->is_economy)
                        <div class="grid grid-cols-2 gap-4">
                            <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                                <div class="p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                                Utestående
                                            </flux:text>
                                            <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                                                {{ number_format($unpaidInvoices, 0, ',', ' ') }} kr
                                            </flux:heading>
                                        </div>
                                        <div class="p-2 rounded-full bg-blue-100 dark:bg-blue-900/30">
                                            <flux:icon.banknotes class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        </div>
                                    </div>
                                </div>
                            </flux:card>

                            <flux:card class="bg-white dark:bg-zinc-900 shadow-sm {{ $overdueInvoicesCount > 0 ? 'ring-2 ring-red-500' : '' }}">
                                <div class="p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                                Forfalt
                                            </flux:text>
                                            <flux:heading size="lg" class="{{ $overdueInvoicesCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">
                                                {{ number_format($overdueInvoices, 0, ',', ' ') }} kr
                                            </flux:heading>
                                        </div>
                                        <div class="p-2 rounded-full {{ $overdueInvoicesCount > 0 ? 'bg-red-100 dark:bg-red-900/30' : 'bg-green-100 dark:bg-green-900/30' }}">
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
                        </div>

                        {{-- Innboks --}}
                        @if($incomingVouchersCount > 0)
                            <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border-l-4 border-l-violet-500">
                                <div class="p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 rounded-full bg-violet-100 dark:bg-violet-900/30">
                                                <flux:icon.inbox-arrow-down class="h-5 w-5 text-violet-600 dark:text-violet-400" />
                                            </div>
                                            <div>
                                                <flux:heading size="base" class="text-zinc-900 dark:text-white">
                                                    Bilag i innboksen
                                                </flux:heading>
                                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $incomingVouchersCount }} {{ $incomingVouchersCount === 1 ? 'bilag' : 'bilag' }} venter
                                                </flux:text>
                                            </div>
                                        </div>
                                        <flux:button href="{{ route('economy.incoming') }}" variant="ghost" size="sm">
                                            Se innboks
                                        </flux:button>
                                    </div>
                                </div>
                            </flux:card>
                        @endif
                    @endif

                    {{-- Aktive prosjekter og arbeidsordrer --}}
                    @if(config('features.projects') || config('features.work_orders'))
                        <div class="grid grid-cols-2 gap-4">
                            @if(config('features.projects') && isset($stats['activeProjects']))
                                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                                    <div class="p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                                    Aktive prosjekter
                                                </flux:text>
                                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                                                    {{ $stats['activeProjects'] }}
                                                </flux:heading>
                                            </div>
                                            <div class="p-2 rounded-full bg-cyan-100 dark:bg-cyan-900/30">
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
                                            <div>
                                                <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                                    Arbeidsordrer
                                                </flux:text>
                                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                                                    {{ $stats['openWorkOrders'] }}
                                                </flux:heading>
                                            </div>
                                            <div class="p-2 rounded-full bg-teal-100 dark:bg-teal-900/30">
                                                <flux:icon.clipboard-document-list class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                                            </div>
                                        </div>
                                    </div>
                                </flux:card>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Hoyre kolonne --}}
                <div class="space-y-6">
                    {{-- Siste fakturaer (kun for okonomi/salg/admin) --}}
                    @if($recentInvoices->count() > 0)
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
                                                    {{ $invoice->contact?->company_name ?? $invoice->customer_name ?? 'Ingen kunde' }}
                                                </flux:text>
                                            </div>
                                            <div class="text-right ml-3">
                                                <flux:text class="text-sm font-medium text-zinc-900 dark:text-white">
                                                    {{ number_format($invoice->total, 0, ',', ' ') }} kr
                                                </flux:text>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </flux:card>
                    @endif

                    {{-- Tilbud og ordrer (kun for salg/admin) --}}
                    @if((auth()->user()->is_admin || auth()->user()->is_sales) && config('features.sales'))
                        @if(isset($stats['activeQuotes']) || isset($stats['openOrders']))
                            <div class="grid grid-cols-2 gap-4">
                                @if(isset($stats['activeQuotes']))
                                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                                        <div class="p-4">
                                            <div class="flex items-center justify-between">
                                                <div>
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
                                                <div class="p-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30">
                                                    <flux:icon.document-text class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                                </div>
                                            </div>
                                        </div>
                                    </flux:card>
                                @endif

                                @if(isset($stats['openOrders']))
                                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                                        <div class="p-4">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                                        Apne ordrer
                                                    </flux:text>
                                                    <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                                                        {{ $stats['openOrders'] }}
                                                    </flux:heading>
                                                </div>
                                                <div class="p-2 rounded-full bg-purple-100 dark:bg-purple-900/30">
                                                    <flux:icon.shopping-cart class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                                </div>
                                            </div>
                                        </div>
                                    </flux:card>
                                @endif
                            </div>
                        @endif
                    @endif

                    {{-- Snarveier --}}
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                        <div class="p-4">
                            <flux:heading size="base" level="2" class="text-zinc-900 dark:text-white mb-3">
                                Snarveier
                            </flux:heading>
                            <div class="grid grid-cols-2 gap-2">
                                <flux:button href="{{ route('timesheets.index') }}" variant="ghost" class="justify-start">
                                    <flux:icon.clock class="h-4 w-4 mr-2 text-indigo-600" />
                                    Timer
                                </flux:button>

                                @if(config('features.sales'))
                                    <flux:button href="{{ route('invoices.create') }}" variant="ghost" class="justify-start">
                                        <flux:icon.plus-circle class="h-4 w-4 mr-2 text-blue-600" />
                                        Ny faktura
                                    </flux:button>
                                @endif

                                @if(config('features.projects'))
                                    <flux:button href="{{ route('projects.index') }}" variant="ghost" class="justify-start">
                                        <flux:icon.folder class="h-4 w-4 mr-2 text-cyan-600" />
                                        Prosjekter
                                    </flux:button>
                                @endif

                                @if(auth()->user()->is_admin || auth()->user()->is_economy)
                                    <flux:button href="{{ route('economy.dashboard') }}" variant="ghost" class="justify-start">
                                        <flux:icon.calculator class="h-4 w-4 mr-2 text-green-600" />
                                        Økonomi
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </flux:card>
                </div>
            </div>
        </flux:main>
    </div>
</x-layouts.app>
