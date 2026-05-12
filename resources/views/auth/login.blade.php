<x-layouts.guest :title="'Login — ' . config('app.name')">

    <x-slot:header>
        <h2 class="text-center text-2xl font-bold tracking-tight text-white">
            Acesse sua conta
        </h2>
        <p class="mt-2 text-center text-sm text-primary-300">
            Sistema Financeiro Empresarial
        </p>
    </x-slot:header>

    @if(session('error'))
    <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
        <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-ui.input
            label="E-mail"
            name="email"
            type="email"
            autocomplete="email"
            required
            :value="old('email')"
            :error="$errors->first('email')"
            placeholder="seu@email.com"
        />

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-300">Senha</label>
                <a href="{{ route('password.request') }}" class="text-xs text-primary-600 hover:text-primary-500">
                    Esqueceu a senha?
                </a>
            </div>
            <x-ui.input
                name="password"
                type="password"
                autocomplete="current-password"
                required
                :error="$errors->first('password')"
                placeholder="••••••••"
            />
        </div>

        <div class="flex items-center gap-2">
            <input
                id="remember"
                name="remember"
                type="checkbox"
                class="h-4 w-4 rounded border-surface-300 text-primary-600 focus:ring-primary-500"
            >
            <label for="remember" class="text-sm text-surface-600 dark:text-surface-400">
                Manter conectado
            </label>
        </div>

        <x-ui.button type="submit" class="w-full justify-center" size="lg">
            Entrar
        </x-ui.button>
    </form>

</x-layouts.guest>
