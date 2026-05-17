<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\ContaBancaria;
use App\Models\ContaPagar;
use App\Models\DespesaRecorrente;
use App\Models\FormaPagamento;
use App\Models\Fornecedor;
use Illuminate\Http\Request;

class DespesaRecorrenteController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $recorrencias = DespesaRecorrente::where('empresa_id', $empresaId)
            ->with(['fornecedor', 'categoria', 'contaBancaria'])
            ->orderBy('descricao')
            ->get();

        return view('despesas-recorrentes.index', compact('recorrencias'));
    }

    public function create()
    {
        $empresaId = auth()->user()->empresa_id;
        return view('despesas-recorrentes.create', $this->formData($empresaId));
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
            'status_inicial'       => ['in:pendente,pago'],
            'criar_automaticamente'=> ['boolean'],
            'ajuste_fim_semana'    => ['in:manter,antecipar,postergar'],
            'fornecedor_id'        => ['nullable', 'exists:fornecedores,id'],
            'categoria_id'         => ['nullable', 'exists:categorias_financeiras,id'],
            'centro_custo_id'      => ['nullable', 'exists:centros_custo,id'],
            'forma_pagamento_id'   => ['nullable', 'exists:formas_pagamento,id'],
            'conta_bancaria_id'    => ['nullable', 'exists:contas_bancarias,id'],
            'observacoes'          => ['nullable', 'string'],
        ]);

        $rec = DespesaRecorrente::create(array_merge($data, [
            'empresa_id'    => $empresaId,
            'user_id'       => auth()->id(),
            'proxima_geracao' => $data['data_inicio'],
            'criar_automaticamente' => $request->boolean('criar_automaticamente', true),
        ]));

        return redirect()->route('despesas-recorrentes.index')
            ->with('success', 'Despesa recorrente criada.');
    }

    public function edit(DespesaRecorrente $despesasRecorrente)
    {
        $this->authorize($despesasRecorrente);
        $empresaId = auth()->user()->empresa_id;

        $contasGeradas = ContaPagar::where('despesa_recorrente_id', $despesasRecorrente->id)
            ->orderByDesc('data_vencimento')->limit(10)->get();

        return view('despesas-recorrentes.edit', array_merge(
            $this->formData($empresaId),
            ['recorrencia' => $despesasRecorrente, 'contasGeradas' => $contasGeradas]
        ));
    }

    public function update(Request $request, DespesaRecorrente $despesasRecorrente)
    {
        $this->authorize($despesasRecorrente);

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
            'status_inicial'       => ['in:pendente,pago'],
            'criar_automaticamente'=> ['boolean'],
            'ajuste_fim_semana'    => ['in:manter,antecipar,postergar'],
            'fornecedor_id'        => ['nullable', 'exists:fornecedores,id'],
            'categoria_id'         => ['nullable', 'exists:categorias_financeiras,id'],
            'centro_custo_id'      => ['nullable', 'exists:centros_custo,id'],
            'forma_pagamento_id'   => ['nullable', 'exists:formas_pagamento,id'],
            'conta_bancaria_id'    => ['nullable', 'exists:contas_bancarias,id'],
            'observacoes'          => ['nullable', 'string'],
        ]);

        $despesasRecorrente->update(array_merge($data, [
            'criar_automaticamente' => $request->boolean('criar_automaticamente', true),
        ]));

        return redirect()->route('despesas-recorrentes.index')
            ->with('success', 'Despesa recorrente atualizada.');
    }

    public function destroy(DespesaRecorrente $despesasRecorrente)
    {
        $this->authorize($despesasRecorrente);
        $despesasRecorrente->delete();
        return back()->with('success', 'Despesa recorrente removida.');
    }

    public function toggle(DespesaRecorrente $despesasRecorrente)
    {
        $this->authorize($despesasRecorrente);
        $despesasRecorrente->update(['ativo' => !$despesasRecorrente->ativo]);
        return back()->with('success', $despesasRecorrente->ativo ? 'Ativada.' : 'Desativada.');
    }

    public function gerarAgora(DespesaRecorrente $despesasRecorrente)
    {
        $this->authorize($despesasRecorrente);

        $conta = ContaPagar::create([
            'empresa_id'             => $despesasRecorrente->empresa_id,
            'user_id'                => auth()->id(),
            'fornecedor_id'          => $despesasRecorrente->fornecedor_id,
            'categoria_id'           => $despesasRecorrente->categoria_id,
            'centro_custo_id'        => $despesasRecorrente->centro_custo_id,
            'forma_pagamento_id'     => $despesasRecorrente->forma_pagamento_id,
            'conta_bancaria_id'      => $despesasRecorrente->conta_bancaria_id,
            'despesa_recorrente_id'  => $despesasRecorrente->id,
            'descricao'              => $despesasRecorrente->descricao,
            'valor_total'            => $despesasRecorrente->valor,
            'valor_pago'             => 0,
            'data_vencimento'        => $despesasRecorrente->proxima_geracao ?? today(),
            'status'                 => $despesasRecorrente->status_inicial,
            'observacoes'            => $despesasRecorrente->observacoes,
        ]);

        $proxima = $despesasRecorrente->calcularProximaGeracao();
        $despesasRecorrente->increment('ocorrencias_geradas');
        $despesasRecorrente->update(['proxima_geracao' => $proxima, 'ultima_geracao' => today()]);

        return back()->with('success', 'Conta a pagar gerada: #' . $conta->id);
    }

    private function formData(int $empresaId): array
    {
        return [
            'fornecedores' => Fornecedor::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome_razao_social')->get(),
            'categorias'   => CategoriaFinanceira::where(fn($q) => $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id'))->orderBy('nome')->get(),
            'centros'      => CentroCusto::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get(),
            'formas'       => FormaPagamento::where(fn($q) => $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id'))->where('ativo', true)->orderBy('nome')->get(),
            'contas'       => ContaBancaria::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get(),
        ];
    }

    private function authorize(DespesaRecorrente $rec): void
    {
        abort_if($rec->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
