<?php

namespace App\Http\Controllers;

use App\Models\ContaReceber;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InadimplenciaController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $de  = $request->de  ?? now()->startOfMonth()->toDateString();
        $ate = $request->ate ?? now()->toDateString();

        $contas = ContaReceber::where('empresa_id', $empresaId)
            ->whereIn('status', ['pendente', 'vencido', 'parcial'])
            ->where('data_vencimento', '<', now())
            ->when($request->de,  fn($q) => $q->where('data_vencimento', '>=', $de))
            ->when($request->ate, fn($q) => $q->where('data_vencimento', '<=', $ate))
            ->with('cliente')
            ->orderBy('data_vencimento')
            ->get();

        $total = $contas->sum('valor_total');
        $totalRecebido = $contas->sum('valor_recebido');
        $totalAberto = $total - $totalRecebido;
        $qtd = $contas->count();

        // Group by cliente
        $porCliente = $contas->groupBy('cliente_id')->map(function ($items) {
            return [
                'cliente' => $items->first()->cliente,
                'total'   => $items->sum('valor_total'),
                'aberto'  => $items->sum('valor_total') - $items->sum('valor_recebido'),
                'qtd'     => $items->count(),
            ];
        })->sortByDesc('aberto');

        return view('relatorios.inadimplencia', compact('contas', 'total', 'totalAberto', 'totalRecebido', 'qtd', 'porCliente', 'de', 'ate'));
    }

    public function pdf(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $de  = $request->de  ?? now()->startOfMonth()->toDateString();
        $ate = $request->ate ?? now()->toDateString();

        $contas = ContaReceber::where('empresa_id', $empresaId)
            ->whereIn('status', ['pendente', 'vencido', 'parcial'])
            ->where('data_vencimento', '<', now())
            ->with('cliente')
            ->orderBy('data_vencimento')
            ->get();

        $total = $contas->sum('valor_total');
        $totalRecebido = $contas->sum('valor_recebido');
        $totalAberto = $total - $totalRecebido;

        $pdf = Pdf::loadView('relatorios.inadimplencia-pdf', compact('contas', 'total', 'totalAberto', 'totalRecebido', 'de', 'ate'));
        return $pdf->download('inadimplencia.pdf');
    }
}
