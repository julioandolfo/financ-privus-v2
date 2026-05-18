<x-layouts.app title="Boleto #{{ $boleto->id }}">

    <div class="max-w-4xl mx-auto" x-data="{
        copied: false,
        copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        }
    }">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('boletos.index') }}"
               class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-semibold text-surface-900 dark:text-white">
                        Boleto #{{ $boleto->id }}
                        @if($boleto->numero_boleto) — {{ $boleto->numero_boleto }} @endif
                    </h1>
                    @php
                        $statusMap = [
                            'rascunho'  => ['default',  'Rascunho'],
                            'emitido'   => ['primary',  'Emitido'],
                            'pago'      => ['success',  'Pago'],
                            'cancelado' => ['default',  'Cancelado'],
                            'vencido'   => ['danger',   'Vencido'],
                        ];
                        [$variant, $label] = $statusMap[$boleto->status] ?? ['default', $boleto->status];
                    @endphp
                    <x-ui.badge :variant="$boleto->esta_vencido ? 'danger' : $variant">
                        {{ $boleto->esta_vencido ? 'Vencido' : $label }}
                    </x-ui.badge>
                </div>
                <p class="text-sm text-surface-500 mt-0.5">
                    Criado em {{ $boleto->created_at->format('d/m/Y \à\s H:i') }}
                </p>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ session('success') }}
        </div>
        @endif
        @if(session('info'))
        <div class="mb-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-4 py-3 text-sm text-blue-700 dark:text-blue-300">
            {{ session('info') }}
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- Main info --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Dados do boleto --}}
                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Dados do Boleto</h2>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3">
                        <div>
                            <dt class="text-xs text-surface-400 uppercase tracking-wide">Cliente</dt>
                            <dd class="mt-0.5 text-sm font-medium text-surface-900 dark:text-white">
                                {{ $boleto->cliente?->nome_razao_social ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-surface-400 uppercase tracking-wide">Valor</dt>
                            <dd class="mt-0.5 text-xl font-bold text-surface-900 dark:text-white">
                                R$ {{ number_format($boleto->valor, 2, ',', '.') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-surface-400 uppercase tracking-wide">Vencimento</dt>
                            <dd class="mt-0.5 text-sm font-medium {{ $boleto->esta_vencido ? 'text-red-600 dark:text-red-400' : 'text-surface-900 dark:text-white' }}">
                                {{ $boleto->data_vencimento->format('d/m/Y') }}
                                @if($boleto->esta_vencido)
                                <span class="text-xs">({{ $boleto->data_vencimento->diffForHumans() }})</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-surface-400 uppercase tracking-wide">Emissão</dt>
                            <dd class="mt-0.5 text-sm text-surface-900 dark:text-white">
                                {{ $boleto->data_emissao?->format('d/m/Y') ?? '—' }}
                            </dd>
                        </div>
                        @if($boleto->data_pagamento)
                        <div>
                            <dt class="text-xs text-surface-400 uppercase tracking-wide">Pagamento</dt>
                            <dd class="mt-0.5 text-sm font-medium text-green-600 dark:text-green-400">
                                {{ $boleto->data_pagamento->format('d/m/Y') }}
                            </dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-xs text-surface-400 uppercase tracking-wide">Banco</dt>
                            <dd class="mt-0.5 text-sm text-surface-900 dark:text-white uppercase">
                                {{ $boleto->banco ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-surface-400 uppercase tracking-wide">Multa</dt>
                            <dd class="mt-0.5 text-sm text-surface-900 dark:text-white">{{ $boleto->multa }}%</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-surface-400 uppercase tracking-wide">Juros ao Mês</dt>
                            <dd class="mt-0.5 text-sm text-surface-900 dark:text-white">{{ $boleto->juros }}%</dd>
                        </div>
                        @if($boleto->desconto > 0)
                        <div>
                            <dt class="text-xs text-surface-400 uppercase tracking-wide">Desconto</dt>
                            <dd class="mt-0.5 text-sm text-green-600 dark:text-green-400">
                                R$ {{ number_format($boleto->desconto, 2, ',', '.') }}
                            </dd>
                        </div>
                        @endif
                        @if($boleto->nosso_numero)
                        <div>
                            <dt class="text-xs text-surface-400 uppercase tracking-wide">Nosso Número</dt>
                            <dd class="mt-0.5 text-sm font-mono text-surface-900 dark:text-white">{{ $boleto->nosso_numero }}</dd>
                        </div>
                        @endif
                    </dl>
                </x-ui.card>

                {{-- Linha digitável --}}
                @if($boleto->linha_digitavel)
                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-3">Linha Digitável</h2>
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-50 dark:bg-surface-900 border border-surface-200 dark:border-surface-700">
                        <code class="flex-1 text-sm font-mono text-surface-800 dark:text-surface-200 break-all">
                            {{ $boleto->linha_digitavel }}
                        </code>
                        <button type="button"
                                @click="copyText('{{ $boleto->linha_digitavel }}')"
                                class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                                       text-surface-600 hover:bg-surface-200 dark:text-surface-400 dark:hover:bg-surface-700">
                            <svg x-show="!copied" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                            </svg>
                            <svg x-show="copied" x-cloak class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span x-show="!copied">Copiar</span>
                            <span x-show="copied" x-cloak class="text-green-600">Copiado!</span>
                        </button>
                    </div>
                </x-ui.card>
                @endif

                {{-- Código de barras --}}
                @if($boleto->codigo_barras)
                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-3">Código de Barras</h2>
                    <div class="p-3 rounded-xl bg-surface-50 dark:bg-surface-900 border border-surface-200 dark:border-surface-700">
                        <code class="text-xs font-mono text-surface-700 dark:text-surface-300 break-all">
                            {{ $boleto->codigo_barras }}
                        </code>
                    </div>
                </x-ui.card>
                @endif

                {{-- PIX --}}
                @if($boleto->pix_copia_cola || $boleto->pix_qrcode)
                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-3">PIX</h2>
                    @if($boleto->pix_copia_cola)
                    <div class="mb-3">
                        <p class="text-xs text-surface-400 mb-1.5">Pix Copia e Cola</p>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-50 dark:bg-surface-900 border border-surface-200 dark:border-surface-700">
                            <code class="flex-1 text-xs font-mono text-surface-700 dark:text-surface-300 break-all">
                                {{ \Illuminate\Support\Str::limit($boleto->pix_copia_cola, 80) }}
                            </code>
                            <button type="button"
                                    @click="copyText('{{ addslashes($boleto->pix_copia_cola) }}')"
                                    class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-200 dark:text-surface-400 dark:hover:bg-surface-700 transition-colors">
                                Copiar
                            </button>
                        </div>
                    </div>
                    @endif
                    @if($boleto->pix_qrcode)
                    <div>
                        <p class="text-xs text-surface-400 mb-2">QR Code</p>
                        <img src="{{ $boleto->pix_qrcode }}" alt="QR Code PIX" class="w-40 h-40 rounded-xl border border-surface-200 dark:border-surface-700" />
                    </div>
                    @endif
                </x-ui.card>
                @endif

                {{-- Instruções --}}
                @if($boleto->instrucoes)
                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-2">Instruções ao Caixa</h2>
                    <p class="text-sm text-surface-600 dark:text-surface-400 whitespace-pre-line">{{ $boleto->instrucoes }}</p>
                </x-ui.card>
                @endif

                {{-- Boleto iframe / link --}}
                @if($boleto->url_boleto)
                <x-ui.card>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Boleto PDF</h2>
                        <a href="{{ $boleto->url_boleto }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            Abrir em nova aba
                        </a>
                    </div>
                    <iframe src="{{ $boleto->url_boleto }}"
                            class="w-full h-96 rounded-xl border border-surface-200 dark:border-surface-700"
                            title="Boleto PDF">
                    </iframe>
                </x-ui.card>
                @endif

            </div>

            {{-- Sidebar: actions + timeline --}}
            <div class="space-y-4">

                {{-- Actions --}}
                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-3">Ações</h2>
                    <div class="flex flex-col gap-2">

                        @if($boleto->status === 'rascunho')
                        <form method="POST" action="{{ route('boletos.emitir', $boleto) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium bg-primary-600 text-white hover:bg-primary-700 transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                </svg>
                                Emitir Boleto
                            </button>
                        </form>
                        @endif

                        @if(in_array($boleto->status, ['rascunho', 'emitido']))
                        <form method="POST" action="{{ route('boletos.marcar-pago', $boleto) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium bg-green-600 text-white hover:bg-green-700 transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Marcar como Pago
                            </button>
                        </form>
                        @endif

                        @if(!in_array($boleto->status, ['pago', 'cancelado']))
                        <form method="POST" action="{{ route('boletos.cancelar', $boleto) }}"
                              onsubmit="return confirm('Cancelar este boleto?')">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                                Cancelar Boleto
                            </button>
                        </form>
                        @endif

                        @if($boleto->status === 'rascunho')
                        <form method="POST" action="{{ route('boletos.destroy', $boleto) }}"
                              onsubmit="return confirm('Excluir este boleto? Esta ação não pode ser desfeita.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-surface-600 hover:bg-surface-100 dark:text-surface-400 dark:hover:bg-surface-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                Excluir Rascunho
                            </button>
                        </form>
                        @endif

                    </div>
                </x-ui.card>

                {{-- Conta a Receber vinculada --}}
                @if($boleto->contaReceber)
                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-3">Conta a Receber</h2>
                    <div class="space-y-1.5">
                        <p class="text-sm font-medium text-surface-900 dark:text-white">
                            {{ $boleto->contaReceber->descricao }}
                        </p>
                        <p class="text-sm text-surface-500">
                            R$ {{ number_format($boleto->contaReceber->valor_total, 2, ',', '.') }}
                        </p>
                        <a href="{{ route('contas-receber.edit', $boleto->contaReceber) }}"
                           class="inline-flex items-center text-xs text-primary-600 hover:underline dark:text-primary-400 mt-1">
                            Ver conta a receber →
                        </a>
                    </div>
                </x-ui.card>
                @endif

                {{-- Status timeline --}}
                <x-ui.card>
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Histórico de Status</h2>
                    <ol class="relative border-l border-surface-200 dark:border-surface-700 ml-2 space-y-4">

                        {{-- Created --}}
                        <li class="ml-4">
                            <div class="absolute -left-1.5 w-3 h-3 rounded-full bg-surface-300 dark:bg-surface-600 border-2 border-white dark:border-surface-800"></div>
                            <p class="text-xs font-semibold text-surface-900 dark:text-white">Criado</p>
                            <p class="text-xs text-surface-400">{{ $boleto->created_at->format('d/m/Y H:i') }}</p>
                        </li>

                        @if($boleto->data_emissao && $boleto->status !== 'rascunho')
                        <li class="ml-4">
                            <div class="absolute -left-1.5 w-3 h-3 rounded-full bg-primary-400 border-2 border-white dark:border-surface-800"></div>
                            <p class="text-xs font-semibold text-surface-900 dark:text-white">Emitido</p>
                            <p class="text-xs text-surface-400">{{ $boleto->data_emissao->format('d/m/Y') }}</p>
                        </li>
                        @endif

                        @if($boleto->status === 'pago')
                        <li class="ml-4">
                            <div class="absolute -left-1.5 w-3 h-3 rounded-full bg-green-400 border-2 border-white dark:border-surface-800"></div>
                            <p class="text-xs font-semibold text-green-600 dark:text-green-400">Pago</p>
                            <p class="text-xs text-surface-400">{{ $boleto->data_pagamento?->format('d/m/Y') ?? '—' }}</p>
                        </li>
                        @endif

                        @if($boleto->status === 'cancelado')
                        <li class="ml-4">
                            <div class="absolute -left-1.5 w-3 h-3 rounded-full bg-surface-400 border-2 border-white dark:border-surface-800"></div>
                            <p class="text-xs font-semibold text-surface-500">Cancelado</p>
                            <p class="text-xs text-surface-400">{{ $boleto->updated_at->format('d/m/Y H:i') }}</p>
                        </li>
                        @endif

                        @if($boleto->esta_vencido)
                        <li class="ml-4">
                            <div class="absolute -left-1.5 w-3 h-3 rounded-full bg-red-400 border-2 border-white dark:border-surface-800"></div>
                            <p class="text-xs font-semibold text-red-600 dark:text-red-400">Vencido</p>
                            <p class="text-xs text-surface-400">{{ $boleto->data_vencimento->format('d/m/Y') }}</p>
                        </li>
                        @endif

                    </ol>
                </x-ui.card>

                {{-- API integration notice --}}
                <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-amber-800 dark:text-amber-300">Integração Bancária</p>
                            <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                                Para emissão e cancelamento automáticos, configure a integração com o banco em
                                <a href="{{ route('integracoes.index') }}" class="underline">Integrações</a>.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</x-layouts.app>
