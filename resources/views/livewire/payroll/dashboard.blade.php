<div>
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <flux:icon.users class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <flux:text class="text-zinc-500 dark:text-zinc-400 text-sm">Ansatte</flux:text>
                        <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $employeeCount }}</div>
                    </div>
                </div>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                        <flux:icon.clock class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <flux:text class="text-zinc-500 dark:text-zinc-400 text-sm">Ventende kjøringer</flux:text>
                        <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $pendingRuns }}</div>
                    </div>
                </div>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <flux:icon.banknotes class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <flux:text class="text-zinc-500 dark:text-zinc-400 text-sm">Hittil i år (brutto)</flux:text>
                        <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['bruttolonn'], 0, ',', ' ') }}</div>
                    </div>
                </div>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <flux:icon.building-office class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <flux:text class="text-zinc-500 dark:text-zinc-400 text-sm">Arb.giveravgift (YTD)</flux:text>
                        <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['arbeidsgiveravgift'], 0, ',', ' ') }}</div>
                    </div>
                </div>
            </div>
        </flux:card>
    </div>

    <!-- Quick Actions and Recent Runs -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Quick Actions -->
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-4">Hurtighandlinger</flux:heading>
                <div class="space-y-3">
                    <a href="{{ route('payroll.runs') }}" class="flex items-center gap-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                        <flux:icon.plus class="w-5 h-5 text-emerald-500" />
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-white">Ny lønnskjøring</div>
                            <div class="text-xs text-zinc-500">Opprett manuell lønnskjøring</div>
                        </div>
                    </a>
                    <a href="{{ route('payroll.employees') }}" class="flex items-center gap-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                        <flux:icon.user-plus class="w-5 h-5 text-blue-500" />
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-white">Legg til ansatt</div>
                            <div class="text-xs text-zinc-500">Registrer ny ansatt i lønnssystemet</div>
                        </div>
                    </a>
                    <a href="{{ route('payroll.a-melding') }}" class="flex items-center gap-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                        <flux:icon.cloud-arrow-up class="w-5 h-5 text-teal-500" />
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-white">A-melding</div>
                            <div class="text-xs text-zinc-500">Generer og send A-melding</div>
                        </div>
                    </a>
                </div>
            </div>
        </flux:card>

        <!-- Recent Payroll Runs -->
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg" class="text-zinc-900 dark:text-white">Siste lønnskjøringer</flux:heading>
                    <a href="{{ route('payroll.runs') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Se alle</a>
                </div>
                @if($recentRuns->isEmpty())
                    <div class="text-center py-8">
                        <flux:icon.calculator class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-600 mb-3" />
                        <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen lønnskjøringer ennå</flux:text>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($recentRuns as $run)
                            <a href="{{ route('payroll.runs.show', $run) }}" class="flex items-center justify-between p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                <div>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $run->period_label }}</div>
                                    <div class="text-sm text-zinc-500">{{ number_format($run->total_bruttolonn, 0, ',', ' ') }} kr</div>
                                </div>
                                <flux:badge size="sm" color="{{ $run->status_color }}">{{ $run->status_label }}</flux:badge>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </flux:card>
    </div>

    <!-- YTD Summary -->
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-4">Årssammendrag {{ $currentYear }}</flux:heading>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Bruttolønn</div>
                    <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['bruttolonn'], 0, ',', ' ') }}</div>
                </div>
                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Forskuddstrekk</div>
                    <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['forskuddstrekk'], 0, ',', ' ') }}</div>
                </div>
                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Nettolønn</div>
                    <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['nettolonn'], 0, ',', ' ') }}</div>
                </div>
                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Arb.giveravgift</div>
                    <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['arbeidsgiveravgift'], 0, ',', ' ') }}</div>
                </div>
                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">OTP</div>
                    <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['otp'], 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>
    </flux:card>
</div>
