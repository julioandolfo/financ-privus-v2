<x-layouts.app title="Editar Conta a Receber">

    <div class="max-w-3xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('contas-receber.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $contaReceber->descricao }}</h1>
                <p class="text-sm text-surface-500 mt-0.5">
                    Vencimento: {{ $contaReceber->data_vencimento->format('d/m/Y') }} &bull;
                    R$ {{ number_format($contaReceber->valor_total, 2, ',', '.') }}
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

        @if(in_array($contaReceber->status, ['pendente','parcial','vencido']))
        <x-ui.card class="mb-4 border-green-200 dark:border-green-800 bg-green-50/50 dark:bg-green-900/10">
            <h2 class="text-sm font-semibold text-green-800 dark:text-green-300 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                Registrar Recebimento
            </h2>
            <form method="POST" action="{{ route('contas-receber.baixar', $contaReceber) }}">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                    <x-ui.input name="data_recebimento" type="date" label="Data Recebimento" required
                        value="{{ old('data_recebimento', today()->format('Y-m-d')) }}" />
                    <x-ui.input name="valor_recebido" type="number" step="0.01" min="0.01" label="Valor Recebido" required
                        value="{{ old('valor_recebido', number_format($contaReceber->valor_aberto, 2, '.', '')) }}" />
                    <x-ui.input name="desconto" type="number" step="0.01" min="0" label="Desconto"
                        value="{{ old('desconto', '0.00') }}" />
                    <x-ui.input name="juros" type="number" step="0.01" min="0" label="Juros/Multa"
                        value="{{ old('juros', '0.00') }}" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <x-ui.select name="forma_recebimento_id" label="Forma de Recebimento">
                        <option value="">— Selecione —</option>
                        @foreach($formas as $fp)
                        <option value="{{ $fp->id }}" @selected(old('forma_recebimento_id', $contaReceber->forma_recebimento_id) == $fp->id)>{{ $fp->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="conta_bancaria_id" label="Conta Bancária">
                        <option value="">— Selecione —</option>
                        @foreach($contas as $cb)
                        <option value="{{ $cb->id }}" @selected(old('conta_bancaria_id', $contaReceber->conta_bancaria_id) == $cb->id)>{{ $cb->nome }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <x-ui.button type="submit" variant="primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    Confirmar Recebimento
                </x-ui.button>
            </form>
        </x-ui.card>
        @endif

        <form method="POST" action="{{ route('contas-receber.update', $contaReceber) }}">
            @csrf @method('PUT')

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Informações Básicas</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input
                            name="descricao"
                            label="Descrição"
                            required
                            value="{{ old('descricao', $contaReceber->descricao) }}"
                            :error="$errors->first('descricao')"
                        />
                    </div>
                    <x-ui.select name="cliente_id" label="Cliente">
                        <option value="">— Selecione —</option>
                        @foreach($clientes as $c)
                        <option value="{{ $c->id }}" @selected(old('cliente_id', $contaReceber->cliente_id) == $c->id)>{{ $c->nome_razao_social }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input
                        name="numero_documento"
                        label="Nº Documento"
                        value="{{ old('numero_documento', $contaReceber->numero_documento) }}"
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
                        value="{{ old('valor_total', number_format($contaReceber->valor_total, 2, '.', '')) }}"
                    />
                    <x-ui.input
                        name="data_vencimento"
                        type="date"
                        label="Data de Vencimento"
                        required
                        value="{{ old('data_vencimento', $contaReceber->data_vencimento->format('Y-m-d')) }}"
                    />
                    <x-ui.input
                        name="data_competencia"
                        type="date"
                        label="Data de Competência"
                        value="{{ old('data_competencia', $contaReceber->data_competencia?->format('Y-m-d')) }}"
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
                        <option value="{{ $c->id }}" @selected(old('categoria_id', $contaReceber->categoria_id) == $c->id)>{{ $c->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="centro_custo_id" label="Centro de Custo">
                        <option value="">— Selecione —</option>
                        @foreach($centrosCusto as $cc)
                        <option value="{{ $cc->id }}" @selected(old('centro_custo_id', $contaReceber->centro_custo_id) == $cc->id)>{{ $cc->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="forma_recebimento_id" label="Forma de Recebimento">
                        <option value="">— Selecione —</option>
                        @foreach($formas as $fp)
                        <option value="{{ $fp->id }}" @selected(old('forma_recebimento_id', $contaReceber->forma_recebimento_id) == $fp->id)>{{ $fp->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="conta_bancaria_id" label="Conta Bancária">
                        <option value="">— Selecione —</option>
                        @foreach($contas as $cb)
                        <option value="{{ $cb->id }}" @selected(old('conta_bancaria_id', $contaReceber->conta_bancaria_id) == $cb->id)>{{ $cb->nome }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-6">
                <x-ui.textarea
                    name="observacoes"
                    label="Observações"
                    rows="3"
                >{{ old('observacoes', $contaReceber->observacoes) }}</x-ui.textarea>
            </x-ui.card>

            <div class="flex items-center justify-between">
                <form method="POST" action="{{ route('contas-receber.destroy', $contaReceber) }}"
                      onsubmit="return confirm('Excluir esta conta permanentemente?')">
                    @csrf @method('DELETE')
                    <x-ui.button type="submit" variant="danger">Excluir</x-ui.button>
                </form>
                <div class="flex items-center gap-3">
                    <x-ui.button href="{{ route('contas-receber.index') }}" variant="ghost">Cancelar</x-ui.button>
                    <x-ui.button type="submit">Salvar Alterações</x-ui.button>
                </div>
            </div>
        </form>
    </div>

</x-layouts.app>
