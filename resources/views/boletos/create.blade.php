<x-layouts.app title="Novo Boleto">

    <div class="max-w-3xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('boletos.index') }}"
               class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Novo Boleto</h1>
                <p class="text-sm text-surface-500 mt-0.5">Crie um boleto de cobrança</p>
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

        <form method="POST" action="{{ route('boletos.store') }}">
            @csrf

            {{-- Pagador --}}
            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Pagador</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.select name="cliente_id" label="Cliente" :error="$errors->first('cliente_id')">
                        <option value="">— Selecione o cliente —</option>
                        @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                            {{ $cliente->nome_razao_social }}
                            @if($cliente->cpf_cnpj) ({{ $cliente->cpf_cnpj }}) @endif
                        </option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="conta_receber_id" label="Conta a Receber (opcional)" :error="$errors->first('conta_receber_id')">
                        <option value="">— Vincular a uma conta —</option>
                        @foreach($contasReceber as $cr)
                        <option value="{{ $cr->id }}" @selected(old('conta_receber_id') == $cr->id)>
                            {{ $cr->descricao }} — R$ {{ number_format($cr->valor_total, 2, ',', '.') }}
                            (venc. {{ $cr->data_vencimento->format('d/m/Y') }})
                        </option>
                        @endforeach
                    </x-ui.select>
                </div>
            </x-ui.card>

            {{-- Valores e Datas --}}
            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Valores e Datas</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-ui.input
                        name="valor"
                        type="number"
                        step="0.01"
                        min="0.01"
                        label="Valor (R$)"
                        required
                        placeholder="0,00"
                        value="{{ old('valor') }}"
                        :error="$errors->first('valor')"
                    />
                    <x-ui.input
                        name="data_vencimento"
                        type="date"
                        label="Data de Vencimento"
                        required
                        value="{{ old('data_vencimento', today()->addDays(3)->format('Y-m-d')) }}"
                        :error="$errors->first('data_vencimento')"
                    />
                    <x-ui.input
                        name="data_emissao"
                        type="date"
                        label="Data de Emissão"
                        value="{{ old('data_emissao', today()->format('Y-m-d')) }}"
                        :error="$errors->first('data_emissao')"
                    />
                </div>
            </x-ui.card>

            {{-- Encargos --}}
            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Encargos</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-ui.input
                        name="multa"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        label="Multa (%)"
                        value="{{ old('multa', '2.00') }}"
                        :error="$errors->first('multa')"
                        hint="Percentual aplicado após vencimento"
                    />
                    <x-ui.input
                        name="juros"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        label="Juros ao Mês (%)"
                        value="{{ old('juros', '1.00') }}"
                        :error="$errors->first('juros')"
                        hint="Juros mensais após vencimento"
                    />
                    <x-ui.input
                        name="desconto"
                        type="number"
                        step="0.01"
                        min="0"
                        label="Desconto (R$)"
                        value="{{ old('desconto', '0.00') }}"
                        :error="$errors->first('desconto')"
                        hint="Desconto para pagamento antecipado"
                    />
                </div>
            </x-ui.card>

            {{-- Banco --}}
            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Banco</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Banco</label>
                        <select name="banco"
                                class="w-full rounded-xl border border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-700 text-surface-900 dark:text-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">— Selecione —</option>
                            <option value="001" @selected(old('banco') === '001')>001 — Banco do Brasil</option>
                            <option value="033" @selected(old('banco') === '033')>033 — Santander</option>
                            <option value="104" @selected(old('banco') === '104')>104 — Caixa Econômica Federal</option>
                            <option value="237" @selected(old('banco') === '237')>237 — Bradesco</option>
                            <option value="341" @selected(old('banco') === '341')>341 — Itaú</option>
                            <option value="748" @selected(old('banco') === '748')>748 — Sicredi</option>
                            <option value="756" @selected(old('banco') === '756')>756 — Sicoob</option>
                            <option value="077" @selected(old('banco') === '077')>077 — Inter</option>
                            <option value="260" @selected(old('banco') === '260')>260 — Nu Pagamentos (Nubank)</option>
                        </select>
                        @error('banco')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-4 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            A emissão automática do boleto requer configuração de integração bancária.
                            O boleto será salvo como <strong>rascunho</strong> e poderá ser emitido manualmente após configurar a integração.
                        </p>
                    </div>
                </div>
            </x-ui.card>

            {{-- Instruções --}}
            <x-ui.card class="mb-6">
                <x-ui.textarea
                    name="instrucoes"
                    label="Instruções ao Caixa"
                    rows="3"
                    placeholder="Ex: Não receber após vencimento. Cobrar multa de 2% e juros de 1% ao mês."
                    :error="$errors->first('instrucoes')"
                >{{ old('instrucoes') }}</x-ui.textarea>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('boletos.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Criar Boleto</x-ui.button>
            </div>
        </form>

    </div>

</x-layouts.app>
