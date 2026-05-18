<x-layouts.app title="Contas a Receber — Deletados">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-surface-500 mb-1">
                <a href="{{ route('contas-receber.index') }}" class="hover:text-primary-600">Contas a Receber</a>
                <span>/</span>
                <span>Deletados</span>
            </div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Contas Deletadas</h1>
            <p class="text-sm text-surface-500 mt-0.5">Registros removidos que podem ser restaurados</p>
        </div>
        <x-ui.button href="{{ route('contas-receber.index') }}" variant="secondary">
            Voltar à lista
        </x-ui.button>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- Table --}}
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Vencimento</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Deletado em</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($contas as $conta)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-surface-900 dark:text-white">{{ $conta->descricao }}</div>
                            @if($conta->numero_documento)
                            <div class="text-xs text-surface-400">Doc: {{ $conta->numero_documento }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">{{ $conta->cliente?->nome_razao_social ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-surface-500 whitespace-nowrap">
                            {{ $conta->data_vencimento->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-4 text-sm font-semibold text-surface-900 dark:text-white text-right whitespace-nowrap">
                            R$ {{ number_format($conta->valor_total, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $statusMap = [
                                    'pendente'  => ['warning', 'Pendente'],
                                    'vencido'   => ['danger',  'Vencido'],
                                    'parcial'   => ['info',    'Parcial'],
                                    'recebido'  => ['success', 'Recebido'],
                                    'cancelado' => ['default', 'Cancelado'],
                                ];
                                [$variant, $label] = $statusMap[$conta->status] ?? ['default', $conta->status];
                            @endphp
                            <x-ui.badge :variant="$variant">{{ $label }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500 whitespace-nowrap">
                            {{ $conta->deleted_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <form method="POST" action="{{ route('contas-receber.restore', $conta->id) }}"
                                  onsubmit="return confirm('Restaurar esta conta?')">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                    Restaurar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhum registro deletado encontrado.
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
