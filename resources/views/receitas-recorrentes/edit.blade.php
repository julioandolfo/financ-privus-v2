<x-layouts.app title="Editar Receita Recorrente">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('receitas-recorrentes.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $recorrencia->descricao }}</h1>
                <p class="text-sm text-surface-500 mt-0.5">{{ $recorrencia->frequencia_label }} · R$ {{ number_format($recorrencia->valor, 2, ',', '.') }}</p>
            </div>
        </div>
        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        @if($contasGeradas->isNotEmpty())
        <x-ui.card class="mb-4">
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-3">Últimas contas geradas</h2>
            <div class="space-y-2">
                @foreach($contasGeradas as $c)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-surface-700 dark:text-surface-300">{{ $c->data_vencimento->format('d/m/Y') }}</span>
                    <span class="text-surface-500">R$ {{ number_format($c->valor_total, 2, ',', '.') }}</span>
                    <x-ui.badge :variant="match($c->status) { 'recebido' => 'success', 'vencido' => 'danger', default => 'secondary' }">
                        {{ ucfirst($c->status) }}
                    </x-ui.badge>
                </div>
                @endforeach
            </div>
        </x-ui.card>
        @endif

        <form method="POST" action="{{ route('receitas-recorrentes.update', $recorrencia) }}"
            x-data="{ frequencia: '{{ old('frequencia', $recorrencia->frequencia) }}' }">
            @csrf @method('PUT')
            @include('receitas-recorrentes._form', ['model' => $recorrencia])
            <div class="flex items-center justify-between mt-6">
                <form method="POST" action="{{ route('receitas-recorrentes.destroy', $recorrencia) }}" onsubmit="return confirm('Remover esta recorrência?')">
                    @csrf @method('DELETE')
                    <x-ui.button type="submit" variant="danger">Excluir</x-ui.button>
                </form>
                <div class="flex gap-3">
                    <x-ui.button href="{{ route('receitas-recorrentes.index') }}" variant="ghost">Cancelar</x-ui.button>
                    <x-ui.button type="submit">Salvar Alterações</x-ui.button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
