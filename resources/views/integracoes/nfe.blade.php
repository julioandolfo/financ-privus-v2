<x-layouts.app title="Integração NF-e / NFS-e">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('integracoes.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">NF-e / NFS-e</h1>
                <p class="text-sm text-surface-500 mt-0.5">Emissão de Notas Fiscais Eletrônicas</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('integracoes.salvar', 'nfe') }}">
            @csrf @method('PUT')

            <x-ui.card class="mb-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Configuração Fiscal</h2>
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-surface-700 dark:text-surface-300">
                        <input type="checkbox" name="ativo" value="1" @checked($integracao->ativo ?? false)
                            class="rounded border-surface-300 dark:border-surface-600 text-primary-600">
                        Integração ativa
                    </label>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.select name="configs[ambiente]" label="Ambiente">
                        <option value="homologacao" @selected(old('configs.ambiente', $integracao->config('ambiente', 'homologacao')) === 'homologacao')>Homologação (Teste)</option>
                        <option value="producao"    @selected(old('configs.ambiente', $integracao->config('ambiente')) === 'producao')>Produção</option>
                    </x-ui.select>
                    <x-ui.select name="configs[regime_tributario]" label="Regime Tributário">
                        <option value="1" @selected(old('configs.regime_tributario', $integracao->config('regime_tributario')) === '1')>Simples Nacional</option>
                        <option value="2" @selected(old('configs.regime_tributario', $integracao->config('regime_tributario')) === '2')>Simples Nacional — Excesso</option>
                        <option value="3" @selected(old('configs.regime_tributario', $integracao->config('regime_tributario')) === '3')>Regime Normal</option>
                    </x-ui.select>
                    <x-ui.input name="configs[inscricao_estadual]"   label="Inscrição Estadual"  value="{{ old('configs.inscricao_estadual', $integracao->config('inscricao_estadual')) }}" />
                    <x-ui.input name="configs[inscricao_municipal]"  label="Inscrição Municipal" value="{{ old('configs.inscricao_municipal', $integracao->config('inscricao_municipal')) }}" />
                    <x-ui.input name="configs[serie]"   type="number" label="Série NF-e" value="{{ old('configs.serie', $integracao->config('serie', 1)) }}" />
                    <x-ui.input name="configs[ultimo_numero]" type="number" label="Último Número Emitido" value="{{ old('configs.ultimo_numero', $integracao->config('ultimo_numero', 0)) }}" />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">API de Emissão</h2>
                <div class="space-y-4">
                    <x-ui.input name="configs[token_api]" type="password" label="Token API (WebmaniaBR / NFe.io)" value="{{ old('configs.token_api', $integracao->config('token_api')) }}" />
                    <x-ui.input name="configs[csc]"       label="CSC (Código de Segurança)" value="{{ old('configs.csc', $integracao->config('csc')) }}" />
                    <x-ui.input name="configs[csc_id]"    label="ID do CSC" value="{{ old('configs.csc_id', $integracao->config('csc_id')) }}" />
                </div>
            </x-ui.card>

            <div class="flex justify-end gap-3">
                <x-ui.button href="{{ route('integracoes.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar Configuração</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
