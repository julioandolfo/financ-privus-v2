<x-layouts.app title="DRE - Demonstrativo de Resultado">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">DRE — Demonstrativo de Resultado</h1>
            <p class="text-sm text-surface-500 mt-0.5">{{ $inicio->translatedFormat('F \d\e Y') }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-44">
                <x-ui.select name="mes" label="Mês">
                    @foreach($meses as $num => $nome)
                    <option value="{{ $num }}" @selected($mes == $num)>{{ ucfirst($nome) }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="w-32">
                <x-ui.select name="ano" label="Ano">
                    @foreach($anos as $a)
                    <option value="{{ $a }}" @selected($ano == $a)>{{ $a }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <x-ui.button type="submit" variant="secondary">Atualizar</x-ui.button>
        </form>
    </x-ui.card>

    {{-- Cards de resumo --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-ui.card class="bg-green-50 dark:bg-green-900/10 border-green-100 dark:border-green-800">
            <p class="text-xs font-medium text-green-700 dark:text-green-400 mb-1">Receitas Recebidas</p>
            <p class="text-2xl font-bold text-green-700 dark:text-green-300">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</p>
            @if($totalReceitasAnterior > 0)
            @php $varReceita = $totalReceitasAnterior > 0 ? (($totalReceitas - $totalReceitasAnterior) / $totalReceitasAnterior) * 100 : 0; @endphp
            <p class="text-xs mt-1 {{ $varReceita >= 0 ? 'text-green-600' : 'text-red-500' }}">
                {{ $varReceita >= 0 ? '▲' : '▼' }} {{ number_format(abs($varReceita), 1) }}% vs mês anterior
            </p>
            @endif
        </x-ui.card>
        <x-ui.card class="bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800">
            <p class="text-xs font-medium text-red-700 dark:text-red-400 mb-1">Despesas Pagas</p>
            <p class="text-2xl font-bold text-red-700 dark:text-red-300">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</p>
            @if($totalDespesasAnterior > 0)
            @php $varDespesa = $totalDespesasAnterior > 0 ? (($totalDespesas - $totalDespesasAnterior) / $totalDespesasAnterior) * 100 : 0; @endphp
            <p class="text-xs mt-1 {{ $varDespesa <= 0 ? 'text-green-600' : 'text-red-500' }}">
                {{ $varDespesa >= 0 ? '▲' : '▼' }} {{ number_format(abs($varDespesa), 1) }}% vs mês anterior
            </p>
            @endif
        </x-ui.card>
        <x-ui.card class="{{ $resultado >= 0 ? 'bg-primary-50 dark:bg-primary-900/10 border-primary-100 dark:border-primary-800' : 'bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800' }}">
            <p class="text-xs font-medium text-surface-500 mb-1">Resultado Líquido</p>
            <p class="text-2xl font-bold {{ $resultado >= 0 ? 'text-primary-700 dark:text-primary-300' : 'text-red-700 dark:text-red-300' }}">
                {{ $resultado >= 0 ? '+' : '' }}R$ {{ number_format($resultado, 2, ',', '.') }}
            </p>
            @if($resultadoAnterior != 0)
            @php $varResultado = $resultadoAnterior != 0 ? (($resultado - $resultadoAnterior) / abs($resultadoAnterior)) * 100 : 0; @endphp
            <p class="text-xs mt-1 {{ $varResultado >= 0 ? 'text-green-600' : 'text-red-500' }}">
                {{ $varResultado >= 0 ? '▲' : '▼' }} {{ number_format(abs($varResultado), 1) }}% vs mês anterior
            </p>
            @endif
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Receitas por categoria --}}
        <x-ui.card>
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4 flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500"></span>
                Receitas por Categoria
            </h2>
            @if($receitasPorCategoria->isNotEmpty())
            <div class="space-y-3">
                @foreach($receitasPorCategoria as $cat => $valor)
                @php $pct = $totalReceitas > 0 ? ($valor / $totalReceitas) * 100 : 0; @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-surface-700 dark:text-surface-300 truncate">{{ $cat }}</span>
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400 ml-2 whitespace-nowrap">R$ {{ number_format($valor, 2, ',', '.') }}</span>
                    </div>
                    <div class="h-1.5 bg-surface-100 dark:bg-surface-700 rounded-full overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-surface-400 mt-0.5">{{ number_format($pct, 1) }}% do total</p>
                </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700 flex justify-between items-center">
                <span class="text-sm font-semibold text-surface-700 dark:text-surface-300">Total Receitas</span>
                <span class="text-sm font-bold text-green-600 dark:text-green-400">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</span>
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
            <div class="space-y-3">
                @foreach($despesasPorCategoria as $cat => $valor)
                @php $pct = $totalDespesas > 0 ? ($valor / $totalDespesas) * 100 : 0; @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-surface-700 dark:text-surface-300 truncate">{{ $cat }}</span>
                        <span class="text-sm font-semibold text-red-600 dark:text-red-400 ml-2 whitespace-nowrap">R$ {{ number_format($valor, 2, ',', '.') }}</span>
                    </div>
                    <div class="h-1.5 bg-surface-100 dark:bg-surface-700 rounded-full overflow-hidden">
                        <div class="h-full bg-red-500 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-surface-400 mt-0.5">{{ number_format($pct, 1) }}% do total</p>
                </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700 flex justify-between items-center">
                <span class="text-sm font-semibold text-surface-700 dark:text-surface-300">Total Despesas</span>
                <span class="text-sm font-bold text-red-600 dark:text-red-400">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</span>
            </div>
            @else
            <p class="text-sm text-surface-400 text-center py-8">Nenhuma despesa paga neste período.</p>
            @endif
        </x-ui.card>

    </div>

    {{-- Resultado comparativo DRE formal --}}
    <x-ui.card class="mt-6">
        <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Demonstrativo Formal</h2>
        <table class="min-w-full">
            <thead>
                <tr class="border-b border-surface-100 dark:border-surface-700">
                    <th class="pb-2 text-left text-xs font-medium text-surface-500 uppercase">Descrição</th>
                    <th class="pb-2 text-right text-xs font-medium text-surface-500 uppercase">
                        {{ $inicio->translatedFormat('M/Y') }}
                    </th>
                    <th class="pb-2 text-right text-xs font-medium text-surface-500 uppercase">
                        {{ $inicio->copy()->subMonth()->translatedFormat('M/Y') }}
                    </th>
                    <th class="pb-2 text-right text-xs font-medium text-surface-500 uppercase">Var %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-50 dark:divide-surface-800">
                <tr class="bg-green-50/50 dark:bg-green-900/5">
                    <td class="py-2.5 text-sm font-semibold text-surface-900 dark:text-white">Receita Bruta</td>
                    <td class="py-2.5 text-sm text-right font-semibold text-green-600 dark:text-green-400">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</td>
                    <td class="py-2.5 text-sm text-right text-surface-500">R$ {{ number_format($totalReceitasAnterior, 2, ',', '.') }}</td>
                    <td class="py-2.5 text-sm text-right {{ isset($varReceita) && $varReceita >= 0 ? 'text-green-600' : 'text-red-500' }}">
                        @isset($varReceita){{ $varReceita >= 0 ? '+' : '' }}{{ number_format($varReceita, 1) }}%@endisset
                    </td>
                </tr>
                <tr class="bg-red-50/50 dark:bg-red-900/5">
                    <td class="py-2.5 text-sm font-semibold text-surface-900 dark:text-white">(-) Despesas Operacionais</td>
                    <td class="py-2.5 text-sm text-right font-semibold text-red-600 dark:text-red-400">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</td>
                    <td class="py-2.5 text-sm text-right text-surface-500">R$ {{ number_format($totalDespesasAnterior, 2, ',', '.') }}</td>
                    <td class="py-2.5 text-sm text-right {{ isset($varDespesa) && $varDespesa <= 0 ? 'text-green-600' : 'text-red-500' }}">
                        @isset($varDespesa){{ $varDespesa >= 0 ? '+' : '' }}{{ number_format($varDespesa, 1) }}%@endisset
                    </td>
                </tr>
                <tr class="border-t-2 border-surface-200 dark:border-surface-600">
                    <td class="py-3 text-sm font-bold text-surface-900 dark:text-white">= Resultado Líquido</td>
                    <td class="py-3 text-sm text-right font-bold {{ $resultado >= 0 ? 'text-primary-600 dark:text-primary-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $resultado >= 0 ? '+' : '' }}R$ {{ number_format($resultado, 2, ',', '.') }}
                    </td>
                    <td class="py-3 text-sm text-right text-surface-500">
                        {{ $resultadoAnterior >= 0 ? '+' : '' }}R$ {{ number_format($resultadoAnterior, 2, ',', '.') }}
                    </td>
                    <td class="py-3 text-sm text-right {{ isset($varResultado) && $varResultado >= 0 ? 'text-green-600' : 'text-red-500' }}">
                        @isset($varResultado){{ $varResultado >= 0 ? '+' : '' }}{{ number_format($varResultado, 1) }}%@endisset
                    </td>
                </tr>
            </tbody>
        </table>
    </x-ui.card>

</x-layouts.app>
