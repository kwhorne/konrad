<div>
    @if($show)
        <div class="bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800">
            <div class="max-w-7xl mx-auto py-3 px-3 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between flex-wrap">
                    <div class="flex-1 flex items-center">
                        <span class="flex p-2 rounded-lg bg-amber-100 dark:bg-amber-800">
                            <flux:icon.shield-exclamation class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        </span>
                        <p class="ml-3 font-medium text-amber-800 dark:text-amber-200 truncate">
                            @if($daysRemaining !== null && $daysRemaining > 0)
                                <span class="hidden md:inline">
                                    Aktiver tofaktorautentisering innen {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'dag' : 'dager' }} for å unngå at kontoen din blir låst.
                                </span>
                                <span class="md:hidden">
                                    Aktiver 2FA innen {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'dag' : 'dager' }}
                                </span>
                            @else
                                <span class="hidden md:inline">
                                    Siste sjanse! Aktiver tofaktorautentisering i dag for å unngå at kontoen din blir låst.
                                </span>
                                <span class="md:hidden">
                                    Aktiver 2FA i dag!
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="order-3 mt-2 shrink-0 w-full sm:order-2 sm:mt-0 sm:w-auto">
                        <a href="{{ route('settings') }}" class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-amber-800 bg-amber-100 hover:bg-amber-200 dark:text-amber-200 dark:bg-amber-800 dark:hover:bg-amber-700">
                            Aktiver nå
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
