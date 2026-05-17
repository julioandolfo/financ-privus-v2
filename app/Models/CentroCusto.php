<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CentroCusto extends Model
{
    protected $table = 'centros_custo';

    protected $fillable = ['empresa_id', 'codigo', 'nome', 'centro_pai_id', 'ativo'];

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
        return $this->belongsTo(CentroCusto::class, 'centro_pai_id');
    }

    public function filhos(): HasMany
    {
        return $this->hasMany(CentroCusto::class, 'centro_pai_id');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
