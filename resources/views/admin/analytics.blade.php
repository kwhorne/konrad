<x-layouts.app title="Admin - Analyse">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="analytics" />
        <x-admin-header current="analytics" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">Analyseoversikt</flux:heading>

            <flux:text class="mb-6 mt-2 text-base text-zinc-600 dark:text-zinc-400">Oversikt over systemmetrikker og brukerstatistikk</flux:text>

            <flux:separator variant="subtle" />

            <!-- Analytics Cards -->
            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Total Users -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <flux:icon.users class="h-8 w-8 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <flux:heading size="2xl" level="3" class="text-zinc-900 dark:text-white">
                                    {{ $totalUsers }}
                                </flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    Total Users
                                </flux:text>
                            </div>
                        </div>
                    </div>
                </flux:card>

                <!-- Admin Users -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <flux:icon.shield-check class="h-8 w-8 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <flux:heading size="2xl" level="3" class="text-zinc-900 dark:text-white">
                                    {{ $adminUsers }}
                                </flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    Admin Users
                                </flux:text>
                            </div>
                        </div>
                    </div>
                </flux:card>

                <!-- Recent Users -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <flux:icon.user-plus class="h-8 w-8 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <flux:heading size="2xl" level="3" class="text-zinc-900 dark:text-white">
                                    {{ $recentUsers }}
                                </flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    New This Week
                                </flux:text>
                            </div>
                        </div>
                    </div>
                </flux:card>
            </div>

            <!-- System Health -->
            <div class="mt-8">
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="lg" level="2" class="mb-4">System Health</flux:heading>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-3 w-3 bg-green-500 rounded-full"></div>
                                    <flux:text class="text-zinc-900 dark:text-white">Database Connection</flux:text>
                                </div>
                                <flux:badge variant="success">Healthy</flux:badge>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-3 w-3 bg-green-500 rounded-full"></div>
                                    <flux:text class="text-zinc-900 dark:text-white">Application Cache</flux:text>
                                </div>
                                <flux:badge variant="success">Healthy</flux:badge>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-3 w-3 bg-green-500 rounded-full"></div>
                                    <flux:text class="text-zinc-900 dark:text-white">Queue Processing</flux:text>
                                </div>
                                <flux:badge variant="success">Healthy</flux:badge>
                            </div>
                        </div>
                    </div>
                </flux:card>
            </div>

            <!-- Recent Activity -->
            <div class="mt-8">
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="lg" level="2" class="mb-4">Recent Activity</flux:heading>
                        
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-2 w-2 bg-blue-500 rounded-full"></div>
                                </div>
                                <div class="flex-1">
                                    <flux:text class="text-zinc-900 dark:text-white">
                                        System analytics updated
                                    </flux:text>
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Just now
                                    </flux:text>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                                </div>
                                <div class="flex-1">
                                    <flux:text class="text-zinc-900 dark:text-white">
                                        Database backup completed
                                    </flux:text>
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                        2 hours ago
                                    </flux:text>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-2 w-2 bg-purple-500 rounded-full"></div>
                                </div>
                                <div class="flex-1">
                                    <flux:text class="text-zinc-900 dark:text-white">
                                        Cache cleared successfully
                                    </flux:text>
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                        5 hours ago
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                    </div>
                </flux:card>
            </div>
        </flux:main>

        <!-- Hidden logout form -->
    </div>
</x-layouts.app>
