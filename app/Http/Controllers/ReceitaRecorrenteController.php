<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\ContaBancaria;
use App\Models\ContaReceber;
use App\Models\FormaPagamento;
use App\Models\ReceitaRecorrente;
use Illuminate\Http\Request;

class ReceitaRecorrenteController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $recorrencias = ReceitaRecorrente::where('empresa_id', $empresaId)
            ->with(['cliente', 'categoria', 'contaBancaria'])
            ->orderBy('descricao')
            ->get();

        return view('receitas-recorrentes.index', compact('recorrencias'));
    }

    public function create()
    {
        $empresaId = auth()->user()->empresa_id;
        return view('receitas-recorrentes.create', $this->formData($empresaId));
    }

    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'descricao'            => ['required', 'string', 'max:255'],
            'valor'                => ['required', 'numeric', 'min:0.01'],
            'frequencia'           => ['required', 'in:diaria,semanal,quinzenal,mensal,bimestral,trimestral,semestral,anual,personalizado'],
            'dia_mes'              => ['nullable', 'integer', 'min:1', 'max:31'],
            'intervalo_dias'       => ['nullable', 'integer', 'min:1'],
            'data_inicio'          => ['required', 'date'],
            'data_fim'             => ['nullable', 'date', 'after:data_inicio'],
            'max_ocorrencias'      => ['nullable', 'integer', 'min:1'],
            'antecedencia_dias'    => ['integer', 'min:0', 'max:30'],
            'status_inicial'       => ['in:pendente,recebido'],
            'criar_automaticamente'=> ['boolean'],
            'ajuste_fim_semana'    => ['in:manter,antecipar,postergar'],
            'cliente_id'           => ['nullable', 'exists:clientes,id'],
            'categoria_id'         => ['nullable', 'exists:categorias_financeiras,id'],
            'centro_custo_id'      => ['nullable', 'exists:centros_custo,id'],
            'forma_pagamento_id'   => ['nullable', 'exists:formas_pagamento,id'],
            'conta_bancaria_id'    => ['nullable', 'exists:contas_bancarias,id'],
            'observacoes'          => ['nullable', 'string'],
        ]);

        ReceitaRecorrente::create(array_merge($data, [
            'empresa_id'    => $empresaId,
            'user_id'       => auth()->id(),
            'proxima_geracao' => $data['data_inicio'],
            'criar_automaticamente' => $request->boolean('criar_automaticamente', true),
        ]));

        return redirect()->route('receitas-recorrentes.index')
            ->with('success', 'Receita recorrente criada.');
    }

    public function edit(ReceitaRecorrente $receitasRecorrente)
    {
        $this->authorize($receitasRecorrente);
        $empresaId = auth()->user()->empresa_id;

        $contasGeradas = ContaReceber::where('receita_recorrente_id', $receitasRecorrente->id)
            ->orderByDesc('data_vencimento')->limit(10)->get();

        return view('receitas-recorrentes.edit', array_merge(
            $this->formData($empresaId),
            ['recorrencia' => $receitasRecorrente, 'contasGeradas' => $contasGeradas]
        ));
    }

    public function update(Request $request, ReceitaRecorrente $receitasRecorrente)
    {
        $this->authorize($receitasRecorrente);

        $data = $request->validate([
            'descricao'            => ['required', 'string', 'max:255'],
            'valor'                => ['required', 'numeric', 'min:0.01'],
            'frequencia'           => ['required', 'in:diaria,semanal,quinzenal,mensal,bimestral,trimestral,semestral,anual,personalizado'],
            'dia_mes'              => ['nullable', 'integer', 'min:1', 'max:31'],
            'intervalo_dias'       => ['nullable', 'integer', 'min:1'],
            'data_inicio'          => ['required', 'date'],
            'data_fim'             => ['nullable', 'date'],
            'max_ocorrencias'      => ['nullable', 'integer', 'min:1'],
            'antecedencia_dias'    => ['integer', 'min:0', 'max:30'],
            'status_inicial'       => ['in:pendente,recebido'],
            'criar_automaticamente'=> ['boolean'],
            'ajuste_fim_semana'    => ['in:manter,antecipar,postergar'],
            'cliente_id'           => ['nullable', 'exists:clientes,id'],
            'categoria_id'         => ['nullable', 'exists:categorias_financeiras,id'],
            'centro_custo_id'      => ['nullable', 'exists:centros_custo,id'],
            'forma_pagamento_id'   => ['nullable', 'exists:formas_pagamento,id'],
            'conta_bancaria_id'    => ['nullable', 'exists:contas_bancarias,id'],
            'observacoes'          => ['nullable', 'string'],
        ]);

        $receitasRecorrente->update(array_merge($data, [
            'criar_automaticamente' => $request->boolean('criar_automaticamente', true),
        ]));

        return redirect()->route('receitas-recorrentes.index')
            ->with('success', 'Receita recorrente atualizada.');
    }

    public function destroy(ReceitaRecorrente $receitasRecorrente)
    {
        $this->authorize($receitasRecorrente);
        $receitasRecorrente->delete();
        return back()->with('success', 'Receita recorrente removida.');
    }

    public function toggle(ReceitaRecorrente $receitasRecorrente)
    {
        $this->authorize($receitasRecorrente);
        $receitasRecorrente->update(['ativo' => !$receitasRecorrente->ativo]);
        return back();
    }

    public function gerarAgora(ReceitaRecorrente $receitasRecorrente)
    {
        $this->authorize($receitasRecorrente);

        $conta = ContaReceber::create([
            'empresa_id'            => $receitasRecorrente->empresa_id,
            'user_id'               => auth()->id(),
            'cliente_id'            => $receitasRecorrente->cliente_id,
            'categoria_id'          => $receitasRecorrente->categoria_id,
            'centro_custo_id'       => $receitasRecorrente->centro_custo_id,
            'forma_recebimento_id'  => $receitasRecorrente->forma_pagamento_id,
            'conta_bancaria_id'     => $receitasRecorrente->conta_bancaria_id,
            'receita_recorrente_id' => $receitasRecorrente->id,
            'descricao'             => $receitasRecorrente->descricao,
            'valor_total'           => $receitasRecorrente->valor,
            'valor_recebido'        => 0,
            'data_vencimento'       => $receitasRecorrente->proxima_geracao ?? today(),
            'status'                => $receitasRecorrente->status_inicial,
            'observacoes'           => $receitasRecorrente->observacoes,
        ]);

        $proxima = $receitasRecorrente->calcularProximaGeracao();
        $receitasRecorrente->increment('ocorrencias_geradas');
        $receitasRecorrente->update(['proxima_geracao' => $proxima, 'ultima_geracao' => today()]);

        return back()->with('success', 'Conta a receber gerada: #' . $conta->id);
    }

    private function formData(int $empresaId): array
    {
        return [
            'clientes'   => Cliente::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome_razao_social')->get(),
            'categorias' => CategoriaFinanceira::where(fn($q) => $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id'))->orderBy('nome')->get(),
            'centros'    => CentroCusto::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get(),
            'formas'     => FormaPagamento::where(fn($q) => $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id'))->where('ativo', true)->orderBy('nome')->get(),
            'contas'     => ContaBancaria::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get(),
        ];
    }

    private function authorize(ReceitaRecorrente $rec): void
    {
        abort_if($rec->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
