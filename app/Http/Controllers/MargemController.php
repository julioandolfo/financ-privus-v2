<?php

namespace App\Http\Controllers;

use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\PedidoVinculado;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class MargemController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $de  = $request->de  ?? now()->startOfMonth()->toDateString();
        $ate = $request->ate ?? now()->endOfMonth()->toDateString();

        // Receitas pagas no período
        $receitas = ContaReceber::where('empresa_id', $empresaId)
            ->where('status', 'pago')
            ->whereBetween('data_recebimento', [$de, $ate])
            ->with('categoria')
            ->get();

        // Despesas pagas no período
        $despesas = ContaPagar::where('empresa_id', $empresaId)
            ->where('status', 'pago')
            ->whereBetween('data_pagamento', [$de, $ate])
            ->with('categoria')
            ->get();

        $totalReceitas = $receitas->sum('valor_recebido');
        $totalDespesas = $despesas->sum('valor_pago');
        $lucroLiquido  = $totalReceitas - $totalDespesas;
        $margem        = $totalReceitas > 0 ? ($lucroLiquido / $totalReceitas) * 100 : 0;

        // By category
        $receitasPorCategoria = $receitas->groupBy('categoria_id')->map(function ($items) {
            return [
                'nome'  => $items->first()->categoria?->nome ?? 'Sem categoria',
                'total' => $items->sum('valor_recebido'),
                'qtd'   => $items->count(),
            ];
        })->sortByDesc('total')->values();

        $despesasPorCategoria = $despesas->groupBy('categoria_id')->map(function ($items) {
            return [
                'nome'  => $items->first()->categoria?->nome ?? 'Sem categoria',
                'total' => $items->sum('valor_pago'),
                'qtd'   => $items->count(),
            ];
        })->sortByDesc('total')->values();

        // Monthly evolution for chart (last 6 months)
        $meses = collect();
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $ini = $mes->copy()->startOfMonth()->toDateString();
            $fim = $mes->copy()->endOfMonth()->toDateString();

            $r = ContaReceber::where('empresa_id', $empresaId)->where('status','pago')->whereBetween('data_recebimento',[$ini,$fim])->sum('valor_recebido');
            $d = ContaPagar::where('empresa_id', $empresaId)->where('status','pago')->whereBetween('data_pagamento',[$ini,$fim])->sum('valor_pago');

            $meses->push([
                'label'   => $mes->translatedFormat('M'),
                'receita' => (float) $r,
                'despesa' => (float) $d,
                'lucro'   => (float) ($r - $d),
            ]);
        }

        return view('relatorios.margem', compact(
            'totalReceitas','totalDespesas','lucroLiquido','margem',
            'receitasPorCategoria','despesasPorCategoria','meses','de','ate'
        ));
    }

    public function pdf(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $de  = $request->de  ?? now()->startOfMonth()->toDateString();
        $ate = $request->ate ?? now()->endOfMonth()->toDateString();

        $receitas = ContaReceber::where('empresa_id',$empresaId)->where('status','pago')->whereBetween('data_recebimento',[$de,$ate])->with('categoria')->get();
        $despesas = ContaPagar::where('empresa_id',$empresaId)->where('status','pago')->whereBetween('data_pagamento',[$de,$ate])->with('categoria')->get();

        $totalReceitas = $receitas->sum('valor_recebido');
        $totalDespesas = $despesas->sum('valor_pago');
        $lucroLiquido  = $totalReceitas - $totalDespesas;
        $margem        = $totalReceitas > 0 ? ($lucroLiquido / $totalReceitas) * 100 : 0;

        $receitasPorCategoria = $receitas->groupBy('categoria_id')->map(fn($i) => ['nome' => $i->first()->categoria?->nome ?? 'Sem categoria','total' => $i->sum('valor_recebido'),'qtd' => $i->count()])->sortByDesc('total');
        $despesasPorCategoria = $despesas->groupBy('categoria_id')->map(fn($i) => ['nome' => $i->first()->categoria?->nome ?? 'Sem categoria','total' => $i->sum('valor_pago'),'qtd' => $i->count()])->sortByDesc('total');

        $pdf = Pdf::loadView('relatorios.margem-pdf', compact('totalReceitas','totalDespesas','lucroLiquido','margem','receitasPorCategoria','despesasPorCategoria','de','ate'));
        return $pdf->download('margem-lucratividade.pdf');
    }
}
