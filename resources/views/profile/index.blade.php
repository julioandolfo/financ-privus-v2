<x-layouts.app title="Meu Perfil">

    <div class="max-w-2xl mx-auto">

        <div class="mb-6">
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Meu Perfil</h1>
            <p class="text-sm text-surface-500 mt-0.5">Gerencie suas informações de conta</p>
        </div>

        @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ session('success') }}
        </div>
        @endif

        <x-ui.card class="mb-4">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-2xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                    <span class="text-xl font-bold text-primary-600 dark:text-primary-400">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <p class="text-base font-semibold text-surface-900 dark:text-white">{{ auth()->user()->name }}</p>
                    <p class="text-sm text-surface-500">{{ auth()->user()->email }}</p>
                    <x-ui.badge variant="primary" class="mt-1">{{ ucfirst(auth()->user()->role ?? 'user') }}</x-ui.badge>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <x-ui.input name="name" label="Nome" required value="{{ old('name', auth()->user()->name) }}" :error="$errors->first('name')" />
                    <x-ui.input name="email" type="email" label="E-mail" required value="{{ old('email', auth()->user()->email) }}" :error="$errors->first('email')" />
                </div>
                <div class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700 flex justify-end">
                    <x-ui.button type="submit">Salvar Alterações</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Alterar Senha</h2>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <x-ui.input name="current_password" type="password" label="Senha Atual" :error="$errors->first('current_password')" />
                    <x-ui.input name="password" type="password" label="Nova Senha" :error="$errors->first('password')" />
                    <x-ui.input name="password_confirmation" type="password" label="Confirmar Nova Senha" />
                </div>
                <div class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700 flex justify-end">
                    <x-ui.button type="submit" variant="secondary">Alterar Senha</x-ui.button>
                </div>
            </form>
        </x-ui.card>

    </div>

</x-layouts.app>
