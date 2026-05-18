<?php

namespace App\Http\Controllers;

use App\Models\Boleto;
use App\Models\Cliente;
use App\Models\ContaReceber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoletoController extends Controller
{
    public function index(Request $request): View
    {
        $empresaId = auth()->user()->empresa_id;

        $query = Boleto::where('empresa_id', $empresaId)
            ->with(['cliente', 'contaReceber'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->data_inicio, fn($q) => $q->whereDate('data_vencimento', '>=', $request->data_inicio))
            ->when($request->data_fim, fn($q) => $q->whereDate('data_vencimento', '<=', $request->data_fim))
            ->when($request->busca, fn($q) => $q
                ->where(fn($sq) => $sq
                    ->whereHas('cliente', fn($cq) => $cq->where('nome_razao_social', 'like', "%{$request->busca}%"))
                    ->orWhere('numero_boleto', 'like', "%{$request->busca}%")
                    ->orWhere('linha_digitavel', 'like', "%{$request->busca}%")
                )
            )
            ->orderBy('data_vencimento', 'desc')
            ->orderBy('id', 'desc');

        $boletos = $query->paginate(30)->withQueryString();

        // Stats
        $stats = Boleto::where('empresa_id', $empresaId)
            ->selectRaw("
                COUNT(CASE WHEN status = 'emitido' THEN 1 END) as total_emitidos,
                COUNT(CASE WHEN status = 'pago' THEN 1 END) as total_pagos,
                COUNT(CASE WHEN status NOT IN ('pago','cancelado') AND data_vencimento < CURDATE() THEN 1 END) as total_vencidos,
                COUNT(CASE WHEN status NOT IN ('pago','cancelado') AND data_vencimento >= CURDATE() THEN 1 END) as total_a_vencer,
                COALESCE(SUM(CASE WHEN status NOT IN ('pago','cancelado') THEN valor ELSE 0 END), 0) as valor_em_aberto
            ")
            ->first();

        return view('boletos.index', compact('boletos', 'stats'));
    }

    public function create(): View
    {
        $empresaId = auth()->user()->empresa_id;

        $clientes = Cliente::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome_razao_social')
            ->get();

        $contasReceber = ContaReceber::where('empresa_id', $empresaId)
            ->whereIn('status', ['pendente', 'parcial', 'vencido'])
            ->orderBy('data_vencimento')
            ->get();

        return view('boletos.create', compact('clientes', 'contasReceber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cliente_id'       => 'nullable|exists:clientes,id',
            'conta_receber_id' => 'nullable|exists:contas_receber,id',
            'valor'            => 'required|numeric|min:0.01',
            'data_vencimento'  => 'required|date',
            'data_emissao'     => 'nullable|date',
            'instrucoes'       => 'nullable|string',
            'multa'            => 'nullable|numeric|min:0|max:100',
            'juros'            => 'nullable|numeric|min:0|max:100',
            'desconto'         => 'nullable|numeric|min:0',
            'banco'            => 'nullable|string|max:50',
        ]);

        $empresaId = auth()->user()->empresa_id;

        // Ensure the linked conta belongs to the same empresa
        if (! empty($data['conta_receber_id'])) {
            abort_unless(
                ContaReceber::where('id', $data['conta_receber_id'])->where('empresa_id', $empresaId)->exists(),
                403
            );
        }
        if (! empty($data['cliente_id'])) {
            abort_unless(
                Cliente::where('id', $data['cliente_id'])->where('empresa_id', $empresaId)->exists(),
                403
            );
        }

        $data['empresa_id'] = $empresaId;
        $data['status']     = 'rascunho';
        $data['multa']      = $data['multa']   ?? 2.00;
        $data['juros']      = $data['juros']   ?? 1.00;
        $data['desconto']   = $data['desconto'] ?? 0;

        $boleto = Boleto::create($data);

        return redirect()->route('boletos.show', $boleto)->with('success', 'Boleto criado como rascunho com sucesso.');
    }

    public function show(int $id): View
    {
        $empresaId = auth()->user()->empresa_id;

        $boleto = Boleto::where('empresa_id', $empresaId)
            ->with(['cliente', 'contaReceber'])
            ->findOrFail($id);

        return view('boletos.show', compact('boleto'));
    }

    public function emitir(int $id): RedirectResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $boleto = Boleto::where('empresa_id', $empresaId)
            ->where('status', 'rascunho')
            ->findOrFail($id);

        $boleto->update([
            'status'       => 'emitido',
            'data_emissao' => $boleto->data_emissao ?? today(),
        ]);

        return redirect()->route('boletos.show', $boleto)
            ->with('info', 'Boleto marcado como emitido. Integração com banco necessária para emitir boleto automaticamente.');
    }

    public function cancelar(int $id): RedirectResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $boleto = Boleto::where('empresa_id', $empresaId)
            ->whereNotIn('status', ['pago', 'cancelado'])
            ->findOrFail($id);

        $boleto->update(['status' => 'cancelado']);

        return redirect()->route('boletos.show', $boleto)->with('success', 'Boleto cancelado.');
    }

    public function marcarPago(Request $request, int $id): RedirectResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $boleto = Boleto::where('empresa_id', $empresaId)
            ->whereNotIn('status', ['pago', 'cancelado'])
            ->findOrFail($id);

        $boleto->update([
            'status'          => 'pago',
            'data_pagamento'  => today(),
        ]);

        // If there is a linked ContaReceber still open, mark it as received too
        if ($boleto->conta_receber_id) {
            $contaReceber = ContaReceber::where('empresa_id', $empresaId)
                ->whereNotIn('status', ['recebido', 'cancelado'])
                ->find($boleto->conta_receber_id);

            if ($contaReceber) {
                $contaReceber->update([
                    'status'           => 'recebido',
                    'valor_recebido'   => $contaReceber->valor_total,
                    'data_recebimento' => today(),
                ]);
            }
        }

        return redirect()->route('boletos.show', $boleto)->with('success', 'Boleto marcado como pago.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $boleto = Boleto::where('empresa_id', $empresaId)
            ->where('status', 'rascunho')
            ->findOrFail($id);

        $boleto->delete();

        return redirect()->route('boletos.index')->with('success', 'Boleto excluído.');
    }
}
