@php
    $isEdit = isset($regra) && $regra->exists;
    $title  = $isEdit ? 'Editar Regra' : 'Nova Regra de Envio';
    $action = $isEdit ? route('whatsapp.regras.update', $regra) : route('whatsapp.regras.store');
@endphp

<x-layouts.app :title="$title">

    <div class="max-w-3xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('whatsapp.regras.index') }}"
               class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $title }}</h1>
                <p class="text-sm text-surface-500 mt-0.5">Configure o agendamento e os destinatários</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ $action }}"
              x-data="{
                  periodicidade: '{{ old('periodicidade', $regra->periodicidade ?? 'diario') }}',
                  destinatarios: {{ json_encode(
                      old('destinatarios', $isEdit
                          ? $regra->destinatarios->map(fn($d) => ['nome' => $d->nome, 'telefone' => $d->telefone])->toArray()
                          : [['nome' => '', 'telefone' => '']]
                      )
                  ) }},
                  addDestinatario() {
                      this.destinatarios.push({ nome: '', telefone: '' });
                  },
                  removeDestinatario(idx) {
                      if (this.destinatarios.length > 1) {
                          this.destinatarios.splice(idx, 1);
                      }
                  }
              }">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Configuração da Regra</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input
                            name="nome"
                            label="Nome da Regra"
                            required
                            placeholder="Ex: Vencimentos do dia"
                            value="{{ old('nome', $regra->nome ?? '') }}"
                            :error="$errors->first('nome')"
                        />
                    </div>

                    <x-ui.select name="evolution_config_id" label="Conexão WhatsApp" :error="$errors->first('evolution_config_id')">
                        <option value="">— Padrão do sistema —</option>
                        @foreach($conexoes as $cx)
                        <option value="{{ $cx->id }}" @selected(old('evolution_config_id', $regra->evolution_config_id ?? '') == $cx->id)>
                            {{ $cx->nome }}
                        </option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select name="tipo" label="Tipo de Relatório" required :error="$errors->first('tipo')">
                        <option value="">— Selecione —</option>
                        <option value="vencimentos"  @selected(old('tipo', $regra->tipo ?? '') === 'vencimentos')>Vencimentos</option>
                        <option value="fluxo_caixa"  @selected(old('tipo', $regra->tipo ?? '') === 'fluxo_caixa')>Fluxo de Caixa</option>
                        <option value="dre"          @selected(old('tipo', $regra->tipo ?? '') === 'dre')>DRE</option>
                        <option value="recorrencias" @selected(old('tipo', $regra->tipo ?? '') === 'recorrencias')>Recorrências</option>
                        <option value="cobranca"     @selected(old('tipo', $regra->tipo ?? '') === 'cobranca')>Cobrança</option>
                    </x-ui.select>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Agendamento</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
                            Periodicidade <span class="text-red-500">*</span>
                        </label>
                        <select name="periodicidade" x-model="periodicidade" required
                            class="block w-full rounded-xl border border-surface-200 dark:border-surface-600 bg-white dark:bg-surface-800 px-3.5 py-2.5 text-sm text-surface-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                            <option value="diario">Diário</option>
                            <option value="semanal">Semanal</option>
                            <option value="mensal">Mensal</option>
                        </select>
                    </div>

                    <x-ui.input
                        name="hora_envio"
                        type="time"
                        label="Hora de Envio"
                        required
                        value="{{ old('hora_envio', isset($regra->hora_envio) ? \Illuminate\Support\Str::substr($regra->hora_envio, 0, 5) : '08:00') }}"
                        :error="$errors->first('hora_envio')"
                    />

                    {{-- Dia da semana (só para semanal) --}}
                    <div x-show="periodicidade === 'semanal'" x-cloak>
                        <x-ui.select name="dia_semana" label="Dia da Semana" :error="$errors->first('dia_semana')">
                            <option value="0" @selected(old('dia_semana', $regra->dia_semana ?? '') == 0)>Domingo</option>
                            <option value="1" @selected(old('dia_semana', $regra->dia_semana ?? '') == 1)>Segunda-feira</option>
                            <option value="2" @selected(old('dia_semana', $regra->dia_semana ?? '') == 2)>Terça-feira</option>
                            <option value="3" @selected(old('dia_semana', $regra->dia_semana ?? '') == 3)>Quarta-feira</option>
                            <option value="4" @selected(old('dia_semana', $regra->dia_semana ?? '') == 4)>Quinta-feira</option>
                            <option value="5" @selected(old('dia_semana', $regra->dia_semana ?? '') == 5)>Sexta-feira</option>
                            <option value="6" @selected(old('dia_semana', $regra->dia_semana ?? '') == 6)>Sábado</option>
                        </x-ui.select>
                    </div>

                    {{-- Dia do mês (só para mensal) --}}
                    <div x-show="periodicidade === 'mensal'" x-cloak>
                        <x-ui.input
                            name="dia_mes"
                            type="number"
                            min="1"
                            max="31"
                            label="Dia do Mês"
                            placeholder="1"
                            value="{{ old('dia_mes', $regra->dia_mes ?? '') }}"
                            :error="$errors->first('dia_mes')"
                            hint="Dia 1 a 31"
                        />
                    </div>
                </div>
            </x-ui.card>

            {{-- Destinatários --}}
            <x-ui.card class="mb-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300">Destinatários</h2>
                    <button type="button" @click="addDestinatario()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Adicionar
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(dest, idx) in destinatarios" :key="idx">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Nome</label>
                                    <input type="text"
                                           :name="`destinatarios[${idx}][nome]`"
                                           x-model="dest.nome"
                                           placeholder="Ex: João Silva"
                                           class="block w-full rounded-xl border border-surface-200 dark:border-surface-600 bg-white dark:bg-surface-800 px-3.5 py-2.5 text-sm text-surface-900 dark:text-white placeholder-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">
                                        Telefone <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           :name="`destinatarios[${idx}][telefone]`"
                                           x-model="dest.telefone"
                                           required
                                           placeholder="5511999999999"
                                           class="block w-full rounded-xl border border-surface-200 dark:border-surface-600 bg-white dark:bg-surface-800 px-3.5 py-2.5 text-sm text-surface-900 dark:text-white placeholder-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                </div>
                            </div>
                            <button type="button"
                                    @click="removeDestinatario(idx)"
                                    x-show="destinatarios.length > 1"
                                    class="flex-shrink-0 mt-5 p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <p class="mt-3 text-xs text-surface-400">
                    Informe o número com DDI+DDD sem espaços ou sinais. Ex: 5511987654321
                </p>
            </x-ui.card>

            <x-ui.card class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-surface-700 dark:text-surface-300">Regra ativa</p>
                        <p class="text-xs text-surface-400 mt-0.5">Desative para pausar os envios sem excluir a regra</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="ativo" value="0">
                        <input type="checkbox" name="ativo" value="1"
                               {{ old('ativo', $regra->ativo ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-surface-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-surface-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-surface-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-surface-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('whatsapp.regras.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">
                    {{ $isEdit ? 'Atualizar Regra' : 'Criar Regra' }}
                </x-ui.button>
            </div>
        </form>
    </div>

</x-layouts.app>
