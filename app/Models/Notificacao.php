<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Notificacao extends Model
{
    protected $table = 'notificacoes';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'tipo',
        'titulo',
        'mensagem',
        'link',
        'icone',
        'cor',
        'lida',
        'lida_em',
    ];

    protected function casts(): array
    {
        return [
            'lida'    => 'boolean',
            'lida_em' => 'datetime',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeNaoLidas(Builder $query): Builder
    {
        return $query->where('lida', false);
    }

    // ─── Static factory ───────────────────────────────────────────────────────

    public static function criar(
        int $userId,
        int $empresaId,
        string $tipo,
        string $titulo,
        string $mensagem,
        ?string $link = null,
        string $icone = 'bell',
        string $cor = 'blue'
    ): self {
        return static::create([
            'user_id'    => $userId,
            'empresa_id' => $empresaId,
            'tipo'       => $tipo,
            'titulo'     => $titulo,
            'mensagem'   => $mensagem,
            'link'       => $link,
            'icone'      => $icone,
            'cor'        => $cor,
            'lida'       => false,
        ]);
    }
}
