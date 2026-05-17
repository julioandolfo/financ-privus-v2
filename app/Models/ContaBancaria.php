<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContaBancaria extends Model
{
    protected $table = 'contas_bancarias';

    protected $fillable = [
        'empresa_id', 'nome', 'banco_codigo', 'banco_nome',
        'agencia', 'conta', 'tipo_conta', 'saldo_inicial', 'saldo_atual', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'saldo_inicial' => 'decimal:2',
            'saldo_atual' => 'decimal:2',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(MovimentacaoCaixa::class);
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }
}
