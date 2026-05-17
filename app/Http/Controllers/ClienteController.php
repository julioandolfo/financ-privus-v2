<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::where('empresa_id', auth()->user()->empresa_id)
            ->when($request->search, fn($q) => $q->where('nome_razao_social', 'like', "%{$request->search}%")
                ->orWhere('cpf_cnpj', 'like', "%{$request->search}%"))
            ->when($request->status !== null, fn($q) => $q->where('ativo', $request->status))
            ->latest()->paginate(20)->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
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
        Cliente::create($data);

        return redirect()->route('clientes.index')->with('success', 'Cliente criado com sucesso.');
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
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

        $cliente->update($data);
        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente removido.');
    }
}
