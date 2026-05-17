<?php

namespace App\Http\Controllers;

use App\Models\FormaPagamento;
use Illuminate\Http\Request;

class FormaPagamentoController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $formas = FormaPagamento::where(fn($q) =>
                $q->whereNull('empresa_id')->orWhere('empresa_id', $empresaId)
            )
            ->orderBy('nome')
            ->get();

        return view('formas-pagamento.index', compact('formas'));
    }

    public function create()
    {
        return view('formas-pagamento.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'   => 'required|string|max:100',
            'codigo' => 'nullable|string|max:20',
            'tipo'   => 'required|in:pagamento,recebimento,ambos',
            'padrao' => 'boolean',
        ]);

        $data['empresa_id'] = auth()->user()->empresa_id;

        if (!empty($data['padrao'])) {
            FormaPagamento::where('empresa_id', $data['empresa_id'])
                ->where('tipo', $data['tipo'])
                ->update(['padrao' => false]);
        }

        FormaPagamento::create($data);

        return redirect()->route('formas-pagamento.index')->with('success', 'Forma de pagamento criada.');
    }

    public function edit(FormaPagamento $formaPagamento)
    {
        $this->authorizeEdit($formaPagamento);
        return view('formas-pagamento.edit', compact('formaPagamento'));
    }

    public function update(Request $request, FormaPagamento $formaPagamento)
    {
        $this->authorizeEdit($formaPagamento);

        $data = $request->validate([
            'nome'   => 'required|string|max:100',
            'codigo' => 'nullable|string|max:20',
            'tipo'   => 'required|in:pagamento,recebimento,ambos',
            'padrao' => 'boolean',
            'ativo'  => 'boolean',
        ]);

        if (!empty($data['padrao'])) {
            FormaPagamento::where('empresa_id', auth()->user()->empresa_id)
                ->where('tipo', $data['tipo'])
                ->where('id', '!=', $formaPagamento->id)
                ->update(['padrao' => false]);
        }

        $formaPagamento->update($data);

        return redirect()->route('formas-pagamento.index')->with('success', 'Forma de pagamento atualizada.');
    }

    public function destroy(FormaPagamento $formaPagamento)
    {
        $this->authorizeEdit($formaPagamento);
        $formaPagamento->delete();

        return redirect()->route('formas-pagamento.index')->with('success', 'Forma de pagamento removida.');
    }

    private function authorizeEdit(FormaPagamento $fp): void
    {
        // Formas globais (empresa_id null) só admins podem editar
        if (is_null($fp->empresa_id)) {
            abort_if(auth()->user()->role !== 'admin', 403);
        } else {
            abort_if($fp->empresa_id !== auth()->user()->empresa_id, 403);
        }
    }
}
