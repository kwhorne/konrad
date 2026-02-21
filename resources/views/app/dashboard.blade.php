<x-layouts.app title="Dashboard">
    <div class="min-h-screen bg-zinc-100 dark:bg-zinc-950">
        <x-app-sidebar current="dashboard" />
        <x-app-header current="dashboard" />

        <flux:main class="bg-zinc-100 dark:bg-zinc-950">

            {{-- Header --}}
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                        God {{ $greeting }}, {{ auth()->user()->name }}
                    </h1>
                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ now()->locale('nb')->isoFormat('dddd D. MMMM YYYY') }}
                    </p>
                </div>
                <flux:button href="{{ route('timesheets.index') }}" variant="primary" icon="clock">
                    Registrer timer
                </flux:button>
            </div>

            {{-- KPI strip --}}
            @if(auth()->user()->is_admin || auth()->user()->is_economy)
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                    {{-- Timer denne uken --}}
                    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Timer denne uken</p>
                                <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">
                                    {{ number_format($myHoursThisWeek, 1, ',', ' ') }}
                                    <span class="text-sm font-normal text-zinc-400 dark:text-zinc-500">t</span>
                                </p>
                                @php
                                    $statusMap = [
                                        'draft' => ['Kladd', 'zinc'],
                                        'submitted' => ['Til godkjenning', 'amber'],
                                        'approved' => ['Godkjent', 'green'],
                                        'rejected' => ['Avvist', 'red'],
                                    ];
                                    [$label, $color] = $statusMap[$myTimesheetStatus] ?? ['—', 'zinc'];
                                @endphp
                                <flux:badge :color="$color" size="sm" class="mt-2">{{ $label }}</flux:badge>
                            </div>
                            <div class="w-9 h-9 rounded-lg bg-violet-50 dark:bg-violet-950/50 flex items-center justify-center shrink-0">
                                <flux:icon.clock class="w-4.5 h-4.5 text-violet-600 dark:text-violet-400" />
                            </div>
                        </div>
                    </div>

                    {{-- Utestående --}}
                    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Utestående</p>
                                <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">
                                    {{ number_format($unpaidInvoices, 0, ',', ' ') }}
                                    <span class="text-sm font-normal text-zinc-400 dark:text-zinc-500">kr</span>
                                </p>
                                <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">Ubetalte fakturaer</p>
                            </div>
                            <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center shrink-0">
                                <flux:icon.banknotes class="w-4.5 h-4.5 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                    </div>

                    {{-- Forfalt --}}
                    <div class="bg-white dark:bg-zinc-900 rounded-xl border {{ $overdueInvoicesCount > 0 ? 'border-red-200 dark:border-red-900/40' : 'border-zinc-200/80 dark:border-zinc-800' }} p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Forfalt</p>
                                <p class="mt-2 text-2xl font-bold {{ $overdueInvoicesCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">
                                    {{ number_format($overdueInvoices, 0, ',', ' ') }}
                                    <span class="text-sm font-normal {{ $overdueInvoicesCount > 0 ? 'text-red-400 dark:text-red-500' : 'text-zinc-400 dark:text-zinc-500' }}">kr</span>
                                </p>
                                @if($overdueInvoicesCount > 0)
                                    <flux:badge color="red" size="sm" class="mt-2">{{ $overdueInvoicesCount }} faktura{{ $overdueInvoicesCount > 1 ? 'er' : '' }}</flux:badge>
                                @else
                                    <p class="mt-2 text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                                        <flux:icon.check-circle class="w-3.5 h-3.5" /> Alt i orden
                                    </p>
                                @endif
                            </div>
                            <div class="w-9 h-9 rounded-lg {{ $overdueInvoicesCount > 0 ? 'bg-red-50 dark:bg-red-950/50' : 'bg-green-50 dark:bg-green-950/50' }} flex items-center justify-center shrink-0">
                                @if($overdueInvoicesCount > 0)
                                    <flux:icon.exclamation-triangle class="w-4.5 h-4.5 text-red-600 dark:text-red-400" />
                                @else
                                    <flux:icon.check-circle class="w-4.5 h-4.5 text-green-600 dark:text-green-400" />
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Bilag / Prosjekter / Tilbud --}}
                    @if($incomingVouchersCount > 0)
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-amber-200 dark:border-amber-900/40 p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Bilag i innboks</p>
                                    <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ $incomingVouchersCount }}
                                        <span class="text-sm font-normal text-zinc-400 dark:text-zinc-500">stk</span>
                                    </p>
                                    <a href="{{ route('economy.incoming') }}" class="mt-2 text-xs text-amber-600 dark:text-amber-400 hover:underline inline-flex items-center gap-1">
                                        Se innboks <flux:icon.arrow-right class="w-3 h-3" />
                                    </a>
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center shrink-0">
                                    <flux:icon.inbox-arrow-down class="w-4.5 h-4.5 text-amber-600 dark:text-amber-400" />
                                </div>
                            </div>
                        </div>
                    @elseif(config('features.projects') && isset($stats['activeProjects']))
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Aktive prosjekter</p>
                                    <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['activeProjects'] }}</p>
                                    @if(isset($stats['openWorkOrders']))
                                        <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">{{ $stats['openWorkOrders'] }} arbeidsordrer</p>
                                    @endif
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-cyan-50 dark:bg-cyan-950/50 flex items-center justify-center shrink-0">
                                    <flux:icon.folder class="w-4.5 h-4.5 text-cyan-600 dark:text-cyan-400" />
                                </div>
                            </div>
                        </div>
                    @elseif((auth()->user()->is_admin || auth()->user()->is_sales) && isset($stats['activeQuotes']))
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Aktive tilbud</p>
                                    <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['activeQuotes'] }}</p>
                                    @if($stats['activeQuotesValue'] > 0)
                                        <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">{{ number_format($stats['activeQuotesValue'], 0, ',', ' ') }} kr</p>
                                    @endif
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center shrink-0">
                                    <flux:icon.document-text class="w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Mine aktiviteter</p>
                                    <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">—</p>
                                    <a href="{{ route('my-activities') }}" class="mt-2 text-xs text-violet-600 dark:text-violet-400 hover:underline inline-flex items-center gap-1">
                                        Se aktiviteter <flux:icon.arrow-right class="w-3 h-3" />
                                    </a>
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-violet-50 dark:bg-violet-950/50 flex items-center justify-center shrink-0">
                                    <flux:icon.calendar-days class="w-4.5 h-4.5 text-violet-600 dark:text-violet-400" />
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Main content --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

                {{-- Left column (3/5) --}}
                <div class="lg:col-span-3 space-y-4">

                    {{-- Timer card --}}
                    @if(!(auth()->user()->is_admin || auth()->user()->is_economy))
                        @php
                            $statusMap = [
                                'draft' => ['Kladd', 'zinc'],
                                'submitted' => ['Til godkjenning', 'amber'],
                                'approved' => ['Godkjent', 'green'],
                                'rejected' => ['Avvist', 'red'],
                            ];
                            [$label, $color] = $statusMap[$myTimesheetStatus] ?? ['—', 'zinc'];
                        @endphp
                        <div class="bg-gradient-to-br from-violet-600 to-indigo-700 rounded-xl p-5 shadow-lg shadow-violet-500/10">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-white/70">Mine timer denne uken</p>
                                    <div class="flex items-baseline gap-2 mt-1">
                                        <span class="text-4xl font-bold text-white tracking-tight">{{ number_format($myHoursThisWeek, 1, ',', ' ') }}</span>
                                        <span class="text-lg text-white/60">timer</span>
                                    </div>
                                    <flux:badge :color="$color" class="mt-3 bg-white/15 border-white/20 text-white">{{ $label }}</flux:badge>
                                </div>
                                <flux:button href="{{ route('timesheets.index') }}" class="bg-white/15 hover:bg-white/25 border-white/20 text-white">
                                    <flux:icon.clock class="w-4 h-4" />
                                    Registrer
                                </flux:button>
                            </div>
                        </div>
                    @endif

                    {{-- Timesedler til godkjenning --}}
                    @if((auth()->user()->is_admin || auth()->user()->is_economy) && $pendingTimesheetsCount > 0)
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden">
                            <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center">
                                        <flux:icon.clock class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Timer til godkjenning</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $pendingTimesheetsCount }} {{ $pendingTimesheetsCount === 1 ? 'timeseddel' : 'timesedler' }} venter</p>
                                    </div>
                                </div>
                                <flux:button href="{{ route('timesheets.approval') }}" variant="primary" size="sm">Behandle</flux:button>
                            </div>
                            @if($pendingTimesheets->count() > 0)
                                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @foreach($pendingTimesheets->take(3) as $timesheet)
                                        <div class="flex items-center justify-between px-5 py-3">
                                            <div class="flex items-center gap-3">
                                                <flux:avatar size="sm" name="{{ $timesheet->user->name }}" />
                                                <div>
                                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $timesheet->user->name }}</p>
                                                    <p class="text-xs text-zinc-400 dark:text-zinc-500">Uke {{ $timesheet->week_start->weekOfYear }}</p>
                                                </div>
                                            </div>
                                            <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ number_format($timesheet->total_hours, 1, ',', ' ') }} t</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Prosjekter + Arbeidsordrer --}}
                    @if(config('features.projects') || config('features.work_orders'))
                        @if(isset($stats['activeProjects']) || isset($stats['openWorkOrders']))
                            <div class="grid grid-cols-2 gap-3">
                                @if(config('features.projects') && isset($stats['activeProjects']))
                                    <a href="{{ route('projects.index') }}" class="group bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4 hover:border-cyan-300 dark:hover:border-cyan-700 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Aktive prosjekter</p>
                                                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $stats['activeProjects'] }}</p>
                                            </div>
                                            <div class="w-9 h-9 rounded-lg bg-cyan-50 dark:bg-cyan-950/50 flex items-center justify-center">
                                                <flux:icon.folder class="w-4.5 h-4.5 text-cyan-600 dark:text-cyan-400" />
                                            </div>
                                        </div>
                                    </a>
                                @endif
                                @if(config('features.work_orders') && isset($stats['openWorkOrders']))
                                    <a href="{{ route('work-orders.index') }}" class="group bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4 hover:border-teal-300 dark:hover:border-teal-700 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Arbeidsordrer</p>
                                                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $stats['openWorkOrders'] }}</p>
                                            </div>
                                            <div class="w-9 h-9 rounded-lg bg-teal-50 dark:bg-teal-950/50 flex items-center justify-center">
                                                <flux:icon.clipboard-document-list class="w-4.5 h-4.5 text-teal-600 dark:text-teal-400" />
                                            </div>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        @endif
                    @endif

                    {{-- Tilbud og ordrer (salg) --}}
                    @if((auth()->user()->is_admin || auth()->user()->is_sales) && config('features.sales'))
                        @if(isset($stats['activeQuotes']) || isset($stats['openOrders']))
                            <div class="grid grid-cols-2 gap-3">
                                @if(isset($stats['activeQuotes']))
                                    <a href="{{ route('quotes.index') }}" class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Aktive tilbud</p>
                                                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $stats['activeQuotes'] }}</p>
                                                @if($stats['activeQuotesValue'] > 0)
                                                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ number_format($stats['activeQuotesValue'], 0, ',', ' ') }} kr</p>
                                                @endif
                                            </div>
                                            <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center">
                                                <flux:icon.document-text class="w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400" />
                                            </div>
                                        </div>
                                    </a>
                                @endif
                                @if(isset($stats['openOrders']))
                                    <a href="{{ route('orders.index') }}" class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4 hover:border-purple-300 dark:hover:border-purple-700 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Åpne ordrer</p>
                                                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $stats['openOrders'] }}</p>
                                            </div>
                                            <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-950/50 flex items-center justify-center">
                                                <flux:icon.shopping-cart class="w-4.5 h-4.5 text-purple-600 dark:text-purple-400" />
                                            </div>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Right column (2/5) --}}
                <div class="lg:col-span-2 space-y-4">

                    {{-- Siste fakturaer --}}
                    @if($recentInvoices->count() > 0)
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden">
                            <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">Siste fakturaer</p>
                                <a href="{{ route('invoices.index') }}" class="text-xs text-violet-600 dark:text-violet-400 hover:underline">Se alle</a>
                            </div>
                            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($recentInvoices as $invoice)
                                    <a href="{{ route('invoices.show', $invoice) }}" class="flex items-center justify-between px-5 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $invoice->invoice_number }}</span>
                                                <flux:badge size="sm" :color="match($invoice->invoiceStatus?->code ?? '') {
                                                    'paid' => 'green',
                                                    'sent' => 'blue',
                                                    'overdue' => 'red',
                                                    'partially_paid' => 'amber',
                                                    default => 'zinc'
                                                }">{{ $invoice->invoiceStatus?->name ?? '—' }}</flux:badge>
                                            </div>
                                            <p class="text-xs text-zinc-400 dark:text-zinc-500 truncate mt-0.5">
                                                {{ $invoice->contact?->company_name ?? $invoice->customer_name ?? 'Ingen kunde' }}
                                            </p>
                                        </div>
                                        <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 ml-3 shrink-0">
                                            {{ number_format($invoice->total, 0, ',', ' ') }} kr
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Snarveier --}}
                    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-5">
                        <p class="text-xs font-semibold tracking-wide text-zinc-500 dark:text-zinc-400 uppercase mb-4">Snarveier</p>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('timesheets.index') }}" class="flex flex-col items-center gap-2 rounded-lg p-3 bg-zinc-50 dark:bg-zinc-800/60 hover:bg-violet-50 dark:hover:bg-violet-950/40 hover:border-violet-200 dark:hover:border-violet-800 border border-transparent transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center group-hover:bg-violet-200 dark:group-hover:bg-violet-900/60 transition-colors">
                                    <flux:icon.clock class="w-4 h-4 text-violet-600 dark:text-violet-400" />
                                </div>
                                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Timer</span>
                            </a>

                            @if(config('features.sales'))
                                <a href="{{ route('invoices.create') }}" class="flex flex-col items-center gap-2 rounded-lg p-3 bg-zinc-50 dark:bg-zinc-800/60 hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:border-blue-200 dark:hover:border-blue-800 border border-transparent transition-all group">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-900/60 transition-colors">
                                        <flux:icon.plus-circle class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Ny faktura</span>
                                </a>
                            @endif

                            @if(config('features.projects'))
                                <a href="{{ route('projects.index') }}" class="flex flex-col items-center gap-2 rounded-lg p-3 bg-zinc-50 dark:bg-zinc-800/60 hover:bg-cyan-50 dark:hover:bg-cyan-950/40 hover:border-cyan-200 dark:hover:border-cyan-800 border border-transparent transition-all group">
                                    <div class="w-8 h-8 rounded-lg bg-cyan-100 dark:bg-cyan-900/40 flex items-center justify-center group-hover:bg-cyan-200 dark:group-hover:bg-cyan-900/60 transition-colors">
                                        <flux:icon.folder class="w-4 h-4 text-cyan-600 dark:text-cyan-400" />
                                    </div>
                                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Prosjekter</span>
                                </a>
                            @endif

                            @if(auth()->user()->is_admin || auth()->user()->is_economy)
                                <a href="{{ route('economy.dashboard') }}" class="flex flex-col items-center gap-2 rounded-lg p-3 bg-zinc-50 dark:bg-zinc-800/60 hover:bg-green-50 dark:hover:bg-green-950/40 hover:border-green-200 dark:hover:border-green-800 border border-transparent transition-all group">
                                    <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/40 flex items-center justify-center group-hover:bg-green-200 dark:group-hover:bg-green-900/60 transition-colors">
                                        <flux:icon.calculator class="w-4 h-4 text-green-600 dark:text-green-400" />
                                    </div>
                                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Økonomi</span>
                                </a>
                            @endif

                            <a href="{{ route('my-activities') }}" class="flex flex-col items-center gap-2 rounded-lg p-3 bg-zinc-50 dark:bg-zinc-800/60 hover:bg-amber-50 dark:hover:bg-amber-950/40 hover:border-amber-200 dark:hover:border-amber-800 border border-transparent transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center group-hover:bg-amber-200 dark:group-hover:bg-amber-900/60 transition-colors">
                                    <flux:icon.calendar-days class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                </div>
                                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Aktiviteter</span>
                            </a>

                            @if(auth()->user()->is_admin || auth()->user()->is_sales)
                                <a href="{{ route('contacts.index') }}" class="flex flex-col items-center gap-2 rounded-lg p-3 bg-zinc-50 dark:bg-zinc-800/60 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:border-rose-200 dark:hover:border-rose-800 border border-transparent transition-all group">
                                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center group-hover:bg-rose-200 dark:group-hover:bg-rose-900/60 transition-colors">
                                        <flux:icon.users class="w-4 h-4 text-rose-600 dark:text-rose-400" />
                                    </div>
                                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">CRM</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </flux:main>
    </div>
</x-layouts.app>
