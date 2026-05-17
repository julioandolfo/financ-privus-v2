<x-layouts.app title="Integrações">

    <div class="mb-6">
        <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Integrações</h1>
        <p class="text-sm text-surface-500 mt-0.5">Conecte o sistema com ferramentas externas</p>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-4">
        @foreach($tipos as $tipo => $info)
        @php $integracao = $integracoes->get($tipo); @endphp
        <x-ui.card>
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="text-3xl flex-shrink-0">{{ $info['icon'] }}</div>
                    <div>
                        <h2 class="text-sm font-semibold text-surface-900 dark:text-white">{{ $info['label'] }}</h2>
                        <p class="text-xs text-surface-500 mt-0.5 leading-relaxed">{{ $info['descricao'] }}</p>
                        @if($integracao)
                        <div class="flex items-center gap-2 mt-2">
                            @if($integracao->ativo)
                            <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                Ativa
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-xs text-surface-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-surface-400"></span>
                                Configurada (inativa)
                            </span>
                            @endif
                            @if($integracao->status_sync === 'erro')
                            <span class="text-xs text-red-500">● Erro na conexão</span>
                            @elseif($integracao->status_sync === 'ok')
                            <span class="text-xs text-green-500">● Conexão OK</span>
                            @endif
                            @if($integracao->ultimo_sync)
                            <span class="text-xs text-surface-400">· Sync: {{ $integracao->ultimo_sync->format('d/m H:i') }}</span>
                            @endif
                        </div>
                        @else
                        <p class="text-xs text-surface-400 mt-2">Não configurada</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-surface-100 dark:border-surface-700">
                <a href="{{ route('integracoes.configurar', $tipo) }}"
                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                    {{ $integracao ? 'Configurar' : 'Conectar' }}
                </a>
                @if($integracao && in_array($tipo, ['woocommerce', 'whatsapp']))
                <form method="POST" action="{{ route('integracoes.testar', $tipo) }}">
                    @csrf
                    <button class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-surface-100 dark:bg-surface-700 text-surface-600 dark:text-surface-300 hover:bg-surface-200 dark:hover:bg-surface-600 transition-colors">
                        Testar Conexão
                    </button>
                </form>
                @endif
            </div>
        </x-ui.card>
        @endforeach
    </div>

</x-layouts.app>
