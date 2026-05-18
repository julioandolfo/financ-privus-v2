<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFinanceira;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DreController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $mes = (int) $request->get('mes', now()->month);
        $ano = (int) $request->get('ano', now()->year);

        $inicio = Carbon::createFromDate($ano, $mes, 1)->startOfMonth();
        $fim    = $inicio->copy()->endOfMonth();

        // Receitas pagas (contas a receber quitadas no período)
        $receitas = ContaReceber::where('empresa_id', $empresaId)
            ->where('status', 'recebido')
            ->whereBetween('data_recebimento', [$inicio, $fim])
            ->with('categoria')
            ->get();

        // Despesas pagas (contas a pagar quitadas no período)
        $despesas = ContaPagar::where('empresa_id', $empresaId)
            ->where('status', 'pago')
            ->whereBetween('data_pagamento', [$inicio, $fim])
            ->with('categoria')
            ->get();

        // Agrupar receitas por categoria
        $receitasPorCategoria = $receitas->groupBy(fn($r) => $r->categoria?->nome ?? 'Sem categoria')
            ->map(fn($items) => $items->sum('valor_recebido'))
            ->sortByDesc(fn($v) => $v);

        // Agrupar despesas por categoria
        $despesasPorCategoria = $despesas->groupBy(fn($d) => $d->categoria?->nome ?? 'Sem categoria')
            ->map(fn($items) => $items->sum('valor_pago'))
            ->sortByDesc(fn($v) => $v);

        $totalReceitas = $receitas->sum('valor_recebido');
        $totalDespesas = $despesas->sum('valor_pago');
        $resultado     = $totalReceitas - $totalDespesas;

        // Comparativo com mês anterior
        $inicioAnterior = $inicio->copy()->subMonth()->startOfMonth();
        $fimAnterior    = $inicioAnterior->copy()->endOfMonth();

        $totalReceitasAnterior = ContaReceber::where('empresa_id', $empresaId)
            ->where('status', 'recebido')
            ->whereBetween('data_recebimento', [$inicioAnterior, $fimAnterior])
            ->sum('valor_recebido');

        $totalDespesasAnterior = ContaPagar::where('empresa_id', $empresaId)
            ->where('status', 'pago')
            ->whereBetween('data_pagamento', [$inicioAnterior, $fimAnterior])
            ->sum('valor_pago');

        $resultadoAnterior = $totalReceitasAnterior - $totalDespesasAnterior;

        $meses = collect(range(1, 12))->mapWithKeys(fn($m) => [
            $m => Carbon::create(null, $m)->translatedFormat('F'),
        ]);

        $anos = range(now()->year - 3, now()->year + 1);

        return view('relatorios.dre', compact(
            'receitasPorCategoria', 'despesasPorCategoria',
            'totalReceitas', 'totalDespesas', 'resultado',
            'totalReceitasAnterior', 'totalDespesasAnterior', 'resultadoAnterior',
            'mes', 'ano', 'meses', 'anos', 'inicio', 'fim'
        ));
    }

    public function pdf(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $mes = (int) $request->get('mes', now()->month);
        $ano = (int) $request->get('ano', now()->year);

        $inicio = Carbon::createFromDate($ano, $mes, 1)->startOfMonth();
        $fim    = $inicio->copy()->endOfMonth();

        $receitas = ContaReceber::where('empresa_id', $empresaId)
            ->where('status', 'recebido')
            ->whereBetween('data_recebimento', [$inicio, $fim])
            ->with('categoria')
            ->get();

        $despesas = ContaPagar::where('empresa_id', $empresaId)
            ->where('status', 'pago')
            ->whereBetween('data_pagamento', [$inicio, $fim])
            ->with('categoria')
            ->get();

        $receitasPorCategoria = $receitas->groupBy(fn($r) => $r->categoria?->nome ?? 'Sem categoria')
            ->map(fn($items) => $items->sum('valor_recebido'))
            ->sortByDesc(fn($v) => $v);

        $despesasPorCategoria = $despesas->groupBy(fn($d) => $d->categoria?->nome ?? 'Sem categoria')
            ->map(fn($items) => $items->sum('valor_pago'))
            ->sortByDesc(fn($v) => $v);

        $totalReceitas = $receitas->sum('valor_recebido');
        $totalDespesas = $despesas->sum('valor_pago');
        $resultado     = $totalReceitas - $totalDespesas;

        $inicioAnterior = $inicio->copy()->subMonth()->startOfMonth();
        $fimAnterior    = $inicioAnterior->copy()->endOfMonth();

        $totalReceitasAnterior = ContaReceber::where('empresa_id', $empresaId)
            ->where('status', 'recebido')
            ->whereBetween('data_recebimento', [$inicioAnterior, $fimAnterior])
            ->sum('valor_recebido');

        $totalDespesasAnterior = ContaPagar::where('empresa_id', $empresaId)
            ->where('status', 'pago')
            ->whereBetween('data_pagamento', [$inicioAnterior, $fimAnterior])
            ->sum('valor_pago');

        $resultadoAnterior = $totalReceitasAnterior - $totalDespesasAnterior;

        $data = compact(
            'receitasPorCategoria', 'despesasPorCategoria',
            'totalReceitas', 'totalDespesas', 'resultado',
            'totalReceitasAnterior', 'totalDespesasAnterior', 'resultadoAnterior',
            'mes', 'ano', 'inicio', 'fim'
        );

        return Pdf::loadView('relatorios.dre-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->stream('dre.pdf');
    }
}
