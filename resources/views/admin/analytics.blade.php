<x-layouts.app title="Admin - Analyse">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="analytics" />
        <x-admin-header current="analytics" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">Analyseoversikt</flux:heading>
            <flux:text class="mb-6 mt-2 text-base text-zinc-600 dark:text-zinc-400">Oversikt over inntekter, selskaper og brukere</flux:text>
            <flux:separator variant="subtle" />

            {{-- Row 1: Revenue --}}
            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-green-100 dark:bg-green-900/30">
                            <flux:icon.banknotes class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">MRR</flux:text>
                            <flux:heading size="xl">{{ number_format($mrr / 100, 0, ',', ' ') }} kr</flux:heading>
                        </div>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-blue-100 dark:bg-blue-900/30">
                            <flux:icon.chart-bar class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">ARR</flux:text>
                            <flux:heading size="xl">{{ number_format($arr / 100, 0, ',', ' ') }} kr</flux:heading>
                        </div>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-violet-100 dark:bg-violet-900/30">
                            <flux:icon.puzzle-piece class="w-6 h-6 text-violet-600 dark:text-violet-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Aktive premiumabonnementer</flux:text>
                            <flux:heading size="xl">{{ $activePremiumSubscriptions }}</flux:heading>
                        </div>
                    </div>
                </flux:card>
            </div>

            {{-- Row 2: Companies --}}
            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-4">
                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-indigo-100 dark:bg-indigo-900/30">
                            <flux:icon.building-office-2 class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Totalt selskaper</flux:text>
                            <flux:heading size="xl">{{ $totalCompanies }}</flux:heading>
                        </div>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-green-100 dark:bg-green-900/30">
                            <flux:icon.check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Aktive selskaper</flux:text>
                            <flux:heading size="xl">{{ $activeCompanies }}</flux:heading>
                        </div>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-blue-100 dark:bg-blue-900/30">
                            <flux:icon.plus-circle class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Nye denne måneden</flux:text>
                            <flux:heading size="xl">{{ $newCompaniesThisMonth }}</flux:heading>
                        </div>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-red-100 dark:bg-red-900/30">
                            <flux:icon.arrow-trending-down class="w-6 h-6 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Churn denne måneden</flux:text>
                            <flux:heading size="xl">{{ $churnThisMonth }}</flux:heading>
                        </div>
                    </div>
                </flux:card>
            </div>

            {{-- Row 3: Users --}}
            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon.users class="w-6 h-6 text-zinc-600 dark:text-zinc-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Totalt brukere</flux:text>
                            <flux:heading size="xl">{{ $totalUsers }}</flux:heading>
                        </div>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-yellow-100 dark:bg-yellow-900/30">
                            <flux:icon.user-plus class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Nye denne uken</flux:text>
                            <flux:heading size="xl">{{ $newUsersThisWeek }}</flux:heading>
                        </div>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-purple-100 dark:bg-purple-900/30">
                            <flux:icon.calendar class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Nye denne måneden</flux:text>
                            <flux:heading size="xl">{{ $newUsersThisMonth }}</flux:heading>
                        </div>
                    </div>
                </flux:card>
            </div>

            {{-- Module popularity + revenue --}}
            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <flux:card class="bg-white dark:bg-zinc-900">
                    <flux:heading size="lg" level="2" class="mb-4">Modulpopularitet</flux:heading>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Modul</flux:table.column>
                            <flux:table.column>Aktive selskaper</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse($modulePopularity as $module)
                                <flux:table.row wire:key="mod-pop-{{ $module->id }}">
                                    <flux:table.cell>
                                        <flux:text class="font-medium">{{ $module->name }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge color="violet">{{ $module->enabled_count }}</flux:badge>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="2" class="text-center py-4">
                                        <flux:text class="text-zinc-400">Ingen moduler</flux:text>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900">
                    <flux:heading size="lg" level="2" class="mb-4">Inntekt per modul</flux:heading>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Modul</flux:table.column>
                            <flux:table.column>MRR</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse($revenuePerModule as $module)
                                <flux:table.row wire:key="mod-rev-{{ $module->id }}">
                                    <flux:table.cell>
                                        <flux:text class="font-medium">{{ $module->name }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text>{{ number_format($module->monthly_revenue / 100, 0, ',', ' ') }} kr</flux:text>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="2" class="text-center py-4">
                                        <flux:text class="text-zinc-400">Ingen moduler</flux:text>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </flux:card>
            </div>

            {{-- Recent signups --}}
            <div class="mt-8">
                <flux:card class="bg-white dark:bg-zinc-900">
                    <flux:heading size="lg" level="2" class="mb-4">Siste registreringer</flux:heading>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Selskap</flux:table.column>
                            <flux:table.column>Org.nr</flux:table.column>
                            <flux:table.column>E-post</flux:table.column>
                            <flux:table.column>Eier</flux:table.column>
                            <flux:table.column>Registrert</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse($recentSignups as $company)
                                <flux:table.row wire:key="signup-{{ $company->id }}">
                                    <flux:table.cell>
                                        <a href="{{ route('admin.company-detail', $company->id) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ $company->name }}
                                        </a>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text class="text-sm font-mono">{{ $company->formatted_organization_number }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text class="text-sm">{{ $company->email }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text class="text-sm">{{ $company->users->first()?->name ?? '—' }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text class="text-sm text-zinc-500">{{ $company->created_at->format('d.m.Y') }}</flux:text>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="5" class="text-center py-4">
                                        <flux:text class="text-zinc-400">Ingen selskaper</flux:text>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </flux:card>
            </div>
        </flux:main>
    </div>
</x-layouts.app>
