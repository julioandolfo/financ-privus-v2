<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>DFC — Demonstrativo de Fluxo de Caixa</title>
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
        <div class="title">DFC — Demonstrativo de Fluxo de Caixa</div>
        <div class="period">Período: {{ \Carbon\Carbon::parse($de)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($ate)->format('d/m/Y') }}</div>
    </div>

    {{-- Resumo --}}
    <table>
        <thead>
            <tr>
                <th>Total Entradas</th>
                <th class="right">Total Saídas</th>
                <th class="right">Saldo do Período</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="green" style="font-size:13px; font-weight:bold;">+ R$ {{ number_format($totalEntradas, 2, ',', '.') }}</td>
                <td class="right red" style="font-size:13px; font-weight:bold;">− R$ {{ number_format($totalSaidas, 2, ',', '.') }}</td>
                <td class="right {{ $saldoPeriodo >= 0 ? 'green' : 'red' }}" style="font-size:13px; font-weight:bold;">
                    {{ $saldoPeriodo >= 0 ? '+' : '' }}R$ {{ number_format($saldoPeriodo, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Entradas --}}
    <div class="section-title">Entradas</div>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Cliente</th>
                <th class="right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recebimentos as $r)
            <tr>
                <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($r->data_recebimento)->format('d/m/Y') }}</td>
                <td>{{ $r->descricao }}</td>
                <td>{{ $r->cliente?->nome ?? '—' }}</td>
                <td class="right green" style="font-weight:bold;">R$ {{ number_format($r->valor_recebido, 2, ',', '.') }}</td>
            </tr>
            @empty
            @endforelse
            @foreach($movimentacoes->where('tipo', 'entrada') as $mov)
            <tr>
                <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($mov->data_movimentacao)->format('d/m/Y') }}</td>
                <td>{{ $mov->descricao }}</td>
                <td>{{ $mov->categoria?->nome ?? '—' }}</td>
                <td class="right green" style="font-weight:bold;">R$ {{ number_format($mov->valor, 2, ',', '.') }}</td>
            </tr>
            @endforeach
            @if($recebimentos->isEmpty() && $movimentacoes->where('tipo','entrada')->isEmpty())
            <tr><td colspan="4" style="text-align:center; color:#888; padding:12px;">Nenhuma entrada no período.</td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total Entradas</td>
                <td class="right green">+ R$ {{ number_format($totalEntradas, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Saídas --}}
    <div class="section-title">Saídas</div>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Fornecedor</th>
                <th class="right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pagamentos as $p)
            <tr>
                <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($p->data_pagamento)->format('d/m/Y') }}</td>
                <td>{{ $p->descricao }}</td>
                <td>{{ $p->fornecedor?->nome ?? '—' }}</td>
                <td class="right red" style="font-weight:bold;">R$ {{ number_format($p->valor_pago, 2, ',', '.') }}</td>
            </tr>
            @empty
            @endforelse
            @foreach($movimentacoes->where('tipo', 'saida') as $mov)
            <tr>
                <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($mov->data_movimentacao)->format('d/m/Y') }}</td>
                <td>{{ $mov->descricao }}</td>
                <td>{{ $mov->categoria?->nome ?? '—' }}</td>
                <td class="right red" style="font-weight:bold;">R$ {{ number_format($mov->valor, 2, ',', '.') }}</td>
            </tr>
            @endforeach
            @if($pagamentos->isEmpty() && $movimentacoes->where('tipo','saida')->isEmpty())
            <tr><td colspan="4" style="text-align:center; color:#888; padding:12px;">Nenhuma saída no período.</td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total Saídas</td>
                <td class="right red">− R$ {{ number_format($totalSaidas, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y \à\s H:i') }} — Financ Privus
    </div>

</body>
</html>
