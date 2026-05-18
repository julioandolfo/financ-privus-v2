<x-layouts.app title="Contas a Pagar">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Contas a Pagar</h1>
            <p class="text-sm text-surface-500 mt-0.5">Gerencie suas obrigações financeiras</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('contas-pagar.deletados') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                Ver deletados
            </a>
            <x-ui.button href="{{ route('contas-pagar.create') }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Nova Conta
            </x-ui.button>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filters --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <x-ui.input name="search" placeholder="Buscar por descrição ou documento..." value="{{ request('search') }}" label="Buscar" />
            </div>
            <div class="w-40">
                <x-ui.select name="status" label="Status">
                    <option value="">Todos</option>
                    <option value="pendente" @selected(request('status') === 'pendente')>Pendente</option>
                    <option value="vencido"  @selected(request('status') === 'vencido')>Vencido</option>
                    <option value="parcial"  @selected(request('status') === 'parcial')>Parcial</option>
                    <option value="pago"     @selected(request('status') === 'pago')>Pago</option>
                </x-ui.select>
            </div>
            <div class="w-40">
                <x-ui.input name="vencimento_de" type="date" label="Vencimento de" value="{{ request('vencimento_de') }}" />
            </div>
            <div class="w-40">
                <x-ui.input name="vencimento_ate" type="date" label="Vencimento até" value="{{ request('vencimento_ate') }}" />
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
            @if(request()->hasAny(['search','status','vencimento_de','vencimento_ate']))
            <x-ui.button href="{{ route('contas-pagar.index') }}" variant="ghost">Limpar</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    {{-- Table --}}
    <x-ui.card :padding="false" x-data="{ selected: [], all: false }">

        {{-- Bulk action bar --}}
        <div x-show="selected.length > 0" x-cloak class="flex items-center gap-3 px-5 py-3 bg-primary-50 dark:bg-primary-900/20 border-b border-primary-100 dark:border-primary-800">
            <span class="text-sm font-medium text-primary-700 dark:text-primary-300" x-text="selected.length + ' selecionado(s)'"></span>
            <form method="POST" action="{{ route('contas-pagar.baixa-massa') }}" class="flex items-center gap-2">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <x-ui.input type="date" name="data_pagamento" required class="py-1.5 text-xs w-36" />
                <x-ui.button type="submit" size="sm" variant="primary">Baixar Selecionados</x-ui.button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="w-10 px-5 py-3">
                            <input type="checkbox" class="rounded border-surface-300 text-primary-600"
                                x-model="all"
                                @change="selected = all ? Array.from(document.querySelectorAll('[data-id]')).map(el => el.dataset.id) : []">
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Fornecedor</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Vencimento</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Pago</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($contas as $conta)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4">
                            @if(in_array($conta->status, ['pendente','parcial','vencido']))
                            <input type="checkbox" class="rounded border-surface-300 text-primary-600"
                                data-id="{{ $conta->id }}"
                                :value="'{{ $conta->id }}'"
                                x-model="selected">
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-surface-900 dark:text-white">{{ $conta->descricao }}</div>
                            @if($conta->numero_documento)
                            <div class="text-xs text-surface-400">Doc: {{ $conta->numero_documento }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $conta->fornecedor?->nome_razao_social ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-surface-500 whitespace-nowrap">
                            {{ $conta->data_vencimento->format('d/m/Y') }}
                            @if($conta->data_vencimento->isPast() && !in_array($conta->status, ['pago','cancelado']))
                            <span class="ml-1 text-xs text-red-500">({{ $conta->data_vencimento->diffForHumans() }})</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm font-semibold text-surface-900 dark:text-white text-right whitespace-nowrap">
                            R$ {{ number_format($conta->valor_total, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500 text-right whitespace-nowrap">
                            R$ {{ number_format($conta->valor_pago, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $statusMap = [
                                    'pendente'  => ['warning', 'Pendente'],
                                    'vencido'   => ['danger',  'Vencido'],
                                    'parcial'   => ['info',    'Parcial'],
                                    'pago'      => ['success', 'Pago'],
                                    'cancelado' => ['default', 'Cancelado'],
                                ];
                                [$variant, $label] = $statusMap[$conta->status] ?? ['default', $conta->status];
                            @endphp
                            <x-ui.badge :variant="$variant">{{ $label }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('contas-pagar.show', $conta) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Ver
                                </a>
                                @if(in_array($conta->status, ['pendente','parcial','vencido']))
                                <a href="{{ route('contas-pagar.edit', $conta) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-300 transition-colors">
                                    Baixar
                                </a>
                                @endif
                                @if($conta->status === 'pago')
                                <form method="POST" action="{{ route('contas-pagar.cancelar-baixa', $conta) }}"
                                      onsubmit="return confirm('Cancelar a baixa desta conta?')">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors">
                                        Cancelar Baixa
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('contas-pagar.edit', $conta) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('contas-pagar.destroy', $conta) }}"
                                      onsubmit="return confirm('Remover esta conta?')">
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
                            Nenhuma conta encontrada.
                            <a href="{{ route('contas-pagar.create') }}" class="text-primary-600 hover:underline ml-1">Criar nova</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contas->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $contas->links() }}
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
