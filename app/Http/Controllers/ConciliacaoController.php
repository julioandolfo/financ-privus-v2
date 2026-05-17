<?php

namespace App\Http\Controllers;

use App\Models\ContaBancaria;
use App\Models\MovimentacaoCaixa;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ConciliacaoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $contas = ContaBancaria::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        $contaId = $request->get('conta_bancaria_id', $contas->first()?->id);
        $de  = $request->filled('de')  ? Carbon::parse($request->de)  : now()->startOfMonth();
        $ate = $request->filled('ate') ? Carbon::parse($request->ate) : now()->endOfMonth();

        $conta = $contaId ? ContaBancaria::find($contaId) : null;

        $movimentacoes = MovimentacaoCaixa::where('empresa_id', $empresaId)
            ->when($contaId, fn($q) => $q->where('conta_bancaria_id', $contaId))
            ->whereBetween('data_movimentacao', [$de->startOfDay(), $ate->endOfDay()])
            ->with('categoria')
            ->orderBy('data_movimentacao')
            ->orderBy('id')
            ->get();

        $totalConciliadas    = $movimentacoes->where('conciliado', true)->count();
        $totalNaoConciliadas = $movimentacoes->where('conciliado', false)->count();

        $saldoConciliado = $movimentacoes->where('conciliado', true)
            ->sum(fn($m) => $m->tipo === 'entrada' ? $m->valor : -$m->valor);

        return view('conciliacao.index', compact(
            'contas', 'conta', 'contaId', 'de', 'ate',
            'movimentacoes', 'totalConciliadas', 'totalNaoConciliadas', 'saldoConciliado'
        ));
    }

    public function conciliar(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Nenhuma movimentação selecionada.');
        }

        MovimentacaoCaixa::where('empresa_id', $empresaId)
            ->whereIn('id', $ids)
            ->update(['conciliado' => true]);

        return back()->with('success', count($ids) . ' movimentação(ões) conciliada(s).');
    }

    public function desconciliar(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Nenhuma movimentação selecionada.');
        }

        MovimentacaoCaixa::where('empresa_id', $empresaId)
            ->whereIn('id', $ids)
            ->update(['conciliado' => false]);

        return back()->with('success', count($ids) . ' movimentação(ões) desconciliada(s).');
    }

    public function conciliarItem(MovimentacaoCaixa $movimentacao)
    {
        abort_if($movimentacao->empresa_id !== auth()->user()->empresa_id, 403);
        $movimentacao->update(['conciliado' => !$movimentacao->conciliado]);
        return back();
    }
}
