<?php

namespace App\Http\Controllers;

use App\Models\ContaBancaria;
use App\Models\MovimentacaoCaixa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FluxoCaixaController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $de  = $request->filled('de')  ? Carbon::parse($request->de)  : now()->startOfMonth();
        $ate = $request->filled('ate') ? Carbon::parse($request->ate) : now()->endOfMonth();
        $agrupamento = $request->get('agrupamento', 'dia');
        $contaId = $request->get('conta_bancaria_id');

        $base = MovimentacaoCaixa::where('empresa_id', $empresaId)
            ->whereBetween('data_movimentacao', [$de->startOfDay(), $ate->endOfDay()])
            ->when($contaId, fn($q) => $q->where('conta_bancaria_id', $contaId));

        $format = match ($agrupamento) {
            'mes'     => '%Y-%m',
            'semana'  => '%x-W%v',
            default   => '%Y-%m-%d',
        };

        $periodos = (clone $base)
            ->selectRaw("DATE_FORMAT(data_movimentacao, ?) as periodo,
                SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as entradas,
                SUM(CASE WHEN tipo = 'saida'   THEN valor ELSE 0 END) as saidas,
                COUNT(*) as total_lancamentos", [$format])
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $totalEntradas = $periodos->sum('entradas');
        $totalSaidas   = $periodos->sum('saidas');
        $saldoPeriodo  = $totalEntradas - $totalSaidas;

        // Saldo acumulado por período
        $acumulado = 0;
        $periodos = $periodos->map(function ($p) use (&$acumulado) {
            $acumulado += ($p->entradas - $p->saidas);
            $p->saldo_acumulado = $acumulado;
            return $p;
        });

        // Saldo anterior ao período (saldo inicial)
        $saldoAnterior = (clone $base)
            ->where('data_movimentacao', '<', $de->copy()->startOfDay())
            ->selectRaw("SUM(CASE WHEN tipo = 'entrada' THEN valor WHEN tipo = 'saida' THEN -valor ELSE 0 END) as saldo")
            ->value('saldo') ?? 0;

        $contas = ContaBancaria::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('relatorios.fluxo-caixa', compact(
            'periodos', 'totalEntradas', 'totalSaidas', 'saldoPeriodo',
            'saldoAnterior', 'de', 'ate', 'agrupamento', 'contas', 'contaId'
        ));
    }

    public function pdf(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $de  = $request->filled('de')  ? Carbon::parse($request->de)  : now()->startOfMonth();
        $ate = $request->filled('ate') ? Carbon::parse($request->ate) : now()->endOfMonth();
        $agrupamento = $request->get('agrupamento', 'dia');
        $contaId = $request->get('conta_bancaria_id');

        $base = MovimentacaoCaixa::where('empresa_id', $empresaId)
            ->whereBetween('data_movimentacao', [$de->startOfDay(), $ate->endOfDay()])
            ->when($contaId, fn($q) => $q->where('conta_bancaria_id', $contaId));

        $format = match ($agrupamento) {
            'mes'     => '%Y-%m',
            'semana'  => '%x-W%v',
            default   => '%Y-%m-%d',
        };

        $periodos = (clone $base)
            ->selectRaw("DATE_FORMAT(data_movimentacao, ?) as periodo,
                SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as entradas,
                SUM(CASE WHEN tipo = 'saida'   THEN valor ELSE 0 END) as saidas,
                COUNT(*) as total_lancamentos", [$format])
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $totalEntradas = $periodos->sum('entradas');
        $totalSaidas   = $periodos->sum('saidas');
        $saldoPeriodo  = $totalEntradas - $totalSaidas;

        $acumulado = 0;
        $periodos = $periodos->map(function ($p) use (&$acumulado) {
            $acumulado += ($p->entradas - $p->saidas);
            $p->saldo_acumulado = $acumulado;
            return $p;
        });

        $saldoAnterior = (clone $base)
            ->where('data_movimentacao', '<', $de->copy()->startOfDay())
            ->selectRaw("SUM(CASE WHEN tipo = 'entrada' THEN valor WHEN tipo = 'saida' THEN -valor ELSE 0 END) as saldo")
            ->value('saldo') ?? 0;

        $data = compact(
            'periodos', 'totalEntradas', 'totalSaidas', 'saldoPeriodo',
            'saldoAnterior', 'de', 'ate', 'agrupamento', 'contaId'
        );

        return Pdf::loadView('relatorios.fluxo-caixa-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->stream('fluxo-caixa.pdf');
    }
}
