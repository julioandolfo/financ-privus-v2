<x-layouts.app title="Empresas">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Empresas</h1>
            <p class="text-sm text-surface-500 mt-0.5">Gerencie as empresas do sistema</p>
        </div>
        <x-ui.button href="{{ route('empresas.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nova Empresa
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
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Empresa</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">CNPJ</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Código</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Clientes</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Fornecedores</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($empresas as $empresa)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-surface-900 dark:text-white">{{ $empresa->razao_social }}</div>
                            @if($empresa->nome_fantasia)
                            <div class="text-xs text-surface-400">{{ $empresa->nome_fantasia }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $empresa->cnpj ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $empresa->codigo }}</td>
                        <td class="px-5 py-4 text-sm text-surface-500 text-right">{{ $empresa->clientes_count }}</td>
                        <td class="px-5 py-4 text-sm text-surface-500 text-right">{{ $empresa->fornecedores_count }}</td>
                        <td class="px-5 py-4">
                            <x-ui.badge variant="{{ $empresa->ativo ? 'success' : 'default' }}">
                                {{ $empresa->ativo ? 'Ativa' : 'Inativa' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('empresas.edit', $empresa) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('empresas.destroy', $empresa) }}"
                                      onsubmit="return confirm('Remover esta empresa?')">
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
                            Nenhuma empresa cadastrada.
                            <a href="{{ route('empresas.create') }}" class="text-primary-600 hover:underline ml-1">Criar nova</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($empresas->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $empresas->links() }}
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
