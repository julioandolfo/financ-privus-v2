<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtratoLancamento extends Model
{
    protected $table = 'extratos_lancamentos';

    protected $fillable = [
        'extrato_id', 'fitid', 'data_lancamento', 'valor', 'tipo',
        'descricao', 'movimentacao_id', 'conciliado', 'ignorado',
    ];

    protected function casts(): array
    {
        return [
            'data_lancamento' => 'date',
            'valor'           => 'decimal:2',
            'conciliado'      => 'boolean',
            'ignorado'        => 'boolean',
        ];
    }

    public function extrato(): BelongsTo      { return $this->belongsTo(ExtratoBancario::class); }
    public function movimentacao(): BelongsTo { return $this->belongsTo(MovimentacaoCaixa::class); }

    public function isPendente(): bool { return !$this->conciliado && !$this->ignorado; }
}
