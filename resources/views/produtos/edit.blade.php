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

        {{-- ================================================================
             VARIAÇÕES
             ================================================================ --}}
        <div
            class="mt-6"
            x-data="variacoesManager({{ $produto->id }})"
            x-init="carregar()"
        >
            <x-ui.card :padding="false">
                <div class="px-5 py-4 border-b border-surface-100 dark:border-surface-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Variações</h2>
                        <p class="text-xs text-surface-400 mt-0.5">Cor, tamanho, peso e outros atributos do produto</p>
                    </div>
                    <button
                        type="button"
                        @click="mostrarForm = !mostrarForm"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary-600 text-white hover:bg-primary-700 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Adicionar
                    </button>
                </div>

                {{-- Add form --}}
                <div x-show="mostrarForm" x-cloak x-transition class="px-5 py-4 border-b border-surface-100 dark:border-surface-700 bg-surface-50 dark:bg-surface-800/50">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Atributo <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                x-model="novaVariacao.atributo"
                                placeholder="cor, tamanho..."
                                maxlength="50"
                                class="block w-full rounded-lg border-0 py-2 px-3 text-xs bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Valor <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                x-model="novaVariacao.valor"
                                placeholder="Azul, G, 1kg..."
                                maxlength="100"
                                class="block w-full rounded-lg border-0 py-2 px-3 text-xs bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">SKU</label>
                            <input
                                type="text"
                                x-model="novaVariacao.sku"
                                placeholder="SKU-001"
                                maxlength="100"
                                class="block w-full rounded-lg border-0 py-2 px-3 text-xs bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Preço adicional</label>
                            <input
                                type="number"
                                x-model="novaVariacao.preco_adicional"
                                step="0.01"
                                min="0"
                                placeholder="0,00"
                                class="block w-full rounded-lg border-0 py-2 px-3 text-xs bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Estoque</label>
                            <input
                                type="number"
                                x-model="novaVariacao.estoque"
                                step="0.001"
                                min="0"
                                placeholder="0"
                                class="block w-full rounded-lg border-0 py-2 px-3 text-xs bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                            >
                        </div>
                        <div class="flex items-end gap-2">
                            <button
                                type="button"
                                @click="salvarVariacao()"
                                :disabled="salvando"
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs font-medium bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50 transition-colors"
                            >
                                <span x-show="!salvando">Salvar</span>
                                <span x-show="salvando" x-cloak>...</span>
                            </button>
                            <button type="button" @click="mostrarForm = false" class="px-2 py-2 rounded-lg text-xs text-surface-500 hover:bg-surface-200 dark:hover:bg-surface-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                    <template x-if="erroVariacao">
                        <p class="mt-2 text-xs text-red-500" x-text="erroVariacao"></p>
                    </template>
                </div>

                {{-- Loading --}}
                <div x-show="carregando" class="px-5 py-8 text-center text-sm text-surface-400">Carregando variações...</div>

                {{-- List --}}
                <div x-show="!carregando">
                    <template x-if="variacoes.length === 0">
                        <div class="px-5 py-8 text-center text-sm text-surface-400">Nenhuma variação cadastrada.</div>
                    </template>
                    <template x-if="variacoes.length > 0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                                <thead>
                                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                                        <th class="px-5 py-2.5 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Atributo</th>
                                        <th class="px-5 py-2.5 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                                        <th class="px-5 py-2.5 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">SKU</th>
                                        <th class="px-5 py-2.5 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Preço Adicional</th>
                                        <th class="px-5 py-2.5 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Estoque</th>
                                        <th class="px-5 py-2.5"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                                    <template x-for="v in variacoes" :key="v.id">
                                        <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                                            <td class="px-5 py-3 text-xs text-surface-600 dark:text-surface-400" x-text="v.atributo"></td>
                                            <td class="px-5 py-3 text-sm font-medium text-surface-900 dark:text-white" x-text="v.valor"></td>
                                            <td class="px-5 py-3 text-xs font-mono text-surface-500" x-text="v.sku || '—'"></td>
                                            <td class="px-5 py-3 text-sm text-right text-surface-700 dark:text-surface-300" x-text="'R$ ' + Number(v.preco_adicional).toLocaleString('pt-BR', {minimumFractionDigits: 2})"></td>
                                            <td class="px-5 py-3 text-sm text-right text-surface-700 dark:text-surface-300" x-text="Number(v.estoque).toLocaleString('pt-BR', {minimumFractionDigits: 3})"></td>
                                            <td class="px-5 py-3 text-right">
                                                <button
                                                    type="button"
                                                    @click="excluirVariacao(v.id)"
                                                    class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                >
                                                    Excluir
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>
            </x-ui.card>
        </div>

        {{-- ================================================================
             FOTOS
             ================================================================ --}}
        <div
            class="mt-6 mb-6"
            x-data="fotosManager({{ $produto->id }})"
            x-init="carregar()"
        >
            <x-ui.card :padding="false">
                <div class="px-5 py-4 border-b border-surface-100 dark:border-surface-700">
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Fotos do Produto</h2>
                    <p class="text-xs text-surface-400 mt-0.5">Imagens exibidas no catálogo. A primeira foto marcada como principal será usada como capa.</p>
                </div>

                <div class="p-5">
                    {{-- Loading state --}}
                    <div x-show="carregando" class="text-center text-sm text-surface-400 py-4">Carregando fotos...</div>

                    {{-- Photo grid --}}
                    <div x-show="!carregando" class="flex flex-wrap gap-3 mb-4">
                        <template x-for="foto in fotos" :key="foto.id">
                            <div class="relative group w-24 h-24 rounded-xl overflow-hidden border-2 border-surface-200 dark:border-surface-700"
                                 :class="foto.principal ? 'border-primary-500' : ''">
                                <img
                                    :src="foto.url"
                                    :alt="foto.path"
                                    class="w-full h-full object-cover"
                                >
                                {{-- Principal badge --}}
                                <template x-if="foto.principal">
                                    <span class="absolute bottom-0 left-0 right-0 text-center text-[10px] font-bold bg-primary-600 text-white py-0.5">
                                        Principal
                                    </span>
                                </template>
                                {{-- Delete overlay --}}
                                <button
                                    type="button"
                                    @click="excluirFoto(foto.id)"
                                    class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-600 text-white items-center justify-center hidden group-hover:flex transition-all shadow"
                                    title="Excluir foto"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        {{-- Upload area --}}
                        <label
                            class="w-24 h-24 rounded-xl border-2 border-dashed border-surface-300 dark:border-surface-600 flex flex-col items-center justify-center gap-1 cursor-pointer hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors"
                            :class="enviando ? 'opacity-50 cursor-not-allowed' : ''"
                        >
                            <svg x-show="!enviando" class="w-6 h-6 text-surface-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                            </svg>
                            <svg x-show="enviando" x-cloak class="w-5 h-5 text-primary-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span class="text-[10px] text-surface-400" x-show="!enviando">Adicionar</span>
                            <input
                                type="file"
                                accept="image/*"
                                class="sr-only"
                                :disabled="enviando"
                                @change="uploadFoto($event)"
                            >
                        </label>
                    </div>

                    <template x-if="erroFoto">
                        <p class="text-xs text-red-500 mt-1" x-text="erroFoto"></p>
                    </template>

                    <p class="text-xs text-surface-400">Formatos aceitos: JPG, PNG, GIF, WebP. Tamanho máximo: 5 MB.</p>
                </div>
            </x-ui.card>
        </div>
    </div>

</x-layouts.app>

<script>
function variacoesManager(produtoId) {
    return {
        produtoId,
        variacoes: [],
        carregando: true,
        salvando: false,
        mostrarForm: false,
        erroVariacao: null,
        novaVariacao: {
            atributo: '',
            valor: '',
            sku: '',
            preco_adicional: 0,
            estoque: 0,
        },

        async carregar() {
            this.carregando = true;
            try {
                const res = await fetch(`/produtos/${this.produtoId}/variacoes`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) throw new Error('Erro ao carregar variações');
                this.variacoes = await res.json();
            } catch (e) {
                console.error(e);
            } finally {
                this.carregando = false;
            }
        },

        async salvarVariacao() {
            if (!this.novaVariacao.atributo.trim() || !this.novaVariacao.valor.trim()) {
                this.erroVariacao = 'Atributo e Valor são obrigatórios.';
                return;
            }
            this.salvando = true;
            this.erroVariacao = null;
            try {
                const res = await fetch(`/produtos/${this.produtoId}/variacoes`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.novaVariacao),
                });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || 'Erro ao salvar variação');
                }
                const variacao = await res.json();
                this.variacoes.push(variacao);
                this.novaVariacao = { atributo: '', valor: '', sku: '', preco_adicional: 0, estoque: 0 };
                this.mostrarForm = false;
            } catch (e) {
                this.erroVariacao = e.message;
            } finally {
                this.salvando = false;
            }
        },

        async excluirVariacao(id) {
            if (!confirm('Excluir esta variação?')) return;
            try {
                const res = await fetch(`/produtos/${this.produtoId}/variacoes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) throw new Error('Erro ao excluir');
                this.variacoes = this.variacoes.filter(v => v.id !== id);
            } catch (e) {
                alert(e.message);
            }
        },
    };
}

function fotosManager(produtoId) {
    return {
        produtoId,
        fotos: [],
        carregando: true,
        enviando: false,
        erroFoto: null,

        async carregar() {
            this.carregando = true;
            try {
                // Reuse the variacoes endpoint pattern — fotos come embedded via the edit controller
                // We build URLs from server-rendered data if available, otherwise fetch
                @php
                    $fotosJson = $produto->fotos()
                        ->orderBy('ordem')
                        ->get()
                        ->map(fn($f) => [
                            'id'        => $f->id,
                            'path'      => $f->path,
                            'url'       => asset('storage/' . $f->path),
                            'principal' => (bool) $f->principal,
                        ])
                        ->toJson();
                @endphp
                this.fotos = {!! $fotosJson !!};
            } finally {
                this.carregando = false;
            }
        },

        async uploadFoto(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.erroFoto = null;
            this.enviando = true;

            const formData = new FormData();
            formData.append('foto', file);

            try {
                const res = await fetch(`/produtos/${this.produtoId}/fotos`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || `Erro ${res.status} ao enviar foto`);
                }

                const foto = await res.json();
                this.fotos.push(foto);
            } catch (e) {
                this.erroFoto = e.message;
            } finally {
                this.enviando = false;
                event.target.value = '';
            }
        },

        async excluirFoto(id) {
            if (!confirm('Excluir esta foto?')) return;
            try {
                const res = await fetch(`/produtos/${this.produtoId}/fotos/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) throw new Error('Erro ao excluir foto');
                this.fotos = this.fotos.filter(f => f.id !== id);
            } catch (e) {
                alert(e.message);
            }
        },
    };
}
</script>
