<x-layouts.app title="Editar Movimentação">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('movimentacoes.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $movimentacao->descricao }}</h1>
                <p class="text-sm text-surface-500 mt-0.5">{{ $movimentacao->data_movimentacao->format('d/m/Y') }} · R$ {{ number_format($movimentacao->valor, 2, ',', '.') }}</p>
            </div>
        </div>
        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        <form method="POST" action="{{ route('movimentacoes.update', $movimentacao) }}">
            @csrf @method('PUT')
            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Dados da Movimentação</h2>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">Tipo <span class="text-red-500">*</span></label>
                    <div class="flex gap-3">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="tipo" value="entrada" @checked(old('tipo', $movimentacao->tipo) === 'entrada') class="sr-only peer">
                            <div class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 text-sm font-medium transition-all peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 dark:peer-checked:border-green-500 dark:peer-checked:bg-green-900/20 dark:peer-checked:text-green-400 border-surface-200 dark:border-surface-700 text-surface-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0-6.75 6.75M12 4.5l6.75 6.75" /></svg>
                                Entrada
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="tipo" value="saida" @checked(old('tipo', $movimentacao->tipo) === 'saida') class="sr-only peer">
                            <div class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 text-sm font-medium transition-all peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 dark:peer-checked:border-red-500 dark:peer-checked:bg-red-900/20 dark:peer-checked:text-red-400 border-surface-200 dark:border-surface-700 text-surface-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0 6.75-6.75M12 19.5l-6.75-6.75" /></svg>
                                Saída
                            </div>
                        </label>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input name="descricao" label="Descrição" required value="{{ old('descricao', $movimentacao->descricao) }}" :error="$errors->first('descricao')" />
                    </div>
                    <x-ui.input name="valor" type="number" step="0.01" min="0.01" label="Valor" required value="{{ old('valor', number_format($movimentacao->valor, 2, '.', '')) }}" :error="$errors->first('valor')" />
                    <x-ui.input name="data_movimentacao" type="date" label="Data" required value="{{ old('data_movimentacao', $movimentacao->data_movimentacao->format('Y-m-d')) }}" />
                    <x-ui.select name="conta_bancaria_id" label="Conta Bancária" required :error="$errors->first('conta_bancaria_id')">
                        <option value="">— Selecione —</option>
                        @foreach($contas as $c)
                        <option value="{{ $c->id }}" @selected(old('conta_bancaria_id', $movimentacao->conta_bancaria_id) == $c->id)>{{ $c->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input name="data_competencia" type="date" label="Data Competência" value="{{ old('data_competencia', $movimentacao->data_competencia?->format('Y-m-d')) }}" hint="Opcional" />
                </div>
            </x-ui.card>
            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Classificação</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.select name="categoria_id" label="Categoria">
                        <option value="">— Selecione —</option>
                        @foreach($categorias as $c)
                        <option value="{{ $c->id }}" @selected(old('categoria_id', $movimentacao->categoria_id) == $c->id)>{{ $c->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="centro_custo_id" label="Centro de Custo">
                        <option value="">— Selecione —</option>
                        @foreach($centros as $cc)
                        <option value="{{ $cc->id }}" @selected(old('centro_custo_id', $movimentacao->centro_custo_id) == $cc->id)>{{ $cc->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="forma_pagamento_id" label="Forma de Pagamento">
                        <option value="">— Selecione —</option>
                        @foreach($formas as $fp)
                        <option value="{{ $fp->id }}" @selected(old('forma_pagamento_id', $movimentacao->forma_pagamento_id) == $fp->id)>{{ $fp->nome }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </x-ui.card>
            <x-ui.card class="mb-6">
                <x-ui.textarea name="observacoes" label="Observações" rows="2">{{ old('observacoes', $movimentacao->observacoes) }}</x-ui.textarea>
            </x-ui.card>
            <div class="flex items-center justify-between">
                <form method="POST" action="{{ route('movimentacoes.destroy', $movimentacao) }}" onsubmit="return confirm('Remover? O saldo da conta será revertido.')">
                    @csrf @method('DELETE')
                    <x-ui.button type="submit" variant="danger">Excluir</x-ui.button>
                </form>
                <div class="flex gap-3">
                    <x-ui.button href="{{ route('movimentacoes.index') }}" variant="ghost">Cancelar</x-ui.button>
                    <x-ui.button type="submit">Salvar Alterações</x-ui.button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
