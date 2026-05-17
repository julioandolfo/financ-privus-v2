<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    public function index(Request $request)
    {
        $fornecedores = Fornecedor::where('empresa_id', auth()->user()->empresa_id)
            ->when($request->search, fn($q) => $q->where('nome_razao_social', 'like', "%{$request->search}%")
                ->orWhere('cpf_cnpj', 'like', "%{$request->search}%"))
            ->latest()->paginate(20)->withQueryString();

        return view('fornecedores.index', compact('fornecedores'));
    }

    public function create()
    {
        return view('fornecedores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo'              => 'required|in:fisica,juridica',
            'nome_razao_social' => 'required|string|max:255',
            'nome_fantasia'     => 'nullable|string|max:255',
            'cpf_cnpj'          => 'nullable|string|max:18',
            'email'             => 'nullable|email|max:255',
            'telefone'          => 'nullable|string|max:20',
            'celular'           => 'nullable|string|max:20',
            'observacoes'       => 'nullable|string',
        ]);

        $data['empresa_id'] = auth()->user()->empresa_id;
        Fornecedor::create($data);

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor criado com sucesso.');
    }

    public function edit(Fornecedor $fornecedor)
    {
        return view('fornecedores.edit', compact('fornecedor'));
    }

    public function update(Request $request, Fornecedor $fornecedor)
    {
        $data = $request->validate([
            'tipo'              => 'required|in:fisica,juridica',
            'nome_razao_social' => 'required|string|max:255',
            'nome_fantasia'     => 'nullable|string|max:255',
            'cpf_cnpj'          => 'nullable|string|max:18',
            'email'             => 'nullable|email|max:255',
            'telefone'          => 'nullable|string|max:20',
            'celular'           => 'nullable|string|max:20',
            'observacoes'       => 'nullable|string',
            'ativo'             => 'boolean',
        ]);

        $fornecedor->update($data);
        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor atualizado.');
    }

    public function destroy(Fornecedor $fornecedor)
    {
        $fornecedor->delete();
        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor removido.');
    }
}
