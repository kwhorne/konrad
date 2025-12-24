@props(['current' => null])

<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    <flux:spacer />

    <flux:dropdown position="top" align="start">
        <flux:profile avatar="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4f46e5&color=fff" />

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
</flux:header>
