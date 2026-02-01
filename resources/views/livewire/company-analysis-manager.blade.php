<div>
    @if(!$hasAnalysis)
        {{-- Initial State - Run Analysis --}}
        <div class="flex flex-col items-center justify-center py-16">
            <div class="relative mb-8">
                <div class="absolute inset-0 bg-gradient-to-r from-violet-600 to-indigo-600 rounded-full blur-2xl opacity-20 animate-pulse"></div>
                <div class="relative w-32 h-32 bg-gradient-to-br from-violet-500 to-indigo-600 rounded-full flex items-center justify-center shadow-2xl">
                    <flux:icon.sparkles class="w-16 h-16 text-white" />
                </div>
            </div>

            <flux:heading size="xl" class="text-zinc-900 dark:text-white mb-3">
                Selskapsanalyse
            </flux:heading>

            <flux:text class="text-zinc-600 dark:text-zinc-400 text-center max-w-lg mb-8">
                Få en komplett analyse av selskapets økonomiske helse, med styrker, svakheter, muligheter og konkrete anbefalinger basert på dine regnskapsdata.
            </flux:text>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10 max-w-2xl">
                <div class="flex items-center gap-3 p-4 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center shrink-0">
                        <flux:icon.chart-bar class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="text-sm">
                        <div class="font-medium text-zinc-900 dark:text-white">Økonomisk helse</div>
                        <div class="text-zinc-500 dark:text-zinc-400">Score og status</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-4 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center shrink-0">
                        <flux:icon.light-bulb class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="text-sm">
                        <div class="font-medium text-zinc-900 dark:text-white">Innsikt</div>
                        <div class="text-zinc-500 dark:text-zinc-400">Styrker og svakheter</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-4 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center shrink-0">
                        <flux:icon.arrow-trending-up class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                    </div>
                    <div class="text-sm">
                        <div class="font-medium text-zinc-900 dark:text-white">Anbefalinger</div>
                        <div class="text-zinc-500 dark:text-zinc-400">Konkrete tiltak</div>
                    </div>
                </div>
            </div>

            @if($error)
                <flux:callout variant="danger" icon="exclamation-triangle" class="mb-6 max-w-lg">
                    <flux:callout.heading>Feil ved analyse</flux:callout.heading>
                    <flux:callout.text>{{ $error }}</flux:callout.text>
                </flux:callout>
            @endif

            <flux:button
                wire:click="runAnalysis"
                variant="primary"
                class="bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 shadow-lg shadow-violet-500/25 px-6 py-3"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="runAnalysis" class="flex items-center gap-2">
                    <flux:icon.sparkles class="w-5 h-5" />
                    Start analyse
                </span>
                <span wire:loading wire:target="runAnalysis" class="flex items-center gap-2">
                    <flux:icon.arrow-path class="w-5 h-5 animate-spin" />
                    Analyserer...
                </span>
            </flux:button>

            <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-4">
                Analysen tar vanligvis 10-30 sekunder
            </flux:text>
        </div>
    @else
        {{-- Analysis Results --}}
        <div class="space-y-8">
            {{-- Header with Health Score --}}
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <flux:heading size="xl" class="text-zinc-900 dark:text-white mb-2">
                        Selskapsanalyse
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        {{ $financialData['company']['name'] ?? 'Ukjent selskap' }}
                        @if($generatedAt)
                            <span class="text-zinc-400 dark:text-zinc-500">•</span>
                            Generert {{ \Carbon\Carbon::parse($generatedAt)->format('d.m.Y H:i') }}
                        @endif
                    </flux:text>
                </div>

                <div class="flex items-center gap-4">
                    <flux:button wire:click="runAnalysis" variant="ghost" size="sm">
                        <flux:icon.arrow-path class="w-4 h-4 mr-2" wire:loading.class="animate-spin" wire:target="runAnalysis" />
                        Kjør ny analyse
                    </flux:button>
                </div>
            </div>

            {{-- Health Score Card --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-{{ $this->healthColor }}-500 to-{{ $this->healthColor }}-600 rounded-2xl p-8 text-white shadow-xl">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

                <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex-1">
                        <div class="text-white/80 text-sm font-medium mb-2">Økonomisk helse</div>
                        <div class="flex items-baseline gap-3 mb-4">
                            <span class="text-6xl font-bold">{{ $analysis['health_score'] ?? 0 }}</span>
                            <span class="text-2xl text-white/70">/100</span>
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-sm font-medium">
                            @if(($analysis['health_score'] ?? 0) >= 60)
                                <flux:icon.check-circle class="w-4 h-4" />
                            @else
                                <flux:icon.exclamation-triangle class="w-4 h-4" />
                            @endif
                            {{ $analysis['health_label'] ?? 'Ukjent' }}
                        </div>
                    </div>

                    <div class="flex-1 max-w-xl">
                        <p class="text-white/90 text-lg leading-relaxed">
                            {{ $analysis['summary'] ?? 'Ingen oppsummering tilgjengelig.' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Key Metrics --}}
            @if(!empty($analysis['key_metrics']))
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($analysis['key_metrics'] as $key => $metric)
                        @php
                            $labels = [
                                'liquidity' => 'Likviditet',
                                'profitability' => 'Lønnsomhet',
                                'growth' => 'Vekst',
                                'receivables' => 'Kundefordringer',
                            ];
                            $icons = [
                                'liquidity' => 'banknotes',
                                'profitability' => 'chart-bar',
                                'growth' => 'arrow-trending-up',
                                'receivables' => 'users',
                            ];
                            $statusColors = [
                                'good' => 'green',
                                'warning' => 'yellow',
                                'critical' => 'red',
                            ];
                            $color = $statusColors[$metric['status'] ?? 'good'] ?? 'zinc';
                        @endphp
                        <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="w-8 h-8 bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 rounded-lg flex items-center justify-center">
                                        <flux:icon :name="$icons[$key] ?? 'chart-bar'" class="w-4 h-4 text-{{ $color }}-600 dark:text-{{ $color }}-400" />
                                    </div>
                                    <flux:badge size="sm" :color="$color">
                                        {{ $metric['status'] === 'good' ? 'OK' : ($metric['status'] === 'warning' ? 'Advarsel' : 'Kritisk') }}
                                    </flux:badge>
                                </div>
                                <div class="text-2xl font-bold text-zinc-900 dark:text-white mb-1">
                                    {{ $metric['value'] ?? '-' }}
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $labels[$key] ?? $key }}
                                </div>
                                @if(!empty($metric['comment']))
                                    <div class="text-xs text-zinc-400 dark:text-zinc-500 mt-2">
                                        {{ $metric['comment'] }}
                                    </div>
                                @endif
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            @endif

            {{-- Strengths & Weaknesses --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Strengths --}}
                <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                                <flux:icon.check-circle class="w-5 h-5 text-green-600 dark:text-green-400" />
                            </div>
                            <flux:heading size="lg" class="text-zinc-900 dark:text-white">Styrker</flux:heading>
                        </div>

                        <div class="space-y-4">
                            @forelse($analysis['strengths'] ?? [] as $strength)
                                <div class="p-4 bg-green-50 dark:bg-zinc-800 rounded-xl border border-green-200 dark:border-zinc-700">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="font-semibold text-green-900 dark:text-green-300 mb-1">
                                                {{ $strength['title'] }}
                                            </div>
                                            <div class="text-sm text-zinc-700 dark:text-zinc-300">
                                                {{ $strength['description'] }}
                                            </div>
                                        </div>
                                        @if(!empty($strength['metric']))
                                            <div class="shrink-0 px-3 py-1 bg-green-100 dark:bg-green-900/40 rounded-lg text-sm font-semibold text-green-700 dark:text-green-300">
                                                {{ $strength['metric'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-zinc-500 dark:text-zinc-400 italic">
                                    Ingen styrker identifisert
                                </div>
                            @endforelse
                        </div>
                    </div>
                </flux:card>

                {{-- Weaknesses --}}
                <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
                                <flux:icon.exclamation-triangle class="w-5 h-5 text-red-600 dark:text-red-400" />
                            </div>
                            <flux:heading size="lg" class="text-zinc-900 dark:text-white">Svakheter</flux:heading>
                        </div>

                        <div class="space-y-4">
                            @forelse($analysis['weaknesses'] ?? [] as $weakness)
                                <div class="p-4 bg-red-50 dark:bg-zinc-800 rounded-xl border border-red-200 dark:border-zinc-700">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="font-semibold text-red-900 dark:text-red-300 mb-1">
                                                {{ $weakness['title'] }}
                                            </div>
                                            <div class="text-sm text-zinc-700 dark:text-zinc-300">
                                                {{ $weakness['description'] }}
                                            </div>
                                        </div>
                                        @if(!empty($weakness['metric']))
                                            <div class="shrink-0 px-3 py-1 bg-red-100 dark:bg-red-900/40 rounded-lg text-sm font-semibold text-red-700 dark:text-red-300">
                                                {{ $weakness['metric'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-zinc-500 dark:text-zinc-400 italic">
                                    Ingen svakheter identifisert
                                </div>
                            @endforelse
                        </div>
                    </div>
                </flux:card>
            </div>

            {{-- Opportunities & Risks --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Opportunities --}}
                <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                                <flux:icon.light-bulb class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <flux:heading size="lg" class="text-zinc-900 dark:text-white">Muligheter</flux:heading>
                        </div>

                        <div class="space-y-4">
                            @forelse($analysis['opportunities'] ?? [] as $opportunity)
                                <div class="p-4 bg-blue-50 dark:bg-zinc-800 rounded-xl border border-blue-200 dark:border-zinc-700">
                                    <div class="font-semibold text-blue-900 dark:text-blue-300 mb-1">
                                        {{ $opportunity['title'] }}
                                    </div>
                                    <div class="text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $opportunity['description'] }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-zinc-500 dark:text-zinc-400 italic">
                                    Ingen muligheter identifisert
                                </div>
                            @endforelse
                        </div>
                    </div>
                </flux:card>

                {{-- Risks --}}
                <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
                                <flux:icon.shield-exclamation class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                            </div>
                            <flux:heading size="lg" class="text-zinc-900 dark:text-white">Risikoer</flux:heading>
                        </div>

                        <div class="space-y-4">
                            @forelse($analysis['risks'] ?? [] as $risk)
                                <div class="p-4 bg-orange-50 dark:bg-zinc-800 rounded-xl border border-orange-200 dark:border-zinc-700">
                                    <div class="font-semibold text-orange-900 dark:text-orange-300 mb-1">
                                        {{ $risk['title'] }}
                                    </div>
                                    <div class="text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $risk['description'] }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-zinc-500 dark:text-zinc-400 italic">
                                    Ingen risikoer identifisert
                                </div>
                            @endforelse
                        </div>
                    </div>
                </flux:card>
            </div>

            {{-- Recommendations --}}
            @if(!empty($analysis['recommendations']))
                <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-xl flex items-center justify-center">
                                <flux:icon.clipboard-document-check class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                            </div>
                            <flux:heading size="lg" class="text-zinc-900 dark:text-white">Anbefalinger</flux:heading>
                        </div>

                        <div class="space-y-4">
                            @foreach($analysis['recommendations'] as $recommendation)
                                @php
                                    $priorityColors = [
                                        'high' => 'red',
                                        'medium' => 'yellow',
                                        'low' => 'green',
                                    ];
                                    $priorityLabels = [
                                        'high' => 'Høy',
                                        'medium' => 'Medium',
                                        'low' => 'Lav',
                                    ];
                                    $color = $priorityColors[$recommendation['priority'] ?? 'medium'] ?? 'zinc';
                                @endphp
                                <div class="p-5 bg-zinc-50 dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                                    <div class="flex items-start gap-4">
                                        <div class="shrink-0 mt-1">
                                            <flux:badge size="sm" :color="$color">
                                                {{ $priorityLabels[$recommendation['priority'] ?? 'medium'] ?? 'Medium' }} prioritet
                                            </flux:badge>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-semibold text-zinc-900 dark:text-white mb-2">
                                                {{ $recommendation['title'] }}
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">
                                                {{ $recommendation['description'] }}
                                            </div>
                                            @if(!empty($recommendation['expected_impact']))
                                                <div class="flex items-center gap-2 text-sm">
                                                    <flux:icon.arrow-trending-up class="w-4 h-4 text-green-500" />
                                                    <span class="text-green-600 dark:text-green-400 font-medium">
                                                        {{ $recommendation['expected_impact'] }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </flux:card>
            @endif

            {{-- Footer --}}
            <div class="flex items-center justify-center pt-4">
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 flex items-center gap-2">
                    <flux:icon.sparkles class="w-4 h-4" />
                    Analysen er generert med AI og bør verifiseres av en regnskapsfører
                </flux:text>
            </div>
        </div>
    @endif
</div>
