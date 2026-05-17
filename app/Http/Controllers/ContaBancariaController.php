<?php

namespace App\Http\Controllers;

use App\Models\ContaBancaria;
use Illuminate\Http\Request;

class ContaBancariaController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $contas = ContaBancaria::where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get();

        $saldoTotal = $contas->where('ativo', true)->sum('saldo_atual');

        return view('contas-bancarias.index', compact('contas', 'saldoTotal'));
    }

    public function create()
    {
        return view('contas-bancarias.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'        => 'required|string|max:100',
            'banco_codigo'=> 'nullable|string|max:10',
            'banco_nome'  => 'nullable|string|max:100',
            'agencia'     => 'nullable|string|max:10',
            'conta'       => 'nullable|string|max:20',
            'tipo_conta'  => 'required|in:corrente,poupanca,investimento,caixa',
            'saldo_inicial'=> 'required|numeric',
        ]);

        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['saldo_atual'] = $data['saldo_inicial'];

        ContaBancaria::create($data);

        return redirect()->route('contas-bancarias.index')->with('success', 'Conta bancária criada com sucesso.');
    }

    public function edit(ContaBancaria $contaBancaria)
    {
        $this->authorizeEmpresa($contaBancaria);
        return view('contas-bancarias.edit', compact('contaBancaria'));
    }

    public function update(Request $request, ContaBancaria $contaBancaria)
    {
        $this->authorizeEmpresa($contaBancaria);

        $data = $request->validate([
            'nome'        => 'required|string|max:100',
            'banco_codigo'=> 'nullable|string|max:10',
            'banco_nome'  => 'nullable|string|max:100',
            'agencia'     => 'nullable|string|max:10',
            'conta'       => 'nullable|string|max:20',
            'tipo_conta'  => 'required|in:corrente,poupanca,investimento,caixa',
            'ativo'       => 'boolean',
        ]);

        $contaBancaria->update($data);

        return redirect()->route('contas-bancarias.index')->with('success', 'Conta bancária atualizada.');
    }

    public function destroy(ContaBancaria $contaBancaria)
    {
        $this->authorizeEmpresa($contaBancaria);
        $contaBancaria->delete();

        return redirect()->route('contas-bancarias.index')->with('success', 'Conta bancária removida.');
    }

    private function authorizeEmpresa(ContaBancaria $conta): void
    {
        abort_if($conta->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
