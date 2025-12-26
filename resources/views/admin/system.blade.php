<x-layouts.app title="Admin - System">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="system" />
        <x-admin-header current="system" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">Systemadministrasjon</flux:heading>

            <flux:text class="mb-6 mt-2 text-base text-zinc-600 dark:text-zinc-400">Administrer systeminnstillinger, vedlikehold og konfigurasjon</flux:text>

            <flux:separator variant="subtle" />

            <!-- System Information -->
            <div class="mt-8">
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="lg" level="2" class="mb-4">System Information</flux:heading>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">
                                    Laravel Version
                                </flux:text>
                                <flux:text class="text-zinc-900 dark:text-white">
                                    {{ app()->version() }}
                                </flux:text>
                            </div>
                            
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">
                                    PHP Version
                                </flux:text>
                                <flux:text class="text-zinc-900 dark:text-white">
                                    {{ PHP_VERSION }}
                                </flux:text>
                            </div>
                            
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">
                                    Environment
                                </flux:text>
                                <flux:badge variant="{{ app()->environment() === 'production' ? 'danger' : 'warning' }}">
                                    {{ ucfirst(app()->environment()) }}
                                </flux:badge>
                            </div>
                            
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">
                                    Debug Mode
                                </flux:text>
                                <flux:badge variant="{{ config('app.debug') ? 'warning' : 'success' }}">
                                    {{ config('app.debug') ? 'Enabled' : 'Disabled' }}
                                </flux:badge>
                            </div>
                        </div>
                    </div>
                </flux:card>
            </div>

            <!-- System Actions -->
            <div class="mt-8">
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="lg" level="2" class="mb-4">System Actions</flux:heading>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <flux:button variant="outline" class="flex items-center justify-center p-4">
                                <flux:icon.arrow-path class="h-5 w-5 mr-2" />
                                Clear Cache
                            </flux:button>
                            
                            <flux:button variant="outline" class="flex items-center justify-center p-4">
                                <flux:icon.arrow-down-tray class="h-5 w-5 mr-2" />
                                Backup Database
                            </flux:button>
                            
                            <flux:button variant="outline" class="flex items-center justify-center p-4">
                                <flux:icon.document-text class="h-5 w-5 mr-2" />
                                View Logs
                            </flux:button>
                            
                            <flux:button variant="outline" class="flex items-center justify-center p-4">
                                <flux:icon.chart-bar class="h-5 w-5 mr-2" />
                                Performance Report
                            </flux:button>
                            
                            <flux:button variant="outline" class="flex items-center justify-center p-4">
                                <flux:icon.shield-check class="h-5 w-5 mr-2" />
                                Security Scan
                            </flux:button>
                            
                            <flux:button variant="outline" class="flex items-center justify-center p-4">
                                <flux:icon.cog-6-tooth class="h-5 w-5 mr-2" />
                                System Config
                            </flux:button>
                        </div>
                    </div>
                </flux:card>
            </div>

            <!-- Maintenance Mode -->
            <div class="mt-8">
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="lg" level="2" class="mb-4">Maintenance Mode</flux:heading>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="text-zinc-900 dark:text-white font-medium mb-1">
                                    Application Status
                                </flux:text>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                    Put the application in maintenance mode to perform updates
                                </flux:text>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <flux:badge variant="success">Online</flux:badge>
                                <flux:button variant="danger">
                                    Enable Maintenance
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </flux:card>
            </div>

            <!-- Storage & Performance -->
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Storage Usage -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="lg" level="2" class="mb-4">Storage Usage</flux:heading>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                        Database
                                    </flux:text>
                                    <flux:text class="text-sm text-zinc-900 dark:text-white">
                                        2.4 MB
                                    </flux:text>
                                </div>
                                <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: 15%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                        Uploads
                                    </flux:text>
                                    <flux:text class="text-sm text-zinc-900 dark:text-white">
                                        45.2 MB
                                    </flux:text>
                                </div>
                                <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: 30%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                        Cache
                                    </flux:text>
                                    <flux:text class="text-sm text-zinc-900 dark:text-white">
                                        12.8 MB
                                    </flux:text>
                                </div>
                                <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                    <div class="bg-yellow-600 h-2 rounded-full" style="width: 8%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </flux:card>

                <!-- Performance Metrics -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="lg" level="2" class="mb-4">Performance Metrics</flux:heading>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                    Average Response Time
                                </flux:text>
                                <flux:text class="text-sm text-zinc-900 dark:text-white font-medium">
                                    142ms
                                </flux:text>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                    Memory Usage
                                </flux:text>
                                <flux:text class="text-sm text-zinc-900 dark:text-white font-medium">
                                    24.5 MB
                                </flux:text>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                    Active Sessions
                                </flux:text>
                                <flux:text class="text-sm text-zinc-900 dark:text-white font-medium">
                                    3
                                </flux:text>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                    Uptime
                                </flux:text>
                                <flux:text class="text-sm text-zinc-900 dark:text-white font-medium">
                                    2d 14h 32m
                                </flux:text>
                            </div>
                        </div>
                    </div>
                </flux:card>
            </div>
        </flux:main>

        <!-- Hidden logout form -->
    </div>
</x-layouts.app>
