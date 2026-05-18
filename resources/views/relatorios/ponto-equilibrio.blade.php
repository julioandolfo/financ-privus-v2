<x-layouts.app title="Ponto de Equilíbrio">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Ponto de Equilíbrio</h1>
            <p class="text-sm text-surface-500 mt-0.5">Análise da receita mínima para cobrir todos os custos</p>
        </div>
    </div>

    {{-- Filtros --}}
    <x-ui.card class="mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-40">
                <x-ui.select name="mes" label="Mês">
                    @foreach($meses as $m)
                    <option value="{{ $m['value'] }}" @selected($mes == $m['value'])>{{ $m['label'] }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="w-28">
                <x-ui.select name="ano" label="Ano">
                    @foreach($anos as $a)
                    <option value="{{ $a['value'] }}" @selected($ano == $a['value'])>{{ $a['label'] }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <x-ui.button type="submit" variant="secondary">Calcular</x-ui.button>
            <a href="{{ url('/relatorios/ponto-equilibrio/pdf') }}?{{ http_build_query(request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700"
               target="_blank">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
              PDF
            </a>
        </form>
    </x-ui.card>

    {{-- Cards de Resumo --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Receita do Período</p>
            <p class="text-xl font-bold text-green-600 dark:text-green-400">R$ {{ number_format($receitaTotal, 2, ',', '.') }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Custos Fixos</p>
            <p class="text-xl font-bold text-red-600 dark:text-red-400">R$ {{ number_format($totalFixo, 2, ',', '.') }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Custos Variáveis</p>
            <p class="text-xl font-bold text-amber-600 dark:text-amber-400">R$ {{ number_format($totalVariavel, 2, ',', '.') }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Resultado</p>
            <p class="text-xl font-bold {{ $resultado >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                R$ {{ number_format($resultado, 2, ',', '.') }}
            </p>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Ponto de Equilíbrio --}}
        <x-ui.card>
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Ponto de Equilíbrio Operacional</h2>

            @if($pontoEquilibrio !== null)
            <div class="text-center py-4">
                <p class="text-xs text-surface-500 mb-1">Receita mínima necessária</p>
                <p class="text-3xl font-bold text-surface-900 dark:text-white mb-4">
                    R$ {{ number_format($pontoEquilibrio, 2, ',', '.') }}
                </p>

                {{-- Barra de progresso --}}
                <div class="mb-3">
                    <div class="flex justify-between text-xs text-surface-500 mb-1">
                        <span>R$ 0</span>
                        <span>PE: R$ {{ number_format($pontoEquilibrio, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-4 bg-surface-100 dark:bg-surface-700 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500
                            {{ $percentualAtingido >= 100 ? 'bg-green-500' : ($percentualAtingido >= 70 ? 'bg-amber-500' : 'bg-red-500') }}"
                            style="width: {{ min($percentualAtingido, 100) }}%">
                        </div>
                    </div>
                    <p class="text-xs text-surface-500 mt-1 text-right">
                        {{ number_format(min($percentualAtingido, 100), 1) }}% atingido
                    </p>
                </div>

                @if($receitaTotal >= $pontoEquilibrio)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    Ponto de equilíbrio atingido
                </div>
                @else
                <div class="text-center">
                    <p class="text-sm text-red-600 dark:text-red-400 font-medium">
                        Faltam R$ {{ number_format($pontoEquilibrio - $receitaTotal, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-surface-500 mt-0.5">para atingir o ponto de equilíbrio</p>
                </div>
                @endif
            </div>
            @else
            <div class="text-center py-8 text-surface-400">
                <p class="text-sm">Não há custos variáveis suficientes para calcular.</p>
                <p class="text-xs mt-1">Registre despesas pagas no período para uma análise completa.</p>
            </div>
            @endif

            <div class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-surface-500">Margem de Contribuição</span>
                    <span class="font-semibold text-surface-900 dark:text-white">{{ number_format($margemContribuicao, 1) }}%</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-surface-500">Total de Despesas</span>
                    <span class="font-semibold text-surface-900 dark:text-white">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</span>
                </div>
            </div>
        </x-ui.card>

        {{-- Composição dos Custos --}}
        <x-ui.card>
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Composição dos Custos</h2>

            @if($totalDespesas > 0)
            @php $pctFixo = ($totalFixo / $totalDespesas) * 100; $pctVar = 100 - $pctFixo; @endphp

            <div class="mb-4">
                <div class="flex h-6 rounded-full overflow-hidden mb-2">
                    <div class="bg-red-500 transition-all" style="width: {{ $pctFixo }}%"></div>
                    <div class="bg-amber-400 transition-all" style="width: {{ $pctVar }}%"></div>
                </div>
                <div class="flex gap-4 text-xs">
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded-sm bg-red-500"></div>
                        <span class="text-surface-600 dark:text-surface-400">Fixos {{ number_format($pctFixo, 1) }}%</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded-sm bg-amber-400"></div>
                        <span class="text-surface-600 dark:text-surface-400">Variáveis {{ number_format($pctVar, 1) }}%</span>
                    </div>
                </div>
            </div>
            @endif

            <div class="space-y-2">
                <p class="text-xs font-semibold text-surface-500 uppercase tracking-wider">Custos Fixos</p>
                @forelse($fixos->take(5) as $d)
                <div class="flex justify-between text-sm py-1 border-b border-surface-50 dark:border-surface-800">
                    <span class="text-surface-700 dark:text-surface-300 truncate max-w-[60%]">{{ $d->descricao }}</span>
                    <span class="text-red-600 dark:text-red-400 font-medium">R$ {{ number_format($d->valor_pago, 2, ',', '.') }}</span>
                </div>
                @empty
                <p class="text-xs text-surface-400 py-2">Nenhum custo fixo identificado</p>
                @endforelse

                <p class="text-xs font-semibold text-surface-500 uppercase tracking-wider pt-3">Custos Variáveis</p>
                @forelse($variaveis->take(5) as $d)
                <div class="flex justify-between text-sm py-1 border-b border-surface-50 dark:border-surface-800">
                    <span class="text-surface-700 dark:text-surface-300 truncate max-w-[60%]">{{ $d->descricao }}</span>
                    <span class="text-amber-600 dark:text-amber-400 font-medium">R$ {{ number_format($d->valor_pago, 2, ',', '.') }}</span>
                </div>
                @empty
                <p class="text-xs text-surface-400 py-2">Nenhum custo variável identificado</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    {{-- Nota metodológica --}}
    <x-ui.card>
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-surface-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>
            <div>
                <p class="text-xs font-semibold text-surface-700 dark:text-surface-300 mb-1">Como funciona a classificação</p>
                <p class="text-xs text-surface-500 leading-relaxed">
                    Despesas são classificadas como <strong>fixas</strong> quando a categoria ou descrição contém termos como "aluguel", "salário", "mensalidade", "assinatura" ou "condomínio".
                    As demais são tratadas como <strong>variáveis</strong>. O Ponto de Equilíbrio é calculado como:
                    <span class="font-mono bg-surface-100 dark:bg-surface-800 px-1 rounded">PE = Custos Fixos ÷ Margem de Contribuição</span>.
                </p>
            </div>
        </div>
    </x-ui.card>

</x-layouts.app>
