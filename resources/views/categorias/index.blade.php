<x-layouts.app title="Categorias Financeiras">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Categorias Financeiras</h1>
            <p class="text-sm text-surface-500 mt-0.5">Classifique receitas e despesas</p>
        </div>
        <x-ui.button href="{{ route('categorias.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nova Categoria
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
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Nome</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Código</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($categorias as $cat)
                    {{-- Categoria pai --}}
                    <tr class="bg-surface-50/50 dark:bg-surface-800/30 hover:bg-surface-100 dark:hover:bg-surface-700/50 transition-colors">
                        <td class="px-5 py-3">
                            <span class="text-sm font-semibold text-surface-900 dark:text-white">{{ $cat->nome }}</span>
                        </td>
                        <td class="px-5 py-3 text-sm text-surface-500">{{ $cat->codigo ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @php
                                $tipoMap = ['receita' => ['success','Receita'], 'despesa' => ['danger','Despesa'], 'ambos' => ['info','Ambos']];
                                [$v, $l] = $tipoMap[$cat->tipo] ?? ['default', $cat->tipo];
                            @endphp
                            <x-ui.badge :variant="$v">{{ $l }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-3">
                            <x-ui.badge :variant="$cat->ativo ? 'success' : 'default'">{{ $cat->ativo ? 'Ativa' : 'Inativa' }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('categorias.edit', $cat) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">Editar</a>
                                <form method="POST" action="{{ route('categorias.destroy', $cat) }}" onsubmit="return confirm('Remover categoria e todas as subcategorias?')">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    {{-- Subcategorias --}}
                    @foreach($cat->filhas->sortBy('nome') as $sub)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2 pl-4">
                                <div class="w-3 h-px bg-surface-300 dark:bg-surface-600 flex-shrink-0"></div>
                                <span class="text-sm text-surface-700 dark:text-surface-300">{{ $sub->nome }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-surface-500">{{ $sub->codigo ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @php [$v2, $l2] = $tipoMap[$sub->tipo] ?? ['default', $sub->tipo]; @endphp
                            <x-ui.badge :variant="$v2">{{ $l2 }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-3">
                            <x-ui.badge :variant="$sub->ativo ? 'success' : 'default'">{{ $sub->ativo ? 'Ativa' : 'Inativa' }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('categorias.edit', $sub) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">Editar</a>
                                <form method="POST" action="{{ route('categorias.destroy', $sub) }}" onsubmit="return confirm('Remover subcategoria?')">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhuma categoria cadastrada.
                            <a href="{{ route('categorias.create') }}" class="text-primary-600 hover:underline ml-1">Criar agora</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

</x-layouts.app>
