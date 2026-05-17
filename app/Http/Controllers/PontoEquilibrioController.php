<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\MovimentacaoCaixa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PontoEquilibrioController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $mes  = (int) $request->get('mes', now()->month);
        $ano  = (int) $request->get('ano', now()->year);

        $inicio = Carbon::create($ano, $mes, 1)->startOfMonth();
        $fim    = $inicio->copy()->endOfMonth();

        // Receita total do período (contas recebidas)
        $receitaTotal = ContaReceber::where('empresa_id', $empresaId)
            ->where('status', 'recebido')
            ->whereBetween('data_recebimento', [$inicio, $fim])
            ->sum('valor_total');

        // Despesas pagas no período
        $despesasPagas = ContaPagar::with('categoria')
            ->where('empresa_id', $empresaId)
            ->where('status', 'pago')
            ->whereBetween('data_pagamento', [$inicio, $fim])
            ->get();

        // Custos fixos: categorias que normalmente são fixas (aluguel, salários, assinaturas)
        // Custos variáveis: demais
        // Heurística: se a categoria tem "fixo" no nome, ou se é aluguel/salário → fixo
        $fixos    = $this->classificarCustos($despesasPagas, 'fixo');
        $variaveis = $this->classificarCustos($despesasPagas, 'variavel');

        $totalFixo     = $fixos->sum('valor_pago');
        $totalVariavel = $variaveis->sum('valor_pago');
        $totalDespesas = $totalFixo + $totalVariavel;

        // Margem de contribuição = (Receita - Custos Variáveis) / Receita
        $margemContribuicao = $receitaTotal > 0
            ? (($receitaTotal - $totalVariavel) / $receitaTotal) * 100
            : 0;

        // Ponto de Equilíbrio = Custos Fixos / Margem de Contribuição (em %)
        $pontoEquilibrio = $margemContribuicao > 0
            ? $totalFixo / ($margemContribuicao / 100)
            : null;

        // Resultado do período
        $resultado = $receitaTotal - $totalDespesas;

        // Percentual atingido do ponto de equilíbrio
        $percentualAtingido = ($pontoEquilibrio && $pontoEquilibrio > 0)
            ? min(($receitaTotal / $pontoEquilibrio) * 100, 200)
            : 0;

        // Meses e anos para filtro
        $meses = collect(range(1, 12))->map(fn($m) => [
            'value' => $m,
            'label' => Carbon::create(null, $m)->translatedFormat('F'),
        ]);

        $anos = collect(range(now()->year - 2, now()->year + 1))
            ->map(fn($a) => ['value' => $a, 'label' => $a]);

        return view('relatorios.ponto-equilibrio', compact(
            'receitaTotal', 'totalFixo', 'totalVariavel', 'totalDespesas',
            'margemContribuicao', 'pontoEquilibrio', 'resultado', 'percentualAtingido',
            'fixos', 'variaveis', 'mes', 'ano', 'meses', 'anos'
        ));
    }

    private function classificarCustos(Collection $despesas, string $tipo): Collection
    {
        $palavrasFixas = ['aluguel', 'salário', 'salario', 'fixo', 'mensalidade', 'assinatura', 'condomínio', 'condominio', 'iptu', 'ipva'];

        return $despesas->filter(function ($d) use ($tipo, $palavrasFixas) {
            $nome = strtolower($d->categoria?->nome ?? $d->descricao ?? '');
            $ehFixo = collect($palavrasFixas)->contains(fn($p) => str_contains($nome, $p));
            return $tipo === 'fixo' ? $ehFixo : !$ehFixo;
        });
    }
}
