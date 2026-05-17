<x-layouts.app title="Movimentações de Caixa">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Movimentações de Caixa</h1>
            <p class="text-sm text-surface-500 mt-0.5">Entradas e saídas registradas</p>
        </div>
        <x-ui.button href="{{ route('movimentacoes.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nova Movimentação
        </x-ui.button>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
    @endif

    {{-- Totais do período --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
        <x-ui.card class="bg-green-50 dark:bg-green-900/10 border-green-100 dark:border-green-800">
            <p class="text-xs font-medium text-green-700 dark:text-green-400 mb-1">Total Entradas</p>
            <p class="text-xl font-bold text-green-700 dark:text-green-300">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</p>
        </x-ui.card>
        <x-ui.card class="bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800">
            <p class="text-xs font-medium text-red-700 dark:text-red-400 mb-1">Total Saídas</p>
            <p class="text-xl font-bold text-red-700 dark:text-red-300">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</p>
        </x-ui.card>
        <x-ui.card class="{{ ($totalEntradas - $totalSaidas) >= 0 ? 'bg-primary-50 dark:bg-primary-900/10 border-primary-100 dark:border-primary-800' : 'bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800' }}">
            <p class="text-xs font-medium text-surface-500 mb-1">Saldo do Período</p>
            <p class="text-xl font-bold {{ ($totalEntradas - $totalSaidas) >= 0 ? 'text-primary-700 dark:text-primary-300' : 'text-red-700 dark:text-red-300' }}">
                R$ {{ number_format($totalEntradas - $totalSaidas, 2, ',', '.') }}
            </p>
        </x-ui.card>
    </div>

    {{-- Filtros --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[160px]">
                <x-ui.input name="search" placeholder="Buscar descrição..." value="{{ request('search') }}" label="Buscar" />
            </div>
            <div class="w-36">
                <x-ui.select name="tipo" label="Tipo">
                    <option value="">Todos</option>
                    <option value="entrada" @selected(request('tipo') === 'entrada')>Entradas</option>
                    <option value="saida"   @selected(request('tipo') === 'saida')>Saídas</option>
                </x-ui.select>
            </div>
            <div class="w-48">
                <x-ui.select name="conta_bancaria_id" label="Conta">
                    <option value="">Todas as contas</option>
                    @foreach($contas as $c)
                    <option value="{{ $c->id }}" @selected(request('conta_bancaria_id') == $c->id)>{{ $c->nome }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="w-36">
                <x-ui.input name="de" type="date" label="De" value="{{ request('de') }}" />
            </div>
            <div class="w-36">
                <x-ui.input name="ate" type="date" label="Até" value="{{ request('ate') }}" />
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
            @if(request()->hasAny(['search','tipo','conta_bancaria_id','de','ate']))
            <x-ui.button href="{{ route('movimentacoes.index') }}" variant="ghost">Limpar</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Data</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Conta</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Categoria</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($movimentacoes as $mov)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4 text-sm text-surface-500 whitespace-nowrap">{{ $mov->data_movimentacao->format('d/m/Y') }}</td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $mov->descricao }}</p>
                            @if($mov->conciliado)
                            <span class="text-xs text-green-600 dark:text-green-400">● Conciliado</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $mov->contaBancaria?->nome ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $mov->categoria?->nome ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <x-ui.badge :variant="$mov->tipo === 'entrada' ? 'success' : 'danger'">
                                {{ $mov->tipo === 'entrada' ? 'Entrada' : 'Saída' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-sm font-semibold text-right whitespace-nowrap {{ $mov->tipo === 'entrada' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $mov->tipo === 'saida' ? '−' : '+' }} R$ {{ number_format($mov->valor, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('movimentacoes.edit', $mov) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">Editar</a>
                                <form method="POST" action="{{ route('movimentacoes.destroy', $mov) }}" onsubmit="return confirm('Remover esta movimentação? O saldo da conta será revertido.')">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-surface-400">Nenhuma movimentação encontrada. <a href="{{ route('movimentacoes.create') }}" class="text-primary-600 hover:underline">Registrar primeira</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movimentacoes->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">{{ $movimentacoes->links() }}</div>
        @endif
    </x-ui.card>

</x-layouts.app>
