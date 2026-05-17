<x-layouts.app title="Formas de Pagamento">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Formas de Pagamento</h1>
            <p class="text-sm text-surface-500 mt-0.5">Métodos de pagamento e recebimento disponíveis</p>
        </div>
        <x-ui.button href="{{ route('formas-pagamento.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nova Forma
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
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Padrão</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Escopo</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($formas as $forma)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4 text-sm font-medium text-surface-900 dark:text-white">{{ $forma->nome }}</td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $forma->codigo ?? '—' }}</td>
                        <td class="px-5 py-4">
                            @php $tipoMap = ['pagamento'=>['default','Pagamento'],'recebimento'=>['success','Recebimento'],'ambos'=>['primary','Ambos']]; [$v,$l] = $tipoMap[$forma->tipo] ?? ['default',$forma->tipo]; @endphp
                            <x-ui.badge :variant="$v">{{ $l }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4">
                            @if($forma->padrao)<x-ui.badge variant="warning">Padrão</x-ui.badge>@else<span class="text-surface-300">—</span>@endif
                        </td>
                        <td class="px-5 py-4"><x-ui.badge :variant="$forma->ativo ? 'success' : 'default'">{{ $forma->ativo ? 'Ativa' : 'Inativa' }}</x-ui.badge></td>
                        <td class="px-5 py-4">
                            <x-ui.badge :variant="$forma->empresa_id ? 'info' : 'default'">{{ $forma->empresa_id ? 'Empresa' : 'Global' }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            @if($forma->empresa_id || auth()->user()->role === 'admin')
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('formas-pagamento.edit', $forma) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">Editar</a>
                                @if($forma->empresa_id)
                                <form method="POST" action="{{ route('formas-pagamento.destroy', $forma) }}" onsubmit="return confirm('Remover?')">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Excluir</button>
                                </form>
                                @endif
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-surface-400">Nenhuma forma cadastrada. <a href="{{ route('formas-pagamento.create') }}" class="text-primary-600 hover:underline">Criar agora</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>
