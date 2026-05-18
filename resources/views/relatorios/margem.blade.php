<x-layouts.app title="Margem e Lucratividade">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Margem e Lucratividade</h1>
            <p class="text-sm text-surface-500 mt-0.5">Análise de receitas, despesas e margem no período</p>
        </div>
    </div>

    {{-- Filtros --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-44">
                <x-ui.input type="date" name="de" label="De" value="{{ $de }}" />
            </div>
            <div class="w-44">
                <x-ui.input type="date" name="ate" label="Até" value="{{ $ate }}" />
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
            <a href="{{ url('/relatorios/margem/pdf') }}?{{ http_build_query(request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700"
               target="_blank">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
              PDF
            </a>
        </form>
    </x-ui.card>

    {{-- Cards de resumo --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.card class="bg-green-50 dark:bg-green-900/10 border-green-100 dark:border-green-800">
            <p class="text-xs font-medium text-green-700 dark:text-green-400 mb-1">Total Receitas</p>
            <p class="text-2xl font-bold text-green-700 dark:text-green-300">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</p>
            <p class="text-xs text-green-600 dark:text-green-500 mt-1">Recebidas no período</p>
        </x-ui.card>

        <x-ui.card class="bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800">
            <p class="text-xs font-medium text-red-700 dark:text-red-400 mb-1">Total Despesas</p>
            <p class="text-2xl font-bold text-red-700 dark:text-red-300">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</p>
            <p class="text-xs text-red-600 dark:text-red-500 mt-1">Pagas no período</p>
        </x-ui.card>

        <x-ui.card class="{{ $lucroLiquido >= 0 ? 'bg-primary-50 dark:bg-primary-900/10 border-primary-100 dark:border-primary-800' : 'bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800' }}">
            <p class="text-xs font-medium text-surface-500 mb-1">Lucro Líquido</p>
            <p class="text-2xl font-bold {{ $lucroLiquido >= 0 ? 'text-primary-700 dark:text-primary-300' : 'text-red-700 dark:text-red-300' }}">
                {{ $lucroLiquido >= 0 ? '+' : '' }}R$ {{ number_format($lucroLiquido, 2, ',', '.') }}
            </p>
            <p class="text-xs {{ $lucroLiquido >= 0 ? 'text-primary-600 dark:text-primary-500' : 'text-red-600 dark:text-red-500' }} mt-1">Receitas menos despesas</p>
        </x-ui.card>

        <x-ui.card class="{{ $margem >= 0 ? 'bg-primary-50 dark:bg-primary-900/10 border-primary-100 dark:border-primary-800' : 'bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800' }}">
            <p class="text-xs font-medium text-surface-500 mb-1">Margem Líquida</p>
            <p class="text-2xl font-bold {{ $margem >= 0 ? 'text-primary-700 dark:text-primary-300' : 'text-red-700 dark:text-red-300' }}">
                {{ number_format($margem, 1) }}%
            </p>
            <p class="text-xs {{ $margem >= 0 ? 'text-primary-600 dark:text-primary-500' : 'text-red-600 dark:text-red-500' }} mt-1">Sobre receita total</p>
        </x-ui.card>
    </div>

    {{-- Gráfico de evolução --}}
    <x-ui.card class="mb-6">
        <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Evolução dos Últimos 6 Meses</h2>
        <div class="relative" style="height: 280px;">
            <canvas id="margemChart"></canvas>
        </div>
    </x-ui.card>

    {{-- Tabelas por categoria --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Receitas por categoria --}}
        <x-ui.card>
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4 flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500"></span>
                Receitas por Categoria
            </h2>
            @if($receitasPorCategoria->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-surface-100 dark:border-surface-700">
                            <th class="pb-2 text-left text-xs font-medium text-surface-500 uppercase">Categoria</th>
                            <th class="pb-2 text-center text-xs font-medium text-surface-500 uppercase">Qtd</th>
                            <th class="pb-2 text-right text-xs font-medium text-surface-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-50 dark:divide-surface-800">
                        @foreach($receitasPorCategoria as $cat)
                        <tr>
                            <td class="py-2.5 text-sm text-surface-700 dark:text-surface-300 truncate max-w-[180px]">{{ $cat['nome'] }}</td>
                            <td class="py-2.5 text-sm text-center text-surface-500">{{ $cat['qtd'] }}</td>
                            <td class="py-2.5 text-sm text-right font-semibold text-green-600 dark:text-green-400">R$ {{ number_format($cat['total'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-surface-200 dark:border-surface-600">
                            <td class="pt-2.5 text-sm font-semibold text-surface-700 dark:text-surface-300" colspan="2">Total</td>
                            <td class="pt-2.5 text-sm text-right font-bold text-green-600 dark:text-green-400">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <p class="text-sm text-surface-400 text-center py-8">Nenhuma receita recebida neste período.</p>
            @endif
        </x-ui.card>

        {{-- Despesas por categoria --}}
        <x-ui.card>
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4 flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500"></span>
                Despesas por Categoria
            </h2>
            @if($despesasPorCategoria->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-surface-100 dark:border-surface-700">
                            <th class="pb-2 text-left text-xs font-medium text-surface-500 uppercase">Categoria</th>
                            <th class="pb-2 text-center text-xs font-medium text-surface-500 uppercase">Qtd</th>
                            <th class="pb-2 text-right text-xs font-medium text-surface-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-50 dark:divide-surface-800">
                        @foreach($despesasPorCategoria as $cat)
                        <tr>
                            <td class="py-2.5 text-sm text-surface-700 dark:text-surface-300 truncate max-w-[180px]">{{ $cat['nome'] }}</td>
                            <td class="py-2.5 text-sm text-center text-surface-500">{{ $cat['qtd'] }}</td>
                            <td class="py-2.5 text-sm text-right font-semibold text-red-600 dark:text-red-400">R$ {{ number_format($cat['total'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-surface-200 dark:border-surface-600">
                            <td class="pt-2.5 text-sm font-semibold text-surface-700 dark:text-surface-300" colspan="2">Total</td>
                            <td class="pt-2.5 text-sm text-right font-bold text-red-600 dark:text-red-400">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <p class="text-sm text-surface-400 text-center py-8">Nenhuma despesa paga neste período.</p>
            @endif
        </x-ui.card>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        const data = @json($meses);
        const labels   = data.map(m => m.label);
        const receitas = data.map(m => m.receita);
        const despesas = data.map(m => m.despesa);
        const lucros   = data.map(m => m.lucro);

        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
        const tickColor = isDark ? '#94a3b8' : '#64748b';

        new Chart(document.getElementById('margemChart'), {
            data: {
                labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Receitas',
                        data: receitas,
                        backgroundColor: 'rgba(34,197,94,0.7)',
                        borderColor: 'rgba(34,197,94,1)',
                        borderWidth: 1,
                        borderRadius: 4,
                        order: 2,
                    },
                    {
                        type: 'bar',
                        label: 'Despesas',
                        data: despesas,
                        backgroundColor: 'rgba(239,68,68,0.7)',
                        borderColor: 'rgba(239,68,68,1)',
                        borderWidth: 1,
                        borderRadius: 4,
                        order: 2,
                    },
                    {
                        type: 'line',
                        label: 'Lucro',
                        data: lucros,
                        borderColor: 'rgba(99,102,241,1)',
                        backgroundColor: 'rgba(99,102,241,0.1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(99,102,241,1)',
                        pointRadius: 4,
                        tension: 0.3,
                        fill: false,
                        order: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        labels: { color: tickColor, font: { size: 12 } },
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' R$ ' + ctx.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 2 }),
                        },
                    },
                },
                scales: {
                    x: {
                        ticks: { color: tickColor },
                        grid: { color: gridColor },
                    },
                    y: {
                        ticks: {
                            color: tickColor,
                            callback: v => 'R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 0 }),
                        },
                        grid: { color: gridColor },
                    },
                },
            },
        });
    })();
    </script>
    @endpush

</x-layouts.app>
