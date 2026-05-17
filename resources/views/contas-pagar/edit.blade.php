<x-layouts.app title="Editar Conta a Pagar">

    <div class="max-w-3xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('contas-pagar.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $contaPagar->descricao }}</h1>
                <p class="text-sm text-surface-500 mt-0.5">
                    Vencimento: {{ $contaPagar->data_vencimento->format('d/m/Y') }} &bull;
                    R$ {{ number_format($contaPagar->valor_total, 2, ',', '.') }}
                </p>
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

        {{-- Baixa section for pending accounts --}}
        @if(in_array($contaPagar->status, ['pendente','parcial','vencido']))
        <x-ui.card class="mb-4 border-green-200 dark:border-green-800 bg-green-50/50 dark:bg-green-900/10">
            <h2 class="text-sm font-semibold text-green-800 dark:text-green-300 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                Registrar Pagamento
            </h2>
            <form method="POST" action="{{ route('contas-pagar.baixar', $contaPagar) }}">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                    <x-ui.input name="data_pagamento" type="date" label="Data Pagamento" required
                        value="{{ old('data_pagamento', today()->format('Y-m-d')) }}" />
                    <x-ui.input name="valor_pago" type="number" step="0.01" min="0.01" label="Valor Pago" required
                        value="{{ old('valor_pago', number_format($contaPagar->valor_aberto, 2, '.', '')) }}" />
                    <x-ui.input name="desconto" type="number" step="0.01" min="0" label="Desconto"
                        value="{{ old('desconto', '0.00') }}" />
                    <x-ui.input name="juros" type="number" step="0.01" min="0" label="Juros/Multa"
                        value="{{ old('juros', '0.00') }}" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <x-ui.select name="forma_pagamento_id" label="Forma de Pagamento">
                        <option value="">— Selecione —</option>
                        @foreach($formas as $fp)
                        <option value="{{ $fp->id }}" @selected(old('forma_pagamento_id', $contaPagar->forma_pagamento_id) == $fp->id)>{{ $fp->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="conta_bancaria_id" label="Conta Bancária">
                        <option value="">— Selecione —</option>
                        @foreach($contas as $cb)
                        <option value="{{ $cb->id }}" @selected(old('conta_bancaria_id', $contaPagar->conta_bancaria_id) == $cb->id)>{{ $cb->nome }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <x-ui.button type="submit" variant="primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    Confirmar Pagamento
                </x-ui.button>
            </form>
        </x-ui.card>
        @endif

        {{-- Edit form --}}
        <form method="POST" action="{{ route('contas-pagar.update', $contaPagar) }}">
            @csrf @method('PUT')

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Informações Básicas</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input
                            name="descricao"
                            label="Descrição"
                            required
                            value="{{ old('descricao', $contaPagar->descricao) }}"
                            :error="$errors->first('descricao')"
                        />
                    </div>
                    <x-ui.select name="fornecedor_id" label="Fornecedor" :error="$errors->first('fornecedor_id')">
                        <option value="">— Selecione —</option>
                        @foreach($fornecedores as $f)
                        <option value="{{ $f->id }}" @selected(old('fornecedor_id', $contaPagar->fornecedor_id) == $f->id)>{{ $f->nome_razao_social }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input
                        name="numero_documento"
                        label="Nº Documento"
                        value="{{ old('numero_documento', $contaPagar->numero_documento) }}"
                        :error="$errors->first('numero_documento')"
                    />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Valores e Datas</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-ui.input
                        name="valor_total"
                        type="number"
                        step="0.01"
                        min="0.01"
                        label="Valor Total"
                        required
                        value="{{ old('valor_total', number_format($contaPagar->valor_total, 2, '.', '')) }}"
                        :error="$errors->first('valor_total')"
                    />
                    <x-ui.input
                        name="data_vencimento"
                        type="date"
                        label="Data de Vencimento"
                        required
                        value="{{ old('data_vencimento', $contaPagar->data_vencimento->format('Y-m-d')) }}"
                        :error="$errors->first('data_vencimento')"
                    />
                    <x-ui.input
                        name="data_competencia"
                        type="date"
                        label="Data de Competência"
                        value="{{ old('data_competencia', $contaPagar->data_competencia?->format('Y-m-d')) }}"
                        hint="Opcional"
                    />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Classificação</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.select name="categoria_id" label="Categoria">
                        <option value="">— Selecione —</option>
                        @foreach($categorias as $c)
                        <option value="{{ $c->id }}" @selected(old('categoria_id', $contaPagar->categoria_id) == $c->id)>{{ $c->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="centro_custo_id" label="Centro de Custo">
                        <option value="">— Selecione —</option>
                        @foreach($centrosCusto as $cc)
                        <option value="{{ $cc->id }}" @selected(old('centro_custo_id', $contaPagar->centro_custo_id) == $cc->id)>{{ $cc->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="forma_pagamento_id" label="Forma de Pagamento">
                        <option value="">— Selecione —</option>
                        @foreach($formas as $fp)
                        <option value="{{ $fp->id }}" @selected(old('forma_pagamento_id', $contaPagar->forma_pagamento_id) == $fp->id)>{{ $fp->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="conta_bancaria_id" label="Conta Bancária">
                        <option value="">— Selecione —</option>
                        @foreach($contas as $cb)
                        <option value="{{ $cb->id }}" @selected(old('conta_bancaria_id', $contaPagar->conta_bancaria_id) == $cb->id)>{{ $cb->nome }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-6">
                <x-ui.textarea
                    name="observacoes"
                    label="Observações"
                    rows="3"
                    :error="$errors->first('observacoes')"
                >{{ old('observacoes', $contaPagar->observacoes) }}</x-ui.textarea>
            </x-ui.card>

            <div class="flex items-center justify-between">
                <form method="POST" action="{{ route('contas-pagar.destroy', $contaPagar) }}"
                      onsubmit="return confirm('Excluir esta conta permanentemente?')">
                    @csrf @method('DELETE')
                    <x-ui.button type="submit" variant="danger" size="md">Excluir</x-ui.button>
                </form>
                <div class="flex items-center gap-3">
                    <x-ui.button href="{{ route('contas-pagar.index') }}" variant="ghost">Cancelar</x-ui.button>
                    <x-ui.button type="submit">Salvar Alterações</x-ui.button>
                </div>
            </div>
        </form>
    </div>

</x-layouts.app>
