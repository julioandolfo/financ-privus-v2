<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoItem extends Model
{
    protected $table = 'pedidos_itens';

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'codigo_produto_origem',
        'nome_produto',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'custo_unitario',
        'custo_total',
    ];

    protected function casts(): array
    {
        return [
            'quantidade'     => 'decimal:3',
            'valor_unitario' => 'decimal:2',
            'valor_total'    => 'decimal:2',
            'custo_unitario' => 'decimal:2',
            'custo_total'    => 'decimal:2',
        ];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoVinculado::class, 'pedido_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    /**
     * Gross margin percentage for this line item.
     */
    public function getMargemPercentualAttribute(): float
    {
        $valorTotal = (float) $this->valor_total;

        if ($valorTotal <= 0) {
            return 0.0;
        }

        return (($valorTotal - (float) $this->custo_total) / $valorTotal) * 100;
    }
}
