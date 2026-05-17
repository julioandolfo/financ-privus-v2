<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresas = Empresa::withCount(['clientes', 'fornecedores'])->latest()->paginate(20);
        return view('empresas.index', compact('empresas'));
    }

    public function create()
    {
        $grupos = Empresa::whereNull('grupo_empresarial_id')->where('ativo', true)->get();
        return view('empresas.create', compact('grupos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo'            => 'required|string|max:20|unique:empresas',
            'razao_social'      => 'required|string|max:255',
            'nome_fantasia'     => 'nullable|string|max:255',
            'cnpj'              => 'nullable|string|max:18|unique:empresas',
            'grupo_empresarial_id' => 'nullable|exists:empresas,id',
            'ativo'             => 'boolean',
        ]);

        Empresa::create($data);
        return redirect()->route('empresas.index')->with('success', 'Empresa criada com sucesso.');
    }

    public function edit(Empresa $empresa)
    {
        $grupos = Empresa::whereNull('grupo_empresarial_id')->where('ativo', true)->where('id', '!=', $empresa->id)->get();
        return view('empresas.edit', compact('empresa', 'grupos'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'codigo'            => 'required|string|max:20|unique:empresas,codigo,' . $empresa->id,
            'razao_social'      => 'required|string|max:255',
            'nome_fantasia'     => 'nullable|string|max:255',
            'cnpj'              => 'nullable|string|max:18|unique:empresas,cnpj,' . $empresa->id,
            'grupo_empresarial_id' => 'nullable|exists:empresas,id',
            'ativo'             => 'boolean',
        ]);

        $empresa->update($data);
        return redirect()->route('empresas.index')->with('success', 'Empresa atualizada.');
    }

    public function destroy(Empresa $empresa)
    {
        $empresa->delete();
        return redirect()->route('empresas.index')->with('success', 'Empresa removida.');
    }
}
