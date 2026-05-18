<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Margem e Lucratividade</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; padding: 30px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1a1a1a; padding-bottom: 12px; }
        .header .company { font-size: 14px; font-weight: bold; color: #111; }
        .header .title { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .header .period { font-size: 11px; color: #555; margin-top: 4px; }
        .green { color: #16a34a; }
        .red { color: #dc2626; }
        .blue { color: #4f46e5; }
        .section-title { font-size: 12px; font-weight: bold; color: #333; margin: 20px 0 8px 0; padding-bottom: 4px; border-bottom: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #f3f4f6; padding: 7px 10px; text-align: left; font-size: 10px; text-transform: uppercase; color: #555; font-weight: bold; border: 1px solid #ddd; }
        thead th.right { text-align: right; }
        thead th.center { text-align: center; }
        tbody td { padding: 6px 10px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: middle; }
        tbody td.right { text-align: right; }
        tbody td.center { text-align: center; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tfoot td { padding: 7px 10px; font-weight: bold; border: 1px solid #ddd; background: #f3f4f6; font-size: 11px; }
        tfoot td.right { text-align: right; }
        .summary-row { display: table; width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-cell { display: table-cell; width: 25%; padding: 10px 12px; border: 1px solid #ddd; vertical-align: top; }
        .summary-label { font-size: 9px; text-transform: uppercase; color: #666; font-weight: bold; margin-bottom: 4px; }
        .summary-value { font-size: 15px; font-weight: bold; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 9px; color: #888; text-align: center; }
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
        <div class="title">Margem e Lucratividade</div>
        <div class="period">Período: {{ \Carbon\Carbon::parse($de)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($ate)->format('d/m/Y') }}</div>
    </div>

    {{-- Resumo --}}
    <div class="summary-row">
        <div class="summary-cell">
            <div class="summary-label">Total Receitas</div>
            <div class="summary-value green">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</div>
        </div>
        <div class="summary-cell">
            <div class="summary-label">Total Despesas</div>
            <div class="summary-value red">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</div>
        </div>
        <div class="summary-cell">
            <div class="summary-label">Lucro Líquido</div>
            <div class="summary-value {{ $lucroLiquido >= 0 ? 'blue' : 'red' }}">
                {{ $lucroLiquido >= 0 ? '+' : '' }}R$ {{ number_format($lucroLiquido, 2, ',', '.') }}
            </div>
        </div>
        <div class="summary-cell">
            <div class="summary-label">Margem Líquida</div>
            <div class="summary-value {{ $margem >= 0 ? 'blue' : 'red' }}">{{ number_format($margem, 1) }}%</div>
        </div>
    </div>

    {{-- Receitas por Categoria --}}
    <div class="section-title">Receitas por Categoria</div>
    <table>
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="center">Qtd</th>
                <th class="right">Total (R$)</th>
                <th class="right">% do Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receitasPorCategoria as $cat)
            @php $pct = $totalReceitas > 0 ? ($cat['total'] / $totalReceitas) * 100 : 0; @endphp
            <tr>
                <td>{{ $cat['nome'] }}</td>
                <td class="center">{{ $cat['qtd'] }}</td>
                <td class="right green">R$ {{ number_format($cat['total'], 2, ',', '.') }}</td>
                <td class="right">{{ number_format($pct, 1) }}%</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center; color:#888; padding:12px;">Nenhuma receita recebida neste período.</td></tr>
            @endforelse
        </tbody>
        @if($receitasPorCategoria->isNotEmpty())
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="center">{{ $receitasPorCategoria->sum('qtd') }}</td>
                <td class="right green">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</td>
                <td class="right">100%</td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Despesas por Categoria --}}
    <div class="section-title">Despesas por Categoria</div>
    <table>
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="center">Qtd</th>
                <th class="right">Total (R$)</th>
                <th class="right">% do Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($despesasPorCategoria as $cat)
            @php $pct = $totalDespesas > 0 ? ($cat['total'] / $totalDespesas) * 100 : 0; @endphp
            <tr>
                <td>{{ $cat['nome'] }}</td>
                <td class="center">{{ $cat['qtd'] }}</td>
                <td class="right red">R$ {{ number_format($cat['total'], 2, ',', '.') }}</td>
                <td class="right">{{ number_format($pct, 1) }}%</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center; color:#888; padding:12px;">Nenhuma despesa paga neste período.</td></tr>
            @endforelse
        </tbody>
        @if($despesasPorCategoria->isNotEmpty())
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="center">{{ $despesasPorCategoria->sum('qtd') }}</td>
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
