<x-layouts.app title="Integração WooCommerce">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('integracoes.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">WooCommerce</h1>
                <p class="text-sm text-surface-500 mt-0.5">Sincronize pedidos, clientes e produtos com sua loja</p>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('integracoes.salvar', 'woocommerce') }}">
            @csrf @method('PUT')

            <x-ui.card class="mb-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Conexão</h2>
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-surface-700 dark:text-surface-300">
                        <input type="checkbox" name="ativo" value="1" @checked($integracao->ativo ?? false)
                            class="rounded border-surface-300 dark:border-surface-600 text-primary-600">
                        Integração ativa
                    </label>
                </div>
                <div class="space-y-4">
                    <x-ui.input name="configs[url]" label="URL da Loja" required placeholder="https://sujaloja.com.br" value="{{ old('configs.url', $integracao->config('url')) }}" :error="$errors->first('configs.url')" />
                    <x-ui.input name="configs[consumer_key]" label="Consumer Key" required placeholder="ck_..." value="{{ old('configs.consumer_key', $integracao->config('consumer_key')) }}" />
                    <x-ui.input name="configs[consumer_secret]" type="password" label="Consumer Secret" required placeholder="cs_..." value="{{ old('configs.consumer_secret', $integracao->config('consumer_secret')) }}" />
                </div>
                <p class="text-xs text-surface-400 mt-3">
                    Gere as chaves em: WooCommerce → Configurações → Avançado → API REST
                </p>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">O que sincronizar</h2>
                <div class="space-y-3">
                    @foreach([
                        'sync_pedidos'   => 'Pedidos → Contas a Receber',
                        'sync_clientes'  => 'Clientes do WooCommerce',
                        'sync_produtos'  => 'Produtos',
                    ] as $key => $label)
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="configs[{{ $key }}]" value="0">
                        <input type="checkbox" name="configs[{{ $key }}]" value="1"
                            @checked($integracao->config($key, true))
                            class="rounded border-surface-300 dark:border-surface-600 text-primary-600">
                        <span class="text-sm text-surface-700 dark:text-surface-300">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </x-ui.card>

            <div class="flex justify-end gap-3">
                <x-ui.button href="{{ route('integracoes.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar Configuração</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
