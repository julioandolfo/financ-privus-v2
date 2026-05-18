<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdutoFoto extends Model
{
    protected $table = 'produto_fotos';

    protected $fillable = [
        'produto_id',
        'path',
        'nome_original',
        'principal',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'boolean',
            'ordem'     => 'integer',
        ];
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
