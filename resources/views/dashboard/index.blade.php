<x-layouts.app title="Dashboard">

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

        <x-ui.stat-card
            label="Contas a Pagar"
            value="R$ 48.320,00"
            :trend="-5"
            trend-label="vs mês anterior"
            color="red"
            :icon='\'<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>\''
        />

        <x-ui.stat-card
            label="Contas a Receber"
            value="R$ 87.650,00"
            :trend="12"
            trend-label="vs mês anterior"
            color="green"
            :icon='\'<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>\''
        />

        <x-ui.stat-card
            label="Saldo em Caixa"
            value="R$ 39.330,00"
            :trend="8"
            trend-label="vs mês anterior"
            color="primary"
            :icon='\'<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>\''
        />

        <x-ui.stat-card
            label="Vencidos Hoje"
            value="3 títulos"
            color="yellow"
            :icon='\'<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>\''
        />

    </div>

    {{-- Charts + Table --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">

        {{-- Chart placeholder --}}
        <x-ui.card class="xl:col-span-2">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-semibold text-surface-900 dark:text-white">Fluxo de Caixa</h3>
                    <p class="text-xs text-surface-500 mt-0.5">Entradas e saídas dos últimos 6 meses</p>
                </div>
                <x-ui.button variant="ghost" size="sm">Ver relatório</x-ui.button>
            </div>
            <div class="h-48 flex items-center justify-center bg-surface-50 dark:bg-surface-700/30 rounded-xl">
                <p class="text-sm text-surface-400">Gráfico (Chart.js)</p>
            </div>
        </x-ui.card>

        {{-- Quick actions --}}
        <x-ui.card>
            <h3 class="text-base font-semibold text-surface-900 dark:text-white mb-4">Ações Rápidas</h3>
            <div class="space-y-2">
                <x-ui.button variant="outline" class="w-full justify-start gap-3" size="md">
                    <svg class="w-4 h-4 text-surface-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nova Conta a Pagar
                </x-ui.button>
                <x-ui.button variant="outline" class="w-full justify-start gap-3" size="md">
                    <svg class="w-4 h-4 text-surface-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nova Conta a Receber
                </x-ui.button>
                <x-ui.button variant="outline" class="w-full justify-start gap-3" size="md">
                    <svg class="w-4 h-4 text-surface-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Conciliar Extratos
                </x-ui.button>
                <x-ui.button variant="outline" class="w-full justify-start gap-3" size="md">
                    <svg class="w-4 h-4 text-surface-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Exportar DRE
                </x-ui.button>
            </div>
        </x-ui.card>

    </div>

    {{-- Recent transactions --}}
    <x-ui.card :padding="false">
        <div class="flex items-center justify-between p-5 lg:p-6 border-b border-surface-100 dark:border-surface-700">
            <h3 class="text-base font-semibold text-surface-900 dark:text-white">Vencimentos Próximos</h3>
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @foreach([
                        ['Aluguel Sede', 'Imobiliária Silva', '15/05/2026', 'R$ 8.500,00', 'pending'],
                        ['Energia Elétrica', 'CEMIG', '18/05/2026', 'R$ 1.230,00', 'pending'],
                        ['Fornecedor XYZ', 'XYZ Ltda', '10/05/2026', 'R$ 5.400,00', 'overdue'],
                        ['Software ERP', 'TechSoft', '20/05/2026', 'R$ 890,00', 'pending'],
                    ] as $item)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4 text-sm font-medium text-surface-900 dark:text-white">{{ $item[0] }}</td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $item[1] }}</td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $item[2] }}</td>
                        <td class="px-5 py-4 text-sm font-semibold text-surface-900 dark:text-white text-right">{{ $item[3] }}</td>
                        <td class="px-5 py-4">
                            @if($item[4] === 'overdue')
                            <x-ui.badge variant="danger">Vencido</x-ui.badge>
                            @else
                            <x-ui.badge variant="warning">Pendente</x-ui.badge>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>

</x-layouts.app>
