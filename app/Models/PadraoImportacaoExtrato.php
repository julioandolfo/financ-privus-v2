<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PadraoImportacaoExtrato extends Model
{
    protected $table = 'padroes_importacao_extrato';

    protected $fillable = [
        'empresa_id',
        'descricao_contem',
        'tipo_correspondencia',
        'tipo_transacao',
        'categoria_id',
        'descricao_padrao',
        'prioridade',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'prioridade' => 'integer',
            'ativo'      => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaFinanceira::class, 'categoria_id');
    }

    /**
     * Find the first active pattern that matches the given description and transaction type,
     * ordered by prioridade DESC (highest priority first).
     */
    public static function matchear(string $descricao, string $tipo, int $empresaId): ?self
    {
        $padroes = self::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->where(function ($q) use ($tipo) {
                $q->where('tipo_transacao', 'ambos')
                  ->orWhere('tipo_transacao', $tipo);
            })
            ->orderByDesc('prioridade')
            ->get();

        foreach ($padroes as $padrao) {
            $descricaoLower  = mb_strtolower($descricao);
            $padraoLower     = mb_strtolower($padrao->descricao_contem);

            $match = match ($padrao->tipo_correspondencia) {
                'exato'      => $descricaoLower === $padraoLower,
                'comeca_com' => str_starts_with($descricaoLower, $padraoLower),
                default      => str_contains($descricaoLower, $padraoLower),
            };

            if ($match) {
                return $padrao;
            }
        }

        return null;
    }
}
