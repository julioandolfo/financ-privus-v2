<x-layouts.app title="Produtos">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Produtos e Serviços</h1>
            <p class="text-sm text-surface-500 mt-0.5">Gerencie seu catálogo de produtos e serviços</p>
        </div>
        <x-ui.button href="{{ route('produtos.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Novo Produto
        </x-ui.button>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    @if($totalEstoqueBaixo > 0)
    <div class="mb-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3 flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <div class="flex-1">
            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
                {{ $totalEstoqueBaixo }} {{ $totalEstoqueBaixo === 1 ? 'produto com estoque baixo' : 'produtos com estoque baixo' }}
            </p>
        </div>
        <a href="{{ request()->fullUrlWithQuery(['estoque_baixo' => '1']) }}"
           class="text-xs font-medium text-amber-700 dark:text-amber-400 hover:underline">
            Ver produtos
        </a>
    </div>
    @endif

    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <x-ui.input name="search" placeholder="Buscar por nome, código ou SKU..." value="{{ request('search') }}" label="Buscar" />
            </div>
            <div class="w-36">
                <x-ui.select name="tipo" label="Tipo">
                    <option value="">Todos</option>
                    <option value="produto" @selected(request('tipo') === 'produto')>Produto</option>
                    <option value="servico" @selected(request('tipo') === 'servico')>Serviço</option>
                </x-ui.select>
            </div>
            <div class="w-44">
                <x-ui.select name="categoria_id" label="Categoria">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" @selected(request('categoria_id') == $cat->id)>{{ $cat->nome }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <label class="flex items-center gap-2 cursor-pointer pb-0.5">
                <input type="checkbox" name="estoque_baixo" value="1"
                    @checked(request('estoque_baixo'))
                    class="rounded text-amber-600 focus:ring-amber-500">
                <span class="text-sm text-surface-700 dark:text-surface-300 whitespace-nowrap">Estoque baixo</span>
            </label>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
            @if(request()->hasAny(['search','tipo','categoria_id','estoque_baixo']))
            <x-ui.button href="{{ route('produtos.index') }}" variant="ghost">Limpar</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Produto</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Categoria</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Custo</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Preço Venda</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Margem</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Estoque</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($produtos as $produto)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($produto->isEstoqueBaixo() && $produto->tipo === 'produto')
                                <span class="inline-flex items-center justify-center w-2 h-2 rounded-full bg-amber-500 flex-shrink-0" title="Estoque baixo"></span>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-surface-900 dark:text-white">{{ $produto->nome }}</div>
                                    <div class="text-xs text-surface-400">
                                        @if($produto->codigo) Cód: {{ $produto->codigo }} @endif
                                        @if($produto->sku) &bull; SKU: {{ $produto->sku }} @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.badge variant="{{ $produto->tipo === 'servico' ? 'primary' : 'default' }}">
                                {{ $produto->tipo === 'servico' ? 'Serviço' : 'Produto' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $produto->categoria?->nome ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-right text-surface-600 dark:text-surface-400">
                            R$ {{ number_format($produto->custo_unitario, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-right font-medium text-surface-900 dark:text-white">
                            R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-right">
                            @php $margem = $produto->margem; @endphp
                            <span class="{{ $margem >= 30 ? 'text-green-600 dark:text-green-400' : ($margem >= 10 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                                {{ number_format($margem, 1) }}%
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm text-right">
                            @if($produto->tipo === 'produto')
                            <span class="{{ $produto->isEstoqueBaixo() ? 'text-amber-600 dark:text-amber-400 font-medium' : 'text-surface-700 dark:text-surface-300' }}">
                                {{ number_format($produto->estoque, 0, ',', '.') }} {{ $produto->unidade_medida }}
                            </span>
                            @else
                            <span class="text-surface-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('produtos.edit', $produto) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('produtos.destroy', $produto) }}"
                                      onsubmit="return confirm('Remover este produto?')">
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
                            Nenhum produto encontrado.
                            <a href="{{ route('produtos.create') }}" class="text-primary-600 hover:underline ml-1">Cadastrar novo</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($produtos->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $produtos->links() }}
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
