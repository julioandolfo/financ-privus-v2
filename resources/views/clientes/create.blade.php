<x-layouts.app title="Novo Cliente">

    <div class="max-w-2xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('clientes.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Novo Cliente</h1>
                <p class="text-sm text-surface-500 mt-0.5">Cadastre um novo cliente</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('clientes.store') }}"
              x-data="{
                  buscando: false,
                  async buscarCnpj(valor) {
                      const cnpj = valor.replace(/\D/g, '');
                      if (cnpj.length !== 14) return;
                      this.buscando = true;
                      try {
                          const r = await fetch(`/api/cnpj/${cnpj}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                          if (!r.ok) return;
                          const d = await r.json();
                          if (d.error) return;
                          if (d.razao_social) $refs.nomeRazaoSocial.value = d.razao_social;
                          if (d.nome_fantasia) $refs.nomeFantasia.value = d.nome_fantasia;
                          if (d.email) $refs.emailField.value = d.email;
                          if (d.telefone) $refs.telefone.value = d.telefone;
                      } catch (e) {
                      } finally {
                          this.buscando = false;
                      }
                  }
              }">
            @csrf

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Identificação</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">Tipo de Pessoa</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tipo" value="fisica" @checked(old('tipo', 'fisica') === 'fisica')
                                class="text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-surface-700 dark:text-surface-300">Pessoa Física</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tipo" value="juridica" @checked(old('tipo') === 'juridica')
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
                            x-ref="nomeRazaoSocial"
                            value="{{ old('nome_razao_social') }}"
                            :error="$errors->first('nome_razao_social')"
                        />
                    </div>
                    <x-ui.input
                        name="nome_fantasia"
                        label="Nome Fantasia"
                        x-ref="nomeFantasia"
                        value="{{ old('nome_fantasia') }}"
                        hint="Opcional"
                    />
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">CPF / CNPJ</label>
                        <div class="relative">
                            <input type="text" name="cpf_cnpj"
                                value="{{ old('cpf_cnpj') }}"
                                placeholder="00.000.000/0000-00"
                                @blur="buscarCnpj($event.target.value)"
                                class="block w-full rounded-xl border-0 py-2.5 px-3.5 pr-10 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 placeholder:text-surface-400 focus:ring-2 focus:ring-primary-500 focus:outline-none transition-shadow">
                            <div x-show="buscando" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="animate-spin w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs text-surface-500">Preenche dados automaticamente para CNPJ</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Contato</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input
                            name="email"
                            type="email"
                            label="E-mail"
                            x-ref="emailField"
                            value="{{ old('email') }}"
                            :error="$errors->first('email')"
                        />
                    </div>
                    <x-ui.input name="telefone" label="Telefone" x-ref="telefone" value="{{ old('telefone') }}" placeholder="(00) 0000-0000" />
                    <x-ui.input name="celular" label="Celular" value="{{ old('celular') }}" placeholder="(00) 00000-0000" />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-6">
                <x-ui.textarea
                    name="observacoes"
                    label="Observações"
                    rows="3"
                    placeholder="Informações adicionais..."
                >{{ old('observacoes') }}</x-ui.textarea>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('clientes.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar Cliente</x-ui.button>
            </div>
        </form>
    </div>

</x-layouts.app>
