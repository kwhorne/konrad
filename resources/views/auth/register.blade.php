<x-layouts.app title="Register">
    <div class="min-h-screen bg-white dark:bg-zinc-800 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <flux:heading size="2xl" level="1" class="text-zinc-900 dark:text-white">
                    Create your account
                </flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">
                    Join us today and get started
                </flux:text>
            </div>

            <flux:card class="mt-8 bg-white dark:bg-zinc-900 shadow-xl border border-zinc-200 dark:border-zinc-700">
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <div>
                        <flux:field>
                            <flux:label for="name">Full Name</flux:label>
                            <flux:input 
                                id="name" 
                                name="name" 
                                type="text" 
                                value="{{ old('name') }}" 
                                required 
                                autofocus
                                placeholder="Enter your full name"
                                class="mt-1"
                            />
                            @error('name')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label for="email">Email Address</flux:label>
                            <flux:input 
                                id="email" 
                                name="email" 
                                type="email" 
                                value="{{ old('email') }}" 
                                required
                                placeholder="Enter your email"
                                class="mt-1"
                            />
                            @error('email')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label for="password">Password</flux:label>
                            <flux:input 
                                id="password" 
                                name="password" 
                                type="password" 
                                required
                                placeholder="Create a secure password"
                                class="mt-1"
                            />
                            @error('password')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label for="password_confirmation">Confirm Password</flux:label>
                            <flux:input 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                type="password" 
                                required
                                placeholder="Confirm your password"
                                class="mt-1"
                            />
                        </flux:field>
                    </div>

                    <div class="pt-4">
                        <flux:button type="submit" variant="primary" class="w-full">
                            Create Account
                        </flux:button>
                    </div>
                </form>

                <flux:separator class="my-6" />

                <div class="text-center">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        Already have an account?
                        <flux:link href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                            Sign in
                        </flux:link>
                    </flux:text>
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts.app>
