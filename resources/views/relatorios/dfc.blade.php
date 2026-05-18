<x-layouts.app title="DFC - Demonstrativo de Fluxo de Caixa">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">DFC — Demonstrativo de Fluxo de Caixa</h1>
            <p class="text-sm text-surface-500 mt-0.5">{{ \Carbon\Carbon::parse($de)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($ate)->format('d/m/Y') }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-36">
                <x-ui.input name="de" type="date" label="De" value="{{ $de }}" />
            </div>
            <div class="w-36">
                <x-ui.input name="ate" type="date" label="Até" value="{{ $ate }}" />
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
            <a href="{{ url('/relatorios/dfc/pdf') }}?{{ http_build_query(request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700"
               target="_blank">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
              PDF
            </a>
        </form>
    </x-ui.card>

    {{-- Cards de resumo --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-ui.card class="bg-green-50 dark:bg-green-900/10 border-green-100 dark:border-green-800">
            <p class="text-xs font-medium text-green-700 dark:text-green-400 mb-1">Total Entradas</p>
            <p class="text-2xl font-bold text-green-700 dark:text-green-300">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</p>
        </x-ui.card>
        <x-ui.card class="bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800">
            <p class="text-xs font-medium text-red-700 dark:text-red-400 mb-1">Total Saídas</p>
            <p class="text-2xl font-bold text-red-700 dark:text-red-300">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</p>
        </x-ui.card>
        <x-ui.card class="{{ $saldoPeriodo >= 0 ? 'bg-primary-50 dark:bg-primary-900/10 border-primary-100 dark:border-primary-800' : 'bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800' }}">
            <p class="text-xs font-medium text-surface-500 mb-1">Saldo do Período</p>
            <p class="text-2xl font-bold {{ $saldoPeriodo >= 0 ? 'text-primary-700 dark:text-primary-300' : 'text-red-700 dark:text-red-300' }}">
                {{ $saldoPeriodo >= 0 ? '+' : '' }}R$ {{ number_format($saldoPeriodo, 2, ',', '.') }}
            </p>
        </x-ui.card>
    </div>

    {{-- Tabela Entradas --}}
    <x-ui.card :padding="false" class="mb-6">
        <div class="px-5 py-4 border-b border-surface-100 dark:border-surface-700">
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500"></span>
                Entradas
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Data</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($recebimentos as $r)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-3.5 text-sm text-surface-600 dark:text-surface-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($r->data_recebimento)->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-surface-900 dark:text-white max-w-xs truncate">{{ $r->descricao }}</td>
                        <td class="px-5 py-3.5 text-sm text-surface-600 dark:text-surface-400">{{ $r->cliente?->nome ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold text-green-600 dark:text-green-400 whitespace-nowrap">
                            R$ {{ number_format($r->valor_recebido, 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-sm text-surface-400">Nenhum recebimento no período.</td>
                    </tr>
                    @endforelse
                    @foreach($movimentacoes->where('tipo', 'entrada') as $mov)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-3.5 text-sm text-surface-600 dark:text-surface-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($mov->data_movimentacao)->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-surface-900 dark:text-white max-w-xs truncate">{{ $mov->descricao }}</td>
                        <td class="px-5 py-3.5 text-sm text-surface-600 dark:text-surface-400">{{ $mov->categoria?->nome ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold text-green-600 dark:text-green-400 whitespace-nowrap">
                            R$ {{ number_format($mov->valor, 2, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                @if($recebimentos->isNotEmpty() || $movimentacoes->where('tipo', 'entrada')->isNotEmpty())
                <tfoot class="border-t-2 border-surface-200 dark:border-surface-600">
                    <tr class="bg-surface-50 dark:bg-surface-800/50 font-semibold">
                        <td colspan="3" class="px-5 py-3.5 text-sm text-surface-900 dark:text-white">Total Entradas</td>
                        <td class="px-5 py-3.5 text-sm text-right text-green-700 dark:text-green-300">
                            R$ {{ number_format($totalEntradas, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-ui.card>

    {{-- Tabela Saídas --}}
    <x-ui.card :padding="false">
        <div class="px-5 py-4 border-b border-surface-100 dark:border-surface-700">
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500"></span>
                Saídas
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Data</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Fornecedor</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($pagamentos as $p)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-3.5 text-sm text-surface-600 dark:text-surface-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($p->data_pagamento)->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-surface-900 dark:text-white max-w-xs truncate">{{ $p->descricao }}</td>
                        <td class="px-5 py-3.5 text-sm text-surface-600 dark:text-surface-400">{{ $p->fornecedor?->nome ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold text-red-600 dark:text-red-400 whitespace-nowrap">
                            R$ {{ number_format($p->valor_pago, 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-sm text-surface-400">Nenhum pagamento no período.</td>
                    </tr>
                    @endforelse
                    @foreach($movimentacoes->where('tipo', 'saida') as $mov)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-3.5 text-sm text-surface-600 dark:text-surface-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($mov->data_movimentacao)->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-surface-900 dark:text-white max-w-xs truncate">{{ $mov->descricao }}</td>
                        <td class="px-5 py-3.5 text-sm text-surface-600 dark:text-surface-400">{{ $mov->categoria?->nome ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold text-red-600 dark:text-red-400 whitespace-nowrap">
                            R$ {{ number_format($mov->valor, 2, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                @if($pagamentos->isNotEmpty() || $movimentacoes->where('tipo', 'saida')->isNotEmpty())
                <tfoot class="border-t-2 border-surface-200 dark:border-surface-600">
                    <tr class="bg-surface-50 dark:bg-surface-800/50 font-semibold">
                        <td colspan="3" class="px-5 py-3.5 text-sm text-surface-900 dark:text-white">Total Saídas</td>
                        <td class="px-5 py-3.5 text-sm text-right text-red-700 dark:text-red-300">
                            R$ {{ number_format($totalSaidas, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-ui.card>

</x-layouts.app>
