<x-layouts.app title="Fluxo de Caixa">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Fluxo de Caixa</h1>
            <p class="text-sm text-surface-500 mt-0.5">
                {{ $de->format('d/m/Y') }} até {{ $ate->format('d/m/Y') }}
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-36">
                <x-ui.input name="de" type="date" label="De" value="{{ $de->format('Y-m-d') }}" />
            </div>
            <div class="w-36">
                <x-ui.input name="ate" type="date" label="Até" value="{{ $ate->format('Y-m-d') }}" />
            </div>
            <div class="w-44">
                <x-ui.select name="agrupamento" label="Agrupamento">
                    <option value="dia"    @selected($agrupamento === 'dia')>Por Dia</option>
                    <option value="semana" @selected($agrupamento === 'semana')>Por Semana</option>
                    <option value="mes"    @selected($agrupamento === 'mes')>Por Mês</option>
                </x-ui.select>
            </div>
            <div class="w-48">
                <x-ui.select name="conta_bancaria_id" label="Conta">
                    <option value="">Todas as contas</option>
                    @foreach($contas as $c)
                    <option value="{{ $c->id }}" @selected($contaId == $c->id)>{{ $c->nome }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
        </form>
    </x-ui.card>

    {{-- Totais --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
        <x-ui.card class="bg-surface-50 dark:bg-surface-800/50">
            <p class="text-xs font-medium text-surface-500 mb-1">Saldo Inicial</p>
            <p class="text-xl font-bold {{ $saldoAnterior >= 0 ? 'text-surface-900 dark:text-white' : 'text-red-600 dark:text-red-400' }}">
                R$ {{ number_format($saldoAnterior, 2, ',', '.') }}
            </p>
        </x-ui.card>
        <x-ui.card class="bg-green-50 dark:bg-green-900/10 border-green-100 dark:border-green-800">
            <p class="text-xs font-medium text-green-700 dark:text-green-400 mb-1">Total Entradas</p>
            <p class="text-xl font-bold text-green-700 dark:text-green-300">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</p>
        </x-ui.card>
        <x-ui.card class="bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800">
            <p class="text-xs font-medium text-red-700 dark:text-red-400 mb-1">Total Saídas</p>
            <p class="text-xl font-bold text-red-700 dark:text-red-300">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</p>
        </x-ui.card>
        <x-ui.card class="{{ $saldoPeriodo >= 0 ? 'bg-primary-50 dark:bg-primary-900/10 border-primary-100 dark:border-primary-800' : 'bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800' }}">
            <p class="text-xs font-medium text-surface-500 mb-1">Saldo do Período</p>
            <p class="text-xl font-bold {{ $saldoPeriodo >= 0 ? 'text-primary-700 dark:text-primary-300' : 'text-red-700 dark:text-red-300' }}">
                {{ $saldoPeriodo >= 0 ? '+' : '' }}R$ {{ number_format($saldoPeriodo, 2, ',', '.') }}
            </p>
        </x-ui.card>
    </div>

    {{-- Tabela por período --}}
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Período</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Entradas</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Saídas</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Saldo do Período</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Saldo Acumulado</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Lançamentos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($periodos as $p)
                    @php $saldo = $p->entradas - $p->saidas; @endphp
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-3.5 text-sm font-medium text-surface-900 dark:text-white whitespace-nowrap">{{ $p->periodo }}</td>
                        <td class="px-5 py-3.5 text-sm text-right text-green-600 dark:text-green-400 font-medium whitespace-nowrap">
                            + R$ {{ number_format($p->entradas, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right text-red-600 dark:text-red-400 font-medium whitespace-nowrap">
                            − R$ {{ number_format($p->saidas, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold whitespace-nowrap {{ $saldo >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                            {{ $saldo >= 0 ? '+' : '' }}R$ {{ number_format($saldo, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold whitespace-nowrap {{ $p->saldo_acumulado >= 0 ? 'text-surface-900 dark:text-white' : 'text-red-700 dark:text-red-300' }}">
                            R$ {{ number_format($p->saldo_acumulado, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right text-surface-500 whitespace-nowrap">{{ $p->total_lancamentos }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhuma movimentação no período selecionado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($periodos->isNotEmpty())
                <tfoot class="border-t-2 border-surface-200 dark:border-surface-600">
                    <tr class="bg-surface-50 dark:bg-surface-800/50 font-semibold">
                        <td class="px-5 py-3.5 text-sm text-surface-900 dark:text-white">Total</td>
                        <td class="px-5 py-3.5 text-sm text-right text-green-700 dark:text-green-300">+ R$ {{ number_format($totalEntradas, 2, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-sm text-right text-red-700 dark:text-red-300">− R$ {{ number_format($totalSaidas, 2, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-sm text-right {{ $saldoPeriodo >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                            {{ $saldoPeriodo >= 0 ? '+' : '' }}R$ {{ number_format($saldoPeriodo, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right text-surface-900 dark:text-white">—</td>
                        <td class="px-5 py-3.5 text-sm text-right text-surface-500">{{ $periodos->sum('total_lancamentos') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-ui.card>

</x-layouts.app>
