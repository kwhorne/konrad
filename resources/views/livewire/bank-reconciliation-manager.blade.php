<div>
    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <flux:text class="text-green-800 dark:text-green-200">{{ session('success') }}</flux:text>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <flux:text class="text-red-800 dark:text-red-200">{{ session('error') }}</flux:text>
        </div>
    @endif

    {{-- Step indicator --}}
    <div class="mb-8">
        <nav aria-label="Progress">
            <ol class="flex items-center justify-between w-full max-w-3xl">
                @foreach([
                    ['step' => 1, 'label' => 'Last opp'],
                    ['step' => 2, 'label' => 'Auto-match'],
                    ['step' => 3, 'label' => 'Gjennomgå'],
                    ['step' => 4, 'label' => 'Fullfør'],
                ] as $stepData)
                    <li class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                        <div class="flex items-center">
                            <span class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-colors
                                {{ $currentStep > $stepData['step'] ? 'bg-green-500 border-green-500 text-white' : '' }}
                                {{ $currentStep === $stepData['step'] ? 'bg-indigo-600 border-indigo-600 text-white' : '' }}
                                {{ $currentStep < $stepData['step'] ? 'border-zinc-300 dark:border-zinc-600 text-zinc-500 dark:text-zinc-400' : '' }}
                            ">
                                @if($currentStep > $stepData['step'])
                                    <flux:icon.check class="w-5 h-5" />
                                @else
                                    {{ $stepData['step'] }}
                                @endif
                            </span>
                            <span class="ml-3 text-sm font-medium
                                {{ $currentStep >= $stepData['step'] ? 'text-zinc-900 dark:text-white' : 'text-zinc-500 dark:text-zinc-400' }}
                            ">{{ $stepData['label'] }}</span>
                        </div>
                        @if(!$loop->last)
                            <div class="flex-1 h-0.5 mx-4
                                {{ $currentStep > $stepData['step'] ? 'bg-green-500' : 'bg-zinc-200 dark:bg-zinc-700' }}
                            "></div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>

    {{-- Step content --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($currentStep === 1)
                @include('livewire.partials.bank-reconciliation.step-1-upload')
            @elseif($currentStep === 2)
                @include('livewire.partials.bank-reconciliation.step-2-review')
            @elseif($currentStep === 3)
                @include('livewire.partials.bank-reconciliation.step-3-resolve')
            @elseif($currentStep === 4)
                @include('livewire.partials.bank-reconciliation.step-4-finalize')
            @endif
        </div>
    </flux:card>

    {{-- Match Modal --}}
    @include('livewire.partials.bank-reconciliation.match-modal')

    {{-- Draft Voucher Modal --}}
    @include('livewire.partials.bank-reconciliation.draft-voucher-modal')
</div>
