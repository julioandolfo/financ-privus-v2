<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\ContaBancaria;
use App\Models\ContaReceber;
use App\Models\FormaPagamento;
use Illuminate\Http\Request;

class ContasReceberController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $contas = ContaReceber::where('empresa_id', $empresaId)
            ->with(['cliente', 'categoria'])
            ->when($request->search, fn($q) =>
                $q->where('descricao', 'like', "%{$request->search}%")
                  ->orWhere('numero_documento', 'like', "%{$request->search}%")
            )
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->vencimento_de, fn($q) => $q->whereDate('data_vencimento', '>=', $request->vencimento_de))
            ->when($request->vencimento_ate, fn($q) => $q->whereDate('data_vencimento', '<=', $request->vencimento_ate))
            ->orderBy('data_vencimento')
            ->paginate(20)
            ->withQueryString();

        return view('contas-receber.index', compact('contas'));
    }

    public function create()
    {
        $empresaId = auth()->user()->empresa_id;
        $clientes     = Cliente::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome_razao_social')->get();
        $categorias   = CategoriaFinanceira::where('empresa_id', $empresaId)->where('tipo', '!=', 'despesa')->orderBy('nome')->get();
        $centrosCusto = CentroCusto::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get();
        $formas       = FormaPagamento::where(fn($q) => $q->whereNull('empresa_id')->orWhere('empresa_id', $empresaId))->where('ativo', true)->orderBy('nome')->get();
        $contas       = ContaBancaria::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get();

        return view('contas-receber.create', compact('clientes', 'categorias', 'centrosCusto', 'formas', 'contas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'          => 'nullable|exists:clientes,id',
            'categoria_id'        => 'nullable|exists:categorias_financeiras,id',
            'centro_custo_id'     => 'nullable|exists:centros_custo,id',
            'forma_recebimento_id'=> 'nullable|exists:formas_pagamento,id',
            'conta_bancaria_id'   => 'nullable|exists:contas_bancarias,id',
            'numero_documento'    => 'nullable|string|max:100',
            'descricao'           => 'required|string|max:255',
            'valor_total'         => 'required|numeric|min:0.01',
            'data_vencimento'     => 'required|date',
            'data_competencia'    => 'nullable|date',
            'observacoes'         => 'nullable|string',
        ]);

        $data['empresa_id']     = auth()->user()->empresa_id;
        $data['user_id']        = auth()->id();
        $data['status']         = 'pendente';
        $data['valor_recebido'] = 0;

        ContaReceber::create($data);

        return redirect()->route('contas-receber.index')->with('success', 'Conta a receber criada com sucesso.');
    }

    public function edit(ContaReceber $contaReceber)
    {
        $this->authorizeEmpresa($contaReceber);
        $empresaId = auth()->user()->empresa_id;

        $clientes     = Cliente::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome_razao_social')->get();
        $categorias   = CategoriaFinanceira::where('empresa_id', $empresaId)->where('tipo', '!=', 'despesa')->orderBy('nome')->get();
        $centrosCusto = CentroCusto::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get();
        $formas       = FormaPagamento::where(fn($q) => $q->whereNull('empresa_id')->orWhere('empresa_id', $empresaId))->where('ativo', true)->orderBy('nome')->get();
        $contas       = ContaBancaria::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get();

        return view('contas-receber.edit', compact('contaReceber', 'clientes', 'categorias', 'centrosCusto', 'formas', 'contas'));
    }

    public function update(Request $request, ContaReceber $contaReceber)
    {
        $this->authorizeEmpresa($contaReceber);

        $data = $request->validate([
            'cliente_id'          => 'nullable|exists:clientes,id',
            'categoria_id'        => 'nullable|exists:categorias_financeiras,id',
            'centro_custo_id'     => 'nullable|exists:centros_custo,id',
            'forma_recebimento_id'=> 'nullable|exists:formas_pagamento,id',
            'conta_bancaria_id'   => 'nullable|exists:contas_bancarias,id',
            'numero_documento'    => 'nullable|string|max:100',
            'descricao'           => 'required|string|max:255',
            'valor_total'         => 'required|numeric|min:0.01',
            'data_vencimento'     => 'required|date',
            'data_competencia'    => 'nullable|date',
            'observacoes'         => 'nullable|string',
        ]);

        $contaReceber->update($data);

        return redirect()->route('contas-receber.index')->with('success', 'Conta a receber atualizada.');
    }

    public function destroy(ContaReceber $contaReceber)
    {
        $this->authorizeEmpresa($contaReceber);
        $contaReceber->delete();

        return redirect()->route('contas-receber.index')->with('success', 'Conta a receber removida.');
    }

    public function baixar(Request $request, ContaReceber $contaReceber)
    {
        $this->authorizeEmpresa($contaReceber);

        $data = $request->validate([
            'data_recebimento'    => 'required|date',
            'valor_recebido'      => 'required|numeric|min:0.01',
            'desconto'            => 'nullable|numeric|min:0',
            'juros'               => 'nullable|numeric|min:0',
            'multa'               => 'nullable|numeric|min:0',
            'forma_recebimento_id'=> 'nullable|exists:formas_pagamento,id',
            'conta_bancaria_id'   => 'nullable|exists:contas_bancarias,id',
            'observacoes'         => 'nullable|string',
        ]);

        $valorRecebido = $data['valor_recebido'];
        $valorAberto   = $contaReceber->valor_total - ($data['desconto'] ?? 0);
        $status        = $valorRecebido >= $valorAberto ? 'pago' : 'parcial';

        $contaReceber->update([
            'data_recebimento'    => $data['data_recebimento'],
            'valor_recebido'      => $valorRecebido,
            'desconto'            => $data['desconto'] ?? 0,
            'juros'               => $data['juros'] ?? 0,
            'multa'               => $data['multa'] ?? 0,
            'forma_recebimento_id'=> $data['forma_recebimento_id'] ?? $contaReceber->forma_recebimento_id,
            'conta_bancaria_id'   => $data['conta_bancaria_id'] ?? $contaReceber->conta_bancaria_id,
            'observacoes'         => $data['observacoes'] ?? $contaReceber->observacoes,
            'status'              => $status,
        ]);

        return redirect()->route('contas-receber.index')->with('success', 'Baixa realizada com sucesso.');
    }

    public function baixaMassa(Request $request)
    {
        $request->validate([
            'ids'                 => 'required|array',
            'ids.*'               => 'exists:contas_receber,id',
            'data_recebimento'    => 'required|date',
            'forma_recebimento_id'=> 'nullable|exists:formas_pagamento,id',
            'conta_bancaria_id'   => 'nullable|exists:contas_bancarias,id',
        ]);

        $empresaId = auth()->user()->empresa_id;

        ContaReceber::whereIn('id', $request->ids)
            ->where('empresa_id', $empresaId)
            ->whereIn('status', ['pendente', 'parcial', 'vencido'])
            ->get()
            ->each(function ($conta) use ($request) {
                $conta->update([
                    'data_recebimento'    => $request->data_recebimento,
                    'valor_recebido'      => $conta->valor_total,
                    'status'              => 'pago',
                    'forma_recebimento_id'=> $request->forma_recebimento_id ?? $conta->forma_recebimento_id,
                    'conta_bancaria_id'   => $request->conta_bancaria_id ?? $conta->conta_bancaria_id,
                ]);
            });

        return redirect()->route('contas-receber.index')->with('success', 'Baixa em massa realizada.');
    }

    public function show(ContaReceber $contaReceber)
    {
        $this->authorizeEmpresa($contaReceber);
        $contaReceber->load(['cliente', 'categoria', 'centroCusto', 'formaRecebimento', 'contaBancaria', 'user']);

        return view('contas-receber.show', compact('contaReceber'));
    }

    public function cancelarBaixa(ContaReceber $contaReceber)
    {
        $this->authorizeEmpresa($contaReceber);

        $contaReceber->update([
            'status'           => 'pendente',
            'data_recebimento' => null,
            'valor_recebido'   => 0,
            'desconto'         => 0,
            'juros'            => 0,
            'multa'            => 0,
        ]);

        return redirect()->back()->with('success', 'Baixa cancelada com sucesso.');
    }

    public function deletados()
    {
        $empresaId = auth()->user()->empresa_id;

        $contas = ContaReceber::onlyTrashed()
            ->where('empresa_id', $empresaId)
            ->with(['cliente', 'categoria'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(20);

        return view('contas-receber.deletados', compact('contas'));
    }

    public function restore(int $id)
    {
        $conta = ContaReceber::onlyTrashed()->findOrFail($id);
        abort_if($conta->empresa_id !== auth()->user()->empresa_id, 403);

        $conta->restore();

        return redirect()->route('contas-receber.deletados')->with('success', 'Conta restaurada com sucesso.');
    }

    private function authorizeEmpresa(ContaReceber $conta): void
    {
        abort_if($conta->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
