<x-layouts.app title="Relatório: {{ $perfisConsolidacao->nome }}">

    <div class="mb-6">
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('perfis-consolidacao.index') }}"
               class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $perfisConsolidacao->nome }}</h1>
                    @if($perfisConsolidacao->publico)
                    <x-ui.badge variant="info">Público</x-ui.badge>
                    @endif
                </div>
                @if($perfisConsolidacao->descricao)
                <p class="text-sm text-surface-500 mt-0.5">{{ $perfisConsolidacao->descricao }}</p>
                @endif
            </div>
            @if($perfisConsolidacao->user_id === auth()->id())
            <x-ui.button href="{{ route('perfis-consolidacao.edit', $perfisConsolidacao) }}" variant="outline" size="sm">
                Editar
            </x-ui.button>
            @endif
        </div>

        @php
            $periodoLabels = ['mes_atual' => 'Mês Atual', 'trimestre' => 'Trimestre Atual', 'ano' => 'Ano Atual'];
            $periodoLabel  = $periodoLabels[$config['periodo'] ?? 'mes_atual'] ?? 'Mês Atual';
            $dataInicioFmt = \Carbon\Carbon::parse($dataInicio)->format('d/m/Y');
            $dataFimFmt    = \Carbon\Carbon::parse($dataFim)->format('d/m/Y');
        @endphp
        <p class="text-xs text-surface-400 mt-1 ml-8">
            {{ $periodoLabel }} &mdash; {{ $dataInicioFmt }} a {{ $dataFimFmt }}
        </p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        @if(in_array($config['tipo'] ?? 'ambos', ['receitas', 'ambos']))
        <x-ui.stat-card
            label="Total de Receitas"
            value="R$ {{ number_format($receitas, 2, ',', '.') }}"
            color="green"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>'
        />
        @endif

        @if(in_array($config['tipo'] ?? 'ambos', ['despesas', 'ambos']))
        <x-ui.stat-card
            label="Total de Despesas"
            value="R$ {{ number_format($despesas, 2, ',', '.') }}"
            color="red"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.306-4.306a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181" /></svg>'
        />
        @endif

        @if(($config['tipo'] ?? 'ambos') === 'ambos')
        <x-ui.stat-card
            label="Resultado"
            value="R$ {{ number_format($resultado, 2, ',', '.') }}"
            :color="$resultado >= 0 ? 'green' : 'red'"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.97Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.97Z" /></svg>'
        />
        @endif
    </div>

    {{-- Category Breakdown Table --}}
    <x-ui.card :padding="false">
        <div class="px-5 py-4 border-b border-surface-100 dark:border-surface-700">
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Detalhamento por Categoria</h2>
        </div>

        @if(empty($porCategoria))
        <div class="px-5 py-12 text-center text-sm text-surface-400">
            Nenhum dado encontrado para o período e filtros selecionados.
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Categoria</th>
                        @if(in_array($config['tipo'] ?? 'ambos', ['receitas', 'ambos']))
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Receitas</th>
                        @endif
                        @if(in_array($config['tipo'] ?? 'ambos', ['despesas', 'ambos']))
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Despesas</th>
                        @endif
                        @if(($config['tipo'] ?? 'ambos') === 'ambos')
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Resultado</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @foreach($porCategoria as $catNome => $valores)
                    @php $res = ($valores['receitas'] ?? 0) - ($valores['despesas'] ?? 0); @endphp
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4 text-sm font-medium text-surface-900 dark:text-white">{{ $catNome }}</td>
                        @if(in_array($config['tipo'] ?? 'ambos', ['receitas', 'ambos']))
                        <td class="px-5 py-4 text-sm text-green-600 dark:text-green-400 text-right font-mono whitespace-nowrap">
                            R$ {{ number_format($valores['receitas'] ?? 0, 2, ',', '.') }}
                        </td>
                        @endif
                        @if(in_array($config['tipo'] ?? 'ambos', ['despesas', 'ambos']))
                        <td class="px-5 py-4 text-sm text-red-600 dark:text-red-400 text-right font-mono whitespace-nowrap">
                            R$ {{ number_format($valores['despesas'] ?? 0, 2, ',', '.') }}
                        </td>
                        @endif
                        @if(($config['tipo'] ?? 'ambos') === 'ambos')
                        <td class="px-5 py-4 text-sm text-right font-mono font-semibold whitespace-nowrap {{ $res >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            R$ {{ number_format($res, 2, ',', '.') }}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-surface-200 dark:border-surface-600">
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <td class="px-5 py-3 text-sm font-bold text-surface-700 dark:text-surface-300">Total</td>
                        @if(in_array($config['tipo'] ?? 'ambos', ['receitas', 'ambos']))
                        <td class="px-5 py-3 text-sm font-bold text-green-600 dark:text-green-400 text-right font-mono whitespace-nowrap">
                            R$ {{ number_format($receitas, 2, ',', '.') }}
                        </td>
                        @endif
                        @if(in_array($config['tipo'] ?? 'ambos', ['despesas', 'ambos']))
                        <td class="px-5 py-3 text-sm font-bold text-red-600 dark:text-red-400 text-right font-mono whitespace-nowrap">
                            R$ {{ number_format($despesas, 2, ',', '.') }}
                        </td>
                        @endif
                        @if(($config['tipo'] ?? 'ambos') === 'ambos')
                        <td class="px-5 py-3 text-sm font-bold text-right font-mono whitespace-nowrap {{ $resultado >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            R$ {{ number_format($resultado, 2, ',', '.') }}
                        </td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
