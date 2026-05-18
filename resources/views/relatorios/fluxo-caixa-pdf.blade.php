<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fluxo de Caixa</title>
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
        .gray { color: #555; }
        .summary-row { display: table; width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-cell { display: table-cell; width: 25%; padding: 10px 12px; border: 1px solid #ddd; vertical-align: top; }
        .summary-label { font-size: 9px; text-transform: uppercase; color: #666; font-weight: bold; margin-bottom: 4px; }
        .summary-value { font-size: 13px; font-weight: bold; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 9px; color: #888; text-align: center; }
        @php
            $agrupamentoLabel = match($agrupamento) {
                'mes'    => 'por Mês',
                'semana' => 'por Semana',
                default  => 'por Dia',
            };
        @endphp
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
        <div class="title">Fluxo de Caixa</div>
        <div class="period">
            Período: {{ $de->format('d/m/Y') }} até {{ $ate->format('d/m/Y') }}
            &nbsp;·&nbsp;
            Agrupamento:
            @php
                $agrupamentoLabel = match($agrupamento) { 'mes' => 'Mensal', 'semana' => 'Semanal', default => 'Diário' };
            @endphp
            {{ $agrupamentoLabel }}
        </div>
    </div>

    {{-- Resumo --}}
    <table>
        <thead>
            <tr>
                <th>Saldo Inicial</th>
                <th class="right">Total Entradas</th>
                <th class="right">Total Saídas</th>
                <th class="right">Saldo do Período</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-size:13px; font-weight:bold; {{ $saldoAnterior < 0 ? 'color:#dc2626;' : '' }}">
                    R$ {{ number_format($saldoAnterior, 2, ',', '.') }}
                </td>
                <td class="right green" style="font-size:13px; font-weight:bold;">
                    + R$ {{ number_format($totalEntradas, 2, ',', '.') }}
                </td>
                <td class="right red" style="font-size:13px; font-weight:bold;">
                    − R$ {{ number_format($totalSaidas, 2, ',', '.') }}
                </td>
                <td class="right {{ $saldoPeriodo >= 0 ? 'green' : 'red' }}" style="font-size:13px; font-weight:bold;">
                    {{ $saldoPeriodo >= 0 ? '+' : '' }}R$ {{ number_format($saldoPeriodo, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Tabela por período --}}
    <div class="section-title">Movimentações por Período</div>
    <table>
        <thead>
            <tr>
                <th>Período</th>
                <th class="right">Entradas</th>
                <th class="right">Saídas</th>
                <th class="right">Saldo do Período</th>
                <th class="right">Saldo Acumulado</th>
                <th class="right">Lançamentos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($periodos as $p)
            @php $saldo = $p->entradas - $p->saidas; @endphp
            <tr>
                <td style="font-weight:500;">{{ $p->periodo }}</td>
                <td class="right green">+ R$ {{ number_format($p->entradas, 2, ',', '.') }}</td>
                <td class="right red">− R$ {{ number_format($p->saidas, 2, ',', '.') }}</td>
                <td class="right {{ $saldo >= 0 ? 'green' : 'red' }}" style="font-weight:bold;">
                    {{ $saldo >= 0 ? '+' : '' }}R$ {{ number_format($saldo, 2, ',', '.') }}
                </td>
                <td class="right {{ $p->saldo_acumulado >= 0 ? '' : 'red' }}" style="font-weight:bold;">
                    R$ {{ number_format($p->saldo_acumulado, 2, ',', '.') }}
                </td>
                <td class="right gray">{{ $p->total_lancamentos }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; color:#888; padding:16px;">
                    Nenhuma movimentação no período selecionado.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($periodos->isNotEmpty())
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="right green">+ R$ {{ number_format($totalEntradas, 2, ',', '.') }}</td>
                <td class="right red">− R$ {{ number_format($totalSaidas, 2, ',', '.') }}</td>
                <td class="right {{ $saldoPeriodo >= 0 ? 'green' : 'red' }}">
                    {{ $saldoPeriodo >= 0 ? '+' : '' }}R$ {{ number_format($saldoPeriodo, 2, ',', '.') }}
                </td>
                <td class="right">—</td>
                <td class="right gray">{{ $periodos->sum('total_lancamentos') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y \à\s H:i') }} — Financ Privus
    </div>

</body>
</html>
