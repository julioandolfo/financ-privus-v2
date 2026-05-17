<x-layouts.app title="Editar Usuário">
    <div class="max-w-lg mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('usuarios.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $usuario->name }}</h1>
                <p class="text-sm text-surface-500 mt-0.5">{{ $usuario->email }}</p>
            </div>
        </div>
        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
            @csrf @method('PUT')
            <x-ui.card class="mb-4">
                <div class="space-y-4">
                    <x-ui.input name="name" label="Nome" required value="{{ old('name', $usuario->name) }}" :error="$errors->first('name')" />
                    <x-ui.input name="email" type="email" label="E-mail" required value="{{ old('email', $usuario->email) }}" :error="$errors->first('email')" />
                    <x-ui.select name="role" label="Perfil de Acesso">
                        <option value="user"  @selected(old('role', $usuario->role) === 'user')>Usuário</option>
                        <option value="admin" @selected(old('role', $usuario->role) === 'admin')>Administrador</option>
                    </x-ui.select>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-surface-700 dark:text-surface-300">
                            <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $usuario->ativo))
                                class="rounded border-surface-300 dark:border-surface-600 text-primary-600">
                            Usuário ativo
                        </label>
                    </div>
                </div>
            </x-ui.card>
            <x-ui.card class="mb-6">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-1">Alterar Senha</h2>
                <p class="text-xs text-surface-400 mb-4">Deixe em branco para não alterar</p>
                <div class="space-y-4">
                    <x-ui.input name="password" type="password" label="Nova Senha" :error="$errors->first('password')" />
                    <x-ui.input name="password_confirmation" type="password" label="Confirmar Nova Senha" />
                </div>
            </x-ui.card>
            <div class="flex justify-end gap-3">
                <x-ui.button href="{{ route('usuarios.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar Alterações</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
