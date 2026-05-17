<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContaPagar extends Model
{
    use SoftDeletes;

    protected $table = 'contas_pagar';

    protected $fillable = [
        'empresa_id', 'fornecedor_id', 'categoria_id', 'centro_custo_id',
        'forma_pagamento_id', 'conta_bancaria_id', 'user_id',
        'numero_documento', 'descricao', 'valor_total', 'valor_pago',
        'desconto', 'juros', 'multa', 'data_vencimento', 'data_competencia',
        'data_pagamento', 'status', 'tem_rateio', 'observacoes', 'anexo',
    ];

    protected function casts(): array
    {
        return [
            'valor_total'     => 'decimal:2',
            'valor_pago'      => 'decimal:2',
            'desconto'        => 'decimal:2',
            'juros'           => 'decimal:2',
            'multa'           => 'decimal:2',
            'data_vencimento' => 'date',
            'data_competencia'=> 'date',
            'data_pagamento'  => 'date',
            'tem_rateio'      => 'boolean',
        ];
    }

    public function empresa(): BelongsTo   { return $this->belongsTo(Empresa::class); }
    public function fornecedor(): BelongsTo { return $this->belongsTo(Fornecedor::class); }
    public function categoria(): BelongsTo  { return $this->belongsTo(CategoriaFinanceira::class, 'categoria_id'); }
    public function centroCusto(): BelongsTo { return $this->belongsTo(CentroCusto::class, 'centro_custo_id'); }
    public function formaPagamento(): BelongsTo { return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id'); }
    public function contaBancaria(): BelongsTo { return $this->belongsTo(ContaBancaria::class, 'conta_bancaria_id'); }
    public function user(): BelongsTo       { return $this->belongsTo(User::class); }

    public function getValorAbertoAttribute(): float
    {
        return max(0, $this->valor_total - $this->valor_pago - $this->desconto);
    }

    public function scopePendentes($query)
    {
        return $query->whereIn('status', ['pendente', 'parcial', 'vencido']);
    }

    public function scopeVencidas($query)
    {
        return $query->where('data_vencimento', '<', today())->whereIn('status', ['pendente', 'parcial']);
    }

    public function scopeDoMes($query, $mes = null, $ano = null)
    {
        $mes = $mes ?? now()->month;
        $ano = $ano ?? now()->year;
        return $query->whereMonth('data_vencimento', $mes)->whereYear('data_vencimento', $ano);
    }
}
