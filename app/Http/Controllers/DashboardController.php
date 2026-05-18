<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        // --- Period resolution ---
        $periodo = $request->query('periodo', 'mes');
        $hoje    = Carbon::today();

        if ($request->filled('de') && $request->filled('ate')) {
            $de  = Carbon::parse($request->query('de'))->startOfDay();
            $ate = Carbon::parse($request->query('ate'))->endOfDay();
            $periodo = 'custom';
        } else {
            switch ($periodo) {
                case 'hoje':
                    $de  = $hoje->copy()->startOfDay();
                    $ate = $hoje->copy()->endOfDay();
                    break;
                case 'semana':
                    $de  = $hoje->copy()->startOfWeek();
                    $ate = $hoje->copy()->endOfWeek();
                    break;
                case 'trimestre':
                    $de  = $hoje->copy()->firstOfQuarter()->startOfDay();
                    $ate = $hoje->copy()->lastOfQuarter()->endOfDay();
                    break;
                case 'ano':
                    $de  = $hoje->copy()->startOfYear();
                    $ate = $hoje->copy()->endOfYear();
                    break;
                case 'mes':
                default:
                    $periodo = 'mes';
                    $de  = $hoje->copy()->startOfMonth();
                    $ate = $hoje->copy()->endOfMonth();
                    break;
            }
        }

        // --- KPI cards (period-aware) ---
        $totalPagar = ContaPagar::where('empresa_id', $empresaId)
            ->pendentes()
            ->whereBetween('data_vencimento', [$de->toDateString(), $ate->toDateString()])
            ->sum('valor_total');

        $totalReceber = ContaReceber::where('empresa_id', $empresaId)
            ->pendentes()
            ->whereBetween('data_vencimento', [$de->toDateString(), $ate->toDateString()])
            ->sum('valor_total');

        $vencidosPagar = ContaPagar::where('empresa_id', $empresaId)
            ->vencidas()->count();

        $vencidosReceber = ContaReceber::where('empresa_id', $empresaId)
            ->vencidas()->count();

        $saldoContas = DB::table('contas_bancarias')
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->sum('saldo_atual');

        // --- Upcoming due dates (always next 7 days) ---
        $vencimentosProximos = ContaPagar::where('empresa_id', $empresaId)
            ->pendentes()
            ->whereBetween('data_vencimento', [today(), today()->addDays(7)])
            ->with('fornecedor')
            ->orderBy('data_vencimento')
            ->limit(10)
            ->get();

        // --- Receita / Despesa do mês corrente ---
        $mesAtual = Carbon::now();
        $receitasMes = ContaReceber::where('empresa_id', $empresaId)
            ->where('status', 'pago')
            ->whereMonth('data_recebimento', $mesAtual->month)
            ->whereYear('data_recebimento', $mesAtual->year)
            ->sum('valor_recebido');

        $despesasMes = ContaPagar::where('empresa_id', $empresaId)
            ->where('status', 'pago')
            ->whereMonth('data_pagamento', $mesAtual->month)
            ->whereYear('data_pagamento', $mesAtual->year)
            ->sum('valor_pago');

        // --- Inadimplência: ContaReceber vencidas (status != pago) ---
        $inadimplentesQuery = ContaReceber::where('empresa_id', $empresaId)
            ->where('data_vencimento', '<', today())
            ->where('status', '!=', 'pago');

        $inadimplentes = [
            'count' => $inadimplentesQuery->count(),
            'total' => $inadimplentesQuery->sum('valor_total'),
        ];

        // --- 6-month cash flow chart ---
        $fluxoLabels   = [];
        $fluxoEntradas = [];
        $fluxoSaidas   = [];

        $shortMonths = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
        ];

        for ($i = 5; $i >= 0; $i--) {
            $ref = Carbon::now()->subMonths($i);
            $m   = (int) $ref->month;
            $y   = (int) $ref->year;

            $fluxoLabels[] = $shortMonths[$m];

            $fluxoEntradas[] = (float) ContaReceber::where('empresa_id', $empresaId)
                ->where('status', 'pago')
                ->whereMonth('data_recebimento', $m)
                ->whereYear('data_recebimento', $y)
                ->sum('valor_recebido');

            $fluxoSaidas[] = (float) ContaPagar::where('empresa_id', $empresaId)
                ->where('status', 'pago')
                ->whereMonth('data_pagamento', $m)
                ->whereYear('data_pagamento', $y)
                ->sum('valor_pago');
        }

        return view('dashboard.index', compact(
            'totalPagar', 'totalReceber',
            'vencidosPagar', 'vencidosReceber',
            'saldoContas', 'vencimentosProximos',
            'periodo', 'de', 'ate',
            'receitasMes', 'despesasMes',
            'inadimplentes',
            'fluxoLabels', 'fluxoEntradas', 'fluxoSaidas'
        ));
    }
}
