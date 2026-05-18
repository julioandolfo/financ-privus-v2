<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerfilConsolidacao extends Model
{
    protected $table = 'perfis_consolidacao';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'nome',
        'descricao',
        'configuracao',
        'publico',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'configuracao' => 'array',
            'publico'      => 'boolean',
            'ativo'        => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
