<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProduto;
use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $produtos = Produto::where('empresa_id', $empresaId)
            ->when($request->filled('search'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->search . '%')
                  ->orWhere('codigo', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            }))
            ->when($request->filled('tipo'), fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('categoria_id'), fn($q) => $q->where('categoria_id', $request->categoria_id))
            ->when($request->filled('estoque_baixo'), fn($q) => $q->whereColumn('estoque', '<=', 'estoque_minimo'))
            ->with('categoria')
            ->orderBy('nome')
            ->paginate(30)
            ->withQueryString();

        $categorias = CategoriaProduto::where('empresa_id', $empresaId)->orderBy('nome')->get();
        $totalEstoqueBaixo = Produto::where('empresa_id', $empresaId)
            ->whereColumn('estoque', '<=', 'estoque_minimo')
            ->count();

        return view('produtos.index', compact('produtos', 'categorias', 'totalEstoqueBaixo'));
    }

    public function create()
    {
        $empresaId = auth()->user()->empresa_id;
        $categorias = CategoriaProduto::where('empresa_id', $empresaId)->orderBy('nome')->get();
        return view('produtos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'nome'            => ['required', 'string', 'max:255'],
            'codigo'          => ['nullable', 'string', 'max:50', 'unique:produtos,codigo,NULL,id,empresa_id,' . $empresaId],
            'sku'             => ['nullable', 'string', 'max:100'],
            'codigo_barras'   => ['nullable', 'string', 'max:50'],
            'tipo'            => ['required', 'in:produto,servico'],
            'descricao'       => ['nullable', 'string'],
            'custo_unitario'  => ['nullable', 'numeric', 'min:0'],
            'preco_venda'     => ['required', 'numeric', 'min:0'],
            'unidade_medida'  => ['nullable', 'string', 'max:20'],
            'estoque'         => ['nullable', 'numeric', 'min:0'],
            'estoque_minimo'  => ['nullable', 'numeric', 'min:0'],
            'categoria_id'    => ['nullable', 'exists:categorias_produto,id'],
            'ncm'             => ['nullable', 'string', 'max:10'],
            'cfop'            => ['nullable', 'string', 'max:10'],
        ]);

        Produto::create(array_merge($data, [
            'empresa_id' => $empresaId,
            'user_id'    => auth()->id(),
        ]));

        return redirect()->route('produtos.index')
            ->with('success', 'Produto criado com sucesso.');
    }

    public function edit(Produto $produto)
    {
        $this->authorize($produto);
        $empresaId = auth()->user()->empresa_id;
        $categorias = CategoriaProduto::where('empresa_id', $empresaId)->orderBy('nome')->get();
        return view('produtos.edit', compact('produto', 'categorias'));
    }

    public function update(Request $request, Produto $produto)
    {
        $this->authorize($produto);
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'nome'            => ['required', 'string', 'max:255'],
            'codigo'          => ['nullable', 'string', 'max:50', 'unique:produtos,codigo,' . $produto->id . ',id,empresa_id,' . $empresaId],
            'sku'             => ['nullable', 'string', 'max:100'],
            'codigo_barras'   => ['nullable', 'string', 'max:50'],
            'tipo'            => ['required', 'in:produto,servico'],
            'descricao'       => ['nullable', 'string'],
            'custo_unitario'  => ['nullable', 'numeric', 'min:0'],
            'preco_venda'     => ['required', 'numeric', 'min:0'],
            'unidade_medida'  => ['nullable', 'string', 'max:20'],
            'estoque'         => ['nullable', 'numeric', 'min:0'],
            'estoque_minimo'  => ['nullable', 'numeric', 'min:0'],
            'categoria_id'    => ['nullable', 'exists:categorias_produto,id'],
            'ncm'             => ['nullable', 'string', 'max:10'],
            'cfop'            => ['nullable', 'string', 'max:10'],
        ]);

        $produto->update($data);

        return redirect()->route('produtos.index')
            ->with('success', 'Produto atualizado.');
    }

    public function destroy(Produto $produto)
    {
        $this->authorize($produto);
        $produto->delete();
        return back()->with('success', 'Produto removido.');
    }

    private function authorize(Produto $produto): void
    {
        abort_if($produto->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
