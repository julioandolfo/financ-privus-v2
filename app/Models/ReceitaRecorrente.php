<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ReceitaRecorrente extends Model
{
    protected $table = 'receitas_recorrentes';

    protected $fillable = [
        'empresa_id', 'user_id', 'cliente_id', 'categoria_id', 'centro_custo_id',
        'forma_pagamento_id', 'conta_bancaria_id', 'descricao', 'valor',
        'frequencia', 'dia_mes', 'dia_semana', 'intervalo_dias',
        'data_inicio', 'data_fim', 'max_ocorrencias', 'ocorrencias_geradas',
        'proxima_geracao', 'ultima_geracao', 'antecedencia_dias',
        'status_inicial', 'criar_automaticamente', 'ajuste_fim_semana', 'ativo', 'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'valor'               => 'decimal:2',
            'data_inicio'         => 'date',
            'data_fim'            => 'date',
            'proxima_geracao'     => 'date',
            'ultima_geracao'      => 'date',
            'criar_automaticamente' => 'boolean',
            'ativo'               => 'boolean',
        ];
    }

    public function empresa(): BelongsTo     { return $this->belongsTo(Empresa::class); }
    public function user(): BelongsTo        { return $this->belongsTo(User::class); }
    public function cliente(): BelongsTo     { return $this->belongsTo(Cliente::class); }
    public function categoria(): BelongsTo   { return $this->belongsTo(CategoriaFinanceira::class, 'categoria_id'); }
    public function centroCusto(): BelongsTo { return $this->belongsTo(CentroCusto::class, 'centro_custo_id'); }
    public function contaBancaria(): BelongsTo { return $this->belongsTo(ContaBancaria::class, 'conta_bancaria_id'); }
    public function contasReceber(): HasMany { return $this->hasMany(ContaReceber::class); }

    public function calcularProximaGeracao(Carbon $base = null): Carbon
    {
        $base = $base ?? Carbon::parse($this->proxima_geracao ?? $this->data_inicio);

        $next = match ($this->frequencia) {
            'diaria'       => $base->copy()->addDay(),
            'semanal'      => $base->copy()->addWeek(),
            'quinzenal'    => $base->copy()->addDays(15),
            'bimestral'    => $base->copy()->addMonths(2),
            'trimestral'   => $base->copy()->addMonths(3),
            'semestral'    => $base->copy()->addMonths(6),
            'anual'        => $base->copy()->addYear(),
            'personalizado'=> $base->copy()->addDays($this->intervalo_dias ?? 30),
            default        => $base->copy()->addMonth(),
        };

        if ($this->frequencia === 'mensal' && $this->dia_mes) {
            $next->day(min($this->dia_mes, $next->daysInMonth));
        }

        return $this->ajustarFimSemana($next);
    }

    private function ajustarFimSemana(Carbon $date): Carbon
    {
        if ($this->ajuste_fim_semana === 'manter' || !$date->isWeekend()) {
            return $date;
        }
        return $this->ajuste_fim_semana === 'antecipar'
            ? $date->previous(Carbon::FRIDAY)
            : $date->next(Carbon::MONDAY);
    }

    public function getFrequenciaLabelAttribute(): string
    {
        return match ($this->frequencia) {
            'diaria'       => 'Diária',
            'semanal'      => 'Semanal',
            'quinzenal'    => 'Quinzenal',
            'mensal'       => 'Mensal',
            'bimestral'    => 'Bimestral',
            'trimestral'   => 'Trimestral',
            'semestral'    => 'Semestral',
            'anual'        => 'Anual',
            'personalizado'=> 'Personalizado',
            default        => ucfirst($this->frequencia),
        };
    }

    public function scopeAtivas($query)  { return $query->where('ativo', true); }
    public function scopeParaGerar($query)
    {
        return $query->where('ativo', true)
            ->where('criar_automaticamente', true)
            ->where('proxima_geracao', '<=', today()->addDays(5))
            ->where(fn($q) => $q->whereNull('data_fim')->orWhere('data_fim', '>=', today()))
            ->where(fn($q) => $q->whereNull('max_ocorrencias')
                ->orWhereColumn('ocorrencias_geradas', '<', 'max_ocorrencias'));
    }
}
