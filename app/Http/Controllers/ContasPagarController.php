<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\ContaBancaria;
use App\Models\ContaPagar;
use App\Models\FormaPagamento;
use App\Models\Fornecedor;
use Illuminate\Http\Request;

class ContasPagarController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $contas = ContaPagar::where('empresa_id', $empresaId)
            ->with(['fornecedor', 'categoria'])
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

        return view('contas-pagar.index', compact('contas'));
    }

    public function create()
    {
        $empresaId = auth()->user()->empresa_id;
        $fornecedores = Fornecedor::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome_razao_social')->get();
        $categorias   = CategoriaFinanceira::where('empresa_id', $empresaId)->where('tipo', '!=', 'receita')->orderBy('nome')->get();
        $centrosCusto = CentroCusto::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get();
        $formas       = FormaPagamento::where(fn($q) => $q->whereNull('empresa_id')->orWhere('empresa_id', $empresaId))->where('ativo', true)->orderBy('nome')->get();
        $contas       = ContaBancaria::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get();

        return view('contas-pagar.create', compact('fornecedores', 'categorias', 'centrosCusto', 'formas', 'contas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fornecedor_id'      => 'nullable|exists:fornecedores,id',
            'categoria_id'       => 'nullable|exists:categorias_financeiras,id',
            'centro_custo_id'    => 'nullable|exists:centros_custo,id',
            'forma_pagamento_id' => 'nullable|exists:formas_pagamento,id',
            'conta_bancaria_id'  => 'nullable|exists:contas_bancarias,id',
            'numero_documento'   => 'nullable|string|max:100',
            'descricao'          => 'required|string|max:255',
            'valor_total'        => 'required|numeric|min:0.01',
            'data_vencimento'    => 'required|date',
            'data_competencia'   => 'nullable|date',
            'observacoes'        => 'nullable|string',
        ]);

        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['user_id']    = auth()->id();
        $data['status']     = 'pendente';
        $data['valor_pago'] = 0;

        ContaPagar::create($data);

        return redirect()->route('contas-pagar.index')->with('success', 'Conta a pagar criada com sucesso.');
    }

    public function edit(ContaPagar $contaPagar)
    {
        $this->authorizeEmpresa($contaPagar);
        $empresaId = auth()->user()->empresa_id;

        $fornecedores = Fornecedor::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome_razao_social')->get();
        $categorias   = CategoriaFinanceira::where('empresa_id', $empresaId)->where('tipo', '!=', 'receita')->orderBy('nome')->get();
        $centrosCusto = CentroCusto::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get();
        $formas       = FormaPagamento::where(fn($q) => $q->whereNull('empresa_id')->orWhere('empresa_id', $empresaId))->where('ativo', true)->orderBy('nome')->get();
        $contas       = ContaBancaria::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get();

        return view('contas-pagar.edit', compact('contaPagar', 'fornecedores', 'categorias', 'centrosCusto', 'formas', 'contas'));
    }

    public function update(Request $request, ContaPagar $contaPagar)
    {
        $this->authorizeEmpresa($contaPagar);

        $data = $request->validate([
            'fornecedor_id'      => 'nullable|exists:fornecedores,id',
            'categoria_id'       => 'nullable|exists:categorias_financeiras,id',
            'centro_custo_id'    => 'nullable|exists:centros_custo,id',
            'forma_pagamento_id' => 'nullable|exists:formas_pagamento,id',
            'conta_bancaria_id'  => 'nullable|exists:contas_bancarias,id',
            'numero_documento'   => 'nullable|string|max:100',
            'descricao'          => 'required|string|max:255',
            'valor_total'        => 'required|numeric|min:0.01',
            'data_vencimento'    => 'required|date',
            'data_competencia'   => 'nullable|date',
            'observacoes'        => 'nullable|string',
        ]);

        $contaPagar->update($data);

        return redirect()->route('contas-pagar.index')->with('success', 'Conta a pagar atualizada.');
    }

    public function destroy(ContaPagar $contaPagar)
    {
        $this->authorizeEmpresa($contaPagar);
        $contaPagar->delete();

        return redirect()->route('contas-pagar.index')->with('success', 'Conta a pagar removida.');
    }

    public function baixar(Request $request, ContaPagar $contaPagar)
    {
        $this->authorizeEmpresa($contaPagar);

        $data = $request->validate([
            'data_pagamento'     => 'required|date',
            'valor_pago'         => 'required|numeric|min:0.01',
            'desconto'           => 'nullable|numeric|min:0',
            'juros'              => 'nullable|numeric|min:0',
            'multa'              => 'nullable|numeric|min:0',
            'forma_pagamento_id' => 'nullable|exists:formas_pagamento,id',
            'conta_bancaria_id'  => 'nullable|exists:contas_bancarias,id',
            'observacoes'        => 'nullable|string',
        ]);

        $valorPago   = $data['valor_pago'];
        $valorAberto = $contaPagar->valor_total - ($data['desconto'] ?? 0);

        $status = $valorPago >= $valorAberto ? 'pago' : 'parcial';

        $contaPagar->update([
            'data_pagamento'     => $data['data_pagamento'],
            'valor_pago'         => $valorPago,
            'desconto'           => $data['desconto'] ?? 0,
            'juros'              => $data['juros'] ?? 0,
            'multa'              => $data['multa'] ?? 0,
            'forma_pagamento_id' => $data['forma_pagamento_id'] ?? $contaPagar->forma_pagamento_id,
            'conta_bancaria_id'  => $data['conta_bancaria_id'] ?? $contaPagar->conta_bancaria_id,
            'observacoes'        => $data['observacoes'] ?? $contaPagar->observacoes,
            'status'             => $status,
        ]);

        return redirect()->route('contas-pagar.index')->with('success', 'Baixa realizada com sucesso.');
    }

    public function baixaMassa(Request $request)
    {
        $request->validate([
            'ids'                => 'required|array',
            'ids.*'              => 'exists:contas_pagar,id',
            'data_pagamento'     => 'required|date',
            'forma_pagamento_id' => 'nullable|exists:formas_pagamento,id',
            'conta_bancaria_id'  => 'nullable|exists:contas_bancarias,id',
        ]);

        $empresaId = auth()->user()->empresa_id;

        ContaPagar::whereIn('id', $request->ids)
            ->where('empresa_id', $empresaId)
            ->whereIn('status', ['pendente', 'parcial', 'vencido'])
            ->get()
            ->each(function ($conta) use ($request) {
                $conta->update([
                    'data_pagamento'     => $request->data_pagamento,
                    'valor_pago'         => $conta->valor_total,
                    'status'             => 'pago',
                    'forma_pagamento_id' => $request->forma_pagamento_id ?? $conta->forma_pagamento_id,
                    'conta_bancaria_id'  => $request->conta_bancaria_id ?? $conta->conta_bancaria_id,
                ]);
            });

        return redirect()->route('contas-pagar.index')->with('success', 'Baixa em massa realizada.');
    }

    public function show(ContaPagar $contaPagar)
    {
        $this->authorizeEmpresa($contaPagar);
        $contaPagar->load(['fornecedor', 'categoria', 'centroCusto', 'formaPagamento', 'contaBancaria', 'user']);

        return view('contas-pagar.show', compact('contaPagar'));
    }

    public function cancelarBaixa(ContaPagar $contaPagar)
    {
        $this->authorizeEmpresa($contaPagar);

        $contaPagar->update([
            'status'          => 'pendente',
            'data_pagamento'  => null,
            'valor_pago'      => 0,
            'desconto'        => 0,
            'juros'           => 0,
            'multa'           => 0,
        ]);

        return redirect()->back()->with('success', 'Baixa cancelada com sucesso.');
    }

    public function deletados()
    {
        $empresaId = auth()->user()->empresa_id;

        $contas = ContaPagar::onlyTrashed()
            ->where('empresa_id', $empresaId)
            ->with(['fornecedor', 'categoria'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(20);

        return view('contas-pagar.deletados', compact('contas'));
    }

    public function restore(int $id)
    {
        $conta = ContaPagar::onlyTrashed()->findOrFail($id);
        abort_if($conta->empresa_id !== auth()->user()->empresa_id, 403);

        $conta->restore();

        return redirect()->route('contas-pagar.deletados')->with('success', 'Conta restaurada com sucesso.');
    }

    private function authorizeEmpresa(ContaPagar $conta): void
    {
        abort_if($conta->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
