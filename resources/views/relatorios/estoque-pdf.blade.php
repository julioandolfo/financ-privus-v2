<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Estoque de Produtos</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; padding: 30px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1a1a1a; padding-bottom: 12px; }
        .header .company { font-size: 14px; font-weight: bold; color: #111; }
        .header .title { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .header .period { font-size: 11px; color: #555; margin-top: 4px; }
        .green { color: #16a34a; }
        .red { color: #dc2626; }
        .orange { color: #ea580c; }
        .section-title { font-size: 12px; font-weight: bold; color: #333; margin: 20px 0 8px 0; padding-bottom: 4px; border-bottom: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #f3f4f6; padding: 7px 10px; text-align: left; font-size: 10px; text-transform: uppercase; color: #555; font-weight: bold; border: 1px solid #ddd; }
        thead th.right { text-align: right; }
        thead th.center { text-align: center; }
        tbody td { padding: 6px 10px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: middle; }
        tbody td.right { text-align: right; }
        tbody td.center { text-align: center; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody tr.low-stock td { background: #fef2f2; }
        tfoot td { padding: 7px 10px; font-weight: bold; border: 1px solid #ddd; background: #f3f4f6; font-size: 11px; }
        tfoot td.right { text-align: right; }
        .summary-row { display: table; width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-cell { display: table-cell; width: 25%; padding: 10px 12px; border: 1px solid #ddd; vertical-align: top; }
        .summary-label { font-size: 9px; text-transform: uppercase; color: #666; font-weight: bold; margin-bottom: 4px; }
        .summary-value { font-size: 15px; font-weight: bold; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-ok { background: #dcfce7; color: #15803d; }
        .badge-low { background: #fee2e2; color: #b91c1c; }
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
        <div class="title">Relatório de Estoque de Produtos</div>
        <div class="period">Gerado em {{ now()->format('d/m/Y \à\s H:i') }}</div>
    </div>

    {{-- Resumo --}}
    @php
        $totalProdutos   = $produtos->count();
        $estoqueBaixo    = $produtos->filter(fn($p) => ($p->estoque ?? 0) <= ($p->estoque_minimo ?? 0))->count();
    @endphp
    <div class="summary-row">
        <div class="summary-cell">
            <div class="summary-label">Total de Produtos</div>
            <div class="summary-value">{{ $totalProdutos }}</div>
        </div>
        <div class="summary-cell">
            <div class="summary-label">Estoque Baixo</div>
            <div class="summary-value {{ $estoqueBaixo > 0 ? 'red' : '' }}">{{ $estoqueBaixo }}</div>
        </div>
        <div class="summary-cell">
            <div class="summary-label">Valor em Estoque (custo)</div>
            <div class="summary-value">R$ {{ number_format($valorEstoque, 2, ',', '.') }}</div>
        </div>
        <div class="summary-cell">
            <div class="summary-label">Valor a Venda</div>
            <div class="summary-value green">R$ {{ number_format($valorVendaTotal, 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- Tabela de Produtos --}}
    <div class="section-title">Posição de Estoque</div>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Categoria</th>
                <th class="right">Estoque</th>
                <th class="right">Mínimo</th>
                <th class="center">Status</th>
                <th class="right">Custo Unit.</th>
                <th class="right">Preço Venda</th>
                <th class="right">Valor Estoque</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produtos as $produto)
            @php
                $baixo = ($produto->estoque ?? 0) <= ($produto->estoque_minimo ?? 0);
                $valorItem = ($produto->estoque ?? 0) * ($produto->custo_unitario ?? 0);
            @endphp
            <tr class="{{ $baixo ? 'low-stock' : '' }}">
                <td style="font-weight: {{ $baixo ? 'bold' : 'normal' }};">{{ $produto->nome }}</td>
                <td style="color: #555;">{{ $produto->categoria?->nome ?? '—' }}</td>
                <td class="right {{ $baixo ? 'red' : '' }}" style="{{ $baixo ? 'font-weight:bold;' : '' }}">
                    {{ number_format($produto->estoque ?? 0, 0, ',', '.') }}
                </td>
                <td class="right" style="color: #555;">{{ number_format($produto->estoque_minimo ?? 0, 0, ',', '.') }}</td>
                <td class="center">
                    <span class="badge {{ $baixo ? 'badge-low' : 'badge-ok' }}">{{ $baixo ? 'Baixo' : 'OK' }}</span>
                </td>
                <td class="right">R$ {{ number_format($produto->custo_unitario ?? 0, 2, ',', '.') }}</td>
                <td class="right">R$ {{ number_format($produto->preco_venda ?? 0, 2, ',', '.') }}</td>
                <td class="right" style="font-weight: bold;">R$ {{ number_format($valorItem, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #888; padding: 12px;">Nenhum produto cadastrado.</td>
            </tr>
            @endforelse
        </tbody>
        @if($produtos->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="7">Total Valor em Estoque (custo)</td>
                <td class="right">R$ {{ number_format($valorEstoque, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="7">Total Valor a Venda</td>
                <td class="right green">R$ {{ number_format($valorVendaTotal, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y \à\s H:i') }} — Financ Privus
    </div>

</body>
</html>
