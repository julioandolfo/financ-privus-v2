<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\MovimentacaoCaixa;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DfcController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $de  = $request->de  ?? now()->startOfMonth()->toDateString();
        $ate = $request->ate ?? now()->endOfMonth()->toDateString();

        // Recebimentos (ContaReceber pagas no período)
        $recebimentos = ContaReceber::where('empresa_id', $empresaId)
            ->where('status', 'pago')
            ->whereBetween('data_recebimento', [$de, $ate])
            ->with('cliente', 'categoria')
            ->orderBy('data_recebimento')
            ->get();

        // Pagamentos (ContaPagar pagas no período)
        $pagamentos = ContaPagar::where('empresa_id', $empresaId)
            ->where('status', 'pago')
            ->whereBetween('data_pagamento', [$de, $ate])
            ->with('fornecedor', 'categoria')
            ->orderBy('data_pagamento')
            ->get();

        // Movimentações de caixa no período
        $movimentacoes = MovimentacaoCaixa::where('empresa_id', $empresaId)
            ->whereBetween('data_movimentacao', [$de, $ate])
            ->with('categoria')
            ->orderBy('data_movimentacao')
            ->get();

        $totalEntradas = $recebimentos->sum('valor_recebido') + $movimentacoes->where('tipo', 'entrada')->sum('valor');
        $totalSaidas   = $pagamentos->sum('valor_pago') + $movimentacoes->where('tipo', 'saida')->sum('valor');
        $saldoPeriodo  = $totalEntradas - $totalSaidas;

        return view('relatorios.dfc', compact(
            'recebimentos',
            'pagamentos',
            'movimentacoes',
            'totalEntradas',
            'totalSaidas',
            'saldoPeriodo',
            'de',
            'ate'
        ));
    }

    public function pdf(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $de  = $request->de  ?? now()->startOfMonth()->toDateString();
        $ate = $request->ate ?? now()->endOfMonth()->toDateString();

        $recebimentos  = ContaReceber::where('empresa_id', $empresaId)->where('status', 'pago')->whereBetween('data_recebimento', [$de, $ate])->with('cliente')->get();
        $pagamentos    = ContaPagar::where('empresa_id', $empresaId)->where('status', 'pago')->whereBetween('data_pagamento', [$de, $ate])->with('fornecedor')->get();
        $movimentacoes = MovimentacaoCaixa::where('empresa_id', $empresaId)->whereBetween('data_movimentacao', [$de, $ate])->get();

        $totalEntradas = $recebimentos->sum('valor_recebido') + $movimentacoes->where('tipo', 'entrada')->sum('valor');
        $totalSaidas   = $pagamentos->sum('valor_pago') + $movimentacoes->where('tipo', 'saida')->sum('valor');
        $saldoPeriodo  = $totalEntradas - $totalSaidas;

        $pdf = Pdf::loadView('relatorios.dfc-pdf', compact('recebimentos', 'pagamentos', 'movimentacoes', 'totalEntradas', 'totalSaidas', 'saldoPeriodo', 'de', 'ate'));
        return $pdf->download('dfc.pdf');
    }
}
