<x-layouts.app title="Pedido #{{ $pedido->numero_pedido }}">

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
        $oLabel   = $origemCfg[$pedido->origem]['label'];
        $oVariant = $origemCfg[$pedido->origem]['variant'];
        $sLabel   = $statusBadge[$pedido->status]['label'];
        $sVariant = $statusBadge[$pedido->status]['variant'];

        $lucro         = (float) $pedido->valor_total - (float) $pedido->valor_custo_total;
        $margemPercent = $pedido->margem_percentual;
    @endphp

    {{-- Page header --}}
    <div class="flex items-start justify-between mb-6 gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('pedidos.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-semibold text-surface-900 dark:text-white">
                        Pedido #{{ $pedido->numero_pedido }}
                    </h1>
                    <x-ui.badge :variant="$oVariant">{{ $oLabel }}</x-ui.badge>
                    <x-ui.badge :variant="$sVariant">{{ $sLabel }}</x-ui.badge>
                </div>
                <p class="text-sm text-surface-500 mt-0.5">
                    Realizado em {{ $pedido->data_pedido->format('d/m/Y') }}
                    @if($pedido->origem_id)
                    &bull; ID externo: <span class="font-mono">{{ $pedido->origem_id }}</span>
                    @endif
                    @if($pedido->status_origem)
                    &bull; Status origem: {{ $pedido->status_origem }}
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
            <x-ui.button href="{{ route('pedidos.edit', $pedido) }}" variant="secondary" size="sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Editar
            </x-ui.button>
            <form method="POST" action="{{ route('pedidos.destroy', $pedido) }}"
                  onsubmit="return confirm('Remover este pedido? Esta ação não pode ser desfeita.')">
                @csrf @method('DELETE')
                <x-ui.button type="submit" variant="danger" size="sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Excluir
                </x-ui.button>
            </form>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left column: items + financials --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Items table --}}
            <x-ui.card :padding="false">
                <div class="px-5 py-4 border-b border-surface-100 dark:border-surface-700">
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">
                        Itens do Pedido
                        <span class="ml-1.5 text-xs font-normal text-surface-400">({{ $pedido->itens->count() }} {{ $pedido->itens->count() === 1 ? 'item' : 'itens' }})</span>
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                        <thead>
                            <tr class="bg-surface-50 dark:bg-surface-800/50">
                                <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Produto</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Qtd</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor Unit.</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Total Venda</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Custo Unit.</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Total Custo</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Margem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                            @forelse($pedido->itens as $item)
                            @php
                                $itemMargem = $item->margem_percentual;
                            @endphp
                            <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/30 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="text-sm font-medium text-surface-900 dark:text-white">{{ $item->nome_produto }}</div>
                                    @if($item->codigo_produto_origem)
                                    <div class="text-xs text-surface-400 mt-0.5">Cód: {{ $item->codigo_produto_origem }}</div>
                                    @endif
                                    @if($item->produto)
                                    <div class="text-xs text-surface-400 mt-0.5">SKU: {{ $item->produto->sku ?? $item->produto->codigo ?? '—' }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-surface-700 dark:text-surface-300 text-right whitespace-nowrap">
                                    {{ number_format($item->quantidade, $item->quantidade == intval($item->quantidade) ? 0 : 3, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 text-sm text-surface-700 dark:text-surface-300 text-right whitespace-nowrap">
                                    R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-surface-900 dark:text-white text-right whitespace-nowrap">
                                    R$ {{ number_format($item->valor_total, 2, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 text-sm text-surface-500 text-right whitespace-nowrap">
                                    R$ {{ number_format($item->custo_unitario, 2, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 text-sm text-surface-500 text-right whitespace-nowrap">
                                    R$ {{ number_format($item->custo_total, 2, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 text-sm text-right whitespace-nowrap">
                                    <span class="{{ $itemMargem >= 30 ? 'text-green-600 dark:text-green-400 font-medium' : ($itemMargem >= 10 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                                        {{ number_format($itemMargem, 1) }}%
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-sm text-surface-400">
                                    Nenhum item registrado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            {{-- Totals summary --}}
            <x-ui.card>
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Resumo Financeiro</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500">Valor Total Bruto</span>
                        <span class="text-sm font-semibold text-surface-900 dark:text-white">
                            R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}
                        </span>
                    </div>

                    @if($pedido->desconto > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500">Desconto</span>
                        <span class="text-sm font-medium text-red-600 dark:text-red-400">
                            - R$ {{ number_format($pedido->desconto, 2, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-surface-100 dark:border-surface-700">
                        <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Valor Líquido</span>
                        <span class="text-sm font-bold text-surface-900 dark:text-white">
                            R$ {{ number_format((float) $pedido->valor_total - (float) $pedido->desconto, 2, ',', '.') }}
                        </span>
                    </div>
                    @endif

                    <div class="flex justify-between items-center pt-2 border-t border-surface-100 dark:border-surface-700">
                        <span class="text-sm text-surface-500">Custo Total</span>
                        <span class="text-sm font-medium text-surface-600 dark:text-surface-400">
                            R$ {{ number_format($pedido->valor_custo_total, 2, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-surface-100 dark:border-surface-700">
                        <span class="text-sm font-semibold text-surface-700 dark:text-surface-300">Lucro Bruto</span>
                        <span class="text-base font-bold {{ $lucro >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            R$ {{ number_format($lucro, 2, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-surface-700 dark:text-surface-300">Margem Bruta</span>
                        <span class="text-base font-bold {{ $margemPercent >= 30 ? 'text-green-600 dark:text-green-400' : ($margemPercent >= 10 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                            {{ number_format($margemPercent, 2) }}%
                        </span>
                    </div>
                </div>
            </x-ui.card>

            {{-- Linked contas a receber --}}
            @if($contasReceber->isNotEmpty())
            <x-ui.card :padding="false">
                <div class="px-5 py-4 border-b border-surface-100 dark:border-surface-700">
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Contas a Receber Vinculadas</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                        <thead>
                            <tr class="bg-surface-50 dark:bg-surface-800/50">
                                <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Vencimento</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                            @foreach($contasReceber as $cr)
                            @php
                                $crStatus = [
                                    'pendente'  => ['warning', 'Pendente'],
                                    'vencido'   => ['danger',  'Vencido'],
                                    'parcial'   => ['info',    'Parcial'],
                                    'pago'      => ['success', 'Pago'],
                                    'cancelado' => ['default', 'Cancelado'],
                                ];
                                [$crVariant, $crLabel] = $crStatus[$cr->status] ?? ['default', $cr->status];
                            @endphp
                            <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/30 transition-colors">
                                <td class="px-5 py-4 text-sm text-surface-900 dark:text-white">{{ $cr->descricao }}</td>
                                <td class="px-5 py-4 text-sm text-surface-500 whitespace-nowrap">
                                    {{ $cr->data_vencimento?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-surface-900 dark:text-white text-right whitespace-nowrap">
                                    R$ {{ number_format($cr->valor_total, 2, ',', '.') }}
                                </td>
                                <td class="px-5 py-4">
                                    <x-ui.badge :variant="$crVariant">{{ $crLabel }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('contas-receber.edit', $cr) }}"
                                       class="text-xs font-medium text-primary-600 hover:underline">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
            @endif

        </div>

        {{-- Right column: order details sidebar --}}
        <div class="space-y-6">

            {{-- Client info --}}
            <x-ui.card>
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-3">Cliente</h2>
                @if($pedido->cliente)
                <div>
                    <p class="text-sm font-medium text-surface-900 dark:text-white">
                        {{ $pedido->cliente->nome_razao_social }}
                    </p>
                    @if($pedido->cliente->nome_fantasia)
                    <p class="text-xs text-surface-400 mt-0.5">{{ $pedido->cliente->nome_fantasia }}</p>
                    @endif
                    @if($pedido->cliente->email)
                    <p class="text-xs text-surface-500 mt-2">{{ $pedido->cliente->email }}</p>
                    @endif
                    @if($pedido->cliente->celular ?? $pedido->cliente->telefone ?? null)
                    <p class="text-xs text-surface-500 mt-0.5">{{ $pedido->cliente->celular ?? $pedido->cliente->telefone }}</p>
                    @endif
                    <div class="mt-3">
                        <a href="{{ route('clientes.edit', $pedido->cliente) }}"
                           class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
                            Ver cliente
                        </a>
                    </div>
                </div>
                @else
                <p class="text-sm text-surface-400">Nenhum cliente vinculado</p>
                @endif
            </x-ui.card>

            {{-- Order details --}}
            <x-ui.card>
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-3">Detalhes</h2>
                <dl class="space-y-2.5">
                    <div class="flex justify-between gap-2">
                        <dt class="text-xs text-surface-500">Número</dt>
                        <dd class="text-xs font-medium text-surface-900 dark:text-white text-right">{{ $pedido->numero_pedido }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-xs text-surface-500">Origem</dt>
                        <dd class="text-right"><x-ui.badge :variant="$oVariant" class="text-xs">{{ $oLabel }}</x-ui.badge></dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-xs text-surface-500">Status</dt>
                        <dd class="text-right"><x-ui.badge :variant="$sVariant" class="text-xs">{{ $sLabel }}</x-ui.badge></dd>
                    </div>
                    @if($pedido->origem_id)
                    <div class="flex justify-between gap-2">
                        <dt class="text-xs text-surface-500">ID Externo</dt>
                        <dd class="text-xs font-mono text-surface-700 dark:text-surface-300 text-right break-all">{{ $pedido->origem_id }}</dd>
                    </div>
                    @endif
                    @if($pedido->status_origem)
                    <div class="flex justify-between gap-2">
                        <dt class="text-xs text-surface-500">Status Origem</dt>
                        <dd class="text-xs text-surface-700 dark:text-surface-300 text-right">{{ $pedido->status_origem }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between gap-2 pt-2 border-t border-surface-100 dark:border-surface-700">
                        <dt class="text-xs text-surface-500">Data do Pedido</dt>
                        <dd class="text-xs font-medium text-surface-900 dark:text-white">{{ $pedido->data_pedido->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-xs text-surface-500">Criado em</dt>
                        <dd class="text-xs text-surface-500">{{ $pedido->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-xs text-surface-500">Atualizado em</dt>
                        <dd class="text-xs text-surface-500">{{ $pedido->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            {{-- Observations --}}
            @if($pedido->observacoes)
            <x-ui.card>
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-3">Observações</h2>
                <p class="text-sm text-surface-600 dark:text-surface-400 whitespace-pre-wrap">{{ $pedido->observacoes }}</p>
            </x-ui.card>
            @endif

            {{-- Quick actions --}}
            <x-ui.card>
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-3">Ações</h2>
                <div class="space-y-2">
                    <x-ui.button href="{{ route('pedidos.edit', $pedido) }}" variant="secondary" class="w-full justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Editar Pedido
                    </x-ui.button>

                    <form method="POST" action="{{ route('pedidos.destroy', $pedido) }}"
                          onsubmit="return confirm('Remover este pedido? Esta ação não pode ser desfeita.')">
                        @csrf @method('DELETE')
                        <x-ui.button type="submit" variant="outline" class="w-full justify-center text-red-600 hover:text-red-700 dark:text-red-400 border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/10">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            Excluir Pedido
                        </x-ui.button>
                    </form>
                </div>
            </x-ui.card>

        </div>
    </div>

</x-layouts.app>
