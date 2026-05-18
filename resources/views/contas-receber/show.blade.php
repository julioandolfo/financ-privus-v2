<x-layouts.app title="Conta a Receber — {{ $contaReceber->descricao }}">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-surface-500 mb-1">
                <a href="{{ route('contas-receber.index') }}" class="hover:text-primary-600">Contas a Receber</a>
                <span>/</span>
                <span>Detalhe</span>
            </div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $contaReceber->descricao }}</h1>
        </div>
        <div class="flex items-center gap-2">
            @if($contaReceber->status === 'pago' || $contaReceber->status === 'parcial')
            <form method="POST" action="{{ route('contas-receber.cancelar-baixa', $contaReceber) }}"
                  onsubmit="return confirm('Cancelar a baixa desta conta? O status voltará para pendente.')">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/20 dark:text-orange-300 border border-orange-200 dark:border-orange-800 transition-colors">
                    Cancelar Baixa
                </button>
            </form>
            @endif
            <a href="{{ route('contas-receber.edit', $contaReceber) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-surface-700 bg-surface-100 hover:bg-surface-200 dark:bg-surface-700 dark:text-surface-200 dark:hover:bg-surface-600 transition-colors">
                Editar
            </a>
            <a href="{{ route('contas-receber.index') }}"
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
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaReceber->descricao }}</dd>
                    </div>
                    @if($contaReceber->numero_documento)
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Número do Documento</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaReceber->numero_documento }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Cliente</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaReceber->cliente?->nome_razao_social ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Categoria</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaReceber->categoria?->nome ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Centro de Custo</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaReceber->centroCusto?->nome ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Forma de Recebimento</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaReceber->formaRecebimento?->nome ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Conta Bancária</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaReceber->contaBancaria?->nome ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Criado por</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaReceber->user?->name ?? '—' }}</dd>
                    </div>
                    @if($contaReceber->observacoes)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Observações</dt>
                        <dd class="text-sm text-surface-900 dark:text-white whitespace-pre-line">{{ $contaReceber->observacoes }}</dd>
                    </div>
                    @endif
                </dl>
            </x-ui.card>

            {{-- Payment info --}}
            <x-ui.card>
                <h2 class="text-base font-semibold text-surface-900 dark:text-white mb-4">Informações de Recebimento</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Valor Total</dt>
                        <dd class="text-lg font-semibold text-surface-900 dark:text-white">R$ {{ number_format($contaReceber->valor_total, 2, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Valor Recebido</dt>
                        <dd class="text-lg font-semibold {{ $contaReceber->valor_recebido > 0 ? 'text-green-600' : 'text-surface-400' }}">
                            R$ {{ number_format($contaReceber->valor_recebido, 2, ',', '.') }}
                        </dd>
                    </div>
                    @if($contaReceber->desconto > 0)
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Desconto</dt>
                        <dd class="text-sm text-green-600">R$ {{ number_format($contaReceber->desconto, 2, ',', '.') }}</dd>
                    </div>
                    @endif
                    @if($contaReceber->juros > 0)
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Juros</dt>
                        <dd class="text-sm text-red-600">R$ {{ number_format($contaReceber->juros, 2, ',', '.') }}</dd>
                    </div>
                    @endif
                    @if($contaReceber->multa > 0)
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Multa</dt>
                        <dd class="text-sm text-red-600">R$ {{ number_format($contaReceber->multa, 2, ',', '.') }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Vencimento</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">
                            {{ $contaReceber->data_vencimento->format('d/m/Y') }}
                            @if($contaReceber->data_vencimento->isPast() && !in_array($contaReceber->status, ['pago','cancelado']))
                            <span class="ml-1 text-xs text-red-500">({{ $contaReceber->data_vencimento->diffForHumans() }})</span>
                            @endif
                        </dd>
                    </div>
                    @if($contaReceber->data_competencia)
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Competência</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaReceber->data_competencia->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                    @if($contaReceber->data_recebimento)
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Data de Recebimento</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaReceber->data_recebimento->format('d/m/Y') }}</dd>
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
                        'pago'      => ['success', 'Recebido'],
                        'cancelado' => ['default', 'Cancelado'],
                    ];
                    [$variant, $label] = $statusMap[$contaReceber->status] ?? ['default', $contaReceber->status];
                @endphp
                <div class="flex items-center gap-3">
                    <x-ui.badge :variant="$variant" class="text-sm px-3 py-1">{{ $label }}</x-ui.badge>
                </div>

                @if($contaReceber->valor_aberto > 0 && $contaReceber->status !== 'pago')
                <div class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700">
                    <p class="text-xs text-surface-500 uppercase tracking-wider mb-1">Valor em Aberto</p>
                    <p class="text-xl font-bold text-red-600">R$ {{ number_format($contaReceber->valor_aberto, 2, ',', '.') }}</p>
                </div>
                @endif
            </x-ui.card>

            {{-- Timestamps --}}
            <x-ui.card>
                <h2 class="text-base font-semibold text-surface-900 dark:text-white mb-4">Histórico</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Criado em</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaReceber->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider mb-1">Última atualização</dt>
                        <dd class="text-sm text-surface-900 dark:text-white">{{ $contaReceber->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </x-ui.card>

        </div>
    </div>

</x-layouts.app>
