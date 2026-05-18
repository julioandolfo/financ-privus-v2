<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boleto extends Model
{
    use SoftDeletes;

    protected $table = 'boletos';

    protected $fillable = [
        'empresa_id',
        'conta_receber_id',
        'cliente_id',
        'numero_boleto',
        'nosso_numero',
        'linha_digitavel',
        'codigo_barras',
        'url_boleto',
        'valor',
        'data_vencimento',
        'data_emissao',
        'data_pagamento',
        'status',
        'banco',
        'banco_referencia_id',
        'pix_qrcode',
        'pix_copia_cola',
        'instrucoes',
        'multa',
        'juros',
        'desconto',
    ];

    protected function casts(): array
    {
        return [
            'valor'          => 'decimal:2',
            'multa'          => 'decimal:2',
            'juros'          => 'decimal:2',
            'desconto'       => 'decimal:2',
            'data_vencimento' => 'date',
            'data_emissao'   => 'date',
            'data_pagamento' => 'date',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function contaReceber(): BelongsTo
    {
        return $this->belongsTo(ContaReceber::class, 'conta_receber_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function scopePendentes($query)
    {
        return $query->whereIn('status', ['emitido', 'rascunho']);
    }

    public function scopeVencidos($query)
    {
        return $query->where('data_vencimento', '<', today())
            ->whereNotIn('status', ['pago', 'cancelado']);
    }

    public function getEstaVencidoAttribute(): bool
    {
        return $this->data_vencimento->isPast()
            && ! in_array($this->status, ['pago', 'cancelado']);
    }
}
