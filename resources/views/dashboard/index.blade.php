<x-layouts.app title="Dashboard">

    {{-- Period filter bar --}}
    <div class="flex flex-wrap items-center gap-2 mb-6" x-data>
        @php
            $periodos = [
                'hoje'      => 'Hoje',
                'semana'    => 'Esta Semana',
                'mes'       => 'Este Mês',
                'trimestre' => 'Trimestre',
                'ano'       => 'Ano',
            ];
            $baseUrl = route('dashboard');
        @endphp

        @foreach($periodos as $key => $label)
            <button
                type="button"
                @click="window.location.href = '{{ $baseUrl }}?periodo={{ $key }}'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                    {{ $periodo === $key
                        ? 'bg-primary-600 text-white shadow-sm'
                        : 'bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700' }}"
            >
                {{ $label }}
            </button>
        @endforeach

        {{-- Custom date range --}}
        <form method="GET" action="{{ $baseUrl }}" class="flex items-center gap-2 ml-2">
            <input
                type="date"
                name="de"
                value="{{ $periodo === 'custom' ? $de->toDateString() : '' }}"
                class="px-3 py-2 rounded-lg text-sm border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-700 dark:text-surface-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
            <span class="text-surface-400 text-sm">até</span>
            <input
                type="date"
                name="ate"
                value="{{ $periodo === 'custom' ? $ate->toDateString() : '' }}"
                class="px-3 py-2 rounded-lg text-sm border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-700 dark:text-surface-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
            <button
                type="submit"
                class="px-3 py-2 rounded-lg text-sm font-medium bg-surface-100 dark:bg-surface-700 text-surface-700 dark:text-surface-300 hover:bg-surface-200 dark:hover:bg-surface-600 transition-colors"
            >
                Filtrar
            </button>
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

        <x-ui.stat-card
            label="Contas a Pagar"
            :value="'R$ ' . number_format($totalPagar, 2, ',', '.')"
            color="red"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z\" /></svg>"'
        />

        <x-ui.stat-card
            label="Contas a Receber"
            :value="'R$ ' . number_format($totalReceber, 2, ',', '.')"
            color="green"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\" /></svg>"'
        />

        <x-ui.stat-card
            label="Saldo em Contas"
            :value="'R$ ' . number_format($saldoContas, 2, ',', '.')"
            color="primary"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z\" /></svg>"'
        />

        <x-ui.stat-card
            label="Títulos Vencidos"
            :value="($vencidosPagar + $vencidosReceber) . ' título' . (($vencidosPagar + $vencidosReceber) != 1 ? 's' : '')"
            color="yellow"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z\" /></svg>"'
        />

    </div>

    {{-- Receita / Despesa do Mês --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

        <x-ui.stat-card
            label="Receita do Mês"
            :value="'R$ ' . number_format($receitasMes, 2, ',', '.')"
            color="green"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941\" /></svg>"'
        />

        <x-ui.stat-card
            label="Despesa do Mês"
            :value="'R$ ' . number_format($despesasMes, 2, ',', '.')"
            color="red"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181\" /></svg>"'
        />

    </div>

    {{-- Charts + Quick actions --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">

        <x-ui.card class="xl:col-span-2">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-semibold text-surface-900 dark:text-white">Fluxo de Caixa</h3>
                    <p class="text-xs text-surface-500 mt-0.5">Entradas e saídas dos últimos 6 meses</p>
                </div>
                <x-ui.button variant="ghost" size="sm">Ver relatório</x-ui.button>
            </div>
            <div class="h-48">
                <canvas id="fluxoChart" class="w-full h-full"></canvas>
            </div>
        </x-ui.card>

        <x-ui.card>
            <h3 class="text-base font-semibold text-surface-900 dark:text-white mb-4">Ações Rápidas</h3>
            <div class="space-y-2">
                <x-ui.button variant="outline" class="w-full justify-start gap-3" size="md" href="{{ route('contas-pagar.create') }}">
                    <svg class="w-4 h-4 text-surface-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nova Conta a Pagar
                </x-ui.button>
                <x-ui.button variant="outline" class="w-full justify-start gap-3" size="md" href="{{ route('contas-receber.create') }}">
                    <svg class="w-4 h-4 text-surface-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nova Conta a Receber
                </x-ui.button>
                <x-ui.button variant="outline" class="w-full justify-start gap-3" size="md" href="{{ route('clientes.create') }}">
                    <svg class="w-4 h-4 text-surface-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                    Novo Cliente
                </x-ui.button>
                <x-ui.button variant="outline" class="w-full justify-start gap-3" size="md" href="{{ route('fornecedores.create') }}">
                    <svg class="w-4 h-4 text-surface-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                    Novo Fornecedor
                </x-ui.button>
            </div>
        </x-ui.card>

    </div>

    {{-- Inadimplência --}}
    <x-ui.card class="mb-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-semibold text-surface-900 dark:text-white">Inadimplência</h3>
                <p class="text-xs text-surface-500 mt-0.5">Contas a receber vencidas e não pagas</p>
            </div>
            <x-ui.button variant="ghost" size="sm" href="{{ route('contas-receber.index') }}">Ver todas</x-ui.button>
        </div>
        <div class="flex flex-wrap items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-surface-500 dark:text-surface-400">Títulos em atraso</p>
                    <p class="text-2xl font-bold text-surface-900 dark:text-white">
                        {{ $inadimplentes['count'] }}
                        <span class="text-sm font-normal text-surface-500">
                            {{ $inadimplentes['count'] == 1 ? 'título' : 'títulos' }}
                        </span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-surface-500 dark:text-surface-400">Total inadimplente</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                        R$ {{ number_format($inadimplentes['total'], 2, ',', '.') }}
                    </p>
                </div>
            </div>
            @if($inadimplentes['count'] > 0)
            <div class="ml-auto">
                <x-ui.badge variant="danger">
                    {{ $inadimplentes['count'] }} {{ $inadimplentes['count'] == 1 ? 'vencido' : 'vencidos' }}
                </x-ui.badge>
            </div>
            @endif
        </div>
    </x-ui.card>

    {{-- Upcoming due dates --}}
    <x-ui.card :padding="false">
        <div class="flex items-center justify-between p-5 lg:p-6 border-b border-surface-100 dark:border-surface-700">
            <div>
                <h3 class="text-base font-semibold text-surface-900 dark:text-white">Vencimentos Próximos</h3>
                <p class="text-xs text-surface-500 mt-0.5">Contas a pagar nos próximos 7 dias</p>
            </div>
            <x-ui.button variant="ghost" size="sm" href="{{ route('contas-pagar.index') }}">Ver todos</x-ui.button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Fornecedor</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Vencimento</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($vencimentosProximos as $conta)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4 text-sm font-medium text-surface-900 dark:text-white">{{ $conta->descricao }}</td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $conta->fornecedor?->nome_razao_social ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $conta->data_vencimento->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-sm font-semibold text-surface-900 dark:text-white text-right">R$ {{ number_format($conta->valor_total, 2, ',', '.') }}</td>
                        <td class="px-5 py-4">
                            @if($conta->status === 'vencido' || $conta->data_vencimento->isPast())
                                <x-ui.badge variant="danger">Vencido</x-ui.badge>
                            @else
                                <x-ui.badge variant="warning">Pendente</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <x-ui.button variant="ghost" size="sm" href="{{ route('contas-pagar.edit', $conta) }}">Baixar</x-ui.button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-surface-400">
                            Nenhum vencimento nos próximos 7 dias
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const labels   = @json($fluxoLabels);
            const entradas = @json($fluxoEntradas);
            const saidas   = @json($fluxoSaidas);

            const isDark = document.documentElement.classList.contains('dark');
            const gridColor  = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
            const labelColor = isDark ? '#94a3b8' : '#64748b';

            const ctx = document.getElementById('fluxoChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Entradas',
                            data: entradas,
                            backgroundColor: 'rgba(34,197,94,0.7)',
                            borderColor: 'rgb(34,197,94)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'Saídas',
                            data: saidas,
                            backgroundColor: 'rgba(239,68,68,0.7)',
                            borderColor: 'rgb(239,68,68)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: labelColor, font: { size: 12 } },
                        },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const val = ctx.parsed.y.toLocaleString('pt-BR', {
                                        style: 'currency', currency: 'BRL'
                                    });
                                    return ' ' + ctx.dataset.label + ': ' + val;
                                }
                            }
                        },
                    },
                    scales: {
                        x: {
                            ticks: { color: labelColor },
                            grid:  { color: gridColor },
                        },
                        y: {
                            ticks: {
                                color: labelColor,
                                callback: function (v) {
                                    return 'R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 0 });
                                }
                            },
                            grid: { color: gridColor },
                        },
                    },
                },
            });
        })();
    </script>

</x-layouts.app>
