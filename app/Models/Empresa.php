<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo', 'razao_social', 'nome_fantasia', 'cnpj',
        'grupo_empresarial_id', 'ativo', 'configuracoes',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'configuracoes' => 'array',
        ];
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'grupo_empresarial_id');
    }

    public function filiais(): HasMany
    {
        return $this->hasMany(Empresa::class, 'grupo_empresarial_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public function fornecedores(): HasMany
    {
        return $this->hasMany(Fornecedor::class);
    }

    public function contasPagar(): HasMany
    {
        return $this->hasMany(ContaPagar::class);
    }

    public function contasReceber(): HasMany
    {
        return $this->hasMany(ContaReceber::class);
    }

    public function contasBancarias(): HasMany
    {
        return $this->hasMany(ContaBancaria::class);
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(CategoriaFinanceira::class);
    }

    public function centrosCusto(): HasMany
    {
        return $this->hasMany(CentroCusto::class);
    }

    public function getNomeExibicaoAttribute(): string
    {
        return $this->nome_fantasia ?: $this->razao_social;
    }
}
