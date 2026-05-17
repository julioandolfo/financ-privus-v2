<x-layouts.app title="Conciliação Bancária">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Conciliação Bancária</h1>
            <p class="text-sm text-surface-500 mt-0.5">Marque as movimentações conferidas com o extrato</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
    @endif

    {{-- Filtros --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-56">
                <x-ui.select name="conta_bancaria_id" label="Conta Bancária" required>
                    @foreach($contas as $c)
                    <option value="{{ $c->id }}" @selected($contaId == $c->id)>{{ $c->nome }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="w-36">
                <x-ui.input name="de" type="date" label="De" value="{{ $de->format('Y-m-d') }}" />
            </div>
            <div class="w-36">
                <x-ui.input name="ate" type="date" label="Até" value="{{ $ate->format('Y-m-d') }}" />
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
        </form>
    </x-ui.card>

    {{-- Resumo --}}
    @if($conta)
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 mb-1">Saldo Atual da Conta</p>
            <p class="text-xl font-bold {{ $conta->saldo_atual >= 0 ? 'text-surface-900 dark:text-white' : 'text-red-600 dark:text-red-400' }}">
                R$ {{ number_format($conta->saldo_atual, 2, ',', '.') }}
            </p>
            <p class="text-xs text-surface-400 mt-0.5">{{ $conta->nome }}</p>
        </x-ui.card>
        <x-ui.card class="bg-green-50 dark:bg-green-900/10 border-green-100 dark:border-green-800">
            <p class="text-xs font-medium text-green-700 dark:text-green-400 mb-1">Conciliadas</p>
            <p class="text-xl font-bold text-green-700 dark:text-green-300">{{ $totalConciliadas }}</p>
            <p class="text-xs text-green-600 mt-0.5">Saldo: R$ {{ number_format($saldoConciliado, 2, ',', '.') }}</p>
        </x-ui.card>
        <x-ui.card class="bg-amber-50 dark:bg-amber-900/10 border-amber-100 dark:border-amber-800">
            <p class="text-xs font-medium text-amber-700 dark:text-amber-400 mb-1">Pendentes</p>
            <p class="text-xl font-bold text-amber-700 dark:text-amber-300">{{ $totalNaoConciliadas }}</p>
            <p class="text-xs text-amber-600 mt-0.5">Aguardando conciliação</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 mb-1">Total no Período</p>
            <p class="text-xl font-bold text-surface-900 dark:text-white">{{ $movimentacoes->count() }}</p>
            <p class="text-xs text-surface-400 mt-0.5">movimentações</p>
        </x-ui.card>
    </div>
    @endif

    {{-- Tabela com seleção em massa --}}
    <x-ui.card :padding="false"
        x-data="{
            selected: [],
            get allOnPage() { return {{ $movimentacoes->count() }}; },
            toggleAll(check) {
                if (check) {
                    this.selected = {{ $movimentacoes->pluck('id') }};
                } else {
                    this.selected = [];
                }
            }
        }">

        {{-- Barra de ação em massa --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-surface-100 dark:border-surface-700">
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-sm text-surface-600 dark:text-surface-400 cursor-pointer">
                    <input type="checkbox" @change="toggleAll($event.target.checked)"
                        class="rounded border-surface-300 dark:border-surface-600 text-primary-600">
                    Selecionar todos
                </label>
                <span x-show="selected.length > 0" class="text-xs text-surface-500" x-text="selected.length + ' selecionada(s)'"></span>
            </div>
            <div class="flex gap-2" x-show="selected.length > 0">
                <form method="POST" action="{{ route('conciliacao.conciliar') }}">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <x-ui.button type="submit" variant="secondary" class="text-xs py-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Conciliar Selecionadas
                    </x-ui.button>
                </form>
                <form method="POST" action="{{ route('conciliacao.desconciliar') }}">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <x-ui.button type="submit" variant="ghost" class="text-xs py-1.5">
                        Desconciliar
                    </x-ui.button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="w-10 px-4 py-3"></th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Data</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Categoria</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($movimentacoes as $mov)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors"
                        :class="{ 'opacity-60': {{ $mov->conciliado ? 'true' : 'false' }} }">
                        <td class="px-4 py-3.5">
                            <input type="checkbox" :value="{{ $mov->id }}" x-model="selected"
                                class="rounded border-surface-300 dark:border-surface-600 text-primary-600">
                        </td>
                        <td class="px-5 py-3.5 text-sm text-surface-500 whitespace-nowrap">{{ $mov->data_movimentacao->format('d/m/Y') }}</td>
                        <td class="px-5 py-3.5 text-sm font-medium text-surface-900 dark:text-white">{{ $mov->descricao }}</td>
                        <td class="px-5 py-3.5 text-sm text-surface-500">{{ $mov->categoria?->nome ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <x-ui.badge :variant="$mov->tipo === 'entrada' ? 'success' : 'danger'">
                                {{ $mov->tipo === 'entrada' ? 'Entrada' : 'Saída' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-3.5 text-sm font-semibold text-right whitespace-nowrap {{ $mov->tipo === 'entrada' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $mov->tipo === 'saida' ? '−' : '+' }} R$ {{ number_format($mov->valor, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <form method="POST" action="{{ route('conciliacao.toggle', $mov) }}">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors
                                    {{ $mov->conciliado
                                        ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400'
                                        : 'bg-surface-100 text-surface-500 hover:bg-surface-200 dark:bg-surface-700 dark:text-surface-400' }}">
                                    @if($mov->conciliado)
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    Conciliado
                                    @else
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    Pendente
                                    @endif
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhuma movimentação encontrada para o período.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

</x-layouts.app>
