<x-layouts.app title="Editar Perfil de Consolidação">

    <div class="max-w-2xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('perfis-consolidacao.index') }}"
               class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Editar Perfil de Consolidação</h1>
                <p class="text-sm text-surface-500 mt-0.5">{{ $perfisConsolidacao->nome }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('perfis-consolidacao.update', $perfisConsolidacao) }}">
            @csrf @method('PUT')

            @php $config = $perfisConsolidacao->configuracao ?? []; @endphp

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Identificação</h2>
                <div class="space-y-4">
                    <x-ui.input
                        name="nome"
                        label="Nome do Perfil"
                        required
                        maxlength="100"
                        value="{{ old('nome', $perfisConsolidacao->nome) }}"
                        :error="$errors->first('nome')"
                    />
                    <x-ui.textarea
                        name="descricao"
                        label="Descrição"
                        rows="2"
                    >{{ old('descricao', $perfisConsolidacao->descricao) }}</x-ui.textarea>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Configuração</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.select name="periodo" label="Período" required :error="$errors->first('periodo')">
                        @php $periodo = old('periodo', $config['periodo'] ?? 'mes_atual'); @endphp
                        <option value="mes_atual" @selected($periodo === 'mes_atual')>Mês Atual</option>
                        <option value="trimestre" @selected($periodo === 'trimestre')>Trimestre Atual</option>
                        <option value="ano"        @selected($periodo === 'ano')>Ano Atual</option>
                    </x-ui.select>

                    <x-ui.select name="tipo" label="Tipo de Dados" required :error="$errors->first('tipo')">
                        @php $tipo = old('tipo', $config['tipo'] ?? 'ambos'); @endphp
                        <option value="ambos"    @selected($tipo === 'ambos')>Receitas e Despesas</option>
                        <option value="receitas" @selected($tipo === 'receitas')>Somente Receitas</option>
                        <option value="despesas" @selected($tipo === 'despesas')>Somente Despesas</option>
                    </x-ui.select>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-1">Categorias</h2>
                <p class="text-xs text-surface-400 mb-4">Deixe em branco para incluir todas as categorias.</p>
                @php $selectedCats = old('categorias', $config['categorias'] ?? []); @endphp
                @if($categorias->isEmpty())
                <p class="text-sm text-surface-400 italic">Nenhuma categoria financeira cadastrada.</p>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($categorias as $categoria)
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input
                            type="checkbox"
                            name="categorias[]"
                            value="{{ $categoria->id }}"
                            @checked(in_array($categoria->id, $selectedCats))
                            class="rounded border-surface-300 dark:border-surface-600 text-primary-600 focus:ring-primary-500"
                        >
                        <span class="text-sm text-surface-700 dark:text-surface-300 group-hover:text-surface-900 dark:group-hover:text-white transition-colors">
                            {{ $categoria->nome }}
                        </span>
                    </label>
                    @endforeach
                </div>
                @endif
            </x-ui.card>

            <x-ui.card class="mb-6">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Opções</h2>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="mostrar_grafico"
                            value="1"
                            @checked(old('mostrar_grafico') !== null ? old('mostrar_grafico') : ($config['mostrar_grafico'] ?? false))
                            class="rounded border-surface-300 dark:border-surface-600 text-primary-600 focus:ring-primary-500"
                        >
                        <div>
                            <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Mostrar gráfico</span>
                            <p class="text-xs text-surface-400">Exibe gráfico de barras por categoria no relatório</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="publico"
                            value="1"
                            @checked(old('publico') !== null ? old('publico') : $perfisConsolidacao->publico)
                            class="rounded border-surface-300 dark:border-surface-600 text-primary-600 focus:ring-primary-500"
                        >
                        <div>
                            <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Perfil público</span>
                            <p class="text-xs text-surface-400">Visível para todos os usuários da empresa</p>
                        </div>
                    </label>
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('perfis-consolidacao.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar Alterações</x-ui.button>
            </div>
        </form>
    </div>

</x-layouts.app>
