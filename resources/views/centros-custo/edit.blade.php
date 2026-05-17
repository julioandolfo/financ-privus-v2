<x-layouts.app title="Editar Centro de Custo">
    <div class="max-w-xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('centros-custo.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $centroCusto->nome }}</h1>
        </div>
        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        <form method="POST" action="{{ route('centros-custo.update', $centroCusto) }}">
            @csrf @method('PUT')
            <x-ui.card class="mb-4">
                <div class="space-y-4">
                    <x-ui.input name="nome" label="Nome" required value="{{ old('nome', $centroCusto->nome) }}" :error="$errors->first('nome')" />
                    <x-ui.input name="codigo" label="Código" value="{{ old('codigo', $centroCusto->codigo) }}" hint="Opcional" />
                    @if($pais->isNotEmpty())
                    <x-ui.select name="centro_pai_id" label="Centro Pai">
                        <option value="">— Centro principal —</option>
                        @foreach($pais as $p)
                        <option value="{{ $p->id }}" @selected(old('centro_pai_id', $centroCusto->centro_pai_id) == $p->id)>{{ $p->nome }}</option>
                        @endforeach
                    </x-ui.select>
                    @endif
                    <div class="flex items-center justify-between pt-2 border-t border-surface-100 dark:border-surface-700">
                        <p class="text-sm font-medium text-surface-700 dark:text-surface-300">Ativo</p>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $centroCusto->ativo)) class="sr-only peer">
                            <div class="w-11 h-6 bg-surface-200 rounded-full peer dark:bg-surface-700 peer-checked:bg-primary-600 transition-colors"></div>
                            <div class="absolute left-0.5 top-0.5 bg-white rounded-full h-5 w-5 transition-transform peer-checked:translate-x-5 shadow-sm"></div>
                        </label>
                    </div>
                </div>
            </x-ui.card>
            <div class="flex items-center justify-between">
                <form method="POST" action="{{ route('centros-custo.destroy', $centroCusto) }}" onsubmit="return confirm('Excluir este centro?')">
                    @csrf @method('DELETE')
                    <x-ui.button type="submit" variant="danger">Excluir</x-ui.button>
                </form>
                <div class="flex gap-3">
                    <x-ui.button href="{{ route('centros-custo.index') }}" variant="ghost">Cancelar</x-ui.button>
                    <x-ui.button type="submit">Salvar</x-ui.button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
