<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\CategoriaProduto;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class EstoqueController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $produtos = Produto::where('empresa_id', $empresaId)
            ->where('tipo', 'produto')
            ->when($request->filled('categoria_id'), fn($q) => $q->where('categoria_id', $request->categoria_id))
            ->when($request->filled('estoque_baixo'), fn($q) => $q->whereColumn('estoque', '<=', 'estoque_minimo'))
            ->with('categoria')
            ->orderBy('nome')
            ->get();

        $categorias = CategoriaProduto::where('empresa_id', $empresaId)->orderBy('nome')->get();

        $totalProdutos    = $produtos->count();
        $estoqueBaixo     = $produtos->filter(fn($p) => $p->estoque <= $p->estoque_minimo)->count();
        $valorEstoque     = $produtos->sum(fn($p) => ($p->estoque ?? 0) * ($p->custo_unitario ?? 0));
        $valorVendaTotal  = $produtos->sum(fn($p) => ($p->estoque ?? 0) * ($p->preco_venda ?? 0));

        return view('relatorios.estoque', compact(
            'produtos','categorias',
            'totalProdutos','estoqueBaixo','valorEstoque','valorVendaTotal'
        ));
    }

    public function pdf(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $produtos = Produto::where('empresa_id', $empresaId)->where('tipo','produto')->with('categoria')->orderBy('nome')->get();

        $valorEstoque    = $produtos->sum(fn($p) => ($p->estoque ?? 0) * ($p->custo_unitario ?? 0));
        $valorVendaTotal = $produtos->sum(fn($p) => ($p->estoque ?? 0) * ($p->preco_venda ?? 0));

        $pdf = Pdf::loadView('relatorios.estoque-pdf', compact('produtos','valorEstoque','valorVendaTotal'));
        return $pdf->download('estoque.pdf');
    }
}
