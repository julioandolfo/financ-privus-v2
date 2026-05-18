<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório de Inadimplência</title>
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
        thead th.center { text-align: center; }
        tbody td { padding: 6px 10px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: middle; }
        tbody td.right { text-align: right; }
        tbody td.center { text-align: center; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tfoot td { padding: 7px 10px; font-weight: bold; border: 1px solid #ddd; background: #f3f4f6; font-size: 11px; }
        tfoot td.right { text-align: right; }
        .green { color: #16a34a; }
        .red { color: #dc2626; }
        .orange { color: #ea580c; }
        .summary-row { display: table; width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-cell { display: table-cell; width: 33.33%; padding: 10px 12px; border: 1px solid #ddd; vertical-align: top; }
        .summary-label { font-size: 9px; text-transform: uppercase; color: #666; font-weight: bold; margin-bottom: 4px; }
        .summary-value { font-size: 14px; font-weight: bold; }
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
        <div class="title">Relatório de Inadimplência</div>
        <div class="period">Período: {{ \Carbon\Carbon::parse($de)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($ate)->format('d/m/Y') }}</div>
    </div>

    {{-- Resumo --}}
    <table>
        <thead>
            <tr>
                <th>Total em Aberto</th>
                <th class="right">Total Vencido</th>
                <th class="right">Total Recebido</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="red" style="font-size:13px; font-weight:bold;">R$ {{ number_format($totalAberto, 2, ',', '.') }}</td>
                <td class="right orange" style="font-size:13px; font-weight:bold;">R$ {{ number_format($total, 2, ',', '.') }}</td>
                <td class="right green" style="font-size:13px; font-weight:bold;">R$ {{ number_format($totalRecebido, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Títulos Vencidos --}}
    <div class="section-title">Títulos Vencidos</div>
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Descrição</th>
                <th class="right">Vencimento</th>
                <th class="right">Dias em Atraso</th>
                <th class="right">Valor Total</th>
                <th class="right">Valor em Aberto</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contas as $conta)
            @php $diasAtraso = now()->diffInDays($conta->data_vencimento); @endphp
            <tr>
                <td>{{ $conta->cliente?->nome ?? '—' }}</td>
                <td>{{ $conta->descricao }}</td>
                <td class="right">{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</td>
                <td class="right {{ $diasAtraso > 30 ? 'red' : 'orange' }}" style="font-weight:bold;">{{ $diasAtraso }}d</td>
                <td class="right">R$ {{ number_format($conta->valor_total, 2, ',', '.') }}</td>
                <td class="right red" style="font-weight:bold;">R$ {{ number_format($conta->valor_total - $conta->valor_recebido, 2, ',', '.') }}</td>
                <td class="center">{{ ucfirst($conta->status) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#888; padding:16px;">Nenhum título inadimplente encontrado.</td>
            </tr>
            @endforelse
        </tbody>
        @if($contas->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="4">Total</td>
                <td class="right">R$ {{ number_format($total, 2, ',', '.') }}</td>
                <td class="right red">R$ {{ number_format($totalAberto, 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y \à\s H:i') }} — Financ Privus
    </div>

</body>
</html>
