<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoVinculado extends Model
{
    use SoftDeletes;

    protected $table = 'pedidos_vinculados';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'origem',
        'origem_id',
        'numero_pedido',
        'status',
        'status_origem',
        'valor_total',
        'valor_custo_total',
        'desconto',
        'data_pedido',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'valor_total'       => 'decimal:2',
            'valor_custo_total' => 'decimal:2',
            'desconto'          => 'decimal:2',
            'data_pedido'       => 'date',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeDoEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Gross margin percentage: ((valor_total - valor_custo_total) / valor_total) * 100
     * Returns 0 when valor_total is zero to avoid division by zero.
     */
    public function getMargemPercentualAttribute(): float
    {
        $valorTotal = (float) $this->valor_total;

        if ($valorTotal <= 0) {
            return 0.0;
        }

        return (($valorTotal - (float) $this->valor_custo_total) / $valorTotal) * 100;
    }

    // -------------------------------------------------------------------------
    // Business logic
    // -------------------------------------------------------------------------

    /**
     * Recalculate valor_total and valor_custo_total by summing all items and save.
     */
    public function recalcularTotais(): void
    {
        $this->itens()->each(function (PedidoItem $item): void {
            $item->valor_total = round($item->quantidade * $item->valor_unitario, 2);
            $item->custo_total = round($item->quantidade * $item->custo_unitario, 2);
            $item->saveQuietly();
        });

        $this->valor_total       = $this->itens()->sum('valor_total');
        $this->valor_custo_total = $this->itens()->sum('custo_total');
        $this->save();
    }
}
