<x-layouts.app title="Nova Empresa">

    <div class="max-w-2xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('empresas.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Nova Empresa</h1>
                <p class="text-sm text-surface-500 mt-0.5">Cadastre uma nova empresa no sistema</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('empresas.store') }}">
            @csrf

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Dados Cadastrais</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input name="codigo" label="Código" required placeholder="EMP001"
                        value="{{ old('codigo') }}" :error="$errors->first('codigo')" />
                    <x-ui.input name="cnpj" label="CNPJ" placeholder="00.000.000/0000-00"
                        value="{{ old('cnpj') }}" :error="$errors->first('cnpj')" hint="Opcional" />
                    <div class="sm:col-span-2">
                        <x-ui.input name="razao_social" label="Razão Social" required
                            value="{{ old('razao_social') }}" :error="$errors->first('razao_social')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-ui.input name="nome_fantasia" label="Nome Fantasia"
                            value="{{ old('nome_fantasia') }}" hint="Opcional" />
                    </div>
                    @if($grupos->isNotEmpty())
                    <div class="sm:col-span-2">
                        <x-ui.select name="grupo_empresarial_id" label="Grupo Empresarial" hint="Opcional">
                            <option value="">— Nenhum —</option>
                            @foreach($grupos as $g)
                            <option value="{{ $g->id }}" @selected(old('grupo_empresarial_id') == $g->id)>{{ $g->razao_social }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @endif
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('empresas.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar Empresa</x-ui.button>
            </div>
        </form>
    </div>

</x-layouts.app>
