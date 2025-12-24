@props(['current' => null])

<flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r rtl:border-r-0 rtl:border-l border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <flux:brand href="{{ route('dashboard') }}" name="Konrad Admin" class="px-2">
        <flux:icon.shield-check class="h-8 w-8 text-indigo-600" />
    </flux:brand>

    <flux:input as="button" variant="filled" placeholder="Søk..." icon="magnifying-glass" />

    <flux:navlist variant="outline">
        <flux:navlist.item icon="arrow-left" href="{{ route('dashboard') }}">
            Tilbake til app
        </flux:navlist.item>
        
        <flux:separator variant="subtle" class="my-4" />
        
        <flux:navlist.group expandable heading="Administrasjon" class="grid">
            <flux:navlist.item icon="users" href="{{ route('admin.users') }}" :current="$current === 'users'">
                Brukere
            </flux:navlist.item>
            <flux:navlist.item icon="chart-bar" href="{{ route('admin.analytics') }}" :current="$current === 'analytics'">
                Analyse
            </flux:navlist.item>
            <flux:navlist.item icon="cog-6-tooth" href="{{ route('admin.system') }}" :current="$current === 'system'">
                System
            </flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>

    <flux:spacer />

    <flux:navlist variant="outline">
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
            
            <flux:menu.item icon="cog-6-tooth" href="{{ route('settings') }}">
                Innstillinger
            </flux:menu.item>

            <flux:menu.separator />

            <flux:menu.item icon="arrow-right-start-on-rectangle" onclick="document.getElementById('logout-form').submit();">
                Logg ut
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>
