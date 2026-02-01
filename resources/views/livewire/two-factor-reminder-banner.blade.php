<div>
    @if($show)
        <div class="relative bg-gradient-to-r from-indigo-600/90 via-indigo-500/90 to-violet-500/90 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto py-2.5 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-6">
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- Icon --}}
                        <div class="flex-shrink-0 p-1.5 bg-white/10 rounded-lg">
                            <flux:icon.shield-exclamation class="size-4 text-white" />
                        </div>

                        {{-- Text --}}
                        <p class="text-sm text-white/95 font-medium">
                            <span class="hidden sm:inline">
                                Aktiver tofaktorautentisering innen {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'dag' : 'dager' }} for å unngå at kontoen din blir låst.
                            </span>
                            <span class="sm:hidden">
                                Aktiver 2FA innen {{ $daysRemaining }}d
                            </span>
                        </p>
                    </div>

                    {{-- Button --}}
                    <a href="{{ route('settings') }}" class="flex-shrink-0 inline-flex items-center gap-1.5 px-3.5 py-1.5 text-sm font-medium text-indigo-600 bg-white rounded-lg hover:bg-indigo-50 transition-colors shadow-sm">
                        Aktiver nå
                        <flux:icon.arrow-right class="size-3.5" />
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
