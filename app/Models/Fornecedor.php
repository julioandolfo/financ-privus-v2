<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fornecedor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'empresa_id', 'codigo', 'tipo', 'nome_razao_social', 'nome_fantasia',
        'cpf_cnpj', 'email', 'telefone', 'celular', 'endereco', 'observacoes', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'endereco' => 'array',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function contasPagar(): HasMany
    {
        return $this->hasMany(ContaPagar::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function getNomeExibicaoAttribute(): string
    {
        return $this->nome_fantasia ?: $this->nome_razao_social;
    }
}
