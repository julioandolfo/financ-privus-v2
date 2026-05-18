<x-layouts.app title="Conta a Pagar — {{ $contaPagar->descricao }}">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-surface-500 mb-1">
                <a href="{{ route('contas-pagar.index') }}" class="hover:text-primary-600">Contas a Pagar</a>
                <span>/</span>
                <span>Detalhe</span>
            </div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $contaPagar->descricao }}</h1>
        </div>
        <div class="flex items-center gap-2">
            @if($contaPagar->status === 'pago' || $contaPagar->status === 'parcial')
            <form method="POST" action="{{ route('contas-pagar.cancelar-baixa', $contaPagar) }}"
                  onsubmit="return confirm('Cancelar a baixa desta conta? O status voltará para pendente.')">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/20 dark:text-orange-300 border border-orange-200 dark:border-orange-800 transition-colors">
                    Cancelar Baixa
                </button>
            </form>
            @endif
            <a href="{{ route('contas-pagar.edit', $contaPagar) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-surface-700 bg-surface-100 hover:bg-surface-200 dark:bg-surface-700 dark:text-surface-200 dark:hover:bg-surface-600 transition-colors">
                Editar
            </a>
            <a href="{{ route('contas-pagar.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                Voltar
            </a>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main info --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Basic info --}}
            <x-ui.card>
                <h2 class="text-base font-semibold text-surface-900 dark:text-white mb-4">Informações Gerais</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Descrição</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaPagar->descricao }}</dd>
                    </div>
                    @if($contaPagar->numero_documento)
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Número do Documento</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaPagar->numero_documento }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Fornecedor</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaPagar->fornecedor?->nome_razao_social ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Categoria</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaPagar->categoria?->nome ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Centro de Custo</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaPagar->centroCusto?->nome ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Forma de Pagamento</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaPagar->formaPagamento?->nome ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Conta Bancária</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaPagar->contaBancaria?->nome ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Criado por</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaPagar->user?->name ?? '—' }}</dd>
                    </div>
                    @if($contaPagar->observacoes)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Observações</dt>
                        <dd class="text-sm text-surface-900 dark:text-white whitespace-pre-line">{{ $contaPagar->observacoes }}</dd>
                    </div>
                    @endif
                </dl>
            </x-ui.card>

            {{-- Payment info --}}
            <x-ui.card>
                <h2 class="text-base font-semibold text-surface-900 dark:text-white mb-4">Informações de Pagamento</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Valor Total</dt>
                        <dd class="text-lg font-semibold text-surface-900 dark:text-white">R$ {{ number_format($contaPagar->valor_total, 2, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Valor Pago</dt>
                        <dd class="text-lg font-semibold {{ $contaPagar->valor_pago > 0 ? 'text-green-600' : 'text-surface-400' }}">
                            R$ {{ number_format($contaPagar->valor_pago, 2, ',', '.') }}
                        </dd>
                    </div>
                    @if($contaPagar->desconto > 0)
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Desconto</dt>
                        <dd class="text-sm text-green-600">R$ {{ number_format($contaPagar->desconto, 2, ',', '.') }}</dd>
                    </div>
                    @endif
                    @if($contaPagar->juros > 0)
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Juros</dt>
                        <dd class="text-sm text-red-600">R$ {{ number_format($contaPagar->juros, 2, ',', '.') }}</dd>
                    </div>
                    @endif
                    @if($contaPagar->multa > 0)
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Multa</dt>
                        <dd class="text-sm text-red-600">R$ {{ number_format($contaPagar->multa, 2, ',', '.') }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Vencimento</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">
                            {{ $contaPagar->data_vencimento->format('d/m/Y') }}
                            @if($contaPagar->data_vencimento->isPast() && !in_array($contaPagar->status, ['pago','cancelado']))
                            <span class="ml-1 text-xs text-red-500">({{ $contaPagar->data_vencimento->diffForHumans() }})</span>
                            @endif
                        </dd>
                    </div>
                    @if($contaPagar->data_competencia)
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Competência</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaPagar->data_competencia->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                    @if($contaPagar->data_pagamento)
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Data de Pagamento</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaPagar->data_pagamento->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                </dl>
            </x-ui.card>

        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">

            {{-- Status card --}}
            <x-ui.card>
                <h2 class="text-base font-semibold text-surface-900 dark:text-white mb-4">Status</h2>
                @php
                    $statusMap = [
                        'pendente'  => ['warning', 'Pendente'],
                        'vencido'   => ['danger',  'Vencido'],
                        'parcial'   => ['info',    'Parcial'],
                        'pago'      => ['success', 'Pago'],
                        'cancelado' => ['default', 'Cancelado'],
                    ];
                    [$variant, $label] = $statusMap[$contaPagar->status] ?? ['default', $contaPagar->status];
                @endphp
                <div class="flex items-center gap-3">
                    <x-ui.badge :variant="$variant" class="text-sm px-3 py-1">{{ $label }}</x-ui.badge>
                </div>

                @if($contaPagar->valor_aberto > 0 && $contaPagar->status !== 'pago')
                <div class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700">
                    <p class="text-xs text-surface-500 uppercase tracking-wider mb-1">Valor em Aberto</p>
                    <p class="text-xl font-bold text-red-600">R$ {{ number_format($contaPagar->valor_aberto, 2, ',', '.') }}</p>
                </div>
                @endif
            </x-ui.card>

            {{-- Timestamps --}}
            <x-ui.card>
                <h2 class="text-base font-semibold text-surface-900 dark:text-white mb-4">Histórico</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Criado em</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaPagar->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Última atualização</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaPagar->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </x-ui.card>

        </div>
    </div>

</x-layouts.app>
