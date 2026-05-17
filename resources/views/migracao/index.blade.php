<x-layouts.app title="Migração do Sistema Legado">

<div class="max-w-2xl mx-auto"
     x-data="{
         form: {
             host:     '{{ old('host', '127.0.0.1') }}',
             port:     3306,
             database: '{{ old('database', 'financeiro') }}',
             username: '{{ old('username', 'root') }}',
             password: '',
         },
         teste: { status: null, mensagem: '', tabelas: 0 },
         etapas: [
             { key: 'empresas',             label: 'Empresas',                 status: 'pendente', output: '' },
             { key: 'categorias',           label: 'Categorias Financeiras',   status: 'pendente', output: '' },
             { key: 'centros_custo',        label: 'Centros de Custo',         status: 'pendente', output: '' },
             { key: 'formas_pagamento',     label: 'Formas de Pagamento',      status: 'pendente', output: '' },
             { key: 'usuarios',             label: 'Usuários',                 status: 'pendente', output: '' },
             { key: 'clientes',             label: 'Clientes',                 status: 'pendente', output: '' },
             { key: 'fornecedores',         label: 'Fornecedores',             status: 'pendente', output: '' },
             { key: 'contas_bancarias',     label: 'Contas Bancárias',         status: 'pendente', output: '' },
             { key: 'contas_pagar',         label: 'Contas a Pagar',           status: 'pendente', output: '' },
             { key: 'contas_receber',       label: 'Contas a Receber',         status: 'pendente', output: '' },
             { key: 'parcelas_receber',     label: 'Parcelas a Receber',       status: 'pendente', output: '' },
             { key: 'despesas_recorrentes', label: 'Despesas Recorrentes',     status: 'pendente', output: '' },
             { key: 'receitas_recorrentes', label: 'Receitas Recorrentes',     status: 'pendente', output: '' },
             { key: 'movimentacoes_caixa',  label: 'Movimentações de Caixa',   status: 'pendente', output: '' },
             { key: 'categorias_produto',   label: 'Categorias de Produto',    status: 'pendente', output: '' },
             { key: 'produtos',             label: 'Produtos',                 status: 'pendente', output: '' },
         ],
         executando: false,
         concluido:  false,
         csrfToken: '{{ csrf_token() }}',

         get testado()  { return this.teste.status !== null; },
         get testeOk()  { return this.teste.status === 'ok'; },
         get erros()    { return this.etapas.filter(e => e.status === 'erro').length; },
         get feitos()   { return this.etapas.filter(e => e.status === 'concluido').length; },

         async testar() {
             this.teste = { status: 'testando', mensagem: 'Testando conexão...', tabelas: 0 };
             try {
                 const r = await this.post('{{ route('migracao.testar') }}', this.form);
                 const d = await r.json();
                 this.teste = { status: d.ok ? 'ok' : 'erro', mensagem: d.mensagem, tabelas: d.tabelas ?? 0 };
             } catch (e) {
                 this.teste = { status: 'erro', mensagem: 'Falha de rede: ' + e.message, tabelas: 0 };
             }
         },

         async executar() {
             if (!this.testeOk || this.executando) return;
             this.executando = true;
             this.concluido  = false;

             for (const etapa of this.etapas) {
                 etapa.status = 'executando';
                 try {
                     const r = await this.post('{{ route('migracao.passo') }}', { ...this.form, etapa: etapa.key });
                     const d = await r.json();
                     etapa.status = d.ok ? 'concluido' : 'erro';
                     etapa.output = d.output ?? '';
                 } catch (e) {
                     etapa.status = 'erro';
                     etapa.output = e.message;
                 }
             }

             this.executando = false;
             this.concluido  = true;
         },

         async post(url, body) {
             return fetch(url, {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': this.csrfToken,
                     'X-Requested-With': 'XMLHttpRequest',
                 },
                 body: JSON.stringify(body),
             });
         },
     }">

    {{-- Cabeçalho --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('configuracoes.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Migração do Sistema Legado</h1>
            <p class="text-sm text-surface-500 mt-0.5">Importe os dados do banco de dados antigo diretamente pela interface</p>
        </div>
    </div>

    {{-- Alerta de aviso --}}
    <div class="mb-6 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3 flex gap-3">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <div class="text-sm text-amber-800 dark:text-amber-300">
            <p class="font-medium mb-0.5">Atenção antes de migrar</p>
            <p class="text-xs leading-relaxed">Esta operação <strong>insere ou atualiza</strong> registros pelo ID original. Execute após configurar o novo sistema e, de preferência, com o banco de dados vazio. O servidor do banco legado precisa estar acessível a partir desta máquina.</p>
        </div>
    </div>

    {{-- Credenciais --}}
    <x-ui.card class="mb-4">
        <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Credenciais do banco legado</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2 grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Host</label>
                    <input type="text" x-model="form.host" placeholder="127.0.0.1"
                        class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Porta</label>
                    <input type="number" x-model="form.port" placeholder="3306"
                        class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Banco de dados</label>
                <input type="text" x-model="form.database" placeholder="financeiro"
                    class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Usuário</label>
                <input type="text" x-model="form.username" placeholder="root"
                    class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Senha</label>
                <input type="password" x-model="form.password" placeholder="••••••••"
                    class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none">
            </div>
        </div>

        {{-- Resultado do teste --}}
        <div x-show="testado" x-cloak class="mt-4">
            <div :class="testeOk
                    ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-300'
                    : (teste.status === 'testando'
                        ? 'bg-surface-50 dark:bg-surface-800 border-surface-200 dark:border-surface-700 text-surface-500'
                        : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400')"
                 class="flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm">
                <svg x-show="teste.status === 'testando'" class="animate-spin w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <svg x-show="testeOk" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                <svg x-show="!testeOk && teste.status !== 'testando'" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                <span x-text="teste.mensagem"></span>
            </div>
        </div>

        <div class="flex items-center gap-3 mt-4">
            <button @click="testar()"
                :disabled="teste.status === 'testando' || executando"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-surface-100 dark:bg-surface-700 text-surface-700 dark:text-surface-300 hover:bg-surface-200 dark:hover:bg-surface-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <svg x-show="teste.status !== 'testando'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                <svg x-show="teste.status === 'testando'" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Testar conexão
            </button>

            <button @click="executar()"
                :disabled="!testeOk || executando"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-primary-600 text-white hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <svg x-show="!executando" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                <svg x-show="executando" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="executando ? 'Migrando...' : 'Iniciar migração'"></span>
            </button>
        </div>
    </x-ui.card>

    {{-- Progresso por etapa --}}
    <x-ui.card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Etapas da migração</h2>
            <div x-show="feitos > 0 || erros > 0" class="flex items-center gap-3 text-xs">
                <span class="text-green-600 dark:text-green-400 font-medium" x-text="feitos + ' concluídas'"></span>
                <span x-show="erros > 0" class="text-red-600 dark:text-red-400 font-medium" x-text="erros + ' com erro'"></span>
            </div>
        </div>

        {{-- Barra de progresso geral --}}
        <div x-show="feitos > 0" x-cloak class="mb-4">
            <div class="h-1.5 bg-surface-100 dark:bg-surface-700 rounded-full overflow-hidden">
                <div class="h-full bg-primary-500 rounded-full transition-all duration-300"
                     :style="'width: ' + Math.round((feitos / etapas.length) * 100) + '%'"></div>
            </div>
        </div>

        <div class="space-y-1">
            <template x-for="etapa in etapas" :key="etapa.key">
                <div>
                    <div class="flex items-center gap-3 py-2 px-3 rounded-lg"
                         :class="{
                             'bg-green-50 dark:bg-green-900/10': etapa.status === 'concluido',
                             'bg-red-50 dark:bg-red-900/10': etapa.status === 'erro',
                             'bg-primary-50 dark:bg-primary-900/10': etapa.status === 'executando',
                         }">
                        {{-- Ícone de status --}}
                        <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center">
                            <template x-if="etapa.status === 'pendente'">
                                <div class="w-3 h-3 rounded-full border-2 border-surface-300 dark:border-surface-600"></div>
                            </template>
                            <template x-if="etapa.status === 'executando'">
                                <svg class="animate-spin w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </template>
                            <template x-if="etapa.status === 'concluido'">
                                <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </template>
                            <template x-if="etapa.status === 'erro'">
                                <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </template>
                        </div>

                        {{-- Label --}}
                        <span class="flex-1 text-sm"
                              :class="{
                                  'text-surface-400 dark:text-surface-600': etapa.status === 'pendente',
                                  'text-primary-700 dark:text-primary-300 font-medium': etapa.status === 'executando',
                                  'text-green-700 dark:text-green-300': etapa.status === 'concluido',
                                  'text-red-700 dark:text-red-400': etapa.status === 'erro',
                              }"
                              x-text="etapa.label">
                        </span>

                        {{-- Output inline --}}
                        <span x-show="etapa.output && etapa.status !== 'pendente'"
                              x-text="etapa.output"
                              class="text-xs text-surface-500 dark:text-surface-400 font-mono truncate max-w-[200px]">
                        </span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Mensagem de conclusão --}}
        <div x-show="concluido" x-cloak class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700">
            <div x-show="erros === 0"
                 class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                Migração concluída com sucesso! Todos os dados foram importados.
            </div>
            <div x-show="erros > 0"
                 class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
                <p class="font-medium mb-1">Migração concluída com <span x-text="erros"></span> erro(s)</p>
                <p class="text-xs">As etapas com erro podem ser re-executadas individualmente via <code class="bg-amber-100 dark:bg-amber-900/40 px-1 rounded">php artisan migrate:legado --only=nome_da_etapa</code></p>
            </div>
        </div>
    </x-ui.card>

</div>
</x-layouts.app>
