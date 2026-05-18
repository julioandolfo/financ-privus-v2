<x-layouts.app title="Estoque de Produtos">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Estoque de Produtos</h1>
            <p class="text-sm text-surface-500 mt-0.5">Posição atual do estoque com valores e alertas</p>
        </div>
    </div>

    {{-- Filtros --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-52">
                <x-ui.select name="categoria_id" label="Categoria">
                    <option value="">Todas as categorias</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" @selected(request('categoria_id') == $cat->id)>{{ $cat->nome }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="flex items-center gap-2 pb-1">
                <input type="checkbox" id="estoque_baixo" name="estoque_baixo" value="1"
                    @checked(request('estoque_baixo'))
                    class="w-4 h-4 rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                <label for="estoque_baixo" class="text-sm text-surface-700 dark:text-surface-300 whitespace-nowrap">Apenas estoque baixo</label>
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
            <a href="{{ url('/relatorios/estoque/pdf') }}?{{ http_build_query(request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700"
               target="_blank">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
              PDF
            </a>
        </form>
    </x-ui.card>

    {{-- Cards de resumo --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 mb-1">Total de Produtos</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-white">{{ $totalProdutos }}</p>
            <p class="text-xs text-surface-400 mt-1">Itens no estoque</p>
        </x-ui.card>

        <x-ui.card class="{{ $estoqueBaixo > 0 ? 'bg-red-50 dark:bg-red-900/10 border-red-100 dark:border-red-800' : '' }}">
            <p class="text-xs font-medium {{ $estoqueBaixo > 0 ? 'text-red-700 dark:text-red-400' : 'text-surface-500' }} mb-1">Estoque Baixo</p>
            <p class="text-2xl font-bold {{ $estoqueBaixo > 0 ? 'text-red-700 dark:text-red-300' : 'text-surface-900 dark:text-white' }}">{{ $estoqueBaixo }}</p>
            <p class="text-xs {{ $estoqueBaixo > 0 ? 'text-red-600 dark:text-red-500' : 'text-surface-400' }} mt-1">{{ $estoqueBaixo > 0 ? 'Abaixo do mínimo!' : 'Tudo em ordem' }}</p>
        </x-ui.card>

        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 mb-1">Valor em Estoque</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-white">R$ {{ number_format($valorEstoque, 2, ',', '.') }}</p>
            <p class="text-xs text-surface-400 mt-1">Custo unitário × estoque</p>
        </x-ui.card>

        <x-ui.card class="bg-green-50 dark:bg-green-900/10 border-green-100 dark:border-green-800">
            <p class="text-xs font-medium text-green-700 dark:text-green-400 mb-1">Valor a Venda</p>
            <p class="text-2xl font-bold text-green-700 dark:text-green-300">R$ {{ number_format($valorVendaTotal, 2, ',', '.') }}</p>
            <p class="text-xs text-green-600 dark:text-green-500 mt-1">Preço venda × estoque</p>
        </x-ui.card>
    </div>

    {{-- Tabela de produtos --}}
    <x-ui.card>
        <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Posição de Estoque</h2>
        @if($produtos->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-surface-100 dark:border-surface-700">
                        <th class="pb-2 text-left text-xs font-medium text-surface-500 uppercase">Produto</th>
                        <th class="pb-2 text-left text-xs font-medium text-surface-500 uppercase">Categoria</th>
                        <th class="pb-2 text-right text-xs font-medium text-surface-500 uppercase">Estoque</th>
                        <th class="pb-2 text-right text-xs font-medium text-surface-500 uppercase">Mínimo</th>
                        <th class="pb-2 text-center text-xs font-medium text-surface-500 uppercase">Status</th>
                        <th class="pb-2 text-right text-xs font-medium text-surface-500 uppercase">Custo Unit.</th>
                        <th class="pb-2 text-right text-xs font-medium text-surface-500 uppercase">Preço Venda</th>
                        <th class="pb-2 text-right text-xs font-medium text-surface-500 uppercase">Valor Estoque</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-50 dark:divide-surface-800">
                    @foreach($produtos as $produto)
                    @php $baixo = ($produto->estoque ?? 0) <= ($produto->estoque_minimo ?? 0); @endphp
                    <tr class="{{ $baixo ? 'bg-red-50/60 dark:bg-red-900/10' : '' }}">
                        <td class="py-2.5 text-sm font-medium text-surface-900 dark:text-white">{{ $produto->nome }}</td>
                        <td class="py-2.5 text-sm text-surface-500">{{ $produto->categoria?->nome ?? '—' }}</td>
                        <td class="py-2.5 text-sm text-right {{ $baixo ? 'font-bold text-red-600 dark:text-red-400' : 'text-surface-700 dark:text-surface-300' }}">
                            {{ number_format($produto->estoque ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 text-sm text-right text-surface-500">{{ number_format($produto->estoque_minimo ?? 0, 0, ',', '.') }}</td>
                        <td class="py-2.5 text-center">
                            @if($baixo)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                    Baixo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    OK
                                </span>
                            @endif
                        </td>
                        <td class="py-2.5 text-sm text-right text-surface-700 dark:text-surface-300">
                            R$ {{ number_format($produto->custo_unitario ?? 0, 2, ',', '.') }}
                        </td>
                        <td class="py-2.5 text-sm text-right text-surface-700 dark:text-surface-300">
                            R$ {{ number_format($produto->preco_venda ?? 0, 2, ',', '.') }}
                        </td>
                        <td class="py-2.5 text-sm text-right font-semibold text-surface-900 dark:text-white">
                            R$ {{ number_format(($produto->estoque ?? 0) * ($produto->custo_unitario ?? 0), 2, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-surface-200 dark:border-surface-600">
                        <td colspan="7" class="pt-3 text-sm font-semibold text-surface-700 dark:text-surface-300">Total Valor em Estoque (custo)</td>
                        <td class="pt-3 text-sm text-right font-bold text-surface-900 dark:text-white">R$ {{ number_format($valorEstoque, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <p class="text-sm text-surface-400 text-center py-10">Nenhum produto encontrado com os filtros selecionados.</p>
        @endif
    </x-ui.card>

</x-layouts.app>
