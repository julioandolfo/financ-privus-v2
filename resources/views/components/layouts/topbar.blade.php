@props(['title' => ''])

<header class="flex h-16 flex-shrink-0 items-center gap-4 border-b border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800/50 px-4 lg:px-6 backdrop-blur-sm">

    {{-- Mobile menu toggle --}}
    <button @click="$store.sidebar.toggle()" class="lg:hidden p-2 rounded-lg text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    {{-- Title --}}
    <div class="flex-1">
        <h1 class="text-base font-semibold text-surface-900 dark:text-white">{{ $title }}</h1>
    </div>

    {{-- Right actions --}}
    <div class="flex items-center gap-2">

        {{-- Notifications --}}
        <x-notification-bell />

        {{-- Theme toggle --}}
        <div x-data class="relative" x-id="['theme-menu']">
            <button @click="$el.nextElementSibling.classList.toggle('hidden')"
                class="p-2 rounded-lg text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                <svg x-show="$store.theme.current !== 'dark'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
                <svg x-show="$store.theme.current === 'dark'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
            </button>

            <div class="hidden absolute right-0 top-full mt-2 w-36 bg-white dark:bg-surface-800 rounded-xl shadow-lg border border-surface-200 dark:border-surface-700 overflow-hidden z-50"
                @click.away="$el.classList.add('hidden')">
                @foreach(['light' => 'Claro', 'dark' => 'Escuro', 'system' => 'Sistema'] as $value => $label)
                <button @click="$store.theme.set('{{ $value }}'); $el.closest('.relative').querySelector('.hidden') && $el.closest('.relative').querySelector('[class*=hidden]').classList.add('hidden')"
                    class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700 transition-colors"
                    :class="$store.theme.current === '{{ $value }}' ? 'text-primary-600 dark:text-primary-400' : ''">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>

    </div>
</header>
