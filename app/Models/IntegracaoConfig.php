<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegracaoConfig extends Model
{
    protected $table = 'integracoes_config';

    protected $fillable = [
        'empresa_id', 'tipo', 'nome', 'ativo',
        'configuracoes', 'ultimo_sync', 'status_sync', 'ultimo_erro',
    ];

    protected function casts(): array
    {
        return [
            'ativo'          => 'boolean',
            'configuracoes'  => 'array',
            'ultimo_sync'    => 'datetime',
        ];
    }

    public function empresa(): BelongsTo { return $this->belongsTo(Empresa::class); }

    public function config(string $chave, mixed $default = null): mixed
    {
        return data_get($this->configuracoes, $chave, $default);
    }

    public static function forEmpresa(int $empresaId, string $tipo): ?self
    {
        return static::where('empresa_id', $empresaId)->where('tipo', $tipo)->first();
    }

    public static function tiposDisponiveis(): array
    {
        return [
            'woocommerce'    => ['label' => 'WooCommerce',         'icon' => '🛒', 'descricao' => 'Sincronize pedidos e clientes com sua loja WooCommerce'],
            'whatsapp'       => ['label' => 'WhatsApp (Evolution)','icon' => '💬', 'descricao' => 'Notificações e cobranças via WhatsApp'],
            'boleto'         => ['label' => 'Boleto Bancário',     'icon' => '🏦', 'descricao' => 'Emissão de boletos via API bancária'],
            'nfe'            => ['label' => 'NF-e / NFS-e',        'icon' => '📄', 'descricao' => 'Emissão de notas fiscais eletrônicas'],
        ];
    }
}
