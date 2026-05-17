<x-layouts.app title="Nova Despesa Recorrente">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('despesas-recorrentes.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Nova Despesa Recorrente</h1>
                <p class="text-sm text-surface-500 mt-0.5">Configure uma despesa que se repete automaticamente</p>
            </div>
        </div>
        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        <form method="POST" action="{{ route('despesas-recorrentes.store') }}" x-data="{ frequencia: '{{ old('frequencia', 'mensal') }}' }">
            @csrf
            @include('despesas-recorrentes._form')
            <div class="flex justify-end gap-3 mt-6">
                <x-ui.button href="{{ route('despesas-recorrentes.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar Recorrência</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
