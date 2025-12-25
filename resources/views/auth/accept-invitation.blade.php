<x-layouts.app title="Aksepter invitasjon">
    <div class="min-h-screen bg-white dark:bg-zinc-800 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <flux:heading size="2xl" level="1" class="text-zinc-900 dark:text-white">
                    Velkommen, {{ $user->name }}!
                </flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">
                    Velg et passord for å aktivere kontoen din
                </flux:text>
            </div>

            <flux:card class="mt-8 bg-white dark:bg-zinc-900 shadow-xl border border-zinc-200 dark:border-zinc-700">
                <form method="POST" action="{{ route('invitation.accept', $token) }}" class="space-y-6">
                    @csrf

                    <div>
                        <flux:field>
                            <flux:label>E-post</flux:label>
                            <flux:input
                                type="email"
                                value="{{ $user->email }}"
                                disabled
                                class="mt-1 bg-zinc-50 dark:bg-zinc-800"
                            />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label for="password">Passord</flux:label>
                            <flux:input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autofocus
                                placeholder="Velg et sterkt passord"
                                class="mt-1"
                            />
                            @error('password')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label for="password_confirmation">Bekreft passord</flux:label>
                            <flux:input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                placeholder="Gjenta passordet"
                                class="mt-1"
                            />
                        </flux:field>
                    </div>

                    <div class="pt-4">
                        <flux:button type="submit" variant="primary" class="w-full">
                            Aktiver konto
                        </flux:button>
                    </div>
                </form>
            </flux:card>
        </div>
    </div>
</x-layouts.app>
