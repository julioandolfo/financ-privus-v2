<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFinanceira;
use Illuminate\Http\Request;

class CategoriaFinanceiraController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $categorias = CategoriaFinanceira::where('empresa_id', $empresaId)
            ->with('filhas')
            ->whereNull('categoria_pai_id')
            ->orderBy('nome')
            ->get();

        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        $pais = CategoriaFinanceira::where('empresa_id', auth()->user()->empresa_id)
            ->whereNull('categoria_pai_id')
            ->orderBy('nome')
            ->get();

        return view('categorias.create', compact('pais'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'            => 'required|string|max:100',
            'codigo'          => 'nullable|string|max:20',
            'tipo'            => 'required|in:receita,despesa,ambos',
            'categoria_pai_id'=> 'nullable|exists:categorias_financeiras,id',
        ]);

        $data['empresa_id'] = auth()->user()->empresa_id;

        CategoriaFinanceira::create($data);

        return redirect()->route('categorias.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function edit(CategoriaFinanceira $categoria)
    {
        $this->authorizeEmpresa($categoria);

        $pais = CategoriaFinanceira::where('empresa_id', auth()->user()->empresa_id)
            ->whereNull('categoria_pai_id')
            ->where('id', '!=', $categoria->id)
            ->orderBy('nome')
            ->get();

        return view('categorias.edit', compact('categoria', 'pais'));
    }

    public function update(Request $request, CategoriaFinanceira $categoria)
    {
        $this->authorizeEmpresa($categoria);

        $data = $request->validate([
            'nome'            => 'required|string|max:100',
            'codigo'          => 'nullable|string|max:20',
            'tipo'            => 'required|in:receita,despesa,ambos',
            'categoria_pai_id'=> 'nullable|exists:categorias_financeiras,id',
            'ativo'           => 'boolean',
        ]);

        $categoria->update($data);

        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada.');
    }

    public function destroy(CategoriaFinanceira $categoria)
    {
        $this->authorizeEmpresa($categoria);
        $categoria->delete();

        return redirect()->route('categorias.index')->with('success', 'Categoria removida.');
    }

    private function authorizeEmpresa(CategoriaFinanceira $c): void
    {
        abort_if($c->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
