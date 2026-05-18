<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransacaoPendente extends Model
{
    protected $table = 'transacoes_pendentes';

    protected $fillable = [
        'empresa_id',
        'conta_bancaria_id',
        'tipo',
        'valor',
        'data_transacao',
        'descricao_original',
        'descricao_normalizada',
        'status',
        'categoria_sugerida_id',
        'conta_pagar_id',
        'conta_receber_id',
        'aprovada_por',
        'aprovada_em',
        'observacao',
        'origem',
    ];

    protected function casts(): array
    {
        return [
            'valor'        => 'decimal:2',
            'data_transacao' => 'date',
            'aprovada_em'  => 'datetime',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function contaBancaria(): BelongsTo
    {
        return $this->belongsTo(ContaBancaria::class, 'conta_bancaria_id');
    }

    public function categoriaSugerida(): BelongsTo
    {
        return $this->belongsTo(CategoriaFinanceira::class, 'categoria_sugerida_id');
    }

    public function contaPagar(): BelongsTo
    {
        return $this->belongsTo(ContaPagar::class, 'conta_pagar_id');
    }

    public function contaReceber(): BelongsTo
    {
        return $this->belongsTo(ContaReceber::class, 'conta_receber_id');
    }

    public function aprovadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovada_por');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeAprovadas($query)
    {
        return $query->where('status', 'aprovada');
    }

    public function scopeIgnoradas($query)
    {
        return $query->where('status', 'ignorada');
    }

    public function getIsDebitoAttribute(): bool
    {
        return $this->tipo === 'debito';
    }

    public function getIsCreditoAttribute(): bool
    {
        return $this->tipo === 'credito';
    }
}
