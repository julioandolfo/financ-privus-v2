<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvolutionConfig extends Model
{
    protected $table = 'evolution_configs';

    protected $fillable = [
        'empresa_id',
        'nome',
        'provider',
        'base_url',
        'instance_name',
        'api_key',
        'numero_remetente',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function regras(): HasMany
    {
        return $this->hasMany(WhatsAppRegra::class, 'evolution_config_id');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeParaEmpresa($query, int $empresaId)
    {
        return $query->where(function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id');
        });
    }
}
