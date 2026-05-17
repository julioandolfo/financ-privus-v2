<x-layouts.app title="Integração Boleto Bancário">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('integracoes.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Boleto Bancário</h1>
                <p class="text-sm text-surface-500 mt-0.5">Emissão de boletos via API do banco</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('integracoes.salvar', 'boleto') }}">
            @csrf @method('PUT')

            <x-ui.card class="mb-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Configuração do Banco</h2>
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-surface-700 dark:text-surface-300">
                        <input type="checkbox" name="ativo" value="1" @checked($integracao->ativo ?? false)
                            class="rounded border-surface-300 dark:border-surface-600 text-primary-600">
                        Integração ativa
                    </label>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.select name="configs[banco]" label="Banco" required>
                        @foreach(['bradesco'=>'Bradesco','itau'=>'Itaú','sicoob'=>'Sicoob','sicredi'=>'Sicredi','inter'=>'Banco Inter','bb'=>'Banco do Brasil','santander'=>'Santander'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('configs.banco', $integracao->config('banco')) === $v)>{{ $l }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="configs[ambiente]" label="Ambiente">
                        <option value="sandbox"   @selected(old('configs.ambiente', $integracao->config('ambiente', 'sandbox')) === 'sandbox')>Sandbox / Homologação</option>
                        <option value="producao"  @selected(old('configs.ambiente', $integracao->config('ambiente')) === 'producao')>Produção</option>
                    </x-ui.select>
                    <x-ui.input name="configs[agencia]"   label="Agência"    value="{{ old('configs.agencia', $integracao->config('agencia')) }}" />
                    <x-ui.input name="configs[conta]"     label="Conta"      value="{{ old('configs.conta', $integracao->config('conta')) }}" />
                    <x-ui.input name="configs[convenio]"  label="Convênio"   value="{{ old('configs.convenio', $integracao->config('convenio')) }}" />
                    <x-ui.input name="configs[carteira]"  label="Carteira"   value="{{ old('configs.carteira', $integracao->config('carteira')) }}" />
                    <x-ui.input name="configs[cedente]"   label="Nome Cedente" value="{{ old('configs.cedente', $integracao->config('cedente')) }}" class="sm:col-span-2" />
                    <x-ui.input name="configs[cnpj_cedente]" label="CNPJ Cedente" value="{{ old('configs.cnpj_cedente', $integracao->config('cnpj_cedente')) }}" />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">API / OAuth</h2>
                <div class="space-y-4">
                    <x-ui.input name="configs[client_id]"     label="Client ID"     value="{{ old('configs.client_id', $integracao->config('client_id')) }}" />
                    <x-ui.input name="configs[client_secret]" type="password" label="Client Secret" value="{{ old('configs.client_secret', $integracao->config('client_secret')) }}" />
                </div>
            </x-ui.card>

            <div class="flex justify-end gap-3">
                <x-ui.button href="{{ route('integracoes.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar Configuração</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
