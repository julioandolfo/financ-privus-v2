<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $totalPagar = ContaPagar::where('empresa_id', $empresaId)
            ->pendentes()->doMes()->sum('valor_total');

        $totalReceber = ContaReceber::where('empresa_id', $empresaId)
            ->pendentes()->doMes()->sum('valor_total');

        $vencidosPagar = ContaPagar::where('empresa_id', $empresaId)
            ->vencidas()->count();

        $vencidosReceber = ContaReceber::where('empresa_id', $empresaId)
            ->vencidas()->count();

        $saldoContas = DB::table('contas_bancarias')
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->sum('saldo_atual');

        $vencimentosProximos = ContaPagar::where('empresa_id', $empresaId)
            ->pendentes()
            ->whereBetween('data_vencimento', [today(), today()->addDays(7)])
            ->with('fornecedor')
            ->orderBy('data_vencimento')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'totalPagar', 'totalReceber',
            'vencidosPagar', 'vencidosReceber',
            'saldoContas', 'vencimentosProximos'
        ));
    }
}
