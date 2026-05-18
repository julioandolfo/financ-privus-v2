<x-layouts.app title="Novo Pedido">

    <div class="max-w-5xl mx-auto">

        {{-- Page header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('pedidos.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Novo Pedido</h1>
                <p class="text-sm text-surface-500 mt-0.5">Registre um pedido de venda</p>
            </div>
        </div>

        {{-- Validation errors --}}
        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form
            method="POST"
            action="{{ route('pedidos.store') }}"
            x-data="{
                itens: [
                    { nome_produto: '', produto_id: '', codigo_produto_origem: '', quantidade: 1, valor_unitario: 0, custo_unitario: 0 }
                ],

                get totalVenda() {
                    return this.itens.reduce((sum, item) => {
                        return sum + (parseFloat(item.quantidade) || 0) * (parseFloat(item.valor_unitario) || 0);
                    }, 0);
                },

                get totalCusto() {
                    return this.itens.reduce((sum, item) => {
                        return sum + (parseFloat(item.quantidade) || 0) * (parseFloat(item.custo_unitario) || 0);
                    }, 0);
                },

                get margemPercentual() {
                    const tv = this.totalVenda;
                    if (tv <= 0) return 0;
                    return ((tv - this.totalCusto) / tv) * 100;
                },

                itemTotal(item) {
                    return (parseFloat(item.quantidade) || 0) * (parseFloat(item.valor_unitario) || 0);
                },

                addItem() {
                    this.itens.push({ nome_produto: '', produto_id: '', codigo_produto_origem: '', quantidade: 1, valor_unitario: 0, custo_unitario: 0 });
                },

                removeItem(index) {
                    if (this.itens.length === 1) return;
                    this.itens.splice(index, 1);
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
                }
            }"
        >
            @csrf

            {{-- Order details --}}
            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Dados do Pedido</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <x-ui.input
                        name="numero_pedido"
                        label="Número do Pedido"
                        required
                        placeholder="Ex: PED-001"
                        value="{{ old('numero_pedido') }}"
                        :error="$errors->first('numero_pedido')"
                    />

                    <x-ui.input
                        name="data_pedido"
                        type="date"
                        label="Data do Pedido"
                        required
                        value="{{ old('data_pedido', today()->format('Y-m-d')) }}"
                        :error="$errors->first('data_pedido')"
                    />

                    <x-ui.select name="cliente_id" label="Cliente" :error="$errors->first('cliente_id')">
                        <option value="">— Nenhum —</option>
                        @foreach($clientes as $c)
                        <option value="{{ $c->id }}" @selected(old('cliente_id') == $c->id)>{{ $c->nome_razao_social }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select name="origem" label="Origem" required :error="$errors->first('origem')">
                        <option value="manual"      @selected(old('origem', 'manual') === 'manual')>Manual</option>
                        <option value="woocommerce" @selected(old('origem') === 'woocommerce')>WooCommerce</option>
                        <option value="marketplace" @selected(old('origem') === 'marketplace')>Marketplace</option>
                    </x-ui.select>

                    <x-ui.input
                        name="origem_id"
                        label="ID Externo"
                        placeholder="ID do pedido no sistema de origem"
                        value="{{ old('origem_id') }}"
                        :error="$errors->first('origem_id')"
                        hint="Opcional — Ex: ID WooCommerce"
                    />

                    <x-ui.select name="status" label="Status" required :error="$errors->first('status')">
                        <option value="pendente"    @selected(old('status', 'pendente') === 'pendente')>Pendente</option>
                        <option value="processando" @selected(old('status') === 'processando')>Processando</option>
                        <option value="concluido"   @selected(old('status') === 'concluido')>Concluído</option>
                        <option value="cancelado"   @selected(old('status') === 'cancelado')>Cancelado</option>
                        <option value="reembolsado" @selected(old('status') === 'reembolsado')>Reembolsado</option>
                    </x-ui.select>

                    <x-ui.input
                        name="status_origem"
                        label="Status de Origem"
                        placeholder="Ex: processing, completed..."
                        value="{{ old('status_origem') }}"
                        :error="$errors->first('status_origem')"
                        hint="Opcional — status original do marketplace"
                    />

                    <x-ui.input
                        name="desconto"
                        type="number"
                        step="0.01"
                        min="0"
                        label="Desconto (R$)"
                        placeholder="0,00"
                        value="{{ old('desconto', '0') }}"
                        :error="$errors->first('desconto')"
                    />
                </div>
            </x-ui.card>

            {{-- Items section --}}
            <x-ui.card class="mb-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Itens do Pedido</h2>
                    <button
                        type="button"
                        @click="addItem()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Adicionar Item
                    </button>
                </div>

                {{-- Items table header --}}
                <div class="hidden sm:grid sm:grid-cols-12 gap-2 px-1 mb-1">
                    <div class="sm:col-span-4 text-xs font-medium text-surface-500 uppercase tracking-wide">Produto / Descrição</div>
                    <div class="sm:col-span-2 text-xs font-medium text-surface-500 uppercase tracking-wide text-right">Qtd</div>
                    <div class="sm:col-span-2 text-xs font-medium text-surface-500 uppercase tracking-wide text-right">Valor Unit.</div>
                    <div class="sm:col-span-2 text-xs font-medium text-surface-500 uppercase tracking-wide text-right">Custo Unit.</div>
                    <div class="sm:col-span-1 text-xs font-medium text-surface-500 uppercase tracking-wide text-right">Total</div>
                    <div class="sm:col-span-1"></div>
                </div>

                {{-- Item rows --}}
                <div class="space-y-3">
                    <template x-for="(item, index) in itens" :key="index">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 p-3 rounded-xl bg-surface-50 dark:bg-surface-800/50 border border-surface-100 dark:border-surface-700">

                            {{-- Hidden fields --}}
                            <input type="hidden" :name="'itens[' + index + '][produto_id]'" :value="item.produto_id">
                            <input type="hidden" :name="'itens[' + index + '][codigo_produto_origem]'" :value="item.codigo_produto_origem">

                            {{-- Nome produto --}}
                            <div class="sm:col-span-4">
                                <label class="sm:hidden text-xs font-medium text-surface-500 mb-1 block">Produto / Descrição</label>
                                <input
                                    type="text"
                                    :name="'itens[' + index + '][nome_produto]'"
                                    x-model="item.nome_produto"
                                    placeholder="Nome do produto ou serviço"
                                    required
                                    class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 placeholder:text-surface-400 focus:ring-2 focus:ring-primary-500 focus:outline-none transition-shadow"
                                >
                            </div>

                            {{-- Quantidade --}}
                            <div class="sm:col-span-2">
                                <label class="sm:hidden text-xs font-medium text-surface-500 mb-1 block">Quantidade</label>
                                <input
                                    type="number"
                                    :name="'itens[' + index + '][quantidade]'"
                                    x-model="item.quantidade"
                                    step="0.001"
                                    min="0.001"
                                    required
                                    class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 text-right placeholder:text-surface-400 focus:ring-2 focus:ring-primary-500 focus:outline-none transition-shadow"
                                >
                            </div>

                            {{-- Valor unitário --}}
                            <div class="sm:col-span-2">
                                <label class="sm:hidden text-xs font-medium text-surface-500 mb-1 block">Valor Unit. (R$)</label>
                                <input
                                    type="number"
                                    :name="'itens[' + index + '][valor_unitario]'"
                                    x-model="item.valor_unitario"
                                    step="0.01"
                                    min="0"
                                    required
                                    placeholder="0,00"
                                    class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 text-right placeholder:text-surface-400 focus:ring-2 focus:ring-primary-500 focus:outline-none transition-shadow"
                                >
                            </div>

                            {{-- Custo unitário --}}
                            <div class="sm:col-span-2">
                                <label class="sm:hidden text-xs font-medium text-surface-500 mb-1 block">Custo Unit. (R$)</label>
                                <input
                                    type="number"
                                    :name="'itens[' + index + '][custo_unitario]'"
                                    x-model="item.custo_unitario"
                                    step="0.01"
                                    min="0"
                                    placeholder="0,00"
                                    class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 text-right placeholder:text-surface-400 focus:ring-2 focus:ring-primary-500 focus:outline-none transition-shadow"
                                >
                            </div>

                            {{-- Computed total (read-only display) --}}
                            <div class="sm:col-span-1 flex items-center sm:justify-end">
                                <label class="sm:hidden text-xs font-medium text-surface-500 mr-2">Total:</label>
                                <span
                                    class="text-sm font-semibold text-surface-900 dark:text-white"
                                    x-text="formatCurrency(itemTotal(item))"
                                ></span>
                            </div>

                            {{-- Remove button --}}
                            <div class="sm:col-span-1 flex items-center justify-end">
                                <button
                                    type="button"
                                    @click="removeItem(index)"
                                    x-bind:disabled="itens.length === 1"
                                    class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                    title="Remover item"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Add item button (bottom) --}}
                <button
                    type="button"
                    @click="addItem()"
                    class="mt-3 w-full py-2.5 rounded-xl border-2 border-dashed border-surface-200 dark:border-surface-700 text-sm text-surface-400 hover:border-primary-400 hover:text-primary-600 dark:hover:border-primary-600 dark:hover:text-primary-400 transition-colors"
                >
                    + Adicionar Item
                </button>

                {{-- Totals summary --}}
                <div class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-end gap-2 sm:gap-8">
                        <div class="text-right">
                            <p class="text-xs text-surface-500 uppercase tracking-wide">Total Venda</p>
                            <p class="text-xl font-bold text-surface-900 dark:text-white" x-text="formatCurrency(totalVenda)"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-surface-500 uppercase tracking-wide">Total Custo</p>
                            <p class="text-lg font-semibold text-surface-600 dark:text-surface-400" x-text="formatCurrency(totalCusto)"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-surface-500 uppercase tracking-wide">Margem Bruta</p>
                            <p
                                class="text-lg font-bold"
                                :class="{
                                    'text-green-600 dark:text-green-400': margemPercentual >= 30,
                                    'text-amber-600 dark:text-amber-400': margemPercentual >= 10 && margemPercentual < 30,
                                    'text-red-600 dark:text-red-400': margemPercentual < 10
                                }"
                                x-text="margemPercentual.toFixed(1) + '%'"
                            ></p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Observations --}}
            <x-ui.card class="mb-6">
                <x-ui.textarea
                    name="observacoes"
                    label="Observações"
                    rows="3"
                    placeholder="Informações adicionais sobre o pedido..."
                    :error="$errors->first('observacoes')"
                >{{ old('observacoes') }}</x-ui.textarea>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('pedidos.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar Pedido</x-ui.button>
            </div>
        </form>
    </div>

</x-layouts.app>
