<x-layouts.app title="Relatório de Inadimplência">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Inadimplência</h1>
            <p class="text-sm text-surface-500 mt-0.5">Títulos vencidos e não pagos</p>
        </div>
    </div>

    {{-- Filtros --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-36">
                <x-ui.input name="de" type="date" label="Vencimento de" value="{{ $de }}" />
            </div>
            <div class="w-36">
                <x-ui.input name="ate" type="date" label="Vencimento até" value="{{ $ate }}" />
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
            <a href="{{ url('/relatorios/inadimplencia/pdf') }}?{{ http_build_query(request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700"
               target="_blank">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
              PDF
            </a>
        </form>
    </x-ui.card>

    {{-- Cards de resumo --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-ui.card class="bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800">
            <p class="text-xs font-medium text-red-700 dark:text-red-400 mb-1">Total em Aberto</p>
            <p class="text-2xl font-bold text-red-700 dark:text-red-300">R$ {{ number_format($totalAberto, 2, ',', '.') }}</p>
        </x-ui.card>
        <x-ui.card class="bg-surface-50 dark:bg-surface-800/50">
            <p class="text-xs font-medium text-surface-500 mb-1">Qtd de Títulos</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-white">{{ $qtd }}</p>
        </x-ui.card>
        <x-ui.card class="bg-orange-50 dark:bg-orange-900/10 border-orange-100 dark:border-orange-800">
            <p class="text-xs font-medium text-orange-700 dark:text-orange-400 mb-1">Total Vencido</p>
            <p class="text-2xl font-bold text-orange-700 dark:text-orange-300">R$ {{ number_format($total, 2, ',', '.') }}</p>
        </x-ui.card>
    </div>

    {{-- Por Cliente --}}
    <x-ui.card :padding="false" class="mb-6">
        <div class="px-5 py-4 border-b border-surface-100 dark:border-surface-700">
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Inadimplência por Cliente</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Qtd Títulos</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Total</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor Recebido</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor em Aberto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($porCliente as $item)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-3.5 text-sm text-surface-900 dark:text-white">
                            {{ $item['cliente']?->nome ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right text-surface-600 dark:text-surface-400">{{ $item['qtd'] }}</td>
                        <td class="px-5 py-3.5 text-sm text-right text-surface-600 dark:text-surface-400">R$ {{ number_format($item['total'], 2, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-sm text-right text-green-600 dark:text-green-400">R$ {{ number_format($item['total'] - $item['aberto'], 2, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold text-red-600 dark:text-red-400">R$ {{ number_format($item['aberto'], 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-surface-400">Nenhum título inadimplente encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($porCliente->isNotEmpty())
                <tfoot class="border-t-2 border-surface-200 dark:border-surface-600">
                    <tr class="bg-surface-50 dark:bg-surface-800/50 font-semibold">
                        <td class="px-5 py-3.5 text-sm text-surface-900 dark:text-white">Total</td>
                        <td class="px-5 py-3.5 text-sm text-right text-surface-700 dark:text-surface-300">{{ $qtd }}</td>
                        <td class="px-5 py-3.5 text-sm text-right text-surface-700 dark:text-surface-300">R$ {{ number_format($total, 2, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-sm text-right text-green-700 dark:text-green-300">R$ {{ number_format($totalRecebido, 2, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-sm text-right text-red-700 dark:text-red-300">R$ {{ number_format($totalAberto, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-ui.card>

    {{-- Títulos individuais --}}
    <x-ui.card :padding="false">
        <div class="px-5 py-4 border-b border-surface-100 dark:border-surface-700">
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Títulos Vencidos</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Vencimento</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Dias em Atraso</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor Total</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor em Aberto</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($contas as $conta)
                    @php $diasAtraso = now()->diffInDays($conta->data_vencimento); @endphp
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-3.5 text-sm text-surface-900 dark:text-white">{{ $conta->cliente?->nome ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-sm text-surface-600 dark:text-surface-400 max-w-xs truncate">{{ $conta->descricao }}</td>
                        <td class="px-5 py-3.5 text-sm text-right text-surface-600 dark:text-surface-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold whitespace-nowrap
                            {{ $diasAtraso > 30 ? 'text-red-600 dark:text-red-400' : 'text-orange-600 dark:text-orange-400' }}">
                            {{ $diasAtraso }} dias
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right text-surface-600 dark:text-surface-400 whitespace-nowrap">
                            R$ {{ number_format($conta->valor_total, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold text-red-600 dark:text-red-400 whitespace-nowrap">
                            R$ {{ number_format($conta->valor_total - $conta->valor_recebido, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $conta->status === 'parcial' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ ucfirst($conta->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-surface-400">Nenhum título inadimplente encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

</x-layouts.app>
