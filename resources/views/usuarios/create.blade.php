<x-layouts.app title="Novo Usuário">
    <div class="max-w-lg mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('usuarios.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Novo Usuário</h1>
                <p class="text-sm text-surface-500 mt-0.5">Conceder acesso ao sistema</p>
            </div>
        </div>
        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf
            <x-ui.card class="mb-4">
                <div class="space-y-4">
                    <x-ui.input name="name" label="Nome" required value="{{ old('name') }}" :error="$errors->first('name')" />
                    <x-ui.input name="email" type="email" label="E-mail" required value="{{ old('email') }}" :error="$errors->first('email')" />
                    <x-ui.select name="role" label="Perfil de Acesso">
                        <option value="user"  @selected(old('role','user') === 'user')>Usuário</option>
                        <option value="admin" @selected(old('role') === 'admin')>Administrador</option>
                    </x-ui.select>
                </div>
            </x-ui.card>
            <x-ui.card class="mb-6">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Senha</h2>
                <div class="space-y-4">
                    <x-ui.input name="password" type="password" label="Senha" required :error="$errors->first('password')" />
                    <x-ui.input name="password_confirmation" type="password" label="Confirmar Senha" required />
                </div>
            </x-ui.card>
            <div class="flex justify-end gap-3">
                <x-ui.button href="{{ route('usuarios.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Criar Usuário</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
