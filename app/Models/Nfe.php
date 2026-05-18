<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nfe extends Model
{
    use SoftDeletes;

    protected $table = 'nfes';

    protected $fillable = [
        'empresa_id',
        'pedido_id',
        'conta_receber_id',
        'cliente_id',
        'numero',
        'serie',
        'chave_acesso',
        'status',
        'natureza_operacao',
        'valor_produtos',
        'valor_frete',
        'valor_desconto',
        'valor_total',
        'data_emissao',
        'data_competencia',
        'xml_nfe',
        'pdf_danfe_url',
        'link_danfe',
        'webmaniabr_id',
        'motivo_cancelamento',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'valor_produtos'  => 'decimal:2',
            'valor_frete'     => 'decimal:2',
            'valor_desconto'  => 'decimal:2',
            'valor_total'     => 'decimal:2',
            'data_emissao'    => 'date',
            'data_competencia'=> 'date',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function contaReceber(): BelongsTo
    {
        return $this->belongsTo(ContaReceber::class);
    }

    public function pedidoVinculado(): BelongsTo
    {
        return $this->belongsTo(PedidoVinculado::class, 'pedido_id');
    }

    // -------------------------------------------------------------------------
    // Status helpers
    // -------------------------------------------------------------------------

    public function estaAutorizada(): bool
    {
        return $this->status === 'autorizada';
    }

    public function podeEmitir(): bool
    {
        return $this->status === 'rascunho';
    }

    public function podeCancelar(): bool
    {
        return $this->status === 'autorizada';
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeDoEmpresa($query, int $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getNumeroSerieAttribute(): string
    {
        $numero = $this->numero ?? 'S/N';
        return "{$numero}/{$this->serie}";
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'rascunho'    => 'Rascunho',
            'processando' => 'Processando',
            'autorizada'  => 'Autorizada',
            'cancelada'   => 'Cancelada',
            'denegada'    => 'Denegada',
            default       => $this->status,
        };
    }

    public function getStatusVariantAttribute(): string
    {
        return match ($this->status) {
            'rascunho'    => 'default',
            'processando' => 'info',
            'autorizada'  => 'success',
            'cancelada'   => 'danger',
            'denegada'    => 'warning',
            default       => 'default',
        };
    }
}
