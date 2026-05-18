<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppRegra extends Model
{
    protected $table = 'whatsapp_regras';

    protected $fillable = [
        'empresa_id',
        'evolution_config_id',
        'nome',
        'tipo',
        'periodicidade',
        'hora_envio',
        'dia_semana',
        'dia_mes',
        'ativo',
        'ultimo_envio',
    ];

    protected function casts(): array
    {
        return [
            'ativo'        => 'boolean',
            'ultimo_envio' => 'datetime',
            'dia_semana'   => 'integer',
            'dia_mes'      => 'integer',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function evolutionConfig(): BelongsTo
    {
        return $this->belongsTo(EvolutionConfig::class, 'evolution_config_id');
    }

    public function destinatarios(): HasMany
    {
        return $this->hasMany(WhatsAppDestinatario::class, 'regra_id');
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'vencimentos'  => 'Vencimentos',
            'fluxo_caixa'  => 'Fluxo de Caixa',
            'dre'          => 'DRE',
            'recorrencias' => 'Recorrências',
            'cobranca'     => 'Cobrança',
            default        => $this->tipo,
        };
    }

    public function getPeriodicidadeLabelAttribute(): string
    {
        return match ($this->periodicidade) {
            'diario'  => 'Diário',
            'semanal' => 'Semanal',
            'mensal'  => 'Mensal',
            default   => $this->periodicidade,
        };
    }
}
