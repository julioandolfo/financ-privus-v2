<x-layouts.app title="Nova Conta Bancária">

    <div class="max-w-xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('contas-bancarias.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Nova Conta Bancária</h1>
                <p class="text-sm text-surface-500 mt-0.5">Cadastre uma conta bancária ou caixa</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('contas-bancarias.store') }}">
            @csrf

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Identificação</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input name="nome" label="Nome da Conta" required
                            placeholder="Ex: Bradesco Corrente, Caixa Loja..."
                            value="{{ old('nome') }}" :error="$errors->first('nome')" />
                    </div>
                    <x-ui.select name="tipo_conta" label="Tipo" required :error="$errors->first('tipo_conta')">
                        <option value="corrente"     @selected(old('tipo_conta') === 'corrente')>Conta Corrente</option>
                        <option value="poupanca"     @selected(old('tipo_conta') === 'poupanca')>Poupança</option>
                        <option value="investimento" @selected(old('tipo_conta') === 'investimento')>Investimento</option>
                        <option value="caixa"        @selected(old('tipo_conta') === 'caixa')>Caixa (dinheiro)</option>
                    </x-ui.select>
                    <x-ui.input name="saldo_inicial" type="number" step="0.01" label="Saldo Inicial" required
                        placeholder="0,00" value="{{ old('saldo_inicial', '0.00') }}" :error="$errors->first('saldo_inicial')"
                        hint="O saldo atual será igual ao inicial" />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-6" x-data="{ tipo: '{{ old('tipo_conta', 'corrente') }}' }" x-show="tipo !== 'caixa'">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Dados Bancários</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input name="banco_codigo" label="Código do Banco" placeholder="001" value="{{ old('banco_codigo') }}" hint="Opcional" />
                    <x-ui.input name="banco_nome" label="Nome do Banco" placeholder="Banco do Brasil" value="{{ old('banco_nome') }}" hint="Opcional" />
                    <x-ui.input name="agencia" label="Agência" placeholder="0001" value="{{ old('agencia') }}" hint="Opcional" />
                    <x-ui.input name="conta" label="Número da Conta" placeholder="12345-6" value="{{ old('conta') }}" hint="Opcional" />
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('contas-bancarias.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar Conta</x-ui.button>
            </div>
        </form>
    </div>

</x-layouts.app>
