<x-layouts.app title="Configurações">

    <div class="mb-6">
        <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Configurações</h1>
        <p class="text-sm text-surface-500 mt-0.5">Personalize o comportamento do sistema</p>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
    @endif

    <div x-data="{ tab: '{{ request('tab', 'geral') }}' }" class="flex gap-6">

        {{-- Sidebar de abas --}}
        <div class="w-48 flex-shrink-0">
            <nav class="space-y-0.5">
                @foreach([
                    'geral'   => ['label' => 'Geral',         'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.28c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z'],
                    'email'   => ['label' => 'E-mail SMTP',   'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75'],
                    'ia'      => ['label' => 'IA & OpenAI',   'icon' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z'],
                ] as $key => $item)
                <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'bg-surface-100 dark:bg-surface-700 text-surface-900 dark:text-white' : 'text-surface-500 hover:bg-surface-50 dark:hover:bg-surface-800'"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium transition-colors text-left">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" /></svg>
                    {{ $item['label'] }}
                </button>
                @endforeach
            </nav>
        </div>

        {{-- Conteúdo das abas --}}
        <div class="flex-1 min-w-0">

            {{-- Geral --}}
            <div x-show="tab === 'geral'">
                <form method="POST" action="{{ route('configuracoes.update') }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="grupo" value="geral">
                    <x-ui.card>
                        <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Configurações Gerais</h2>
                        <div class="space-y-4">
                            <x-ui.select name="geral.moeda" label="Moeda">
                                <option value="BRL" @selected(($configs['geral.moeda'] ?? 'BRL') === 'BRL')>BRL — Real Brasileiro</option>
                                <option value="USD" @selected(($configs['geral.moeda'] ?? '') === 'USD')>USD — Dólar Americano</option>
                                <option value="EUR" @selected(($configs['geral.moeda'] ?? '') === 'EUR')>EUR — Euro</option>
                            </x-ui.select>
                            <x-ui.select name="geral.formato_data" label="Formato de Data">
                                <option value="d/m/Y" @selected(($configs['geral.formato_data'] ?? 'd/m/Y') === 'd/m/Y')>DD/MM/AAAA</option>
                                <option value="m/d/Y" @selected(($configs['geral.formato_data'] ?? '') === 'm/d/Y')>MM/DD/AAAA</option>
                                <option value="Y-m-d" @selected(($configs['geral.formato_data'] ?? '') === 'Y-m-d')>AAAA-MM-DD</option>
                            </x-ui.select>
                        </div>
                        <div class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700 flex justify-end">
                            <x-ui.button type="submit">Salvar</x-ui.button>
                        </div>
                    </x-ui.card>
                </form>
            </div>

            {{-- E-mail --}}
            <div x-show="tab === 'email'" x-cloak>
                <form method="POST" action="{{ route('configuracoes.update') }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="grupo" value="email">
                    <x-ui.card>
                        <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Servidor SMTP</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-ui.input name="email.smtp_host" label="Servidor SMTP" value="{{ $configs['email.smtp_host'] ?? '' }}" placeholder="smtp.gmail.com" />
                            <x-ui.input name="email.smtp_port" type="number" label="Porta" value="{{ $configs['email.smtp_port'] ?? 587 }}" />
                            <x-ui.select name="email.smtp_seguranca" label="Segurança">
                                <option value="tls"  @selected(($configs['email.smtp_seguranca'] ?? 'tls') === 'tls')>TLS</option>
                                <option value="ssl"  @selected(($configs['email.smtp_seguranca'] ?? '') === 'ssl')>SSL</option>
                                <option value="none" @selected(($configs['email.smtp_seguranca'] ?? '') === 'none')>Sem criptografia</option>
                            </x-ui.select>
                            <x-ui.input name="email.smtp_usuario" label="Usuário" value="{{ $configs['email.smtp_usuario'] ?? '' }}" />
                            <x-ui.input name="email.senha" type="password" label="Senha" value="{{ $configs['email.senha'] ?? '' }}" />
                            <x-ui.input name="email.remetente_nome" label="Nome Remetente" value="{{ $configs['email.remetente_nome'] ?? '' }}" />
                            <x-ui.input name="email.remetente_email" type="email" label="E-mail Remetente" value="{{ $configs['email.remetente_email'] ?? '' }}" />
                        </div>
                        <div class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700 flex justify-end">
                            <x-ui.button type="submit">Salvar</x-ui.button>
                        </div>
                    </x-ui.card>
                </form>
            </div>

            {{-- IA --}}
            <div x-show="tab === 'ia'" x-cloak>
                <form method="POST" action="{{ route('configuracoes.update') }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="grupo" value="ia">
                    <x-ui.card>
                        <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">OpenAI / Inteligência Artificial</h2>
                        <div class="space-y-4">
                            <x-ui.input name="api.openai_key" type="password" label="Chave API OpenAI" value="{{ $configs['api.openai_key'] ?? '' }}" placeholder="sk-..." />
                            <x-ui.select name="api.openai_model" label="Modelo">
                                <option value="gpt-4o-mini" @selected(($configs['api.openai_model'] ?? 'gpt-4o-mini') === 'gpt-4o-mini')>GPT-4o Mini (recomendado)</option>
                                <option value="gpt-4o"      @selected(($configs['api.openai_model'] ?? '') === 'gpt-4o')>GPT-4o</option>
                                <option value="gpt-4-turbo" @selected(($configs['api.openai_model'] ?? '') === 'gpt-4-turbo')>GPT-4 Turbo</option>
                            </x-ui.select>
                        </div>
                        <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mt-6 mb-4">Funcionalidades IA</h2>
                        <div class="space-y-3">
                            @foreach([
                                'ia.insights_dashboard_habilitado' => 'Insights no Dashboard',
                                'ia.sugestao_categorias'           => 'Sugestão automática de categorias',
                                'ia.deteccao_duplicatas'           => 'Detecção de lançamentos duplicados',
                            ] as $key => $label)
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="{{ $key }}" value="0">
                                <input type="checkbox" name="{{ $key }}" value="1"
                                    @checked(filter_var($configs[$key] ?? false, FILTER_VALIDATE_BOOLEAN))
                                    class="rounded border-surface-300 dark:border-surface-600 text-primary-600">
                                <span class="text-sm text-surface-700 dark:text-surface-300">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700 flex justify-end">
                            <x-ui.button type="submit">Salvar</x-ui.button>
                        </div>
                    </x-ui.card>
                </form>
            </div>

        </div>
    </div>

</x-layouts.app>
