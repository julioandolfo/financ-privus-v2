<x-layouts.app title="Centros de Custo">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Centros de Custo</h1>
            <p class="text-sm text-surface-500 mt-0.5">Gerencie centros de custo para análise gerencial</p>
        </div>
        <x-ui.button href="{{ route('centros-custo.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Novo Centro
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
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Nome</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Código</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($centros as $centro)
                    <tr class="bg-surface-50/50 dark:bg-surface-800/30 hover:bg-surface-100 dark:hover:bg-surface-700/50 transition-colors">
                        <td class="px-5 py-3 text-sm font-semibold text-surface-900 dark:text-white">{{ $centro->nome }}</td>
                        <td class="px-5 py-3 text-sm text-surface-500">{{ $centro->codigo ?? '—' }}</td>
                        <td class="px-5 py-3"><x-ui.badge :variant="$centro->ativo ? 'success' : 'default'">{{ $centro->ativo ? 'Ativo' : 'Inativo' }}</x-ui.badge></td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('centros-custo.edit', $centro) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">Editar</a>
                                <form method="POST" action="{{ route('centros-custo.destroy', $centro) }}" onsubmit="return confirm('Remover este centro?')">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @foreach($centro->filhos->sortBy('nome') as $filho)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2 pl-4">
                                <div class="w-3 h-px bg-surface-300 dark:bg-surface-600 flex-shrink-0"></div>
                                <span class="text-sm text-surface-700 dark:text-surface-300">{{ $filho->nome }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-surface-500">{{ $filho->codigo ?? '—' }}</td>
                        <td class="px-5 py-3"><x-ui.badge :variant="$filho->ativo ? 'success' : 'default'">{{ $filho->ativo ? 'Ativo' : 'Inativo' }}</x-ui.badge></td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('centros-custo.edit', $filho) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">Editar</a>
                                <form method="POST" action="{{ route('centros-custo.destroy', $filho) }}" onsubmit="return confirm('Remover?')">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @empty
                    <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-surface-400">Nenhum centro cadastrado. <a href="{{ route('centros-custo.create') }}" class="text-primary-600 hover:underline">Criar agora</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>
