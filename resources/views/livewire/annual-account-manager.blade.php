<div>
    {{-- Header with actions --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                Oversikt over årsregnskap og innsending til Regnskapsregisteret
            </flux:text>
        </div>

        <flux:button wire:click="openCreateModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Nytt årsregnskap
        </flux:button>
    </div>

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
    @if(session('info'))
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <flux:text class="text-blue-800 dark:text-blue-200">{{ session('info') }}</flux:text>
        </div>
    @endif

    {{-- Annual accounts list --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($annualAccounts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    År
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Størrelse
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Omsetning
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Resultat
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Frist
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Handlinger
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($annualAccounts as $account)
                                <tr wire:key="account-{{ $account->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                {{ $account->fiscal_year }}
                                            </flux:text>
                                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $account->getReportPeriod() }}
                                            </flux:text>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:badge variant="outline">
                                            {{ $account->getSizeLabel() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($account->revenue, 0, ',', ' ') }} kr
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium {{ $account->net_profit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($account->net_profit, 0, ',', ' ') }} kr
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:badge variant="{{ $account->getStatusBadgeColor() }}">
                                            {{ $account->getStatusLabel() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($account->isOverdue())
                                            <flux:badge variant="danger">Forfalt</flux:badge>
                                        @elseif($account->getDaysUntilDeadline() <= 30)
                                            <flux:text class="text-orange-600 dark:text-orange-400">
                                                {{ $account->getDaysUntilDeadline() }} dager
                                            </flux:text>
                                        @else
                                            <flux:text class="text-zinc-500 dark:text-zinc-400">
                                                {{ $account->getDeadline()->format('d.m.Y') }}
                                            </flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button wire:click="viewAccount({{ $account->id }})" variant="ghost" size="sm">
                                                <flux:icon.eye class="w-4 h-4" />
                                            </flux:button>
                                            <flux:button wire:click="delete({{ $account->id }})" wire:confirm="Er du sikker på at du vil slette dette årsregnskapet?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                                                <flux:icon.trash class="w-4 h-4" />
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.document-chart-bar class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        Ingen årsregnskap ennå
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        Opprett ditt første årsregnskap for å komme i gang
                    </flux:text>
                    <flux:button wire:click="openCreateModal" variant="primary">
                        <flux:icon.plus class="w-5 h-5 mr-2" />
                        Nytt årsregnskap
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Create Modal --}}
    <flux:modal wire:model="showCreateModal" variant="flyout" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    Nytt årsregnskap
                </flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    Opprett årsregnskap for et regnskapsår
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Regnskapsår *</flux:label>
                    <flux:select wire:model.live="createYear">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </flux:select>
                    @error('createYear')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                @if($hasPreviousYear)
                    <flux:field>
                        <flux:checkbox wire:model="cloneFromPrevious" label="Kopier noter fra forrige år" />
                        <flux:description>Kopierer noteinnhold som utgangspunkt</flux:description>
                    </flux:field>
                @endif

                <flux:callout variant="info">
                    <flux:text class="text-sm">
                        Årsregnskapet vil automatisk hente nøkkeltall fra regnskapet og opprette standard noter.
                    </flux:text>
                </flux:callout>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeCreateModal" variant="ghost">
                    Avbryt
                </flux:button>
                <flux:button wire:click="createAccount" variant="primary">
                    Opprett
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Detail Modal --}}
    <flux:modal wire:model="showDetailModal" variant="flyout" class="w-full max-w-2xl">
        @if($viewingAccount)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        Årsregnskap {{ $viewingAccount->fiscal_year }}
                    </flux:heading>
                    <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                        {{ $viewingAccount->getReportPeriod() }}
                    </flux:text>
                </div>

                <flux:separator />

                {{-- Status --}}
                <div class="flex items-center justify-between">
                    <flux:text class="font-medium">Status</flux:text>
                    <flux:badge variant="{{ $viewingAccount->getStatusBadgeColor() }}">
                        {{ $viewingAccount->getStatusLabel() }}
                    </flux:badge>
                </div>

                {{-- Key figures --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Omsetning</flux:text>
                        <flux:heading size="lg" class="mt-1">{{ number_format($viewingAccount->revenue, 0, ',', ' ') }} kr</flux:heading>
                    </div>
                    <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Årsresultat</flux:text>
                        <flux:heading size="lg" class="mt-1 {{ $viewingAccount->net_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($viewingAccount->net_profit, 0, ',', ' ') }} kr</flux:heading>
                    </div>
                    <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Sum eiendeler</flux:text>
                        <flux:heading size="lg" class="mt-1">{{ number_format($viewingAccount->total_assets, 0, ',', ' ') }} kr</flux:heading>
                    </div>
                    <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Egenkapitalandel</flux:text>
                        <flux:heading size="lg" class="mt-1">{{ $viewingAccount->getEquityRatio() }}%</flux:heading>
                    </div>
                </div>

                {{-- Links --}}
                <div class="space-y-2">
                    <flux:heading size="sm">Innhold</flux:heading>
                    <div class="flex flex-wrap gap-2">
                        <flux:button href="{{ route('annual-accounts.notes', $viewingAccount) }}" variant="outline" size="sm">
                            <flux:icon.document-text class="w-4 h-4 mr-2" />
                            Noter ({{ $viewingAccount->accountNotes->where('is_visible', true)->count() }})
                        </flux:button>
                        @if($viewingAccount->requiresCashFlowStatement())
                            <flux:button href="{{ route('annual-accounts.cash-flow', $viewingAccount) }}" variant="outline" size="sm">
                                <flux:icon.banknotes class="w-4 h-4 mr-2" />
                                Kontantstrøm
                            </flux:button>
                        @endif
                    </div>
                </div>

                {{-- Info --}}
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Selskapsstørrelse</flux:text>
                        <flux:text>{{ $viewingAccount->getSizeLabel() }}</flux:text>
                    </div>
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Gjennomsnittlig ansatte</flux:text>
                        <flux:text>{{ $viewingAccount->average_employees }}</flux:text>
                    </div>
                    @if($viewingAccount->auditor_name)
                        <div class="flex justify-between">
                            <flux:text class="text-zinc-500">Revisor</flux:text>
                            <flux:text>{{ $viewingAccount->auditor_name }}</flux:text>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Innsendingsfrist</flux:text>
                        <flux:text>{{ $viewingAccount->getDeadline()->format('d.m.Y') }}</flux:text>
                    </div>
                </div>

                <flux:separator />

                {{-- Actions --}}
                <div class="flex flex-wrap gap-2">
                    @if($viewingAccount->canBeEdited())
                        <flux:button wire:click="refreshData({{ $viewingAccount->id }})" variant="outline">
                            <flux:icon.arrow-path class="w-4 h-4 mr-2" />
                            Oppdater data
                        </flux:button>
                        <flux:button wire:click="validateAccount({{ $viewingAccount->id }})" variant="outline">
                            <flux:icon.check-circle class="w-4 h-4 mr-2" />
                            Valider
                        </flux:button>
                    @endif

                    @if($viewingAccount->isDraft())
                        <flux:button wire:click="approve({{ $viewingAccount->id }})" variant="primary">
                            <flux:icon.check class="w-4 h-4 mr-2" />
                            Godkjenn
                        </flux:button>
                    @endif

                    @if($viewingAccount->isApproved())
                        <flux:button wire:click="markAsDraft({{ $viewingAccount->id }})" variant="outline">
                            <flux:icon.arrow-left class="w-4 h-4 mr-2" />
                            Tilbake til utkast
                        </flux:button>
                        <flux:button wire:click="submitToAltinn({{ $viewingAccount->id }})" variant="primary">
                            <flux:icon.paper-airplane class="w-4 h-4 mr-2" />
                            Send til Altinn
                        </flux:button>
                    @endif
                </div>

                <div class="flex justify-end">
                    <flux:button wire:click="closeDetailModal" variant="ghost">
                        Lukk
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
