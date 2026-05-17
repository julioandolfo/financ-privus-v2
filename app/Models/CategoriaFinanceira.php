<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaFinanceira extends Model
{
    protected $table = 'categorias_financeiras';

    protected $fillable = ['empresa_id', 'codigo', 'nome', 'tipo', 'categoria_pai_id', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function pai(): BelongsTo
    {
        return $this->belongsTo(CategoriaFinanceira::class, 'categoria_pai_id');
    }

    public function filhas(): HasMany
    {
        return $this->hasMany(CategoriaFinanceira::class, 'categoria_pai_id');
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeReceitas($query)
    {
        return $query->whereIn('tipo', ['receita', 'ambos']);
    }

    public function scopeDespesas($query)
    {
        return $query->whereIn('tipo', ['despesa', 'ambos']);
    }
}
