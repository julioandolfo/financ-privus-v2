<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ponto de Equilíbrio</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; padding: 30px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1a1a1a; padding-bottom: 12px; }
        .header .company { font-size: 14px; font-weight: bold; color: #111; }
        .header .title { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .header .period { font-size: 11px; color: #555; margin-top: 4px; }
        .section-title { font-size: 12px; font-weight: bold; color: #333; margin: 20px 0 8px 0; padding-bottom: 4px; border-bottom: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #f3f4f6; padding: 7px 10px; text-align: left; font-size: 10px; text-transform: uppercase; color: #555; font-weight: bold; border: 1px solid #ddd; }
        thead th.right { text-align: right; }
        tbody td { padding: 6px 10px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: middle; }
        tbody td.right { text-align: right; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tfoot td { padding: 7px 10px; font-weight: bold; border: 1px solid #ddd; background: #f3f4f6; font-size: 11px; }
        tfoot td.right { text-align: right; }
        .green { color: #16a34a; }
        .red { color: #dc2626; }
        .amber { color: #d97706; }
        .gray { color: #555; }
        .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .kpi-table td { padding: 10px 12px; border: 1px solid #ddd; vertical-align: top; width: 25%; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #666; font-weight: bold; margin-bottom: 4px; }
        .kpi-value { font-size: 14px; font-weight: bold; }
        .highlight-box { border: 1px solid #ddd; padding: 14px 16px; margin-bottom: 20px; background: #f9fafb; }
        .highlight-box .pe-label { font-size: 10px; color: #555; text-transform: uppercase; font-weight: bold; margin-bottom: 6px; }
        .highlight-box .pe-value { font-size: 22px; font-weight: bold; color: #1a1a1a; }
        .highlight-box .pe-meta { font-size: 10px; color: #555; margin-top: 6px; }
        .pct-bar-bg { background: #e5e7eb; height: 8px; border-radius: 4px; margin: 6px 0 4px 0; }
        .pct-bar { height: 8px; border-radius: 4px; }
        .status-ok { color: #16a34a; font-weight: bold; }
        .status-fail { color: #dc2626; font-weight: bold; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 9px; color: #888; text-align: center; }
        .note-box { border: 1px solid #e5e7eb; padding: 10px 14px; background: #f9fafb; font-size: 10px; color: #555; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="company">
            @if(auth()->check() && auth()->user()->empresa)
                {{ auth()->user()->empresa->razao_social }}
            @else
                Financ Privus
            @endif
        </div>
        <div class="title">Ponto de Equilíbrio Operacional</div>
        <div class="period">
            Período: {{ $inicio->translatedFormat('F \d\e Y') }}
        </div>
    </div>

    {{-- KPI Summary --}}
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-label">Receita do Período</div>
                <div class="kpi-value green">R$ {{ number_format($receitaTotal, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="kpi-label">Custos Fixos</div>
                <div class="kpi-value red">R$ {{ number_format($totalFixo, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="kpi-label">Custos Variáveis</div>
                <div class="kpi-value amber">R$ {{ number_format($totalVariavel, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="kpi-label">Resultado do Período</div>
                <div class="kpi-value {{ $resultado >= 0 ? 'green' : 'red' }}">
                    R$ {{ number_format($resultado, 2, ',', '.') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Ponto de Equilíbrio --}}
    <div class="section-title">Ponto de Equilíbrio Operacional</div>

    @if($pontoEquilibrio !== null)
    <div class="highlight-box">
        <div class="pe-label">Receita mínima necessária para cobrir todos os custos</div>
        <div class="pe-value">R$ {{ number_format($pontoEquilibrio, 2, ',', '.') }}</div>
        <div class="pct-bar-bg">
            <div class="pct-bar" style="width: {{ min($percentualAtingido, 100) }}%; background: {{ $percentualAtingido >= 100 ? '#16a34a' : ($percentualAtingido >= 70 ? '#d97706' : '#dc2626') }};"></div>
        </div>
        <div class="pe-meta">
            {{ number_format(min($percentualAtingido, 100), 1) }}% atingido
            &nbsp;·&nbsp;
            Margem de Contribuição: {{ number_format($margemContribuicao, 1) }}%
        </div>
        <div style="margin-top: 8px;">
            @if($receitaTotal >= $pontoEquilibrio)
            <span class="status-ok">Ponto de equilíbrio atingido neste período.</span>
            @else
            <span class="status-fail">
                Faltam R$ {{ number_format($pontoEquilibrio - $receitaTotal, 2, ',', '.') }} para atingir o ponto de equilíbrio.
            </span>
            @endif
        </div>
    </div>
    @else
    <p style="color:#888; padding: 12px 0;">Não há dados suficientes para calcular o ponto de equilíbrio. Registre despesas pagas no período para uma análise completa.</p>
    @endif

    {{-- Resumo de Custos --}}
    <div class="section-title">Resumo de Custos</div>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th class="right">Valor (R$)</th>
                <th class="right">% do Total</th>
            </tr>
        </thead>
        <tbody>
            @php $pctFixo = $totalDespesas > 0 ? ($totalFixo / $totalDespesas) * 100 : 0; @endphp
            @php $pctVar  = $totalDespesas > 0 ? ($totalVariavel / $totalDespesas) * 100 : 0; @endphp
            <tr>
                <td style="font-weight:bold;">Custos Fixos</td>
                <td class="right red">R$ {{ number_format($totalFixo, 2, ',', '.') }}</td>
                <td class="right">{{ number_format($pctFixo, 1) }}%</td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Custos Variáveis</td>
                <td class="right amber">R$ {{ number_format($totalVariavel, 2, ',', '.') }}</td>
                <td class="right">{{ number_format($pctVar, 1) }}%</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td>Total de Despesas</td>
                <td class="right">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</td>
                <td class="right">100%</td>
            </tr>
        </tfoot>
    </table>

    {{-- Custos Fixos --}}
    <div class="section-title">Detalhamento — Custos Fixos</div>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th class="right">Valor (R$)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fixos as $d)
            <tr>
                <td>{{ $d->descricao }}</td>
                <td class="right red">R$ {{ number_format($d->valor_pago, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" style="text-align:center; color:#888; padding:12px;">Nenhum custo fixo identificado neste período.</td>
            </tr>
            @endforelse
        </tbody>
        @if($fixos->isNotEmpty())
        <tfoot>
            <tr>
                <td>Total Fixo</td>
                <td class="right red">R$ {{ number_format($totalFixo, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Custos Variáveis --}}
    <div class="section-title">Detalhamento — Custos Variáveis</div>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th class="right">Valor (R$)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($variaveis as $d)
            <tr>
                <td>{{ $d->descricao }}</td>
                <td class="right amber">R$ {{ number_format($d->valor_pago, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" style="text-align:center; color:#888; padding:12px;">Nenhum custo variável identificado neste período.</td>
            </tr>
            @endforelse
        </tbody>
        @if($variaveis->isNotEmpty())
        <tfoot>
            <tr>
                <td>Total Variável</td>
                <td class="right amber">R$ {{ number_format($totalVariavel, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="note-box">
        <strong>Metodologia:</strong> Despesas são classificadas como fixas quando a categoria ou descrição contém termos como "aluguel", "salário", "mensalidade", "assinatura" ou "condomínio". As demais são tratadas como variáveis. Ponto de Equilíbrio = Custos Fixos ÷ Margem de Contribuição.
    </div>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y \à\s H:i') }} — Financ Privus
    </div>

</body>
</html>
