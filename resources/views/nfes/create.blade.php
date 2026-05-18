<x-layouts.app title="Nova NF-e">

    <div class="max-w-3xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('nfes.index') }}"
               class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Nova NF-e</h1>
                <p class="text-sm text-surface-500 mt-0.5">Cria como rascunho — emita quando estiver pronto</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('nfes.store') }}"
              x-data="{
                  valorProdutos: '{{ old('valor_produtos', '0') }}',
                  valorFrete: '{{ old('valor_frete', '0') }}',
                  valorDesconto: '{{ old('valor_desconto', '0') }}',
                  get valorTotal() {
                      const p = parseFloat(this.valorProdutos) || 0;
                      const f = parseFloat(this.valorFrete) || 0;
                      const d = parseFloat(this.valorDesconto) || 0;
                      return Math.max(0, p + f - d).toFixed(2);
                  }
              }">
            @csrf

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Identificação</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.select name="cliente_id" label="Cliente" :error="$errors->first('cliente_id')">
                        <option value="">— Selecione —</option>
                        @foreach($clientes as $c)
                        <option value="{{ $c->id }}" @selected(old('cliente_id') == $c->id)>
                            {{ $c->nome_razao_social }}
                        </option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select name="conta_receber_id" label="Conta a Receber (opcional)" :error="$errors->first('conta_receber_id')">
                        <option value="">— Nenhuma —</option>
                        @foreach($contasReceber as $cr)
                        <option value="{{ $cr->id }}" @selected(old('conta_receber_id') == $cr->id)>
                            {{ $cr->descricao }} — R$ {{ number_format($cr->valor_total, 2, ',', '.') }}
                            @if($cr->cliente) ({{ $cr->cliente->nome_razao_social }}) @endif
                        </option>
                        @endforeach
                    </x-ui.select>

                    <div class="sm:col-span-2">
                        <x-ui.input
                            name="natureza_operacao"
                            label="Natureza da Operação"
                            required
                            placeholder="Venda de Mercadoria"
                            value="{{ old('natureza_operacao', 'Venda de Mercadoria') }}"
                            :error="$errors->first('natureza_operacao')"
                        />
                    </div>

                    <x-ui.input
                        name="serie"
                        label="Série"
                        required
                        placeholder="1"
                        value="{{ old('serie', '1') }}"
                        :error="$errors->first('serie')"
                    />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Datas</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input
                        name="data_emissao"
                        type="date"
                        label="Data de Emissão"
                        value="{{ old('data_emissao', today()->format('Y-m-d')) }}"
                        :error="$errors->first('data_emissao')"
                    />
                    <x-ui.input
                        name="data_competencia"
                        type="date"
                        label="Data de Competência"
                        value="{{ old('data_competencia', today()->format('Y-m-d')) }}"
                        :error="$errors->first('data_competencia')"
                    />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Valores</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-ui.input
                            name="valor_produtos"
                            type="number"
                            step="0.01"
                            min="0"
                            label="Valor dos Produtos"
                            required
                            placeholder="0,00"
                            x-model="valorProdutos"
                            value="{{ old('valor_produtos', '0') }}"
                            :error="$errors->first('valor_produtos')"
                        />
                    </div>
                    <div>
                        <x-ui.input
                            name="valor_frete"
                            type="number"
                            step="0.01"
                            min="0"
                            label="Valor do Frete"
                            placeholder="0,00"
                            x-model="valorFrete"
                            value="{{ old('valor_frete', '0') }}"
                            :error="$errors->first('valor_frete')"
                        />
                    </div>
                    <div>
                        <x-ui.input
                            name="valor_desconto"
                            type="number"
                            step="0.01"
                            min="0"
                            label="Desconto"
                            placeholder="0,00"
                            x-model="valorDesconto"
                            value="{{ old('valor_desconto', '0') }}"
                            :error="$errors->first('valor_desconto')"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
                            Valor Total <span class="text-xs text-surface-400">(calculado)</span>
                        </label>
                        <div class="flex items-center rounded-xl ring-1 ring-inset ring-surface-200 dark:ring-surface-700 bg-surface-50 dark:bg-surface-800/50 px-3.5 py-2.5">
                            <span class="text-sm font-semibold text-surface-900 dark:text-white" x-text="'R$ ' + parseFloat(valorTotal).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })">
                                R$ {{ old('valor_total', '0,00') }}
                            </span>
                        </div>
                        <input type="hidden" name="valor_total" :value="valorTotal">
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-6">
                <x-ui.textarea
                    name="observacoes"
                    label="Observações"
                    rows="3"
                    placeholder="Informações adicionais da nota fiscal..."
                >{{ old('observacoes') }}</x-ui.textarea>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('nfes.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar como Rascunho</x-ui.button>
            </div>
        </form>
    </div>

</x-layouts.app>
