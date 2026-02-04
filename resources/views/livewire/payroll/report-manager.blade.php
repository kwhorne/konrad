<div>
    <!-- Year Filter -->
    <div class="mb-6">
        <flux:select wire:model.live="selectedYear" class="w-40">
            @for($y = now()->year; $y >= now()->year - 3; $y--)
                <flux:select.option value="{{ $y }}">{{ $y }}</flux:select.option>
            @endfor
        </flux:select>
    </div>

    <!-- YTD Summary -->
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 mb-6">
        <div class="p-6">
            <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-4">Arsoversikt {{ $selectedYear }}</flux:heading>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="text-sm text-zinc-500 mb-1">Bruttolonn</div>
                    <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['bruttolonn'], 0, ',', ' ') }}</div>
                </div>
                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="text-sm text-zinc-500 mb-1">Forskuddstrekk</div>
                    <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['forskuddstrekk'], 0, ',', ' ') }}</div>
                </div>
                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="text-sm text-zinc-500 mb-1">Nettolonn</div>
                    <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['nettolonn'], 0, ',', ' ') }}</div>
                </div>
                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="text-sm text-zinc-500 mb-1">Arb.giveravgift</div>
                    <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['arbeidsgiveravgift'], 0, ',', ' ') }}</div>
                </div>
                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="text-sm text-zinc-500 mb-1">OTP</div>
                    <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['otp'], 0, ',', ' ') }}</div>
                </div>
                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="text-sm text-zinc-500 mb-1">Feriep.grunnlag</div>
                    <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($ytdTotals['feriepenger_grunnlag'], 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>
    </flux:card>

    <!-- Monthly Breakdown -->
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
            <flux:heading size="lg" class="text-zinc-900 dark:text-white">Manedsvis oversikt</flux:heading>
        </div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Maned</flux:table.column>
                <flux:table.column class="text-right">Bruttolonn</flux:table.column>
                <flux:table.column class="text-right">Forskuddstrekk</flux:table.column>
                <flux:table.column class="text-right">Nettolonn</flux:table.column>
                <flux:table.column class="text-right">Arb.giveravgift</flux:table.column>
                <flux:table.column class="text-right">OTP</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @php
                    $months = ['Januar', 'Februar', 'Mars', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Desember'];
                @endphp
                @foreach($months as $index => $monthName)
                    @php
                        $monthNum = $index + 1;
                        $run = $monthlyData->firstWhere('month', $monthNum);
                    @endphp
                    <flux:table.row class="{{ !$run ? 'opacity-50' : '' }}">
                        <flux:table.cell class="font-medium">{{ $monthName }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ $run ? number_format($run->total_bruttolonn, 0, ',', ' ') : '-' }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ $run ? number_format($run->total_forskuddstrekk, 0, ',', ' ') : '-' }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ $run ? number_format($run->total_nettolonn, 0, ',', ' ') : '-' }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ $run ? number_format($run->total_arbeidsgiveravgift, 0, ',', ' ') : '-' }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ $run ? number_format($run->total_otp, 0, ',', ' ') : '-' }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
                <flux:table.row class="font-bold bg-zinc-50 dark:bg-zinc-800">
                    <flux:table.cell>Totalt</flux:table.cell>
                    <flux:table.cell class="text-right">{{ number_format($ytdTotals['bruttolonn'], 0, ',', ' ') }}</flux:table.cell>
                    <flux:table.cell class="text-right">{{ number_format($ytdTotals['forskuddstrekk'], 0, ',', ' ') }}</flux:table.cell>
                    <flux:table.cell class="text-right">{{ number_format($ytdTotals['nettolonn'], 0, ',', ' ') }}</flux:table.cell>
                    <flux:table.cell class="text-right">{{ number_format($ytdTotals['arbeidsgiveravgift'], 0, ',', ' ') }}</flux:table.cell>
                    <flux:table.cell class="text-right">{{ number_format($ytdTotals['otp'], 0, ',', ' ') }}</flux:table.cell>
                </flux:table.row>
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
