<x-layouts.app title="NF-e {{ $nfe->numero_serie }}">

    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('nfes.index') }}"
                   class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-semibold text-surface-900 dark:text-white font-mono">
                            NF-e {{ $nfe->numero_serie }}
                        </h1>
                        <x-ui.badge :variant="$nfe->status_variant">{{ $nfe->status_label }}</x-ui.badge>
                    </div>
                    <p class="text-sm text-surface-500 mt-0.5">{{ $nfe->natureza_operacao }}</p>
                </div>
            </div>

            {{-- Actions por status --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                @if($nfe->podeEmitir())
                    @if(!$temToken)
                    <span class="text-xs text-amber-600 bg-amber-50 dark:bg-amber-900/20 px-3 py-1.5 rounded-lg border border-amber-200 dark:border-amber-800">
                        Configure o WebmaniaBR para emitir
                    </span>
                    @else
                    <form method="POST" action="{{ route('nfes.emitir', $nfe->id) }}"
                          onsubmit="return confirm('Emitir esta NF-e? Esta ação não pode ser desfeita.')">
                        @csrf
                        <x-ui.button type="submit">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                            Emitir NF-e
                        </x-ui.button>
                    </form>
                    @endif

                    <x-ui.button href="{{ route('nfes.edit', $nfe) }}" variant="secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                        Editar
                    </x-ui.button>
                @endif

                @if($nfe->estaAutorizada())
                    <a href="{{ route('nfes.danfe', $nfe->id) }}" target="_blank">
                        <x-ui.button variant="secondary">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                            Ver DANFE
                        </x-ui.button>
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300">
            {{ session('error') }}
        </div>
        @endif

        {{-- Aviso: WebmaniaBR não configurado --}}
        @if($nfe->podeEmitir() && !$temToken)
        <div class="mb-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                <div>
                    <p class="text-sm font-medium text-amber-700 dark:text-amber-300">WebmaniaBR não configurado</p>
                    <p class="text-sm text-amber-600 dark:text-amber-400 mt-0.5">
                        Configure o token do WebmaniaBR em
                        <a href="{{ route('configuracoes.index') }}" class="underline font-medium">Configurações</a>
                        para emitir NF-e.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

            {{-- Informações principais --}}
            <div class="xl:col-span-2 space-y-4">

                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Informações Gerais</h2>
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider">Número/Série</dt>
                            <dd class="mt-1 text-sm font-semibold text-surface-900 dark:text-white font-mono">{{ $nfe->numero_serie }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider">Natureza</dt>
                            <dd class="mt-1 text-sm text-surface-700 dark:text-surface-300">{{ $nfe->natureza_operacao }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider">Data de Emissão</dt>
                            <dd class="mt-1 text-sm text-surface-700 dark:text-surface-300">
                                {{ $nfe->data_emissao ? $nfe->data_emissao->format('d/m/Y') : '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider">Competência</dt>
                            <dd class="mt-1 text-sm text-surface-700 dark:text-surface-300">
                                {{ $nfe->data_competencia ? $nfe->data_competencia->format('d/m/Y') : '—' }}
                            </dd>
                        </div>
                        @if($nfe->chave_acesso)
                        <div class="col-span-2">
                            <dt class="text-xs font-medium text-surface-500 uppercase tracking-wider">Chave de Acesso</dt>
                            <dd class="mt-1 text-xs font-mono text-surface-600 dark:text-surface-400 break-all">
                                {{ $nfe->chave_acesso }}
                            </dd>
                        </div>
                        @endif
                    </dl>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Valores</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-surface-100 dark:border-surface-700">
                            <span class="text-sm text-surface-600 dark:text-surface-400">Valor dos Produtos</span>
                            <span class="text-sm font-medium text-surface-900 dark:text-white">
                                R$ {{ number_format($nfe->valor_produtos, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-surface-100 dark:border-surface-700">
                            <span class="text-sm text-surface-600 dark:text-surface-400">Frete</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">
                                R$ {{ number_format($nfe->valor_frete, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-surface-100 dark:border-surface-700">
                            <span class="text-sm text-surface-600 dark:text-surface-400">Desconto</span>
                            <span class="text-sm text-red-600 dark:text-red-400">
                                — R$ {{ number_format($nfe->valor_desconto, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm font-semibold text-surface-900 dark:text-white">Total</span>
                            <span class="text-lg font-bold text-surface-900 dark:text-white">
                                R$ {{ number_format($nfe->valor_total, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </x-ui.card>

                @if($nfe->observacoes)
                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-2">Observações</h2>
                    <p class="text-sm text-surface-600 dark:text-surface-400 whitespace-pre-line">{{ $nfe->observacoes }}</p>
                </x-ui.card>
                @endif

                @if($nfe->status === 'cancelada' && $nfe->motivo_cancelamento)
                <x-ui.card>
                    <h2 class="text-sm font-semibold text-red-600 dark:text-red-400 mb-2">Motivo do Cancelamento</h2>
                    <p class="text-sm text-surface-600 dark:text-surface-400">{{ $nfe->motivo_cancelamento }}</p>
                </x-ui.card>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">

                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Cliente</h2>
                    @if($nfe->cliente)
                    <div>
                        <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $nfe->cliente->nome_razao_social }}</p>
                        @if($nfe->cliente->cpf_cnpj)
                        <p class="text-xs text-surface-400 mt-0.5">{{ $nfe->cliente->cpf_cnpj }}</p>
                        @endif
                        @if($nfe->cliente->email)
                        <p class="text-xs text-surface-500 mt-1">{{ $nfe->cliente->email }}</p>
                        @endif
                    </div>
                    @else
                    <p class="text-sm text-surface-400">Nenhum cliente vinculado</p>
                    @endif
                </x-ui.card>

                @if($nfe->contaReceber)
                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-3">Conta a Receber</h2>
                    <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $nfe->contaReceber->descricao }}</p>
                    <p class="text-xs text-surface-400 mt-0.5">
                        Venc: {{ $nfe->contaReceber->data_vencimento->format('d/m/Y') }}
                    </p>
                    <p class="text-xs font-semibold text-surface-700 dark:text-surface-300 mt-1">
                        R$ {{ number_format($nfe->contaReceber->valor_total, 2, ',', '.') }}
                    </p>
                </x-ui.card>
                @endif

                @if($nfe->podeCancelar())
                <x-ui.card>
                    <h2 class="text-sm font-semibold text-red-600 dark:text-red-400 mb-3">Cancelar NF-e</h2>
                    <form method="POST" action="{{ route('nfes.cancelar', $nfe->id) }}"
                          x-data="{ motivo: '' }"
                          onsubmit="return confirm('Cancelar esta NF-e? Esta ação é irreversível.')">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">
                                Motivo (mín. 15 caracteres) <span class="text-red-500">*</span>
                            </label>
                            <textarea name="motivo_cancelamento"
                                      required
                                      minlength="15"
                                      rows="3"
                                      x-model="motivo"
                                      placeholder="Descreva o motivo do cancelamento..."
                                      class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-sm ring-1 ring-inset ring-surface-200 dark:ring-surface-700 bg-white dark:bg-surface-800 text-surface-900 dark:text-white placeholder-surface-400 focus:ring-2 focus:ring-red-500 focus:outline-none transition-shadow"></textarea>
                            <p class="mt-1 text-xs text-surface-400" x-text="motivo.length + ' de 15 mínimos'"></p>
                        </div>
                        <x-ui.button type="submit" variant="danger" class="w-full">
                            Cancelar NF-e
                        </x-ui.button>
                    </form>
                </x-ui.card>
                @endif

            </div>
        </div>
    </div>

</x-layouts.app>
