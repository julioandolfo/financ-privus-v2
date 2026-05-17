<x-layouts.app title="Contas Bancárias">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Contas Bancárias</h1>
            <p class="text-sm text-surface-500 mt-0.5">Saldo consolidado:
                <span class="font-semibold {{ $saldoTotal >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    R$ {{ number_format($saldoTotal, 2, ',', '.') }}
                </span>
            </p>
        </div>
        <x-ui.button href="{{ route('contas-bancarias.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nova Conta
        </x-ui.button>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    @if($contas->isEmpty())
    <x-ui.card class="text-center py-12">
        <svg class="w-12 h-12 text-surface-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75" />
        </svg>
        <p class="text-sm text-surface-400 mb-3">Nenhuma conta bancária cadastrada</p>
        <x-ui.button href="{{ route('contas-bancarias.create') }}" size="sm">Adicionar primeira conta</x-ui.button>
    </x-ui.card>
    @else

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($contas as $conta)
        <x-ui.card class="{{ !$conta->ativo ? 'opacity-60' : '' }}">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center
                        {{ $conta->tipo_conta === 'caixa' ? 'bg-yellow-100 dark:bg-yellow-900/30' : 'bg-primary-50 dark:bg-primary-900/30' }}">
                        @if($conta->tipo_conta === 'caixa')
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75" />
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                        </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-surface-900 dark:text-white">{{ $conta->nome }}</p>
                        @if($conta->banco_nome)
                        <p class="text-xs text-surface-400">{{ $conta->banco_nome }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    @if(!$conta->ativo)
                    <x-ui.badge variant="default">Inativa</x-ui.badge>
                    @endif
                    <a href="{{ route('contas-bancarias.edit', $conta) }}"
                       class="p-1.5 rounded-lg text-surface-400 hover:text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                    </a>
                </div>
            </div>

            <div class="space-y-1.5 mb-4">
                @if($conta->agencia || $conta->conta)
                <div class="flex gap-4 text-xs text-surface-500">
                    @if($conta->agencia)
                    <span>Ag: {{ $conta->agencia }}</span>
                    @endif
                    @if($conta->conta)
                    <span>CC: {{ $conta->conta }}</span>
                    @endif
                </div>
                @endif
                <div class="text-xs text-surface-400">
                    {{ match($conta->tipo_conta) {
                        'corrente'     => 'Conta Corrente',
                        'poupanca'     => 'Poupança',
                        'investimento' => 'Investimento',
                        'caixa'        => 'Caixa',
                        default        => ucfirst($conta->tipo_conta)
                    } }}
                </div>
            </div>

            <div class="pt-3 border-t border-surface-100 dark:border-surface-700">
                <p class="text-xs text-surface-400 mb-0.5">Saldo atual</p>
                <p class="text-xl font-bold {{ $conta->saldo_atual >= 0 ? 'text-surface-900 dark:text-white' : 'text-red-600' }}">
                    R$ {{ number_format($conta->saldo_atual, 2, ',', '.') }}
                </p>
            </div>
        </x-ui.card>
        @endforeach
    </div>
    @endif

</x-layouts.app>
