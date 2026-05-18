<x-layouts.app title="Perfis de Consolidação">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Perfis de Consolidação</h1>
            <p class="text-sm text-surface-500 mt-0.5">Visões personalizadas de dados financeiros consolidados</p>
        </div>
        <x-ui.button href="{{ route('perfis-consolidacao.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Novo Perfil
        </x-ui.button>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    @if($perfis->isEmpty())
    <x-ui.card class="text-center py-16">
        <svg class="w-12 h-12 text-surface-300 dark:text-surface-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
        </svg>
        <p class="text-surface-500 dark:text-surface-400 text-sm mb-4">Nenhum perfil de consolidação criado ainda.</p>
        <x-ui.button href="{{ route('perfis-consolidacao.create') }}" variant="secondary">Criar primeiro perfil</x-ui.button>
    </x-ui.card>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($perfis as $perfil)
        @php
            $config = $perfil->configuracao ?? [];
            $periodoLabels = ['mes_atual' => 'Mês Atual', 'trimestre' => 'Trimestre', 'ano' => 'Ano'];
            $tipoLabels    = ['receitas' => 'Receitas', 'despesas' => 'Despesas', 'ambos' => 'Receitas & Despesas'];
            $periodoLabel  = $periodoLabels[$config['periodo'] ?? 'mes_atual'] ?? 'Mês Atual';
            $tipoLabel     = $tipoLabels[$config['tipo'] ?? 'ambos'] ?? 'Ambos';
            $isOwner       = $perfil->user_id === auth()->id();
        @endphp
        <x-ui.card class="flex flex-col gap-4 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-surface-900 dark:text-white truncate">{{ $perfil->nome }}</h3>
                        @if($perfil->publico)
                        <x-ui.badge variant="info">Público</x-ui.badge>
                        @endif
                    </div>
                    @if($perfil->descricao)
                    <p class="text-xs text-surface-500 dark:text-surface-400 mt-1 line-clamp-2">{{ $perfil->descricao }}</p>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-ui.badge variant="primary">{{ $periodoLabel }}</x-ui.badge>
                <x-ui.badge>{{ $tipoLabel }}</x-ui.badge>
                @if($config['mostrar_grafico'] ?? false)
                <x-ui.badge variant="success">Com gráfico</x-ui.badge>
                @endif
            </div>

            @if(!$isOwner && $perfil->user)
            <p class="text-xs text-surface-400">Por {{ $perfil->user->name }}</p>
            @endif

            <div class="flex items-center justify-between gap-2 pt-2 border-t border-surface-100 dark:border-surface-700">
                <x-ui.button href="{{ route('perfis-consolidacao.show', $perfil) }}" variant="primary" size="sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    Ver Relatório
                </x-ui.button>

                @if($isOwner)
                <div class="flex items-center gap-1">
                    <a href="{{ route('perfis-consolidacao.edit', $perfil) }}"
                       class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                        Editar
                    </a>
                    <form method="POST" action="{{ route('perfis-consolidacao.destroy', $perfil) }}"
                          onsubmit="return confirm('Remover este perfil?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                            Excluir
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </x-ui.card>
        @endforeach
    </div>
    @endif

</x-layouts.app>
