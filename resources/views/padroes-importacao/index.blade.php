<x-layouts.app title="Padrões de Importação de Extrato">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Padrões de Importação de Extrato</h1>
            <p class="text-sm text-surface-500 mt-0.5">
                Padrões são aplicados automaticamente durante a importação de extratos OFX/CSV para sugerir categorias.
            </p>
        </div>
        <x-ui.button href="{{ route('padroes-importacao.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Novo Padrão
        </x-ui.button>
    </div>

    {{-- Info Banner --}}
    <div class="mb-4 flex items-start gap-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-4 py-3">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
        <p class="text-sm text-blue-700 dark:text-blue-300">
            Os padrões são verificados em ordem de <strong>prioridade</strong> (maior primeiro). O primeiro padrão que corresponder ao texto da transação será aplicado. Padrões inativos são ignorados.
        </p>
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
                        <th class="px-5 py-3 text-center text-xs font-medium text-surface-500 uppercase tracking-wider w-16">Prioridade</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Texto Correspondente</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Correspondência</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Categoria</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição Sugerida</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-surface-500 uppercase tracking-wider">Ativo</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($padroes as $padrao)
                    @php
                        $corrBadge = match($padrao->tipo_correspondencia) {
                            'exato'      => ['warning', 'Exato'],
                            'comeca_com' => ['info',    'Começa com'],
                            default      => ['default', 'Contém'],
                        };
                        $tipoBadge = match($padrao->tipo_transacao) {
                            'debito'  => ['danger',  'Débito'],
                            'credito' => ['success', 'Crédito'],
                            default   => ['default', 'Ambos'],
                        };
                    @endphp
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                                {{ $padrao->prioridade > 50 ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' : 'bg-surface-100 dark:bg-surface-700 text-surface-600 dark:text-surface-300' }}">
                                {{ $padrao->prioridade }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="font-mono text-sm text-surface-900 dark:text-white">{{ $padrao->descricao_contem }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.badge :variant="$corrBadge[0]">{{ $corrBadge[1] }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.badge :variant="$tipoBadge[0]">{{ $tipoBadge[1] }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-600 dark:text-surface-400">
                            {{ $padrao->categoria?->nome ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-600 dark:text-surface-400">
                            {{ $padrao->descricao_padrao ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($padrao->ativo)
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-400" title="Ativo"></span>
                            @else
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-surface-300 dark:bg-surface-600" title="Inativo"></span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('padroes-importacao.edit', $padrao) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('padroes-importacao.destroy', $padrao) }}"
                                      onsubmit="return confirm('Remover este padrão?')">
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
                        <td colspan="8" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhum padrão de importação cadastrado.
                            <a href="{{ route('padroes-importacao.create') }}" class="text-primary-600 hover:underline ml-1">Criar primeiro padrão</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

</x-layouts.app>
