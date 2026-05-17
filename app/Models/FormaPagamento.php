<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormaPagamento extends Model
{
    protected $table = 'formas_pagamento';

    protected $fillable = ['empresa_id', 'codigo', 'nome', 'tipo', 'padrao', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean', 'padrao' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePagamento($query)
    {
        return $query->whereIn('tipo', ['pagamento', 'ambos']);
    }

    public function scopeRecebimento($query)
    {
        return $query->whereIn('tipo', ['recebimento', 'ambos']);
    }
}
