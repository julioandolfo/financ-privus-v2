<x-layouts.app title="Editar Cliente">

    <div class="max-w-2xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('clientes.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $cliente->nome_razao_social }}</h1>
                <p class="text-sm text-surface-500 mt-0.5">{{ $cliente->cpf_cnpj ?? 'Sem CPF/CNPJ' }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('clientes.update', $cliente) }}">
            @csrf @method('PUT')

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Identificação</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">Tipo de Pessoa</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tipo" value="fisica" @checked(old('tipo', $cliente->tipo) === 'fisica')
                                class="text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-surface-700 dark:text-surface-300">Pessoa Física</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tipo" value="juridica" @checked(old('tipo', $cliente->tipo) === 'juridica')
                                class="text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-surface-700 dark:text-surface-300">Pessoa Jurídica</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input
                            name="nome_razao_social"
                            label="Nome / Razão Social"
                            required
                            value="{{ old('nome_razao_social', $cliente->nome_razao_social) }}"
                            :error="$errors->first('nome_razao_social')"
                        />
                    </div>
                    <x-ui.input name="nome_fantasia" label="Nome Fantasia" value="{{ old('nome_fantasia', $cliente->nome_fantasia) }}" hint="Opcional" />
                    <x-ui.input name="cpf_cnpj" label="CPF / CNPJ" value="{{ old('cpf_cnpj', $cliente->cpf_cnpj) }}" />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Contato</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input name="email" type="email" label="E-mail" value="{{ old('email', $cliente->email) }}" :error="$errors->first('email')" />
                    </div>
                    <x-ui.input name="telefone" label="Telefone" value="{{ old('telefone', $cliente->telefone) }}" />
                    <x-ui.input name="celular" label="Celular" value="{{ old('celular', $cliente->celular) }}" />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-surface-700 dark:text-surface-300">Status</p>
                        <p class="text-xs text-surface-500 mt-0.5">Cliente {{ $cliente->ativo ? 'ativo' : 'inativo' }} no sistema</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $cliente->ativo)) class="sr-only peer">
                        <div class="w-11 h-6 bg-surface-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 rounded-full peer dark:bg-surface-700 peer-checked:bg-primary-600 transition-colors"></div>
                        <div class="absolute left-0.5 top-0.5 bg-white rounded-full h-5 w-5 transition-transform peer-checked:translate-x-5 shadow-sm"></div>
                    </label>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-6">
                <x-ui.textarea name="observacoes" label="Observações" rows="3">{{ old('observacoes', $cliente->observacoes) }}</x-ui.textarea>
            </x-ui.card>

            <div class="flex items-center justify-between">
                <form method="POST" action="{{ route('clientes.destroy', $cliente) }}"
                      onsubmit="return confirm('Excluir este cliente permanentemente?')">
                    @csrf @method('DELETE')
                    <x-ui.button type="submit" variant="danger">Excluir</x-ui.button>
                </form>
                <div class="flex items-center gap-3">
                    <x-ui.button href="{{ route('clientes.index') }}" variant="ghost">Cancelar</x-ui.button>
                    <x-ui.button type="submit">Salvar Alterações</x-ui.button>
                </div>
            </div>
        </form>
    </div>

</x-layouts.app>
