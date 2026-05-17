<x-layouts.app title="Editar Conta Bancária">

    <div class="max-w-xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('contas-bancarias.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $contaBancaria->nome }}</h1>
                <p class="text-sm text-surface-500 mt-0.5">Saldo atual: R$ {{ number_format($contaBancaria->saldo_atual, 2, ',', '.') }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('contas-bancarias.update', $contaBancaria) }}">
            @csrf @method('PUT')

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Identificação</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input name="nome" label="Nome da Conta" required
                            value="{{ old('nome', $contaBancaria->nome) }}" :error="$errors->first('nome')" />
                    </div>
                    <x-ui.select name="tipo_conta" label="Tipo" required :error="$errors->first('tipo_conta')">
                        <option value="corrente"     @selected(old('tipo_conta', $contaBancaria->tipo_conta) === 'corrente')>Conta Corrente</option>
                        <option value="poupanca"     @selected(old('tipo_conta', $contaBancaria->tipo_conta) === 'poupanca')>Poupança</option>
                        <option value="investimento" @selected(old('tipo_conta', $contaBancaria->tipo_conta) === 'investimento')>Investimento</option>
                        <option value="caixa"        @selected(old('tipo_conta', $contaBancaria->tipo_conta) === 'caixa')>Caixa (dinheiro)</option>
                    </x-ui.select>
                    <div>
                        <p class="text-xs text-surface-500 mb-1">Saldo inicial</p>
                        <p class="text-sm font-medium text-surface-700 dark:text-surface-300">
                            R$ {{ number_format($contaBancaria->saldo_inicial, 2, ',', '.') }}
                        </p>
                        <p class="text-xs text-surface-400 mt-0.5">Alterado pelas movimentações</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Dados Bancários</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input name="banco_codigo" label="Código do Banco" value="{{ old('banco_codigo', $contaBancaria->banco_codigo) }}" hint="Opcional" />
                    <x-ui.input name="banco_nome" label="Nome do Banco" value="{{ old('banco_nome', $contaBancaria->banco_nome) }}" hint="Opcional" />
                    <x-ui.input name="agencia" label="Agência" value="{{ old('agencia', $contaBancaria->agencia) }}" hint="Opcional" />
                    <x-ui.input name="conta" label="Número da Conta" value="{{ old('conta', $contaBancaria->conta) }}" hint="Opcional" />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-surface-700 dark:text-surface-300">Conta ativa</p>
                        <p class="text-xs text-surface-500 mt-0.5">Contas inativas não aparecem nas seleções</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $contaBancaria->ativo)) class="sr-only peer">
                        <div class="w-11 h-6 bg-surface-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 rounded-full peer dark:bg-surface-700 peer-checked:bg-primary-600 transition-colors"></div>
                        <div class="absolute left-0.5 top-0.5 bg-white rounded-full h-5 w-5 transition-transform peer-checked:translate-x-5 shadow-sm"></div>
                    </label>
                </div>
            </x-ui.card>

            <div class="flex items-center justify-between">
                <form method="POST" action="{{ route('contas-bancarias.destroy', $contaBancaria) }}"
                      onsubmit="return confirm('Excluir esta conta? Esta ação não pode ser desfeita.')">
                    @csrf @method('DELETE')
                    <x-ui.button type="submit" variant="danger">Excluir</x-ui.button>
                </form>
                <div class="flex items-center gap-3">
                    <x-ui.button href="{{ route('contas-bancarias.index') }}" variant="ghost">Cancelar</x-ui.button>
                    <x-ui.button type="submit">Salvar Alterações</x-ui.button>
                </div>
            </div>
        </form>
    </div>

</x-layouts.app>
