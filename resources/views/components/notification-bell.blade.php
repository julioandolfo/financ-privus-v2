<div
    x-data="{
        open: false,
        count: 0,
        items: [],
        loading: false,

        init() {
            this.fetchNotifications();
        },

        fetchNotifications() {
            this.loading = true;
            fetch('/api/notificacoes/dropdown', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(d => {
                this.count = d.nao_lidas;
                this.items = d.notificacoes;
            })
            .catch(() => {})
            .finally(() => { this.loading = false; });
        },

        markRead(id) {
            const item = this.items.find(n => n.id === id);
            if (!item || item.lida) return;

            fetch('/notificacoes/' + id + '/marcar-lida', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                }
            })
            .then(r => r.json())
            .then(d => {
                if (d.ok) {
                    item.lida = true;
                    this.count = Math.max(0, this.count - 1);
                }
            })
            .catch(() => {});
        },

        markAllRead() {
            fetch('/notificacoes/marcar-todas-lidas', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                }
            })
            .then(r => r.json())
            .then(d => {
                if (d.ok) {
                    this.items.forEach(n => n.lida = true);
                    this.count = 0;
                }
            })
            .catch(() => {});
        },

        iconPath(icone) {
            const icons = {
                'bell': 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
                'clock': 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                'check-circle': 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                'exclamation-triangle': 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
                'arrow-path': 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99',
                'information-circle': 'm11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
            };
            return icons[icone] ?? icons['bell'];
        },

        iconColor(cor) {
            const colors = {
                'blue':   'text-blue-500',
                'red':    'text-red-500',
                'green':  'text-green-500',
                'yellow': 'text-yellow-500',
                'orange': 'text-orange-500',
            };
            return colors[cor] ?? colors['blue'];
        },

        iconBg(cor) {
            const bgs = {
                'blue':   'bg-blue-100 dark:bg-blue-900/30',
                'red':    'bg-red-100 dark:bg-red-900/30',
                'green':  'bg-green-100 dark:bg-green-900/30',
                'yellow': 'bg-yellow-100 dark:bg-yellow-900/30',
                'orange': 'bg-orange-100 dark:bg-orange-900/30',
            };
            return bgs[cor] ?? bgs['blue'];
        },
    }"
    x-init="init()"
    class="relative"
    @keydown.escape.window="open = false"
>
    {{-- Bell button --}}
    <button
        @click="open = !open"
        class="relative p-2 rounded-lg text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1"
        :aria-label="'Notificações' + (count > 0 ? ': ' + count + ' não lidas' : '')"
    >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        {{-- Unread badge --}}
        <span
            x-show="count > 0"
            x-cloak
            x-text="count > 99 ? '99+' : count"
            class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 flex items-center justify-center rounded-full text-[10px] font-bold bg-red-500 text-white leading-none"
        ></span>
    </button>

    {{-- Dropdown panel --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        @click.away="open = false"
        class="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white dark:bg-surface-800 rounded-2xl shadow-xl border border-surface-200 dark:border-surface-700 overflow-hidden z-50 origin-top-right"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-surface-100 dark:border-surface-700">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-surface-900 dark:text-white">Notificações</span>
                <span
                    x-show="count > 0"
                    x-cloak
                    x-text="count"
                    class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400"
                ></span>
            </div>
            <button
                x-show="count > 0"
                x-cloak
                @click="markAllRead()"
                class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium"
            >
                Marcar todas lidas
            </button>
        </div>

        {{-- Loading state --}}
        <div x-show="loading" x-cloak class="flex items-center justify-center py-8">
            <svg class="w-5 h-5 text-surface-400 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        {{-- Items list --}}
        <div x-show="!loading" class="max-h-80 overflow-y-auto divide-y divide-surface-100 dark:divide-surface-700">

            {{-- Empty state --}}
            <template x-if="items.length === 0">
                <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                    <svg class="w-10 h-10 text-surface-300 dark:text-surface-600 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    <p class="text-sm text-surface-500">Nenhuma notificação</p>
                </div>
            </template>

            {{-- Notification items --}}
            <template x-for="item in items" :key="item.id">
                <div
                    class="flex items-start gap-3 px-4 py-3 transition-colors cursor-pointer"
                    :class="item.lida ? 'hover:bg-surface-50 dark:hover:bg-surface-700/50' : 'bg-blue-50/50 dark:bg-blue-900/10 hover:bg-blue-50 dark:hover:bg-blue-900/20'"
                    @click="markRead(item.id); if(item.link) { window.location.href = item.link; } else { open = false; }"
                >
                    {{-- Icon --}}
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="iconBg(item.cor)">
                            <svg class="w-4 h-4" :class="iconColor(item.cor)" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath(item.icone)" />
                            </svg>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-medium text-surface-900 dark:text-white truncate" x-text="item.titulo"></p>
                            <span x-show="!item.lida" x-cloak class="flex-shrink-0 w-2 h-2 rounded-full bg-primary-500 mt-1.5"></span>
                        </div>
                        <p class="text-xs text-surface-500 dark:text-surface-400 mt-0.5 line-clamp-2" x-text="item.mensagem"></p>
                        <p class="text-xs text-surface-400 mt-1" x-text="item.tempo_relativo"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="px-4 py-3 border-t border-surface-100 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/50">
            <a
                href="/notificacoes"
                @click="open = false"
                class="flex items-center justify-center gap-1.5 text-sm text-primary-600 dark:text-primary-400 hover:underline font-medium"
            >
                Ver todas as notificações
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>
    </div>
</div>
