<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParcelaReceber extends Model
{
    protected $table = 'parcelas_receber';

    protected $fillable = [
        'conta_receber_id', 'numero_parcela', 'valor_parcela', 'valor_recebido',
        'desconto', 'juros', 'multa', 'data_vencimento', 'data_recebimento',
        'status', 'forma_recebimento_id', 'conta_bancaria_id', 'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'valor_parcela'    => 'decimal:2',
            'valor_recebido'   => 'decimal:2',
            'desconto'         => 'decimal:2',
            'juros'            => 'decimal:2',
            'multa'            => 'decimal:2',
            'data_vencimento'  => 'date',
            'data_recebimento' => 'date',
        ];
    }

    public function contaReceber(): BelongsTo { return $this->belongsTo(ContaReceber::class); }
    public function formaRecebimento(): BelongsTo { return $this->belongsTo(FormaPagamento::class, 'forma_recebimento_id'); }
    public function contaBancaria(): BelongsTo { return $this->belongsTo(ContaBancaria::class, 'conta_bancaria_id'); }
}
