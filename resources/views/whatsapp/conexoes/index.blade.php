<x-layouts.app title="Conexões WhatsApp">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Conexões WhatsApp</h1>
            <p class="text-sm text-surface-500 mt-0.5">Instâncias Evolution API configuradas</p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.button href="{{ route('whatsapp.index') }}" variant="ghost">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                Dashboard
            </x-ui.button>
            <x-ui.button href="{{ route('whatsapp.conexoes.create') }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Nova Conexão
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

    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Nome</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Provider</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Base URL</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Remetente</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Regras</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($conexoes as $conexao)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors"
                        x-data="{ testando: false, resultado: null }"
                        id="conexao-{{ $conexao->id }}">
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-surface-900 dark:text-white">{{ $conexao->nome }}</div>
                            @if($conexao->instance_name)
                            <div class="text-xs text-surface-400">Instância: {{ $conexao->instance_name }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $conexao->provider }}</td>
                        <td class="px-5 py-4">
                            <div class="text-sm text-surface-600 dark:text-surface-400 font-mono max-w-[220px] truncate" title="{{ $conexao->base_url }}">
                                {{ $conexao->base_url }}
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">
                            {{ $conexao->numero_remetente ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500 text-center">
                            {{ $conexao->regras_count }}
                        </td>
                        <td class="px-5 py-4">
                            @if($conexao->ativo)
                                <x-ui.badge variant="success">Ativo</x-ui.badge>
                            @else
                                <x-ui.badge variant="default">Inativo</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                {{-- Botão Testar com Alpine AJAX --}}
                                <button type="button"
                                    @click="
                                        testando = true;
                                        resultado = null;
                                        fetch('{{ route('whatsapp.conexoes.testar', $conexao) }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                'Accept': 'application/json'
                                            }
                                        })
                                        .then(r => r.json())
                                        .then(data => { resultado = data.ok ? 'ok' : 'erro'; testando = false; })
                                        .catch(() => { resultado = 'erro'; testando = false; });
                                    "
                                    :disabled="testando"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors"
                                    :class="resultado === 'ok' ? 'text-green-700 bg-green-50 dark:bg-green-900/20 dark:text-green-300' : resultado === 'erro' ? 'text-red-600 bg-red-50 dark:bg-red-900/20' : 'text-blue-600 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100'">
                                    <span x-show="!testando && !resultado">Testar</span>
                                    <span x-show="testando" x-cloak>
                                        <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    </span>
                                    <span x-show="resultado === 'ok'" x-cloak>Conectado</span>
                                    <span x-show="resultado === 'erro'" x-cloak>Falhou</span>
                                </button>

                                <a href="{{ route('whatsapp.conexoes.edit', $conexao) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Editar
                                </a>

                                <form method="POST" action="{{ route('whatsapp.conexoes.destroy', $conexao) }}"
                                      onsubmit="return confirm('Remover esta conexão?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhuma conexão configurada.
                            <a href="{{ route('whatsapp.conexoes.create') }}" class="text-primary-600 hover:underline ml-1">Criar agora</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($conexoes->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $conexoes->links() }}
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
