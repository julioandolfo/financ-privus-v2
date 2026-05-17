<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContaReceber extends Model
{
    use SoftDeletes;

    protected $table = 'contas_receber';

    protected $fillable = [
        'empresa_id', 'cliente_id', 'categoria_id', 'centro_custo_id',
        'forma_recebimento_id', 'conta_bancaria_id', 'user_id',
        'numero_documento', 'descricao', 'valor_total', 'valor_recebido',
        'desconto', 'juros', 'multa', 'data_vencimento', 'data_competencia',
        'data_recebimento', 'status', 'num_parcelas', 'tem_rateio', 'observacoes', 'anexo',
    ];

    protected function casts(): array
    {
        return [
            'valor_total'      => 'decimal:2',
            'valor_recebido'   => 'decimal:2',
            'desconto'         => 'decimal:2',
            'juros'            => 'decimal:2',
            'multa'            => 'decimal:2',
            'data_vencimento'  => 'date',
            'data_competencia' => 'date',
            'data_recebimento' => 'date',
            'tem_rateio'       => 'boolean',
        ];
    }

    public function empresa(): BelongsTo    { return $this->belongsTo(Empresa::class); }
    public function cliente(): BelongsTo    { return $this->belongsTo(Cliente::class); }
    public function categoria(): BelongsTo  { return $this->belongsTo(CategoriaFinanceira::class, 'categoria_id'); }
    public function centroCusto(): BelongsTo { return $this->belongsTo(CentroCusto::class, 'centro_custo_id'); }
    public function formaRecebimento(): BelongsTo { return $this->belongsTo(FormaPagamento::class, 'forma_recebimento_id'); }
    public function contaBancaria(): BelongsTo { return $this->belongsTo(ContaBancaria::class, 'conta_bancaria_id'); }
    public function user(): BelongsTo       { return $this->belongsTo(User::class); }

    public function parcelas(): HasMany
    {
        return $this->hasMany(ParcelaReceber::class);
    }

    public function getValorAbertoAttribute(): float
    {
        return max(0, $this->valor_total - $this->valor_recebido - $this->desconto);
    }

    public function scopePendentes($query)
    {
        return $query->whereIn('status', ['pendente', 'parcial', 'vencido']);
    }

    public function scopeVencidas($query)
    {
        return $query->where('data_vencimento', '<', today())->whereIn('status', ['pendente', 'parcial']);
    }

    public function scopeDoMes($query, $mes = null, $ano = null)
    {
        $mes = $mes ?? now()->month;
        $ano = $ano ?? now()->year;
        return $query->whereMonth('data_vencimento', $mes)->whereYear('data_vencimento', $ano);
    }
}
