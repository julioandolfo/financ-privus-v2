<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExtratoBancario extends Model
{
    protected $table = 'extratos_bancarios';

    protected $fillable = [
        'empresa_id', 'conta_bancaria_id', 'nome_arquivo', 'tipo',
        'data_inicio', 'data_fim',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim'    => 'date',
        ];
    }

    public function empresa(): BelongsTo     { return $this->belongsTo(Empresa::class); }
    public function contaBancaria(): BelongsTo { return $this->belongsTo(ContaBancaria::class); }
    public function lancamentos(): HasMany   { return $this->hasMany(ExtratoLancamento::class, 'extrato_id'); }

    public function getTotalAttribute(): int        { return $this->lancamentos()->count(); }
    public function getConciliadosAttribute(): int  { return $this->lancamentos()->where('conciliado', true)->count(); }
    public function getPendentesAttribute(): int    { return $this->lancamentos()->where('conciliado', false)->where('ignorado', false)->count(); }
    public function getIgnoradosAttribute(): int    { return $this->lancamentos()->where('ignorado', true)->count(); }
}
