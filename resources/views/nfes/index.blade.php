<x-layouts.app title="NF-e">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Notas Fiscais Eletrônicas</h1>
            <p class="text-sm text-surface-500 mt-0.5">Emissão e gestão de NF-e via WebmaniaBR</p>
        </div>
        <x-ui.button href="{{ route('nfes.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nova NF-e
        </x-ui.button>
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
            label="Total de NF-e"
            :value="$stats['total']"
            color="primary"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z\" /></svg>"'
        />
        <x-ui.stat-card
            label="Autorizadas"
            :value="$stats['autorizadas']"
            color="green"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\" /></svg>"'
        />
        <x-ui.stat-card
            label="Canceladas"
            :value="$stats['canceladas']"
            color="red"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M9.75 9.75l4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\" /></svg>"'
        />
        <x-ui.stat-card
            label="Rascunhos"
            :value="$stats['rascunhos']"
            color="yellow"
            :icon='"<svg class=\"w-6 h-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10\" /></svg>"'
        />
    </div>

    {{-- Filtros --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <x-ui.input
                    name="busca"
                    placeholder="Nº, chave de acesso ou cliente..."
                    value="{{ request('busca') }}"
                    label="Buscar"
                />
            </div>
            <div class="w-44">
                <x-ui.select name="status" label="Status">
                    <option value="">Todos</option>
                    <option value="rascunho"    @selected(request('status') === 'rascunho')>Rascunho</option>
                    <option value="processando" @selected(request('status') === 'processando')>Processando</option>
                    <option value="autorizada"  @selected(request('status') === 'autorizada')>Autorizada</option>
                    <option value="cancelada"   @selected(request('status') === 'cancelada')>Cancelada</option>
                    <option value="denegada"    @selected(request('status') === 'denegada')>Denegada</option>
                </x-ui.select>
            </div>
            <div class="w-40">
                <x-ui.input name="data_inicio" type="date" label="Emissão de" value="{{ request('data_inicio') }}" />
            </div>
            <div class="w-40">
                <x-ui.input name="data_fim" type="date" label="Emissão até" value="{{ request('data_fim') }}" />
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
            @if(request()->hasAny(['busca', 'status', 'data_inicio', 'data_fim']))
            <x-ui.button href="{{ route('nfes.index') }}" variant="ghost">Limpar</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    {{-- Tabela --}}
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Número</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Chave de Acesso</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Emissão</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($nfes as $nfe)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-surface-900 dark:text-white font-mono">
                                {{ $nfe->numero_serie }}
                            </div>
                            <div class="text-xs text-surface-400">{{ $nfe->natureza_operacao }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-600 dark:text-surface-400">
                            {{ $nfe->cliente?->nome_razao_social ?? '—' }}
                        </td>
                        <td class="px-5 py-4">
                            @if($nfe->chave_acesso)
                            <span class="text-xs font-mono text-surface-500 dark:text-surface-400"
                                  title="{{ $nfe->chave_acesso }}">
                                {{ substr($nfe->chave_acesso, 0, 8) }}...{{ substr($nfe->chave_acesso, -8) }}
                            </span>
                            @else
                            <span class="text-xs text-surface-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm font-semibold text-surface-900 dark:text-white text-right whitespace-nowrap">
                            R$ {{ number_format($nfe->valor_total, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500 whitespace-nowrap">
                            {{ $nfe->data_emissao ? $nfe->data_emissao->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.badge :variant="$nfe->status_variant">{{ $nfe->status_label }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('nfes.show', $nfe) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Ver
                                </a>

                                @if($nfe->podeEmitir())
                                <form method="POST" action="{{ route('nfes.emitir', $nfe->id) }}"
                                      onsubmit="return confirm('Emitir esta NF-e?')">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-300 transition-colors">
                                        Emitir
                                    </button>
                                </form>
                                @endif

                                @if($nfe->estaAutorizada())
                                <a href="{{ route('nfes.danfe', $nfe->id) }}" target="_blank"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-300 transition-colors">
                                    DANFE
                                </a>
                                @endif

                                @if($nfe->podeEmitir())
                                <a href="{{ route('nfes.edit', $nfe) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Editar
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhuma NF-e encontrada.
                            <a href="{{ route('nfes.create') }}" class="text-primary-600 hover:underline ml-1">Criar nova</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($nfes->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $nfes->links() }}
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
