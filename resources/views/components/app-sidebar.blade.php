@props(['current' => null])

<flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r rtl:border-r-0 rtl:border-l border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <flux:brand href="{{ route('dashboard') }}" name="Konrad" class="px-2">
        <flux:icon.building-2 class="h-8 w-8 text-indigo-600" />
    </flux:brand>

    <flux:input as="button" variant="filled" placeholder="Søk..." icon="magnifying-glass" />

    <flux:navlist variant="outline">
        <flux:navlist.item icon="home" href="{{ route('dashboard') }}" :current="$current === 'dashboard'">
            Dashboard
        </flux:navlist.item>
        
        <flux:separator variant="subtle" class="my-4" />
        
        <flux:navlist.group expandable heading="Funksjoner" class="grid">
            @if(config('features.contracts'))
                <flux:navlist.item icon="document-text" href="{{ route('contracts.index') }}" :current="$current === 'contracts'">
                    Kontraktsregister
                </flux:navlist.item>
            @endif
            
            @if(config('features.assets'))
                <flux:navlist.item icon="cube" href="{{ route('assets.index') }}" :current="$current === 'assets'">
                    Eiendelsregister
                </flux:navlist.item>
            @endif
            
            @if(config('features.contacts'))
                <flux:navlist.item icon="users" href="{{ route('contacts.index') }}" :current="$current === 'contacts'">
                    Kontaktregister
                </flux:navlist.item>
            @endif

            @if(config('features.products'))
                <flux:navlist.item icon="cube" href="{{ route('products.index') }}" :current="$current === 'products'">
                    Vareregister
                </flux:navlist.item>
            @endif

            @if(config('features.projects'))
                <flux:navlist.item icon="folder" href="{{ route('projects.index') }}" :current="$current === 'projects'">
                    Prosjekter
                </flux:navlist.item>
            @endif

            @if(config('features.work_orders'))
                <flux:navlist.item icon="clipboard-document-list" href="{{ route('work-orders.index') }}" :current="$current === 'work-orders'">
                    Arbeidsordrer
                </flux:navlist.item>
            @endif
        </flux:navlist.group>

        @if(config('features.sales'))
            <flux:navlist.group expandable heading="Salg" class="grid">
                <flux:navlist.item icon="document-text" href="{{ route('quotes.index') }}" :current="$current === 'quotes'">
                    Tilbud
                </flux:navlist.item>
                <flux:navlist.item icon="shopping-cart" href="{{ route('orders.index') }}" :current="$current === 'orders'">
                    Ordrer
                </flux:navlist.item>
                <flux:navlist.item icon="banknotes" href="{{ route('invoices.index') }}" :current="$current === 'invoices'">
                    Fakturaer
                </flux:navlist.item>
            </flux:navlist.group>
        @endif

        @if(!config('features.contracts') && !config('features.assets') && !config('features.contacts') && !config('features.products') && !config('features.projects') && !config('features.work_orders') && !config('features.sales'))
            <flux:text class="px-3 py-2 text-sm text-zinc-500 dark:text-zinc-400">
                Ingen moduler aktivert enna.
            </flux:text>
        @endif

        @if(auth()->user()->is_admin)
            <flux:separator variant="subtle" class="my-4" />
            
            <flux:navlist.item icon="shield-check" href="{{ route('admin.users') }}">
                Administrasjon
            </flux:navlist.item>
        @endif
    </flux:navlist>

    <flux:spacer />

    <flux:navlist variant="outline">
        <flux:navlist.item icon="cog-6-tooth" href="{{ route('settings') }}" :current="$current === 'settings'">
            Innstillinger
        </flux:navlist.item>
        <flux:navlist.item icon="information-circle" href="#">
            Hjelp
        </flux:navlist.item>
    </flux:navlist>

    <flux:dropdown position="top" align="start" class="max-lg:hidden">
        <flux:profile avatar="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4f46e5&color=fff" name="{{ auth()->user()->name }}" />

        <flux:menu>
            <flux:menu.item disabled>
                {{ auth()->user()->email }}
            </flux:menu.item>

            <flux:menu.separator />

            <flux:menu.item icon="arrow-right-start-on-rectangle" onclick="document.getElementById('logout-form').submit();">
                Logg ut
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>
