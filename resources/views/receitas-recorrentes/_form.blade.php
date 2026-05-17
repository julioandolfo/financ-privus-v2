@php $m = $model ?? null; @endphp

<x-ui.card class="mb-4">
    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Dados da Receita</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <x-ui.input name="descricao" label="Descrição" required value="{{ old('descricao', $m?->descricao) }}" :error="$errors->first('descricao')" />
        </div>
        <x-ui.input name="valor" type="number" step="0.01" min="0.01" label="Valor" required value="{{ old('valor', $m ? number_format($m->valor, 2, '.', '') : '') }}" :error="$errors->first('valor')" />
        <x-ui.select name="cliente_id" label="Cliente">
            <option value="">— Opcional —</option>
            @foreach($clientes as $c)
            <option value="{{ $c->id }}" @selected(old('cliente_id', $m?->cliente_id) == $c->id)>{{ $c->nome_razao_social }}</option>
            @endforeach
        </x-ui.select>
    </div>
</x-ui.card>

<x-ui.card class="mb-4">
    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Frequência</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-ui.select name="frequencia" label="Frequência" required x-model="frequencia">
            @foreach(['diaria'=>'Diária','semanal'=>'Semanal','quinzenal'=>'Quinzenal','mensal'=>'Mensal','bimestral'=>'Bimestral','trimestral'=>'Trimestral','semestral'=>'Semestral','anual'=>'Anual','personalizado'=>'Personalizado'] as $v => $l)
            <option value="{{ $v }}" @selected(old('frequencia', $m?->frequencia ?? 'mensal') === $v)>{{ $l }}</option>
            @endforeach
        </x-ui.select>
        <div x-show="['mensal','bimestral','trimestral','semestral','anual'].includes(frequencia)">
            <x-ui.input name="dia_mes" type="number" min="1" max="31" label="Dia do mês" hint="0 = último dia" value="{{ old('dia_mes', $m?->dia_mes) }}" />
        </div>
        <div x-show="frequencia === 'personalizado'">
            <x-ui.input name="intervalo_dias" type="number" min="1" label="Intervalo em dias" value="{{ old('intervalo_dias', $m?->intervalo_dias) }}" />
        </div>
        <x-ui.input name="data_inicio" type="date" label="Data de Início" required value="{{ old('data_inicio', $m?->data_inicio?->format('Y-m-d')) }}" :error="$errors->first('data_inicio')" />
        <x-ui.input name="data_fim" type="date" label="Data de Encerramento" hint="Opcional" value="{{ old('data_fim', $m?->data_fim?->format('Y-m-d')) }}" />
        <x-ui.input name="max_ocorrencias" type="number" min="1" label="Máx. Ocorrências" hint="Opcional" value="{{ old('max_ocorrencias', $m?->max_ocorrencias) }}" />
        <x-ui.input name="antecedencia_dias" type="number" min="0" max="30" label="Antecedência (dias)" value="{{ old('antecedencia_dias', $m?->antecedencia_dias ?? 5) }}" />
        <x-ui.select name="ajuste_fim_semana" label="Ajuste Fim de Semana">
            <option value="manter"    @selected(old('ajuste_fim_semana', $m?->ajuste_fim_semana ?? 'manter') === 'manter')>Manter</option>
            <option value="antecipar" @selected(old('ajuste_fim_semana', $m?->ajuste_fim_semana) === 'antecipar')>Antecipar para sexta</option>
            <option value="postergar" @selected(old('ajuste_fim_semana', $m?->ajuste_fim_semana) === 'postergar')>Postergar para segunda</option>
        </x-ui.select>
        <x-ui.select name="status_inicial" label="Status ao Gerar">
            <option value="pendente"  @selected(old('status_inicial', $m?->status_inicial ?? 'pendente') === 'pendente')>Pendente</option>
            <option value="recebido"  @selected(old('status_inicial', $m?->status_inicial) === 'recebido')>Recebido automaticamente</option>
        </x-ui.select>
    </div>
    <div class="mt-4 flex items-center gap-3">
        <label class="flex items-center gap-2 cursor-pointer text-sm text-surface-700 dark:text-surface-300">
            <input type="checkbox" name="criar_automaticamente" value="1"
                @checked(old('criar_automaticamente', $m?->criar_automaticamente ?? true))
                class="rounded border-surface-300 dark:border-surface-600 text-primary-600">
            Gerar automaticamente (via agendamento)
        </label>
    </div>
</x-ui.card>

<x-ui.card class="mb-4">
    <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Classificação</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-ui.select name="categoria_id" label="Categoria">
            <option value="">— Selecione —</option>
            @foreach($categorias as $c)
            <option value="{{ $c->id }}" @selected(old('categoria_id', $m?->categoria_id) == $c->id)>{{ $c->nome }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="centro_custo_id" label="Centro de Custo">
            <option value="">— Selecione —</option>
            @foreach($centros as $cc)
            <option value="{{ $cc->id }}" @selected(old('centro_custo_id', $m?->centro_custo_id) == $cc->id)>{{ $cc->nome }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="forma_pagamento_id" label="Forma de Recebimento">
            <option value="">— Selecione —</option>
            @foreach($formas as $fp)
            <option value="{{ $fp->id }}" @selected(old('forma_pagamento_id', $m?->forma_pagamento_id) == $fp->id)>{{ $fp->nome }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="conta_bancaria_id" label="Conta Bancária">
            <option value="">— Selecione —</option>
            @foreach($contas as $c)
            <option value="{{ $c->id }}" @selected(old('conta_bancaria_id', $m?->conta_bancaria_id) == $c->id)>{{ $c->nome }}</option>
            @endforeach
        </x-ui.select>
    </div>
</x-ui.card>

<x-ui.card>
    <x-ui.textarea name="observacoes" label="Observações" rows="2">{{ old('observacoes', $m?->observacoes) }}</x-ui.textarea>
</x-ui.card>
