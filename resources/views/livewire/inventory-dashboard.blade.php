<div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="p-4">
                <flux:text class="text-xs font-medium text-zinc-500">Lagervarer</flux:text>
                <flux:heading size="xl" class="mt-1">{{ $stockedProductsCount }}</flux:heading>
                <flux:text class="text-xs text-zinc-400">Lagerforte produkter</flux:text>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="p-4">
                <flux:text class="text-xs font-medium text-zinc-500">Total verdi</flux:text>
                <flux:heading size="xl" class="mt-1">{{ number_format($totalValue, 0, ',', ' ') }}</flux:heading>
                <flux:text class="text-xs text-zinc-400">Vektet gjennomsnitt</flux:text>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="p-4">
                <flux:text class="text-xs font-medium text-zinc-500">Apne bestillinger</flux:text>
                <flux:heading size="xl" class="mt-1">{{ $openPurchaseOrders }}</flux:heading>
                <flux:text class="text-xs text-zinc-400">Innkjopsordrer</flux:text>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="p-4">
                <flux:text class="text-xs font-medium text-zinc-500">Under bestillingspunkt</flux:text>
                <flux:heading size="xl" class="mt-1 {{ $belowReorderPoint > 0 ? 'text-amber-600' : '' }}">{{ $belowReorderPoint }}</flux:heading>
                <flux:text class="text-xs text-zinc-400">Varer som ma bestilles</flux:text>
            </div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="p-4">
                <flux:heading size="base" class="mb-4">Siste bevegelser</flux:heading>
                @if($recentTransactions->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentTransactions as $transaction)
                            <div class="flex justify-between items-center py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                                <div>
                                    <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $transaction->product?->name }}</flux:text>
                                    <flux:text class="text-xs text-zinc-500">{{ $transaction->stockLocation?->name }} - {{ $transaction->transaction_date->format('d.m.Y') }}</flux:text>
                                </div>
                                <flux:badge color="{{ $transaction->quantity > 0 ? 'green' : 'red' }}">
                                    {{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format($transaction->quantity, 0, ',', ' ') }}
                                </flux:badge>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <flux:button href="{{ route('inventory.transactions') }}" variant="ghost" size="sm" class="w-full">
                            Se alle transaksjoner
                        </flux:button>
                    </div>
                @else
                    <flux:text class="text-zinc-500 text-sm">Ingen bevegelser enna</flux:text>
                @endif
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="p-4">
                <flux:heading size="base" class="mb-4">Siste varemottak</flux:heading>
                @if($recentReceipts->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentReceipts as $receipt)
                            <a href="{{ route('purchasing.goods-receipts.show', $receipt) }}" class="flex justify-between items-center py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0 hover:bg-zinc-50 dark:hover:bg-zinc-800 -mx-2 px-2 rounded transition-colors">
                                <div>
                                    <flux:text class="font-mono text-indigo-600 dark:text-indigo-400">{{ $receipt->receipt_number }}</flux:text>
                                    <flux:text class="text-xs text-zinc-500">{{ $receipt->contact?->company_name }} - {{ $receipt->receipt_date->format('d.m.Y') }}</flux:text>
                                </div>
                                <flux:badge color="{{ $receipt->status_color }}">{{ $receipt->status_label }}</flux:badge>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <flux:button href="{{ route('purchasing.goods-receipts.index') }}" variant="ghost" size="sm" class="w-full">
                            Se alle varemottak
                        </flux:button>
                    </div>
                @else
                    <flux:text class="text-zinc-500 text-sm">Ingen varemottak enna</flux:text>
                @endif
            </div>
        </flux:card>
    </div>
</div>
