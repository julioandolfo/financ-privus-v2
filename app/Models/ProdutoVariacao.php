<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdutoVariacao extends Model
{
    protected $table = 'produto_variacoes';

    protected $fillable = [
        'produto_id',
        'atributo',
        'valor',
        'sku',
        'preco_adicional',
        'custo',
        'estoque',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'preco_adicional' => 'decimal:2',
            'custo'           => 'decimal:2',
            'estoque'         => 'decimal:3',
            'ativo'           => 'boolean',
        ];
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
