<x-layouts.app title="Admin - Brukere">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="users" />
        <x-admin-header current="users" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">Brukerhåndtering</flux:heading>

            <flux:text class="mb-6 mt-2 text-base text-zinc-600 dark:text-zinc-400">Administrer alle registrerte brukere og deres tillatelser</flux:text>

            <flux:separator variant="subtle" />

            <!-- Users Table -->
            <div class="mt-8">
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">Alle brukere</flux:heading>
                            <flux:badge variant="outline">{{ $users->total() }} total</flux:badge>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Bruker
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Rolle
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Registrert
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Handlinger
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($users as $user)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-white">
                                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                            {{ $user->name }}
                                                        </flux:text>
                                                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                            {{ $user->email }}
                                                        </flux:text>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($user->is_admin)
                                                    <flux:badge variant="primary">Admin</flux:badge>
                                                @else
                                                    <flux:badge variant="outline">Bruker</flux:badge>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $user->created_at->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <flux:button variant="ghost" size="sm">
                                                    Rediger
                                                </flux:button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $users->links() }}
                        </div>
                    </div>
                </flux:card>
            </div>
        </flux:main>

        <!-- Hidden logout form -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</x-layouts.app>
