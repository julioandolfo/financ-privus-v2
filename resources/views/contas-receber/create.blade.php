<x-layouts.app title="Nova Conta a Receber">

    <div class="max-w-3xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('contas-receber.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Nova Conta a Receber</h1>
                <p class="text-sm text-surface-500 mt-0.5">Registre um novo recebível</p>
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

        <form method="POST" action="{{ route('contas-receber.store') }}">
            @csrf

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Informações Básicas</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input
                            name="descricao"
                            label="Descrição"
                            required
                            placeholder="Ex: Venda de mercadoria, Prestação de serviço..."
                            value="{{ old('descricao') }}"
                            :error="$errors->first('descricao')"
                        />
                    </div>
                    <x-ui.select name="cliente_id" label="Cliente" :error="$errors->first('cliente_id')">
                        <option value="">— Selecione —</option>
                        @foreach($clientes as $c)
                        <option value="{{ $c->id }}" @selected(old('cliente_id') == $c->id)>{{ $c->nome_razao_social }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input
                        name="numero_documento"
                        label="Nº Documento"
                        placeholder="NF, pedido, etc."
                        value="{{ old('numero_documento') }}"
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
                        placeholder="0,00"
                        value="{{ old('valor_total') }}"
                        :error="$errors->first('valor_total')"
                    />
                    <x-ui.input
                        name="data_vencimento"
                        type="date"
                        label="Data de Vencimento"
                        required
                        value="{{ old('data_vencimento', today()->format('Y-m-d')) }}"
                        :error="$errors->first('data_vencimento')"
                    />
                    <x-ui.input
                        name="data_competencia"
                        type="date"
                        label="Data de Competência"
                        value="{{ old('data_competencia') }}"
                        hint="Opcional"
                    />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Classificação</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.select name="categoria_id" label="Categoria" :error="$errors->first('categoria_id')">
                        <option value="">— Selecione —</option>
                        @foreach($categorias as $c)
                        <option value="{{ $c->id }}" @selected(old('categoria_id') == $c->id)>{{ $c->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="centro_custo_id" label="Centro de Custo">
                        <option value="">— Selecione —</option>
                        @foreach($centrosCusto as $cc)
                        <option value="{{ $cc->id }}" @selected(old('centro_custo_id') == $cc->id)>{{ $cc->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="forma_recebimento_id" label="Forma de Recebimento">
                        <option value="">— Selecione —</option>
                        @foreach($formas as $fp)
                        <option value="{{ $fp->id }}" @selected(old('forma_recebimento_id') == $fp->id)>{{ $fp->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="conta_bancaria_id" label="Conta Bancária">
                        <option value="">— Selecione —</option>
                        @foreach($contas as $cb)
                        <option value="{{ $cb->id }}" @selected(old('conta_bancaria_id') == $cb->id)>{{ $cb->nome }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-6">
                <x-ui.textarea
                    name="observacoes"
                    label="Observações"
                    rows="3"
                    placeholder="Informações adicionais..."
                >{{ old('observacoes') }}</x-ui.textarea>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('contas-receber.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar Conta</x-ui.button>
            </div>
        </form>
    </div>

</x-layouts.app>
