<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    use SoftDeletes;

    protected $table = 'produtos';

    protected $fillable = [
        'empresa_id', 'user_id', 'categoria_id',
        'codigo', 'sku', 'codigo_barras', 'nome', 'descricao',
        'custo_unitario', 'preco_venda', 'unidade_medida',
        'estoque', 'estoque_minimo',
        'ncm', 'cest', 'cfop', 'aliquota_icms', 'aliquota_ipi',
        'aliquota_pis', 'aliquota_cofins', 'origem_fiscal', 'tipo',
        'woo_id', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'custo_unitario' => 'decimal:4',
            'preco_venda'    => 'decimal:4',
            'estoque'        => 'decimal:3',
            'estoque_minimo' => 'decimal:3',
            'ativo'          => 'boolean',
        ];
    }

    public function empresa(): BelongsTo   { return $this->belongsTo(Empresa::class); }
    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
    public function categoria(): BelongsTo { return $this->belongsTo(CategoriaProduto::class, 'categoria_id'); }

    public function getMargemAttribute(): float
    {
        if ($this->preco_venda <= 0) {
            return 0;
        }
        return (($this->preco_venda - $this->custo_unitario) / $this->preco_venda) * 100;
    }

    public function isEstoqueBaixo(): bool
    {
        return $this->estoque <= $this->estoque_minimo;
    }

    public function scopeAtivos($query)   { return $query->where('ativo', true); }
    public function scopeServicos($query) { return $query->where('tipo', 'servico'); }
    public function scopeProdutos($query) { return $query->where('tipo', 'produto'); }
}
