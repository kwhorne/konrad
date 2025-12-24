<x-layouts.app title="Login">
    <div class="min-h-screen bg-white dark:bg-zinc-800 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <flux:heading size="2xl" level="1" class="text-zinc-900 dark:text-white">
                    Welcome back
                </flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">
                    Sign in to your account
                </flux:text>
            </div>

            <flux:card class="mt-8 bg-white dark:bg-zinc-900 shadow-xl border border-zinc-200 dark:border-zinc-700">
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <flux:field>
                            <flux:label for="email">Email Address</flux:label>
                            <flux:input 
                                id="email" 
                                name="email" 
                                type="email" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus
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
                                placeholder="Enter your password"
                                class="mt-1"
                            />
                            @error('password')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>
                    </div>

                    <div class="flex items-center justify-between">
                        <flux:checkbox id="remember" name="remember" label="Remember me" />
                        
                        <flux:link href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                            Forgot password?
                        </flux:link>
                    </div>

                    <div class="pt-4">
                        <flux:button type="submit" variant="primary" class="w-full">
                            Sign In
                        </flux:button>
                    </div>
                </form>

                <flux:separator class="my-6" />

                <div class="text-center">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        Don't have an account?
                        <flux:link href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                            Create one
                        </flux:link>
                    </flux:text>
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts.app>
