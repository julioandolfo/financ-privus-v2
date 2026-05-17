<x-layouts.app title="Editar Empresa">

    <div class="max-w-2xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('empresas.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $empresa->razao_social }}</h1>
                <p class="text-sm text-surface-500 mt-0.5">CNPJ: {{ $empresa->cnpj ?? '—' }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('empresas.update', $empresa) }}">
            @csrf @method('PUT')

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Dados Cadastrais</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input name="codigo" label="Código" required
                        value="{{ old('codigo', $empresa->codigo) }}" :error="$errors->first('codigo')" />
                    <x-ui.input name="cnpj" label="CNPJ"
                        value="{{ old('cnpj', $empresa->cnpj) }}" :error="$errors->first('cnpj')" hint="Opcional" />
                    <div class="sm:col-span-2">
                        <x-ui.input name="razao_social" label="Razão Social" required
                            value="{{ old('razao_social', $empresa->razao_social) }}" :error="$errors->first('razao_social')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-ui.input name="nome_fantasia" label="Nome Fantasia"
                            value="{{ old('nome_fantasia', $empresa->nome_fantasia) }}" hint="Opcional" />
                    </div>
                    @if($grupos->isNotEmpty())
                    <div class="sm:col-span-2">
                        <x-ui.select name="grupo_empresarial_id" label="Grupo Empresarial" hint="Opcional">
                            <option value="">— Nenhum —</option>
                            @foreach($grupos as $g)
                            <option value="{{ $g->id }}" @selected(old('grupo_empresarial_id', $empresa->grupo_empresarial_id) == $g->id)>{{ $g->razao_social }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @endif
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-surface-700 dark:text-surface-300">Status</p>
                        <p class="text-xs text-surface-500 mt-0.5">Empresa {{ $empresa->ativo ? 'ativa' : 'inativa' }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $empresa->ativo)) class="sr-only peer">
                        <div class="w-11 h-6 bg-surface-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 rounded-full peer dark:bg-surface-700 peer-checked:bg-primary-600 transition-colors"></div>
                        <div class="absolute left-0.5 top-0.5 bg-white rounded-full h-5 w-5 transition-transform peer-checked:translate-x-5 shadow-sm"></div>
                    </label>
                </div>
            </x-ui.card>

            <div class="flex items-center justify-between">
                <form method="POST" action="{{ route('empresas.destroy', $empresa) }}"
                      onsubmit="return confirm('Excluir esta empresa permanentemente? Todos os dados relacionados serão removidos.')">
                    @csrf @method('DELETE')
                    <x-ui.button type="submit" variant="danger">Excluir</x-ui.button>
                </form>
                <div class="flex items-center gap-3">
                    <x-ui.button href="{{ route('empresas.index') }}" variant="ghost">Cancelar</x-ui.button>
                    <x-ui.button type="submit">Salvar Alterações</x-ui.button>
                </div>
            </div>
        </form>
    </div>

</x-layouts.app>
