<x-layouts.app :title="$padrao ? 'Editar Padrão de Importação' : 'Novo Padrão de Importação'">

    <div class="max-w-2xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('padroes-importacao.index') }}"
               class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">
                    {{ $padrao ? 'Editar Padrão de Importação' : 'Novo Padrão de Importação' }}
                </h1>
                <p class="text-sm text-surface-500 mt-0.5">
                    Regra de classificação automática para importação de extratos
                </p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ $action }}">
            @csrf
            @if($method === 'PUT') @method('PUT') @endif

            {{-- Correspondência --}}
            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Regra de Correspondência</h2>
                <div class="space-y-4">
                    <x-ui.input
                        name="descricao_contem"
                        label="Texto a identificar"
                        required
                        maxlength="255"
                        value="{{ old('descricao_contem', $padrao?->descricao_contem) }}"
                        placeholder="Ex.: MERCADO PAGO, PIX RECEBIDO, SALARIO..."
                        hint="Texto que será buscado na descrição da transação (sem diferenciação de maiúsculas)"
                        :error="$errors->first('descricao_contem')"
                    />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.select
                            name="tipo_correspondencia"
                            label="Tipo de correspondência"
                            required
                            :error="$errors->first('tipo_correspondencia')"
                        >
                            @php $tipoCorr = old('tipo_correspondencia', $padrao?->tipo_correspondencia ?? 'contem'); @endphp
                            <option value="contem"      @selected($tipoCorr === 'contem')>Contém</option>
                            <option value="comeca_com"  @selected($tipoCorr === 'comeca_com')>Começa com</option>
                            <option value="exato"       @selected($tipoCorr === 'exato')>Exato</option>
                        </x-ui.select>

                        <x-ui.select
                            name="tipo_transacao"
                            label="Tipo de transação"
                            required
                            :error="$errors->first('tipo_transacao')"
                        >
                            @php $tipoTrans = old('tipo_transacao', $padrao?->tipo_transacao ?? 'ambos'); @endphp
                            <option value="ambos"   @selected($tipoTrans === 'ambos')>Débitos e Créditos</option>
                            <option value="debito"  @selected($tipoTrans === 'debito')>Somente Débitos</option>
                            <option value="credito" @selected($tipoTrans === 'credito')>Somente Créditos</option>
                        </x-ui.select>
                    </div>
                </div>
            </x-ui.card>

            {{-- Classificação --}}
            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Classificação Automática</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Categoria</label>
                        <select
                            name="categoria_id"
                            class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none transition-shadow"
                        >
                            <option value="">Sem categoria (apenas sugerir descrição)</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" @selected(old('categoria_id', $padrao?->categoria_id) == $cat->id)>
                                {{ $cat->nome }}
                            </option>
                            @endforeach
                        </select>
                        @error('categoria_id')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.input
                        name="descricao_padrao"
                        label="Descrição sugerida"
                        maxlength="255"
                        value="{{ old('descricao_padrao', $padrao?->descricao_padrao) }}"
                        placeholder="Ex.: Compra supermercado"
                        hint="Se preenchido, será sugerido como descrição da transação importada"
                        :error="$errors->first('descricao_padrao')"
                    />
                </div>
            </x-ui.card>

            {{-- Opções --}}
            <x-ui.card class="mb-6">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Opções</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input
                        name="prioridade"
                        type="number"
                        min="0"
                        max="127"
                        label="Prioridade"
                        value="{{ old('prioridade', $padrao?->prioridade ?? 0) }}"
                        hint="Maior valor = verificado primeiro (0–127)"
                        :error="$errors->first('prioridade')"
                    />

                    <div class="flex items-center gap-3 mt-6">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input
                                type="checkbox"
                                name="ativo"
                                value="1"
                                @checked(old('ativo') !== null ? old('ativo') : ($padrao?->ativo ?? true))
                                class="rounded border-surface-300 dark:border-surface-600 text-primary-600 focus:ring-primary-500"
                            >
                            <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Padrão ativo</span>
                        </label>
                    </div>
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('padroes-importacao.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">
                    {{ $padrao ? 'Salvar Alterações' : 'Criar Padrão' }}
                </x-ui.button>
            </div>
        </form>
    </div>

</x-layouts.app>
