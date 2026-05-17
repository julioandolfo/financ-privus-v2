<x-layouts.app title="Usuários">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Usuários</h1>
            <p class="text-sm text-surface-500 mt-0.5">Gerencie quem tem acesso ao sistema</p>
        </div>
        <x-ui.button href="{{ route('usuarios.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Novo Usuário
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
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">E-mail</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Perfil</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($usuarios as $u)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 text-xs font-semibold flex-shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $u->name }}</p>
                                    @if($u->id === auth()->id())
                                    <p class="text-xs text-primary-500">Você</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $u->email }}</td>
                        <td class="px-5 py-4">
                            <x-ui.badge :variant="$u->role === 'admin' ? 'primary' : 'secondary'">
                                {{ $u->role === 'admin' ? 'Administrador' : 'Usuário' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.badge :variant="$u->ativo ? 'success' : 'secondary'">
                                {{ $u->ativo ? 'Ativo' : 'Inativo' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('usuarios.edit', $u) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">Editar</a>
                                @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('usuarios.destroy', $u) }}" onsubmit="return confirm('Desativar este usuário?')">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Desativar</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-surface-400">Nenhum usuário encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

</x-layouts.app>
