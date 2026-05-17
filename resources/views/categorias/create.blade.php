<x-layouts.app title="Nova Categoria">
    <div class="max-w-xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('categorias.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Nova Categoria</h1>
                <p class="text-sm text-surface-500 mt-0.5">Crie uma categoria para classificar lançamentos</p>
            </div>
        </div>
        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        <form method="POST" action="{{ route('categorias.store') }}">
            @csrf
            <x-ui.card class="mb-4">
                <div class="space-y-4">
                    <x-ui.input name="nome" label="Nome" required value="{{ old('nome') }}" :error="$errors->first('nome')" />
                    <x-ui.input name="codigo" label="Código" value="{{ old('codigo') }}" hint="Opcional — ex: 1.1, DESP-01" />
                    <x-ui.select name="tipo" label="Tipo" required :error="$errors->first('tipo')">
                        <option value="despesa"  @selected(old('tipo') === 'despesa')>Despesa</option>
                        <option value="receita"  @selected(old('tipo') === 'receita')>Receita</option>
                        <option value="ambos"    @selected(old('tipo') === 'ambos')>Ambos</option>
                    </x-ui.select>
                    @if($pais->isNotEmpty())
                    <x-ui.select name="categoria_pai_id" label="Categoria Pai" hint="Deixe em branco para categoria principal">
                        <option value="">— Categoria principal —</option>
                        @foreach($pais as $p)
                        <option value="{{ $p->id }}" @selected(old('categoria_pai_id') == $p->id)>{{ $p->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    @endif
                </div>
            </x-ui.card>
            <div class="flex justify-end gap-3">
                <x-ui.button href="{{ route('categorias.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
