<x-layouts.app title="Abonnement">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="subscription" />
        <x-app-header current="subscription" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <!-- Header Section -->
            <div class="mb-8">
                <flux:heading size="2xl" level="1" class="text-zinc-900 dark:text-white mb-2">
                    Abonnement og moduler
                </flux:heading>
                <flux:text class="text-lg text-zinc-600 dark:text-zinc-400">
                    Administrer premium-moduler for {{ $company->name }}
                </flux:text>
            </div>

            @if(session('success'))
                <flux:callout variant="success" class="mb-6">
                    {{ session('success') }}
                </flux:callout>
            @endif

            @if(session('error'))
                <flux:callout variant="danger" class="mb-6">
                    {{ session('error') }}
                </flux:callout>
            @endif

            <!-- Premium Modules Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @foreach($premiumModules as $module)
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white">
                                        {{ $module->name }}
                                    </flux:heading>
                                    <flux:text class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">
                                        {{ $module->price_formatted }}
                                    </flux:text>
                                </div>
                                @if($module->is_enabled_for_company)
                                    <flux:badge variant="success">Aktiv</flux:badge>
                                @endif
                            </div>

                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                                {{ $module->description }}
                            </flux:text>

                            @if($module->is_enabled_for_company)
                                @if($module->company_module?->enabledByAdmin())
                                    <flux:badge variant="outline" size="sm" class="mb-4">
                                        Aktivert av administrator
                                    </flux:badge>
                                @elseif($module->company_module?->enabledByStripe())
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">
                                        Abonnement aktivt
                                    </flux:text>
                                @endif
                            @else
                                @if($module->stripe_price_id)
                                    <form action="{{ route('subscription.checkout', $module) }}" method="POST">
                                        @csrf
                                        <flux:button type="submit" variant="primary" class="w-full">
                                            Abonner
                                        </flux:button>
                                    </form>
                                @else
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Kontakt oss for aktivering
                                    </flux:text>
                                @endif
                            @endif
                        </div>
                    </flux:card>
                @endforeach
            </div>

            <!-- Billing Portal Link -->
            @if($company->hasStripeId())
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-2">
                            Administrer betalinger
                        </flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400 mb-4">
                            Oppdater betalingsmetode, se fakturaer og administrer abonnementer.
                        </flux:text>
                        <a href="{{ route('subscription.manage') }}">
                            <flux:button variant="outline">
                                Til betalingsportalen
                            </flux:button>
                        </a>
                    </div>
                </flux:card>
            @endif
        </flux:main>
    </div>
</x-layouts.app>
