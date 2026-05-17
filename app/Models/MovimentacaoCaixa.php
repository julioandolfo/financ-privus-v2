<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimentacaoCaixa extends Model
{
    protected $table = 'movimentacoes_caixa';

    protected $fillable = [
        'empresa_id', 'conta_bancaria_id', 'categoria_id', 'centro_custo_id',
        'forma_pagamento_id', 'user_id', 'tipo', 'descricao', 'valor',
        'data_movimentacao', 'data_competencia', 'conciliado',
        'referencia_tipo', 'referencia_id', 'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'valor'             => 'decimal:2',
            'data_movimentacao' => 'date',
            'data_competencia'  => 'date',
            'conciliado'        => 'boolean',
        ];
    }

    public function empresa(): BelongsTo      { return $this->belongsTo(Empresa::class); }
    public function contaBancaria(): BelongsTo { return $this->belongsTo(ContaBancaria::class, 'conta_bancaria_id'); }
    public function categoria(): BelongsTo    { return $this->belongsTo(CategoriaFinanceira::class, 'categoria_id'); }
    public function centroCusto(): BelongsTo  { return $this->belongsTo(CentroCusto::class, 'centro_custo_id'); }
    public function user(): BelongsTo         { return $this->belongsTo(User::class); }

    public function scopeEntradas($query) { return $query->where('tipo', 'entrada'); }
    public function scopeSaidas($query)   { return $query->where('tipo', 'saida'); }
}
