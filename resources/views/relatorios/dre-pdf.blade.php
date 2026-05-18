<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>DRE — Demonstrativo de Resultado</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; padding: 30px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1a1a1a; padding-bottom: 12px; }
        .header .company { font-size: 14px; font-weight: bold; color: #111; }
        .header .title { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .header .period { font-size: 11px; color: #555; margin-top: 4px; }
        .summary { display: table; width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .summary-cell { display: table-cell; width: 33.33%; padding: 10px 12px; border: 1px solid #ddd; vertical-align: top; }
        .summary-label { font-size: 9px; text-transform: uppercase; color: #666; font-weight: bold; margin-bottom: 4px; }
        .summary-value { font-size: 14px; font-weight: bold; }
        .green { color: #16a34a; }
        .red { color: #dc2626; }
        .blue { color: #2563eb; }
        .section-title { font-size: 12px; font-weight: bold; color: #333; margin: 20px 0 8px 0; padding-bottom: 4px; border-bottom: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #f3f4f6; padding: 7px 10px; text-align: left; font-size: 10px; text-transform: uppercase; color: #555; font-weight: bold; border: 1px solid #ddd; }
        thead th.right { text-align: right; }
        tbody td { padding: 6px 10px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: middle; }
        tbody td.right { text-align: right; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tfoot td { padding: 7px 10px; font-weight: bold; border: 1px solid #ddd; background: #f3f4f6; font-size: 11px; }
        tfoot td.right { text-align: right; }
        .formal-table tbody tr.income-row td { background: #f0fdf4; }
        .formal-table tbody tr.expense-row td { background: #fef2f2; }
        .formal-table tbody tr.result-row td { background: #eff6ff; font-weight: bold; border-top: 2px solid #1a1a1a; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 9px; color: #888; text-align: center; }
        .two-col { display: table; width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 12px; }
        .col:last-child { padding-right: 0; padding-left: 12px; }
        .pct-bar-bg { background: #e5e7eb; height: 6px; border-radius: 3px; margin-top: 2px; }
        .pct-bar { height: 6px; border-radius: 3px; }
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
        <div class="title">DRE — Demonstrativo de Resultado do Exercício</div>
        <div class="period">Período: {{ $inicio->translatedFormat('F \d\e Y') }}</div>
    </div>

    {{-- Resumo --}}
    <table>
        <thead>
            <tr>
                <th>Receitas Recebidas</th>
                <th class="right">Despesas Pagas</th>
                <th class="right">Resultado Líquido</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="green" style="font-size:13px; font-weight:bold;">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</td>
                <td class="right red" style="font-size:13px; font-weight:bold;">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</td>
                <td class="right {{ $resultado >= 0 ? 'green' : 'red' }}" style="font-size:13px; font-weight:bold;">
                    {{ $resultado >= 0 ? '+' : '' }}R$ {{ number_format($resultado, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Demonstrativo Formal --}}
    <div class="section-title">Demonstrativo Formal</div>
    <table class="formal-table">
        <thead>
            <tr>
                <th>Descrição</th>
                <th class="right">{{ $inicio->translatedFormat('M/Y') }}</th>
                <th class="right">{{ $inicio->copy()->subMonth()->translatedFormat('M/Y') }}</th>
                <th class="right">Var %</th>
            </tr>
        </thead>
        <tbody>
            @php
                $varReceita  = $totalReceitasAnterior > 0  ? (($totalReceitas  - $totalReceitasAnterior)  / $totalReceitasAnterior)  * 100 : null;
                $varDespesa  = $totalDespesasAnterior > 0  ? (($totalDespesas  - $totalDespesasAnterior)  / $totalDespesasAnterior)  * 100 : null;
                $varResultado = $resultadoAnterior != 0    ? (($resultado      - $resultadoAnterior)       / abs($resultadoAnterior)) * 100 : null;
            @endphp
            <tr class="income-row">
                <td style="font-weight:bold;">Receita Bruta</td>
                <td class="right green" style="font-weight:bold;">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</td>
                <td class="right" style="color:#555;">R$ {{ number_format($totalReceitasAnterior, 2, ',', '.') }}</td>
                <td class="right {{ $varReceita !== null && $varReceita >= 0 ? 'green' : 'red' }}">
                    @if($varReceita !== null){{ $varReceita >= 0 ? '+' : '' }}{{ number_format($varReceita, 1) }}%@endif
                </td>
            </tr>
            <tr class="expense-row">
                <td style="font-weight:bold;">(-) Despesas Operacionais</td>
                <td class="right red" style="font-weight:bold;">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</td>
                <td class="right" style="color:#555;">R$ {{ number_format($totalDespesasAnterior, 2, ',', '.') }}</td>
                <td class="right {{ $varDespesa !== null && $varDespesa <= 0 ? 'green' : 'red' }}">
                    @if($varDespesa !== null){{ $varDespesa >= 0 ? '+' : '' }}{{ number_format($varDespesa, 1) }}%@endif
                </td>
            </tr>
            <tr class="result-row">
                <td>= Resultado Líquido</td>
                <td class="right {{ $resultado >= 0 ? 'green' : 'red' }}">
                    {{ $resultado >= 0 ? '+' : '' }}R$ {{ number_format($resultado, 2, ',', '.') }}
                </td>
                <td class="right" style="color:#555;">
                    {{ $resultadoAnterior >= 0 ? '+' : '' }}R$ {{ number_format($resultadoAnterior, 2, ',', '.') }}
                </td>
                <td class="right {{ $varResultado !== null && $varResultado >= 0 ? 'green' : 'red' }}">
                    @if($varResultado !== null){{ $varResultado >= 0 ? '+' : '' }}{{ number_format($varResultado, 1) }}%@endif
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Receitas e Despesas por Categoria --}}
    <div class="section-title">Receitas por Categoria</div>
    <table>
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="right">Valor (R$)</th>
                <th class="right">% do Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receitasPorCategoria as $cat => $valor)
            @php $pct = $totalReceitas > 0 ? ($valor / $totalReceitas) * 100 : 0; @endphp
            <tr>
                <td>{{ $cat }}</td>
                <td class="right green">R$ {{ number_format($valor, 2, ',', '.') }}</td>
                <td class="right">{{ number_format($pct, 1) }}%</td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center; color:#888; padding:12px;">Nenhuma receita recebida neste período.</td></tr>
            @endforelse
        </tbody>
        @if($receitasPorCategoria->isNotEmpty())
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="right green">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</td>
                <td class="right">100%</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="section-title">Despesas por Categoria</div>
    <table>
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="right">Valor (R$)</th>
                <th class="right">% do Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($despesasPorCategoria as $cat => $valor)
            @php $pct = $totalDespesas > 0 ? ($valor / $totalDespesas) * 100 : 0; @endphp
            <tr>
                <td>{{ $cat }}</td>
                <td class="right red">R$ {{ number_format($valor, 2, ',', '.') }}</td>
                <td class="right">{{ number_format($pct, 1) }}%</td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center; color:#888; padding:12px;">Nenhuma despesa paga neste período.</td></tr>
            @endforelse
        </tbody>
        @if($despesasPorCategoria->isNotEmpty())
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="right red">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</td>
                <td class="right">100%</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y \à\s H:i') }} — Financ Privus
    </div>

</body>
</html>
