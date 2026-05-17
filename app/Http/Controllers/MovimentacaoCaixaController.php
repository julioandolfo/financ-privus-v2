<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\ContaBancaria;
use App\Models\FormaPagamento;
use App\Models\MovimentacaoCaixa;
use Illuminate\Http\Request;

class MovimentacaoCaixaController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $movimentacoes = MovimentacaoCaixa::where('empresa_id', $empresaId)
            ->with(['contaBancaria', 'categoria'])
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->conta_bancaria_id, fn($q) => $q->where('conta_bancaria_id', $request->conta_bancaria_id))
            ->when($request->de, fn($q) => $q->whereDate('data_movimentacao', '>=', $request->de))
            ->when($request->ate, fn($q) => $q->whereDate('data_movimentacao', '<=', $request->ate))
            ->when($request->search, fn($q) => $q->where('descricao', 'like', "%{$request->search}%"))
            ->orderByDesc('data_movimentacao')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $contas = ContaBancaria::where('empresa_id', $empresaId)->ativas()->orderBy('nome')->get();

        // Totais do período filtrado (sem paginação)
        $baseQuery = MovimentacaoCaixa::where('empresa_id', $empresaId)
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->conta_bancaria_id, fn($q) => $q->where('conta_bancaria_id', $request->conta_bancaria_id))
            ->when($request->de, fn($q) => $q->whereDate('data_movimentacao', '>=', $request->de))
            ->when($request->ate, fn($q) => $q->whereDate('data_movimentacao', '<=', $request->ate))
            ->when($request->search, fn($q) => $q->where('descricao', 'like', "%{$request->search}%"));

        $totalEntradas = (clone $baseQuery)->where('tipo', 'entrada')->sum('valor');
        $totalSaidas   = (clone $baseQuery)->where('tipo', 'saida')->sum('valor');

        return view('movimentacoes.index', compact('movimentacoes', 'contas', 'totalEntradas', 'totalSaidas'));
    }

    public function create()
    {
        $empresaId = auth()->user()->empresa_id;
        $contas     = ContaBancaria::where('empresa_id', $empresaId)->ativas()->orderBy('nome')->get();
        $categorias = CategoriaFinanceira::where('empresa_id', $empresaId)->ativas()->orderBy('nome')->get();
        $centros    = CentroCusto::where('empresa_id', $empresaId)->ativos()->orderBy('nome')->get();
        $formas     = FormaPagamento::where(fn($q) => $q->whereNull('empresa_id')->orWhere('empresa_id', $empresaId))->ativas()->orderBy('nome')->get();

        return view('movimentacoes.create', compact('contas', 'categorias', 'centros', 'formas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo'              => 'required|in:entrada,saida',
            'descricao'         => 'required|string|max:255',
            'valor'             => 'required|numeric|min:0.01',
            'data_movimentacao' => 'required|date',
            'data_competencia'  => 'nullable|date',
            'conta_bancaria_id' => 'required|exists:contas_bancarias,id',
            'categoria_id'      => 'nullable|exists:categorias_financeiras,id',
            'centro_custo_id'   => 'nullable|exists:centros_custo,id',
            'forma_pagamento_id'=> 'nullable|exists:formas_pagamento,id',
            'observacoes'       => 'nullable|string',
        ]);

        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['user_id']    = auth()->id();

        $mov = MovimentacaoCaixa::create($data);

        // Atualiza saldo da conta bancária
        $this->atualizarSaldo($mov);

        return redirect()->route('movimentacoes.index')->with('success', 'Movimentação registrada com sucesso.');
    }

    public function edit(MovimentacaoCaixa $movimentacao)
    {
        $this->authorizeEmpresa($movimentacao);
        $empresaId = auth()->user()->empresa_id;

        $contas     = ContaBancaria::where('empresa_id', $empresaId)->ativas()->orderBy('nome')->get();
        $categorias = CategoriaFinanceira::where('empresa_id', $empresaId)->ativas()->orderBy('nome')->get();
        $centros    = CentroCusto::where('empresa_id', $empresaId)->ativos()->orderBy('nome')->get();
        $formas     = FormaPagamento::where(fn($q) => $q->whereNull('empresa_id')->orWhere('empresa_id', $empresaId))->ativas()->orderBy('nome')->get();

        return view('movimentacoes.edit', compact('movimentacao', 'contas', 'categorias', 'centros', 'formas'));
    }

    public function update(Request $request, MovimentacaoCaixa $movimentacao)
    {
        $this->authorizeEmpresa($movimentacao);

        $data = $request->validate([
            'tipo'              => 'required|in:entrada,saida',
            'descricao'         => 'required|string|max:255',
            'valor'             => 'required|numeric|min:0.01',
            'data_movimentacao' => 'required|date',
            'data_competencia'  => 'nullable|date',
            'conta_bancaria_id' => 'required|exists:contas_bancarias,id',
            'categoria_id'      => 'nullable|exists:categorias_financeiras,id',
            'centro_custo_id'   => 'nullable|exists:centros_custo,id',
            'forma_pagamento_id'=> 'nullable|exists:formas_pagamento,id',
            'observacoes'       => 'nullable|string',
        ]);

        // Reverte saldo antigo antes de atualizar
        $this->reverterSaldo($movimentacao);

        $movimentacao->update($data);

        // Aplica novo saldo
        $this->atualizarSaldo($movimentacao->fresh());

        return redirect()->route('movimentacoes.index')->with('success', 'Movimentação atualizada.');
    }

    public function destroy(MovimentacaoCaixa $movimentacao)
    {
        $this->authorizeEmpresa($movimentacao);

        $this->reverterSaldo($movimentacao);
        $movimentacao->delete();

        return redirect()->route('movimentacoes.index')->with('success', 'Movimentação removida.');
    }

    private function atualizarSaldo(MovimentacaoCaixa $mov): void
    {
        if (!$mov->conta_bancaria_id) return;

        $delta = $mov->tipo === 'entrada' ? $mov->valor : -$mov->valor;
        $mov->contaBancaria()->increment('saldo_atual', $delta);
    }

    private function reverterSaldo(MovimentacaoCaixa $mov): void
    {
        if (!$mov->conta_bancaria_id) return;

        $delta = $mov->tipo === 'entrada' ? -$mov->valor : $mov->valor;
        $mov->contaBancaria()->increment('saldo_atual', $delta);
    }

    private function authorizeEmpresa(MovimentacaoCaixa $m): void
    {
        abort_if($m->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
