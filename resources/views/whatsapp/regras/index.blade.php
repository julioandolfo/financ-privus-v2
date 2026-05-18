<x-layouts.app title="Regras de Envio WhatsApp">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Regras de Envio</h1>
            <p class="text-sm text-surface-500 mt-0.5">Automações de mensagens WhatsApp</p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.button href="{{ route('whatsapp.index') }}" variant="ghost">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                Dashboard
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

    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Nome</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Periodicidade</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Hora</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-surface-500 uppercase tracking-wider">Destinatários</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Último Envio</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-surface-500 uppercase tracking-wider">Ativo</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($regras as $regra)
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
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $regra->periodicidade_label }}</td>
                        <td class="px-5 py-4 text-sm text-surface-500 font-mono">
                            {{ \Illuminate\Support\Str::substr($regra->hora_envio, 0, 5) }}
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500 text-center">
                            {{ $regra->destinatarios_count }}
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">
                            {{ $regra->ultimo_envio ? $regra->ultimo_envio->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <form method="POST" action="{{ route('whatsapp.regras.toggle', $regra) }}">
                                @csrf
                                <button type="submit"
                                    title="{{ $regra->ativo ? 'Desativar' : 'Ativar' }}"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 {{ $regra->ativo ? 'bg-primary-600' : 'bg-surface-200 dark:bg-surface-600' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $regra->ativo ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('whatsapp.regras.edit', $regra) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('whatsapp.regras.destroy', $regra) }}"
                                      onsubmit="return confirm('Remover esta regra e seus destinatários?')">
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
                        <td colspan="8" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhuma regra de envio configurada.
                            <a href="{{ route('whatsapp.regras.create') }}" class="text-primary-600 hover:underline ml-1">Criar agora</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($regras->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $regras->links() }}
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
