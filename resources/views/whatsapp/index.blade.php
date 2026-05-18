<x-layouts.app title="WhatsApp">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">WhatsApp</h1>
            <p class="text-sm text-surface-500 mt-0.5">Envios automáticos via Evolution API</p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.button href="{{ route('whatsapp.conexoes.create') }}" variant="secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Nova Conexão
            </x-ui.button>
            <x-ui.button href="{{ route('whatsapp.regras.create') }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Nova Regra
            </x-ui.button>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300">
        {{ session('error') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card
            label="Conexões Ativas"
            :value="$conexoesAtivas"
            color="green"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z\" /></svg>"'
        />
        <x-ui.stat-card
            label="Regras Ativas"
            :value="$regrasAtivas"
            color="primary"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12\" /></svg>"'
        />
        <x-ui.stat-card
            label="Total de Conexões"
            :value="$conexoes->count()"
            color="yellow"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244\" /></svg>"'
        />
        <x-ui.stat-card
            label="Total de Regras"
            :value="$regras->count()"
            color="primary"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z\" /></svg>"'
        />
    </div>

    {{-- Conexões --}}
    <x-ui.card class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-surface-900 dark:text-white">Conexões Evolution API</h2>
            <x-ui.button href="{{ route('whatsapp.conexoes.index') }}" variant="ghost" size="sm">Ver todas</x-ui.button>
        </div>

        @if($conexoes->isEmpty())
        <div class="py-8 text-center">
            <p class="text-sm text-surface-400 mb-3">Nenhuma conexão configurada.</p>
            <x-ui.button href="{{ route('whatsapp.conexoes.create') }}" variant="outline" size="sm">
                Configurar primeira conexão
            </x-ui.button>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($conexoes as $conexao)
            <div class="rounded-xl border border-surface-200 dark:border-surface-700 p-4 {{ $conexao->ativo ? 'bg-green-50/30 dark:bg-green-900/10' : 'bg-surface-50 dark:bg-surface-800/30' }}">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-surface-900 dark:text-white truncate">{{ $conexao->nome }}</p>
                        <p class="text-xs text-surface-400 truncate">{{ $conexao->base_url }}</p>
                    </div>
                    @if($conexao->ativo)
                        <x-ui.badge variant="success">Ativo</x-ui.badge>
                    @else
                        <x-ui.badge variant="default">Inativo</x-ui.badge>
                    @endif
                </div>
                @if($conexao->numero_remetente)
                <p class="text-xs text-surface-500 mb-3">
                    <span class="font-medium">Remetente:</span> {{ $conexao->numero_remetente }}
                </p>
                @endif
                <div class="flex items-center gap-2">
                    <a href="{{ route('whatsapp.conexoes.edit', $conexao) }}"
                       class="text-xs text-surface-500 hover:text-surface-700 dark:hover:text-surface-300 transition-colors">
                        Editar
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </x-ui.card>

    {{-- Regras --}}
    <x-ui.card :padding="false">
        <div class="flex items-center justify-between px-5 py-4 border-b border-surface-100 dark:border-surface-700">
            <h2 class="text-base font-semibold text-surface-900 dark:text-white">Regras de Envio</h2>
            <x-ui.button href="{{ route('whatsapp.regras.index') }}" variant="ghost" size="sm">Ver todas</x-ui.button>
        </div>

        @if($regras->isEmpty())
        <div class="py-8 text-center">
            <p class="text-sm text-surface-400 mb-3">Nenhuma regra de envio configurada.</p>
            <x-ui.button href="{{ route('whatsapp.regras.create') }}" variant="outline" size="sm">
                Criar primeira regra
            </x-ui.button>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Regra</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Periodicidade</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Destinatários</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Último Envio</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @foreach($regras as $regra)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-surface-900 dark:text-white">{{ $regra->nome }}</div>
                            @if($regra->evolutionConfig)
                            <div class="text-xs text-surface-400">{{ $regra->evolutionConfig->nome }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $tipoVariants = [
                                    'vencimentos'  => 'warning',
                                    'fluxo_caixa'  => 'info',
                                    'dre'          => 'primary',
                                    'recorrencias' => 'default',
                                    'cobranca'     => 'danger',
                                ];
                            @endphp
                            <x-ui.badge :variant="$tipoVariants[$regra->tipo] ?? 'default'">
                                {{ $regra->tipo_label }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">
                            {{ $regra->periodicidade_label }}
                            <div class="text-xs text-surface-400">{{ \Illuminate\Support\Str::substr($regra->hora_envio, 0, 5) }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">
                            {{ $regra->destinatarios->count() }}
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">
                            {{ $regra->ultimo_envio ? $regra->ultimo_envio->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="px-5 py-4">
                            @if($regra->ativo)
                                <x-ui.badge variant="success">Ativa</x-ui.badge>
                            @else
                                <x-ui.badge variant="default">Inativa</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('whatsapp.regras.edit', $regra) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Editar
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
