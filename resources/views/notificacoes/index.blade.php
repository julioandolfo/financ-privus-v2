<x-layouts.app title="Notificações">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Notificações</h1>
            <p class="text-sm text-surface-500 mt-0.5">
                @if($totalNaoLidas > 0)
                    {{ $totalNaoLidas }} {{ $totalNaoLidas === 1 ? 'notificação não lida' : 'notificações não lidas' }}
                @else
                    Todas as notificações foram lidas
                @endif
            </p>
        </div>

        @if($totalNaoLidas > 0)
        <form method="POST" action="{{ route('notificacoes.marcar-todas-lidas') }}">
            @csrf
            <x-ui.button type="submit" variant="secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Marcar todas como lidas
            </x-ui.button>
        </form>
        @endif
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filters --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-48">
                <x-ui.select name="tipo" label="Tipo">
                    <option value="">Todos os tipos</option>
                    <option value="vencimento"  @selected(request('tipo') === 'vencimento')>Vencimento</option>
                    <option value="recorrencia" @selected(request('tipo') === 'recorrencia')>Recorrência</option>
                    <option value="pagamento"   @selected(request('tipo') === 'pagamento')>Pagamento</option>
                    <option value="sistema"     @selected(request('tipo') === 'sistema')>Sistema</option>
                    <option value="alerta"      @selected(request('tipo') === 'alerta')>Alerta</option>
                </x-ui.select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Status</label>
                <div class="flex items-center gap-4 h-[42px]">
                    @foreach([''=>'Todas', 'nao_lidas'=>'Não lidas', 'lidas'=>'Lidas'] as $value => $label)
                    <label class="flex items-center gap-1.5 text-sm text-surface-700 dark:text-surface-300 cursor-pointer">
                        <input type="radio" name="lida" value="{{ $value }}"
                            @checked(request('lida', '') === $value)
                            class="text-primary-600 border-surface-300 focus:ring-primary-500">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>

            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>

            @if(request()->hasAny(['tipo', 'lida']))
            <x-ui.button href="{{ route('notificacoes.index') }}" variant="ghost">Limpar</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    {{-- Notifications list --}}
    <x-ui.card :padding="false">
        <div class="divide-y divide-surface-100 dark:divide-surface-700">
            @forelse($notificacoes as $notificacao)
            @php
                $corMap = [
                    'blue'   => ['icon_bg' => 'bg-blue-100 dark:bg-blue-900/30',   'icon_text' => 'text-blue-600 dark:text-blue-400'],
                    'red'    => ['icon_bg' => 'bg-red-100 dark:bg-red-900/30',     'icon_text' => 'text-red-600 dark:text-red-400'],
                    'green'  => ['icon_bg' => 'bg-green-100 dark:bg-green-900/30', 'icon_text' => 'text-green-600 dark:text-green-400'],
                    'yellow' => ['icon_bg' => 'bg-yellow-100 dark:bg-yellow-900/30','icon_text'=> 'text-yellow-600 dark:text-yellow-400'],
                    'orange' => ['icon_bg' => 'bg-orange-100 dark:bg-orange-900/30','icon_text'=> 'text-orange-600 dark:text-orange-400'],
                ];
                $cores = $corMap[$notificacao->cor] ?? $corMap['blue'];

                $tipoMap = [
                    'vencimento'  => ['warning', 'Vencimento'],
                    'recorrencia' => ['info',    'Recorrência'],
                    'pagamento'   => ['success', 'Pagamento'],
                    'sistema'     => ['default', 'Sistema'],
                    'alerta'      => ['danger',  'Alerta'],
                ];
                [$tipoVariant, $tipoLabel] = $tipoMap[$notificacao->tipo] ?? ['default', $notificacao->tipo];
            @endphp
            <div class="flex items-start gap-4 px-5 py-4 {{ $notificacao->lida ? '' : 'bg-blue-50/40 dark:bg-blue-900/10' }} hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">

                {{-- Color icon --}}
                <div class="flex-shrink-0 mt-0.5">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center {{ $cores['icon_bg'] }}">
                        @if($notificacao->icone === 'bell')
                        <svg class="w-4 h-4 {{ $cores['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        @elseif($notificacao->icone === 'clock')
                        <svg class="w-4 h-4 {{ $cores['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        @elseif($notificacao->icone === 'check-circle')
                        <svg class="w-4 h-4 {{ $cores['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        @elseif($notificacao->icone === 'exclamation-triangle')
                        <svg class="w-4 h-4 {{ $cores['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        @elseif($notificacao->icone === 'arrow-path')
                        <svg class="w-4 h-4 {{ $cores['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        @elseif($notificacao->icone === 'information-circle')
                        <svg class="w-4 h-4 {{ $cores['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        @else
                        <svg class="w-4 h-4 {{ $cores['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        @endif
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-surface-900 dark:text-white {{ $notificacao->lida ? 'font-medium' : '' }}">
                                    {{ $notificacao->titulo }}
                                </span>
                                <x-ui.badge :variant="$tipoVariant">{{ $tipoLabel }}</x-ui.badge>
                                @if(! $notificacao->lida)
                                <span class="inline-block w-2 h-2 rounded-full bg-primary-500 flex-shrink-0" title="Não lida"></span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-sm text-surface-600 dark:text-surface-400">
                                {{ $notificacao->mensagem }}
                            </p>
                            <div class="mt-1.5 flex items-center gap-3 text-xs text-surface-400">
                                <span>{{ $notificacao->created_at->format('d/m/Y H:i') }}</span>
                                @if($notificacao->lida && $notificacao->lida_em)
                                <span>· Lida em {{ $notificacao->lida_em->format('d/m/Y H:i') }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Status badge --}}
                        <div class="flex-shrink-0">
                            @if($notificacao->lida)
                            <x-ui.badge variant="default">Lida</x-ui.badge>
                            @else
                            <x-ui.badge variant="primary">Não lida</x-ui.badge>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex-shrink-0 flex items-center gap-1 mt-0.5">
                    @if($notificacao->link)
                    <a href="{{ $notificacao->link }}"
                       class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                       title="Ver detalhes">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                    @endif

                    @if(! $notificacao->lida)
                    <form method="POST" action="{{ route('notificacoes.marcar-lida', $notificacao) }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                            title="Marcar como lida">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </button>
                    </form>
                    @endif

                    <form method="POST" action="{{ route('notificacoes.destroy', $notificacao) }}"
                          onsubmit="return confirm('Remover esta notificação?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            title="Excluir">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center px-5 py-16 text-center">
                <div class="w-14 h-14 rounded-full bg-surface-100 dark:bg-surface-700 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-surface-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-surface-900 dark:text-white">Nenhuma notificação encontrada</p>
                <p class="text-xs text-surface-400 mt-1">
                    @if(request()->hasAny(['tipo', 'lida']))
                        Tente ajustar os filtros para ver mais resultados.
                    @else
                        Você está em dia! Novas notificações aparecerão aqui.
                    @endif
                </p>
                @if(request()->hasAny(['tipo', 'lida']))
                <a href="{{ route('notificacoes.index') }}"
                   class="mt-3 text-xs text-primary-600 hover:underline">Limpar filtros</a>
                @endif
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notificacoes->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $notificacoes->links() }}
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
