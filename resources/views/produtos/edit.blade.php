<x-layouts.app title="Editar Produto">

    <div class="max-w-3xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('produtos.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Editar Produto</h1>
                <p class="text-sm text-surface-500 mt-0.5">{{ $produto->nome }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('produtos.update', $produto) }}"
              x-data="{ tipo: '{{ old('tipo', $produto->tipo) }}' }">
            @csrf @method('PUT')

            {{-- Identificação --}}
            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Identificação</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">Tipo</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tipo" value="produto" x-model="tipo"
                                class="text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-surface-700 dark:text-surface-300">Produto</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tipo" value="servico" x-model="tipo"
                                class="text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-surface-700 dark:text-surface-300">Serviço</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input
                            name="nome"
                            label="Nome"
                            required
                            value="{{ old('nome', $produto->nome) }}"
                            :error="$errors->first('nome')"
                        />
                    </div>
                    <x-ui.input
                        name="codigo"
                        label="Código"
                        value="{{ old('codigo', $produto->codigo) }}"
                        hint="Identificador interno"
                    />
                    <x-ui.input
                        name="sku"
                        label="SKU"
                        value="{{ old('sku', $produto->sku) }}"
                        hint="Stock Keeping Unit"
                    />
                    <x-ui.input
                        name="codigo_barras"
                        label="Código de Barras"
                        value="{{ old('codigo_barras', $produto->codigo_barras) }}"
                        placeholder="EAN-13, UPC..."
                    />
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Categoria</label>
                        <select name="categoria_id"
                            class="w-full rounded-xl border border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 px-3 py-2 text-sm text-surface-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Sem categoria</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" @selected(old('categoria_id', $produto->categoria_id) == $cat->id)>{{ $cat->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <x-ui.textarea
                        name="descricao"
                        label="Descrição"
                        rows="3"
                        placeholder="Descrição detalhada do produto..."
                    >{{ old('descricao', $produto->descricao) }}</x-ui.textarea>
                </div>
            </x-ui.card>

            {{-- Preços --}}
            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Preços</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-ui.input
                        name="custo_unitario"
                        type="number"
                        step="0.0001"
                        min="0"
                        label="Custo Unitário"
                        value="{{ old('custo_unitario', $produto->custo_unitario) }}"
                        :error="$errors->first('custo_unitario')"
                    />
                    <x-ui.input
                        name="preco_venda"
                        type="number"
                        step="0.0001"
                        min="0"
                        label="Preço de Venda"
                        required
                        value="{{ old('preco_venda', $produto->preco_venda) }}"
                        :error="$errors->first('preco_venda')"
                    />
                    <x-ui.input
                        name="unidade_medida"
                        label="Unidade de Medida"
                        value="{{ old('unidade_medida', $produto->unidade_medida) }}"
                        placeholder="UN, KG, L, M..."
                    />
                </div>
            </x-ui.card>

            {{-- Estoque (apenas para produtos) --}}
            <div x-show="tipo === 'produto'" x-transition>
                <x-ui.card class="mb-4">
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Estoque</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.input
                            name="estoque"
                            type="number"
                            step="0.001"
                            min="0"
                            label="Estoque Atual"
                            value="{{ old('estoque', $produto->estoque) }}"
                            :error="$errors->first('estoque')"
                        />
                        <x-ui.input
                            name="estoque_minimo"
                            type="number"
                            step="0.001"
                            min="0"
                            label="Estoque Mínimo"
                            value="{{ old('estoque_minimo', $produto->estoque_minimo) }}"
                            hint="Alerta quando atingir este valor"
                        />
                    </div>
                </x-ui.card>
            </div>

            {{-- Dados Fiscais --}}
            <x-ui.card class="mb-6">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Dados Fiscais</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-ui.input
                        name="ncm"
                        label="NCM"
                        value="{{ old('ncm', $produto->ncm) }}"
                        placeholder="0000.00.00"
                        hint="Nomenclatura Comum do Mercosul"
                    />
                    <x-ui.input
                        name="cfop"
                        label="CFOP"
                        value="{{ old('cfop', $produto->cfop) }}"
                        placeholder="5.102"
                    />
                    <x-ui.input
                        name="cest"
                        label="CEST"
                        value="{{ old('cest', $produto->cest) }}"
                        placeholder="00.000.00"
                    />
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('produtos.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Atualizar Produto</x-ui.button>
            </div>
        </form>
    </div>

</x-layouts.app>
