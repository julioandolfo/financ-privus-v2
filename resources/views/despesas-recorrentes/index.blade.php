<x-layouts.app title="Despesas Recorrentes">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Despesas Recorrentes</h1>
            <p class="text-sm text-surface-500 mt-0.5">Despesas fixas geradas automaticamente</p>
        </div>
        <x-ui.button href="{{ route('despesas-recorrentes.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nova Recorrência
        </x-ui.button>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
    @endif

    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Fornecedor</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Frequência</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Próx. Geração</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($recorrencias as $r)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors {{ !$r->ativo ? 'opacity-50' : '' }}">
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $r->descricao }}</p>
                            @if($r->categoria)
                            <p class="text-xs text-surface-400 mt-0.5">{{ $r->categoria->nome }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $r->fornecedor?->nome_razao_social ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <x-ui.badge variant="secondary">{{ $r->frequencia_label }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-sm font-semibold text-right text-red-600 dark:text-red-400 whitespace-nowrap">
                            R$ {{ number_format($r->valor, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500 whitespace-nowrap">
                            @if($r->proxima_geracao)
                                <span class="{{ $r->proxima_geracao->isPast() ? 'text-amber-600 dark:text-amber-400 font-medium' : '' }}">
                                    {{ $r->proxima_geracao->format('d/m/Y') }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.badge :variant="$r->ativo ? 'success' : 'secondary'">
                                {{ $r->ativo ? 'Ativa' : 'Inativa' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <form method="POST" action="{{ route('despesas-recorrentes.gerar', $r) }}">
                                    @csrf
                                    <button class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors" title="Gerar agora">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                        Gerar
                                    </button>
                                </form>
                                <a href="{{ route('despesas-recorrentes.edit', $r) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">Editar</a>
                                <form method="POST" action="{{ route('despesas-recorrentes.toggle', $r) }}">
                                    @csrf
                                    <button class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                        {{ $r->ativo ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-surface-400">
                        Nenhuma despesa recorrente. <a href="{{ route('despesas-recorrentes.create') }}" class="text-primary-600 hover:underline">Criar primeira</a>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

</x-layouts.app>
