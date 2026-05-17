<x-layouts.app title="Extrato Bancário">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Extrato Bancário</h1>
            <p class="text-sm text-surface-500 mt-0.5">Importe e concilie extratos OFX e CSV</p>
        </div>
        <x-ui.button href="{{ route('extratos.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
            Importar Extrato
        </x-ui.button>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Conta / Arquivo</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Período</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-surface-500 uppercase tracking-wider">Total</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-surface-500 uppercase tracking-wider">Conciliados</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-surface-500 uppercase tracking-wider">Pendentes</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($extratos as $extrato)
                    @php
                        $total       = $extrato->lancamentos_count ?? 0;
                        $conciliados = $extrato->conciliados_count ?? 0;
                        $pendentes   = $extrato->pendentes_count ?? 0;
                        $pct         = $total > 0 ? round(($conciliados / $total) * 100) : 0;
                    @endphp
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-surface-900 dark:text-white">{{ $extrato->contaBancaria->nome }}</div>
                            <div class="text-xs text-surface-400 truncate max-w-xs">{{ $extrato->nome_arquivo }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-600 dark:text-surface-400">
                            @if($extrato->data_inicio)
                            {{ $extrato->data_inicio->format('d/m/Y') }} — {{ $extrato->data_fim?->format('d/m/Y') }}
                            @else
                            —
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.badge variant="{{ $extrato->tipo === 'ofx' ? 'primary' : 'default' }}">
                                {{ strtoupper($extrato->tipo) }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-sm text-center text-surface-700 dark:text-surface-300">{{ $total }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ $conciliados }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="text-sm font-medium {{ $pendentes > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-surface-400' }}">
                                {{ $pendentes }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('extratos.show', $extrato) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                    {{ $pendentes > 0 ? 'Conciliar' : 'Ver detalhes' }}
                                </a>
                                <form method="POST" action="{{ route('extratos.destroy', $extrato) }}"
                                      onsubmit="return confirm('Remover este extrato? As movimentações vinculadas serão desmarcadas como conciliadas.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhum extrato importado.
                            <a href="{{ route('extratos.create') }}" class="text-primary-600 hover:underline ml-1">Importar agora</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($extratos->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $extratos->links() }}
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
