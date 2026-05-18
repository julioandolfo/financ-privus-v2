<x-layouts.app title="Boletos">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Boletos</h1>
            <p class="text-sm text-surface-500 mt-0.5">Gerencie seus boletos de cobrança</p>
        </div>
        <x-ui.button href="{{ route('boletos.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Novo Boleto
        </x-ui.button>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif
    @if(session('info'))
    <div class="mb-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-4 py-3 text-sm text-blue-700 dark:text-blue-300">
        {{ session('info') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <x-ui.stat-card
            label="Emitidos"
            :value="number_format($stats->total_emitidos)"
            color="primary"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>'
        />
        <x-ui.stat-card
            label="Pagos"
            :value="number_format($stats->total_pagos)"
            color="green"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>'
        />
        <x-ui.stat-card
            label="Vencidos"
            :value="number_format($stats->total_vencidos)"
            color="red"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>'
        />
        <x-ui.stat-card
            label="A Vencer"
            :value="number_format($stats->total_a_vencer)"
            color="yellow"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>'
        />
        <x-ui.stat-card
            label="Total em Aberto"
            :value="'R$ ' . number_format($stats->valor_em_aberto, 2, ',', '.')"
            color="primary"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>'
        />
    </div>

    {{-- Filters --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <x-ui.input name="busca" placeholder="Buscar por cliente, nº boleto ou linha digitável..." value="{{ request('busca') }}" label="Buscar" />
            </div>
            <div class="w-44">
                <x-ui.select name="status" label="Status">
                    <option value="">Todos</option>
                    <option value="rascunho"  @selected(request('status') === 'rascunho')>Rascunho</option>
                    <option value="emitido"   @selected(request('status') === 'emitido')>Emitido</option>
                    <option value="pago"      @selected(request('status') === 'pago')>Pago</option>
                    <option value="cancelado" @selected(request('status') === 'cancelado')>Cancelado</option>
                    <option value="vencido"   @selected(request('status') === 'vencido')>Vencido</option>
                </x-ui.select>
            </div>
            <div class="w-40">
                <x-ui.input name="data_inicio" type="date" label="Vencimento de" value="{{ request('data_inicio') }}" />
            </div>
            <div class="w-40">
                <x-ui.input name="data_fim" type="date" label="Vencimento até" value="{{ request('data_fim') }}" />
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
            @if(request()->hasAny(['busca','status','data_inicio','data_fim']))
            <x-ui.button href="{{ route('boletos.index') }}" variant="ghost">Limpar</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    {{-- Table --}}
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Vencimento</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Linha Digitável</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Banco</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($boletos as $boleto)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-surface-900 dark:text-white">
                                {{ $boleto->cliente?->nome_razao_social ?? '—' }}
                            </div>
                            @if($boleto->numero_boleto)
                            <div class="text-xs text-surface-400">Nº {{ $boleto->numero_boleto }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm font-semibold text-surface-900 dark:text-white text-right whitespace-nowrap">
                            R$ {{ number_format($boleto->valor, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500 whitespace-nowrap">
                            {{ $boleto->data_vencimento->format('d/m/Y') }}
                            @if($boleto->esta_vencido)
                            <span class="ml-1 text-xs text-red-500">({{ $boleto->data_vencimento->diffForHumans() }})</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $statusMap = [
                                    'rascunho'  => ['default',  'Rascunho'],
                                    'emitido'   => ['primary',  'Emitido'],
                                    'pago'      => ['success',  'Pago'],
                                    'cancelado' => ['default',  'Cancelado'],
                                    'vencido'   => ['danger',   'Vencido'],
                                ];
                                [$variant, $label] = $statusMap[$boleto->status] ?? ['default', $boleto->status];
                            @endphp
                            <x-ui.badge :variant="$boleto->esta_vencido ? 'danger' : $variant">
                                {{ $boleto->esta_vencido ? 'Vencido' : $label }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500 font-mono max-w-[180px] truncate">
                            {{ $boleto->linha_digitavel ? \Illuminate\Support\Str::limit($boleto->linha_digitavel, 30) : '—' }}
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500 uppercase">
                            {{ $boleto->banco ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('boletos.show', $boleto) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Ver
                                </a>
                                @if($boleto->status === 'rascunho')
                                <form method="POST" action="{{ route('boletos.emitir', $boleto) }}">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-primary-700 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-300 transition-colors">
                                        Emitir
                                    </button>
                                </form>
                                @endif
                                @if(in_array($boleto->status, ['emitido', 'rascunho']))
                                <form method="POST" action="{{ route('boletos.marcar-pago', $boleto) }}">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-300 transition-colors">
                                        Pago
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhum boleto encontrado.
                            <a href="{{ route('boletos.create') }}" class="text-primary-600 hover:underline ml-1">Criar novo</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($boletos->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $boletos->links() }}
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
