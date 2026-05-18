<x-layouts.app title="Pedidos Vinculados">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Pedidos Vinculados</h1>
            <p class="text-sm text-surface-500 mt-0.5">Pedidos de venda manuais e de marketplaces</p>
        </div>
        <x-ui.button href="{{ route('pedidos.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Novo Pedido
        </x-ui.button>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats bar --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <x-ui.card class="col-span-2 sm:col-span-1 lg:col-span-1">
            <p class="text-xs font-medium text-surface-500 uppercase tracking-wide">Total Pedidos</p>
            <p class="mt-1.5 text-2xl font-bold text-surface-900 dark:text-white">{{ number_format($totalPedidos) }}</p>
        </x-ui.card>

        <x-ui.card class="col-span-2 sm:col-span-1 lg:col-span-1">
            <p class="text-xs font-medium text-surface-500 uppercase tracking-wide">Valor Total</p>
            <p class="mt-1.5 text-xl font-bold text-surface-900 dark:text-white">R$ {{ number_format($valorTotal, 2, ',', '.') }}</p>
        </x-ui.card>

        <x-ui.card class="col-span-2 sm:col-span-1 lg:col-span-1">
            <p class="text-xs font-medium text-surface-500 uppercase tracking-wide">Margem Média</p>
            <p class="mt-1.5 text-2xl font-bold {{ $margemMedia >= 30 ? 'text-green-600 dark:text-green-400' : ($margemMedia >= 10 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                {{ number_format($margemMedia, 1) }}%
            </p>
        </x-ui.card>

        @php
            $statusCfg = [
                'pendente'    => ['label' => 'Pendente',    'class' => 'text-yellow-700 dark:text-yellow-300'],
                'processando' => ['label' => 'Processando', 'class' => 'text-blue-700 dark:text-blue-300'],
                'concluido'   => ['label' => 'Concluído',   'class' => 'text-green-700 dark:text-green-300'],
                'cancelado'   => ['label' => 'Cancelado',   'class' => 'text-red-700 dark:text-red-300'],
                'reembolsado' => ['label' => 'Reembolsado', 'class' => 'text-surface-600 dark:text-surface-400'],
            ];
        @endphp

        @foreach($statusCfg as $key => $cfg)
        <x-ui.card>
            <p class="text-xs font-medium text-surface-500 uppercase tracking-wide">{{ $cfg['label'] }}</p>
            <p class="mt-1.5 text-2xl font-bold {{ $cfg['class'] }}">{{ $porStatus[$key] ?? 0 }}</p>
        </x-ui.card>
        @endforeach
    </div>

    {{-- Filters --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <x-ui.input
                    name="busca"
                    label="Buscar"
                    placeholder="Número do pedido ou cliente..."
                    value="{{ request('busca') }}"
                />
            </div>

            <div class="w-40">
                <x-ui.select name="origem" label="Origem">
                    <option value="">Todas</option>
                    <option value="manual"       @selected(request('origem') === 'manual')>Manual</option>
                    <option value="woocommerce"  @selected(request('origem') === 'woocommerce')>WooCommerce</option>
                    <option value="marketplace"  @selected(request('origem') === 'marketplace')>Marketplace</option>
                </x-ui.select>
            </div>

            <div class="w-40">
                <x-ui.select name="status" label="Status">
                    <option value="">Todos</option>
                    <option value="pendente"    @selected(request('status') === 'pendente')>Pendente</option>
                    <option value="processando" @selected(request('status') === 'processando')>Processando</option>
                    <option value="concluido"   @selected(request('status') === 'concluido')>Concluído</option>
                    <option value="cancelado"   @selected(request('status') === 'cancelado')>Cancelado</option>
                    <option value="reembolsado" @selected(request('status') === 'reembolsado')>Reembolsado</option>
                </x-ui.select>
            </div>

            <div class="w-44">
                <x-ui.select name="cliente_id" label="Cliente">
                    <option value="">Todos</option>
                    @foreach($clientes as $c)
                    <option value="{{ $c->id }}" @selected(request('cliente_id') == $c->id)>{{ $c->nome_razao_social }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="w-36">
                <x-ui.input name="data_inicio" type="date" label="Data início" value="{{ request('data_inicio') }}" />
            </div>

            <div class="w-36">
                <x-ui.input name="data_fim" type="date" label="Data fim" value="{{ request('data_fim') }}" />
            </div>

            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>

            @if(request()->hasAny(['busca','origem','status','cliente_id','data_inicio','data_fim']))
            <x-ui.button href="{{ route('pedidos.index') }}" variant="ghost">Limpar</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    {{-- Table with bulk selection --}}
    <x-ui.card
        :padding="false"
        x-data="{
            selected: [],
            allChecked: false,
            statusMassa: '',
            toggleAll() {
                const boxes = document.querySelectorAll('[data-pedido-id]');
                this.allChecked = !this.allChecked;
                this.selected = this.allChecked
                    ? Array.from(boxes).map(el => el.dataset.pedidoId)
                    : [];
            },
            async aplicarStatus() {
                if (!this.statusMassa || this.selected.length === 0) return;
                if (!confirm('Atualizar status de ' + this.selected.length + ' pedido(s) para \'' + this.statusMassa + '\'?')) return;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('pedidos.status-massa') }}';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                form.appendChild(csrf);

                const status = document.createElement('input');
                status.type = 'hidden';
                status.name = 'status';
                status.value = this.statusMassa;
                form.appendChild(status);

                this.selected.forEach(id => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'ids[]';
                    inp.value = id;
                    form.appendChild(inp);
                });

                document.body.appendChild(form);
                form.submit();
            }
        }"
    >

        {{-- Bulk action bar --}}
        <div
            x-show="selected.length > 0"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="flex flex-wrap items-center gap-3 px-5 py-3 bg-primary-50 dark:bg-primary-900/20 border-b border-primary-100 dark:border-primary-800"
        >
            <span class="text-sm font-medium text-primary-700 dark:text-primary-300" x-text="selected.length + ' pedido(s) selecionado(s)'"></span>

            <div class="flex items-center gap-2">
                <select
                    x-model="statusMassa"
                    class="rounded-xl border-0 py-1.5 px-3 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                >
                    <option value="">Escolher status...</option>
                    <option value="pendente">Pendente</option>
                    <option value="processando">Processando</option>
                    <option value="concluido">Concluído</option>
                    <option value="cancelado">Cancelado</option>
                    <option value="reembolsado">Reembolsado</option>
                </select>

                <x-ui.button size="sm" variant="primary" @click="aplicarStatus()" x-bind:disabled="!statusMassa">
                    Aplicar
                </x-ui.button>

                <x-ui.button size="sm" variant="ghost" @click="selected = []; allChecked = false;">
                    Limpar seleção
                </x-ui.button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="w-10 px-4 py-3">
                            <input
                                type="checkbox"
                                class="rounded border-surface-300 dark:border-surface-600 text-primary-600 focus:ring-primary-500"
                                @click="toggleAll()"
                                :checked="allChecked"
                            >
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Nº Pedido</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Origem</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Data</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Custo</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Margem</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($pedidos as $pedido)
                    @php
                        $origemCfg = [
                            'manual'      => ['label' => 'Manual',      'variant' => 'default'],
                            'woocommerce' => ['label' => 'WooCommerce', 'variant' => 'primary'],
                            'marketplace' => ['label' => 'Marketplace', 'variant' => 'info'],
                        ];
                        $statusBadge = [
                            'pendente'    => ['label' => 'Pendente',    'variant' => 'warning'],
                            'processando' => ['label' => 'Processando', 'variant' => 'info'],
                            'concluido'   => ['label' => 'Concluído',   'variant' => 'success'],
                            'cancelado'   => ['label' => 'Cancelado',   'variant' => 'danger'],
                            'reembolsado' => ['label' => 'Reembolsado', 'variant' => 'default'],
                        ];
                        $margem = $pedido->margem_percentual;
                        [$oLabel, $oVariant] = [$origemCfg[$pedido->origem]['label'], $origemCfg[$pedido->origem]['variant']];
                        [$sLabel, $sVariant] = [$statusBadge[$pedido->status]['label'], $statusBadge[$pedido->status]['variant']];
                    @endphp
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-4 py-4">
                            <input
                                type="checkbox"
                                class="rounded border-surface-300 dark:border-surface-600 text-primary-600 focus:ring-primary-500"
                                data-pedido-id="{{ $pedido->id }}"
                                :value="'{{ $pedido->id }}'"
                                x-model="selected"
                            >
                        </td>
                        <td class="px-5 py-4">
                            <a href="{{ route('pedidos.show', $pedido) }}"
                               class="text-sm font-semibold text-primary-700 dark:text-primary-400 hover:underline">
                                {{ $pedido->numero_pedido }}
                            </a>
                            @if($pedido->origem_id)
                            <div class="text-xs text-surface-400 mt-0.5">Ext: {{ $pedido->origem_id }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($pedido->cliente)
                            <div class="text-sm text-surface-900 dark:text-white">{{ $pedido->cliente->nome_razao_social }}</div>
                            @if($pedido->cliente->nome_fantasia)
                            <div class="text-xs text-surface-400">{{ $pedido->cliente->nome_fantasia }}</div>
                            @endif
                            @else
                            <span class="text-sm text-surface-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.badge :variant="$oVariant">{{ $oLabel }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500 whitespace-nowrap">
                            {{ $pedido->data_pedido->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-4 text-sm font-semibold text-surface-900 dark:text-white text-right whitespace-nowrap">
                            R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500 text-right whitespace-nowrap">
                            R$ {{ number_format($pedido->valor_custo_total, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-right whitespace-nowrap">
                            <span class="{{ $margem >= 30 ? 'text-green-600 dark:text-green-400 font-medium' : ($margem >= 10 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                                {{ number_format($margem, 1) }}%
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.badge :variant="$sVariant">{{ $sLabel }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('pedidos.show', $pedido) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Ver
                                </a>
                                <a href="{{ route('pedidos.edit', $pedido) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('pedidos.destroy', $pedido) }}"
                                      onsubmit="return confirm('Remover este pedido?')">
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
                        <td colspan="10" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhum pedido encontrado.
                            <a href="{{ route('pedidos.create') }}" class="text-primary-600 hover:underline ml-1">Criar novo pedido</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pedidos->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $pedidos->links() }}
        </div>
        @endif

    </x-ui.card>

</x-layouts.app>
