<?php

namespace App\Http\Controllers;

use App\Models\CentroCusto;
use Illuminate\Http\Request;

class CentroCustoController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $centros = CentroCusto::where('empresa_id', $empresaId)
            ->with('filhos')
            ->whereNull('centro_pai_id')
            ->orderBy('nome')
            ->get();

        return view('centros-custo.index', compact('centros'));
    }

    public function create()
    {
        $pais = CentroCusto::where('empresa_id', auth()->user()->empresa_id)
            ->whereNull('centro_pai_id')
            ->orderBy('nome')
            ->get();

        return view('centros-custo.create', compact('pais'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'          => 'required|string|max:100',
            'codigo'        => 'nullable|string|max:20',
            'centro_pai_id' => 'nullable|exists:centros_custo,id',
        ]);

        $data['empresa_id'] = auth()->user()->empresa_id;

        CentroCusto::create($data);

        return redirect()->route('centros-custo.index')->with('success', 'Centro de custo criado com sucesso.');
    }

    public function edit(CentroCusto $centroCusto)
    {
        $this->authorizeEmpresa($centroCusto);

        $pais = CentroCusto::where('empresa_id', auth()->user()->empresa_id)
            ->whereNull('centro_pai_id')
            ->where('id', '!=', $centroCusto->id)
            ->orderBy('nome')
            ->get();

        return view('centros-custo.edit', compact('centroCusto', 'pais'));
    }

    public function update(Request $request, CentroCusto $centroCusto)
    {
        $this->authorizeEmpresa($centroCusto);

        $data = $request->validate([
            'nome'          => 'required|string|max:100',
            'codigo'        => 'nullable|string|max:20',
            'centro_pai_id' => 'nullable|exists:centros_custo,id',
            'ativo'         => 'boolean',
        ]);

        $centroCusto->update($data);

        return redirect()->route('centros-custo.index')->with('success', 'Centro de custo atualizado.');
    }

    public function destroy(CentroCusto $centroCusto)
    {
        $this->authorizeEmpresa($centroCusto);
        $centroCusto->delete();

        return redirect()->route('centros-custo.index')->with('success', 'Centro de custo removido.');
    }

    private function authorizeEmpresa(CentroCusto $c): void
    {
        abort_if($c->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
