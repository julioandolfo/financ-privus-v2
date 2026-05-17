<x-layouts.app title="Extrato — {{ $extrato->contaBancaria->nome }}">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('extratos.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $extrato->contaBancaria->nome }}</h1>
            <p class="text-sm text-surface-500 mt-0.5">
                {{ $extrato->nome_arquivo }}
                @if($extrato->data_inicio)
                &bull; {{ $extrato->data_inicio->format('d/m/Y') }} — {{ $extrato->data_fim?->format('d/m/Y') }}
                @endif
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- Resumo --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Total</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-white">{{ $resumo['total'] }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Conciliados</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $resumo['conciliados'] }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Pendentes</p>
            <p class="text-2xl font-bold {{ $resumo['pendentes'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-surface-400' }}">
                {{ $resumo['pendentes'] }}
            </p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Ignorados</p>
            <p class="text-2xl font-bold text-surface-400">{{ $resumo['ignorados'] }}</p>
        </x-ui.card>
    </div>

    {{-- Progresso --}}
    @if($resumo['total'] > 0)
    @php $pct = round(($resumo['conciliados'] / $resumo['total']) * 100); @endphp
    <div class="mb-6">
        <div class="flex justify-between text-xs text-surface-500 mb-1">
            <span>Progresso da conciliação</span>
            <span>{{ $pct }}%</span>
        </div>
        <div class="h-2 bg-surface-100 dark:bg-surface-700 rounded-full overflow-hidden">
            <div class="h-full bg-green-500 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
        </div>
    </div>
    @endif

    {{-- Filtro tabs --}}
    <div class="flex gap-1 mb-4">
        @foreach(['pendentes' => 'Pendentes', 'conciliados' => 'Conciliados', 'ignorados' => 'Ignorados', 'todos' => 'Todos'] as $key => $label)
        <a href="{{ request()->fullUrlWithQuery(['filtro' => $key, 'page' => 1]) }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
               {{ $filtro === $key
                   ? 'bg-primary-600 text-white'
                   : 'text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800' }}">
            {{ $label }}
            @if($key === 'pendentes' && $resumo['pendentes'] > 0)
            <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full {{ $filtro === $key ? 'bg-white/30 text-white' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' }}">
                {{ $resumo['pendentes'] }}
            </span>
            @endif
        </a>
        @endforeach
    </div>

    {{-- Lançamentos --}}
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Data</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody x-data="{ aberto: null }" class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($lancamentos as $lanc)
                    {{-- Linha principal --}}
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors"
                        :class="aberto === {{ $lanc->id }} ? 'bg-primary-50 dark:bg-primary-900/10' : ''">
                        <td class="px-5 py-3 text-sm text-surface-700 dark:text-surface-300 whitespace-nowrap">
                            {{ $lanc->data_lancamento->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3 text-sm text-surface-900 dark:text-white max-w-xs truncate">
                            {{ $lanc->descricao ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <x-ui.badge variant="{{ $lanc->tipo === 'credito' ? 'success' : 'danger' }}">
                                {{ $lanc->tipo === 'credito' ? 'Crédito' : 'Débito' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-3 text-sm text-right font-medium {{ $lanc->tipo === 'credito' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $lanc->tipo === 'credito' ? '+' : '-' }} R$ {{ number_format($lanc->valor, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3">
                            @if($lanc->conciliado)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                <span class="text-xs text-green-600 dark:text-green-400 font-medium">Conciliado</span>
                            </div>
                            @elseif($lanc->ignorado)
                            <span class="text-xs text-surface-400">Ignorado</span>
                            @else
                            <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Pendente</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                @if($lanc->conciliado)
                                <span class="text-xs text-surface-400 max-w-[140px] truncate">
                                    {{ $lanc->movimentacao?->descricao }}
                                </span>
                                <form method="POST" action="{{ route('extratos.desconciliar', [$extrato, $lanc]) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                        Desfazer
                                    </button>
                                </form>
                                @elseif($lanc->ignorado)
                                <form method="POST" action="{{ route('extratos.desconciliar', [$extrato, $lanc]) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                        Restaurar
                                    </button>
                                </form>
                                @else
                                <button @click="aberto = aberto === {{ $lanc->id }} ? null : {{ $lanc->id }}"
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                    <span x-text="aberto === {{ $lanc->id }} ? 'Fechar' : 'Conciliar'">Conciliar</span>
                                </button>
                                <form method="POST" action="{{ route('extratos.ignorar', [$extrato, $lanc]) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                        Ignorar
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Painel de conciliação (pendentes) --}}
                    @if($lanc->isPendente())
                    <tr x-show="aberto === {{ $lanc->id }}" x-cloak class="bg-primary-50 dark:bg-primary-900/10">
                        <td colspan="6" class="px-5 py-4">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                                {{-- Candidatos --}}
                                <div>
                                    <p class="text-xs font-semibold text-surface-600 dark:text-surface-400 uppercase tracking-wider mb-3">
                                        Movimentações próximas
                                    </p>
                                    @php $cands = $candidatos[$lanc->id] ?? collect(); @endphp
                                    @if($cands->isNotEmpty())
                                    <div class="space-y-2">
                                        @foreach($cands as $mov)
                                        <form method="POST" action="{{ route('extratos.conciliar', [$extrato, $lanc]) }}"
                                              class="flex items-center justify-between gap-3 p-2.5 rounded-lg bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 hover:border-primary-300 dark:hover:border-primary-600 transition-colors group">
                                            @csrf
                                            <input type="hidden" name="movimentacao_id" value="{{ $mov->id }}">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-medium text-surface-900 dark:text-white truncate">{{ $mov->descricao }}</p>
                                                <p class="text-xs text-surface-400">
                                                    {{ $mov->data_movimentacao->format('d/m/Y') }}
                                                    &bull; R$ {{ number_format($mov->valor, 2, ',', '.') }}
                                                    @if($mov->categoria) &bull; {{ $mov->categoria->nome }} @endif
                                                </p>
                                            </div>
                                            <button type="submit"
                                                class="flex-shrink-0 px-2.5 py-1 rounded-lg text-xs font-medium bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                                                Vincular
                                            </button>
                                        </form>
                                        @endforeach
                                    </div>
                                    @else
                                    <p class="text-xs text-surface-400 py-2">
                                        Nenhuma movimentação compatível encontrada no período de ±10 dias.
                                    </p>
                                    @endif
                                </div>

                                {{-- Criar nova movimentação --}}
                                <div>
                                    <p class="text-xs font-semibold text-surface-600 dark:text-surface-400 uppercase tracking-wider mb-3">
                                        Criar nova movimentação
                                    </p>
                                    <form method="POST" action="{{ route('extratos.criar-movimentacao', [$extrato, $lanc]) }}"
                                          class="space-y-3 p-3 rounded-lg bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Descrição</label>
                                            <input type="text" name="descricao"
                                                value="{{ old('descricao', $lanc->descricao) }}"
                                                required
                                                class="block w-full rounded-lg border-0 py-1.5 px-2.5 text-sm bg-surface-50 dark:bg-surface-900 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Data</label>
                                                <input type="date" name="data_movimentacao"
                                                    value="{{ $lanc->data_lancamento->format('Y-m-d') }}"
                                                    required
                                                    class="block w-full rounded-lg border-0 py-1.5 px-2.5 text-sm bg-surface-50 dark:bg-surface-900 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Categoria</label>
                                                <select name="categoria_id"
                                                    class="block w-full rounded-lg border-0 py-1.5 px-2.5 text-sm bg-surface-50 dark:bg-surface-900 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                                                    <option value="">Sem categoria</option>
                                                    @foreach($categorias as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between pt-1">
                                            <p class="text-xs text-surface-500">
                                                {{ $lanc->tipo === 'credito' ? 'Entrada' : 'Saída' }} de
                                                <strong>R$ {{ number_format($lanc->valor, 2, ',', '.') }}</strong>
                                            </p>
                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium bg-surface-900 dark:bg-white text-white dark:text-surface-900 hover:opacity-80 transition-opacity">
                                                Criar e conciliar
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhum lançamento encontrado para este filtro.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lancamentos->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $lancamentos->links() }}
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
