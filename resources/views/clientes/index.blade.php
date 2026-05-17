<x-layouts.app title="Clientes">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Clientes</h1>
            <p class="text-sm text-surface-500 mt-0.5">Gerencie sua carteira de clientes</p>
        </div>
        <x-ui.button href="{{ route('clientes.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Novo Cliente
        </x-ui.button>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <x-ui.input name="search" placeholder="Buscar por nome, razão social ou CPF/CNPJ..." value="{{ request('search') }}" label="Buscar" />
            </div>
            <div class="w-36">
                <x-ui.select name="status" label="Status">
                    <option value="">Todos</option>
                    <option value="1" @selected(request('status') === '1')>Ativo</option>
                    <option value="0" @selected(request('status') === '0')>Inativo</option>
                </x-ui.select>
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
            @if(request()->hasAny(['search','status']))
            <x-ui.button href="{{ route('clientes.index') }}" variant="ghost">Limpar</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Nome / Razão Social</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">CPF / CNPJ</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Contato</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($clientes as $cliente)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-surface-900 dark:text-white">{{ $cliente->nome_razao_social }}</div>
                            @if($cliente->nome_fantasia)
                            <div class="text-xs text-surface-400">{{ $cliente->nome_fantasia }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $cliente->cpf_cnpj ?? '—' }}</td>
                        <td class="px-5 py-4">
                            @if($cliente->email)
                            <div class="text-sm text-surface-700 dark:text-surface-300">{{ $cliente->email }}</div>
                            @endif
                            @if($cliente->celular)
                            <div class="text-xs text-surface-400">{{ $cliente->celular }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.badge variant="{{ $cliente->tipo === 'juridica' ? 'primary' : 'default' }}">
                                {{ $cliente->tipo === 'juridica' ? 'PJ' : 'PF' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.badge variant="{{ $cliente->ativo ? 'success' : 'default' }}">
                                {{ $cliente->ativo ? 'Ativo' : 'Inativo' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('clientes.edit', $cliente) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('clientes.destroy', $cliente) }}"
                                      onsubmit="return confirm('Remover este cliente?')">
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
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhum cliente encontrado.
                            <a href="{{ route('clientes.create') }}" class="text-primary-600 hover:underline ml-1">Cadastrar novo</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clientes->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $clientes->links() }}
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
